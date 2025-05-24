<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Revenue;
use App\Models\AccountManager;
use App\Models\CorporateCustomer;
use App\Models\Witel;
use App\Models\Divisi;
use App\Models\Regional;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class RevenueController extends Controller
{
    /**
     * ✅ IMPROVED: Main index method dengan filter dan search yang lebih baik
     */
    public function index(Request $request)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // ✅ NEW: Deteksi apakah ini adalah request search/filter
        $isSearching = $request->filled('search') ||
                      $request->filled('witel') ||
                      $request->filled('regional') ||
                      $request->filled('divisi') ||
                      $request->filled('month') ||
                      $request->filled('year');

        // ✅ IMPROVED: Build filtered queries
        $filters = $this->buildFilters($request);

        // Apply filters to all queries
        $revenuesQuery = $this->applyFilters(Revenue::with(['accountManager.witel', 'accountManager.regional', 'corporateCustomer', 'divisi']), $filters, 'revenue');
        $accountManagersQuery = $this->applyFilters(AccountManager::with(['witel', 'divisis', 'regional']), $filters, 'account_manager');
        $corporateCustomersQuery = $this->applyFilters(CorporateCustomer::query(), $filters, 'corporate_customer');

        // ✅ IMPROVED: Sorting
        $revenuesQuery->orderBy('bulan', 'desc');
        $accountManagersQuery->orderBy('nama', 'asc');
        $corporateCustomersQuery->orderBy('nama', 'asc');

        // ✅ IMPROVED: Pagination dengan preserve query parameters
        $perPage = $request->input('per_page', 10);

        $revenues = $revenuesQuery->paginate($perPage)->appends($request->query());
        $accountManagers = $accountManagersQuery->paginate($perPage)->appends($request->query());
        $corporateCustomers = $corporateCustomersQuery->paginate($perPage)->appends($request->query());

        // Data untuk filter dropdowns
        $witels = Witel::orderBy('nama')->get();
        $divisi = Divisi::orderBy('nama')->get();
        $regionals = Regional::orderBy('nama')->get();

        // Rentang tahun untuk filter
        $currentYear = Carbon::now()->year;
        $yearRange = range(2020, $currentYear + 5);

        // ✅ NEW: Hitung statistik untuk info user
        $stats = $this->calculateStats($filters);

        // ✅ IMPROVED: Response berbeda untuk AJAX vs normal request
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('partials.revenue-tables', compact('revenues', 'accountManagers', 'corporateCustomers', 'isSearching', 'stats'))->render(),
                'stats' => $stats
            ]);
        }

        return view('revenueData', compact(
            'revenues',
            'accountManagers',
            'corporateCustomers',
            'witels',
            'divisi',
            'regionals',
            'yearRange',
            'isSearching',
            'stats'
        ));
    }

    /**
     * ✅ NEW: Build filters array dari request
     */
    private function buildFilters(Request $request)
    {
        $filters = [];

        if ($request->filled('search')) {
            $filters['search'] = trim($request->search);
        }

        if ($request->filled('witel')) {
            $filters['witel'] = $request->witel;
        }

        if ($request->filled('regional')) {
            $filters['regional'] = $request->regional;
        }

        if ($request->filled('divisi')) {
            $filters['divisi'] = $request->divisi;
        }

        if ($request->filled('month')) {
            $filters['month'] = $request->month;
        }

        if ($request->filled('year')) {
            $filters['year'] = $request->year;
        }

        return $filters;
    }

    /**
     * ✅ NEW: Apply filters ke query builder
     */
    private function applyFilters($query, $filters, $type)
    {
        // Global search filter
        if (isset($filters['search'])) {
            $search = $filters['search'];

            switch ($type) {
                case 'revenue':
                    $query->where(function ($q) use ($search) {
                        $q->whereHas('accountManager', function ($subQ) use ($search) {
                            $subQ->where('nama', 'LIKE', "%{$search}%")
                                 ->orWhere('nik', 'LIKE', "%{$search}%");
                        })->orWhereHas('corporateCustomer', function ($subQ) use ($search) {
                            $subQ->where('nama', 'LIKE', "%{$search}%")
                                 ->orWhere('nipnas', 'LIKE', "%{$search}%");
                        })->orWhereHas('divisi', function ($subQ) use ($search) {
                            $subQ->where('nama', 'LIKE', "%{$search}%");
                        });
                    });
                    break;

                case 'account_manager':
                    $query->where(function ($q) use ($search) {
                        $q->where('nama', 'LIKE', "%{$search}%")
                          ->orWhere('nik', 'LIKE', "%{$search}%")
                          ->orWhereHas('witel', function ($subQ) use ($search) {
                              $subQ->where('nama', 'LIKE', "%{$search}%");
                          })->orWhereHas('divisis', function ($subQ) use ($search) {
                              $subQ->where('nama', 'LIKE', "%{$search}%");
                          });
                    });
                    break;

                case 'corporate_customer':
                    $query->where(function ($q) use ($search) {
                        $q->where('nama', 'LIKE', "%{$search}%")
                          ->orWhere('nipnas', 'LIKE', "%{$search}%");
                    });
                    break;
            }
        }

        // Witel filter
        if (isset($filters['witel'])) {
            switch ($type) {
                case 'revenue':
                    $query->whereHas('accountManager', function ($q) use ($filters) {
                        $q->where('witel_id', $filters['witel']);
                    });
                    break;
                case 'account_manager':
                    $query->where('witel_id', $filters['witel']);
                    break;
            }
        }

        // Regional filter
        if (isset($filters['regional'])) {
            switch ($type) {
                case 'revenue':
                    $query->whereHas('accountManager', function ($q) use ($filters) {
                        $q->where('regional_id', $filters['regional']);
                    });
                    break;
                case 'account_manager':
                    $query->where('regional_id', $filters['regional']);
                    break;
            }
        }

        // Divisi filter
        if (isset($filters['divisi'])) {
            switch ($type) {
                case 'revenue':
                    $query->where('divisi_id', $filters['divisi']);
                    break;
                case 'account_manager':
                    $query->whereHas('divisis', function ($q) use ($filters) {
                        $q->where('divisi.id', $filters['divisi']);
                    });
                    break;
            }
        }

        // Month filter (hanya untuk revenue)
        if (isset($filters['month']) && $type === 'revenue') {
            $query->whereMonth('bulan', $filters['month']);
        }

        // Year filter (hanya untuk revenue)
        if (isset($filters['year']) && $type === 'revenue') {
            $query->whereYear('bulan', $filters['year']);
        }

        return $query;
    }

    /**
     * ✅ NEW: Calculate statistics untuk info user
     */
    private function calculateStats($filters)
    {
        $stats = [];

        // Total revenue records
        $revenueQuery = Revenue::query();
        $revenueQuery = $this->applyFilters($revenueQuery, $filters, 'revenue');
        $stats['total_revenues'] = $revenueQuery->count();

        // Total account managers
        $amQuery = AccountManager::query();
        $amQuery = $this->applyFilters($amQuery, $filters, 'account_manager');
        $stats['total_account_managers'] = $amQuery->count();

        // Total corporate customers
        $ccQuery = CorporateCustomer::query();
        $ccQuery = $this->applyFilters($ccQuery, $filters, 'corporate_customer');
        $stats['total_corporate_customers'] = $ccQuery->count();

        // Sum of real revenue (hanya jika ada filter)
        if (!empty($filters)) {
            $revenueSum = Revenue::query();
            $revenueSum = $this->applyFilters($revenueSum, $filters, 'revenue');
            $stats['total_real_revenue'] = $revenueSum->sum('real_revenue');
            $stats['total_target_revenue'] = $revenueSum->sum('target_revenue');
        }

        return $stats;
    }

    /**
     * Fungsi untuk halaman filter data revenue (alias untuk index)
     */
    public function data(Request $request)
    {
        return $this->index($request);
    }

    /**
     * ✅ IMPROVED: Store method dengan validasi yang lebih ketat
     */
    public function store(Request $request)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk menambahkan data revenue.'
                ], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk menambahkan data revenue.');
        }

        // Validasi data input
        $validator = Validator::make($request->all(), [
            'account_manager_id' => 'required|exists:account_managers,id',
            'divisi_id' => 'required|exists:divisi,id',
            'corporate_customer_id' => 'required|exists:corporate_customers,id',
            'target_revenue' => 'required|numeric|min:0',
            'real_revenue' => 'required|numeric|min:0',
            'bulan_month' => 'required|string|in:01,02,03,04,05,06,07,08,09,10,11,12',
            'bulan_year' => 'required|numeric|min:2000|max:2100',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $bulan = $request->bulan_year . '-' . $request->bulan_month . '-01';

        try {
            // ✅ IMPROVED: Verifikasi relasi AM-Divisi
            $accountManager = AccountManager::findOrFail($request->account_manager_id);
            $divisiExists = $accountManager->divisis()->where('divisi.id', $request->divisi_id)->exists();

            if (!$divisiExists) {
                throw new \Exception('Divisi yang dipilih tidak terkait dengan Account Manager.');
            }

            // Cek duplikasi data
            $existingRevenue = Revenue::where([
                'account_manager_id' => $request->account_manager_id,
                'divisi_id' => $request->divisi_id,
                'corporate_customer_id' => $request->corporate_customer_id,
            ])->whereYear('bulan', $request->bulan_year)
              ->whereMonth('bulan', $request->bulan_month)
              ->first();

            if ($existingRevenue) {
                // Update data yang sudah ada
                $existingRevenue->update([
                    'target_revenue' => $request->target_revenue,
                    'real_revenue' => $request->real_revenue
                ]);
                $message = 'Data Revenue berhasil diperbarui.';
            } else {
                // Buat data baru
                Revenue::create([
                    'account_manager_id' => $request->account_manager_id,
                    'divisi_id' => $request->divisi_id,
                    'corporate_customer_id' => $request->corporate_customer_id,
                    'target_revenue' => $request->target_revenue,
                    'real_revenue' => $request->real_revenue,
                    'bulan' => $bulan
                ]);
                $message = 'Data Revenue berhasil ditambahkan.';
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return redirect()->route('revenue.index')->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error saat menyimpan revenue: ' . $e->getMessage(), [
                'account_manager_id' => $request->account_manager_id,
                'divisi_id' => $request->divisi_id,
                'corporate_customer_id' => $request->corporate_customer_id,
                'target_revenue' => $request->target_revenue,
                'real_revenue' => $request->real_revenue,
                'bulan' => $bulan
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan revenue: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('revenue.index')->with('error', 'Gagal menyimpan revenue: ' . $e->getMessage());
        }
    }

    /**
     * Edit data revenue
     */
    public function edit($id)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengedit data revenue.');
        }

        try {
            $revenue = Revenue::with(['accountManager', 'corporateCustomer', 'divisi'])->findOrFail($id);
            $accountManagers = AccountManager::with(['witel', 'divisis', 'regional'])->orderBy('nama')->get();
            $corporateCustomers = CorporateCustomer::orderBy('nama')->get();
            $witels = Witel::orderBy('nama')->get();
            $divisi = Divisi::orderBy('nama')->get();
            $regionals = Regional::orderBy('nama')->get();

            // Parse bulan untuk tampilan form
            $bulanParts = explode('-', $revenue->bulan);
            $year = $bulanParts[0];
            $month = $bulanParts[1];

            return view('revenue.edit', compact('revenue', 'accountManagers', 'corporateCustomers', 'witels', 'divisi', 'regionals', 'year', 'month'));
        } catch (\Exception $e) {
            Log::error('Error loading edit form: ' . $e->getMessage());
            return redirect()->route('revenue.index')->with('error', 'Gagal memuat form edit: ' . $e->getMessage());
        }
    }

    /**
     * ✅ IMPROVED: Update method dengan validasi yang lebih ketat
     */
    public function update(Request $request, $id)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk memperbarui data revenue.'
                ], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk memperbarui data revenue.');
        }

        // Validasi data input
        $validator = Validator::make($request->all(), [
            'account_manager_id' => 'required|exists:account_managers,id',
            'divisi_id' => 'required|exists:divisi,id',
            'corporate_customer_id' => 'required|exists:corporate_customers,id',
            'target_revenue' => 'required|numeric|min:0',
            'real_revenue' => 'required|numeric|min:0',
            'bulan_month' => 'required|string|in:01,02,03,04,05,06,07,08,09,10,11,12',
            'bulan_year' => 'required|numeric|min:2000|max:2100',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $bulan = $request->bulan_year . '-' . $request->bulan_month . '-01';

        try {
            // Verifikasi bahwa divisi_id memang terkait dengan account_manager_id
            $accountManager = AccountManager::findOrFail($request->account_manager_id);
            $divisiExists = $accountManager->divisis()->where('divisi.id', $request->divisi_id)->exists();

            if (!$divisiExists) {
                throw new \Exception('Divisi yang dipilih tidak terkait dengan Account Manager.');
            }

            $revenue = Revenue::findOrFail($id);

            // Update data revenue
            $revenue->update([
                'account_manager_id' => $request->account_manager_id,
                'divisi_id' => $request->divisi_id,
                'corporate_customer_id' => $request->corporate_customer_id,
                'target_revenue' => $request->target_revenue,
                'real_revenue' => $request->real_revenue,
                'bulan' => $bulan
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Revenue berhasil diperbarui.'
                ]);
            }

            return redirect()->route('revenue.index')->with('success', 'Revenue berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error saat memperbarui revenue: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui revenue: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('revenue.index')->with('error', 'Gagal memperbarui revenue: ' . $e->getMessage());
        }
    }

    /**
     * Hapus data revenue
     */
    public function destroy($id)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk menghapus data revenue.');
        }

        try {
            $revenue = Revenue::findOrFail($id);
            $revenue->delete();

            return redirect()->route('revenue.index')->with('success', 'Revenue berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error saat menghapus revenue: ' . $e->getMessage());
            return redirect()->route('revenue.index')->with('error', 'Gagal menghapus revenue: ' . $e->getMessage());
        }
    }

    /**
     * ✅ IMPROVED: Enhanced global search dengan info lebih detail
     */
    public function search(Request $request)
    {
        $search = $request->input('search');

        if (empty($search)) {
            return response()->json([
                'success' => true,
                'accountManagers' => [],
                'corporateCustomers' => [],
                'revenues' => [],
                'stats' => [
                    'total_results' => 0,
                    'account_managers_count' => 0,
                    'corporate_customers_count' => 0,
                    'revenues_count' => 0
                ]
            ]);
        }

        try {
            // Search Account Managers
            $accountManagers = AccountManager::where(function ($query) use ($search) {
                $query->where('nama', 'LIKE', "%{$search}%")
                      ->orWhere('nik', 'LIKE', "%{$search}%");
            })
            ->with(['witel', 'divisis', 'regional'])
            ->orderBy('nama')
            ->limit(10)
            ->get();

            // Search Corporate Customers
            $corporateCustomers = CorporateCustomer::where(function ($query) use ($search) {
                $query->where('nama', 'LIKE', "%{$search}%")
                      ->orWhere('nipnas', 'LIKE', "%{$search}%");
            })
            ->orderBy('nama')
            ->limit(10)
            ->get();

            // Search Revenues
            $revenues = Revenue::with(['accountManager', 'corporateCustomer', 'divisi'])
                ->where(function ($query) use ($search) {
                    $query->whereHas('accountManager', function ($q) use ($search) {
                        $q->where('nama', 'LIKE', "%{$search}%")
                          ->orWhere('nik', 'LIKE', "%{$search}%");
                    })->orWhereHas('corporateCustomer', function ($q) use ($search) {
                        $q->where('nama', 'LIKE', "%{$search}%")
                          ->orWhere('nipnas', 'LIKE', "%{$search}%");
                    })->orWhereHas('divisi', function ($q) use ($search) {
                        $q->where('nama', 'LIKE', "%{$search}%");
                    });
                })
                ->orderBy('bulan', 'desc')
                ->limit(10)
                ->get();

            $stats = [
                'total_results' => $accountManagers->count() + $corporateCustomers->count() + $revenues->count(),
                'account_managers_count' => $accountManagers->count(),
                'corporate_customers_count' => $corporateCustomers->count(),
                'revenues_count' => $revenues->count()
            ];

            return response()->json([
                'success' => true,
                'accountManagers' => $accountManagers,
                'corporateCustomers' => $corporateCustomers,
                'revenues' => $revenues,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error dalam search: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan pencarian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NEW: Quick filter - untuk filter cepat tanpa reload
     */
    public function quickFilter(Request $request)
    {
        try {
            $filters = $this->buildFilters($request);

            // Build queries dengan filter
            $revenuesQuery = $this->applyFilters(Revenue::with(['accountManager.witel', 'corporateCustomer', 'divisi']), $filters, 'revenue');
            $accountManagersQuery = $this->applyFilters(AccountManager::with(['witel', 'divisis', 'regional']), $filters, 'account_manager');
            $corporateCustomersQuery = $this->applyFilters(CorporateCustomer::query(), $filters, 'corporate_customer');

            // Get results
            $revenues = $revenuesQuery->orderBy('bulan', 'desc')->limit(20)->get();
            $accountManagers = $accountManagersQuery->orderBy('nama')->limit(20)->get();
            $corporateCustomers = $corporateCustomersQuery->orderBy('nama')->limit(20)->get();

            $stats = $this->calculateStats($filters);

            return response()->json([
                'success' => true,
                'revenues' => $revenues,
                'accountManagers' => $accountManagers,
                'corporateCustomers' => $corporateCustomers,
                'stats' => $stats,
                'has_filters' => !empty($filters)
            ]);
        } catch (\Exception $e) {
            Log::error('Error dalam quick filter: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan filter: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NEW: Reset filter - kembali ke tampilan normal
     */
    public function resetFilter(Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'redirect' => route('revenue.index')
            ]);
        }

        return redirect()->route('revenue.index');
    }

    /**
     * Fungsi untuk export data revenue
     */
    public function export()
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('revenue.index')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengekspor data revenue.');
        }

        try {
            return Excel::download(new \App\Exports\RevenueExport, 'revenue-data-' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Error saat export: ' . $e->getMessage());
            return redirect()->route('revenue.index')->with('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }

    /**
     * ✅ IMPROVED: Import method dengan detailed error handling
     */
    public function import(Request $request)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengimpor data revenue.'
            ], 403);
        }

        // Validasi file input
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // ✅ Increased to 10MB
            'year' => 'nullable|numeric|min:2000|max:2100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // ✅ IMPROVED: Create import instance dengan year parameter
            $year = $request->input('year', date('Y'));
            $import = new \App\Imports\RevenueImport($year);

            // Set memory limit dan timeout untuk file besar
            ini_set('memory_limit', '1024M');
            set_time_limit(300); // 5 minutes

            // Import file
            Excel::import($import, $request->file('file'));

            // ✅ IMPROVED: Get detailed results
            $results = $import->getImportResults();

            // ✅ NEW: Build response berdasarkan hasil import
            if ($results['errors'] > 0) {
                // Ada error - return dengan detail error untuk popup
                return response()->json([
                    'success' => false,
                    'message' => 'Import selesai dengan beberapa error',
                    'data' => [
                        'imported' => $results['imported'],
                        'duplicates' => $results['duplicates'],
                        'errors' => $results['errors'],
                        'error_details' => $results['error_details'], // ✅ Detail baris yang error
                        'total_processed' => $results['imported'] + $results['duplicates'] + $results['errors']
                    ],
                    'show_popup' => true, // ✅ Flag untuk popup
                    'popup_type' => 'warning',
                    'popup_title' => 'Import Selesai dengan Warning',
                    'require_manual_close' => true // ✅ Manual close popup
                ]);
            } else {
                // Semua berhasil
                return response()->json([
                    'success' => true,
                    'message' => 'Data revenue berhasil diimpor semuanya!',
                    'data' => [
                        'imported' => $results['imported'],
                        'duplicates' => $results['duplicates'],
                        'errors' => 0,
                        'total_processed' => $results['imported'] + $results['duplicates']
                    ],
                    'show_popup' => true,
                    'popup_type' => 'success',
                    'popup_title' => 'Import Berhasil',
                    'require_manual_close' => false // ✅ Auto close untuk success
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error saat mengimpor revenue: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimpor data: ' . $e->getMessage(),
                'show_popup' => true,
                'popup_type' => 'error',
                'popup_title' => 'Import Gagal',
                'require_manual_close' => true
            ], 500);
        }
    }

    /**
     * Fungsi untuk mengunduh template Excel untuk import data
     */
    public function template()
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('revenue.index')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengunduh template.');
        }

        try {
            return Excel::download(new \App\Exports\RevenueTemplateExport, 'revenue-template.xlsx');
        } catch (\Exception $e) {
            Log::error('Error saat download template: ' . $e->getMessage());
            return redirect()->route('revenue.index')->with('error', 'Gagal mengunduh template: ' . $e->getMessage());
        }
    }

    /**
     * Fungsi untuk mendapatkan divisi berdasarkan Account Manager
     */
    public function getAccountManagerDivisions($id)
    {
        try {
            $accountManager = AccountManager::findOrFail($id);
            $divisis = $accountManager->divisis()->select('divisi.id', 'divisi.nama')->orderBy('divisi.nama')->get();

            Log::info('Divisi untuk Account Manager', [
                'account_manager_id' => $id,
                'account_manager_name' => $accountManager->nama,
                'divisis_count' => $divisis->count()
            ]);

            return response()->json([
                'success' => true,
                'divisis' => $divisis
            ]);
        } catch (\Exception $e) {
            Log::error('Error saat mengambil divisi: ' . $e->getMessage(), [
                'account_manager_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data divisi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk RevenueData view (fallback)
     */
    public function showRevenueData()
    {
        try {
            $divisi = Divisi::select('id', 'nama')->orderBy('nama')->get();
            $witels = Witel::select('id', 'nama')->orderBy('nama')->get();
            $regionals = Regional::select('id', 'nama')->orderBy('nama')->get();
            $accountManagers = AccountManager::with(['witel', 'divisis', 'regional'])->paginate(10);
            $corporateCustomers = collect([]);
            $revenues = collect([]);
            $yearRange = range(date('Y') - 5, date('Y') + 5);
            $isSearching = false;
            $stats = $this->calculateStats([]);

            return view('revenueData', compact('divisi', 'witels', 'regionals', 'accountManagers', 'corporateCustomers', 'revenues', 'yearRange', 'isSearching', 'stats'));
        } catch (\Exception $e) {
            Log::error('Error loading revenueData: ' . $e->getMessage());
            return view('revenueData', [
                'divisi' => collect([]),
                'witels' => collect([]),
                'regionals' => collect([]),
                'accountManagers' => collect([]),
                'corporateCustomers' => collect([]),
                'revenues' => collect([]),
                'yearRange' => range(date('Y') - 5, date('Y') + 5),
                'isSearching' => false,
                'stats' => [],
                'error' => 'Gagal memuat data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ IMPROVED: Mendapatkan data Revenue untuk edit via AJAX
     */
    public function getRevenueData($id)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses data ini.'
            ], 403);
        }

        try {
            // Ambil data revenue dengan relasi yang dibutuhkan
            $revenue = Revenue::with(['accountManager', 'corporateCustomer', 'divisi'])->findOrFail($id);

            // Format data untuk response
            $data = [
                'id' => $revenue->id,
                'account_manager_id' => $revenue->account_manager_id,
                'divisi_id' => $revenue->divisi_id,
                'corporate_customer_id' => $revenue->corporate_customer_id,
                'target_revenue' => $revenue->target_revenue,
                'real_revenue' => $revenue->real_revenue,
                'bulan' => $revenue->bulan,
                'account_manager' => $revenue->accountManager ? [
                    'id' => $revenue->accountManager->id,
                    'nama' => $revenue->accountManager->nama,
                    'nik' => $revenue->accountManager->nik
                ] : null,
                'divisi' => $revenue->divisi ? [
                    'id' => $revenue->divisi->id,
                    'nama' => $revenue->divisi->nama
                ] : null,
                'corporate_customer' => $revenue->corporateCustomer ? [
                    'id' => $revenue->corporateCustomer->id,
                    'nama' => $revenue->corporateCustomer->nama,
                    'nipnas' => $revenue->corporateCustomer->nipnas
                ] : null
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching Revenue data: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data Revenue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ IMPROVED: Update Revenue via AJAX
     */
    public function updateRevenue(Request $request, $id)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk memperbarui data ini.'
            ], 403);
        }

        try {
            $revenue = Revenue::findOrFail($id);

            // Validasi data input
            $validator = Validator::make($request->all(), [
                'account_manager_id' => 'required|exists:account_managers,id',
                'divisi_id' => 'required|exists:divisi,id',
                'corporate_customer_id' => 'required|exists:corporate_customers,id',
                'target_revenue' => 'required|numeric|min:0',
                'real_revenue' => 'required|numeric|min:0',
                'bulan' => 'required|date_format:Y-m',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verifikasi bahwa divisi_id memang terkait dengan account_manager_id
            $accountManager = AccountManager::findOrFail($request->account_manager_id);
            $divisiExists = $accountManager->divisis()->where('divisi.id', $request->divisi_id)->exists();

            if (!$divisiExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Divisi yang dipilih tidak terkait dengan Account Manager.'
                ], 422);
            }

            // Format bulan dengan menambahkan tanggal "01"
            $bulan = $request->bulan . '-01';

            // Update data revenue
            $revenue->update([
                'account_manager_id' => $request->account_manager_id,
                'divisi_id' => $request->divisi_id,
                'corporate_customer_id' => $request->corporate_customer_id,
                'target_revenue' => $request->target_revenue,
                'real_revenue' => $request->real_revenue,
                'bulan' => $bulan
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Revenue berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating Revenue via AJAX: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui Revenue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NEW: Search Account Manager untuk dropdown/autocomplete
     */
    public function searchAccountManager(Request $request)
    {
        try {
            $search = $request->input('search');
            $accountManagers = AccountManager::where(function ($query) use ($search) {
                $query->where('nama', 'LIKE', "%{$search}%")
                      ->orWhere('nik', 'LIKE', "%{$search}%");
            })
            ->with(['witel', 'divisis'])
            ->orderBy('nama')
            ->limit(10)
            ->get();

            return response()->json([
                'success' => true,
                'data' => $accountManagers
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching Account Manager: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari Account Manager'
            ], 500);
        }
    }

    /**
     * ✅ NEW: Search Corporate Customer untuk dropdown/autocomplete
     */
    public function searchCorporateCustomer(Request $request)
    {
        try {
            $search = $request->input('search');
            $corporateCustomers = CorporateCustomer::where(function ($query) use ($search) {
                $query->where('nama', 'LIKE', "%{$search}%")
                      ->orWhere('nipnas', 'LIKE', "%{$search}%");
            })
            ->orderBy('nama')
            ->limit(10)
            ->get();

            return response()->json([
                'success' => true,
                'data' => $corporateCustomers
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching Corporate Customer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari Corporate Customer'
            ], 500);
        }
    }
}