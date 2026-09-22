<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Models\SupplyCategory;
use App\Models\SupplyItem;
use App\Models\SupplyRequest;
use App\Models\SupplyRequestItem;
use App\Models\Withdrawal;
use App\Models\WithdrawalItem;
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

        return view('requests.index', compact('requests', 'status', 'pendingCount'));
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
        $supplyRequest->load(['personnel', 'items.supplyItem.category', 'reviewedBy', 'withdrawal']);
        return view('requests.show', compact('supplyRequest'));
    }

    // admin — approve: converts the request into a withdrawal
    public function approve(SupplyRequest $supplyRequest)
    {
        if ($supplyRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        try {
            DB::transaction(function () use ($supplyRequest) {
                $withdrawal = Withdrawal::create([
                    'withdrawn_by' => $supplyRequest->personnel->name,
                    'date_withdrawn' => now()->toDateString(),
                    'remark' => $supplyRequest->purpose,
                    'recorded_by' => Auth::id(),
                ]);

                foreach ($supplyRequest->items as $line) {
                    $item = SupplyItem::lockForUpdate()->find($line->supply_item_id);

                    if ($item->balance_per_card < $line->quantity) {
                        throw new \Exception(
                            "Not enough stock for {$item->description}. Only {$item->balance_per_card} available, but {$line->quantity} was requested."
                        );
                    }

                    WithdrawalItem::create([
                        'withdrawal_id' => $withdrawal->id,
                        'supply_item_id' => $item->id,
                        'quantity' => $line->quantity,
                    ]);

                    $item->decrement('balance_per_card', $line->quantity);
                }

                $supplyRequest->update([
                    'status' => 'approved',
                    'reviewed_by' => Auth::id(),
                    'reviewed_at' => now(),
                    'withdrawal_id' => $withdrawal->id,
                ]);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Request approved and recorded as a withdrawal.');
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