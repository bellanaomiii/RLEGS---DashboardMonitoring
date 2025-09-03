<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountManager;
use App\Models\Revenue;
use App\Models\CorporateCustomer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AccountManagerDetailController extends Controller
{
    public function show($id, Request $request)
    {
        // Get selected year for filtering (default to current year)
        $selectedYear = $request->input('year', Carbon::now()->year);

        // Get selected divisi for filtering (optional)
        $selectedDivisiId = $request->input('divisi');

        // Get selected category filter (untuk AM multi divisi DGS+DSS)
        $selectedCategoryFilter = $request->input('category_filter', 'enterprise'); // default enterprise

        // Get view mode (detail atau aggregate)
        $viewMode = $request->input('view_mode', 'detail'); // default detail

        // Get selected month filter for detail view
        $selectedMonth = $request->input('month', 'all');

        // Get the account manager with relationships
        $accountManager = AccountManager::with(['witel', 'divisis', 'corporateCustomers', 'user', 'revenues'])
            ->findOrFail($id);

        // Determine AM category and available filters
        $amCategory = $this->determineAmCategory($accountManager);
        $needsCategoryFilter = $amCategory['needs_filter'];

        // Get global ranking data with previous month comparison
        $globalRanking = $this->getGlobalRanking($accountManager);

        // Get witel ranking data (conditional based on category)
        $witelRanking = $this->getWitelRanking($accountManager, $selectedCategoryFilter);

        // Get ALL division rankings data with previous month comparison (scope: witel)
        $divisionRankings = [];
        foreach ($accountManager->divisis as $divisi) {
            $divisionRankings[$divisi->id] = $this->getDivisionRanking($accountManager, $divisi->id);
            $divisionRankings[$divisi->id]['nama'] = $divisi->nama;
        }

        // For backward compatibility, get the primary division ranking (first one or selected)
        $primaryDivisiId = $selectedDivisiId ?: ($accountManager->divisis->isNotEmpty() ? $accountManager->divisis->first()->id : null);
        $divisionRanking = isset($divisionRankings[$primaryDivisiId]) ? $divisionRankings[$primaryDivisiId] : [
            'position' => 'N/A',
            'total' => 0,
            'previous_position' => 'N/A',
            'position_change' => 0,
            'nama' => 'N/A'
        ];

        // Get customer revenue data for the selected year, separated by divisi
        $customerRevenuesByDivisi = [];

        if ($selectedDivisiId) {
            // If specific divisi is selected, get only data for that divisi
            if ($viewMode === 'detail') {
                $customerRevenuesByDivisi[$selectedDivisiId] = $this->getDetailedCustomerRevenues($accountManager, $selectedYear, $selectedDivisiId, $selectedMonth);
            } else {
                $customerRevenuesByDivisi[$selectedDivisiId] = $this->getCustomerRevenues($accountManager, $selectedYear, $selectedDivisiId);
            }
        } else {
            // Otherwise get data for all divisi
            foreach ($accountManager->divisis as $divisi) {
                if ($viewMode === 'detail') {
                    $customerRevenuesByDivisi[$divisi->id] = $this->getDetailedCustomerRevenues($accountManager, $selectedYear, $divisi->id, $selectedMonth);
                } else {
                    $customerRevenuesByDivisi[$divisi->id] = $this->getCustomerRevenues($accountManager, $selectedYear, $divisi->id);
                }
            }
        }

        // For backward compatibility, also send combined data
        if ($viewMode === 'detail') {
            $customerRevenues = $this->getDetailedCustomerRevenues($accountManager, $selectedYear, null, $selectedMonth);
        } else {
            $customerRevenues = $this->getCustomerRevenues($accountManager, $selectedYear);
        }

        // Get monthly performance data for all divisi
        $monthlyPerformanceByDivisi = [];

        if ($selectedDivisiId) {
            // If specific divisi is selected, get only data for that divisi
            $monthlyPerformanceByDivisi[$selectedDivisiId] = $this->getMonthlyPerformance($accountManager, $selectedYear, $selectedDivisiId);
        } else {
            // Otherwise get data for all divisi
            foreach ($accountManager->divisis as $divisi) {
                $monthlyPerformanceByDivisi[$divisi->id] = $this->getMonthlyPerformance($accountManager, $selectedYear, $divisi->id);
            }
        }

        // For backward compatibility, also send combined data
        $monthlyPerformance = $this->getMonthlyPerformance($accountManager, $selectedYear);

        // Get performance insights for each divisi
        $insightsByDivisi = [];
        foreach ($monthlyPerformanceByDivisi as $divisiId => $performance) {
            $insightsByDivisi[$divisiId] = $this->generateInsights($performance);
        }

        // For backward compatibility, also send combined insights
        $insights = $this->generateInsights($monthlyPerformance);

        // Get year list for dropdown
        $yearsList = Revenue::selectRaw('YEAR(bulan) as year')
            ->where('account_manager_id', $id)
            ->groupBy(DB::raw('YEAR(bulan)'))
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (empty($yearsList)) {
            $yearsList = [Carbon::now()->year];
        }

        // Get months list for dropdown (only for detail view)
        $monthsList = $this->getAvailableMonths($accountManager->id, $selectedYear);

        // Get total revenue time period (earliest to latest month/year)
        $revenuePeriod = $this->getRevenuePeriod($accountManager->id);

        return view('detailAM', [
            'accountManager' => $accountManager,
            'globalRanking' => $globalRanking,
            'witelRanking' => $witelRanking,
            'divisionRanking' => $divisionRanking,
            'divisionRankings' => $divisionRankings,
            'customerRevenues' => $customerRevenues,
            'customerRevenuesByDivisi' => $customerRevenuesByDivisi,
            'monthlyPerformance' => $monthlyPerformance,
            'monthlyPerformanceByDivisi' => $monthlyPerformanceByDivisi,
            'insights' => $insights,
            'insightsByDivisi' => $insightsByDivisi,
            'yearsList' => $yearsList,
            'monthsList' => $monthsList,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'selectedDivisiId' => $selectedDivisiId,
            'selectedCategoryFilter' => $selectedCategoryFilter,
            'viewMode' => $viewMode,
            'amCategory' => $amCategory,
            'needsCategoryFilter' => $needsCategoryFilter,
            'revenuePeriod' => $revenuePeriod
        ]);
    }

    /**
     * NEW FUNCTION: Get detailed customer revenues with monthly breakdown
     */
    private function getDetailedCustomerRevenues($accountManager, $year, $divisiId = null, $selectedMonth = 'all')
    {
        // Start with base query
        $query = DB::table('revenues')
            ->join('corporate_customers', 'revenues.corporate_customer_id', '=', 'corporate_customers.id')
            ->leftJoin('divisi', 'revenues.divisi_id', '=', 'divisi.id')
            ->select(
                'revenues.id as revenue_id',
                'corporate_customers.id as customer_id',
                'corporate_customers.nama as customer_name',
                'corporate_customers.nipnas',
                'divisi.nama as divisi_name',
                'revenues.divisi_id',
                'revenues.real_revenue',
                'revenues.target_revenue',
                DB::raw('CASE WHEN revenues.target_revenue > 0 THEN (revenues.real_revenue / revenues.target_revenue * 100) ELSE 0 END as achievement'),
                'revenues.bulan',
                DB::raw('MONTHNAME(revenues.bulan) as month_name'),
                DB::raw('MONTH(revenues.bulan) as month_number'),
                DB::raw('YEAR(revenues.bulan) as year')
            )
            ->where('revenues.account_manager_id', $accountManager->id)
            ->whereYear('revenues.bulan', $year);

        // Add divisi filter if specified
        if ($divisiId) {
            $query->where('revenues.divisi_id', $divisiId);
        }

        // Add month filter if specified and not 'all'
        if ($selectedMonth !== 'all' && is_numeric($selectedMonth)) {
            $query->whereMonth('revenues.bulan', $selectedMonth);
        }

        // Order by month desc, then by revenue desc
        $detailedRevenueData = $query->orderBy('revenues.bulan', 'desc')
            ->orderBy('revenues.real_revenue', 'desc')
            ->get();

        return $detailedRevenueData;
    }

    /**
     * NEW FUNCTION: Get available months for the selected year and account manager
     */
    private function getAvailableMonths($accountManagerId, $year)
    {
        $months = Revenue::selectRaw('MONTH(bulan) as month_number, MONTHNAME(bulan) as month_name')
            ->where('account_manager_id', $accountManagerId)
            ->whereYear('bulan', $year)
            ->groupBy(DB::raw('MONTH(bulan)'), DB::raw('MONTHNAME(bulan)'))
            ->orderBy(DB::raw('MONTH(bulan)'))
            ->get();

        // Convert to array with Indonesian month names
        $indonesianMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $monthsList = [];
        foreach ($months as $month) {
            $monthsList[] = [
                'number' => $month->month_number,
                'name' => $indonesianMonths[$month->month_number] ?? $month->month_name,
                'english_name' => $month->month_name
            ];
        }

        return $monthsList;
    }

    /**
     * Determine AM category based on their divisions
     */
    private function determineAmCategory($accountManager)
    {
        $divisionNames = $accountManager->divisis->pluck('nama')->toArray();

        // Check what divisions the AM belongs to
        $hasDPS = in_array('DPS', $divisionNames);
        $hasDSS = in_array('DSS', $divisionNames);
        $hasDGS = in_array('DGS', $divisionNames);

        // Determine category
        if ($hasDGS && ($hasDSS || $hasDPS) && !($hasDPS && $hasDSS)) {
            // DGS + DSS OR DGS + DPS (but not all three) = MULTI DIVISI (needs filter)
            return [
                'category' => 'MULTI',
                'is_enterprise' => true,
                'is_government' => true,
                'needs_filter' => true,
                'label' => 'Multi Divisi (Government & Enterprise)'
            ];
        } elseif ($hasDGS && !$hasDSS && !$hasDPS) {
            // DGS only = GOVERNMENT
            return [
                'category' => 'GOVERNMENT',
                'is_enterprise' => false,
                'is_government' => true,
                'needs_filter' => false,
                'label' => 'Government'
            ];
        } elseif (($hasDPS || $hasDSS) && !$hasDGS) {
            // DPS only OR DSS only OR DPS+DSS (without DGS) = ENTERPRISE
            return [
                'category' => 'ENTERPRISE',
                'is_enterprise' => true,
                'is_government' => false,
                'needs_filter' => false,
                'label' => 'Enterprise'
            ];
        } elseif ($hasDGS && $hasDPS && $hasDSS) {
            // All three divisions = SUPER MULTI (also needs filter)
            return [
                'category' => 'MULTI',
                'is_enterprise' => true,
                'is_government' => true,
                'needs_filter' => true,
                'label' => 'Multi Divisi (All Categories)'
            ];
        } else {
            // Default fallback
            return [
                'category' => 'UNKNOWN',
                'is_enterprise' => false,
                'is_government' => false,
                'needs_filter' => false,
                'label' => 'Unknown'
            ];
        }
    }

    /**
     * Get the revenue time period (earliest to latest month/year with data)
     */
    private function getRevenuePeriod($accountManagerId)
    {
        // Ambil tanggal minimum dan maksimum dari revenue
        $minDate = Revenue::where('account_manager_id', $accountManagerId)
            ->orderBy('bulan', 'asc')
            ->value('bulan');

        $maxDate = Revenue::where('account_manager_id', $accountManagerId)
            ->orderBy('bulan', 'desc')
            ->value('bulan');

        // Jika tidak ada data, return default
        if (!$minDate || !$maxDate) {
            return [
                'earliest' => 'Belum ada data',
                'latest' => 'Belum ada data',
                'period_string' => 'Belum ada data',
                'formatted_period' => 'Belum ada data'
            ];
        }

        // Format tanggal dengan Carbon (locale Indonesia)
        $minMonth = Carbon::parse($minDate)->locale('id')->translatedFormat('M Y');
        $maxMonth = Carbon::parse($maxDate)->locale('id')->translatedFormat('M Y');

        // Buat string periode
        $periodString = $minMonth === $maxMonth ? $minMonth : "$minMonth hingga $maxMonth";

        return [
            'earliest' => $minMonth,
            'latest' => $maxMonth,
            'period_string' => $periodString,
            'formatted_period' => "Sejak $periodString",
            'earliest_date' => $minDate,
            'latest_date' => $maxDate
        ];
    }

    private function getGlobalRanking($accountManager)
    {
        // Get current month and previous month
        $currentMonth = Carbon::now()->format('m');
        $previousMonth = Carbon::now()->subMonth()->format('m');
        $currentYear = Carbon::now()->format('Y');

        // Get current month ranking
        $allAMs = AccountManager::select('account_managers.*')
            ->selectSub(function ($query) use ($currentMonth, $currentYear) {
                $query->selectRaw('COALESCE(SUM(revenues.real_revenue), 0)')
                    ->from('revenues')
                    ->whereColumn('revenues.account_manager_id', 'account_managers.id')
                    ->whereMonth('revenues.bulan', $currentMonth)
                    ->whereYear('revenues.bulan', $currentYear);
            }, 'current_revenue')
            ->orderByDesc('current_revenue')
            ->get();

        // Find the position of current AM
        $currentPosition = $allAMs->search(function ($item) use ($accountManager) {
            return $item->id === $accountManager->id;
        });

        // Add +1 because index starts at 0, but handle case where AM is not found
        $currentPosition = $currentPosition !== false ? $currentPosition + 1 : count($allAMs);

        // Get previous month ranking
        $previousAMs = AccountManager::select('account_managers.*')
            ->selectSub(function ($query) use ($previousMonth, $currentYear) {
                $query->selectRaw('COALESCE(SUM(revenues.real_revenue), 0)')
                    ->from('revenues')
                    ->whereColumn('revenues.account_manager_id', 'account_managers.id')
                    ->whereMonth('revenues.bulan', $previousMonth)
                    ->whereYear('revenues.bulan', $currentYear);
            }, 'previous_revenue')
            ->orderByDesc('previous_revenue')
            ->get();

        // Find the position of current AM in previous month
        $previousPosition = $previousAMs->search(function ($item) use ($accountManager) {
            return $item->id === $accountManager->id;
        });

        // Add +1 because index starts at 0, but handle case where AM is not found
        $previousPosition = $previousPosition !== false ? $previousPosition + 1 : count($previousAMs);

        // Calculate change in position
        $positionChange = $previousPosition - $currentPosition;

        return [
            'position' => $currentPosition,
            'total' => $allAMs->count(),
            'previous_position' => $previousPosition,
            'position_change' => $positionChange
        ];
    }

    private function getWitelRanking($accountManager, $categoryFilter = 'enterprise')
    {
        // Only proceed if witel_id is set
        if (!$accountManager->witel_id) {
            return [
                'position' => 'N/A',
                'total' => 0,
                'previous_position' => 'N/A',
                'position_change' => 0
            ];
        }

        $amCategory = $this->determineAmCategory($accountManager);

        // Get current month and previous month
        $currentMonth = Carbon::now()->format('m');
        $previousMonth = Carbon::now()->subMonth()->format('m');
        $currentYear = Carbon::now()->format('Y');

        // Build query based on AM category and filter
        $query = AccountManager::where('witel_id', $accountManager->witel_id);

        if ($amCategory['category'] === 'MULTI' && $categoryFilter === 'government') {
            // For multi AM viewing as government, compare with other government AMs
            $query->whereHas('divisis', function($q) {
                $q->where('nama', 'DGS');
            })->whereDoesntHave('divisis', function($q) {
                $q->whereIn('nama', ['DPS', 'DSS']);
            });
        } elseif ($amCategory['category'] === 'MULTI' && $categoryFilter === 'enterprise') {
            // For multi AM viewing as enterprise, compare with other enterprise AMs
            $query->whereHas('divisis', function($q) {
                $q->whereIn('nama', ['DPS', 'DSS']);
            });
        } elseif ($amCategory['category'] === 'GOVERNMENT') {
            // Government AM compares with other government AMs
            $query->whereHas('divisis', function($q) {
                $q->where('nama', 'DGS');
            })->whereDoesntHave('divisis', function($q) {
                $q->whereIn('nama', ['DPS', 'DSS']);
            });
        } elseif ($amCategory['category'] === 'ENTERPRISE') {
            // Enterprise AM compares with other enterprise AMs
            $query->whereHas('divisis', function($q) {
                $q->whereIn('nama', ['DPS', 'DSS']);
            });
        }

        // Get current month ranking within witel
        $witelAMs = $query->select('account_managers.*')
            ->selectSub(function ($query) use ($currentMonth, $currentYear) {
                $query->selectRaw('COALESCE(SUM(revenues.real_revenue), 0)')
                    ->from('revenues')
                    ->whereColumn('revenues.account_manager_id', 'account_managers.id')
                    ->whereMonth('revenues.bulan', $currentMonth)
                    ->whereYear('revenues.bulan', $currentYear);
            }, 'current_revenue')
            ->orderByDesc('current_revenue')
            ->get();

        // Find the position of current AM within witel
        $currentPosition = $witelAMs->search(function ($item) use ($accountManager) {
            return $item->id === $accountManager->id;
        });

        // Add +1 because index starts at 0, but handle case where AM is not found
        $currentPosition = $currentPosition !== false ? $currentPosition + 1 : count($witelAMs);

        // Get previous month ranking within witel (same filter logic)
        $queryPrevious = AccountManager::where('witel_id', $accountManager->witel_id);

        if ($amCategory['category'] === 'MULTI' && $categoryFilter === 'government') {
            $queryPrevious->whereHas('divisis', function($q) {
                $q->where('nama', 'DGS');
            })->whereDoesntHave('divisis', function($q) {
                $q->whereIn('nama', ['DPS', 'DSS']);
            });
        } elseif ($amCategory['category'] === 'MULTI' && $categoryFilter === 'enterprise') {
            $queryPrevious->whereHas('divisis', function($q) {
                $q->whereIn('nama', ['DPS', 'DSS']);
            });
        } elseif ($amCategory['category'] === 'GOVERNMENT') {
            $queryPrevious->whereHas('divisis', function($q) {
                $q->where('nama', 'DGS');
            })->whereDoesntHave('divisis', function($q) {
                $q->whereIn('nama', ['DPS', 'DSS']);
            });
        } elseif ($amCategory['category'] === 'ENTERPRISE') {
            $queryPrevious->whereHas('divisis', function($q) {
                $q->whereIn('nama', ['DPS', 'DSS']);
            });
        }

        $previousWitelAMs = $queryPrevious->select('account_managers.*')
            ->selectSub(function ($query) use ($previousMonth, $currentYear) {
                $query->selectRaw('COALESCE(SUM(revenues.real_revenue), 0)')
                    ->from('revenues')
                    ->whereColumn('revenues.account_manager_id', 'account_managers.id')
                    ->whereMonth('revenues.bulan', $previousMonth)
                    ->whereYear('revenues.bulan', $currentYear);
            }, 'previous_revenue')
            ->orderByDesc('previous_revenue')
            ->get();

        // Find the position of current AM in previous month within witel
        $previousPosition = $previousWitelAMs->search(function ($item) use ($accountManager) {
            return $item->id === $accountManager->id;
        });

        // Add +1 because index starts at 0, but handle case where AM is not found
        $previousPosition = $previousPosition !== false ? $previousPosition + 1 : count($previousWitelAMs);

        // Calculate change in position
        $positionChange = $previousPosition - $currentPosition;

        return [
            'position' => $currentPosition,
            'total' => $witelAMs->count(),
            'previous_position' => $previousPosition,
            'position_change' => $positionChange,
            'category_label' => $categoryFilter === 'government' ? 'Government' : 'Enterprise'
        ];
    }

    private function getDivisionRanking($accountManager, $divisionId = null)
    {
        // If no division ID provided, return NA
        if (!$divisionId && $accountManager->divisis->isEmpty()) {
            return [
                'position' => 'N/A',
                'total' => 0,
                'previous_position' => 'N/A',
                'position_change' => 0
            ];
        }

        // If no division ID provided, use the first division
        if (!$divisionId) {
            $divisionId = $accountManager->divisis->first()->id;
        }

        // Get current month and previous month
        $currentMonth = Carbon::now()->format('m');
        $previousMonth = Carbon::now()->subMonth()->format('m');
        $currentYear = Carbon::now()->format('Y');

        // Get current month ranking within division AND witel (not global division)
        $divisionAMs = AccountManager::where('witel_id', $accountManager->witel_id) // Add witel scope
            ->whereHas('divisis', function($query) use ($divisionId) {
                $query->where('divisi.id', $divisionId);
            })
            ->select('account_managers.*')
            ->selectSub(function ($query) use ($currentMonth, $currentYear, $divisionId) {
                $query->selectRaw('COALESCE(SUM(revenues.real_revenue), 0)')
                    ->from('revenues')
                    ->whereColumn('revenues.account_manager_id', 'account_managers.id')
                    ->whereMonth('revenues.bulan', $currentMonth)
                    ->whereYear('revenues.bulan', $currentYear)
                    ->where('revenues.divisi_id', $divisionId); // Filter by divisi_id
            }, 'current_revenue')
            ->orderByDesc('current_revenue')
            ->get();

        // Find the position of current AM within division
        $currentPosition = $divisionAMs->search(function ($item) use ($accountManager) {
            return $item->id === $accountManager->id;
        });

        // Add +1 because index starts at 0, but handle case where AM is not found
        $currentPosition = $currentPosition !== false ? $currentPosition + 1 : count($divisionAMs);

        // Get previous month ranking within division AND witel
        $previousDivisionAMs = AccountManager::where('witel_id', $accountManager->witel_id) // Add witel scope
            ->whereHas('divisis', function($query) use ($divisionId) {
                $query->where('divisi.id', $divisionId);
            })
            ->select('account_managers.*')
            ->selectSub(function ($query) use ($previousMonth, $currentYear, $divisionId) {
                $query->selectRaw('COALESCE(SUM(revenues.real_revenue), 0)')
                    ->from('revenues')
                    ->whereColumn('revenues.account_manager_id', 'account_managers.id')
                    ->whereMonth('revenues.bulan', $previousMonth)
                    ->whereYear('revenues.bulan', $currentYear)
                    ->where('revenues.divisi_id', $divisionId); // Filter by divisi_id
            }, 'previous_revenue')
            ->orderByDesc('previous_revenue')
            ->get();

        // Find the position of current AM in previous month within division
        $previousPosition = $previousDivisionAMs->search(function ($item) use ($accountManager) {
            return $item->id === $accountManager->id;
        });

        // Add +1 because index starts at 0, but handle case where AM is not found
        $previousPosition = $previousPosition !== false ? $previousPosition + 1 : count($previousDivisionAMs);

        // Calculate change in position
        $positionChange = $previousPosition - $currentPosition;

        return [
            'position' => $currentPosition,
            'total' => $divisionAMs->count(),
            'previous_position' => $previousPosition,
            'position_change' => $positionChange
        ];
    }

    private function getCustomerRevenues($accountManager, $year, $divisiId = null)
    {
        // Start with base query
        $query = DB::table('revenues')
            ->join('corporate_customers', 'revenues.corporate_customer_id', '=', 'corporate_customers.id')
            ->select(
                'corporate_customers.id',
                'corporate_customers.nama',
                'corporate_customers.nipnas',
                DB::raw('SUM(revenues.real_revenue) as total_revenue'),
                DB::raw('SUM(revenues.target_revenue) as total_target'),
                DB::raw('CASE WHEN SUM(revenues.target_revenue) > 0 THEN (SUM(revenues.real_revenue) / SUM(revenues.target_revenue) * 100) ELSE 0 END as achievement')
            )
            ->where('revenues.account_manager_id', $accountManager->id)
            ->whereYear('revenues.bulan', $year);

        // Add divisi filter if specified
        if ($divisiId) {
            $query->where('revenues.divisi_id', $divisiId);
        }

        // Complete the query
        $customerRevenueData = $query->groupBy('corporate_customers.id', 'corporate_customers.nama', 'corporate_customers.nipnas')
            ->orderByDesc('total_revenue')
            ->get();

        return $customerRevenueData;
    }

    private function getMonthlyPerformance($accountManager, $year, $divisiId = null)
    {
        // Initialize array with all months
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthName = Carbon::create($year, $month)->format('F');
            $monthlyData[$month] = [
                'month_name' => $monthName,
                'month_number' => $month,
                'year' => $year,
                'real_revenue' => 0,
                'target_revenue' => 0,
                'achievement' => 0,
            ];
        }

        // Start with base query
        $query = DB::table('revenues')
            ->select(
                DB::raw('MONTH(bulan) as month'),
                DB::raw('SUM(real_revenue) as real_revenue'),
                DB::raw('SUM(target_revenue) as target_revenue'),
                DB::raw('CASE WHEN SUM(target_revenue) > 0 THEN (SUM(real_revenue) / SUM(target_revenue) * 100) ELSE 0 END as achievement')
            )
            ->where('account_manager_id', $accountManager->id)
            ->whereYear('bulan', $year);

        // Add divisi filter if specified
        if ($divisiId) {
            $query->where('divisi_id', $divisiId);
        }

        // Complete the query
        $monthlyRevenues = $query->groupBy(DB::raw('MONTH(bulan)'))
            ->get();

        // Update monthly data with actual values
        foreach ($monthlyRevenues as $revenue) {
            $monthlyData[$revenue->month]['real_revenue'] = $revenue->real_revenue;
            $monthlyData[$revenue->month]['target_revenue'] = $revenue->target_revenue;
            $monthlyData[$revenue->month]['achievement'] = $revenue->achievement;
        }

        return array_values($monthlyData); // Convert to indexed array for easier use in views
    }

    private function generateInsights($monthlyPerformance)
    {
        // Skip months with no data
        $activeMonths = array_filter($monthlyPerformance, function($month) {
            return $month['real_revenue'] > 0 || $month['target_revenue'] > 0;
        });

        if (empty($activeMonths)) {
            return [
                'best_revenue_month' => null,
                'worst_revenue_month' => null,
                'best_achievement_month' => null,
                'worst_achievement_month' => null,
                'avg_achievement' => 0,
                'trend' => 'neutral',
                'message' => 'Belum ada data performa yang tersedia untuk Account Manager ini.'
            ];
        }

        // Find best and worst months
        $bestRevenueMonth = null;
        $worstRevenueMonth = null;
        $bestAchievementMonth = null;
        $worstAchievementMonth = null;
        $maxRevenue = 0;
        $minRevenue = PHP_INT_MAX;
        $maxAchievement = 0;
        $minAchievement = PHP_INT_MAX;
        $totalAchievement = 0;
        $achievementCount = 0;

        foreach ($activeMonths as $month) {
            // Skip future months
            $currentDate = Carbon::now();
            if ($month['month_number'] > $currentDate->month && $currentDate->year == $month['year']) {
                continue;
            }

            $totalAchievement += $month['achievement'];
            $achievementCount++;

            // Best/worst revenue
            if ($month['real_revenue'] > $maxRevenue) {
                $maxRevenue = $month['real_revenue'];
                $bestRevenueMonth = $month;
            }

            if ($month['real_revenue'] < $minRevenue && $month['real_revenue'] > 0) {
                $minRevenue = $month['real_revenue'];
                $worstRevenueMonth = $month;
            }

            // Best/worst achievement
            if ($month['achievement'] > $maxAchievement) {
                $maxAchievement = $month['achievement'];
                $bestAchievementMonth = $month;
            }

            if ($month['achievement'] < $minAchievement && $month['target_revenue'] > 0) {
                $minAchievement = $month['achievement'];
                $worstAchievementMonth = $month;
            }
        }

        // Calculate average achievement
        $avgAchievement = $achievementCount > 0 ? $totalAchievement / $achievementCount : 0;

        // Determine trend
        $trend = 'neutral';
        $latestMonths = array_slice($activeMonths, -3); // Get last 3 months with data

        if (count($latestMonths) >= 2) {
            $firstMonth = reset($latestMonths);
            $lastMonth = end($latestMonths);

            if ($lastMonth['real_revenue'] > $firstMonth['real_revenue']) {
                $trend = 'up';
            } elseif ($lastMonth['real_revenue'] < $firstMonth['real_revenue']) {
                $trend = 'down';
            }
        }

        // Generate insight message
        $message = "Performance Account Manager menunjukkan achievement tertinggi pada bulan " .
                  ($bestAchievementMonth ? $bestAchievementMonth['month_name'] : "-") .
                  " dengan nilai " . number_format($maxAchievement, 2) . "% dari target. " .
                  "Revenue tertinggi dicapai pada bulan " .
                  ($bestRevenueMonth ? $bestRevenueMonth['month_name'] : "-") .
                  " dengan Rp " . number_format($maxRevenue, 0, ',', '.') . ".";

        return [
            'best_revenue_month' => $bestRevenueMonth,
            'worst_revenue_month' => $worstRevenueMonth,
            'best_achievement_month' => $bestAchievementMonth,
            'worst_achievement_month' => $worstAchievementMonth,
            'avg_achievement' => $avgAchievement,
            'trend' => $trend,
            'message' => $message
        ];
    }

    /**
     * NEW FUNCTION: Get formatted month name in Indonesian
     */
    private function getIndonesianMonthName($monthNumber)
    {
        $indonesianMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return $indonesianMonths[$monthNumber] ?? 'Unknown';
    }

    /**
     * NEW FUNCTION: Format currency to readable format
     */
    private function formatCurrency($amount)
    {
        if ($amount >= 1000000000) {
            return 'Rp ' . number_format($amount / 1000000000, 2, ',', '.') . ' M';
        } elseif ($amount >= 1000000) {
            return 'Rp ' . number_format($amount / 1000000, 2, ',', '.') . ' Jt';
        } else {
            return 'Rp ' . number_format($amount, 0, ',', '.');
        }
    }

    /**
     * NEW FUNCTION: Get achievement badge class based on percentage
     */
    private function getAchievementBadgeClass($achievement)
    {
        if ($achievement >= 100) {
            return 'badge-success';
        } elseif ($achievement >= 80) {
            return 'badge-warning';
        } else {
            return 'badge-danger';
        }
    }

    /**
     * NEW FUNCTION: Get statistics for detailed customer view
     */
    private function getDetailedCustomerStatistics($accountManager, $year, $divisiId = null, $selectedMonth = 'all')
    {
        $detailedData = $this->getDetailedCustomerRevenues($accountManager, $year, $divisiId, $selectedMonth);

        if ($detailedData->isEmpty()) {
            return [
                'total_records' => 0,
                'total_customers' => 0,
                'total_real_revenue' => 0,
                'total_target_revenue' => 0,
                'average_achievement' => 0,
                'highest_achievement' => 0,
                'lowest_achievement' => 0,
                'achievement_above_100' => 0,
                'achievement_80_to_100' => 0,
                'achievement_below_80' => 0
            ];
        }

        $totalRecords = $detailedData->count();
        $uniqueCustomers = $detailedData->unique('customer_id')->count();
        $totalRealRevenue = $detailedData->sum('real_revenue');
        $totalTargetRevenue = $detailedData->sum('target_revenue');
        $averageAchievement = $detailedData->avg('achievement');
        $highestAchievement = $detailedData->max('achievement');
        $lowestAchievement = $detailedData->min('achievement');

        // Count achievements by categories
        $achievementAbove100 = $detailedData->where('achievement', '>=', 100)->count();
        $achievement80To100 = $detailedData->whereBetween('achievement', [80, 99.99])->count();
        $achievementBelow80 = $detailedData->where('achievement', '<', 80)->count();

        return [
            'total_records' => $totalRecords,
            'total_customers' => $uniqueCustomers,
            'total_real_revenue' => $totalRealRevenue,
            'total_target_revenue' => $totalTargetRevenue,
            'average_achievement' => round($averageAchievement, 2),
            'highest_achievement' => round($highestAchievement, 2),
            'lowest_achievement' => round($lowestAchievement, 2),
            'achievement_above_100' => $achievementAbove100,
            'achievement_80_to_100' => $achievement80To100,
            'achievement_below_80' => $achievementBelow80
        ];
    }

    /**
     * NEW FUNCTION: Get customer revenue comparison between aggregate and detail view
     */
    private function getCustomerRevenueComparison($accountManager, $year, $divisiId = null)
    {
        $aggregateData = $this->getCustomerRevenues($accountManager, $year, $divisiId);
        $detailedData = $this->getDetailedCustomerRevenues($accountManager, $year, $divisiId);

        // Group detailed data by customer
        $detailedGrouped = $detailedData->groupBy('customer_id')->map(function ($group) {
            return [
                'customer_name' => $group->first()->customer_name,
                'nipnas' => $group->first()->nipnas,
                'total_records' => $group->count(),
                'months_count' => $group->unique('month_number')->count(),
                'total_real_revenue' => $group->sum('real_revenue'),
                'total_target_revenue' => $group->sum('target_revenue'),
                'average_achievement' => $group->avg('achievement'),
                'best_month_achievement' => $group->max('achievement'),
                'worst_month_achievement' => $group->min('achievement'),
                'months_list' => $group->pluck('month_name')->unique()->values()->toArray()
            ];
        });

        return [
            'aggregate' => $aggregateData,
            'detailed_grouped' => $detailedGrouped,
            'comparison' => [
                'aggregate_count' => $aggregateData->count(),
                'detailed_unique_customers' => $detailedGrouped->count(),
                'total_detail_records' => $detailedData->count()
            ]
        ];
    }

    /**
     * NEW FUNCTION: Get monthly breakdown summary for a specific customer
     */
    private function getCustomerMonthlyBreakdown($accountManager, $customerId, $year, $divisiId = null)
    {
        $query = DB::table('revenues')
            ->join('corporate_customers', 'revenues.corporate_customer_id', '=', 'corporate_customers.id')
            ->leftJoin('divisi', 'revenues.divisi_id', '=', 'divisi.id')
            ->select(
                'revenues.*',
                'corporate_customers.nama as customer_name',
                'corporate_customers.nipnas',
                'divisi.nama as divisi_name',
                DB::raw('MONTHNAME(revenues.bulan) as month_name'),
                DB::raw('MONTH(revenues.bulan) as month_number'),
                DB::raw('CASE WHEN revenues.target_revenue > 0 THEN (revenues.real_revenue / revenues.target_revenue * 100) ELSE 0 END as achievement')
            )
            ->where('revenues.account_manager_id', $accountManager->id)
            ->where('revenues.corporate_customer_id', $customerId)
            ->whereYear('revenues.bulan', $year);

        if ($divisiId) {
            $query->where('revenues.divisi_id', $divisiId);
        }

        $monthlyData = $query->orderBy('revenues.bulan')
            ->get();

        // Calculate totals and statistics
        $totalRealRevenue = $monthlyData->sum('real_revenue');
        $totalTargetRevenue = $monthlyData->sum('target_revenue');
        $overallAchievement = $totalTargetRevenue > 0 ? ($totalRealRevenue / $totalTargetRevenue * 100) : 0;
        $averageMonthlyAchievement = $monthlyData->avg('achievement');
        $bestMonth = $monthlyData->sortByDesc('achievement')->first();
        $worstMonth = $monthlyData->sortBy('achievement')->first();

        return [
            'monthly_data' => $monthlyData,
            'statistics' => [
                'total_months' => $monthlyData->count(),
                'total_real_revenue' => $totalRealRevenue,
                'total_target_revenue' => $totalTargetRevenue,
                'overall_achievement' => round($overallAchievement, 2),
                'average_monthly_achievement' => round($averageMonthlyAchievement, 2),
                'best_month' => $bestMonth,
                'worst_month' => $worstMonth,
                'months_above_target' => $monthlyData->where('achievement', '>=', 100)->count(),
                'months_below_target' => $monthlyData->where('achievement', '<', 100)->count()
            ]
        ];
    }
}