<?php
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\SupplyItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PropertyItemController;

Route::get('/', fn () => redirect()->route('login'));

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

    // Supplies
    Route::get('/supplies', [SupplyItemController::class, 'index'])->name('supplies.index');
    Route::get('/supplies/create', [SupplyItemController::class, 'create'])->name('supplies.create');
    Route::post('/supplies', [SupplyItemController::class, 'store'])->name('supplies.store');
    Route::get('/supplies/category/{category}', [SupplyItemController::class, 'category'])->name('supplies.category');
    Route::get('/supplies/{supply}/edit', [SupplyItemController::class, 'edit'])->name('supplies.edit');
    Route::put('/supplies/{supply}', [SupplyItemController::class, 'update'])->name('supplies.update');
    Route::delete('/supplies/{supply}', [SupplyItemController::class, 'destroy'])->name('supplies.destroy');

    // Withdrawals
    Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::get('/withdrawals/create', [WithdrawalController::class, 'create'])->name('withdrawals.create');
    Route::post('/withdrawals', [WithdrawalController::class, 'store'])->name('withdrawals.store');
    Route::get('/withdrawals/{withdrawal}', [WithdrawalController::class, 'show'])->name('withdrawals.show');

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
    });
});