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
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'ilike', '%' . $search . '%')
                  ->orWhere('property_no', 'ilike', '%' . $search . '%');
            });
        }

        $items = $query->orderBy('description')->paginate(15)->withQueryString();

        return view('property.index', compact('items', 'categories'));
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
            'property_no' => 'nullable|string|max:100',
            'unit' => 'required|string|max:50',
            'unit_value' => 'nullable|numeric|min:0',
            'on_hand_per_count' => 'required|integer|min:0',
            'remarks' => 'nullable|string|max:255',
        ]);

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