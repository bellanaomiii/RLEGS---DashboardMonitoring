<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Revenue;
use App\Models\Witel;
use App\Models\Divisi;
use App\Models\AccountManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

class WitelPerformController extends Controller
{
    /**
     * Default divisions if none exist in the database
     */
    protected $defaultDivisions = ['DSS', 'DPS', 'DGS', 'RLEGS'];

    /**
     * Default regions if none exist in the database
     */
    protected $defaultRegions = [
        'Suramadu',
        'Nusa Tenggara',
        'Jatim Barat',
        'Yogya Jateng Selatan',
        'Bali',
        'Semarang Jateng Utara',
        'Solo Jateng Timur',
        'Jatim Timur'
    ];

    /**
     * Display Witel performance visualization
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            // Default filter parameters
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $selectedRegion = $request->input('region', 'all');
            $selectedDivisi = $request->input('divisi', 'all');

            // Get all witel with fallback to default if empty
            $witels = Witel::pluck('nama')->toArray();
            if (empty($witels)) {
                Log::info('No witels found in database, using default regions');
                $witels = $this->defaultRegions;
            }

            // Get divisions with fallback to defaults if table is empty
            $divisis = Divisi::pluck('nama')->toArray();
            if (empty($divisis)) {
                Log::info('No divisions found in database, using default divisions');
                $divisis = $this->defaultDivisions;
            }

            // Assign regions (fix for the Undefined variable $regions error)
            $regions = $witels;

            // Get revenue summary data
            $summaryData = $this->getRevenueSummary($startDate, $endDate, $selectedRegion);

            // Prepare chart data for ApexCharts in JavaScript
            $chartData = $this->prepareChartData($selectedRegion, $startDate, $endDate);

            // Log data for debugging
            Log::info('Data prepared for witelPerform view', [
                'regionsCount' => count($regions),
                'witelsCount' => count($witels),
                'divisisCount' => count($divisis),
                'selectedRegion' => $selectedRegion,
                'hasChartData' => !empty($chartData)
            ]);

            // Return view with all necessary variables
            return view('witelPerform', compact(
                'regions',  // This fixes the $regions undefined error
                'witels',
                'divisis',
                'selectedRegion',
                'selectedDivisi',
                'startDate',
                'endDate',
                'summaryData',
                'chartData'
            ));
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Error in WitelPerformController: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());

            // Display error page with message
            return view('error', ['message' => 'Terjadi kesalahan dalam memproses data: ' . $e->getMessage()]);
        }
    }

    /**
     * Get revenue summary data from actual database records
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $region
     * @return array
     */
    private function getRevenueSummary($startDate, $endDate, $region = 'all')
    {
        // Use default divisions for summary
        $divisions = $this->defaultDivisions;
        $data = [];

        try {
            // Get witel IDs based on region
            $witelIds = $this->getWitelIdsByRegion($region);

            // Handle different date periods for comparison to calculate change percentage
            $currentPeriodStart = Carbon::parse($startDate);
            $currentPeriodEnd = Carbon::parse($endDate);
            $daysDifference = $currentPeriodEnd->diffInDays($currentPeriodStart) ?: 30; // Default to 30 days if same date

            $previousPeriodEnd = $currentPeriodStart->copy()->subDay();
            $previousPeriodStart = $previousPeriodEnd->copy()->subDays($daysDifference);

            // For each division, get revenue data
            foreach ($divisions as $division) {
                // Get division ID
                $divisionId = null;
                if ($division !== 'RLEGS') {
                    $divisionId = Divisi::where('nama', $division)->value('id');

                    // If we can't find this division, skip calculations
                    if (!$divisionId && $division !== 'RLEGS') {
                        Log::info("Division {$division} not found in database");
                        $data[$division] = [
                            'total_real' => 0,
                            'total_target' => 0,
                            'percentage_change' => 0,
                            'achievement' => 0
                        ];
                        continue;
                    }
                }

                // Calculate current period data
                $currentPeriodData = $this->getDivisionRevenueForPeriod(
                    $divisionId,
                    $currentPeriodStart->format('Y-m-d'),
                    $currentPeriodEnd->format('Y-m-d'),
                    $witelIds
                );

                // Calculate previous period data
                $previousPeriodData = $this->getDivisionRevenueForPeriod(
                    $divisionId,
                    $previousPeriodStart->format('Y-m-d'),
                    $previousPeriodEnd->format('Y-m-d'),
                    $witelIds
                );

                // Calculate percentage change
                $percentageChange = 0;
                if ($previousPeriodData['total_real'] > 0) {
                    $percentageChange = (($currentPeriodData['total_real'] - $previousPeriodData['total_real']) / $previousPeriodData['total_real']) * 100;
                }

                // Calculate achievement percentage
                $achievement = 0;
                if ($currentPeriodData['total_target'] > 0) {
                    $achievement = ($currentPeriodData['total_real'] / $currentPeriodData['total_target']) * 100;
                }

                // Format numbers for display
                $data[$division] = [
                    'total_real' => $currentPeriodData['total_real'] / 1000000, // Convert to millions
                    'total_target' => $currentPeriodData['total_target'] / 1000000, // Convert to millions
                    'percentage_change' => round($percentageChange, 2),
                    'achievement' => round($achievement, 2)
                ];
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('Error in getRevenueSummary: ' . $e->getMessage());

            // Return default data if error occurs
            foreach ($divisions as $division) {
                $data[$division] = [
                    'total_real' => 0,
                    'total_target' => 0,
                    'percentage_change' => 0,
                    'achievement' => 0
                ];
            }

            return $data;
        }
    }

    /**
     * Get division revenue for a specific period
     *
     * @param int|null $divisionId
     * @param string $startDate
     * @param string $endDate
     * @param array $witelIds
     * @return array
     */
    private function getDivisionRevenueForPeriod($divisionId, $startDate, $endDate, $witelIds = [])
    {
        try {
            // Get account managers IDs based on division and witel
            $accountManagerIds = [];

            if ($divisionId) {
                // If we're filtering by both division and witel
                if (!empty($witelIds) && $witelIds[0] !== 'all') {
                    $accountManagerIds = AccountManager::whereIn('witel_id', $witelIds)
                                        ->where('divisi_id', $divisionId)
                                        ->pluck('id')
                                        ->toArray();
                } else {
                    // Only filtering by division
                    $accountManagerIds = AccountManager::where('divisi_id', $divisionId)
                                        ->pluck('id')
                                        ->toArray();
                }
            } else {
                // RLEGS case (all divisions) or no division specified
                // Filter only by witel if provided
                if (!empty($witelIds) && $witelIds[0] !== 'all') {
                    $accountManagerIds = AccountManager::whereIn('witel_id', $witelIds)
                                        ->pluck('id')
                                        ->toArray();
                } else {
                    // No filters at all, get all account managers
                    $accountManagerIds = AccountManager::pluck('id')->toArray();
                }
            }

            // If no account managers found, return zeros
            if (empty($accountManagerIds)) {
                return [
                    'total_target' => 0,
                    'total_real' => 0
                ];
            }

            // Query revenues for this period and these account managers
            $result = Revenue::whereIn('account_manager_id', $accountManagerIds)
                ->whereBetween('bulan', [$startDate, $endDate])
                ->select(
                    DB::raw('COALESCE(SUM(target_revenue), 0) as total_target'),
                    DB::raw('COALESCE(SUM(real_revenue), 0) as total_real')
                )
                ->first();

            return [
                'total_target' => $result ? $result->total_target : 0,
                'total_real' => $result ? $result->total_real : 0
            ];
        } catch (\Exception $e) {
            Log::error('Error in getDivisionRevenueForPeriod: ' . $e->getMessage());
            return [
                'total_target' => 0,
                'total_real' => 0
            ];
        }
    }

    /**
     * Prepare chart data for ApexCharts
     *
     * @param string $region
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function prepareChartData($region, $startDate, $endDate)
    {
        try {
            // Determine witel IDs based on region
            $witelIds = $this->getWitelIdsByRegion($region);

            // Get monthly data for current year and previous year
            $currentYear = date('Y');
            $previousYear = $currentYear - 1;

            $currentYearData = $this->getMonthlyRevenueData($witelIds, $currentYear, $startDate, $endDate);
            $previousYearData = $this->getMonthlyRevenueData($witelIds, $previousYear, $startDate, $endDate);

            // Get revenue data by division
            $divisionData = $this->getDivisionRevenueData($witelIds, $startDate, $endDate);

            // Get achievement percentage by division
            $achievementData = $this->getDivisionAchievementData($witelIds, $startDate, $endDate);

            // Get performance data for all witels based on actual revenue data
            $witelPerformanceData = $this->getWitelPerformanceData($region, $startDate, $endDate);

            // Format months for the trend chart
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            // Log the data for debugging
            Log::info('Chart data prepared', [
                'currentYearDataPoints' => count(array_filter($currentYearData, function($value) { return $value > 0; })),
                'previousYearDataPoints' => count(array_filter($previousYearData, function($value) { return $value > 0; })),
                'divisionsWithData' => array_keys($divisionData['target']),
                'witelPerformanceCategories' => count($witelPerformanceData['categories'])
            ]);

            // Return all chart data as an array
            return [
                'lineChart' => [
                    'months' => $months,
                    'series' => [
                        [
                            'name' => $currentYear,
                            'data' => array_values($currentYearData)
                        ],
                        [
                            'name' => $previousYear,
                            'data' => array_values($previousYearData)
                        ]
                    ]
                ],
                'barChart' => [
                    'divisions' => array_keys($divisionData['target']),
                    'series' => [
                        [
                            'name' => 'Target',
                            'data' => array_values($divisionData['target'])
                        ],
                        [
                            'name' => 'Realisasi',
                            'data' => array_values($divisionData['real'])
                        ],
                        [
                            'name' => 'Achievement (%)',
                            'data' => array_values($divisionData['achievement'])
                        ]
                    ]
                ],
                'donutChart' => [
                    'labels' => array_keys($achievementData),
                    'series' => array_values($achievementData)
                ],
                'witelPerformance' => $witelPerformanceData
            ];
        } catch (\Exception $e) {
            Log::error('Error in prepareChartData: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());

            // Return empty data structure on error
            return [
                'lineChart' => [
                    'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    'series' => [
                        ['name' => date('Y'), 'data' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]],
                        ['name' => (date('Y')-1), 'data' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]]
                    ]
                ],
                'barChart' => [
                    'divisions' => $this->defaultDivisions,
                    'series' => [
                        ['name' => 'Target', 'data' => [0, 0, 0, 0]],
                        ['name' => 'Realisasi', 'data' => [0, 0, 0, 0]],
                        ['name' => 'Achievement (%)', 'data' => [0, 0, 0, 0]]
                    ]
                ],
                'donutChart' => [
                    'labels' => $this->defaultDivisions,
                    'series' => [0, 0, 0, 0]
                ],
                'witelPerformance' => [
                    'categories' => ['Tidak ada data'],
                    'data' => [0]
                ]
            ];
        }
    }

    /**
     * Get witel IDs by region name
     *
     * @param string $region
     * @return array
     */
    private function getWitelIdsByRegion($region)
    {
        if ($region === 'all' || empty($region)) {
            return ['all'];
        }

        try {
            // Simple matching for demonstration - improve with actual business rules
            $witelIds = Witel::where('nama', 'like', '%' . $region . '%')
                    ->orWhere('nama', '=', $region)
                    ->pluck('id')
                    ->toArray();

            return !empty($witelIds) ? $witelIds : ['all'];
        } catch (\Exception $e) {
            Log::error('Error in getWitelIdsByRegion: ' . $e->getMessage());
            return ['all'];
        }
    }

    /**
     * Get monthly revenue data
     *
     * @param array $witelIds
     * @param int $year
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getMonthlyRevenueData($witelIds, $year, $startDate, $endDate)
    {
        // Initialize results array with zeros for all months
        $results = array_fill(1, 12, 0);

        try {
            // Get account managers IDs based on witel filter
            $accountManagerIds = [];

            if ($witelIds[0] === 'all') {
                $accountManagerIds = AccountManager::pluck('id')->toArray();
            } else {
                $accountManagerIds = AccountManager::whereIn('witel_id', $witelIds)->pluck('id')->toArray();
            }

            if (empty($accountManagerIds)) {
                return $results;
            }

            // Get monthly aggregated revenue data
            $data = Revenue::whereIn('account_manager_id', $accountManagerIds)
                ->whereYear('bulan', $year)
                ->select(
                    DB::raw('MONTH(bulan) as month'),
                    DB::raw('SUM(real_revenue) as total_revenue')
                )
                ->groupBy('month')
                ->get();

            // Log the query results for debugging
            Log::info("Monthly revenue data for year {$year}", [
                'count' => $data->count(),
                'sample' => $data->take(3)
            ]);

            // Populate results with data
            foreach ($data as $item) {
                $results[$item->month] = $item->total_revenue / 1000000; // Convert to millions
            }

            return $results;
        } catch (\Exception $e) {
            Log::error('Error in getMonthlyRevenueData: ' . $e->getMessage());
            return $results;
        }
    }

    /**
     * Get revenue data by division
     *
     * @param array $witelIds
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getDivisionRevenueData($witelIds, $startDate, $endDate)
    {
        // Initialize arrays for each division
        $target = [];
        $real = [];
        $achievement = [];

        try {
            // Get all divisions
            $divisions = Divisi::all();

            // If no divisions exist in DB, use defaults
            if ($divisions->isEmpty()) {
                $defaultData = [];
                foreach ($this->defaultDivisions as $division) {
                    $defaultData[] = (object)['id' => null, 'nama' => $division];
                }
                $divisions = collect($defaultData);
            }

            foreach ($divisions as $division) {
                // Handle the RLEGS special case
                if ($division->nama === 'RLEGS') {
                    // For RLEGS, we might need a different approach
                    // For now, just add a placeholder
                    $target['RLEGS'] = 0;
                    $real['RLEGS'] = 0;
                    $achievement['RLEGS'] = 0;
                    continue;
                }

                // Get account managers IDs
                $accountManagerIds = [];

                // If witelIds is 'all', get all account managers for this division
                if ($witelIds[0] === 'all') {
                    $accountManagerIds = AccountManager::where('divisi_id', $division->id)
                                        ->pluck('id')
                                        ->toArray();
                } else {
                    // Get account managers for this division and witel
                    $accountManagerIds = AccountManager::whereIn('witel_id', $witelIds)
                                        ->where('divisi_id', $division->id)
                                        ->pluck('id')
                                        ->toArray();
                }

                // Skip if no account managers found
                if (empty($accountManagerIds)) {
                    // If this is one of our default divisions, add zeros
                    if (in_array($division->nama, $this->defaultDivisions)) {
                        $target[$division->nama] = 0;
                        $real[$division->nama] = 0;
                        $achievement[$division->nama] = 0;
                    }
                    continue;
                }

                // Get aggregated revenue data
                $revenueData = Revenue::whereIn('account_manager_id', $accountManagerIds)
                    ->whereBetween('bulan', [$startDate, $endDate])
                    ->select(
                        DB::raw('COALESCE(SUM(target_revenue), 0) as total_target'),
                        DB::raw('COALESCE(SUM(real_revenue), 0) as total_real')
                    )
                    ->first();

                // Calculate achievement percentage
                $targetValue = $revenueData ? $revenueData->total_target : 0;
                $realValue = $revenueData ? $revenueData->total_real : 0;
                $achievementValue = $targetValue > 0 ? ($realValue / $targetValue) * 100 : 0;

                // Store data (convert to millions for display)
                $target[$division->nama] = round($targetValue / 1000000, 2);
                $real[$division->nama] = round($realValue / 1000000, 2);
                $achievement[$division->nama] = round($achievementValue, 2);
            }

            // Make sure we have all default divisions in our result
            foreach ($this->defaultDivisions as $defaultDiv) {
                if (!isset($target[$defaultDiv])) {
                    $target[$defaultDiv] = 0;
                    $real[$defaultDiv] = 0;
                    $achievement[$defaultDiv] = 0;
                }
            }

            // Log the result for debugging
            Log::info('Division revenue data', [
                'divisions' => array_keys($target),
                'hasData' => !empty(array_filter($real))
            ]);

            return [
                'target' => $target,
                'real' => $real,
                'achievement' => $achievement
            ];
        } catch (\Exception $e) {
            Log::error('Error in getDivisionRevenueData: ' . $e->getMessage());

            // Return default structure on error
            $defaultData = [];
            foreach ($this->defaultDivisions as $division) {
                $defaultData[$division] = 0;
            }

            return [
                'target' => $defaultData,
                'real' => $defaultData,
                'achievement' => $defaultData
            ];
        }
    }

    /**
     * Get achievement percentage data by division
     *
     * @param array $witelIds
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getDivisionAchievementData($witelIds, $startDate, $endDate)
    {
        // Initialize results array
        $results = [];

        try {
            // Get all divisions
            $divisions = Divisi::all();

            // If no divisions exist in DB, use defaults
            if ($divisions->isEmpty()) {
                $defaultData = [];
                foreach ($this->defaultDivisions as $division) {
                    $defaultData[] = (object)['id' => null, 'nama' => $division];
                }
                $divisions = collect($defaultData);
            }

            foreach ($divisions as $division) {
                // Special case for RLEGS
                if ($division->nama === 'RLEGS') {
                    $results['RLEGS'] = 0;
                    continue;
                }

                // Get account managers IDs
                $accountManagerIds = [];

                // If witelIds is 'all', get all account managers for this division
                if ($witelIds[0] === 'all') {
                    $accountManagerIds = AccountManager::where('divisi_id', $division->id)
                                        ->pluck('id')
                                        ->toArray();
                } else {
                    // Get account managers for this division and witel
                    $accountManagerIds = AccountManager::whereIn('witel_id', $witelIds)
                                        ->where('divisi_id', $division->id)
                                        ->pluck('id')
                                        ->toArray();
                }

                // Skip if no account managers found
                if (empty($accountManagerIds)) {
                    // If this is one of our default divisions, add zeros
                    if (in_array($division->nama, $this->defaultDivisions)) {
                        $results[$division->nama] = 0;
                    }
                    continue;
                }

                // Get aggregated revenue data
                $revenueData = Revenue::whereIn('account_manager_id', $accountManagerIds)
                    ->whereBetween('bulan', [$startDate, $endDate])
                    ->select(
                        DB::raw('COALESCE(SUM(target_revenue), 0) as total_target'),
                        DB::raw('COALESCE(SUM(real_revenue), 0) as total_real')
                    )
                    ->first();

                // Calculate achievement percentage
                $targetValue = $revenueData ? $revenueData->total_target : 0;
                $realValue = $revenueData ? $revenueData->total_real : 0;
                $achievementValue = $targetValue > 0 ? ($realValue / $targetValue) * 100 : 0;

                // Store data
                $results[$division->nama] = round($achievementValue, 2);
            }

            // Make sure we have all default divisions in our result
            foreach ($this->defaultDivisions as $defaultDiv) {
                if (!isset($results[$defaultDiv])) {
                    $results[$defaultDiv] = 0;
                }
            }

            return $results;
        } catch (\Exception $e) {
            Log::error('Error in getDivisionAchievementData: ' . $e->getMessage());

            // Return defaults on error
            $defaultData = [];
            foreach ($this->defaultDivisions as $division) {
                $defaultData[$division] = 0;
            }

            return $defaultData;
        }
    }

    /**
     * Get performance data for all witels based on actual revenue data
     *
     * @param string $region
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getWitelPerformanceData($region, $startDate, $endDate)
    {
        try {
            $witelPerformance = [];

            // Get all witels
            $allWitels = Witel::all();

            if ($allWitels->isEmpty()) {
                return [
                    'categories' => ['Tidak ada data witel'],
                    'data' => [0]
                ];
            }

            foreach ($allWitels as $witel) {
                // Skip if not the selected region and region is not 'all'
                if ($region !== 'all' && $witel->nama !== $region) {
                    continue;
                }

                // Get account managers for this witel
                $accountManagerIds = AccountManager::where('witel_id', $witel->id)->pluck('id')->toArray();

                if (empty($accountManagerIds)) {
                    $witelPerformance[$witel->nama] = 0;
                    continue;
                }

                // Get aggregated revenue data
                $revenueData = Revenue::whereIn('account_manager_id', $accountManagerIds)
                    ->whereBetween('bulan', [$startDate, $endDate])
                    ->select(
                        DB::raw('COALESCE(SUM(target_revenue), 0) as total_target'),
                        DB::raw('COALESCE(SUM(real_revenue), 0) as total_real')
                    )
                    ->first();

                // Calculate achievement percentage
                $targetValue = $revenueData ? $revenueData->total_target : 0;
                $realValue = $revenueData ? $revenueData->total_real : 0;
                $achievementValue = $targetValue > 0 ? ($realValue / $targetValue) * 100 : 0;

                // Store data
                $witelPerformance[$witel->nama] = round($achievementValue, 2);
            }

            // Sort by performance (achievement) in descending order
            arsort($witelPerformance);

            // If no data found, provide defaults
            if (empty($witelPerformance)) {
                return [
                    'categories' => ['Tidak ada data'],
                    'data' => [0]
                ];
            }

            // Log for debugging
            Log::info('Witel performance data', [
                'witelsCount' => count($witelPerformance),
                'topWitel' => key($witelPerformance),
                'topScore' => reset($witelPerformance)
            ]);

            return [
                'categories' => array_keys($witelPerformance),
                'data' => array_values($witelPerformance)
            ];
        } catch (\Exception $e) {
            Log::error('Error in getWitelPerformanceData: ' . $e->getMessage());
            return [
                'categories' => ['Tidak ada data'],
                'data' => [0]
            ];
        }
    }

/**
     * Update charts via AJAX
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCharts(Request $request)
    {
        try {
            $region = $request->input('region', 'all');
            $divisi = $request->input('divisi', 'all');
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // Log incoming request for debugging
            Log::info('Chart update request', [
                'region' => $region,
                'divisi' => $divisi,
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);

            // Generate updated chart data
            $chartData = $this->prepareChartData($region, $startDate, $endDate);

            // Get updated summary data
            $summaryData = $this->getRevenueSummary($startDate, $endDate, $region);

            // Check if we have valid data
            if (empty($chartData) || empty($summaryData)) {
                Log::warning('No chart data or summary data generated for request', [
                    'region' => $region,
                    'divisi' => $divisi,
                    'startDate' => $startDate,
                    'endDate' => $endDate
                ]);

                return response()->json([
                    'error' => 'Tidak ditemukan data untuk parameter yang dipilih',
                    'chartData' => $this->getDefaultChartData(),
                    'summaryData' => $this->getDefaultSummaryData()
                ], 200); // Still return 200 with default data
            }

            // Log successful data generation
            Log::info('Successfully generated chart and summary data for AJAX update', [
                'chartDataExists' => !empty($chartData),
                'summaryDataExists' => !empty($summaryData),
                'region' => $region
            ]);

            return response()->json([
                'chartData' => $chartData,
                'summaryData' => $summaryData
            ]);
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Error in updateCharts: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());

            // Return error response
            return response()->json([
                'error' => 'Terjadi kesalahan dalam memproses data: ' . $e->getMessage(),
                'chartData' => $this->getDefaultChartData(),
                'summaryData' => $this->getDefaultSummaryData()
            ], 500);
        }
    }

    /**
     * Get default chart data for error handling
     *
     * @return array
     */
    private function getDefaultChartData()
    {
        return [
            'lineChart' => [
                'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                'series' => [
                    ['name' => date('Y'), 'data' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]],
                    ['name' => (date('Y')-1), 'data' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]]
                ]
            ],
            'barChart' => [
                'divisions' => $this->defaultDivisions,
                'series' => [
                    ['name' => 'Target', 'data' => [0, 0, 0, 0]],
                    ['name' => 'Realisasi', 'data' => [0, 0, 0, 0]],
                    ['name' => 'Achievement (%)', 'data' => [0, 0, 0, 0]]
                ]
            ],
            'donutChart' => [
                'labels' => $this->defaultDivisions,
                'series' => [0, 0, 0, 0]
            ],
            'witelPerformance' => [
                'categories' => ['Tidak ada data'],
                'data' => [0]
            ]
        ];
    }

    /**
     * Get default summary data for error handling
     *
     * @return array
     */
    private function getDefaultSummaryData()
    {
        $defaultData = [];
        foreach ($this->defaultDivisions as $division) {
            $defaultData[$division] = [
                'total_real' => 0,
                'total_target' => 0,
                'percentage_change' => 0,
                'achievement' => 0
            ];
        }
        return $defaultData;
    }

    /**
     * Filter data by division
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterByDivisi(Request $request)
    {
        try {
            $divisiList = $request->input('divisi', []);
            $region = $request->input('region', 'all');
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // If divisiList is empty, return all data
            if (empty($divisiList)) {
                return $this->updateCharts($request);
            }

            // Log the divisions being filtered
            Log::info('Filtering by divisions', [
                'divisions' => $divisiList,
                'region' => $region
            ]);

            // Generate updated chart data
            $chartData = $this->prepareChartData($region, $startDate, $endDate);

            // Get updated summary data
            $summaryData = $this->getRevenueSummary($startDate, $endDate, $region);

            // Filter the data to only include selected divisions
            if (!empty($chartData) && !empty($summaryData)) {
                // Filter bar chart data
                if (!empty($chartData['barChart'])) {
                    $filteredDivisions = [];
                    $filteredTarget = [];
                    $filteredReal = [];
                    $filteredAchievement = [];

                    foreach ($chartData['barChart']['divisions'] as $index => $division) {
                        if (in_array($division, $divisiList)) {
                            $filteredDivisions[] = $division;
                            $filteredTarget[] = $chartData['barChart']['series'][0]['data'][$index];
                            $filteredReal[] = $chartData['barChart']['series'][1]['data'][$index];
                            $filteredAchievement[] = $chartData['barChart']['series'][2]['data'][$index];
                        }
                    }

                    $chartData['barChart']['divisions'] = $filteredDivisions;
                    $chartData['barChart']['series'][0]['data'] = $filteredTarget;
                    $chartData['barChart']['series'][1]['data'] = $filteredReal;
                    $chartData['barChart']['series'][2]['data'] = $filteredAchievement;
                }

                // Filter donut chart data
                if (!empty($chartData['donutChart'])) {
                    $filteredLabels = [];
                    $filteredSeries = [];

                    foreach ($chartData['donutChart']['labels'] as $index => $label) {
                        if (in_array($label, $divisiList)) {
                            $filteredLabels[] = $label;
                            $filteredSeries[] = $chartData['donutChart']['series'][$index];
                        }
                    }

                    $chartData['donutChart']['labels'] = $filteredLabels;
                    $chartData['donutChart']['series'] = $filteredSeries;
                }

                // Filter summary data
                $filteredSummary = [];
                foreach ($summaryData as $division => $data) {
                    if (in_array($division, $divisiList)) {
                        $filteredSummary[$division] = $data;
                    }
                }
                $summaryData = $filteredSummary;
            }

            return response()->json([
                'chartData' => $chartData,
                'summaryData' => $summaryData
            ]);
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Error in filterByDivisi: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());

            // Return error response
            return response()->json([
                'error' => 'Terjadi kesalahan dalam memproses filter: ' . $e->getMessage(),
                'chartData' => $this->getDefaultChartData(),
                'summaryData' => $this->getDefaultSummaryData()
            ], 500);
        }
    }
}