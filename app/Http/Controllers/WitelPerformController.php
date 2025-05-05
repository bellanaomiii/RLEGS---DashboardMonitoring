<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Revenue;
use App\Models\Witel;
use App\Models\Divisi;
use App\Models\AccountManager;
use App\Models\Regional;
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

    public function index(Request $request)
    {
        try {
            // Default filter parameters
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $selectedWitel = $request->input('witel', 'all');
            $selectedRegional = $request->input('regional', 'all');
            $selectedDivisi = $request->input('divisi', 'all');

            // Get all witel with fallback to default if empty
            $witels = Witel::pluck('nama')->toArray();
            if (empty($witels)) {
                $witels = $this->defaultRegions;
            }

            // Get all regionals
            $regionals = Regional::pluck('nama')->toArray();
            if (empty($regionals)) {
                $regionals = ['TREG 1', 'TREG 2', 'TREG 3', 'TREG 4', 'TREG 5', 'TREG 6', 'TREG 7'];
            }

            // Get divisions with fallback to defaults if table is empty
            $divisis = Divisi::pluck('nama')->toArray();
            if (empty($divisis)) {
                $divisis = $this->defaultDivisions;
            }

            // Get revenue summary data
            $summaryData = $this->getRevenueSummary($startDate, $endDate, $selectedWitel, $selectedRegional);

            // Prepare chart data for ApexCharts
            $chartData = $this->prepareChartData($selectedWitel, $selectedRegional, $startDate, $endDate);

            // Set regions for view (backward compatibility)
            $regions = $witels;

            // Return view with all necessary variables
            return view('witelPerform', compact(
                'witels',
                'regionals',
                'divisis',
                'selectedWitel',
                'selectedRegional',
                'selectedDivisi',
                'startDate',
                'endDate',
                'summaryData',
                'chartData',
                'regions'
            ));
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Error in WitelPerformController: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());

            // Display error page with message
            return view('error', ['message' => 'Terjadi kesalahan dalam memproses data: ' . $e->getMessage()]);
        }
    }

    private function prepareChartData($witel, $regional, $startDate, $endDate)
    {
        try {
            // Get account manager IDs based on filters
            $accountManagerIds = $this->getAccountManagerIdsByFilters($witel, $regional);

            // Get monthly target and real revenue data for current year
            $currentYear = date('Y');

            // Get monthly data for target and real revenue
            $targetRevenueData = $this->getMonthlyRevenueData($accountManagerIds, $currentYear, $startDate, $endDate, 'target_revenue');
            $realRevenueData = $this->getMonthlyRevenueData($accountManagerIds, $currentYear, $startDate, $endDate, 'real_revenue');

            // Get revenue data by division
            $divisionData = $this->getDivisionRevenueData($accountManagerIds, $startDate, $endDate);

            // Get achievement percentage by division
            $achievementData = $this->getDivisionAchievementData($accountManagerIds, $startDate, $endDate);

            // Get performance data for all witels
            $witelPerformanceData = $this->getWitelPerformanceData($regional, $startDate, $endDate);

            // Format months for the trend chart
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            // Check if we have any data
            $hasData = false;
            if (!empty(array_filter($targetRevenueData)) ||
                !empty(array_filter($realRevenueData)) ||
                !empty(array_filter($divisionData['real'] ?? [])) ||
                !empty(array_filter($achievementData))) {
                $hasData = true;
            }

            // Return all chart data as an array
            return [
                'isEmpty' => !$hasData,
                'lineChart' => [
                    'months' => $months,
                    'series' => [
                        [
                            'name' => 'Real Revenue',
                            'data' => array_values($realRevenueData)
                        ],
                        [
                            'name' => 'Target Revenue',
                            'data' => array_values($targetRevenueData)
                        ]
                    ]
                ],
                'barChart' => [
                    'divisions' => array_keys($divisionData['target'] ?? $this->getDefaultDivisionData()['target']),
                    'series' => [
                        [
                            'name' => 'Target',
                            'data' => array_values($divisionData['target'] ?? $this->getDefaultDivisionData()['target'])
                        ],
                        [
                            'name' => 'Realisasi',
                            'data' => array_values($divisionData['real'] ?? $this->getDefaultDivisionData()['real'])
                        ],
                        [
                            'name' => 'Achievement (%)',
                            'data' => array_values($divisionData['achievement'] ?? $this->getDefaultDivisionData()['achievement'])
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

            // Return empty data structure on error with error flag
            return [
                'isEmpty' => true,
                'error' => true,
                'message' => $e->getMessage(),
                'lineChart' => [
                    'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    'series' => [
                        ['name' => 'Real Revenue', 'data' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]],
                        ['name' => 'Target Revenue', 'data' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]]
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
     * Get monthly revenue data with option to choose between target_revenue or real_revenue
     *
     * @param array $accountManagerIds
     * @param int $year
     * @param string $startDate
     * @param string $endDate
     * @param string $revenueType 'target_revenue' or 'real_revenue'
     * @return array
     */
    private function getMonthlyRevenueData($accountManagerIds, $year, $startDate, $endDate, $revenueType = 'real_revenue')
    {
        // Initialize results array with zeros for all months
        $results = array_fill(1, 12, 0);

        if (empty($accountManagerIds)) {
            return $results;
        }

        // Get monthly aggregated revenue data
        $data = Revenue::whereIn('account_manager_id', $accountManagerIds)
            ->whereYear('bulan', $year)
            ->select(
                DB::raw('MONTH(bulan) as month'),
                DB::raw("SUM($revenueType) as total_revenue")
            )
            ->groupBy('month')
            ->get();

        // Populate results with data
        foreach ($data as $item) {
            $results[$item->month] = $item->total_revenue / 1000000; // Convert to millions
        }

        return $results;
    }

    // Rest of the methods remain the same...
    // (Keep all other methods unchanged as they are)

    private function getRevenueSummary($startDate, $endDate, $witel = 'all', $regional = 'all')
    {
        $divisions = $this->defaultDivisions;
        $data = [];

        try {
            // Get account manager IDs based on filters
            $accountManagerIds = $this->getAccountManagerIdsByFilters($witel, $regional);

            // Handle different date periods for comparison to calculate change percentage
            $currentPeriodStart = Carbon::parse($startDate);
            $currentPeriodEnd = Carbon::parse($endDate);
            $daysDifference = $currentPeriodEnd->diffInDays($currentPeriodStart) ?: 30;

            $previousPeriodEnd = $currentPeriodStart->copy()->subDay();
            $previousPeriodStart = $previousPeriodEnd->copy()->subDays($daysDifference);

            // For each division, get revenue data
            foreach ($divisions as $division) {
                if ($division === 'RLEGS') {
                    // RLEGS includes all account managers
                    $currentPeriodData = $this->getRevenueForPeriod(
                        $accountManagerIds,
                        $currentPeriodStart->format('Y-m-d'),
                        $currentPeriodEnd->format('Y-m-d')
                    );

                    $previousPeriodData = $this->getRevenueForPeriod(
                        $accountManagerIds,
                        $previousPeriodStart->format('Y-m-d'),
                        $previousPeriodEnd->format('Y-m-d')
                    );
                } else {
                    // Get account managers who have this division
                    $divisionAccountManagers = $this->getAccountManagersByDivision($division, $accountManagerIds);

                    if (empty($divisionAccountManagers)) {
                        $data[$division] = [
                            'total_real' => 0,
                            'total_target' => 0,
                            'percentage_change' => 0,
                            'achievement' => 0
                        ];
                        continue;
                    }

                    $currentPeriodData = $this->getRevenueForPeriod(
                        $divisionAccountManagers,
                        $currentPeriodStart->format('Y-m-d'),
                        $currentPeriodEnd->format('Y-m-d')
                    );

                    $previousPeriodData = $this->getRevenueForPeriod(
                        $divisionAccountManagers,
                        $previousPeriodStart->format('Y-m-d'),
                        $previousPeriodEnd->format('Y-m-d')
                    );
                }

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
     * Get account manager IDs based on witel and regional filters
     *
     * @param string $witel
     * @param string $regional
     * @return array
     */
    private function getAccountManagerIdsByFilters($witel = 'all', $regional = 'all')
    {
        $query = AccountManager::query();

        // Apply regional filter
        if ($regional !== 'all') {
            $regionalId = Regional::where('nama', $regional)->first()?->id;
            if ($regionalId) {
                $query->where('regional_id', $regionalId);
            }
        }

        return $query->pluck('id')->toArray();
    }

    /**
     * Get account managers by division
     *
     * @param string $divisionName
     * @param array $accountManagerIds
     * @return array
     */
    private function getAccountManagersByDivision($divisionName, $accountManagerIds = [])
    {
        $divisionId = Divisi::where('nama', $divisionName)->first()?->id;

        if (!$divisionId) {
            return [];
        }

        $query = DB::table('account_manager_divisi')
            ->where('divisi_id', $divisionId);

        if (!empty($accountManagerIds)) {
            $query->whereIn('account_manager_id', $accountManagerIds);
        }

        return $query->pluck('account_manager_id')->toArray();
    }

    /**
     * Get revenue for specific period
     *
     * @param array $accountManagerIds
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getRevenueForPeriod($accountManagerIds, $startDate, $endDate)
    {
        if (empty($accountManagerIds)) {
            return [
                'total_target' => 0,
                'total_real' => 0
            ];
        }

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
    }

    /**
     * Get revenue data by division
     *
     * @param array $accountManagerIds
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getDivisionRevenueData($accountManagerIds, $startDate, $endDate)
    {
        // Initialize arrays for each division
        $target = [];
        $real = [];
        $achievement = [];

        try {
            foreach ($this->defaultDivisions as $division) {
                if ($division === 'RLEGS') {
                    // RLEGS includes all account managers
                    $data = $this->getRevenueForPeriod($accountManagerIds, $startDate, $endDate);
                } else {
                    // Get account managers who have this division
                    $divisionAccountManagers = $this->getAccountManagersByDivision($division, $accountManagerIds);
                    $data = $this->getRevenueForPeriod($divisionAccountManagers, $startDate, $endDate);
                }

                $target[$division] = round($data['total_target'] / 1000000, 2);
                $real[$division] = round($data['total_real'] / 1000000, 2);
                $achievement[$division] = $data['total_target'] > 0 ?
                    round(($data['total_real'] / $data['total_target']) * 100, 2) : 0;
            }

            return [
                'target' => $target,
                'real' => $real,
                'achievement' => $achievement
            ];
        } catch (\Exception $e) {
            Log::error('Error in getDivisionRevenueData: ' . $e->getMessage());
            return $this->getDefaultDivisionData();
        }
    }

    /**
     * Get achievement percentage data by division
     *
     * @param array $accountManagerIds
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getDivisionAchievementData($accountManagerIds, $startDate, $endDate)
    {
        $results = [];

        try {
            foreach ($this->defaultDivisions as $division) {
                if ($division === 'RLEGS') {
                    // RLEGS includes all account managers
                    $data = $this->getRevenueForPeriod($accountManagerIds, $startDate, $endDate);
                } else {
                    // Get account managers who have this division
                    $divisionAccountManagers = $this->getAccountManagersByDivision($division, $accountManagerIds);
                    $data = $this->getRevenueForPeriod($divisionAccountManagers, $startDate, $endDate);
                }

                $results[$division] = $data['total_target'] > 0 ?
                    round(($data['total_real'] / $data['total_target']) * 100, 2) : 0;
            }

            return $results;
        } catch (\Exception $e) {
            Log::error('Error in getDivisionAchievementData: ' . $e->getMessage());

            // Return default data
            foreach ($this->defaultDivisions as $division) {
                $results[$division] = 0;
            }
            return $results;
        }
    }

    /**
     * Get performance data for all witels
     *
     * @param string $regional
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getWitelPerformanceData($regional, $startDate, $endDate)
    {
        try {
            $witelPerformance = [];

            // Get account manager IDs filtered by regional
            $accountManagerIds = $this->getAccountManagerIdsByFilters('all', $regional);

            // Get all witels
            $allWitels = Witel::all();

            if ($allWitels->isEmpty()) {
                return [
                    'categories' => ['Tidak ada data witel'],
                    'data' => [0]
                ];
            }

            foreach ($allWitels as $witel) {
                // Get account managers for this witel
                $data = $this->getRevenueForPeriod($accountManagerIds, $startDate, $endDate);

                // Calculate achievement percentage
                $achievement = $data['total_target'] > 0 ?
                    round(($data['total_real'] / $data['total_target']) * 100, 2) : 0;

                $witelPerformance[$witel->nama] = $achievement;
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
            $witel = $request->input('witel', 'all');
            $regional = $request->input('regional', 'all');
            $divisi = $request->input('divisi', 'all');
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // Generate updated chart data
            $chartData = $this->prepareChartData($witel, $regional, $startDate, $endDate);

            // Get updated summary data
            $summaryData = $this->getRevenueSummary($startDate, $endDate, $witel, $regional);

            return response()->json([
                'chartData' => $chartData,
                'summaryData' => $summaryData
            ]);
        } catch (\Exception $e) {
            Log::error('Error in updateCharts: ' . $e->getMessage());

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
            'isEmpty' => true,
            'lineChart' => [
                'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                'series' => [
                    ['name' => 'Real Revenue', 'data' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]],
                    ['name' => 'Target Revenue', 'data' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]]
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
     * Get default division data
     *
     * @return array
     */
    private function getDefaultDivisionData()
    {
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

    /**
     * Filter data by witel
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterByWitel(Request $request)
    {
        try {
            $witel = $request->input('witel', 'all');
            $regional = $request->input('regional', 'all');
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // Generate updated chart data
            $chartData = $this->prepareChartData($witel, $regional, $startDate, $endDate);

            // Get updated summary data
            $summaryData = $this->getRevenueSummary($startDate, $endDate, $witel, $regional);

            return response()->json([
                'chartData' => $chartData,
                'summaryData' => $summaryData
            ]);
        } catch (\Exception $e) {
            Log::error('Error in filterByWitel: ' . $e->getMessage());

            return response()->json([
                'error' => 'Terjadi kesalahan dalam memproses filter: ' . $e->getMessage(),
                'chartData' => $this->getDefaultChartData(),
                'summaryData' => $this->getDefaultSummaryData()
            ], 500);
        }
    }

    /**
     * Filter data by regional
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterByRegional(Request $request)
    {
        try {
            $regional = $request->input('regional', 'all');
            $witel = $request->input('witel', 'all');
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // Generate updated chart data
            $chartData = $this->prepareChartData($witel, $regional, $startDate, $endDate);

            // Get updated summary data
            $summaryData = $this->getRevenueSummary($startDate, $endDate, $witel, $regional);

            return response()->json([
                'chartData' => $chartData,
                'summaryData' => $summaryData
            ]);
        } catch (\Exception $e) {
            Log::error('Error in filterByRegional: ' . $e->getMessage());

            return response()->json([
                'error' => 'Terjadi kesalahan dalam memproses filter: ' . $e->getMessage(),
                'chartData' => $this->getDefaultChartData(),
                'summaryData' => $this->getDefaultSummaryData()
            ], 500);
        }
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
            $witel = $request->input('witel', 'all');
            $regional = $request->input('regional', 'all');
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // If divisiList is empty, return all data
            if (empty($divisiList)) {
                return $this->updateCharts($request);
            }

            // Generate updated chart data
            $chartData = $this->prepareChartData($witel, $regional, $startDate, $endDate);

            // Get updated summary data
            $summaryData = $this->getRevenueSummary($startDate, $endDate, $witel, $regional);

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
            Log::error('Error in filterByDivisi: ' . $e->getMessage());

            return response()->json([
                'error' => 'Terjadi kesalahan dalam memproses filter: ' . $e->getMessage(),
                'chartData' => $this->getDefaultChartData(),
                'summaryData' => $this->getDefaultSummaryData()
            ], 500);
        }
    }
}