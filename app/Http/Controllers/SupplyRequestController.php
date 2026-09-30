<?php

namespace App\Http\Controllers;

use App\Models\Release;
use App\Models\ReleaseItem;
use App\Models\Personnel;
use App\Models\SupplyCategory;
use App\Models\SupplyItem;
use App\Models\SupplyRequest;
use App\Models\SupplyRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplyRequestController extends Controller
{
       public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $requests = SupplyRequest::with(['personnel', 'items.supplyItem'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = trim($request->search);
                $q->where(function ($q) use ($term) {
                    $q->whereHas('personnel', fn ($p) => $p->where('name', 'ilike', "%{$term}%"));
                    // "REQ-2026-0003" or "3" both find request #3
                    if (preg_match('/(\d+)$/', $term, $m)) {
                        $q->orWhere('id', (int) $m[1]);
                    }
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $pendingCount = SupplyRequest::where('status', 'pending')->count();
        $forReleaseCount = SupplyRequest::where('status', 'for_release')->count();

        return view('requests.index', compact('requests', 'status', 'pendingCount', 'forReleaseCount'));
    }

    public function create()
    {
        $personnel = Personnel::orderBy('name')->get();
        $items = SupplyItem::with('category')
            ->where('balance_per_card', '>', 0)
            ->orderBy('description')
            ->get();
        $categories = SupplyCategory::orderBy('name')->get();

        return view('requests.create', compact('personnel', 'items', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_date' => 'required|date|before_or_equal:today',
            'personnel_id' => 'required|exists:personnel,id',
            'purpose' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.supply_item_id' => 'required|exists:supply_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $supplyRequest = DB::transaction(function () use ($validated) {
            $supplyRequest = SupplyRequest::create([
                'personnel_id' => $validated['personnel_id'],
                'request_date' => $validated['request_date'],
                'purpose' => $validated['purpose'] ?? null,
                'status' => 'pending',
            ]);

            foreach ($validated['items'] as $line) {
                SupplyRequestItem::create([
                    'supply_request_id' => $supplyRequest->id,
                    'supply_item_id' => $line['supply_item_id'],
                    'quantity' => $line['quantity'],
                ]);
            }

            return $supplyRequest;
        });

        return redirect()->route('requests.show', $supplyRequest)
            ->with('success', 'Request ' . $supplyRequest->requestNo() . ' added.');
    }

    public function show(SupplyRequest $supplyRequest)
    {
        $supplyRequest->load(['personnel', 'items.supplyItem.category', 'reviewedBy', 'releases.items.supplyItem', 'releases.releasedBy']);
        return view('requests.show', compact('supplyRequest'));
    }

        // admin — approve: the decision only, stock moves on release
       
    public function approve(SupplyRequest $supplyRequest)
    {
        if ($supplyRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $supplyRequest->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Request approved. Mark it for release once the items are ready.');
    }
        // admin — items are prepared and ready to hand out
    public function markForRelease(SupplyRequest $supplyRequest)
    {
        if ($supplyRequest->status !== 'approved') {
            return back()->with('error', 'Only approved requests can be marked for release.');
        }

        $supplyRequest->update(['status' => 'for_release']);

        return back()->with('success', 'Request marked for release.');
    }
        // admin — hand out some or all of the approved items
    public function release(Request $request, SupplyRequest $supplyRequest)
    {
        if (!in_array($supplyRequest->status, ['for_release'])) {
            return back()->with('error', 'Only approved requests can be released.');
        }

        $validated = $request->validate([
            'date_released' => 'required|date|before_or_equal:today',
            'received_by' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:255',
            'lines' => 'required|array',
            'lines.*.request_item_id' => 'required|exists:supply_request_items,id',
            'lines.*.quantity' => 'required|integer|min:0',
        ]);

        // nothing to do if every line was left at zero
        $total = collect($validated['lines'])->sum('quantity');
        if ($total < 1) {
            return back()->with('error', 'Enter a quantity for at least one item.');
        }

        try {
            DB::transaction(function () use ($validated, $supplyRequest) {
                $release = Release::create([
                    'supply_request_id' => $supplyRequest->id,
                    'date_released' => $validated['date_released'],
                    'received_by' => $validated['received_by'] ?? null,
                    'remarks' => $validated['remarks'] ?? null,
                    'released_by' => Auth::id(),
                ]);

                foreach ($validated['lines'] as $line) {
                    $qty = (int) $line['quantity'];
                    if ($qty < 1) {
                        continue;
                    }

                    $requestItem = SupplyRequestItem::lockForUpdate()->find($line['request_item_id']);

                    // can't release more than what's still owed on this line
                    if ($qty > $requestItem->remainingQuantity()) {
                        throw new \Exception(
                            "Cannot release {$qty} — only {$requestItem->remainingQuantity()} left to release for this item."
                        );
                    }

                    $item = SupplyItem::lockForUpdate()->find($requestItem->supply_item_id);

                    if ($item->balance_per_card < $qty) {
                        throw new \Exception(
                            "Not enough stock for {$item->description}. Only {$item->balance_per_card} available."
                        );
                    }

                    ReleaseItem::create([
                        'release_id' => $release->id,
                        'supply_item_id' => $item->id,
                        'quantity' => $qty,
                    ]);

                    // stock leaves the office now
                    $item->decrement('balance_per_card', $qty);
                    $item->decrement('on_hand_per_count', $qty);

                    // take from the earliest-expiring batch first
                    $toTake = $qty;
                    foreach ($item->activeBatches()->lockForUpdate()->get() as $batch) {
                        if ($toTake < 1) break;
                        $take = min($toTake, $batch->remaining_quantity);
                        $batch->decrement('remaining_quantity', $take);
                        $toTake -= $take;
                    }

                    $requestItem->increment('released_quantity', $qty);
                }

                // completed only when every line has been fully handed out
                $supplyRequest->load('items');
                if ($supplyRequest->isFullyReleased()) {
                    $supplyRequest->update(['status' => 'completed']);
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Items released and stock updated.');
    }

    // admin — decline
    public function decline(Request $request, SupplyRequest $supplyRequest)
    {
        if ($supplyRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $validated = $request->validate([
            'decline_reason' => 'nullable|string|max:255',
        ]);

        $supplyRequest->update([
            'status' => 'declined',
            'decline_reason' => $validated['decline_reason'] ?? null,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Request declined.');
    }

    // admin — QR poster (dormant, kept for later)
    public function qrCode()
    {
        $url = url('/qr_code/request');
        return view('requests.qr', compact('url'));
    }

    public function submitted()
    {
        return view('requests.submitted');
    }
}