<?php

namespace App\Http\Controllers;

use App\Models\BorrowedUnit;
use App\Models\Borrowing;
use App\Models\Personnel;
use App\Models\PropertyCategory;
use App\Models\PropertyItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BorrowingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $borrowings = Borrowing::with(['personnel', 'units.propertyItem'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = trim($request->search);
                $q->where(function ($q) use ($term) {
                    $q->whereHas('personnel', fn ($p) => $p->where('name', 'ilike', "%{$term}%"));
                    if (preg_match('/(\d+)$/', $term, $m)) {
                        $q->orWhere('id', (int) $m[1]);
                    }
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $pendingCount = Borrowing::where('status', 'pending')->count();
        $outCount = Borrowing::where('status', 'released')->count();

        return view('borrowings.index', compact('borrowings', 'status', 'pendingCount', 'outCount'));
    }

    public function create()
    {
        $personnel = Personnel::orderBy('name')->get();
        $categories = PropertyCategory::orderBy('name')->get();

        // only semi-expendable property is lent out and returned
        $items = PropertyItem::with('category')
            ->where('type', 'semi-expendable')
            ->orderBy('description')
            ->get()
            ->filter(fn ($item) => $item->availableQuantity() > 0)
            ->values();

        return view('borrowings.create', compact('personnel', 'categories', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'personnel_id' => 'required|exists:personnel,id',
            'date_needed' => 'required|date',
            'date_due' => 'nullable|date|after_or_equal:date_needed',
            'purpose' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:255',
            // tagged units arrive as "<property_item_id>|<property_no>"
            'picked' => 'nullable|array',
            'picked.*' => 'string|max:150',
            // untagged items are borrowed by count instead
            'counts' => 'nullable|array',
            'counts.*' => 'nullable|integer|min:0',
        ]);

        $picked = $validated['picked'] ?? [];
        $counts = array_filter($validated['counts'] ?? [], fn ($qty) => (int) $qty > 0);

        if (empty($picked) && empty($counts)) {
            return back()->withInput()->with('error', 'Select at least one piece of equipment to borrow.');
        }

        try {
            $borrowing = DB::transaction(function () use ($validated, $picked, $counts) {
                $borrowing = Borrowing::create([
                    'personnel_id' => $validated['personnel_id'],
                    'date_needed' => $validated['date_needed'],
                    'date_due' => $validated['date_due'] ?? null,
                    'purpose' => $validated['purpose'] ?? null,
                    'remarks' => $validated['remarks'] ?? null,
                    'status' => 'pending',
                ]);

                // specific tagged units
                foreach ($picked as $value) {
                    [$itemId, $number] = array_pad(explode('|', $value, 2), 2, null);

                    $item = PropertyItem::lockForUpdate()->find((int) $itemId);
                    if (! $item) {
                        throw new \Exception('One of the selected items no longer exists.');
                    }

                    // somebody may have reserved it between loading the form and submitting
                    if (! in_array($number, $item->availableNumbers(), true)) {
                        throw new \Exception("Property no. {$number} is no longer available — it was reserved or lent out in the meantime.");
                    }

                    BorrowedUnit::create([
                        'borrowing_id' => $borrowing->id,
                        'property_item_id' => $item->id,
                        'property_no' => $number,
                    ]);
                }

                // untagged items, lent by count
                foreach ($counts as $itemId => $qty) {
                    $item = PropertyItem::lockForUpdate()->find((int) $itemId);
                    if (! $item) {
                        throw new \Exception('One of the selected items no longer exists.');
                    }

                    $qty = (int) $qty;
                    if ($qty > $item->availableQuantity()) {
                        throw new \Exception(
                            "Only {$item->availableQuantity()} of {$item->description} are available to borrow."
                        );
                    }

                    for ($i = 0; $i < $qty; $i++) {
                        BorrowedUnit::create([
                            'borrowing_id' => $borrowing->id,
                            'property_item_id' => $item->id,
                            'property_no' => null,
                        ]);
                    }
                }

                return $borrowing;
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('borrowings.show', $borrowing)
            ->with('success', 'Borrowing ' . $borrowing->borrowNo() . ' recorded.');
    }

    public function show(Borrowing $borrowing)
    {
        $borrowing->load([
            'personnel', 'units.propertyItem.category', 'units.receivedBy',
            'reviewedBy', 'releasedBy',
        ]);

        return view('borrowings.show', compact('borrowing'));
    }

    // the Borrower's Form, prepared before the equipment is handed over
    public function form(Borrowing $borrowing)
    {
        $borrowing->load(['personnel', 'units.propertyItem.category', 'releasedBy']);

        return view('borrowings.form', compact('borrowing'));
    }

    public function approve(Borrowing $borrowing)
    {
        if ($borrowing->status !== 'pending') {
            return back()->with('error', 'This borrowing has already been reviewed.');
        }

        $borrowing->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', "Approved. Print the Borrower's Form before handing over the equipment.");
    }

    public function decline(Request $request, Borrowing $borrowing)
    {
        if ($borrowing->status !== 'pending') {
            return back()->with('error', 'This borrowing has already been reviewed.');
        }

        $validated = $request->validate([
            'decline_reason' => 'nullable|string|max:255',
        ]);

        $borrowing->update([
            'status' => 'declined',
            'decline_reason' => $validated['decline_reason'] ?? null,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Borrowing declined. The reserved units are free again.');
    }

    // the borrower has arrived, signed the form, and taken the equipment
    public function release(Request $request, Borrowing $borrowing)
    {
        if ($borrowing->status !== 'approved') {
            return back()->with('error', 'Only an approved borrowing can be released.');
        }

        $validated = $request->validate([
            'date_released' => 'required|date|before_or_equal:today',
        ]);

        $borrowing->update([
            'status' => 'released',
            'date_released' => $validated['date_released'],
            'released_by' => Auth::id(),
        ]);

        return back()->with('success', 'Equipment released. It is now recorded as borrowed.');
    }

    // some or all of the equipment has come back
    public function returnUnits(Request $request, Borrowing $borrowing)
    {
        if ($borrowing->status !== 'released') {
            return back()->with('error', 'Only borrowed equipment can be returned.');
        }

        $validated = $request->validate([
            'date_returned' => [
                'required', 'date', 'before_or_equal:today',
                'after_or_equal:' . $borrowing->date_released->toDateString(),
            ],
            'unit_ids' => 'required|array|min:1',
            'unit_ids.*' => 'required|exists:borrowed_units,id',
            'condition' => 'required|in:good,damaged',
            'remarks' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($validated, $borrowing) {
                foreach ($validated['unit_ids'] as $unitId) {
                    $unit = BorrowedUnit::lockForUpdate()->find($unitId);

                    if ($unit->borrowing_id !== $borrowing->id) {
                        throw new \Exception('One of the units does not belong to this borrowing.');
                    }

                    if ($unit->returned_at !== null) {
                        throw new \Exception('One of the units has already been returned.');
                    }

                    $unit->update([
                        'returned_at' => $validated['date_returned'],
                        'condition' => $validated['condition'],
                        'received_by' => Auth::id(),
                        'remarks' => $validated['remarks'] ?? null,
                    ]);
                }

                // closed only once every unit is back
                $borrowing->load('units');
                if ($borrowing->isFullyReturned()) {
                    $borrowing->update(['status' => 'returned']);
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Return recorded.');
    }
}
