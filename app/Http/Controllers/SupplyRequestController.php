<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Models\SupplyItem;
use App\Models\SupplyRequest;
use App\Models\SupplyRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Withdrawal;
use App\Models\WithdrawalItem;
use Illuminate\Support\Facades\Auth;

class SupplyRequestController extends Controller
{
    // public page — no login required
   public function create()
    {
        $personnel = Personnel::orderBy('name')->get();
        $items = SupplyItem::with('category')
            ->where('balance_per_card', '>', 0)
            ->orderBy('description')
            ->get();
        $categories = \App\Models\SupplyCategory::orderBy('name')->get();

        return view('requests.create', compact('personnel', 'items', 'categories'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'personnel_id' => 'required|exists:personnel,id',
            'purpose' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.supply_item_id' => 'required|exists:supply_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated) {
            $supplyRequest = SupplyRequest::create([
                'personnel_id' => $validated['personnel_id'],
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
        });

        return redirect()->route('requests.submitted');
    }

    // admin — list requests
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $requests = SupplyRequest::with(['personnel', 'items.supplyItem', 'reviewedBy'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $pendingCount = SupplyRequest::where('status', 'pending')->count();

        return view('requests.index', compact('requests', 'status', 'pendingCount'));
    }

    // admin — the printable QR poster
    public function qrCode()
    {
        $url = route('requests.create');
        return view('requests.qr', compact('url'));
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

    public function submitted()
    {
        return view('requests.submitted');
    }
}