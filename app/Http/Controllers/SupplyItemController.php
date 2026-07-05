<?php

namespace App\Http\Controllers;

use App\Models\SupplyCategory;
use App\Models\SupplyItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class SupplyItemController extends Controller
{
    // list items, optionally filtered by category
    public function index(Request $request)
    {
        $categories = SupplyCategory::all();

        $query = SupplyItem::with('category');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('item_name', 'ilike', '%' . $request->search . '%');
        }

        $items = $query->orderBy('item_name')->paginate(10)->withQueryString();

        return view('supplies.index', compact('items', 'categories'));
    }

    public function create()
    {
        $categories = SupplyCategory::all();
        return view('supplies.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:supply_categories,id',
            'item_name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'current_stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'expiration_date' => 'nullable|date',
        ]);

        SupplyItem::create($validated);

        return redirect()->route('supplies.index')->with('success', 'Supply item added.');
    }

    public function edit(SupplyItem $supply)
    {
        $categories = SupplyCategory::all();
        return view('supplies.edit', compact('supply', 'categories'));
    }

    public function update(Request $request, SupplyItem $supply)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:supply_categories,id',
            'item_name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'current_stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'expiration_date' => 'nullable|date',
        ]);

        $supply->update($validated);
        \App\Models\ExpiryDismissal::where('supply_item_id', $supply->id)->delete();
        return redirect()->route('supplies.index')->with('success', 'Supply item updated.');
    }
    
    public function category(Request $request, SupplyCategory $category)
    {
    $categories = SupplyCategory::all();

    $query = SupplyItem::with('category')->where('category_id', $category->id);

    if ($request->filled('search')) {
        $query->where('item_name', 'ilike', '%' . $request->search . '%');
    }

    $items = $query->orderBy('item_name')->paginate(10)->withQueryString();

    return view('supplies.index', compact('items', 'categories'))
        ->with('activeCategory', $category);
    }

    public function dismissExpiry(SupplyItem $supply)
    {
    \App\Models\ExpiryDismissal::firstOrCreate([
        'user_id' => Auth::id(),
        'supply_item_id' => $supply->id,
    ]);

    return back()->with('success', 'Notification dismissed.');
    }

    public function destroy(SupplyItem $supply)
    {
        $supply->delete();
        return back()->with('success', 'Supply item deleted.');
    }
}