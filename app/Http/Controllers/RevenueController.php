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
// Import namespace untuk Excel
use Maatwebsite\Excel\Facades\Excel;

class RevenueController extends Controller
{
    // Menampilkan halaman dashboard dengan data Revenue, Witel, Regional, dan Divisi
    public function index(Request $request)
    {
            // Validasi dan ambil parameter per_page
    $perPage = $request->get('per_page', 10);
    
    // Pastikan per_page adalah angka yang valid
    if (!in_array($perPage, [10, 25, 50, 75, 100])) {
        $perPage = 10;
    }

        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // Query dasar
        $revenuesQuery = Revenue::with(['accountManager', 'corporateCustomer', 'divisi']);
        $accountManagersQuery = AccountManager::with(['witel', 'divisis', 'regional']);
        $corporateCustomersQuery = CorporateCustomer::query();

        // Penerapan filter pencarian
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;

            // Filter revenue berdasarkan nama AM atau Customer
            $revenuesQuery->where(function ($query) use ($search) {
                $query->whereHas('accountManager', function ($q) use ($search) {
                    $q->where('nama', 'LIKE', "%{$search}%");
                })->orWhereHas('corporateCustomer', function ($q) use ($search) {
                    $q->where('nama', 'LIKE', "%{$search}%");
                });
            });

            // Filter account manager berdasarkan nama
            $accountManagersQuery->where('nama', 'LIKE', "%{$search}%");

            // Filter corporate customer berdasarkan nama
            $corporateCustomersQuery->where('nama', 'LIKE', "%{$search}%");
        }

        // Filter berdasarkan Witel
        if ($request->has('witel') && !empty($request->witel)) {
            $witelId = $request->witel;
            $revenuesQuery->whereHas('accountManager', function ($query) use ($witelId) {
                $query->where('witel_id', $witelId);
            });

            $accountManagersQuery->where('witel_id', $witelId);
        }

        // Filter berdasarkan Regional
        if ($request->has('regional') && !empty($request->regional)) {
            $regionalId = $request->regional;
            $revenuesQuery->whereHas('accountManager', function ($query) use ($regionalId) {
                $query->where('regional_id', $regionalId);
            });

            $accountManagersQuery->where('regional_id', $regionalId);
        }

        // Filter berdasarkan Divisi
        if ($request->has('divisi') && !empty($request->divisi)) {
            $divisiId = $request->divisi;

            // Filter revenue berdasarkan divisi_id
            $revenuesQuery->where('divisi_id', $divisiId);

            // Filter account manager yang memiliki divisi tersebut
            $accountManagersQuery->whereHas('divisis', function ($query) use ($divisiId) {
                $query->where('divisi.id', $divisiId);
            });
        }

        // Filter berdasarkan Bulan
        if ($request->has('month') && !empty($request->month)) {
            $month = $request->month;
            $revenuesQuery->whereMonth('bulan', $month);
        }

        // Filter berdasarkan Tahun
        if ($request->has('year') && !empty($request->year)) {
            $year = $request->year;
            $revenuesQuery->whereYear('bulan', $year);
        }

        // Mengurutkan data
        $revenuesQuery->orderBy('bulan', 'desc');
        $accountManagersQuery->orderBy('nama', 'asc');
        $corporateCustomersQuery->orderBy('nama', 'asc');

        // Mengambil data dengan pagination
        $revenues = $revenuesQuery->paginate($perPage)->appends($request->query());
        $accountManagers = $accountManagersQuery->paginate($perPage)->appends($request->query());
        $corporateCustomers = $corporateCustomersQuery->paginate($perPage)->appends($request->query());

        // Data untuk filter
        $witels = Witel::all();
        $divisi = Divisi::all();
        $regionals = Regional::all();

        // Rentang tahun untuk filter (dari 2020 hingga 100 tahun ke depan)
        $currentYear = Carbon::now()->year;
        $yearRange = range(2020, $currentYear + 100);

