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
use App\Http\Controllers\Auth\NewPasswordController;

// ✅ BASIC ROUTES
Route::get('/', function () {
    return view('auth.login');
});

Route::get('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.store');

Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.reset');

// Search route untuk register
Route::get('/search-account-managers', [RegisteredUserController::class, 'searchAccountManagers'])
    ->middleware('guest')
    ->name('search.account-managers');

// ✅ DASHBOARD ROUTES
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/revenues', [DashboardController::class, 'getRevenuesByYear'])->name('dashboard.revenues');
    // 🆕 FIX: Tambah dashboard account managers route
    Route::get('/dashboard/account-managers', [DashboardController::class, 'getAccountManagers'])->name('dashboard.account-managers');

    // ✅ Revenue data route - admin only
    Route::get('/revenue_data', [RevenueController::class, 'index'])->name('revenue.data')
        ->middleware('admin');
});

// ✅ AUTHENTICATED ROUTES
Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/update-image', [ProfileController::class, 'updateImage'])->name('profile.update-image');

    // ✅ REVENUE ROUTES - Admin only
    Route::prefix('revenue')->name('revenue.')->middleware('admin')->group(function () {
        // Basic CRUD
        Route::get('/', [RevenueController::class, 'index'])->name('index');
        Route::get('/data', [RevenueController::class, 'index'])->name('data.filtered');
        Route::post('/store', [RevenueController::class, 'store'])->name('store');
        Route::post('/', [RevenueController::class, 'store'])->name('store.alt');
        Route::get('/{id}/edit', [RevenueController::class, 'edit'])->name('edit');
        Route::put('/{id}', [RevenueController::class, 'update'])->name('update');
        Route::delete('/{id}', [RevenueController::class, 'destroy'])->name('destroy');

        // Import/Export
        Route::post('/import', [RevenueController::class, 'import'])->name('import');
        Route::get('/export', [RevenueController::class, 'export'])->name('export');
        Route::get('/template', [RevenueController::class, 'downloadTemplate'])->name('template');
        Route::get('/download-template', [RevenueController::class, 'downloadTemplate'])->name('download-template');

        // Search routes
        Route::get('/search', [RevenueController::class, 'search'])->name('search');
        Route::get('/search-account-manager', [RevenueController::class, 'searchAccountManager'])->name('search-account-manager');
        Route::get('/search-corporate-customer', [RevenueController::class, 'searchCorporateCustomer'])->name('search-corporate-customer');

        // Import utility routes
        Route::get('/import-info', [RevenueController::class, 'getImportInfo'])->name('import-info');
        Route::post('/preview-import', [RevenueController::class, 'previewImport'])->name('preview-import');
        Route::get('/stats', [RevenueController::class, 'getStats'])->name('stats');

        // Bulk operations routes
        Route::post('/bulk-delete', [RevenueController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/bulk-delete-preview', [RevenueController::class, 'bulkDeletePreview'])->name('bulk-delete-preview');
        // 🆕 FIX: Tambah bulk delete all dengan filter
        Route::post('/bulk-delete-all', [RevenueController::class, 'bulkDeleteAll'])->name('bulk-delete-all');

        // Monthly stats route
        Route::get('/monthly-stats', [RevenueController::class, 'getMonthlyRevenueStats'])->name('monthly-stats');

        // Utility routes
        Route::get('/account-manager/{id}/divisions', [RevenueController::class, 'getAccountManagerDivisions'])->name('account-manager.divisions');
        Route::get('/statistics', [RevenueController::class, 'getStatistics'])->name('statistics');
    });

    // ✅ ACCOUNT MANAGER ROUTES - Admin only
    Route::prefix('account-manager')->name('account-manager.')->middleware('admin')->group(function () {
        // Basic CRUD
        Route::get('/', [AccountManagerController::class, 'index'])->name('index');
        Route::get('/create', [AccountManagerController::class, 'create'])->name('create');
        Route::post('/store', [AccountManagerController::class, 'store'])->name('store');
        Route::post('/', [AccountManagerController::class, 'store'])->name('store.alt');
        Route::get('/{id}/edit', [AccountManagerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AccountManagerController::class, 'update'])->name('update');
        Route::delete('/{id}', [AccountManagerController::class, 'destroy'])->name('destroy');

        // Import/Export
        Route::post('/import', [AccountManagerController::class, 'import'])->name('import');
        Route::get('/export', [AccountManagerController::class, 'export'])->name('export');
        Route::get('/template', [AccountManagerController::class, 'downloadTemplate'])->name('template');
        Route::get('/download-template', [AccountManagerController::class, 'downloadTemplate'])->name('download-template');

        // Enhanced features
        Route::get('/add-modal', [AccountManagerController::class, 'showAddModal'])->name('add-modal');
        Route::get('/form-data', [AccountManagerController::class, 'getFormData'])->name('form-data');
        Route::post('/bulk-delete', [AccountManagerController::class, 'bulkDelete'])->name('bulk-delete');
        // 🆕 FIX: Tambah bulk delete all untuk Account Manager
        Route::post('/bulk-delete-all', [AccountManagerController::class, 'bulkDeleteAll'])->name('bulk-delete-all');
        Route::post('/validate-nik', [AccountManagerController::class, 'validateNik'])->name('validate-nik');
        Route::get('/statistics', [AccountManagerController::class, 'getStatistics'])->name('statistics');

        // ✅ FIX: Password Management Routes - TAMBAH missing routes
        Route::post('/{id}/change-password', [AccountManagerController::class, 'changePassword'])->name('change-password');
        Route::get('/{id}/user-status', [AccountManagerController::class, 'getUserStatus'])->name('user-status');
        Route::get('/{id}/user-info', [AccountManagerController::class, 'getUserStatus'])->name('user-info');
        Route::get('/{id}/check-user', [AccountManagerController::class, 'checkUserStatus'])->name('check-user');
        Route::delete('/{id}/reset-user', [AccountManagerController::class, 'resetUserAccount'])->name('reset-user');
        Route::post('/bulk-password-reset', [AccountManagerController::class, 'bulkPasswordReset'])->name('bulk-password-reset');

        // Search Routes
        Route::get('/search', [AccountManagerController::class, 'search'])->name('search');
        Route::get('/{id}/divisions', [AccountManagerController::class, 'getDivisions'])->name('divisions');
    });

    // ✅ UNDERSCORE ROUTES for backward compatibility - Account Manager (Admin only)
    Route::prefix('account_manager')->name('account_manager.')->middleware('admin')->group(function () {
        Route::get('/', [AccountManagerController::class, 'index'])->name('index');
        Route::get('/create', [AccountManagerController::class, 'create'])->name('create');
        Route::post('/store', [AccountManagerController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AccountManagerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AccountManagerController::class, 'update'])->name('update');
        Route::delete('/{id}', [AccountManagerController::class, 'destroy'])->name('destroy');

        Route::post('/import', [AccountManagerController::class, 'import'])->name('import');
        Route::get('/export', [AccountManagerController::class, 'export'])->name('export');
        Route::get('/template', [AccountManagerController::class, 'downloadTemplate'])->name('template');
        Route::get('/download-template', [AccountManagerController::class, 'downloadTemplate'])->name('download-template');

        Route::get('/search', [AccountManagerController::class, 'search'])->name('search');
        Route::post('/validate-nik', [AccountManagerController::class, 'validateNik'])->name('validate-nik');
    });

    // ✅ CORPORATE CUSTOMER ROUTES - Admin only
    Route::prefix('corporate-customer')->name('corporate-customer.')->middleware('admin')->group(function () {
        // Basic CRUD
        Route::get('/', [CorporateCustomerController::class, 'index'])->name('index');
        Route::get('/create', [CorporateCustomerController::class, 'create'])->name('create');
        Route::post('/store', [CorporateCustomerController::class, 'store'])->name('store');
        Route::post('/', [CorporateCustomerController::class, 'store'])->name('store.alt');
        Route::get('/{id}/edit', [CorporateCustomerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CorporateCustomerController::class, 'update'])->name('update');
        Route::delete('/{id}', [CorporateCustomerController::class, 'destroy'])->name('destroy');

        // Import/Export
        Route::post('/import', [CorporateCustomerController::class, 'import'])->name('import');
        Route::get('/export', [CorporateCustomerController::class, 'export'])->name('export');
        Route::get('/template', [CorporateCustomerController::class, 'downloadTemplate'])->name('template');
        Route::get('/download-template', [CorporateCustomerController::class, 'downloadTemplate'])->name('download-template');

        // Enhanced features
        Route::get('/search', [CorporateCustomerController::class, 'search'])->name('search');
        Route::get('/statistics', [CorporateCustomerController::class, 'getStatistics'])->name('statistics');
        Route::post('/bulk-delete', [CorporateCustomerController::class, 'bulkDelete'])->name('bulk-delete');
        // 🆕 FIX: Tambah bulk delete all untuk Corporate Customer
        Route::post('/bulk-delete-all', [CorporateCustomerController::class, 'bulkDeleteAll'])->name('bulk-delete-all');
        Route::post('/validate-nipnas', [CorporateCustomerController::class, 'validateNipnas'])->name('validate-nipnas');
    });

    // ✅ Corporate Customer test route (Admin only)
    Route::get('/corporate-customer/test-search', [CorporateCustomerController::class, 'testSearch'])
        ->middleware('admin')
        ->name('corporate-customer.test-search');

    // ✅ UNDERSCORE ROUTES for backward compatibility - Corporate Customer (Admin only)
    Route::prefix('corporate_customer')->name('corporate_customer.')->middleware('admin')->group(function () {
        Route::get('/', [CorporateCustomerController::class, 'index'])->name('index');
        Route::get('/create', [CorporateCustomerController::class, 'create'])->name('create');
        Route::post('/store', [CorporateCustomerController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [CorporateCustomerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CorporateCustomerController::class, 'update'])->name('update');
        Route::delete('/{id}', [CorporateCustomerController::class, 'destroy'])->name('destroy');

        Route::post('/import', [CorporateCustomerController::class, 'import'])->name('import');
        Route::get('/export', [CorporateCustomerController::class, 'export'])->name('export');
        Route::get('/template', [CorporateCustomerController::class, 'downloadTemplate'])->name('template');
        Route::get('/download-template', [CorporateCustomerController::class, 'downloadTemplate'])->name('download-template');

        Route::get('/search', [CorporateCustomerController::class, 'search'])->name('search');
        Route::post('/validate-nipnas', [CorporateCustomerController::class, 'validateNipnas'])->name('validate-nipnas');
    });

    // ✅ API ROUTES
    Route::prefix('api')->name('api.')->group(function () {
        // PUBLIC API: Available for all authenticated users (for dropdowns, etc)
        Route::get('/divisi', [AccountManagerController::class, 'getDivisi'])->name('divisi');
        Route::get('/regional', [AccountManagerController::class, 'getRegional'])->name('regional');
        Route::get('/regionals', [RegionalController::class, 'getRegionals'])->name('regionals');

        // API routes - Admin only
        Route::middleware('admin')->group(function () {
            // 🆕 FIX: Revenue stats route (untuk mengatasi error GET /api/revenue/stats)
            Route::get('/revenue/stats', [RevenueController::class, 'getStats'])->name('revenue.stats');

            // Revenue specific API routes
            Route::get('/revenue/{id}/edit', [RevenueController::class, 'edit'])->name('revenue.edit');
            Route::get('/revenue/{id}/data', [RevenueController::class, 'getRevenueData'])->name('revenue.data');
            Route::put('/revenue/{id}/update', [RevenueController::class, 'updateRevenue'])->name('revenue.update');
            Route::delete('/revenue/{revenue}', [RevenueController::class, 'destroy'])->name('revenue.destroy');
            Route::post('/revenue/bulk-delete', [RevenueController::class, 'bulkDelete'])->name('revenue.bulk-delete');

            // Account Manager specific API routes
            Route::get('/account-manager/{id}/divisi', [RevenueController::class, 'getAccountManagerDivisions'])->name('account-manager.divisi');
            Route::get('/account-manager/{id}/edit', [AccountManagerController::class, 'edit'])->name('account-manager.edit');
            Route::get('/account-manager/{id}/data', [AccountManagerController::class, 'getAccountManagerData'])->name('account-manager.data');
            Route::put('/account-manager/{id}/update', [AccountManagerController::class, 'updateAccountManager'])->name('account-manager.update');
            // 🆕 FIX: TAMBAH missing user status routes di API
            Route::get('/account-manager/{id}/user-status', [AccountManagerController::class, 'getUserStatus'])->name('account-manager.user-status');
            Route::get('/account-manager/{id}/check-user', [AccountManagerController::class, 'checkUserStatus'])->name('account-manager.check-user');

            // Corporate Customer specific API routes
            Route::get('/corporate-customer/{id}/edit', [CorporateCustomerController::class, 'edit'])->name('corporate-customer.edit');
            Route::get('/corporate-customer/{id}/data', [CorporateCustomerController::class, 'getCorporateCustomerData'])->name('corporate-customer.data');
            Route::put('/corporate-customer/{id}/update', [CorporateCustomerController::class, 'updateCorporateCustomer'])->name('corporate-customer.update');
        });
    });

    // ✅ OTHER EXISTING ROUTES - Available for all authenticated users
    Route::get('/profile/show', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/settings', [UserController::class, 'settings'])->name('settings');

    // ✅ Global search - Admin only
    Route::get('/global-search', [RevenueController::class, 'search'])
        ->middleware('admin')
        ->name('global.search');

    // ✅ PUBLIC: Leaderboard routes (available for all users)
    Route::get('/leaderboardAM', [LeaderboardController::class, 'index'])->name('leaderboard');
    Route::get('/witel-perform', [UserController::class, 'witelPerform'])->name('witel.perform');

    // ✅ PUBLIC: Witel Performance routes (available for all users)
    Route::post('/witel-perform/update-charts', [WitelPerformController::class, 'updateCharts'])->name('witel.update-charts');
    Route::post('/witel-perform/filter-by-divisi', [WitelPerformController::class, 'filterByDivisi'])->name('witel.filter-by-divisi');
    Route::post('/witel-perform/filter-by-witel', [WitelPerformController::class, 'filterByWitel'])->name('witel.filter-by-witel');
    Route::post('/witel-perform/filter-by-regional', [WitelPerformController::class, 'filterByRegional'])->name('witel.filter-by-regional');

    // ✅ PUBLIC: Detail routes (available for all users)
    Route::get('/account-manager/{id}', [AccountManagerDetailController::class, 'show'])->name('account_manager.detail');
    Route::get('/witel/{witel_id}/leaderboard', [WitelLeaderboardController::class, 'index'])->name('witel.leaderboard');
    Route::get('/divisi/{divisi_id}/leaderboard', [DivisiLeaderboardController::class, 'index'])->name('divisi.leaderboard');

    // ✅ Regional management routes - Admin only
    Route::prefix('regionals')->name('regional.')->middleware('admin')->group(function () {
        Route::get('/', [RegionalController::class, 'index'])->name('index');
        Route::get('/create', [RegionalController::class, 'create'])->name('create');
        Route::post('/', [RegionalController::class, 'store'])->name('store');
        Route::get('/{regional}/edit', [RegionalController::class, 'edit'])->name('edit');
        Route::put('/{regional}', [RegionalController::class, 'update'])->name('update');
        Route::delete('/{regional}', [RegionalController::class, 'destroy'])->name('destroy');
    });

    // ✅ PUBLIC: Static routes (available for all users)
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