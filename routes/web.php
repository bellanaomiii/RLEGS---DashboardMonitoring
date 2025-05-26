<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\AccountManagerController;
use App\Http\Controllers\CorporateCustomerController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountManagerDetailController;
use App\Http\Controllers\WitelLeaderboardController;
use App\Http\Controllers\DivisiLeaderboardController;
use App\Http\Controllers\WitelPerformController;
use App\Http\Controllers\RegionalController;

// ✅ BASIC ROUTES
Route::get('/', function () {
    return view('auth.login');
});

Route::get('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');

// Search route untuk register
Route::get('/search-account-managers', [RegisteredUserController::class, 'searchAccountManagers'])
    ->middleware('guest')
    ->name('search.account-managers');

// ✅ DASHBOARD ROUTES
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/revenues', [DashboardController::class, 'getRevenuesByYear'])->name('dashboard.revenues');
    Route::get('/revenue_data', [RevenueController::class, 'index'])->name('revenue.data');
});

// ✅ AUTHENTICATED ROUTES
Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/update-image', [ProfileController::class, 'updateImage'])->name('profile.update-image');

    // ✅ REVENUE ROUTES - Complete with FIXED search routes
    Route::prefix('revenue')->name('revenue.')->group(function () {
        // Basic CRUD
        Route::get('/', [RevenueController::class, 'index'])->name('index');
        Route::get('/data', [RevenueController::class, 'index'])->name('data.filtered');
        Route::post('/store', [RevenueController::class, 'store'])->name('store');
        Route::post('/', [RevenueController::class, 'store'])->name('store.alt');
        Route::get('/{id}/edit', [RevenueController::class, 'edit'])->name('edit');
        Route::put('/{id}', [RevenueController::class, 'update'])->name('update');
        Route::delete('/{id}', [RevenueController::class, 'destroy'])->name('destroy');

        // ✅ FIXED: Import/Export using controller methods
        Route::post('/import', [RevenueController::class, 'import'])->name('import');
        Route::get('/export', [RevenueController::class, 'export'])->name('export');
        Route::get('/template', [RevenueController::class, 'downloadTemplate'])->name('template');
        Route::get('/download-template', [RevenueController::class, 'downloadTemplate'])->name('download-template');

        // ✅ CRITICAL FIX: Search routes - Changed from POST to GET to match JavaScript AJAX calls
        Route::get('/search', [RevenueController::class, 'search'])->name('search');
        Route::get('/search-account-manager', [RevenueController::class, 'searchAccountManager'])->name('search-account-manager');
        Route::get('/search-corporate-customer', [RevenueController::class, 'searchCorporateCustomer'])->name('search-corporate-customer');

        // ✅ NEW: Additional utility routes from enhanced controller
        Route::get('/import-info', [RevenueController::class, 'getImportInfo'])->name('import-info');
        Route::post('/preview-import', [RevenueController::class, 'previewImport'])->name('preview-import');
        Route::get('/stats', [RevenueController::class, 'getStats'])->name('stats');

        // ✅ ADDED: Missing utility routes for JavaScript integration
        Route::get('/account-manager/{id}/divisions', [RevenueController::class, 'getAccountManagerDivisions'])->name('account-manager.divisions');
        Route::get('/statistics', [RevenueController::class, 'getStatistics'])->name('statistics');
    });

    // ✅ ACCOUNT MANAGER ROUTES - Complete with ALL missing routes added
    Route::prefix('account-manager')->name('account-manager.')->group(function () {
        // Basic CRUD
        Route::get('/', [AccountManagerController::class, 'index'])->name('index');
        Route::get('/create', [AccountManagerController::class, 'create'])->name('create');
        Route::post('/store', [AccountManagerController::class, 'store'])->name('store');
        Route::post('/', [AccountManagerController::class, 'store'])->name('store.alt');
        Route::get('/{id}/edit', [AccountManagerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AccountManagerController::class, 'update'])->name('update');
        Route::delete('/{id}', [AccountManagerController::class, 'destroy'])->name('destroy');

        // ✅ FIXED: Import/Export using controller methods
        Route::post('/import', [AccountManagerController::class, 'import'])->name('import');
        Route::get('/export', [AccountManagerController::class, 'export'])->name('export');
        Route::get('/template', [AccountManagerController::class, 'downloadTemplate'])->name('template');
        Route::get('/download-template', [AccountManagerController::class, 'downloadTemplate'])->name('download-template');

        // ✅ NEW: Enhanced controller methods that were missing routes
        Route::get('/add-modal', [AccountManagerController::class, 'showAddModal'])->name('add-modal');
        Route::get('/form-data', [AccountManagerController::class, 'getFormData'])->name('form-data');
        Route::post('/bulk-delete', [AccountManagerController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/validate-nik', [AccountManagerController::class, 'validateNik'])->name('validate-nik');
        Route::get('/statistics', [AccountManagerController::class, 'getStatistics'])->name('statistics');

        // Search Routes
        Route::get('/search', [AccountManagerController::class, 'search'])->name('search');
        Route::get('/{id}/divisions', [AccountManagerController::class, 'getDivisions'])->name('divisions');
    });

    // ✅ UNDERSCORE ROUTES for backward compatibility - Account Manager
    Route::prefix('account_manager')->name('account_manager.')->group(function () {
        Route::get('/', [AccountManagerController::class, 'index'])->name('index');
        Route::get('/create', [AccountManagerController::class, 'create'])->name('create');
        Route::post('/store', [AccountManagerController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AccountManagerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AccountManagerController::class, 'update'])->name('update');
        Route::delete('/{id}', [AccountManagerController::class, 'destroy'])->name('destroy');

        // ✅ FIXED: Import/Export using controller methods for consistency
        Route::post('/import', [AccountManagerController::class, 'import'])->name('import');
        Route::get('/export', [AccountManagerController::class, 'export'])->name('export');
        Route::get('/template', [AccountManagerController::class, 'downloadTemplate'])->name('template');
        Route::get('/download-template', [AccountManagerController::class, 'downloadTemplate'])->name('download-template');

        Route::get('/search', [AccountManagerController::class, 'search'])->name('search');
        Route::post('/validate-nik', [AccountManagerController::class, 'validateNik'])->name('validate-nik');
    });

    // ✅ CORPORATE CUSTOMER ROUTES - Complete with ALL missing routes added
    Route::prefix('corporate-customer')->name('corporate-customer.')->group(function () {
        // Basic CRUD
        Route::get('/', [CorporateCustomerController::class, 'index'])->name('index');
        Route::get('/create', [CorporateCustomerController::class, 'create'])->name('create');
        Route::post('/store', [CorporateCustomerController::class, 'store'])->name('store');
        Route::post('/', [CorporateCustomerController::class, 'store'])->name('store.alt');
        Route::get('/{id}/edit', [CorporateCustomerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CorporateCustomerController::class, 'update'])->name('update');
        Route::delete('/{id}', [CorporateCustomerController::class, 'destroy'])->name('destroy');

        // ✅ FIXED: Import/Export using controller methods
        Route::post('/import', [CorporateCustomerController::class, 'import'])->name('import');
        Route::get('/export', [CorporateCustomerController::class, 'export'])->name('export');
        Route::get('/template', [CorporateCustomerController::class, 'downloadTemplate'])->name('template');
        Route::get('/download-template', [CorporateCustomerController::class, 'downloadTemplate'])->name('download-template');

        // ✅ NEW: Enhanced controller methods that were missing routes
        Route::get('/search', [CorporateCustomerController::class, 'search'])->name('search');
        Route::get('/statistics', [CorporateCustomerController::class, 'getStatistics'])->name('statistics');
        Route::post('/bulk-delete', [CorporateCustomerController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/validate-nipnas', [CorporateCustomerController::class, 'validateNipnas'])->name('validate-nipnas');
    });

    Route::get('/corporate-customer/test-search', [CorporateCustomerController::class, 'testSearch'])->name('corporate-customer.test-search');

    // ✅ UNDERSCORE ROUTES for backward compatibility - Corporate Customer
    Route::prefix('corporate_customer')->name('corporate_customer.')->group(function () {
        Route::get('/', [CorporateCustomerController::class, 'index'])->name('index');
        Route::get('/create', [CorporateCustomerController::class, 'create'])->name('create');
        Route::post('/store', [CorporateCustomerController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [CorporateCustomerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CorporateCustomerController::class, 'update'])->name('update');
        Route::delete('/{id}', [CorporateCustomerController::class, 'destroy'])->name('destroy');

        // ✅ FIXED: Import/Export using controller methods for consistency
        Route::post('/import', [CorporateCustomerController::class, 'import'])->name('import');
        Route::get('/export', [CorporateCustomerController::class, 'export'])->name('export');
        Route::get('/template', [CorporateCustomerController::class, 'downloadTemplate'])->name('template');
        Route::get('/download-template', [CorporateCustomerController::class, 'downloadTemplate'])->name('download-template');

        Route::get('/search', [CorporateCustomerController::class, 'search'])->name('search');
        Route::post('/validate-nipnas', [CorporateCustomerController::class, 'validateNipnas'])->name('validate-nipnas');
    });

    // ✅ API ROUTES - Complete API endpoints with ALL missing routes added
    Route::prefix('api')->name('api.')->group(function () {
        // Dropdown data
        Route::get('/divisi', [AccountManagerController::class, 'getDivisi'])->name('divisi');
        Route::get('/regional', [AccountManagerController::class, 'getRegional'])->name('regional');
        Route::get('/regionals', [RegionalController::class, 'getRegionals'])->name('regionals');

        // Account Manager specific
        Route::get('/account-manager/{id}/divisi', [RevenueController::class, 'getAccountManagerDivisions'])->name('account-manager.divisi');
        Route::get('/account-manager/{id}/edit', [AccountManagerController::class, 'getAccountManagerData'])->name('account-manager.edit');
        Route::put('/account-manager/{id}/update', [AccountManagerController::class, 'updateAccountManager'])->name('account-manager.update');

        // Corporate Customer specific
        Route::get('/corporate-customer/{id}/edit', [CorporateCustomerController::class, 'getCorporateCustomerData'])->name('corporate-customer.edit');
        Route::put('/corporate-customer/{id}/update', [CorporateCustomerController::class, 'updateCorporateCustomer'])->name('corporate-customer.update');

        // Revenue specific
        Route::get('/revenue/{id}/edit', [RevenueController::class, 'getRevenueData'])->name('revenue.edit');
        Route::put('/revenue/{id}/update', [RevenueController::class, 'updateRevenue'])->name('revenue.update');
    });

    // ✅ OTHER EXISTING ROUTES - Preserved as requested
    Route::get('/profile/show', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/settings', [UserController::class, 'settings'])->name('settings');
    Route::get('/global-search', [RevenueController::class, 'search'])->name('global.search');

    // Leaderboard routes
    Route::get('/leaderboardAM', [LeaderboardController::class, 'index'])->name('leaderboard');
    Route::get('/witel-perform', [UserController::class, 'witelPerform'])->name('witel.perform');

    // Witel Performance routes
    Route::post('/witel-perform/update-charts', [WitelPerformController::class, 'updateCharts'])->name('witel.update-charts');
    Route::post('/witel-perform/filter-by-divisi', [WitelPerformController::class, 'filterByDivisi'])->name('witel.filter-by-divisi');
    Route::post('/witel-perform/filter-by-witel', [WitelPerformController::class, 'filterByWitel'])->name('witel.filter-by-witel');
    Route::post('/witel-perform/filter-by-regional', [WitelPerformController::class, 'filterByRegional'])->name('witel.filter-by-regional');

    // Detail routes
    Route::get('/account-manager/{id}', [AccountManagerDetailController::class, 'show'])->name('account_manager.detail');
    Route::get('/witel/{witel_id}/leaderboard', [WitelLeaderboardController::class, 'index'])->name('witel.leaderboard');
    Route::get('/divisi/{divisi_id}/leaderboard', [DivisiLeaderboardController::class, 'index'])->name('divisi.leaderboard');

    // Regional routes
    Route::get('/regionals', [RegionalController::class, 'index'])->name('regional.index');
    Route::get('/regionals/create', [RegionalController::class, 'create'])->name('regional.create');
    Route::post('/regionals', [RegionalController::class, 'store'])->name('regional.store');
    Route::get('/regionals/{regional}/edit', [RegionalController::class, 'edit'])->name('regional.edit');
    Route::put('/regionals/{regional}', [RegionalController::class, 'update'])->name('regional.update');
    Route::delete('/regionals/{regional}', [RegionalController::class, 'destroy'])->name('regional.destroy');

    // Static routes
    Route::get('/MonitoringLOP', function () {
        return view('MonitoringLOP');
    })->name('monitoring-LOP');

    Route::get('/sidebarpage', function () {
        return view('layouts.sidebar');
    });

    Route::get('/PerformansiWitel', function () {
        return view('witelPerform');
    });
});

require __DIR__.'/auth.php';