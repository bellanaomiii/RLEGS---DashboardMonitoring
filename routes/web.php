<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
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

// ✅ ENHANCED: Import/Export Classes with proper imports
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RevenueImport;
use App\Imports\AccountManagerImport;
use App\Imports\CorporateCustomerImport;
use App\Exports\RevenueExport;
use App\Exports\RevenueTemplateExport;
use App\Exports\AccountManagerExport;
use App\Exports\AccountManagerTemplateExport;
use App\Exports\CorporateCustomerExport;
use App\Exports\CorporateCustomerTemplateExport;

/**
 * ✅ HELPER FUNCTIONS: Message generators for import results
 */
function generateRevenueImportMessage($results) {
    $messages = [];

    if ($results['imported'] > 0) {
        $messages[] = "{$results['imported']} revenue records berhasil ditambahkan";
    }

    if ($results['updated'] > 0) {
        $messages[] = "{$results['updated']} revenue records berhasil diperbarui";
    }

    if ($results['duplicates'] > 0) {
        $messages[] = "{$results['duplicates']} data duplikat dilewati";
    }

    if ($results['errors'] > 0) {
        $messages[] = "{$results['errors']} baris gagal diproses";
    }

    if (empty($messages)) {
        return 'Tidak ada data yang diproses.';
    }

    $result = implode(', ', $messages) . '.';
    $year = $results['year'] ?? date('Y');

    if ($results['errors'] === 0) {
        return "Import Revenue {$year} berhasil! {$result}";
    } elseif ($results['imported'] > 0 || $results['updated'] > 0) {
        return "Import Revenue {$year} selesai dengan beberapa error. {$result}";
    } else {
        return "Import Revenue {$year} gagal. {$result}";
    }
}

function generateAccountManagerImportMessage($results) {
    $messages = [];

    if ($results['imported'] > 0) {
        $messages[] = "{$results['imported']} Account Manager baru berhasil ditambahkan";
    }

    if ($results['updated'] > 0) {
        $messages[] = "{$results['updated']} Account Manager berhasil diperbarui";
    }

    if ($results['duplicates'] > 0) {
        $messages[] = "{$results['duplicates']} data duplikat dilewati";
    }

    if ($results['errors'] > 0) {
        $messages[] = "{$results['errors']} baris gagal diproses";
    }

    if (empty($messages)) {
        return 'Tidak ada data yang diproses.';
    }

    $result = implode(', ', $messages) . '.';

    if ($results['errors'] === 0) {
        return "Import Account Manager berhasil! {$result}";
    } elseif ($results['imported'] > 0 || $results['updated'] > 0) {
        return "Import Account Manager selesai dengan beberapa error. {$result}";
    } else {
        return "Import Account Manager gagal. {$result}";
    }
}

