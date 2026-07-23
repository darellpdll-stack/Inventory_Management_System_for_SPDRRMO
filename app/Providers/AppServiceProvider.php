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
            $expiringItems = SupplyItem::needsExpiryAttention()
                ->with('category')
                ->orderBy('expiration_date')
                ->get();

            $lowStockItems = SupplyItem::lowStock()
                ->with('category')
                ->orderBy('balance_per_card')
                ->get();

            $navCategories = \App\Models\SupplyCategory::orderBy('name')->get();

            $pendingRequestCount = \App\Models\SupplyRequest::where('status', 'pending')->count();
        } else {
            $expiringItems = collect();
            $lowStockItems = collect();
            $navCategories = collect();
            $pendingRequestCount = 0;
        }

        $view->with('expiringItems', $expiringItems);
        $view->with('lowStockItems', $lowStockItems);
        $view->with('navCategories', $navCategories);
        $view->with('pendingRequestCount', $pendingRequestCount);
    });
}
}