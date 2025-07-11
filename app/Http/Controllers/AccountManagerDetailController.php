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
            $customerRevenuesByDivisi[$selectedDivisiId] = $this->getCustomerRevenues($accountManager, $selectedYear, $selectedDivisiId);
        } else {
            // Otherwise get data for all divisi
            foreach ($accountManager->divisis as $divisi) {
                $customerRevenuesByDivisi[$divisi->id] = $this->getCustomerRevenues($accountManager, $selectedYear, $divisi->id);
            }
        }

        // For backward compatibility, also send combined data
        $customerRevenues = $this->getCustomerRevenues($accountManager, $selectedYear);

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

        // ✅ UPDATED: Get total revenue time period (earliest to latest month/year) - seperti calculatePeriodRange
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
            'selectedYear' => $selectedYear,
            'selectedDivisiId' => $selectedDivisiId,
            'selectedCategoryFilter' => $selectedCategoryFilter,
            'amCategory' => $amCategory,
            'needsCategoryFilter' => $needsCategoryFilter,
            'revenuePeriod' => $revenuePeriod
        ]);
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
     * ✅ UPDATED: Get the revenue time period (earliest to latest month/year with data)
     * Mirip dengan calculatePeriodRange() di DashboardController
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
        $message = "Performance Account Manager menunjukkan achievment tertinggi pada bulan " .
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
}