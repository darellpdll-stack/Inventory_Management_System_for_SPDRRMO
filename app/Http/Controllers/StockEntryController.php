<?php

namespace App\Http\Controllers;

use App\Models\StockEntry;
use App\Models\SupplyItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockEntryController extends Controller
{
    public function create(SupplyItem $supply)
    {
        $supply->load('category');
        $entries = $supply->stockEntries()
            ->with('recordedBy')
            ->orderByDesc('date_received')
            ->orderByDesc('id')
            ->get();

        return view('stock.create', compact('supply', 'entries'));
    }

    public function store(Request $request, SupplyItem $supply)
    {
        $validated = $request->validate([
            'date_received' => 'required|date|before_or_equal:today',
            'delivered_by' => 'nullable|string|max:255',
            'reference_no' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:1',
            'expiration_date' => 'nullable|date|after:today',
            'remarks' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $supply) {
            $item = SupplyItem::lockForUpdate()->find($supply->id);

            StockEntry::create([
                'supply_item_id' => $item->id,
                'date_received' => $validated['date_received'],
                'delivered_by' => $validated['delivered_by'] ?? null,
                'reference_no' => $validated['reference_no'] ?? null,
                'quantity' => $validated['quantity'],
                'expiration_date' => $validated['expiration_date'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'recorded_by' => Auth::id(),
            ]);

            // a delivery arrives physically, so both the card and the shelf go up
            $item->increment('balance_per_card', $validated['quantity']);
            $item->increment('on_hand_per_count', $validated['quantity']);

            // if this batch expires sooner than what's recorded, use the earlier date
            if (!empty($validated['expiration_date'])) {
                $batchExpiry = \Carbon\Carbon::parse($validated['expiration_date']);
                if (!$item->expiration_date || $batchExpiry->lt($item->expiration_date)) {
                    $item->expiration_date = $batchExpiry;
                    $item->tracks_expiry = true;
                    $item->save();
                }
            }
        });

        return redirect()->route('stock.create', $supply)
            ->with('success', 'Stock added and balance updated.');
    }
}