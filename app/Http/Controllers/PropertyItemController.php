<?php

namespace App\Http\Controllers;

use App\Models\PropertyItem;
use App\Models\PropertyCategory;
use App\Models\Personnel;
use Illuminate\Http\Request;

class PropertyItemController extends Controller
{
    public function index(Request $request)
{
    $categories = PropertyCategory::orderBy('name')->get();
    $query = PropertyItem::with(['category', 'personnel']);

    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }

    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }

    if ($request->filled('search')) {
        $search = trim($request->search);

        // get all IDs first, then filter by description OR range match
        $matchingIds = PropertyItem::all()->filter(function ($item) use ($search) {
            // match description
            if (stripos($item->description, $search) !== false) {
                return true;
            }
            // match if search falls within this item's property number range
            return $this->numberInRange($item, $search);
        })->pluck('id');

        $query->whereIn('id', $matchingIds);
    }

    $items = $query->orderBy('description')->paginate(15)->withQueryString();

    return view('property.index', compact('items', 'categories'));
}

// checks if a searched property number falls within an item's generated range
private function numberInRange(PropertyItem $item, string $search): bool
{
    if (!$item->property_no) {
        return false;
    }

    // direct match on the stored (starting) number
    if (stripos($item->property_no, $search) !== false) {
        return true;
    }

    // split both the item's start number and the search into prefix + digits
    if (preg_match('/^(.*?)(\d+)$/', $item->property_no, $itemM)
        && preg_match('/^(.*?)(\d+)$/', $search, $searchM)) {

        $itemPrefix = $itemM[1];
        $searchPrefix = $searchM[1];

        // prefixes must match (e.g. "24-05-")
        if ($itemPrefix !== $searchPrefix) {
            return false;
        }

        $start = (int) $itemM[2];
        $end = $start + ($item->quantity ?? 1) - 1;
        $target = (int) $searchM[2];

        return $target >= $start && $target <= $end;
    }

    return false;
}

    public function create()
    {
        $categories = PropertyCategory::orderBy('name')->get();
        $personnel = Personnel::orderBy('name')->get();
        return view('property.create', compact('categories', 'personnel'));
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'type' => 'required|in:semi-expendable,expendable',
        'category_id' => 'required|exists:property_categories,id',
        'personnel_id' => 'nullable|exists:personnel,id',
        'description' => 'required|string|max:255',
        'property_no' => 'required|string|max:100',
        'quantity' => 'required|integer|min:1|max:9999',
        'unit' => 'required|string|max:50',
        'unit_value' => 'nullable|numeric|min:0',
        'remarks' => 'nullable|string|max:255',
    ]);

    // on_hand equals the quantity
    $validated['on_hand_per_count'] = $validated['quantity'];

    PropertyItem::create($validated);

    return redirect()->route('property.index')->with('success', 'Property item added.');
}

    public function edit(PropertyItem $property)
    {
        $categories = PropertyCategory::orderBy('name')->get();
        $personnel = Personnel::orderBy('name')->get();
        return view('property.edit', compact('property', 'categories', 'personnel'));
    }

    public function update(Request $request, PropertyItem $property)
{
    $validated = $request->validate([
        'type' => 'required|in:semi-expendable,expendable',
        'category_id' => 'required|exists:property_categories,id',
        'personnel_id' => 'nullable|exists:personnel,id',
        'description' => 'required|string|max:255',
        'property_no' => 'required|string|max:100',
        'quantity' => 'required|integer|min:1|max:9999',
        'unit' => 'required|string|max:50',
        'unit_value' => 'nullable|numeric|min:0',
        'remarks' => 'nullable|string|max:255',
    ]);

    // on_hand equals the quantity
    $validated['on_hand_per_count'] = $validated['quantity'];

    $property->update($validated);

    return redirect()->route('property.index')->with('success', 'Property item updated.');
}

    public function destroy(PropertyItem $property)
    {
        $property->delete();
        return back()->with('success', 'Property item deleted.');
    }

    public function reportOptions()
{
    $categories = PropertyCategory::orderBy('name')->get();
    return view('property.report-options', compact('categories'));
}

public function report(Request $request)
{
    $request->validate([
        'category_id' => 'required|exists:property_categories,id',
        'type' => 'required|in:semi-expendable,expendable',
    ]);

    $category = PropertyCategory::findOrFail($request->category_id);

    $items = PropertyItem::with('personnel')
        ->where('category_id', $category->id)
        ->where('type', $request->type)
        ->orderBy('personnel_id')
        ->orderBy('description')
        ->orderBy('property_no')
        ->get();

    // grand total = sum of (unit_value × on_hand_per_count)
    $grandTotal = $items->sum(function ($item) {
        return (float) $item->unit_value * (int) $item->on_hand_per_count;
    });

    return view('property.report', compact('category', 'items', 'grandTotal', 'request'));
}
}