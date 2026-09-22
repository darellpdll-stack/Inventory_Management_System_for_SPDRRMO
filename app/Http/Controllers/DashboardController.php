<?php

namespace App\Http\Controllers;

use App\Models\SupplyItem;
use App\Models\SupplyCategory;
use App\Models\PropertyItem;
use App\Models\SupplyRequest;
use App\Models\WithdrawalItem;

class DashboardController extends Controller
{
    public function index()
    {
        // featured card: total supplies, stock value, breakdown by category
        $totalItems = SupplyItem::count();
        $stockValue = SupplyItem::selectRaw('COALESCE(SUM(balance_per_card * unit_value), 0) as v')->value('v');
        $byCategory = SupplyCategory::withCount('items')->orderBy('name')->get();

        // stat cards
        $totalProperties = PropertyItem::sum('quantity');
        $propertyRecords = PropertyItem::count();
        $lowStockCount   = SupplyItem::lowStock()->count();
        $totalRequests   = SupplyRequest::count();
        $pendingRequests = SupplyRequest::where('status', 'pending')->count();

        // stock status bar — same rules as the supplies list
        $outOfStock = SupplyItem::where('balance_per_card', '<=', 0)->count();
        $low = SupplyItem::where('balance_per_card', '>', 0)
            ->whereColumn('balance_per_card', '<=', 'minimum_stock')->count();
        $ok = SupplyItem::whereColumn('balance_per_card', '>', 'minimum_stock')
            ->where('balance_per_card', '>', 0)->count();

        // activity panels
        $recentWithdrawals = WithdrawalItem::with(['supplyItem.category', 'withdrawal'])
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $recentRequests = SupplyRequest::with(['personnel', 'items.supplyItem'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalItems', 'stockValue', 'byCategory',
            'totalProperties', 'propertyRecords', 'lowStockCount',
            'totalRequests', 'pendingRequests',
            'outOfStock', 'low', 'ok',
            'recentWithdrawals', 'recentRequests'
        ));
    }
}