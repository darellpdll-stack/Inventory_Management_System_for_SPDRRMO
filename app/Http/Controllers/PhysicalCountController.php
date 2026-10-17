<?php

namespace App\Http\Controllers;

use App\Models\BorrowedUnit;
use App\Models\PhysicalCount;
use App\Models\PhysicalCountScan;
use App\Models\PropertyItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PhysicalCountController extends Controller
{
    public function index()
    {
        $counts = PhysicalCount::with('startedBy', 'closedBy')
            ->withCount('scans')
            ->orderByDesc('created_at')
            ->paginate(15);

        $openCount = PhysicalCount::where('status', 'open')->first();

        return view('counts.index', compact('counts', 'openCount'));
    }

    public function store(Request $request)
    {
        // one open count at a time, or scans would land in the wrong session
        if ($existing = PhysicalCount::where('status', 'open')->first()) {
            return redirect()->route('counts.show', $existing)
                ->with('error', 'A count is already in progress. Close it before starting another.');
        }

        $validated = $request->validate([
            'count_date' => 'required|date|before_or_equal:today',
            'remarks' => 'nullable|string|max:255',
        ]);

        $count = PhysicalCount::create([
            'count_date' => $validated['count_date'],
            'remarks' => $validated['remarks'] ?? null,
            'status' => 'open',
            'started_by' => Auth::id(),
        ]);

        return redirect()->route('counts.show', $count)
            ->with('success', 'Count ' . $count->countNo() . ' started. Scan the tags you find.');
    }

    public function show(PhysicalCount $count)
    {
        $expected = $this->expectedUnits();

        $scans = $count->scans()
            ->with('propertyItem.category', 'scannedBy')
            ->orderByDesc('id')
            ->get();

        $scannedNumbers = $scans->pluck('property_no')->all();

        $found = $expected->filter(fn ($u) => in_array($u->number, $scannedNumbers, true));
        $missing = $expected->reject(fn ($u) => in_array($u->number, $scannedNumbers, true));
        $unexpected = $scans->whereNull('property_item_id');

        // a unit out on a Borrower's Form is absent for a good reason
        $borrowedNumbers = BorrowedUnit::stillOut()
            ->with('borrowing.personnel')
            ->get()
            ->keyBy('property_no');

        $missingBorrowed = $missing->filter(fn ($u) => $borrowedNumbers->has($u->number));
        $missingUnaccounted = $missing->reject(fn ($u) => $borrowedNumbers->has($u->number));

        // found on the shelf although the records say it is lent out — usually
        // a return that was never recorded
        $foundButBorrowed = $scans->filter(
            fn ($scan) => $scan->property_item_id && $borrowedNumbers->has($scan->property_no)
        );

        return view('counts.show', compact(
            'count', 'scans', 'expected', 'found',
            'missingBorrowed', 'missingUnaccounted', 'unexpected',
            'borrowedNumbers', 'foundButBorrowed'
        ));
    }

    // one tag read during the count
    public function scan(Request $request, PhysicalCount $count)
    {
        if (! $count->isOpen()) {
            return back()->with('error', 'This count has been closed. Start a new one to keep counting.');
        }

        $validated = $request->validate([
            'property_no' => 'required|string|max:100',
        ]);

        $code = trim($validated['property_no']);

        if ($count->scans()->where('property_no', $code)->exists()) {
            return back()->with('error', "{$code} has already been counted in this session.");
        }

        $item = PropertyItem::whereNotNull('property_no')
            ->get()
            ->first(fn ($p) => in_array($code, $p->propertyNoList(), true));

        PhysicalCountScan::create([
            'physical_count_id' => $count->id,
            'property_no' => $code,
            'property_item_id' => $item?->id,
            'scanned_by' => Auth::id(),
        ]);

        // a known tag needs no interruption — the list updating is feedback enough.
        // an unknown one is worth stopping for.
        if (! $item) {
            return back()->with('error', "{$code} is not in the property records. It has been recorded as unexpected.");
        }

        // on the shelf, but the records say someone has it
        $borrowed = BorrowedUnit::stillOut()
            ->with('borrowing.personnel')
            ->where('property_no', $code)
            ->first();

        if ($borrowed) {
            $who = $borrowed->borrowing->personnel->name ?? 'someone';
            $ref = $borrowed->borrowing?->borrowNo() ?? '';

            return back()->with('error',
                "{$code} was counted, but the records say it is borrowed by {$who} ({$ref}). "
                . 'It may have been returned without being recorded.');
        }

        return back();
    }

    // tick off several units at once, for counting without a scanner
    public function tick(Request $request, PhysicalCount $count)
    {
        if (! $count->isOpen()) {
            return back()->with('error', 'This count has been closed. Start a new one to keep counting.');
        }

        $validated = $request->validate([
            'numbers' => 'required|array|min:1',
            'numbers.*' => 'string|max:100',
        ]);

        $already = $count->scans()->pluck('property_no')->all();
        $toAdd = array_diff(array_unique($validated['numbers']), $already);

        if (empty($toAdd)) {
            return back()->with('error', 'Those units were already counted.');
        }

        // one lookup for the whole batch rather than one per unit
        $items = PropertyItem::whereNotNull('property_no')->get();
        $added = 0;

        DB::transaction(function () use ($toAdd, $items, $count, &$added) {
            foreach ($toAdd as $code) {
                $item = $items->first(fn ($p) => in_array($code, $p->propertyNoList(), true));

                PhysicalCountScan::create([
                    'physical_count_id' => $count->id,
                    'property_no' => $code,
                    'property_item_id' => $item?->id,
                    'scanned_by' => Auth::id(),
                ]);

                $added++;
            }
        });

        return back()->with('success', "{$added} " . \Illuminate\Support\Str::plural('unit', $added) . ' counted.');
    }

    // undo a mis-scan
    public function destroyScan(PhysicalCount $count, PhysicalCountScan $scan)
    {
        if (! $count->isOpen()) {
            return redirect()->route('counts.show', $count, 303)
                ->with('error', 'This count has been closed.');
        }

        if ($scan->physical_count_id !== $count->id) {
            return redirect()->route('counts.show', $count, 303)
                ->with('error', 'That scan belongs to another count.');
        }

        $scan->delete();

        // no popup — the row disappearing says enough, and removing several
        // in a row should not mean dismissing a dialog each time.
        // 303 so the browser follows with a GET; a plain 302 can be re-sent
        // as DELETE, which would hit a route that does not exist
        return redirect()->route('counts.show', $count, 303);
    }

    public function close(Request $request, PhysicalCount $count)
    {
        if (! $count->isOpen()) {
            return back()->with('error', 'This count is already closed.');
        }

        $validated = $request->validate([
            'remarks' => 'nullable|string|max:255',
        ]);

        $count->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => Auth::id(),
            'remarks' => $validated['remarks'] ?? $count->remarks,
        ]);

        return back()->with('success', 'Count closed. The results are now a fixed record.');
    }

    // give up on a count that was never finished. the scans stay as history,
    // but the count is not a result, so it can never be written to the records
    public function abandon(Request $request, PhysicalCount $count)
    {
        if (! $count->isOpen()) {
            return back()->with('error', 'Only a count that is still in progress can be abandoned.');
        }

        $validated = $request->validate([
            'remarks' => 'nullable|string|max:255',
        ]);

        $count->update([
            'status' => 'abandoned',
            'closed_at' => now(),
            'closed_by' => Auth::id(),
            'remarks' => $validated['remarks'] ?: $count->remarks,
        ]);

        return redirect()->route('counts.index')->with('success',
            $count->countNo() . ' was abandoned. Its scans are kept as a record, and a new count can now be started.');
    }

    // write the counted quantities onto the property records, which is what
    // feeds the shortage and overage columns on the printed report
    public function apply(PhysicalCount $count)
    {
        if ($count->isOpen()) {
            return back()->with('error', 'Close the count before applying it to the records.');
        }

        if ($count->isAbandoned()) {
            return back()->with('error',
                'This count was abandoned, so it was never finished. An unfinished count cannot be written to the records.');
        }

        $foundPerItem = $count->scans()
            ->whereNotNull('property_item_id')
            ->get()
            ->groupBy('property_item_id');

        // a unit out on a Borrower's Form is not missing — the office is still
        // accountable for it, so it counts towards what is on hand
        $borrowedPerItem = BorrowedUnit::stillOut()
            ->get()
            ->groupBy('property_item_id');

        $updated = 0;

        DB::transaction(function () use ($foundPerItem, $borrowedPerItem, &$updated) {
            // every item that was either counted or has something lent out
            $itemIds = $foundPerItem->keys()->merge($borrowedPerItem->keys())->unique();

            foreach ($itemIds as $itemId) {
                $found = $foundPerItem->get($itemId)?->count() ?? 0;
                $borrowed = $borrowedPerItem->get($itemId)?->count() ?? 0;

                // nothing of this item was counted and nothing is lent out,
                // so this count says nothing about it
                if ($found === 0 && $borrowed === 0) {
                    continue;
                }

                // an item only touched by a borrowing, never scanned, was not
                // part of this count — leave its record alone
                if ($found === 0) {
                    continue;
                }

                PropertyItem::where('id', $itemId)
                    ->update(['on_hand_per_count' => $found + $borrowed]);

                $updated++;
            }
        });

        return back()->with('success',
            "Counted quantities written to {$updated} " . \Illuminate\Support\Str::plural('item', $updated)
            . '. Units out on a Borrower\'s Form were counted as present.');
    }

    /**
     * Every individual property number the records say exists, each paired
     * with the item it belongs to.
     */
    private function expectedUnits()
    {
        return PropertyItem::with('category', 'personnel')
            ->whereNotNull('property_no')
            ->orderBy('description')
            ->get()
            ->flatMap(function ($item) {
                return collect($item->propertyNoList())
                    ->filter(fn ($no) => $no && $no !== '—')
                    ->map(fn ($no) => (object) ['number' => $no, 'item' => $item]);
            })
            ->values();
    }
}
