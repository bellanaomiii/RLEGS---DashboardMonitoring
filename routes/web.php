<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\AccountManagerController;
use App\Http\Controllers\CorporateCustomerController;
use App\Http\Controllers\AccountManagerExcelController;
use App\Http\Controllers\CorporateCustomerExcelController;
use App\Http\Controllers\RevenueExcelController;
use App\Http\Controllers\LeaderboardController;

Route::get('/', function () {
    return view('auth.login');
});

// Menghapus route dashboard yang duplikat
Route::middleware(['auth', 'verified'])->group(function () {
    // Menampilkan dashboard dengan data Witel dan Divisi
    Route::get('/dashboard', [RevenueController::class, 'index'])->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Revenue routes
    Route::post('/revenue/store', [RevenueController::class, 'store'])->name('revenue.store');
    Route::get('/revenue/{id}/edit', [RevenueController::class, 'edit'])->name('revenue.edit');
    Route::put('/revenue/{id}', [RevenueController::class, 'update'])->name('revenue.update');
    Route::delete('/revenue/{id}', [RevenueController::class, 'destroy'])->name('revenue.destroy');

    // Revenue Excel routes
    Route::post('/revenue/import', [RevenueExcelController::class, 'import'])->name('revenue.import');
    Route::get('/revenue/template', [RevenueExcelController::class, 'downloadTemplate'])->name('revenue.template');

    // Search routes
    Route::get('/search-am', [RevenueController::class, 'searchAccountManager'])->name('revenue.searchAccountManager');
    Route::get('/search-customer', [RevenueController::class, 'searchCorporateCustomer'])->name('revenue.searchCorporateCustomer');

    // Account Manager routes
    Route::get('/account-manager', [AccountManagerController::class, 'index'])->name('account_manager.index');
    Route::get('/account-manager/create', [AccountManagerController::class, 'create'])->name('account_manager.create');
    Route::post('/account-manager/store', [AccountManagerController::class, 'store'])->name('account_manager.store');
    Route::get('/account-manager/{id}/edit', [AccountManagerController::class, 'edit'])->name('account_manager.edit');
    Route::put('/account-manager/{id}', [AccountManagerController::class, 'update'])->name('account_manager.update');
    Route::delete('/account-manager/{id}', [AccountManagerController::class, 'destroy'])->name('account_manager.destroy');

    // Account Manager Excel routes
    Route::post('/account-manager/import', [AccountManagerExcelController::class, 'import'])->name('account_manager.import');
    Route::get('/account-manager/template', [AccountManagerExcelController::class, 'downloadTemplate'])->name('account_manager.template');

    // Corporate Customer routes
    Route::get('/corporate-customer', [CorporateCustomerController::class, 'index'])->name('corporate_customer.index');
    Route::get('/corporate-customer/create', [CorporateCustomerController::class, 'create'])->name('corporate_customer.create');
    Route::post('/corporate-customer/store', [CorporateCustomerController::class, 'store'])->name('corporate_customer.store');
    Route::get('/corporate-customer/{id}/edit', [CorporateCustomerController::class, 'edit'])->name('corporate_customer.edit');
    Route::put('/corporate-customer/{id}', [CorporateCustomerController::class, 'update'])->name('corporate_customer.update');
    Route::delete('/corporate-customer/{id}', [CorporateCustomerController::class, 'destroy'])->name('corporate_customer.destroy');

    // Corporate Customer Excel routes
    Route::post('/corporate-customer/import', [CorporateCustomerExcelController::class, 'import'])->name('corporate_customer.import');
    Route::get('/corporate-customer/template', [CorporateCustomerExcelController::class, 'downloadTemplate'])->name('corporate_customer.template');

    Route::get('/leaderboardAM', [LeaderboardController::class, 'index'])->name('leaderboard');

    // Sidebar dan leaderboard routes
    Route::get('/sidebarpage', function () {
        return view('layouts.sidebar');
    });

    Route::get('/PerformansiWitel', function () {
        return view('witelPerform');
    });
});

require __DIR__.'/auth.php';
