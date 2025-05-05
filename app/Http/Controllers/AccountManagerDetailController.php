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

        // Get the account manager with relationships
        $accountManager = AccountManager::with(['witel', 'divisis', 'corporateCustomers', 'user', 'revenues'])
            ->findOrFail($id);

        // Get global ranking data with previous month comparison
        $globalRanking = $this->getGlobalRanking($accountManager);

        // Get witel ranking data with previous month comparison
        $witelRanking = $this->getWitelRanking($accountManager);

        // Get division ranking data with previous month comparison
        $divisionRanking = $this->getDivisionRanking($accountManager);

        // Get customer revenue data for the selected year
        $customerRevenues = $this->getCustomerRevenues($accountManager, $selectedYear);

        // Get monthly performance data
        $monthlyPerformance = $this->getMonthlyPerformance($accountManager, $selectedYear);

        // Get performance insights
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

        // Get total revenue time period (earliest to latest year)
        $revenuePeriod = $this->getRevenuePeriod($accountManager->id);

        return view('detailAM', [
            'accountManager' => $accountManager,
            'globalRanking' => $globalRanking,
            'witelRanking' => $witelRanking,
            'divisionRanking' => $divisionRanking,
            'customerRevenues' => $customerRevenues,
            'monthlyPerformance' => $monthlyPerformance,
            'insights' => $insights,
            'yearsList' => $yearsList,
            'selectedYear' => $selectedYear,
            'revenuePeriod' => $revenuePeriod
        ]);
    }

    /**
     * Get the revenue time period (earliest to latest year with data)
     */
    private function getRevenuePeriod($accountManagerId)
    {
        $earliestYear = Revenue::where('account_manager_id', $accountManagerId)
            ->orderBy('bulan', 'asc')
            ->value(DB::raw('YEAR(bulan)'));

        $latestYear = Revenue::where('account_manager_id', $accountManagerId)
            ->orderBy('bulan', 'desc')
            ->value(DB::raw('YEAR(bulan)'));

        return [
            'earliest' => $earliestYear ?? Carbon::now()->year,
            'latest' => $latestYear ?? Carbon::now()->year
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
        // FIXED: For ranking, if previousPosition > currentPosition, it's an improvement (up)
        $positionChange = $previousPosition - $currentPosition;

        return [
            'position' => $currentPosition,
            'total' => $allAMs->count(),
            'previous_position' => $previousPosition,
            'position_change' => $positionChange
        ];
    }

    private function getWitelRanking($accountManager)
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

        // Get current month and previous month
        $currentMonth = Carbon::now()->format('m');
        $previousMonth = Carbon::now()->subMonth()->format('m');
        $currentYear = Carbon::now()->format('Y');

        // Get current month ranking within witel
        $witelAMs = AccountManager::where('witel_id', $accountManager->witel_id)
            ->select('account_managers.*')
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

        // Get previous month ranking within witel
        $previousWitelAMs = AccountManager::where('witel_id', $accountManager->witel_id)
            ->select('account_managers.*')
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
        // FIXED: For ranking, if previousPosition > currentPosition, it's an improvement (up)
        $positionChange = $previousPosition - $currentPosition;

        return [
            'position' => $currentPosition,
            'total' => $witelAMs->count(),
            'previous_position' => $previousPosition,
            'position_change' => $positionChange
        ];
    }

    private function getDivisionRanking($accountManager)
    {
        // Check if account manager has any divisions
        if ($accountManager->divisis->isEmpty()) {
            return [
                'position' => 'N/A',
                'total' => 0,
                'previous_position' => 'N/A',
                'position_change' => 0
            ];
        }

        // Get the first division (assuming account manager has at least one)
        $divisionId = $accountManager->divisis->first()->id;

        // Get current month and previous month
        $currentMonth = Carbon::now()->format('m');
        $previousMonth = Carbon::now()->subMonth()->format('m');
        $currentYear = Carbon::now()->format('Y');

        // Get current month ranking within division
        $divisionAMs = AccountManager::whereHas('divisis', function($query) use ($divisionId) {
                $query->where('divisi_id', $divisionId);
            })
            ->select('account_managers.*')
            ->selectSub(function ($query) use ($currentMonth, $currentYear) {
                $query->selectRaw('COALESCE(SUM(revenues.real_revenue), 0)')
                    ->from('revenues')
                    ->whereColumn('revenues.account_manager_id', 'account_managers.id')
                    ->whereMonth('revenues.bulan', $currentMonth)
                    ->whereYear('revenues.bulan', $currentYear);
            }, 'current_revenue')
            ->orderByDesc('current_revenue')
            ->get();

        // Find the position of current AM within division
        $currentPosition = $divisionAMs->search(function ($item) use ($accountManager) {
            return $item->id === $accountManager->id;
        });

        // Add +1 because index starts at 0, but handle case where AM is not found
        $currentPosition = $currentPosition !== false ? $currentPosition + 1 : count($divisionAMs);

        // Get previous month ranking within division
        $previousDivisionAMs = AccountManager::whereHas('divisis', function($query) use ($divisionId) {
                $query->where('divisi_id', $divisionId);
            })
            ->select('account_managers.*')
            ->selectSub(function ($query) use ($previousMonth, $currentYear) {
                $query->selectRaw('COALESCE(SUM(revenues.real_revenue), 0)')
                    ->from('revenues')
                    ->whereColumn('revenues.account_manager_id', 'account_managers.id')
                    ->whereMonth('revenues.bulan', $previousMonth)
                    ->whereYear('revenues.bulan', $currentYear);
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
        // FIXED: For ranking, if previousPosition > currentPosition, it's an improvement (up)
        $positionChange = $previousPosition - $currentPosition;

        return [
            'position' => $currentPosition,
            'total' => $divisionAMs->count(),
            'previous_position' => $previousPosition,
            'position_change' => $positionChange
        ];
    }

    private function getCustomerRevenues($accountManager, $year)
    {
        // Get customer revenues for the selected year
        $customerRevenueData = DB::table('revenues')
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
            ->whereYear('revenues.bulan', $year)
            ->groupBy('corporate_customers.id', 'corporate_customers.nama', 'corporate_customers.nipnas')
            ->orderByDesc('total_revenue')
            ->get();

        return $customerRevenueData;
    }

    private function getMonthlyPerformance($accountManager, $year)
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

        // Get monthly revenue data from database
        $monthlyRevenues = DB::table('revenues')
            ->select(
                DB::raw('MONTH(bulan) as month'),
                DB::raw('SUM(real_revenue) as real_revenue'),
                DB::raw('SUM(target_revenue) as target_revenue'),
                DB::raw('CASE WHEN SUM(target_revenue) > 0 THEN (SUM(real_revenue) / SUM(target_revenue) * 100) ELSE 0 END as achievement')
            )
            ->where('account_manager_id', $accountManager->id)
            ->whereYear('bulan', $year)
            ->groupBy(DB::raw('MONTH(bulan)'))
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
        $message = "Performance Account Manager menunjukkan pencapaian tertinggi pada bulan " .
                  ($bestAchievementMonth ? $bestAchievementMonth['month_name'] : "-") .
                  " dengan nilai " . number_format($maxAchievement, 2) . "% dari target. " .
                  "Pendapatan tertinggi dicapai pada bulan " .
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