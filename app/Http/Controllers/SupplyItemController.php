<?php

namespace App\Http\Controllers;

use App\Models\SupplyCategory;
use App\Models\SupplyItem;
use App\Models\ExpiryDismissal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplyItemController extends Controller
{
    public function index(Request $request)
    {
        $categories = SupplyCategory::all();
        $query = SupplyItem::with('category');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

            if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('description', 'ilike', '%' . $search . '%')
            ->orWhere('product_code', 'ilike', '%' . $search . '%');
        });
        }

        $items = $query->orderBy('description')->paginate(10)->withQueryString();

        return view('supplies.index', compact('items', 'categories'));
    }

    public function category(Request $request, SupplyCategory $category)
    {
        $categories = SupplyCategory::all();

        $query = SupplyItem::with('category')->where('category_id', $category->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'ilike', '%' . $search . '%')
                ->orWhere('product_code', 'ilike', '%' . $search . '%');
            });
        }

        $items = $query->orderBy('description')->paginate(10)->withQueryString();

        return view('supplies.index', compact('items', 'categories'))
            ->with('activeCategory', $category);
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
            'product_code' => 'required|string|max:100',
            'stock_no' => 'nullable|string|max:100',
            'description' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'unit_value' => 'required|numeric|min:0',
            'balance_per_card' => 'required|integer|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
            'expiration_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:255',
        ]);

        $validated['minimum_stock'] = $validated['minimum_stock'] ?? 0;

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
            'product_code' => 'required|string|max:100',
            'stock_no' => 'nullable|string|max:100',
            'description' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'unit_value' => 'required|numeric|min:0',
            'balance_per_card' => 'required|integer|min:0',
            'on_hand_per_count' => 'nullable|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'expiration_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:255',
        ]);

        $supply->update($validated);

        return redirect()->route('supplies.index')->with('success', 'Supply item updated.');
    }

    public function destroy(SupplyItem $supply)
    {
        $supply->delete();
        return back()->with('success', 'Supply item deleted.');
    }

    public function dismissExpiry(SupplyItem $supply)
    {
        ExpiryDismissal::firstOrCreate([
            'user_id' => Auth::id(),
            'supply_item_id' => $supply->id,
        ]);

        return back();
    }
}