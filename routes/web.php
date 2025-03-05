<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\AccountManagerController;
use App\Http\Controllers\CorporateCustomerController;
use App\Http\Controllers\AccountManagerExcelController;





Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Menampilkan dashboard dengan data Witel dan Divisi
    Route::get('/dashboard', [AccountManagerController::class, 'create'])->name('dashboard');
    Route::get('/dashboard', [RevenueController::class, 'index'])->name('dashboard');

    // Route untuk menyimpan data revenue
    Route::post('/revenue/store', [RevenueController::class, 'store'])->name('revenue.store');
    // Add these to handle the search functionality
    Route::get('/search-am', [RevenueController::class, 'searchAccountManager'])->name('revenue.searchAccountManager');
    Route::get('/search-customer', [RevenueController::class, 'searchCorporateCustomer'])->name('revenue.searchCorporateCustomer');

    // Menyimpan Account Manager baru
    Route::post('/account-manager/store', [AccountManagerController::class, 'store'])->name('account_manager.store');
    Route::post('/revenue', [RevenueController::class, 'store'])->name('revenue.store');
    Route::get('/search-am', [RevenueController::class, 'searchAccountManager'])->name('revenue.searchAccountManager');
    Route::get('/search-customer', [RevenueController::class, 'searchCorporateCustomer'])->name(name: 'revenue.searchCorporateCustomer');
    Route::get('account-manager/create', [AccountManagerController::class, 'create'])->name('account_manager.create');
    Route::post('account-manager/store', [AccountManagerController::class, 'store'])->name('account_manager.store');

    Route::get('/account-manager/create', [AccountManagerController::class, 'create'])->name('account_manager.create');
    Route::post('/account-manager', [AccountManagerController::class, 'store'])->name('account_manager.store');


    Route::post('/account-manager/import', [AccountManagerExcelController::class, 'import'])->name('account_manager.import');


    Route::get('corporate-customer/create', [CorporateCustomerController::class, 'create'])->name('corporate_customer.create');
    Route::post('corporate-customer/store', [CorporateCustomerController::class, 'store'])->name('corporate_customer.store');



    Route::get('/search-am', [RevenueController::class, 'searchAccountManager']);
    Route::get('/search-customer', [RevenueController::class, 'searchCorporateCustomer']);


    Route::get('/sidebarpage', function () {
        return view('layouts.sidebar');
    });

    Route::get('/leaderboardAM', function () { return view('leaderboardAM');
    });

    Route::get('/PerformansiWitel', function () { return view('witelPerform');
    });

});

require __DIR__.'/auth.php';
