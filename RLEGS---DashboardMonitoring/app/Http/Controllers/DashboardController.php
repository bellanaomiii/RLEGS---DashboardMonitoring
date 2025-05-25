<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Witel;
use App\Models\Divisi;
use App\Models\AccountManager;
use App\Models\Revenue;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard dengan data yang sesuai berdasarkan role
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ambil parameter tahun dari request, default ke tahun saat ini
        $selectedYear = $request->input('year', date('Y'));

        // Jika user adalah admin, tampilkan semua data
        if ($user->role === 'admin') {
            // Data utama - ambil dari database
            $totalRevenue = Revenue::sum('real_revenue');
            $totalTarget = Revenue::sum('target_revenue');
            $achievementPercentage = $totalTarget > 0 ? round(($totalRevenue / $totalTarget) * 100, 2) : 0;

            // Dapatkan jumlah account manager aktif untuk text informasi
            $activeAccountManagersCount = AccountManager::has('revenues')->count();

            // Top 10 AM berdasarkan revenue dengan relasi witel
            $topAMs = AccountManager::with(['witel', 'divisi'])
                ->select('account_managers.*')
                ->addSelect(DB::raw('(SELECT SUM(real_revenue) FROM revenues WHERE revenues.account_manager_id = account_managers.id) as total_revenue'))
                ->addSelect(DB::raw('(SELECT SUM(target_revenue) FROM revenues WHERE revenues.account_manager_id = account_managers.id) as total_target'))
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('revenues')
                        ->whereColumn('revenues.account_manager_id', 'account_managers.id');
                })
                ->orderBy('total_revenue', 'desc')
                ->limit(10)
                ->get();

            // Hitung achievement percentage untuk setiap AM
            $topAMs->each(function ($am) {
                $am->achievement_percentage = $am->total_target > 0
                    ? round(($am->total_revenue / $am->total_target) * 100, 1)
                    : 0;
            });

            // Revenue per bulan untuk tahun yang dipilih
            $monthlyRevenue = DB::table('revenues')
                ->whereYear('bulan', $selectedYear)
                ->select(
                    DB::raw('MONTH(bulan) as month'),
                    DB::raw('SUM(target_revenue) as target'),
                    DB::raw('SUM(real_revenue) as realisasi')
                )
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Ambil daftar semua tahun yang ada pada data
            $availableYears = DB::table('revenues')
                ->select(DB::raw('DISTINCT YEAR(bulan) as year'))
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            // Pass null for account manager specific data
            $accountManager = null;
            $divisi = null;
            $witel = null;

            return view('dashboard', compact(
                'user', 'totalRevenue', 'totalTarget', 'achievementPercentage',
                'topAMs', 'monthlyRevenue', 'accountManager', 'divisi', 'witel',
                'selectedYear', 'availableYears', 'activeAccountManagersCount'
            ));
        }
        // Jika user adalah account manager
        elseif ($user->role === 'account_manager') {
            // Mendapatkan ID account manager dari user yang login
            $accountManagerId = $user->account_manager_id;

            // Periksa jika account_manager_id tidak null
            if ($accountManagerId) {
                // Ambil data account manager dengan relasi witel dan divisi
                $accountManager = AccountManager::with(['witel', 'divisi'])->find($accountManagerId);

                if (!$accountManager) {
                    return redirect()->route('profile.edit')
                        ->with('error', 'Data Account Manager tidak ditemukan. Silakan update profil Anda.');
                }

                // Data revenue untuk AM yang sedang login
                $totalRevenue = Revenue::where('account_manager_id', $accountManagerId)->sum('real_revenue');
                $totalTarget = Revenue::where('account_manager_id', $accountManagerId)->sum('target_revenue');
                $achievementPercentage = $totalTarget > 0 ? round(($totalRevenue / $totalTarget) * 100, 2) : 0;

                // Revenue per bulan untuk AM yang login dan tahun yang dipilih
                $monthlyRevenue = DB::table('revenues')
                    ->where('account_manager_id', $accountManagerId)
                    ->whereYear('bulan', $selectedYear)
                    ->select(
                        DB::raw('MONTH(bulan) as month'),
                        DB::raw('SUM(target_revenue) as target'),
                        DB::raw('SUM(real_revenue) as realisasi')
                    )
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();

                // Ambil daftar semua tahun yang ada pada data
                $availableYears = DB::table('revenues')
                    ->where('account_manager_id', $accountManagerId)
                    ->select(DB::raw('DISTINCT YEAR(bulan) as year'))
                    ->orderBy('year', 'desc')
                    ->pluck('year')
                    ->toArray();

                // Ambil data divisi dan witel dari relasi
                $divisi = $accountManager->divisi;
                $witel = $accountManager->witel;

                // Buat topAMs kosong untuk menghindari error undefined
                $topAMs = collect();
                $activeAccountManagersCount = 1; // Hanya diri sendiri

                return view('dashboard', compact(
                    'user', 'accountManager', 'totalRevenue', 'totalTarget', 'achievementPercentage',
                    'monthlyRevenue', 'divisi', 'witel', 'topAMs', 'selectedYear', 'availableYears',
                    'activeAccountManagersCount'
                ));
            } else {
                // Jika account_manager_id null
                $accountManager = null;
                $topAMs = collect(); // Inisialisasi koleksi kosong
                $availableYears = [];
                $activeAccountManagersCount = 0;

                return view('dashboard', compact(
                    'user', 'accountManager', 'topAMs', 'selectedYear', 'availableYears',
                    'activeAccountManagersCount'
                ))
                    ->with('warning', 'Akun Anda belum terhubung dengan data Account Manager.');
            }
        }

        // Fallback jika role tidak dikenali
        $accountManager = null;
        $topAMs = collect();
        $availableYears = [];
        $activeAccountManagersCount = 0;

        return view('dashboard', compact(
            'user', 'accountManager', 'topAMs', 'selectedYear', 'availableYears',
            'activeAccountManagersCount'
        ))
            ->with('error', 'Role tidak valid.');
    }

    /**
     * AJAX endpoint untuk mendapatkan data revenue berdasarkan tahun
     */
    public function getRevenuesByYear(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $user = Auth::user();

        if ($user->role === 'admin') {
            // Revenue per bulan untuk tahun yang dipilih (Admin melihat semua)
            $revenues = DB::table('revenues')
                ->whereYear('bulan', $year)
                ->select(
                    DB::raw('MONTH(bulan) as month'),
                    DB::raw('SUM(target_revenue) as target'),
                    DB::raw('SUM(real_revenue) as realisasi')
                )
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        } else {
            // AM hanya melihat datanya sendiri
            $accountManagerId = $user->account_manager_id;

            if (!$accountManagerId) {
                return response()->json(['error' => 'Account Manager tidak valid'], 400);
            }

            $revenues = DB::table('revenues')
                ->where('account_manager_id', $accountManagerId)
                ->whereYear('bulan', $year)
                ->select(
                    DB::raw('MONTH(bulan) as month'),
                    DB::raw('SUM(target_revenue) as target'),
                    DB::raw('SUM(real_revenue) as realisasi')
                )
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }

        return response()->json([
            'data' => $revenues,
            'year' => $year
        ]);
    }
}