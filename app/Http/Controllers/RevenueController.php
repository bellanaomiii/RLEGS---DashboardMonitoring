<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Revenue;
use App\Models\AccountManager;
use App\Models\CorporateCustomer;
use App\Models\Witel;
use App\Models\Divisi;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
// Import namespace untuk Excel
use Maatwebsite\Excel\Facades\Excel;

class RevenueController extends Controller
{
    // Menampilkan halaman dashboard dengan data Revenue, Witel, dan Divisi
    public function index(Request $request)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // Query dasar
        $revenuesQuery = Revenue::with(['accountManager', 'corporateCustomer']);
        $accountManagersQuery = AccountManager::with(['witel', 'divisi']);
        $corporateCustomersQuery = CorporateCustomer::query();

        // Penerapan filter pencarian
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;

            // Filter revenue berdasarkan nama AM atau Customer
            $revenuesQuery->where(function($query) use ($search) {
                $query->whereHas('accountManager', function($q) use ($search) {
                    $q->where('nama', 'LIKE', "%{$search}%");
                })->orWhereHas('corporateCustomer', function($q) use ($search) {
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
            $revenuesQuery->whereHas('accountManager', function($query) use ($witelId) {
                $query->where('witel_id', $witelId);
            });

            $accountManagersQuery->where('witel_id', $witelId);
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
        $revenues = $revenuesQuery->paginate(10);
        $accountManagers = $accountManagersQuery->paginate(10);
        $corporateCustomers = $corporateCustomersQuery->paginate(10);

        // Data untuk filter
        $witels = Witel::all();
        $divisi = Divisi::all();

        // Rentang tahun untuk filter (dari 2020 hingga 100 tahun ke depan)
        $currentYear = Carbon::now()->year;
        $yearRange = range(2020, $currentYear + 100);

        return view('revenueData', compact('revenues', 'accountManagers', 'corporateCustomers', 'witels', 'divisi', 'yearRange'));
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
        // Kode yang sudah ada - tetap tidak diubah
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
            // Cek apakah data sudah ada
            $existingRevenue = Revenue::where('account_manager_id', $request->account_manager_id)
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
        // Kode yang sudah ada - tetap tidak diubah
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengedit data revenue.');
        }

        $revenue = Revenue::findOrFail($id);
        $accountManagers = AccountManager::all();
        $corporateCustomers = CorporateCustomer::all();
        $witels = Witel::all();
        $divisi = Divisi::all();

        // Parse bulan untuk tampilan form
        $bulanParts = explode('-', $revenue->bulan);
        $year = $bulanParts[0];
        $month = $bulanParts[1];

        return view('revenue.edit', compact('revenue', 'accountManagers', 'corporateCustomers', 'witels', 'divisi', 'year', 'month'));
    }

    // Update data revenue
    public function update(Request $request, $id)
    {
        // Kode yang sudah ada - tetap tidak diubah
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
            $revenue = Revenue::findOrFail($id);

            // Update data revenue dengan format tanggal yang benar
            $revenue->update([
                'account_manager_id' => $request->account_manager_id,
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
        // Kode yang sudah ada - tetap tidak diubah
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
            ->with(['witel', 'divisi'])
            ->take(5)
            ->get();

        // Cari corporate customers
        $corporateCustomers = CorporateCustomer::where('nama', 'LIKE', "%{$search}%")
            ->take(5)
            ->get();

        // Cari revenues (kombinasi AM dan CC)
        $revenues = Revenue::with(['accountManager', 'corporateCustomer'])
            ->whereHas('accountManager', function($query) use ($search) {
                $query->where('nama', 'LIKE', "%{$search}%");
            })
            ->orWhereHas('corporateCustomer', function($query) use ($search) {
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
}