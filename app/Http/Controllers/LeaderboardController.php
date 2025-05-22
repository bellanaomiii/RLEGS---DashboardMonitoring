<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountManager;
use App\Models\Revenue;
use App\Models\Witel;
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
        $period = $request->input('period', 'all_time'); // Default to 'all_time'

        // Query dasar
        $baseQuery = AccountManager::with(['witel', 'divisi'])
            ->select('account_managers.*');

        // Subquery untuk menghitung total pendapatan dan target
        $revenueSubquery = function ($query) use ($period) {
            $query->from('revenues')
                ->whereColumn('revenues.account_manager_id', 'account_managers.id');

            // Filter berdasarkan periode jika dipilih bulan ini
            if ($period === 'current_month') {
                $currentMonth = Carbon::now()->format('Y-m');
                $query->whereRaw("DATE_FORMAT(revenues.bulan, '%Y-%m') = ?", [$currentMonth]);
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

        // PERUBAHAN: Mendapatkan semua AM untuk menghitung rank secara global
        // Ini dilakukan SEBELUM menerapkan filter pencarian, tapi dengan filter periode dan region
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

        // Jalankan query final dengan semua filter termasuk pencarian
        $accountManagers = $baseQuery->get();

        // Menambahkan rank global ke setiap AM dari perhitungan global
        foreach ($accountManagers as $am) {
            $am->global_rank = $globalRanks[$am->id] ?? 0;
        }

        // Mendapatkan daftar witel untuk dropdown filter
        $witels = Witel::all();

        // Menentukan display period untuk tampilan
        $displayPeriod = 'Peringkat Sepanjang Waktu';
        if ($period === 'current_month') {
            $displayPeriod = 'Peringkat ' . Carbon::now()->format('F Y');
        }

        return view('leaderboardAM', [
            'accountManagers' => $accountManagers,
            'witels' => $witels,
            'displayPeriod' => $displayPeriod,
            'currentPeriod' => $period
        ]);
    }
}