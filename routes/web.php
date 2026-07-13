<?php
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\SupplyItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeploymentController;
Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    // Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    // Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/supplies', [SupplyItemController::class, 'index'])->name('supplies.index');
    Route::get('/supplies/create', [SupplyItemController::class, 'create'])->name('supplies.create');
    Route::post('/supplies', [SupplyItemController::class, 'store'])->name('supplies.store');
    Route::get('/supplies/{supply}/edit', [SupplyItemController::class, 'edit'])->name('supplies.edit');
    Route::put('/supplies/{supply}', [SupplyItemController::class, 'update'])->name('supplies.update');
    Route::delete('/supplies/{supply}', [SupplyItemController::class, 'destroy'])->name('supplies.destroy');
    Route::get('/supplies/category/{category}', [SupplyItemController::class, 'category'])->name('supplies.category');
    Route::post('/supplies/{supply}/dismiss', [SupplyItemController::class, 'dismissExpiry'])->name('supplies.dismiss');
    Route::get('/deployments', [DeploymentController::class, 'index'])->name('deployments.index');
    Route::get('/deployments/create', [DeploymentController::class, 'create'])->name('deployments.create');
    Route::post('/deployments', [DeploymentController::class, 'store'])->name('deployments.store');
    Route::get('/deployments/{deployment}', [DeploymentController::class, 'show'])->name('deployments.show');
    Route::get('/personnel', [PersonnelController::class, 'index'])->name('personnel.index');
    Route::get('/personnel/create', [PersonnelController::class, 'create'])->name('personnel.create');
    Route::post('/personnel', [PersonnelController::class, 'store'])->name('personnel.store');
    Route::get('/personnel/{person}', [PersonnelController::class, 'show'])->name('personnel.show');
    Route::get('/personnel/{person}/edit', [PersonnelController::class, 'edit'])->name('personnel.edit');
    Route::put('/personnel/{person}', [PersonnelController::class, 'update'])->name('personnel.update');
    Route::delete('/personnel/{person}', [PersonnelController::class, 'destroy'])->name('personnel.destroy');
});