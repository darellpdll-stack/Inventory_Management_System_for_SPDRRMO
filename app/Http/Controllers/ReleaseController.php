<?php

namespace App\Http\Controllers;

use App\Models\Release;
use App\Models\SupplyCategory;
use Illuminate\Http\Request;

class ReleaseController extends Controller
{
    public function index(Request $request)
    {
        $categories = SupplyCategory::orderBy('name')->get();

        $query = Release::with([
            'items.supplyItem.category',
            'request.personnel',
            'releasedBy',
        ]);

        // filter by item category
        if ($request->filled('category')) {
            $catId = $request->category;
            $query->whereHas('items.supplyItem', fn ($q) => $q->where('category_id', $catId));
        }

        // search by person, item, or request number
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('received_by', 'ilike', "%{$search}%")
                  ->orWhereHas('request.personnel', fn ($p) => $p->where('name', 'ilike', "%{$search}%"))
                  ->orWhereHas('items.supplyItem', fn ($i) => $i->where('description', 'ilike', "%{$search}%"));

                if (preg_match('/(\d+)$/', $search, $m)) {
                    $q->orWhere('supply_request_id', (int) $m[1]);
                }
            });
        }

        $releases = $query->orderByDesc('date_released')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('withdrawals.index', compact('releases', 'categories'));
    }

    public function receipt(Release $release)
    {
        $release->load('items.supplyItem', 'request.personnel', 'releasedBy');
        return view('withdrawals.receipt', compact('release'));
    }
}