        return view('revenueData', compact('revenues', 'accountManagers', 'corporateCustomers', 'witels', 'divisi', 'regionals', 'yearRange'));
    }

    // Fungsi untuk halaman filter data revenue
    public function data(Request $request)
    {
        // Ini adalah alias untuk index dengan parameter filter
        return $this->index($request);
    }

    // Menyimpan data revenue baru
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
            'divisi_id' => 'required|exists:divisi,id', // Tambah validasi divisi_id
            'corporate_customer_id' => 'required|exists:corporate_customers,id',
            'target_revenue' => 'required|numeric',
            'real_revenue' => 'required|numeric',
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

        // Gabungkan bulan dan tahun menjadi format Y-m-d
        $bulan = $request->bulan_year . '-' . $request->bulan_month . '-01';

        try {
            // Verifikasi bahwa divisi_id memang terkait dengan account_manager_id
            $accountManager = AccountManager::findOrFail($request->account_manager_id);
            $divisiExists = $accountManager->divisis()->where('divisi.id', $request->divisi_id)->exists();

            if (!$divisiExists) {
                throw new \Exception('Divisi yang dipilih tidak terkait dengan Account Manager.');
            }

            // Cek apakah data sudah ada (perhatikan penambahan divisi_id)
            $existingRevenue = Revenue::where('account_manager_id', $request->account_manager_id)
                ->where('divisi_id', $request->divisi_id) // Tambah filter divisi
                ->where('corporate_customer_id', $request->corporate_customer_id)
                ->whereYear('bulan', $request->bulan_year)
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
                // Buat data baru dengan format bulan yang benar (YYYY-MM-DD)
                Revenue::create([
                    'account_manager_id' => $request->account_manager_id,
                    'divisi_id' => $request->divisi_id, // Tambah divisi_id
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

    // Edit data revenue
    public function edit($id)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengedit data revenue.');
        }

        $revenue = Revenue::findOrFail($id);
        $accountManagers = AccountManager::all();
        $corporateCustomers = CorporateCustomer::all();
        $witels = Witel::all();
        $divisi = Divisi::all();
        $regionals = Regional::all();

        // Parse bulan untuk tampilan form
        $bulanParts = explode('-', $revenue->bulan);
        $year = $bulanParts[0];
        $month = $bulanParts[1];

        return view('revenue.edit', compact('revenue', 'accountManagers', 'corporateCustomers', 'witels', 'divisi', 'regionals', 'year', 'month'));
    }

    // Update data revenue
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
            'divisi_id' => 'required|exists:divisi,id', // Tambahkan validasi divisi_id
            'corporate_customer_id' => 'required|exists:corporate_customers,id',
            'target_revenue' => 'required|numeric',
            'real_revenue' => 'required|numeric',
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

        // Gabungkan bulan dan tahun menjadi format Y-m-d
        $bulan = $request->bulan_year . '-' . $request->bulan_month . '-01';

        try {
            // Verifikasi bahwa divisi_id memang terkait dengan account_manager_id
            $accountManager = AccountManager::findOrFail($request->account_manager_id);
            $divisiExists = $accountManager->divisis()->where('divisi.id', $request->divisi_id)->exists();

            if (!$divisiExists) {
                throw new \Exception('Divisi yang dipilih tidak terkait dengan Account Manager.');
            }

            $revenue = Revenue::findOrFail($id);

            // Update data revenue dengan format tanggal yang benar
            $revenue->update([
                'account_manager_id' => $request->account_manager_id,
                'divisi_id' => $request->divisi_id, // Tambahkan divisi_id
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

    // Hapus data revenue
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
            return redirect()->route('revenue.index')->with('error', 'Gagal menghapus revenue: ' . $e->getMessage());
        }
    }

    // Fungsi pencarian Account Manager
    public function searchAccountManager(Request $request)
    {
        $search = $request->input('search');
        $accountManagers = AccountManager::where('nama', 'LIKE', "%{$search}%")->get();
        return response()->json($accountManagers);
    }

    // Fungsi pencarian Corporate Customer
    public function searchCorporateCustomer(Request $request)
    {
        $search = $request->input('search');
        $corporateCustomers = CorporateCustomer::where('nama', 'LIKE', "%{$search}%")->get();
        return response()->json($corporateCustomers);
    }

    // Fungsi untuk search global yang mengembalikan data dari 3 kategori
    public function search(Request $request)
    {
        $search = $request->input('search');

        if (empty($search)) {
            return response()->json([
                'accountManagers' => [],
                'corporateCustomers' => [],
                'revenues' => []
            ]);
        }

        // Cari account managers
        $accountManagers = AccountManager::where('nama', 'LIKE', "%{$search}%")
            ->with(['witel', 'divisis', 'regional'])
            ->take(5)
            ->get();

        // Cari corporate customers
        $corporateCustomers = CorporateCustomer::where('nama', 'LIKE', "%{$search}%")
            ->take(5)
            ->get();

        // Cari revenues (kombinasi AM dan CC)
        $revenues = Revenue::with(['accountManager', 'corporateCustomer', 'divisi'])
            ->whereHas('accountManager', function ($query) use ($search) {
                $query->where('nama', 'LIKE', "%{$search}%");
            })
            ->orWhereHas('corporateCustomer', function ($query) use ($search) {
                $query->where('nama', 'LIKE', "%{$search}%");
            })
            ->orderBy('bulan', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'accountManagers' => $accountManagers,
            'corporateCustomers' => $corporateCustomers,
            'revenues' => $revenues
        ]);
    }

    // Fungsi untuk export data revenue
    public function export()
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('revenue.index')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengekspor data revenue.');
        }

        try {
            return Excel::download(new \App\Exports\RevenueExport, 'revenue-data-' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            return redirect()->route('revenue.index')->with('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }

    // Fungsi untuk import data revenue dari Excel
    public function import(Request $request)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('revenue.index')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengimpor data revenue.');
        }

        // Validasi file input
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->route('revenue.index')->withErrors($validator)->withInput();
        }

        try {
            Excel::import(new \App\Imports\RevenueImport, $request->file('file'));
            return redirect()->route('revenue.index')->with('success', 'Data revenue berhasil diimpor.');
        } catch (\Exception $e) {
            Log::error('Error saat mengimpor revenue: ' . $e->getMessage());
            return redirect()->route('revenue.index')->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    // Fungsi untuk mengunduh template Excel untuk import data
    public function template()
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('revenue.index')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengunduh template.');
        }

        try {
            return Excel::download(new \App\Exports\RevenueTemplateExport, 'revenue-template.xlsx');
        } catch (\Exception $e) {
            return redirect()->route('revenue.index')->with('error', 'Gagal mengunduh template: ' . $e->getMessage());
        }
    }

    // Fungsi untuk mendapatkan divisi berdasarkan Account Manager
    public function getAccountManagerDivisions($id)
    {
        try {
            // Ambil account manager berdasarkan ID
            $accountManager = AccountManager::findOrFail($id);

            // Ambil semua divisi terkait account manager tersebut
            $divisis = $accountManager->divisis()->select('divisi.id', 'divisi.nama')->get();

            // Log untuk debugging
            Log::info('Divisi untuk Account Manager', [
                'account_manager_id' => $id,
                'account_manager_name' => $accountManager->nama,
                'divisis' => $divisis->toArray()
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

    // Method untuk RevenueData view
    public function showRevenueData()
    {
        try {
            // Pastikan data divisi dan regional adalah Collection, bukan boolean
            $divisi = Divisi::select('id', 'nama')->get();
            $witels = Witel::select('id', 'nama')->get();
            $regionals = Regional::select('id', 'nama')->get(); // Tambahkan regionals
            $accountManagers = AccountManager::with(['witel', 'divisi', 'regional'])->paginate($perPage); 
            $corporateCustomers = collect([]);
            $revenues = collect([]);
            $yearRange = range(date('Y') - 5, date('Y') + 5);

            Log::info('Data untuk revenueData:', [
                'divisi_count' => $divisi->count(),
                'regional_count' => $regionals->count(),
            ]);

            return view('revenueData', compact('divisi', 'witels', 'regionals', 'accountManagers', 'corporateCustomers', 'revenues', 'yearRange'));
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
                'error' => 'Gagal memuat data: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Mendapatkan data Revenue untuk edit via AJAX
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
                    'nama' => $revenue->accountManager->nama
                ] : null,
                'divisi' => $revenue->divisi ? [
                    'id' => $revenue->divisi->id,
                    'nama' => $revenue->divisi->nama
                ] : null,
                'corporate_customer' => $revenue->corporateCustomer ? [
                    'id' => $revenue->corporateCustomer->id,
                    'nama' => $revenue->corporateCustomer->nama
                ] : null
            ];
            
            Log::info('Revenue data fetched for edit:', [
                'id' => $id,
                'account_manager' => $revenue->accountManager->nama ?? 'N/A',
                'corporate_customer' => $revenue->corporateCustomer->nama ?? 'N/A'
            ]);

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
     * Update Revenue via AJAX
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
                'target_revenue' => 'required|numeric',
                'real_revenue' => 'required|numeric',
                'bulan' => 'required|date_format:Y-m',
            ]);

            if ($validator->fails()) {
                Log::warning('Revenue update validation failed via AJAX:', $validator->errors()->toArray());
                
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
}