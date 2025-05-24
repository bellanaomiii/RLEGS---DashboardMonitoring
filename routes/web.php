<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\AccountManagerController;
use App\Http\Controllers\CorporateCustomerController;
use App\Http\Controllers\AccountManagerExcelController;
use App\Http\Controllers\CorporateCustomerExcelController;
use App\Http\Controllers\RevenueExcelController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountManagerDetailController;
use App\Http\Controllers\WitelLeaderboardController;
use App\Http\Controllers\DivisiLeaderboardController;
use App\Http\Controllers\WitelPerformController;
use App\Http\Controllers\RegionalController;


Route::get('/', function () {
    return view('auth.login');
});

Route::get('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');

// Tambahan route search untuk register
Route::get('/search-account-managers', [RegisteredUserController::class, 'searchAccountManagers'])
    ->middleware('guest')
    ->name('search.account-managers');

// Menampilkan dashboard dengan data Witel dan Divisi
Route::middleware(['auth', 'verified'])->group(function () {
    // Route untuk dashboard utama
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Route baru untuk AJAX filter tahun
    Route::get('/dashboard/revenues', [DashboardController::class, 'getRevenuesByYear'])->name('dashboard.revenues');

    // Data Revenue - authorization dilakukan di controller
    Route::get('/revenue_data', [RevenueController::class, 'showRevenueData'])
        ->name('revenue.data');

    // Route untuk export Revenue
    Route::get('/revenue/export', [RevenueExcelController::class, 'export'])->name('revenue.export');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/update-image', [ProfileController::class, 'updateImage'])->name('profile.update-image');

    // Revenue routes - authorization dilakukan di controller
    Route::post('/revenue/store', [RevenueController::class, 'store'])->name('revenue.store');
    Route::get('/revenue/{id}/edit', [RevenueController::class, 'edit'])->name('revenue.edit');
    Route::put('/revenue/{id}', [RevenueController::class, 'update'])->name('revenue.update');
    Route::delete('/revenue/{id}', [RevenueController::class, 'destroy'])->name('revenue.destroy');
    // Tambahkan route ini
    Route::get('/revenue', [RevenueController::class, 'index'])->name('revenue.index');
    Route::get('/profile/show', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/settings', [UserController::class, 'settings'])->name('settings');

    // ✅ UPDATED: Revenue Excel routes - menggunakan RevenueController langsung
    Route::post('/revenue/import', [RevenueController::class, 'import'])->name('revenue.import');
    Route::get('/revenue/export', [RevenueController::class, 'export'])->name('revenue.export');
    Route::get('/revenue/template', [RevenueController::class, 'template'])->name('revenue.template');

    // ✅ LEGACY: Keep existing routes for backward compatibility (jika masih digunakan)
    Route::post('/revenue/import-legacy', [RevenueExcelController::class, 'import'])->name('revenue.import.legacy');
    Route::get('/revenue/template-legacy', [RevenueExcelController::class, 'downloadTemplate'])->name('revenue.template.legacy');

    // Check status import
    Route::get('/revenue/import-status', [RevenueExcelController::class, 'checkImportStatus'])->name('revenue.import.status');

    // Search routes - available for all authenticated users
    Route::get('/search-am', [RevenueController::class, 'searchAccountManager'])->name('revenue.searchAccountManager');
    Route::get('/search-customer', [RevenueController::class, 'searchCorporateCustomer'])->name('revenue.searchCorporateCustomer');

    // Global search route
    Route::get('/global-search', [RevenueController::class, 'search'])->name('revenue.search');

    // Account Manager routes - authorization dilakukan di controller
    Route::get('/account-manager', [AccountManagerController::class, 'index'])->name('account_manager.index');
    Route::get('/account-manager/create', [AccountManagerController::class, 'create'])->name('account_manager.create');
    Route::post('/account-manager/store', [AccountManagerController::class, 'store'])->name('account_manager.store');
    Route::get('/account-manager/{id}/edit', [AccountManagerController::class, 'edit'])->name('account_manager.edit');
    Route::put('/account-manager/{id}', [AccountManagerController::class, 'update'])->name('account_manager.update');
    Route::delete('/account-manager/{id}', [AccountManagerController::class, 'destroy'])->name('account_manager.destroy');

    // ✅ UPDATED: Account Manager Excel routes - menggunakan AccountManagerController langsung
    Route::post('/account-manager/import', [AccountManagerController::class, 'import'])->name('account_manager.import');
    Route::get('/account-manager/export', [AccountManagerController::class, 'export'])->name('account_manager.export');
    Route::get('/account-manager/template', [AccountManagerController::class, 'template'])->name('account_manager.template');

    // ✅ LEGACY: Keep existing routes for backward compatibility (jika masih digunakan)
    Route::post('/account-manager/import-legacy', [AccountManagerExcelController::class, 'import'])->name('account_manager.import.legacy');
    Route::get('/account-manager/template-legacy', [AccountManagerExcelController::class, 'downloadTemplate'])->name('account_manager.template.legacy');

    // Divisi untuk Account Manager
    Route::get('/api/account-manager/{id}/divisi', [RevenueController::class, 'getAccountManagerDivisions'])->name('api.account-manager.divisi');

    // API Routes untuk Edit via AJAX - PERBAIKAN UTAMA DISINI
    Route::get('/api/account-manager/{id}/edit', [AccountManagerController::class, 'getAccountManagerData']);
    // Ubah dari POST ke PUT untuk update
    Route::put('/api/account-manager/{id}/update', [AccountManagerController::class, 'updateAccountManager']);

    Route::get('/api/corporate-customer/{id}/edit', [CorporateCustomerController::class, 'getCorporateCustomerData']);
    // Ubah dari POST ke PUT untuk update
    Route::put('/api/corporate-customer/{id}/update', [CorporateCustomerController::class, 'updateCorporateCustomer']);

    Route::get('/api/revenue/{id}/edit', [RevenueController::class, 'getRevenueData']);
    // Ubah dari POST ke PUT untuk update
    Route::put('/api/revenue/{id}/update', [RevenueController::class, 'updateRevenue']);

    // Corporate Customer routes
    Route::get('/corporate-customer', [CorporateCustomerController::class, 'index'])->name('corporate_customer.index');
    Route::get('/corporate-customer/create', [CorporateCustomerController::class, 'create'])->name('corporate_customer.create');
    Route::post('/corporate-customer/store', [CorporateCustomerController::class, 'store'])->name('corporate_customer.store');
    Route::get('/corporate-customer/{id}/edit', [CorporateCustomerController::class, 'edit'])->name('corporate_customer.edit');
    Route::put('/corporate-customer/{id}', [CorporateCustomerController::class, 'update'])->name('corporate_customer.update');
    Route::delete('/corporate-customer/{id}', [CorporateCustomerController::class, 'destroy'])->name('corporate_customer.destroy');

    // ✅ UPDATED: Corporate Customer Excel routes - menggunakan CorporateCustomerController langsung
    Route::post('/corporate-customer/import', [CorporateCustomerController::class, 'import'])->name('corporate_customer.import');
    Route::get('/corporate-customer/export', [CorporateCustomerController::class, 'export'])->name('corporate_customer.export');
    Route::get('/corporate-customer/template', [CorporateCustomerController::class, 'template'])->name('corporate_customer.template');

    // ✅ LEGACY: Keep existing routes for backward compatibility (jika masih digunakan)
    Route::post('/corporate-customer/import-legacy', [CorporateCustomerExcelController::class, 'import'])->name('corporate_customer.import.legacy');
    Route::get('/corporate-customer/template-legacy', [CorporateCustomerExcelController::class, 'downloadTemplate'])->name('corporate_customer.template.legacy');

    // ✅ NEW: Search routes untuk Account Manager dan Corporate Customer
    Route::get('/account-manager/search', [AccountManagerController::class, 'search'])->name('account_manager.search');
    Route::get('/corporate-customer/search', [CorporateCustomerController::class, 'search'])->name('corporate_customer.search');

    // Leaderboard dan Witel Performance - dapat diakses semua user
    Route::get('/leaderboardAM', [LeaderboardController::class, 'index'])->name('leaderboard');
    Route::get('/witel-perform', [UserController::class, 'witelPerform'])->name('witel.perform');

    // Witel Performance routes diperbaiki
    Route::post('/witel-perform/update-charts', [WitelPerformController::class, 'updateCharts'])->name('witel.update-charts');
    Route::post('/witel-perform/filter-by-divisi', [WitelPerformController::class, 'filterByDivisi'])->name('witel.filter-by-divisi');
    Route::post('/witel-perform/filter-by-witel', [WitelPerformController::class, 'filterByWitel'])->name('witel.filter-by-witel');
    Route::post('/witel-perform/filter-by-regional', [WitelPerformController::class, 'filterByRegional'])->name('witel.filter-by-regional');

    // Account Manager detail
    Route::get('/account-manager/{id}', [AccountManagerDetailController::class, 'show'])->name('account_manager.detail');
    Route::get('/witel/{witel_id}/leaderboard', [WitelLeaderboardController::class, 'index'])->name('witel.leaderboard');
    Route::get('/divisi/{divisi_id}/leaderboard', [DivisiLeaderboardController::class, 'index'])->name('divisi.leaderboard');

    // Routes untuk Regional
    Route::get('/regionals', [RegionalController::class, 'index'])->name('regional.index');
    Route::get('/regionals/create', [RegionalController::class, 'create'])->name('regional.create');
    Route::post('/regionals', [RegionalController::class, 'store'])->name('regional.store');
    Route::get('/regionals/{regional}/edit', [RegionalController::class, 'edit'])->name('regional.edit');
    Route::put('/regionals/{regional}', [RegionalController::class, 'update'])->name('regional.update');
    Route::delete('/regionals/{regional}', [RegionalController::class, 'destroy'])->name('regional.destroy');

    // API endpoint untuk mengambil data Regional (jika diperlukan)
    Route::get('/api/regionals', [RegionalController::class, 'getRegionals'])->name('api.regionals');

    // ✅ NEW: API endpoints untuk mendapatkan data dropdown
    Route::get('/api/divisi', [AccountManagerController::class, 'getDivisi'])->name('api.divisi');
    Route::get('/api/regional', [AccountManagerController::class, 'getRegional'])->name('api.regional');

    // Performansi Witel route
    Route::get('/MonitoringLOP', function () {
        return view('MonitoringLOP');
    })->name('monitoring-LOP');

    // Sidebar route
    Route::get('/sidebarpage', function () {
        return view('layouts.sidebar');
    });

    Route::get('/PerformansiWitel', function () {
        return view('witelPerform');
    });
});

require __DIR__.'/auth.php';