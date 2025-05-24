<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CorporateCustomer;
use App\Models\Witel;
use App\Models\Divisi;
use App\Models\AccountManager;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class CorporateCustomerController extends Controller
{
    /**
     * ✅ IMPROVED: Index method untuk menampilkan data Corporate Customer
     */
    public function index()
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        try {
            $corporateCustomers = CorporateCustomer::orderBy('nama')->paginate(10);
            $accountManagers = AccountManager::with(['witel', 'divisis'])->paginate(10);
            $witels = Witel::orderBy('nama')->get();
            $divisi = Divisi::orderBy('nama')->get();

            return view('corporate_customer.index', compact('corporateCustomers', 'accountManagers', 'witels', 'divisi'));
        } catch (\Exception $e) {
            Log::error('Error loading Corporate Customer index: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Gagal memuat data Corporate Customer: ' . $e->getMessage());
        }
    }

    // Menampilkan form untuk menambah Corporate Customer
    public function create()
    {
        try {
            $corporateCustomers = CorporateCustomer::orderBy('nama')->paginate(10);
            $accountManagers = AccountManager::with(['witel', 'divisis'])->paginate(10);
            $witels = Witel::orderBy('nama')->get();
            $divisi = Divisi::orderBy('nama')->get();

            return view('dashboard', compact('corporateCustomers', 'accountManagers', 'witels', 'divisi'));
        } catch (\Exception $e) {
            Log::error('Error loading create form: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Gagal memuat form: ' . $e->getMessage());
        }
    }

    /**
     * ✅ NEW: Import Corporate Customers dari Excel dengan detailed error handling
     */
    public function import(Request $request)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengimpor data Corporate Customer.'
            ], 403);
        }

        // Validasi file input
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // ✅ Create import instance
            $import = new \App\Imports\CorporateCustomerImport();

            // Set memory limit dan timeout untuk file besar
            ini_set('memory_limit', '1024M');
            set_time_limit(300); // 5 minutes

            // Import file
            Excel::import($import, $request->file('file'));

            // ✅ Get detailed results
            $results = $import->getImportResults();

            // ✅ Build response berdasarkan hasil import
            if ($results['errors'] > 0) {
                // Ada error - return dengan detail error untuk popup
                return response()->json([
                    'success' => false,
                    'message' => 'Import selesai dengan beberapa error',
                    'data' => [
                        'imported' => $results['imported'],
                        'updated' => $results['updated'],
                        'duplicates' => $results['duplicates'],
                        'errors' => $results['errors'],
                        'skipped' => $results['skipped'],
                        'error_details' => $results['error_details'], // ✅ Detail baris yang error
                        'warning_details' => $results['warning_details'],
                        'success_details' => $results['success_details'],
                        'summary' => $results['summary']
                    ],
                    'show_popup' => true, // ✅ Flag untuk popup
                    'popup_type' => 'warning',
                    'popup_title' => 'Import Corporate Customer - Ada Error',
                    'require_manual_close' => true // ✅ Manual close popup
                ]);
            } else {
                // Semua berhasil
                return response()->json([
                    'success' => true,
                    'message' => 'Data Corporate Customer berhasil diimpor semua!',
                    'data' => [
                        'imported' => $results['imported'],
                        'updated' => $results['updated'],
                        'duplicates' => $results['duplicates'],
                        'errors' => 0,
                        'success_details' => $results['success_details'],
                        'summary' => $results['summary']
                    ],
                    'show_popup' => true,
                    'popup_type' => 'success',
                    'popup_title' => 'Import Corporate Customer Berhasil',
                    'require_manual_close' => false // ✅ Auto close untuk success
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error saat mengimpor Corporate Customer: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimpor data: ' . $e->getMessage(),
                'show_popup' => true,
                'popup_type' => 'error',
                'popup_title' => 'Import Corporate Customer Gagal',
                'require_manual_close' => true
            ], 500);
        }
    }

    /**
     * ✅ NEW: Export Corporate Customer data
     */
    public function export()
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengekspor data Corporate Customer.');
        }

        try {
            return Excel::download(new \App\Exports\CorporateCustomerExport, 'corporate-customers-' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Error saat export Corporate Customer: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }

    /**
     * ✅ NEW: Download template Excel untuk import
     */
    public function template()
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengunduh template.');
        }

        try {
            return Excel::download(new \App\Exports\CorporateCustomerTemplateExport, 'corporate-customer-template.xlsx');
        } catch (\Exception $e) {
            Log::error('Error saat download template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengunduh template: ' . $e->getMessage());
        }
    }

    // Menyimpan Corporate Customer baru
    public function store(Request $request)
    {
        // ✅ IMPROVED: Add admin check
        if (Auth::user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk menambahkan Corporate Customer.'
                ], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk menambahkan Corporate Customer.');
        }

        // Validasi data input
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|unique:corporate_customers,nama',
            'nipnas' => 'required|numeric|unique:corporate_customers,nipnas|min:1|max:9999999'
        ]);

        if ($validator->fails()) {
            Log::warning('Corporate Customer validation failed:', $validator->errors()->toArray());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Membuat Corporate Customer baru
            $corporateCustomer = CorporateCustomer::create([
                'nama' => $request->nama,
                'nipnas' => $request->nipnas
            ]);

            Log::info('Corporate Customer created successfully:', [
                'id' => $corporateCustomer->id,
                'nama' => $corporateCustomer->nama,
                'nipnas' => $corporateCustomer->nipnas
            ]);

            // Return JSON response untuk AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Corporate Customer berhasil ditambahkan!'
                ]);
            }

            // Mengembalikan response dengan status sukses
            return redirect()->route('dashboard')->with('success', 'Corporate Customer berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Error creating Corporate Customer: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan Corporate Customer: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal menambahkan Corporate Customer: ' . $e->getMessage())->withInput();
        }
    }

    // Edit Corporate Customer
    public function edit($id)
    {
        // ✅ IMPROVED: Add admin check
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengedit Corporate Customer.');
        }

        try {
            $corporateCustomer = CorporateCustomer::findOrFail($id);
            return view('corporate_customer.edit', compact('corporateCustomer'));
        } catch (\Exception $e) {
            Log::error('Error loading edit form: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Gagal memuat data Corporate Customer: ' . $e->getMessage());
        }
    }

    // Update Corporate Customer
    public function update(Request $request, $id)
    {
        // ✅ IMPROVED: Add admin check
        if (Auth::user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk memperbarui Corporate Customer.'
                ], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk memperbarui Corporate Customer.');
        }

        try {
            $corporateCustomer = CorporateCustomer::findOrFail($id);

            // Validasi data input
            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|unique:corporate_customers,nama,' . $id,
                'nipnas' => 'required|numeric|unique:corporate_customers,nipnas,' . $id . '|min:1|max:9999999'
            ]);

            if ($validator->fails()) {
                Log::warning('Corporate Customer update validation failed:', $validator->errors()->toArray());

                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validasi gagal',
                        'errors' => $validator->errors()
                    ], 422);
                }

                return redirect()->back()->withErrors($validator)->withInput();
            }

            $corporateCustomer->update([
                'nama' => $request->nama,
                'nipnas' => $request->nipnas
            ]);

            Log::info('Corporate Customer updated successfully:', [
                'id' => $corporateCustomer->id,
                'nama' => $corporateCustomer->nama,
                'nipnas' => $corporateCustomer->nipnas
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Corporate Customer berhasil diperbarui!'
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Corporate Customer berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Error updating Corporate Customer: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui Corporate Customer: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal memperbarui Corporate Customer: ' . $e->getMessage())->withInput();
        }
    }

    // Hapus Corporate Customer
    public function destroy($id)
    {
        // ✅ IMPROVED: Add admin check
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk menghapus Corporate Customer.');
        }

        try {
            $corporateCustomer = CorporateCustomer::findOrFail($id);
            $corporateCustomer->delete();

            Log::info('Corporate Customer deleted successfully:', [
                'id' => $id,
                'nama' => $corporateCustomer->nama
            ]);

            return redirect()->route('dashboard')->with('success', 'Corporate Customer berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting Corporate Customer: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->route('dashboard')->with('error', 'Gagal menghapus Corporate Customer: ' . $e->getMessage());
        }
    }

    // Fungsi pencarian Corporate Customer untuk autocomplete
    public function search(Request $request)
    {
        try {
            $search = $request->get('search');
            $corporateCustomers = CorporateCustomer::where('nama', 'like', "%{$search}%")
                               ->orWhere('nipnas', 'like', "%{$search}%")
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

    /**
     * Mendapatkan data Corporate Customer untuk edit via AJAX
     */
    public function getCorporateCustomerData($id)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses data ini.'
            ], 403);
        }

        try {
            // Ambil data corporate customer
            $corporateCustomer = CorporateCustomer::findOrFail($id);

            // Format data untuk response
            $data = [
                'id' => $corporateCustomer->id,
                'nama' => $corporateCustomer->nama,
                'nipnas' => $corporateCustomer->nipnas,
                'created_at' => $corporateCustomer->created_at ? $corporateCustomer->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $corporateCustomer->updated_at ? $corporateCustomer->updated_at->format('Y-m-d H:i:s') : null
            ];

            Log::info('Corporate Customer data fetched for edit:', [
                'id' => $id,
                'nama' => $corporateCustomer->nama
            ]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching Corporate Customer data: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data Corporate Customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Corporate Customer via AJAX
     */
    public function updateCorporateCustomer(Request $request, $id)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk memperbarui data ini.'
            ], 403);
        }

        try {
            $corporateCustomer = CorporateCustomer::findOrFail($id);

            // Validasi data input
            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|unique:corporate_customers,nama,' . $id,
                'nipnas' => 'required|numeric|unique:corporate_customers,nipnas,' . $id . '|min:1|max:9999999'
            ]);

            if ($validator->fails()) {
                Log::warning('Corporate Customer update validation failed via AJAX:', $validator->errors()->toArray());

                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update corporate customer
            $corporateCustomer->update([
                'nama' => $request->nama,
                'nipnas' => $request->nipnas
            ]);

            Log::info('Corporate Customer updated via AJAX:', [
                'id' => $id,
                'nama' => $corporateCustomer->nama,
                'nipnas' => $corporateCustomer->nipnas
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Corporate Customer berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating Corporate Customer via AJAX: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui Corporate Customer: ' . $e->getMessage()
            ], 500);
        }
    }
}