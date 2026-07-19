<?php

namespace App\Http\Controllers;

use App\Models\SupplyItem;
use App\Models\SupplyCategory;
use App\Models\Withdrawal;
use App\Models\WithdrawalItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // summary cards
        $totalItems = SupplyItem::count();
        $lowStockCount = SupplyItem::lowStock()->count();
        $expiringCount = SupplyItem::needsExpiryAttention()->count();
        $totalWithdrawals = Withdrawal::count();

        // items per category
        $byCategory = SupplyCategory::withCount('items')->orderBy('name')->get();
        $categoryLabels = $byCategory->pluck('name');
        $categoryCounts = $byCategory->pluck('items_count');

        // stock status breakdown
        $outOfStock = SupplyItem::where('balance_per_card', '=', 0)->count();
        $low = SupplyItem::where('balance_per_card', '>', 0)
            ->whereColumn('balance_per_card', '<=', 'minimum_stock')->count();
        $ok = SupplyItem::whereColumn('balance_per_card', '>', 'minimum_stock')->count();

        // withdrawals over the last 6 months
        $trend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $count = Withdrawal::whereYear('date_withdrawn', $month->year)
                ->whereMonth('date_withdrawn', $month->month)
                ->count();
            $trend->push(['label' => $month->format('M Y'), 'count' => $count]);
        }

        // most withdrawn items
        $topItems = WithdrawalItem::select('supply_item_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('supply_item_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('supplyItem')
            ->get();

        return view('dashboard', compact(
            'totalItems', 'lowStockCount', 'expiringCount', 'totalWithdrawals',
            'categoryLabels', 'categoryCounts',
            'outOfStock', 'low', 'ok',
            'trend', 'topItems'
        ));
    }
}