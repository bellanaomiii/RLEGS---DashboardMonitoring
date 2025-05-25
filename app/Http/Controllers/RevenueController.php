<?php

namespace App\Http\Controllers;

use App\Models\Revenue;
use App\Models\AccountManager;
use App\Models\CorporateCustomer;
use App\Models\Divisi;
use App\Models\Witel;
use App\Models\Regional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RevenueController extends Controller
{
    /**
     * ✅ FIXED: Display a listing of revenue data with enhanced search
     */
    public function index(Request $request)
    {
        try {
            $query = Revenue::with(['accountManager.witel', 'accountManager.regional', 'corporateCustomer', 'divisi']);

            // ✅ ENHANCED: Search functionality - partial word search
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = trim($request->search);
                $query->where(function($q) use ($searchTerm) {
                    $q->whereHas('accountManager', function($subQuery) use ($searchTerm) {
                        $subQuery->where('nama', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('nik', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('corporateCustomer', function($subQuery) use ($searchTerm) {
                        $subQuery->where('nama', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('nipnas', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('divisi', function($subQuery) use ($searchTerm) {
                        $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('accountManager.witel', function($subQuery) use ($searchTerm) {
                        $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('accountManager.regional', function($subQuery) use ($searchTerm) {
                        $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                    });
                });
            }

            // Filter by month and year separately
            if ($request->has('month') && !empty($request->month)) {
                $query->whereMonth('bulan', $request->month);
            }

            if ($request->has('year') && !empty($request->year)) {
                $query->whereYear('bulan', $request->year);
            }

            // Filter by bulan (Y-m format)
            if ($request->has('bulan') && !empty($request->bulan)) {
                // ✅ FIXED: Handle both Y-m and Y-m-d formats
                $bulanFilter = $request->bulan;
                if (strlen($bulanFilter) === 7) { // Y-m format
                    $bulanFilter .= '-01'; // Convert to Y-m-d format
                }
                $query->where('bulan', 'LIKE', substr($bulanFilter, 0, 7) . '%');
            }

            // Filter by Account Manager
            if ($request->has('account_manager') && !empty($request->account_manager)) {
                $query->where('account_manager_id', $request->account_manager);
            }

            // Filter by Corporate Customer
            if ($request->has('corporate_customer') && !empty($request->corporate_customer)) {
                $query->where('corporate_customer_id', $request->corporate_customer);
            }

            // Filter by Divisi
            if ($request->has('divisi') && !empty($request->divisi)) {
                $query->where('divisi_id', $request->divisi);
            }

            // Filter by Witel (through Account Manager)
            if ($request->has('witel') && !empty($request->witel)) {
                $query->whereHas('accountManager', function($subQuery) use ($request) {
                    $subQuery->where('witel_id', $request->witel);
                });
            }

            // Filter by Regional (through Account Manager)
            if ($request->has('regional') && !empty($request->regional)) {
                $query->whereHas('accountManager', function($subQuery) use ($request) {
                    $subQuery->where('regional_id', $request->regional);
                });
            }

            // ✅ Paginate revenues
            $revenues = $query->orderBy('bulan', 'desc')
                             ->orderBy('created_at', 'desc')
                             ->paginate($request->get('per_page', 15));

            // ✅ FIXED: Enhanced search for AccountManagers
            $accountManagerQuery = AccountManager::with(['witel', 'regional', 'divisis']);

            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = trim($request->search);
                $accountManagerQuery->where(function($q) use ($searchTerm) {
                    $q->where('nama', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('nik', 'LIKE', "%{$searchTerm}%")
                      ->orWhereHas('witel', function($subQuery) use ($searchTerm) {
                          $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                      })
                      ->orWhereHas('regional', function($subQuery) use ($searchTerm) {
                          $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                      })
                      ->orWhereHas('divisis', function($subQuery) use ($searchTerm) {
                          $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                      });
                });
            }

            if ($request->has('witel') && !empty($request->witel)) {
                $accountManagerQuery->where('witel_id', $request->witel);
            }

            if ($request->has('regional') && !empty($request->regional)) {
                $accountManagerQuery->where('regional_id', $request->regional);
            }

            $accountManagers = $accountManagerQuery->orderBy('nama')
                                                 ->paginate($request->get('per_page', 15));

            // ✅ FIXED: Enhanced search for Corporate Customers
            $corporateCustomerQuery = CorporateCustomer::query();

            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = trim($request->search);
                $corporateCustomerQuery->where(function($q) use ($searchTerm) {
                    $q->where('nama', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('nipnas', 'LIKE', "%{$searchTerm}%");
                });
            }

            $corporateCustomers = $corporateCustomerQuery->orderBy('nama')
                                                       ->paginate($request->get('per_page', 15));

            // Get additional data for filters and forms
            $witels = Witel::orderBy('nama')->get();
            $regionals = Regional::orderBy('nama')->get();
            $divisis = Divisi::orderBy('nama')->get();

            // Generate year range for filter dropdown
            $currentYear = date('Y');
            $yearRange = range($currentYear - 5, $currentYear + 2);

            // Get statistics for dashboard
            $statistics = $this->getStatistics();

            return view('revenueData', compact(
                'revenues',
                'accountManagers',
                'corporateCustomers',
                'divisis',
                'witels',
                'regionals',
                'yearRange',
                'statistics'
            ));

        } catch (\Exception $e) {
            Log::error('Revenue Index Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data Revenue.');
        }
    }

    /**
     * ✅ FIXED: Store a newly created revenue with proper date handling
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'account_manager_id' => 'required|exists:account_managers,id',
                'corporate_customer_id' => 'required|exists:corporate_customers,id',
                'divisi_id' => 'required|exists:divisi,id',
                'target_revenue' => 'required|numeric|min:0|max:999999999999',
                'real_revenue' => 'required|numeric|min:0|max:999999999999',
                'bulan' => 'required|date_format:Y-m',
            ], [
                'account_manager_id.required' => 'Account Manager wajib dipilih.',
                'account_manager_id.exists' => 'Account Manager tidak valid.',
                'corporate_customer_id.required' => 'Corporate Customer wajib dipilih.',
                'corporate_customer_id.exists' => 'Corporate Customer tidak valid.',
                'divisi_id.required' => 'Divisi wajib dipilih.',
                'divisi_id.exists' => 'Divisi tidak valid.',
                'target_revenue.required' => 'Target Revenue wajib diisi.',
                'target_revenue.numeric' => 'Target Revenue harus berupa angka.',
                'target_revenue.min' => 'Target Revenue tidak boleh negatif.',
                'target_revenue.max' => 'Target Revenue terlalu besar.',
                'real_revenue.required' => 'Real Revenue wajib diisi.',
                'real_revenue.numeric' => 'Real Revenue harus berupa angka.',
                'real_revenue.min' => 'Real Revenue tidak boleh negatif.',
                'real_revenue.max' => 'Real Revenue terlalu besar.',
                'bulan.required' => 'Bulan wajib dipilih.',
                'bulan.date_format' => 'Format bulan tidak valid (harus YYYY-MM).',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ FIXED: Convert Y-m format to proper date (Y-m-01) for database storage
            $bulanInput = $request->bulan; // Should be Y-m format (e.g., "2025-10")
            $bulanDate = $bulanInput . '-01'; // Convert to Y-m-d format (e.g., "2025-10-01")

            // Validate the date is actually valid
            if (!Carbon::createFromFormat('Y-m-d', $bulanDate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format bulan tidak valid.'
                ], 422);
            }

            // Check for duplicate entry
            $existingRevenue = Revenue::where([
                'account_manager_id' => $request->account_manager_id,
                'corporate_customer_id' => $request->corporate_customer_id,
                'divisi_id' => $request->divisi_id,
                'bulan' => $bulanDate,
            ])->first();

            if ($existingRevenue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data revenue untuk kombinasi Account Manager, Corporate Customer, Divisi, dan Bulan ini sudah ada.'
                ], 422);
            }

            // Create Revenue with proper date format
            $revenue = Revenue::create([
                'account_manager_id' => $request->account_manager_id,
                'corporate_customer_id' => $request->corporate_customer_id,
                'divisi_id' => $request->divisi_id,
                'target_revenue' => $request->target_revenue,
                'real_revenue' => $request->real_revenue,
                'bulan' => $bulanDate, // Store as Y-m-d format
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data Revenue berhasil ditambahkan.',
                'data' => $revenue->load(['accountManager.witel', 'accountManager.regional', 'corporateCustomer', 'divisi'])
            ]);

        } catch (\Exception $e) {
            Log::error('Revenue Store Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified revenue
     */
    public function edit($id)
    {
        try {
            $revenue = Revenue::with(['accountManager.witel', 'accountManager.regional', 'corporateCustomer', 'divisi'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $revenue
            ]);

        } catch (\Exception $e) {
            Log::error('Revenue Edit Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Revenue tidak ditemukan.'
            ], 404);
        }
    }

    /**
     * ✅ FIXED: Update the specified revenue with proper date handling
     */
    public function update(Request $request, $id)
    {
        try {
            $revenue = Revenue::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'account_manager_id' => 'required|exists:account_managers,id',
                'corporate_customer_id' => 'required|exists:corporate_customers,id',
                'divisi_id' => 'required|exists:divisi,id',
                'target_revenue' => 'required|numeric|min:0|max:999999999999',
                'real_revenue' => 'required|numeric|min:0|max:999999999999',
                'bulan' => 'required|date_format:Y-m',
            ], [
                'account_manager_id.required' => 'Account Manager wajib dipilih.',
                'account_manager_id.exists' => 'Account Manager tidak valid.',
                'corporate_customer_id.required' => 'Corporate Customer wajib dipilih.',
                'corporate_customer_id.exists' => 'Corporate Customer tidak valid.',
                'divisi_id.required' => 'Divisi wajib dipilih.',
                'divisi_id.exists' => 'Divisi tidak valid.',
                'target_revenue.required' => 'Target Revenue wajib diisi.',
                'target_revenue.numeric' => 'Target Revenue harus berupa angka.',
                'target_revenue.min' => 'Target Revenue tidak boleh negatif.',
                'target_revenue.max' => 'Target Revenue terlalu besar.',
                'real_revenue.required' => 'Real Revenue wajib diisi.',
                'real_revenue.numeric' => 'Real Revenue harus berupa angka.',
                'real_revenue.min' => 'Real Revenue tidak boleh negatif.',
                'real_revenue.max' => 'Real Revenue terlalu besar.',
                'bulan.required' => 'Bulan wajib dipilih.',
                'bulan.date_format' => 'Format bulan tidak valid (harus YYYY-MM).',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ FIXED: Convert Y-m format to proper date
            $bulanInput = $request->bulan;
            $bulanDate = $bulanInput . '-01';

            // Validate the date
            if (!Carbon::createFromFormat('Y-m-d', $bulanDate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format bulan tidak valid.'
                ], 422);
            }

            // Check for duplicate entry (exclude current record)
            $existingRevenue = Revenue::where([
                'account_manager_id' => $request->account_manager_id,
                'corporate_customer_id' => $request->corporate_customer_id,
                'divisi_id' => $request->divisi_id,
                'bulan' => $bulanDate,
            ])->where('id', '!=', $id)->first();

            if ($existingRevenue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data revenue untuk kombinasi Account Manager, Corporate Customer, Divisi, dan Bulan ini sudah ada.'
                ], 422);
            }

            // Update Revenue
            $revenue->update([
                'account_manager_id' => $request->account_manager_id,
                'corporate_customer_id' => $request->corporate_customer_id,
                'divisi_id' => $request->divisi_id,
                'target_revenue' => $request->target_revenue,
                'real_revenue' => $request->real_revenue,
                'bulan' => $bulanDate,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data Revenue berhasil diperbarui.',
                'data' => $revenue->load(['accountManager.witel', 'accountManager.regional', 'corporateCustomer', 'divisi'])
            ]);

        } catch (\Exception $e) {
            Log::error('Revenue Update Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified revenue
     */
    public function destroy($id)
    {
        try {
            $revenue = Revenue::findOrFail($id);
            $revenue->delete();

            return back()->with('success', 'Data Revenue berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Revenue Delete Error: ' . $e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat menghapus data Revenue.');
        }
    }

    /**
     * ✅ ENHANCED: Search functionality for global search with partial word support
     */
    public function search(Request $request)
    {
        try {
            $searchTerm = trim($request->get('search', ''));

            if (strlen($searchTerm) < 2) {
                return response()->json([
                    'success' => true,
                    'stats' => [
                        'total_results' => 0,
                        'account_managers_count' => 0,
                        'corporate_customers_count' => 0,
                        'revenues_count' => 0,
                    ]
                ]);
            }

            // ✅ ENHANCED: Search Account Managers with partial word matching
            $accountManagersCount = AccountManager::where(function($query) use ($searchTerm) {
                $query->where('nama', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('nik', 'LIKE', "%{$searchTerm}%")
                      ->orWhereHas('witel', function($subQuery) use ($searchTerm) {
                          $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                      })
                      ->orWhereHas('regional', function($subQuery) use ($searchTerm) {
                          $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                      })
                      ->orWhereHas('divisis', function($subQuery) use ($searchTerm) {
                          $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                      });
            })->count();

            // ✅ ENHANCED: Search Corporate Customers with partial word matching
            $corporateCustomersCount = CorporateCustomer::where('nama', 'LIKE', "%{$searchTerm}%")
                                                      ->orWhere('nipnas', 'LIKE', "%{$searchTerm}%")
                                                      ->count();

            // ✅ ENHANCED: Search Revenues with partial word matching
            $revenuesCount = Revenue::where(function($query) use ($searchTerm) {
                $query->whereHas('accountManager', function($subQuery) use ($searchTerm) {
                    $subQuery->where('nama', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('nik', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('corporateCustomer', function($subQuery) use ($searchTerm) {
                    $subQuery->where('nama', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('nipnas', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('divisi', function($subQuery) use ($searchTerm) {
                    $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('accountManager.witel', function($subQuery) use ($searchTerm) {
                    $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('accountManager.regional', function($subQuery) use ($searchTerm) {
                    $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                });
            })->count();

            $totalResults = $accountManagersCount + $corporateCustomersCount + $revenuesCount;

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_results' => $totalResults,
                    'account_managers_count' => $accountManagersCount,
                    'corporate_customers_count' => $corporateCustomersCount,
                    'revenues_count' => $revenuesCount,
                    'search_term' => $searchTerm
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Revenue Search Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat pencarian.',
                'stats' => [
                    'total_results' => 0,
                    'account_managers_count' => 0,
                    'corporate_customers_count' => 0,
                    'revenues_count' => 0,
                ]
            ]);
        }
    }

    /**
     * ✅ ENHANCED: Search Account Manager for autocomplete with partial word support
     */
    public function searchAccountManager(Request $request)
    {
        try {
            $searchTerm = trim($request->get('search', ''));

            if (strlen($searchTerm) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $accountManagers = AccountManager::where(function($query) use ($searchTerm) {
                $query->where('nama', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('nik', 'LIKE', "%{$searchTerm}%")
                      ->orWhereHas('witel', function($subQuery) use ($searchTerm) {
                          $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                      })
                      ->orWhereHas('regional', function($subQuery) use ($searchTerm) {
                          $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                      });
            })
            ->with(['divisis', 'witel', 'regional'])
            ->limit(10)
            ->get(['id', 'nama', 'nik']);

            return response()->json([
                'success' => true,
                'data' => $accountManagers
            ]);

        } catch (\Exception $e) {
            Log::error('Account Manager Search Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat pencarian.',
                'data' => []
            ]);
        }
    }

    /**
     * ✅ ENHANCED: Search Corporate Customer for autocomplete with partial word support
     */
    public function searchCorporateCustomer(Request $request)
    {
        try {
            $searchTerm = trim($request->get('search', ''));

            if (strlen($searchTerm) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $corporateCustomers = CorporateCustomer::where('nama', 'LIKE', "%{$searchTerm}%")
                                               ->orWhere('nipnas', 'LIKE', "%{$searchTerm}%")
                                               ->limit(10)
                                               ->get(['id', 'nama', 'nipnas']);

            return response()->json([
                'success' => true,
                'data' => $corporateCustomers
            ]);

        } catch (\Exception $e) {
            Log::error('Corporate Customer Search Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat pencarian.',
                'data' => []
            ]);
        }
    }

    /**
     * Get Account Manager divisions for dropdown
     */
    public function getAccountManagerDivisions($id)
    {
        try {
            $accountManager = AccountManager::with('divisis')->findOrFail($id);

            return response()->json([
                'success' => true,
                'divisis' => $accountManager->divisis
            ]);

        } catch (\Exception $e) {
            Log::error('Get Account Manager Divisions Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Account Manager tidak ditemukan.',
                'divisis' => []
            ]);
        }
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics()
    {
        try {
            $currentMonth = Carbon::now()->format('Y-m-d');
            $previousMonth = Carbon::now()->subMonth()->format('Y-m-d');

            $totalRevenues = Revenue::count();
            $totalAccountManagers = AccountManager::count();
            $totalCorporateCustomers = CorporateCustomer::count();

            $currentMonthRevenues = Revenue::whereYear('bulan', Carbon::now()->year)
                                          ->whereMonth('bulan', Carbon::now()->month)
                                          ->count();

            $previousMonthRevenues = Revenue::whereYear('bulan', Carbon::now()->subMonth()->year)
                                           ->whereMonth('bulan', Carbon::now()->subMonth()->month)
                                           ->count();

            $currentMonthTargetSum = Revenue::whereYear('bulan', Carbon::now()->year)
                                           ->whereMonth('bulan', Carbon::now()->month)
                                           ->sum('target_revenue');

            $currentMonthRealSum = Revenue::whereYear('bulan', Carbon::now()->year)
                                         ->whereMonth('bulan', Carbon::now()->month)
                                         ->sum('real_revenue');

            $achievementRate = $currentMonthTargetSum > 0 ?
                round(($currentMonthRealSum / $currentMonthTargetSum) * 100, 2) : 0;

            $activeAccountManagers = AccountManager::whereHas('revenues')->distinct()->count();
            $activeCorporateCustomers = CorporateCustomer::whereHas('revenues')->distinct()->count();

            return [
                'total_revenues' => $totalRevenues,
                'total_account_managers' => $totalAccountManagers,
                'total_corporate_customers' => $totalCorporateCustomers,
                'current_month_revenues' => $currentMonthRevenues,
                'previous_month_revenues' => $previousMonthRevenues,
                'current_month_target' => $currentMonthTargetSum,
                'current_month_real' => $currentMonthRealSum,
                'achievement_rate' => $achievementRate,
                'active_account_managers' => $activeAccountManagers,
                'active_corporate_customers' => $activeCorporateCustomers,
                'current_month' => Carbon::now()->format('F Y'),
                'previous_month' => Carbon::now()->subMonth()->format('F Y')
            ];

        } catch (\Exception $e) {
            Log::error('Revenue Statistics Error: ' . $e->getMessage());

            return [
                'total_revenues' => 0,
                'total_account_managers' => 0,
                'total_corporate_customers' => 0,
                'current_month_revenues' => 0,
                'previous_month_revenues' => 0,
                'current_month_target' => 0,
                'current_month_real' => 0,
                'achievement_rate' => 0,
                'active_account_managers' => 0,
                'active_corporate_customers' => 0,
                'current_month' => Carbon::now()->format('F Y'),
                'previous_month' => Carbon::now()->subMonth()->format('F Y')
            ];
        }
    }

    /**
     * ✅ NEW: Get Revenue data for API (alias untuk edit)
     */
    public function getRevenueData($id)
    {
        return $this->edit($id);
    }

    /**
     * ✅ NEW: Update Revenue via API (alias untuk update)
     */
    public function updateRevenue(Request $request, $id)
    {
        return $this->update($request, $id);
    }
}