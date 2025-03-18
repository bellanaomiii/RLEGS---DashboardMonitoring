<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Witel;
use App\Models\Divisi;
use App\Models\AccountManager;
use App\Models\Revenue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard dengan data yang sesuai berdasarkan role
     */
    public function index()
    {
        $user = Auth::user();

        // Jika user adalah admin, tampilkan semua data
        if ($user->role === 'admin') {
            $witels = Witel::all();
            $totalRevenue = Revenue::sum('real_revenue');
            $totalTarget = Revenue::sum('target_revenue');
            $achievementPercentage = $totalTarget > 0 ? round(($totalRevenue / $totalTarget) * 100, 2) : 0;

            // Top 10 AM berdasarkan revenue
            $topAMs = DB::table('account_managers')
                ->join('revenues', 'account_managers.id', '=', 'revenues.account_manager_id')
                ->select('account_managers.nama', DB::raw('SUM(revenues.real_revenue) as total_revenue'))
                ->groupBy('account_managers.id', 'account_managers.nama')
                ->orderBy('total_revenue', 'desc')
                ->limit(10)
                ->get();

            // Revenue per bulan
            $monthlyRevenue = DB::table('revenues')
                ->select(DB::raw('MONTH(bulan) as month'),
                         DB::raw('SUM(target_revenue) as target'),
                         DB::raw('SUM(real_revenue) as realisasi'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Tambahkan variable accountManager untuk menghindari error undefined
            $accountManager = null;

            return view('dashboard', compact('witels', 'totalRevenue', 'totalTarget',
                                          'achievementPercentage', 'topAMs', 'monthlyRevenue', 'accountManager', 'user'));
        }
        // Jika user adalah account manager, tampilkan hanya data terkait user tersebut
        else if ($user->role === 'account_manager') {
            // Mendapatkan ID account manager dari user yang login
            $accountManagerId = $user->account_manager_id;

            // Periksa jika account_manager_id tidak null
            if ($accountManagerId) {
                $accountManager = AccountManager::find($accountManagerId);

                if (!$accountManager) {
                    return redirect()->route('profile.edit')
                        ->with('error', 'Data Account Manager tidak ditemukan. Silakan update profil Anda.');
                }

                // Data revenue untuk AM yang sedang login
                $revenues = Revenue::where('account_manager_id', $accountManagerId)->get();

                $totalRevenue = $revenues->sum('real_revenue');
                $totalTarget = $revenues->sum('target_revenue');
                $achievementPercentage = $totalTarget > 0 ? round(($totalRevenue / $totalTarget) * 100, 2) : 0;

                // Revenue per bulan untuk AM yang login
                $monthlyRevenue = DB::table('revenues')
                    ->where('account_manager_id', $accountManagerId)
                    ->select(DB::raw('MONTH(bulan) as month'),
                             DB::raw('SUM(target_revenue) as target'),
                             DB::raw('SUM(real_revenue) as realisasi'))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();

                // Mendapatkan divisi dan witel dari AM
                $divisi = $accountManager->divisi ?? null;
                $witel = $divisi ? $divisi->witel : null;

                return view('dashboard', compact('accountManager', 'totalRevenue', 'totalTarget',
                                             'achievementPercentage', 'monthlyRevenue', 'divisi', 'witel', 'user'));
            } else {
                // Jika account_manager_id null
                $accountManager = null;
                return view('dashboard', compact('accountManager', 'user'))
                    ->with('warning', 'Akun Anda belum terhubung dengan data Account Manager.');
            }
        }

        // Fallback jika role tidak dikenali
        $accountManager = null; // Tetap sediakan variabel ini
        return view('dashboard', compact('accountManager', 'user'))
            ->with('error', 'Role tidak valid.');
    }
}