<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountManager;
use App\Models\Revenue;
use App\Models\Witel;
use App\Models\Divisi;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        // Get all filter parameters
        $search = $request->input('search');
        $filterBy = $request->input('filter_by', []);
        $regionFilter = $request->input('region_filter', []);
        $divisiFilter = $request->input('divisi_filter', []);
        $categoryFilter = $request->input('category_filter', []);
        $period = $request->input('period', 'all_time');
        $perPage = (int) $request->input('per_page', 10); // TAMBAHAN: per_page parameter

        // Query dasar
        $baseQuery = AccountManager::with(['witel', 'divisis'])
            ->select('account_managers.*');

        // Subquery untuk menghitung total pendapatan dan target
        $revenueSubquery = function ($query) use ($period, $divisiFilter) {
            $query->from('revenues')
                ->whereColumn('revenues.account_manager_id', 'account_managers.id');

            // Filter berdasarkan periode jika dipilih bulan ini
            if ($period === 'current_month') {
                $currentMonth = Carbon::now()->format('Y-m');
                $query->whereRaw("DATE_FORMAT(revenues.bulan, '%Y-%m') = ?", [$currentMonth]);
            }

            // Filter berdasarkan divisi jika dipilih
            if (!empty($divisiFilter)) {
                $query->whereIn('revenues.divisi_id', $divisiFilter);
            }
        };

        // Menambahkan subquery untuk total real revenue
        $baseQuery->selectSub(function ($query) use ($revenueSubquery) {
            $query->selectRaw('COALESCE(SUM(revenues.real_revenue), 0)');
            $revenueSubquery($query);
        }, 'total_real_revenue');

        // Menambahkan subquery untuk total target revenue
        $baseQuery->selectSub(function ($query) use ($revenueSubquery) {
            $query->selectRaw('COALESCE(SUM(revenues.target_revenue), 0)');
            $revenueSubquery($query);
        }, 'total_target_revenue');

        // Menambahkan subquery untuk persentase pencapaian (achievement)
        $baseQuery->selectSub(function ($query) use ($revenueSubquery) {
            $query->selectRaw('CASE
                                WHEN COALESCE(SUM(revenues.target_revenue), 0) > 0
                                THEN (COALESCE(SUM(revenues.real_revenue), 0) / COALESCE(SUM(revenues.target_revenue), 0) * 100)
                                ELSE 0
                              END');
            $revenueSubquery($query);
        }, 'achievement_percentage');

        // Apply divisi filter to main query
        if (!empty($divisiFilter)) {
            $baseQuery->whereHas('divisis', function ($query) use ($divisiFilter) {
                $query->whereIn('divisi.id', $divisiFilter);
            });
        }

        // Apply category filter (enterprise/government)
        if (!empty($categoryFilter)) {
            $this->applyCategoryFilter($baseQuery, $categoryFilter);
        }

        // PERUBAHAN: Mendapatkan semua AM untuk menghitung rank secara global
        // Ini dilakukan SEBELUM menerapkan filter pencarian, tapi dengan filter periode, region, divisi, dan kategori
        $globalQuery = clone $baseQuery;

        // Filter berdasarkan region/witel untuk query global
        if (!empty($regionFilter)) {
            $globalQuery->whereHas('witel', function ($query) use ($regionFilter) {
                $query->whereIn('nama', $regionFilter);
            });
        }

        // Menentukan pengurutan
        if (in_array('Achievement Tertinggi', $filterBy)) {
            $globalQuery->orderByDesc('achievement_percentage');
        } else {
            // Default ke Revenue Tertinggi
            $globalQuery->orderByDesc('total_real_revenue');
        }

        // Mendapatkan semua AM tanpa filter pencarian untuk peringkat global
        $allAMs = $globalQuery->get();

        // Menyimpan peringkat global dalam array untuk referensi cepat
        $globalRanks = [];
        foreach ($allAMs as $index => $am) {
            $globalRanks[$am->id] = $index + 1;
        }

        // Sekarang terapkan filter pencarian ke query utama
        if (!empty($search)) {
            $baseQuery->where('account_managers.nama', 'like', '%' . $search . '%');
        }

        // Filter berdasarkan region/witel
        if (!empty($regionFilter)) {
            $baseQuery->whereHas('witel', function ($query) use ($regionFilter) {
                $query->whereIn('nama', $regionFilter);
            });
        }

        // Sorting berdasarkan filter yang dipilih
        if (in_array('Achievement Tertinggi', $filterBy)) {
            $baseQuery->orderByDesc('achievement_percentage');
        } else {
            // Default ke Revenue Tertinggi
            $baseQuery->orderByDesc('total_real_revenue');
        }

        // PERUBAHAN: Jalankan query final dengan pagination
        $accountManagers = $baseQuery->paginate($perPage)->appends(request()->query());

        // Menambahkan rank global dan kategori ke setiap AM dari perhitungan global
        foreach ($accountManagers->items() as $am) { // PERUBAHAN: ->items() untuk paginated data
            $am->global_rank = $globalRanks[$am->id] ?? 0;
            $am->category_info = $this->determineAmCategory($am);
        }

        // Mendapatkan daftar witel untuk dropdown filter
        $witels = Witel::all();

        // Mendapatkan daftar divisi untuk dropdown filter
        $divisis = Divisi::all();

        // Menentukan display period untuk tampilan
        $displayPeriod = 'Peringkat Sepanjang Waktu';
        if ($period === 'current_month') {
            $displayPeriod = 'Peringkat ' . Carbon::now()->format('F Y');
        }

        return view('leaderboardAM', [
            'accountManagers' => $accountManagers,
            'witels' => $witels,
            'divisis' => $divisis,
            'displayPeriod' => $displayPeriod,
            'currentPeriod' => $period,
            'selectedDivisiFilter' => $divisiFilter,
            'selectedCategoryFilter' => $categoryFilter,
            'currentPerPage' => $perPage // TAMBAHAN: current per page value
        ]);
    }

    /**
     * Determine AM category based on their divisions (same logic as detail controller)
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
            // DGS + DSS OR DGS + DPS (but not all three) = MULTI DIVISI
            return [
                'category' => 'MULTI',
                'is_enterprise' => true,
                'is_government' => true,
                'label' => 'Multi Divisi'
            ];
        } elseif ($hasDGS && !$hasDSS && !$hasDPS) {
            // DGS only = GOVERNMENT
            return [
                'category' => 'GOVERNMENT',
                'is_enterprise' => false,
                'is_government' => true,
                'label' => 'Government'
            ];
        } elseif (($hasDPS || $hasDSS) && !$hasDGS) {
            // DPS only OR DSS only OR DPS+DSS (without DGS) = ENTERPRISE
            return [
                'category' => 'ENTERPRISE',
                'is_enterprise' => true,
                'is_government' => false,
                'label' => 'Enterprise'
            ];
        } elseif ($hasDGS && $hasDPS && $hasDSS) {
            // All three divisions = SUPER MULTI
            return [
                'category' => 'MULTI',
                'is_enterprise' => true,
                'is_government' => true,
                'label' => 'Multi Divisi (All)'
            ];
        } else {
            // Default fallback
            return [
                'category' => 'UNKNOWN',
                'is_enterprise' => false,
                'is_government' => false,
                'label' => 'Unknown'
            ];
        }
    }

    /**
     * Apply category filter to query
     */
    private function applyCategoryFilter($query, $categoryFilter)
    {
        if (empty($categoryFilter)) {
            return;
        }

        $query->where(function ($q) use ($categoryFilter) {
            foreach ($categoryFilter as $category) {
                if ($category === 'enterprise') {
                    // Enterprise: AM yang memiliki DPS atau DSS
                    $q->orWhereHas('divisis', function ($divisiQuery) {
                        $divisiQuery->whereIn('nama', ['DPS', 'DSS']);
                    });
                } elseif ($category === 'government') {
                    // Government: AM yang hanya memiliki DGS (tidak memiliki DPS atau DSS)
                    $q->orWhere(function ($govQuery) {
                        $govQuery->whereHas('divisis', function ($divisiQuery) {
                            $divisiQuery->where('nama', 'DGS');
                        })->whereDoesntHave('divisis', function ($divisiQuery) {
                            $divisiQuery->whereIn('nama', ['DPS', 'DSS']);
                        });
                    });
                } elseif ($category === 'multi') {
                    // Multi: AM yang memiliki DGS + (DSS atau DPS)
                    $q->orWhere(function ($multiQuery) {
                        $multiQuery->whereHas('divisis', function ($divisiQuery) {
                            $divisiQuery->where('nama', 'DGS');
                        })->where(function($subQuery) {
                            // Dan memiliki DSS atau DPS
                            $subQuery->whereHas('divisis', function ($divisiQuery) {
                                $divisiQuery->where('nama', 'DSS');
                            })->orWhereHas('divisis', function ($divisiQuery) {
                                $divisiQuery->where('nama', 'DPS');
                            });
                        });
                    });
                }
            }
        });
    }
}