function generateCorporateCustomerImportMessage($results) {
    $messages = [];

    if ($results['imported'] > 0) {
        $messages[] = "{$results['imported']} Corporate Customer baru berhasil ditambahkan";
    }

    if ($results['updated'] > 0) {
        $messages[] = "{$results['updated']} Corporate Customer berhasil diperbarui";
    }

    if ($results['duplicates'] > 0) {
        $messages[] = "{$results['duplicates']} data duplikat dilewati";
    }

    if ($results['errors'] > 0) {
        $messages[] = "{$results['errors']} baris gagal diproses";
    }

    if (empty($messages)) {
        return 'Tidak ada data yang diproses.';
    }

    $result = implode(', ', $messages) . '.';

    if ($results['errors'] === 0) {
        return "Import Corporate Customer berhasil! {$result}";
    } elseif ($results['imported'] > 0 || $results['updated'] > 0) {
        return "Import Corporate Customer selesai dengan beberapa error. {$result}";
    } else {
        return "Import Corporate Customer gagal. {$result}";
    }
}

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

    // ✅ REVENUE ROUTES - Complete with enhanced import/export
    Route::prefix('revenue')->name('revenue.')->group(function () {
        // Basic CRUD
        Route::get('/', [RevenueController::class, 'index'])->name('index');
        Route::get('/data', [RevenueController::class, 'index'])->name('data.filtered');
        Route::post('/store', [RevenueController::class, 'store'])->name('store');
        Route::post('/', [RevenueController::class, 'store'])->name('store.alt');
        Route::get('/{id}/edit', [RevenueController::class, 'edit'])->name('edit');
        Route::put('/{id}', [RevenueController::class, 'update'])->name('update');
        Route::delete('/{id}', [RevenueController::class, 'destroy'])->name('destroy');

        // ✅ ENHANCED: Import Route with detailed tracking and year parameter
        Route::post('/import', function(Request $request) {
            try {
                $validator = Validator::make($request->all(), [
                    'file' => 'required|mimes:xlsx,xls,csv|max:10240',
                    'year' => 'nullable|integer|min:2020|max:2030'
                ], [
                    'file.required' => 'File Excel wajib diupload.',
                    'file.mimes' => 'File harus berformat Excel (.xlsx, .xls) atau CSV.',
                    'file.max' => 'Ukuran file maksimal 10MB.',
                    'year.integer' => 'Tahun harus berupa angka.',
                    'year.min' => 'Tahun minimal 2020.',
                    'year.max' => 'Tahun maksimal 2030.'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => $validator->errors()->first(),
                        'data' => [
                            'imported' => 0,
                            'updated' => 0,
                            'duplicates' => 0,
                            'errors' => 1,
                            'error_details' => [$validator->errors()->first()]
                        ]
                    ], 422);
                }

                $year = $request->input('year', date('Y'));
                $import = new RevenueImport($year);
                Excel::import($import, $request->file('file'));
                $results = $import->getImportResults();

                $message = generateRevenueImportMessage($results);

                Log::info('Revenue Import completed', [
                    'file_name' => $request->file('file')->getClientOriginalName(),
                    'year' => $year,
                    'results' => $results['summary']
                ]);

                return response()->json([
                    'success' => $results['errors'] == 0 || ($results['imported'] + $results['updated']) > 0,
                    'message' => $message,
                    'data' => $results
                ]);

            } catch (\Exception $e) {
                Log::error('Revenue Import Error: ' . $e->getMessage(), [
                    'file_name' => $request->file('file')?->getClientOriginalName(),
                    'year' => $request->input('year'),
                    'exception' => $e
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memproses file: ' . $e->getMessage(),
                    'data' => [
                        'imported' => 0,
                        'updated' => 0,
                        'duplicates' => 0,
                        'errors' => 1,
                        'error_details' => ['Error sistem: ' . $e->getMessage()]
                    ]
                ], 500);
            }
        })->name('import');

        // Export routes
        Route::get('/export', function(Request $request) {
            return Excel::download(new RevenueExport(), 'revenue_export_' . date('Y-m-d_H-i-s') . '.xlsx');
        })->name('export');

        Route::get('/template', function() {
            return Excel::download(new RevenueTemplateExport(), 'template_revenue_' . date('Y-m-d') . '.xlsx');
        })->name('template');

        // Search Routes
        Route::get('/search', [RevenueController::class, 'search'])->name('search');
        Route::get('/search-account-manager', [RevenueController::class, 'searchAccountManager'])->name('search-account-manager');
        Route::get('/search-corporate-customer', [RevenueController::class, 'searchCorporateCustomer'])->name('search-corporate-customer');

        // Utility Routes
        Route::get('/account-manager/{id}/divisions', [RevenueController::class, 'getAccountManagerDivisions'])->name('account-manager.divisions');
        Route::get('/statistics', [RevenueController::class, 'getStatistics'])->name('statistics');
    });

    // ✅ ACCOUNT MANAGER ROUTES - Complete with enhanced import
    Route::prefix('account-manager')->name('account-manager.')->group(function () {
        // Basic CRUD
        Route::get('/', [AccountManagerController::class, 'index'])->name('index');
        Route::get('/create', [AccountManagerController::class, 'create'])->name('create');
        Route::post('/store', [AccountManagerController::class, 'store'])->name('store');
        Route::post('/', [AccountManagerController::class, 'store'])->name('store.alt');
        Route::get('/{id}/edit', [AccountManagerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AccountManagerController::class, 'update'])->name('update');
        Route::delete('/{id}', [AccountManagerController::class, 'destroy'])->name('destroy');

        // ✅ ENHANCED: Import Route with detailed tracking
        Route::post('/import', function(Request $request) {
            try {
                $validator = Validator::make($request->all(), [
                    'file' => 'required|mimes:xlsx,xls,csv|max:10240'
                ], [
                    'file.required' => 'File Excel wajib diupload.',
                    'file.mimes' => 'File harus berformat Excel (.xlsx, .xls) atau CSV.',
                    'file.max' => 'Ukuran file maksimal 10MB.'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => $validator->errors()->first(),
                        'data' => [
                            'imported' => 0,
                            'updated' => 0,
                            'duplicates' => 0,
                            'errors' => 1,
                            'error_details' => [$validator->errors()->first()]
                        ]
                    ], 422);
                }

                $import = new AccountManagerImport();
                Excel::import($import, $request->file('file'));
                $results = $import->getImportResults();

                $message = generateAccountManagerImportMessage($results);

                Log::info('Account Manager Import completed', [
                    'file_name' => $request->file('file')->getClientOriginalName(),
                    'results' => $results['summary']
                ]);

                return response()->json([
                    'success' => $results['errors'] == 0 || ($results['imported'] + $results['updated']) > 0,
                    'message' => $message,
                    'data' => $results
                ]);

            } catch (\Exception $e) {
                Log::error('Account Manager Import Error: ' . $e->getMessage(), [
                    'file_name' => $request->file('file')?->getClientOriginalName(),
                    'exception' => $e
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memproses file: ' . $e->getMessage(),
                    'data' => [
                        'imported' => 0,
                        'updated' => 0,
                        'duplicates' => 0,
                        'errors' => 1,
                        'error_details' => ['Error sistem: ' . $e->getMessage()]
                    ]
                ], 500);
            }
        })->name('import');

        // Export routes
        Route::get('/export', function(Request $request) {
            return Excel::download(new AccountManagerExport(), 'account_managers_export_' . date('Y-m-d_H-i-s') . '.xlsx');
        })->name('export');

        Route::get('/template', function() {
            return Excel::download(new AccountManagerTemplateExport(), 'template_account_manager_' . date('Y-m-d') . '.xlsx');
        })->name('template');

        // ✅ NEW: Missing methods that were added to controller
        Route::get('/add-modal', [AccountManagerController::class, 'showAddModal'])->name('add-modal');
        Route::get('/form-data', [AccountManagerController::class, 'getFormData'])->name('form-data');
        Route::post('/bulk-delete', [AccountManagerController::class, 'bulkDelete'])->name('bulk-delete');

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

        // Import/Export - same logic as above
        Route::post('/import', function(Request $request) {
            try {
                $validator = Validator::make($request->all(), [
                    'file' => 'required|mimes:xlsx,xls,csv|max:10240'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => $validator->errors()->first(),
                        'data' => ['imported' => 0, 'updated' => 0, 'duplicates' => 0, 'errors' => 1, 'error_details' => [$validator->errors()->first()]]
                    ], 422);
                }

                $import = new AccountManagerImport();
                Excel::import($import, $request->file('file'));
                $results = $import->getImportResults();

                return response()->json([
                    'success' => $results['errors'] == 0 || ($results['imported'] + $results['updated']) > 0,
                    'message' => generateAccountManagerImportMessage($results),
                    'data' => $results
                ]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'data' => ['imported' => 0, 'updated' => 0, 'duplicates' => 0, 'errors' => 1, 'error_details' => [$e->getMessage()]]], 500);
            }
        })->name('import');

        Route::get('/export', function() {
            return Excel::download(new AccountManagerExport(), 'account_managers_export_' . date('Y-m-d_H-i-s') . '.xlsx');
        })->name('export');

        Route::get('/template', function() {
            return Excel::download(new AccountManagerTemplateExport(), 'template_account_manager_' . date('Y-m-d') . '.xlsx');
        })->name('template');

        Route::get('/search', [AccountManagerController::class, 'search'])->name('search');
    });

    // ✅ CORPORATE CUSTOMER ROUTES - Complete with enhanced import
    Route::prefix('corporate-customer')->name('corporate-customer.')->group(function () {
        // Basic CRUD
        Route::get('/', [CorporateCustomerController::class, 'index'])->name('index');
        Route::get('/create', [CorporateCustomerController::class, 'create'])->name('create');
        Route::post('/store', [CorporateCustomerController::class, 'store'])->name('store');
        Route::post('/', [CorporateCustomerController::class, 'store'])->name('store.alt');
        Route::get('/{id}/edit', [CorporateCustomerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CorporateCustomerController::class, 'update'])->name('update');
        Route::delete('/{id}', [CorporateCustomerController::class, 'destroy'])->name('destroy');

        // ✅ ENHANCED: Import Route with detailed tracking
        Route::post('/import', function(Request $request) {
            try {
                $validator = Validator::make($request->all(), [
                    'file' => 'required|mimes:xlsx,xls,csv|max:10240'
                ], [
                    'file.required' => 'File Excel wajib diupload.',
                    'file.mimes' => 'File harus berformat Excel (.xlsx, .xls) atau CSV.',
                    'file.max' => 'Ukuran file maksimal 10MB.'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => $validator->errors()->first(),
                        'data' => [
                            'imported' => 0,
                            'updated' => 0,
                            'duplicates' => 0,
                            'errors' => 1,
                            'error_details' => [$validator->errors()->first()]
                        ]
                    ], 422);
                }

                $import = new CorporateCustomerImport();
                Excel::import($import, $request->file('file'));
                $results = $import->getImportResults();

                $message = generateCorporateCustomerImportMessage($results);

                Log::info('Corporate Customer Import completed', [
                    'file_name' => $request->file('file')->getClientOriginalName(),
                    'results' => $results['summary']
                ]);

                return response()->json([
                    'success' => $results['errors'] == 0 || ($results['imported'] + $results['updated']) > 0,
                    'message' => $message,
                    'data' => $results
                ]);

            } catch (\Exception $e) {
                Log::error('Corporate Customer Import Error: ' . $e->getMessage(), [
                    'file_name' => $request->file('file')?->getClientOriginalName(),
                    'exception' => $e
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memproses file: ' . $e->getMessage(),
                    'data' => [
                        'imported' => 0,
                        'updated' => 0,
                        'duplicates' => 0,
                        'errors' => 1,
                        'error_details' => ['Error sistem: ' . $e->getMessage()]
                    ]
                ], 500);
            }
        })->name('import');

        // Export routes
        Route::get('/export', function(Request $request) {
            return Excel::download(new CorporateCustomerExport(), 'corporate_customers_export_' . date('Y-m-d_H-i-s') . '.xlsx');
        })->name('export');

        Route::get('/template', function() {
            return Excel::download(new CorporateCustomerTemplateExport(), 'template_corporate_customer_' . date('Y-m-d') . '.xlsx');
        })->name('template');

        // Search and Utility Routes
        Route::get('/search', [CorporateCustomerController::class, 'search'])->name('search');
        Route::get('/statistics', [CorporateCustomerController::class, 'getStatistics'])->name('statistics');
        Route::post('/bulk-delete', [CorporateCustomerController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/validate-nipnas', [CorporateCustomerController::class, 'validateNipnas'])->name('validate-nipnas');
    });

    // ✅ UNDERSCORE ROUTES for backward compatibility - Corporate Customer
    Route::prefix('corporate_customer')->name('corporate_customer.')->group(function () {
        Route::get('/', [CorporateCustomerController::class, 'index'])->name('index');
        Route::get('/create', [CorporateCustomerController::class, 'create'])->name('create');
        Route::post('/store', [CorporateCustomerController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [CorporateCustomerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CorporateCustomerController::class, 'update'])->name('update');
        Route::delete('/{id}', [CorporateCustomerController::class, 'destroy'])->name('destroy');

        // Import/Export - same logic as above
        Route::post('/import', function(Request $request) {
            try {
                $validator = Validator::make($request->all(), [
                    'file' => 'required|mimes:xlsx,xls,csv|max:10240'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => $validator->errors()->first(),
                        'data' => ['imported' => 0, 'updated' => 0, 'duplicates' => 0, 'errors' => 1, 'error_details' => [$validator->errors()->first()]]
                    ], 422);
                }

                $import = new CorporateCustomerImport();
                Excel::import($import, $request->file('file'));
                $results = $import->getImportResults();

                return response()->json([
                    'success' => $results['errors'] == 0 || ($results['imported'] + $results['updated']) > 0,
                    'message' => generateCorporateCustomerImportMessage($results),
                    'data' => $results
                ]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'data' => ['imported' => 0, 'updated' => 0, 'duplicates' => 0, 'errors' => 1, 'error_details' => [$e->getMessage()]]], 500);
            }
        })->name('import');

        Route::get('/export', function() {
            return Excel::download(new CorporateCustomerExport(), 'corporate_customers_export_' . date('Y-m-d_H-i-s') . '.xlsx');
        })->name('export');

        Route::get('/template', function() {
            return Excel::download(new CorporateCustomerTemplateExport(), 'template_corporate_customer_' . date('Y-m-d') . '.xlsx');
        })->name('template');

        Route::get('/search', [CorporateCustomerController::class, 'search'])->name('search');
    });

    // ✅ API ROUTES - Complete API endpoints
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

    // ✅ OTHER EXISTING ROUTES - Preserved
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