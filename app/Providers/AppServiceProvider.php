<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\SupplyItem;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
{
    View::composer('layouts.app', function ($view) {
        if (Auth::check()) {
            $dismissedIds = \App\Models\ExpiryDismissal::where('user_id', Auth::id())
                ->pluck('supply_item_id');

            $expiringItems = SupplyItem::needsExpiryAttention()
                ->with('category')
                ->whereNotIn('id', $dismissedIds)
                ->orderBy('expiration_date')
                ->get();

            $lowStockItems = SupplyItem::lowStock()
                ->with('category')
                ->orderBy('current_stock')
                ->get();

            $navCategories = \App\Models\SupplyCategory::orderBy('name')->get();
        } else {
            $expiringItems = collect();
            $lowStockItems = collect();
            $navCategories = collect();
        }

        $view->with('expiringItems', $expiringItems);
        $view->with('lowStockItems', $lowStockItems);
        $view->with('navCategories', $navCategories);
    });
}
}