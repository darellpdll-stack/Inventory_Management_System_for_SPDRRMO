<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use App\Models\SupplyItem;
use App\Models\SupplyCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $categories = SupplyCategory::all();

        $query = Withdrawal::with('items.supplyItem.category', 'recordedBy');

        // filter by item category
        if ($request->filled('category')) {
            $catId = $request->category;
            $query->whereHas('items.supplyItem', function ($q) use ($catId) {
                $q->where('category_id', $catId);
            });
        }

        // search by person or item
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('withdrawn_by', 'ilike', '%' . $search . '%')
                  ->orWhereHas('items.supplyItem', function ($q2) use ($search) {
                      $q2->where('description', 'ilike', '%' . $search . '%');
                  });
            });
        }

        $withdrawals = $query->orderByDesc('date_withdrawn')->paginate(10)->withQueryString();

        return view('withdrawals.index', compact('withdrawals', 'categories'));
    }

    public function create()
    {
        $items = SupplyItem::with('category')
            ->where('balance_per_card', '>', 0)
            ->orderBy('description')
            ->get();

        return view('withdrawals.create', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'withdrawn_by' => 'required|string|max:255',
            'date_withdrawn' => 'required|date',
            'date_returned' => 'nullable|date',
            'remark' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.supply_item_id' => 'required|exists:supply_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated) {
            $withdrawal = Withdrawal::create([
                'withdrawn_by' => $validated['withdrawn_by'],
                'date_withdrawn' => $validated['date_withdrawn'],
                'date_returned' => $validated['date_returned'] ?? null,
                'remark' => $validated['remark'] ?? null,
                'recorded_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $line) {
                $item = SupplyItem::lockForUpdate()->findOrFail($line['supply_item_id']);

                // the stock warning — block if not enough
                if ($item->balance_per_card < $line['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Not enough stock for {$item->description}. Only {$item->balance_per_card} available, but you requested {$line['quantity']}.",
                    ]);
                }

                $item->decrement('balance_per_card', $line['quantity']);

                $withdrawal->items()->create([
                    'supply_item_id' => $item->id,
                    'quantity' => $line['quantity'],
                ]);
            }
        });

        return redirect()->route('withdrawals.index')->with('success', 'Withdrawal recorded and stock updated.');
    }

    public function show(Withdrawal $withdrawal)
    {
        $withdrawal->load('items.supplyItem.category', 'recordedBy');
        return view('withdrawals.show', compact('withdrawal'));
    }
}