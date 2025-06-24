<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Witel;
use App\Models\Divisi;
use App\Models\AccountManager;
use App\Models\Revenue;
use App\Models\User;
use App\Models\CorporateCustomer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

        // Ambil parameter kategori untuk witel
        $selectedCategory = $request->input('category', 'all');

        // Route berdasarkan role
        switch ($user->role) {
            case 'admin':
                return $this->adminDashboard($request, $user, $selectedYear);

            case 'account_manager':
                return $this->accountManagerDashboard($request, $user, $selectedYear);

            case 'witel':
                return $this->witelDashboard($request, $user, $selectedYear, $selectedCategory);

            default:
                return $this->defaultDashboard($user);
        }
    }

    /**
     * Dashboard untuk role Admin
     */
    private function adminDashboard($request, $user, $selectedYear)
    {
        // Data utama - ambil dari database
        $totalRevenue = Revenue::sum('real_revenue');
        $totalTarget = Revenue::sum('target_revenue');
        $achievementPercentage = $totalTarget > 0 ? round(($totalRevenue / $totalTarget) * 100, 2) : 0;

        // Dynamic period range untuk admin (seluruh data)
        $periodRange = $this->calculatePeriodRange();

        // Dapatkan jumlah account manager aktif
        $activeAccountManagersCount = AccountManager::has('revenues')->count();

        // Top 10 AM berdasarkan revenue dengan relasi witel dan divisis
        $topAMs = AccountManager::with(['witel', 'divisis', 'user'])
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

        return view('dashboard', compact(
            'user', 'totalRevenue', 'totalTarget', 'achievementPercentage',
            'topAMs', 'monthlyRevenue', 'selectedYear', 'availableYears',
            'activeAccountManagersCount', 'periodRange'
        ));
    }

    /**
     * Dashboard untuk role Account Manager
     */
    private function accountManagerDashboard($request, $user, $selectedYear)
    {
        // Mendapatkan ID account manager dari user yang login
        $accountManagerId = $user->account_manager_id;

        // Periksa jika account_manager_id tidak null
        if (!$accountManagerId) {
            return $this->handleNoAccountManager($user);
        }

        // Ambil data account manager dengan relasi witel dan divisis (PLURAL)
        $accountManager = AccountManager::with(['witel', 'divisis', 'user'])->find($accountManagerId);

        if (!$accountManager) {
            return redirect()->route('profile.edit')
                ->with('error', 'Data Account Manager tidak ditemukan. Silakan update profil Anda.');
        }

        // Dynamic period range untuk AM spesifik
        $periodRange = $this->calculatePeriodRange($accountManagerId);

        // Data revenue untuk AM yang sedang login
        $totalRevenue = Revenue::where('account_manager_id', $accountManagerId)->sum('real_revenue');
        $totalTarget = Revenue::where('account_manager_id', $accountManagerId)->sum('target_revenue');
        $achievementPercentage = $totalTarget > 0 ? round(($totalRevenue / $totalTarget) * 100, 2) : 0;

        // Corporate customers yang di-handle oleh AM ini
        $corporateCustomers = $this->getCorporateCustomersForAM($accountManager);

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

        $activeAccountManagersCount = 1; // Hanya diri sendiri

        return view('dashboard', compact(
            'user', 'accountManager', 'totalRevenue', 'totalTarget', 'achievementPercentage',
            'monthlyRevenue', 'selectedYear', 'availableYears', 'activeAccountManagersCount',
            'corporateCustomers', 'periodRange'
        ));
    }

    /**
     * Dashboard untuk role Witel
     */
    private function witelDashboard($request, $user, $selectedYear, $selectedCategory)
    {
        // Ambil witel dari user yang login
        $witelId = $user->witel_id;

        if (!$witelId) {
            return $this->handleNoWitelData($user);
        }

        $currentWitel = Witel::find($witelId);

        // Dynamic period range untuk witel spesifik
        $periodRange = $this->calculatePeriodRangeForWitel($witelId);

        // Get revenue data untuk witel ini berdasarkan kategori
        $revenueQuery = $this->getWitelRevenueQuery($witelId, $selectedCategory);

        $totalRevenue = $revenueQuery->sum('real_revenue');
        $totalTarget = $revenueQuery->sum('target_revenue');
        $achievementPercentage = $totalTarget > 0 ? round(($totalRevenue / $totalTarget) * 100, 2) : 0;

        // Top 10 AM dalam witel berdasarkan kategori
        $topAMs = $this->getTopAMsForWitel($witelId, $selectedCategory);

        // Jumlah AM aktif dalam witel
        $activeAccountManagersCount = AccountManager::where('witel_id', $witelId)
            ->has('revenues')
            ->count();

        // Revenue per bulan untuk witel dan tahun yang dipilih
        $monthlyRevenue = $this->getMonthlyRevenueForWitel($witelId, $selectedYear, $selectedCategory);

        // Ambil daftar semua tahun yang ada pada data witel
        $availableYears = DB::table('revenues')
            ->join('account_managers', 'revenues.account_manager_id', '=', 'account_managers.id')
            ->where('account_managers.witel_id', $witelId)
            ->select(DB::raw('DISTINCT YEAR(revenues.bulan) as year'))
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        return view('dashboard', compact(
            'user', 'totalRevenue', 'totalTarget', 'achievementPercentage',
            'topAMs', 'monthlyRevenue', 'selectedYear', 'selectedCategory',
            'availableYears', 'activeAccountManagersCount', 'currentWitel', 'periodRange'
        ));
    }

    /**
     * Dashboard default untuk role yang tidak dikenali
     */
    private function defaultDashboard($user)
    {
        $topAMs = collect();
        $availableYears = [];
        $activeAccountManagersCount = 0;
        $accountManager = null;

        return view('dashboard', compact(
            'user', 'accountManager', 'topAMs', 'availableYears',
            'activeAccountManagersCount'
        ))->with('error', 'Role tidak valid.');
    }

    /**
     * Hitung period range dinamis
     */
    private function calculatePeriodRange($accountManagerId = null, $witelId = null)
    {
        $query = DB::table('revenues');

        if ($accountManagerId) {
            $query->where('account_manager_id', $accountManagerId);
        } elseif ($witelId) {
            $query->join('account_managers', 'revenues.account_manager_id', '=', 'account_managers.id')
                  ->where('account_managers.witel_id', $witelId);
        }

        $minDate = $query->min('bulan');
        $maxDate = $query->max('bulan');

        if (!$minDate || !$maxDate) {
            return 'Belum ada data';
        }

        $minMonth = Carbon::parse($minDate)->locale('id')->translatedFormat('M Y');
        $maxMonth = Carbon::parse($maxDate)->locale('id')->translatedFormat('M Y');

        return $minMonth === $maxMonth ? "Periode: $minMonth" : "Periode: $minMonth - $maxMonth";
    }

    /**
     * Hitung period range untuk witel
     */
    private function calculatePeriodRangeForWitel($witelId)
    {
        return $this->calculatePeriodRange(null, $witelId);
    }

    /**
     * Handle case ketika AM tidak memiliki account_manager_id
     */
    private function handleNoAccountManager($user)
    {
        $accountManager = null;
        $topAMs = collect();
        $availableYears = [];
        $activeAccountManagersCount = 0;
        $selectedYear = date('Y');

        return view('dashboard', compact(
            'user', 'accountManager', 'topAMs', 'selectedYear', 'availableYears',
            'activeAccountManagersCount'
        ))->with('warning', 'Akun Anda belum terhubung dengan data Account Manager.');
    }

    /**
     * Handle case ketika witel tidak memiliki data
     */
    private function handleNoWitelData($user)
    {
        return view('dashboard', compact('user'))
            ->with('error', 'Akun Anda belum terhubung dengan data Witel.');
    }

    /**
     * ✅ FIXED: Get corporate customers untuk account manager (tanpa divisi_id dari pivot)
     */
    private function getCorporateCustomersForAM($accountManager)
    {
        // Ambil corporate customers tanpa mencoba akses divisi_id dari pivot table yang salah
        $corporateCustomers = $accountManager->corporateCustomers()
            ->with(['accountManagers' => function($query) use ($accountManager) {
                $query->where('account_managers.id', $accountManager->id);
            }])
            ->get();

        // Jika butuh informasi divisi, ambil dari account manager (bukan dari pivot customer)
        $accountManagerDivisis = $accountManager->divisis; // Relasi yang benar

        return $corporateCustomers->map(function($customer) use ($accountManagerDivisis) {
            // Attach divisi info dari account manager ke setiap customer
            $customer->account_manager_divisis = $accountManagerDivisis;
            return $customer;
        });
    }

    /**
     * Get revenue query untuk witel berdasarkan kategori
     */
    private function getWitelRevenueQuery($witelId, $category)
    {
        $query = Revenue::join('account_managers', 'revenues.account_manager_id', '=', 'account_managers.id')
            ->where('account_managers.witel_id', $witelId);

        // Filter berdasarkan kategori
        switch ($category) {
            case 'enterprise':
                $query->whereExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('account_manager_divisi')
                      ->whereColumn('account_manager_divisi.account_manager_id', 'account_managers.id')
                      ->join('divisi', 'account_manager_divisi.divisi_id', '=', 'divisi.id')
                      ->whereIn('divisi.nama', ['DPS', 'DSS']);
                });
                break;

            case 'government':
                $query->whereExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('account_manager_divisi')
                      ->whereColumn('account_manager_divisi.account_manager_id', 'account_managers.id')
                      ->join('divisi', 'account_manager_divisi.divisi_id', '=', 'divisi.id')
                      ->where('divisi.nama', 'DGS');
                })
                ->whereNotExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('account_manager_divisi')
                      ->whereColumn('account_manager_divisi.account_manager_id', 'account_managers.id')
                      ->join('divisi', 'account_manager_divisi.divisi_id', '=', 'divisi.id')
                      ->whereIn('divisi.nama', ['DPS', 'DSS']);
                });
                break;

            case 'multi':
                $query->whereExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('account_manager_divisi')
                      ->whereColumn('account_manager_divisi.account_manager_id', 'account_managers.id')
                      ->join('divisi', 'account_manager_divisi.divisi_id', '=', 'divisi.id')
                      ->where('divisi.nama', 'DGS');
                })
                ->whereExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('account_manager_divisi')
                      ->whereColumn('account_manager_divisi.account_manager_id', 'account_managers.id')
                      ->join('divisi', 'account_manager_divisi.divisi_id', '=', 'divisi.id')
                      ->whereIn('divisi.nama', ['DPS', 'DSS']);
                });
                break;

            default: // 'all'
                // Tidak ada filter tambahan
                break;
        }

        return $query->select('revenues.*');
    }

    /**
     * Get top AMs untuk witel berdasarkan kategori
     */
    private function getTopAMsForWitel($witelId, $category)
    {
        $query = AccountManager::with(['witel', 'divisis', 'user'])
            ->where('witel_id', $witelId)
            ->select('account_managers.*')
            ->addSelect(DB::raw('(SELECT SUM(real_revenue) FROM revenues WHERE revenues.account_manager_id = account_managers.id) as total_revenue'))
            ->addSelect(DB::raw('(SELECT SUM(target_revenue) FROM revenues WHERE revenues.account_manager_id = account_managers.id) as total_target'))
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('revenues')
                    ->whereColumn('revenues.account_manager_id', 'account_managers.id');
            });

        // Apply category filter
        switch ($category) {
            case 'enterprise':
                $query->whereExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('account_manager_divisi')
                      ->whereColumn('account_manager_divisi.account_manager_id', 'account_managers.id')
                      ->join('divisi', 'account_manager_divisi.divisi_id', '=', 'divisi.id')
                      ->whereIn('divisi.nama', ['DPS', 'DSS']);
                });
                break;

            case 'government':
                $query->whereExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('account_manager_divisi')
                      ->whereColumn('account_manager_divisi.account_manager_id', 'account_managers.id')
                      ->join('divisi', 'account_manager_divisi.divisi_id', '=', 'divisi.id')
                      ->where('divisi.nama', 'DGS');
                })
                ->whereNotExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('account_manager_divisi')
                      ->whereColumn('account_manager_divisi.account_manager_id', 'account_managers.id')
                      ->join('divisi', 'account_manager_divisi.divisi_id', '=', 'divisi.id')
                      ->whereIn('divisi.nama', ['DPS', 'DSS']);
                });
                break;

            case 'multi':
                $query->whereExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('account_manager_divisi')
                      ->whereColumn('account_manager_divisi.account_manager_id', 'account_managers.id')
                      ->join('divisi', 'account_manager_divisi.divisi_id', '=', 'divisi.id')
                      ->where('divisi.nama', 'DGS');
                })
                ->whereExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('account_manager_divisi')
                      ->whereColumn('account_manager_divisi.account_manager_id', 'account_managers.id')
                      ->join('divisi', 'account_manager_divisi.divisi_id', '=', 'divisi.id')
                      ->whereIn('divisi.nama', ['DPS', 'DSS']);
                });
                break;
        }

        $topAMs = $query->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get();

        // Hitung achievement percentage dan kategorisasi untuk setiap AM
        $topAMs->each(function ($am) {
            $am->achievement_percentage = $am->total_target > 0
                ? round(($am->total_revenue / $am->total_target) * 100, 1)
                : 0;

            // Determine category
            $am->category = $this->determineAMCategory($am);
        });

        return $topAMs;
    }

    /**
     * Get monthly revenue untuk witel
     */
    private function getMonthlyRevenueForWitel($witelId, $year, $category)
    {
        $query = DB::table('revenues')
            ->join('account_managers', 'revenues.account_manager_id', '=', 'account_managers.id')
            ->where('account_managers.witel_id', $witelId)
            ->whereYear('revenues.bulan', $year);

        // Apply category filter seperti di getWitelRevenueQuery
        switch ($category) {
            case 'enterprise':
                $query->whereExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('account_manager_divisi')
                      ->whereColumn('account_manager_divisi.account_manager_id', 'account_managers.id')
                      ->join('divisi', 'account_manager_divisi.divisi_id', '=', 'divisi.id')
                      ->whereIn('divisi.nama', ['DPS', 'DSS']);
                });
                break;

            case 'government':
                $query->whereExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('account_manager_divisi')
                      ->whereColumn('account_manager_divisi.account_manager_id', 'account_managers.id')
                      ->join('divisi', 'account_manager_divisi.divisi_id', '=', 'divisi.id')
                      ->where('divisi.nama', 'DGS');
                })
                ->whereNotExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('account_manager_divisi')
                      ->whereColumn('account_manager_divisi.account_manager_id', 'account_managers.id')
                      ->join('divisi', 'account_manager_divisi.divisi_id', '=', 'divisi.id')
                      ->whereIn('divisi.nama', ['DPS', 'DSS']);
                });
                break;

            case 'multi':
                $query->whereExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('account_manager_divisi')
                      ->whereColumn('account_manager_divisi.account_manager_id', 'account_managers.id')
                      ->join('divisi', 'account_manager_divisi.divisi_id', '=', 'divisi.id')
                      ->where('divisi.nama', 'DGS');
                })
                ->whereExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('account_manager_divisi')
                      ->whereColumn('account_manager_divisi.account_manager_id', 'account_managers.id')
                      ->join('divisi', 'account_manager_divisi.divisi_id', '=', 'divisi.id')
                      ->whereIn('divisi.nama', ['DPS', 'DSS']);
                });
                break;
        }

        return $query->select(
                DB::raw('MONTH(revenues.bulan) as month'),
                DB::raw('SUM(revenues.target_revenue) as target'),
                DB::raw('SUM(revenues.real_revenue) as realisasi')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Determine AM category based on divisions
     */
    private function determineAMCategory($accountManager)
    {
        if (!$accountManager->divisis || $accountManager->divisis->isEmpty()) {
            return 'Unknown';
        }

        $divisionNames = $accountManager->divisis->pluck('nama')->toArray();

        $hasDPS = in_array('DPS', $divisionNames);
        $hasDSS = in_array('DSS', $divisionNames);
        $hasDGS = in_array('DGS', $divisionNames);

        if ($hasDGS && ($hasDSS || $hasDPS)) {
            return 'Multi';
        } elseif ($hasDGS && !$hasDSS && !$hasDPS) {
            return 'Government';
        } elseif (($hasDPS || $hasDSS) && !$hasDGS) {
            return 'Enterprise';
        } else {
            return 'Unknown';
        }
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
        } elseif ($user->role === 'account_manager') {
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
        } elseif ($user->role === 'witel') {
            // Witel melihat data sesuai witelnya
            $witelId = $user->witel_id;
            $category = $request->input('category', 'all');

            if (!$witelId) {
                return response()->json(['error' => 'Witel tidak valid'], 400);
            }

            $revenues = $this->getMonthlyRevenueForWitel($witelId, $year, $category);
        } else {
            return response()->json(['error' => 'Role tidak valid'], 400);
        }

        return response()->json([
            'data' => $revenues,
            'year' => $year
        ]);
    }
}