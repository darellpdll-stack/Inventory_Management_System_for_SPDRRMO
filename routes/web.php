<?php
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\SupplyItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReleaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PropertyItemController;
use App\Http\Controllers\SupplyRequestController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockEntryController;

Route::get('/', fn () => redirect()->route('login'));

// Public supply request (QR code destination) — disabled for now, not in use
// Route::get('/qr_code/request', [SupplyRequestController::class, 'create'])->name('requests.create');
// Route::post('/qr_code/request', [SupplyRequestController::class, 'store'])->name('requests.store');
// Route::get('/qr_code/submitted', [SupplyRequestController::class, 'submitted'])->name('requests.submitted');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Users (admin-managed)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Supplies — specific paths first, numeric wildcards last
    Route::get('/supplies', [SupplyItemController::class, 'index'])->name('supplies.index');
    Route::get('/supplies/create', [SupplyItemController::class, 'create'])->name('supplies.create');
    Route::post('/supplies', [SupplyItemController::class, 'store'])->name('supplies.store');
    Route::get('/supplies/category/{category}', [SupplyItemController::class, 'category'])->name('supplies.category');
    Route::get('/supplies/report/options', [SupplyItemController::class, 'reportOptions'])->name('supplies.report.options');
    Route::get('/supplies/report/generate', [SupplyItemController::class, 'report'])->name('supplies.report');
    Route::get('/supplies/{supply}', [SupplyItemController::class, 'show'])->whereNumber('supply')->name('supplies.show');
    Route::get('/supplies/{supply}/edit', [SupplyItemController::class, 'edit'])->name('supplies.edit');
    Route::put('/supplies/{supply}', [SupplyItemController::class, 'update'])->name('supplies.update');
    Route::delete('/supplies/{supply}', [SupplyItemController::class, 'destroy'])->name('supplies.destroy');

    // Stock-in (receiving)
    Route::get('/supplies/{supply}/stock', [StockEntryController::class, 'create'])->name('stock.create');
    Route::post('/supplies/{supply}/stock', [StockEntryController::class, 'store'])->name('stock.store');

    // Supply requests — any logged-in user can view and add
    Route::get('/requests', [SupplyRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [SupplyRequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [SupplyRequestController::class, 'store'])->name('requests.store');
    Route::get('/requests/{supplyRequest}', [SupplyRequestController::class, 'show'])
        ->whereNumber('supplyRequest')
        ->name('requests.show');

    // Withdrawals — the record of supplies released through requests
    Route::get('/withdrawals', [ReleaseController::class, 'index'])->name('withdrawals.index');
    Route::get('/withdrawals/{release}/receipt', [ReleaseController::class, 'receipt'])->name('withdrawals.receipt');

    // Personnel — all logged-in users can view and add
    Route::get('/personnel', [PersonnelController::class, 'index'])->name('personnel.index');
    Route::get('/personnel/create', [PersonnelController::class, 'create'])->name('personnel.create');
    Route::post('/personnel', [PersonnelController::class, 'store'])->name('personnel.store');
    Route::get('/personnel/{person}', [PersonnelController::class, 'show'])->name('personnel.show');

    // Property — report routes FIRST (before {property} wildcard), viewable by all
    Route::get('/property', [PropertyItemController::class, 'index'])->name('property.index');
    Route::get('/property/create', [PropertyItemController::class, 'create'])->name('property.create');
    Route::post('/property', [PropertyItemController::class, 'store'])->name('property.store');
    Route::get('/property/report/options', [PropertyItemController::class, 'reportOptions'])->name('property.report.options');
    Route::get('/property/report/generate', [PropertyItemController::class, 'report'])->name('property.report');

    // Reports hub
    Route::view('/reports', 'reports.index')->name('reports.index');

    // Admin-only routes
    Route::middleware('admin')->group(function () {
        // Personnel edit/delete
        Route::get('/personnel/{person}/edit', [PersonnelController::class, 'edit'])->name('personnel.edit');
        Route::put('/personnel/{person}', [PersonnelController::class, 'update'])->name('personnel.update');
        Route::delete('/personnel/{person}', [PersonnelController::class, 'destroy'])->name('personnel.destroy');

        // Property edit/delete
        Route::get('/property/{property}/edit', [PropertyItemController::class, 'edit'])->name('property.edit');
        Route::put('/property/{property}', [PropertyItemController::class, 'update'])->name('property.update');
        Route::delete('/property/{property}', [PropertyItemController::class, 'destroy'])->name('property.destroy');

        // Report settings
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

        // Supply requests — reviewing and releasing stay admin-only
        Route::get('/requests/qr', [SupplyRequestController::class, 'qrCode'])->name('requests.qr');
        Route::post('/requests/{supplyRequest}/approve', [SupplyRequestController::class, 'approve'])->name('requests.approve');
        Route::post('/requests/{supplyRequest}/for-release', [SupplyRequestController::class, 'markForRelease'])->name('requests.forRelease');
        Route::post('/requests/{supplyRequest}/decline', [SupplyRequestController::class, 'decline'])->name('requests.decline');
        Route::post('/requests/{supplyRequest}/release', [SupplyRequestController::class, 'release'])->name('requests.release');
    });
});