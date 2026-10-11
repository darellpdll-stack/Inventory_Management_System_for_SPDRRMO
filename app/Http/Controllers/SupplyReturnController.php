<?php

namespace App\Http\Controllers;

use App\Models\Release;
use App\Models\ReleaseItem;
use App\Models\StockEntry;
use App\Models\SupplyItem;
use App\Models\SupplyReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplyReturnController extends Controller
{
    // record items coming back from a release
    public function store(Request $request, Release $release)
    {
        $validated = $request->validate([
            'date_returned' => [
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:' . Carbon::parse($release->date_released)->toDateString(),
            ],
            'returned_by' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:255',
            'lines' => 'required|array',
            'lines.*.release_item_id' => 'required|exists:release_items,id',
            'lines.*.quantity' => 'required|integer|min:0',
            'lines.*.condition' => 'required|in:usable,damaged',
            'lines.*.expiration_date' => 'nullable|date',
        ]);

        // nothing to do if every line was left at zero
        $total = collect($validated['lines'])->sum('quantity');
        if ($total < 1) {
            return back()->with('error', 'Enter a quantity for at least one item.');
        }

        try {
            DB::transaction(function () use ($validated, $release) {
                foreach ($validated['lines'] as $line) {
                    $qty = (int) $line['quantity'];
                    if ($qty < 1) {
                        continue;
                    }

                    $releaseItem = ReleaseItem::lockForUpdate()->find($line['release_item_id']);

                    // the line has to belong to the release being returned against
                    if ($releaseItem->release_id !== $release->id) {
                        throw new \Exception('One of the items does not belong to this release.');
                    }

                    // can't return more than what actually went out
                    $returnable = $releaseItem->returnableQuantity();
                    if ($qty > $returnable) {
                        throw new \Exception(
                            "Cannot return {$qty} — only {$returnable} of that item is still outstanding."
                        );
                    }

                    $item = SupplyItem::lockForUpdate()->find($releaseItem->supply_item_id);
                    $usable = $line['condition'] === 'usable';
                    $stockEntry = null;

                    if ($usable) {
                        if ($item->tracksExpiry() && empty($line['expiration_date'])) {
                            throw new \Exception(
                                "An expiration date is required for {$item->description}."
                            );
                        }

                        // the returned stock comes back as a batch of its own
                        $stockEntry = StockEntry::create([
                            'supply_item_id'     => $item->id,
                            'date_received'      => $validated['date_returned'],
                            'delivered_by'       => $validated['returned_by'] ?? null,
                            'reference_no'       => 'RET-' . $release->request->requestNo(),
                            'quantity'           => $qty,
                            'remaining_quantity' => $qty,
                            'expiration_date'    => $item->tracksExpiry()
                                ? $line['expiration_date']
                                : null,
                            'remarks'            => 'Returned to stock',
                            'recorded_by'        => Auth::id(),
                        ]);

                        $item->increment('balance_per_card', $qty);
                        $item->increment('on_hand_per_count', $qty);
                        $item->refresh()->refreshExpiryFromBatches();
                    }

                    SupplyReturn::create([
                        'release_item_id' => $releaseItem->id,
                        'supply_item_id'  => $item->id,
                        'quantity'        => $qty,
                        'date_returned'   => $validated['date_returned'],
                        'returned_by'     => $validated['returned_by'] ?? null,
                        'condition'       => $line['condition'],
                        'stock_entry_id'  => $stockEntry?->id,
                        'remarks'         => $validated['remarks'] ?? null,
                        'received_by'     => Auth::id(),
                    ]);
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Return recorded and stock updated.');
    }
}