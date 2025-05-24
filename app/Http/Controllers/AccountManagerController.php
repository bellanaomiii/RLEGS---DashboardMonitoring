<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountManager;
use App\Models\Witel;
use App\Models\Divisi;
use App\Models\Regional;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;

class AccountManagerController extends Controller
{
    // Menampilkan halaman data Account Manager
    public function index()
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // Eager loading dengan divisis dan regional
        $accountManagers = AccountManager::with(['witel', 'divisis', 'regional'])->paginate(10);

        // Pastikan data divisi, witel dan regional selalu berupa Collection
        $witels = Witel::select('id', 'nama')->orderBy('nama')->get();
        $divisi = Divisi::select('id', 'nama')->orderBy('nama')->get();
        $regionals = Regional::select('id', 'nama')->orderBy('nama')->get();

        Log::info('Data untuk index:', [
            'divisi_count' => $divisi->count(),
            'regional_count' => $regionals->count(),
            'witel_count' => $witels->count()
        ]);

        return view('account_manager.index', compact('accountManagers', 'witels', 'divisi', 'regionals'));
    }

    // Menampilkan form untuk menambah Account Manager
    public function create()
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // Ambil data Witel, Divisi, dan Regional untuk dikirim ke view
        $witels = Witel::select('id', 'nama')->orderBy('nama')->get();
        $divisi = Divisi::select('id', 'nama')->orderBy('nama')->get();
        $regionals = Regional::select('id', 'nama')->orderBy('nama')->get();

        Log::info('Data untuk create:', [
            'divisi_count' => $divisi->count(),
            'regional_count' => $regionals->count(),
            'witel_count' => $witels->count()
        ]);

        // Eager loading dengan divisis dan regional
        $accountManagers = AccountManager::with(['witel', 'divisis', 'regional'])->paginate(10);
        $corporateCustomers = collect([]);

        return view('dashboard', compact('witels', 'divisi', 'regionals', 'accountManagers', 'corporateCustomers'));
    }

    // Method untuk mendapatkan data untuk modal
    public function getFormData()
    {
        try {
            $witels = Witel::select('id', 'nama')->orderBy('nama')->get();
            $divisi = Divisi::select('id', 'nama')->orderBy('nama')->get();
            $regionals = Regional::select('id', 'nama')->orderBy('nama')->get();

            Log::info('Data form untuk modal:', [
                'divisi_count' => $divisi->count(),
                'regional_count' => $regionals->count(),
                'witels_count' => $witels->count()
            ]);

            return [
                'witels' => $witels,
                'divisi' => $divisi,
                'regionals' => $regionals
            ];
        } catch (\Exception $e) {
            Log::error('Error getting form data: ' . $e->getMessage());
            return [
                'witels' => collect([]),
                'divisi' => collect([]),
                'regionals' => collect([])
            ];
        }
    }

    // Method untuk modal tambah Account Manager
    public function showAddModal()
    {
        $data = $this->getFormData();
        return view('account_manager.modal.add', $data);
    }

    // Handler untuk AJAX request mendapatkan data divisi
    public function getDivisi()
    {
        try {
            $divisi = Divisi::select('id', 'nama')->orderBy('nama')->get();
            return response()->json([
                'success' => true,
                'data' => $divisi
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data divisi: ' . $e->getMessage()
            ], 500);
        }
    }

    // Handler untuk AJAX request mendapatkan data regional
    public function getRegional()
    {
        try {
            $regionals = Regional::select('id', 'nama')->orderBy('nama')->get();
            return response()->json([
                'success' => true,
                'data' => $regionals
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data regional: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NEW: Import Account Managers dari Excel dengan detailed error handling
     */
    public function import(Request $request)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengimpor data Account Manager.'
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
            $import = new \App\Imports\AccountManagerImport();

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
                    'popup_title' => 'Import Account Manager - Ada Error',
                    'require_manual_close' => true // ✅ Manual close popup
                ]);
            } else {
                // Semua berhasil
                return response()->json([
                    'success' => true,
                    'message' => 'Data Account Manager berhasil diimpor semua!',
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
                    'popup_title' => 'Import Account Manager Berhasil',
                    'require_manual_close' => false // ✅ Auto close untuk success
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error saat mengimpor Account Manager: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimpor data: ' . $e->getMessage(),
                'show_popup' => true,
                'popup_type' => 'error',
                'popup_title' => 'Import Account Manager Gagal',
                'require_manual_close' => true
            ], 500);
        }
    }

    /**
     * ✅ NEW: Export Account Manager template atau data
     */
    public function export()
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengekspor data Account Manager.');
        }

        try {
            return Excel::download(new \App\Exports\AccountManagerExport, 'account-managers-' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Error saat export Account Manager: ' . $e->getMessage());
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
            return Excel::download(new \App\Exports\AccountManagerTemplateExport, 'account-manager-template.xlsx');
        } catch (\Exception $e) {
            Log::error('Error saat download template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengunduh template: ' . $e->getMessage());
        }
    }

    // Menyimpan Account Manager baru
    public function store(Request $request)
    {
        // Log request data untuk debugging
        Log::info('AccountManager store request data:', $request->all());

        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk menambahkan Account Manager.'
                ], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk menambahkan Account Manager.');
        }

        // Validasi data input
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|unique:account_managers,nama',
            'nik' => 'required|digits:5|unique:account_managers,nik',
            'witel_id' => 'required|exists:witel,id',
            'regional_id' => 'required|exists:regional,id',
            'divisi_ids' => 'required', // Validasi untuk multiple divisi
        ]);

        if ($validator->fails()) {
            Log::warning('AccountManager validation failed:', $validator->errors()->toArray());

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
            // Buat account manager baru
            $accountManager = AccountManager::create([
                'nama' => $request->nama,
                'nik' => $request->nik,
                'witel_id' => $request->witel_id,
                'regional_id' => $request->regional_id,
            ]);

            // Hubungkan dengan divisi yang dipilih
            if (!empty($request->divisi_ids)) {
                $divisiIds = explode(',', $request->divisi_ids);
                // Filter untuk memastikan nilai valid
                $divisiIds = array_filter($divisiIds, function ($value) {
                    return !empty($value) && is_numeric($value);
                });

                if (!empty($divisiIds)) {
                    $accountManager->divisis()->attach($divisiIds);
                    Log::info('Attached divisi IDs:', $divisiIds);
                }
            }

            // Return response sesuai request
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account Manager berhasil ditambahkan!'
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Account Manager berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Error creating AccountManager: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Response error
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan Account Manager: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal menambahkan Account Manager: ' . $e->getMessage())->withInput();
        }
    }

    // Edit Account Manager
    public function edit($id)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengedit Account Manager.');
        }

        try {
            $accountManager = AccountManager::with(['divisis', 'regional', 'witel'])->findOrFail($id);
            $witels = Witel::select('id', 'nama')->orderBy('nama')->get();
            $divisi = Divisi::select('id', 'nama')->orderBy('nama')->get();
            $regionals = Regional::select('id', 'nama')->orderBy('nama')->get();

            Log::info('Data untuk edit:', [
                'account_manager_id' => $id,
                'divisi_count' => $divisi->count(),
                'regional_count' => $regionals->count(),
                'selected_divisis' => $accountManager->divisis->pluck('id')->toArray()
            ]);

            return view('account_manager.edit', compact('accountManager', 'witels', 'divisi', 'regionals'));
        } catch (\Exception $e) {
            Log::error('Error loading edit form: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Gagal memuat data Account Manager: ' . $e->getMessage());
        }
    }

    // Update Account Manager
    public function update(Request $request, $id)
    {
        // Log request data untuk debugging
        Log::info('AccountManager update request data:', [
            'id' => $id,
            'data' => $request->all()
        ]);

        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk memperbarui Account Manager.'
                ], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk memperbarui Account Manager.');
        }

        try {
            $accountManager = AccountManager::findOrFail($id);

            // Validasi data input
            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|unique:account_managers,nama,' . $id,
                'nik' => 'required|digits:5|unique:account_managers,nik,' . $id,
                'witel_id' => 'required|exists:witel,id',
                'regional_id' => 'required|exists:regional,id',
                'divisi_ids' => 'required', // Untuk multiple divisi
            ]);

            if ($validator->fails()) {
                Log::warning('AccountManager update validation failed:', $validator->errors()->toArray());

                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validasi gagal',
                        'errors' => $validator->errors()
                    ], 422);
                }

                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Update data dasar account manager
            $accountManager->update([
                'nama' => $request->nama,
                'nik' => $request->nik,
                'witel_id' => $request->witel_id,
                'regional_id' => $request->regional_id,
            ]);

            // Sync divisi
            if (!empty($request->divisi_ids)) {
                $divisiIds = explode(',', $request->divisi_ids);
                // Filter untuk memastikan nilai valid
                $divisiIds = array_filter($divisiIds, function($value) {
                    return !empty($value) && is_numeric($value);
                });

                if (!empty($divisiIds)) {
                    $accountManager->divisis()->sync($divisiIds);
                    Log::info('Synced divisi IDs:', $divisiIds);
                }
            } else {
                // Jika tidak ada divisi, hapus semua relasi
                $accountManager->divisis()->detach();
                Log::info('Detached all divisi relations');
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account Manager berhasil diperbarui!'
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Account Manager berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Error updating AccountManager: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui Account Manager: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal memperbarui Account Manager: ' . $e->getMessage())->withInput();
        }
    }

    // Hapus Account Manager
    public function destroy($id)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk menghapus Account Manager.');
        }

        try {
            $accountManager = AccountManager::findOrFail($id);

            // Hapus relasi divisi terlebih dahulu
            $accountManager->divisis()->detach();

            // Hapus account manager
            $accountManager->delete();

            return redirect()->route('dashboard')->with('success', 'Account Manager berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting AccountManager: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->route('dashboard')->with('error', 'Gagal menghapus Account Manager: ' . $e->getMessage());
        }
    }

    // Pencarian Account Manager
    public function search(Request $request)
    {
        try {
            $search = $request->get('search');
            $accountManagers = AccountManager::where('nama', 'like', "%{$search}%")
                ->orWhere('nik', 'like', "%{$search}%")
                ->with(['witel', 'divisis', 'regional'])
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

    // Method untuk RevenueData view
    public function showRevenueData()
    {
        try {
            // Pastikan data divisi dan regional adalah Collection, bukan boolean
            $divisi = Divisi::select('id', 'nama')->orderBy('nama')->get();
            $witels = Witel::select('id', 'nama')->orderBy('nama')->get();
            $regionals = Regional::select('id', 'nama')->orderBy('nama')->get();
            $accountManagers = AccountManager::with(['witel', 'divisis', 'regional'])->paginate(10);
            $corporateCustomers = collect([]);
            $revenues = collect([]);
            $yearRange = range(date('Y') - 5, date('Y') + 5);

            Log::info('Data untuk revenueData:', [
                'divisi_count' => $divisi->count(),
                'regional_count' => $regionals->count(),
                'witel_count' => $witels->count()
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
     * Mendapatkan data Account Manager untuk edit via AJAX
     */
    public function getAccountManagerData($id)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses data ini.'
            ], 403);
        }

        try {
            // Ambil data account manager dengan relasi divisi
            $accountManager = AccountManager::with(['divisis', 'witel', 'regional'])->findOrFail($id);

            // Format data untuk response
            $data = [
                'id' => $accountManager->id,
                'nama' => $accountManager->nama,
                'nik' => $accountManager->nik,
                'witel_id' => $accountManager->witel_id,
                'regional_id' => $accountManager->regional_id,
                'witel' => $accountManager->witel ? [
                    'id' => $accountManager->witel->id,
                    'nama' => $accountManager->witel->nama
                ] : null,
                'regional' => $accountManager->regional ? [
                    'id' => $accountManager->regional->id,
                    'nama' => $accountManager->regional->nama
                ] : null,
                'divisis' => $accountManager->divisis->map(function($divisi) {
                    return [
                        'id' => $divisi->id,
                        'nama' => $divisi->nama
                    ];
                })
            ];

            Log::info('Account Manager data fetched for edit:', [
                'id' => $id,
                'divisi_count' => count($data['divisis'])
            ]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching Account Manager data: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data Account Manager: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Account Manager via AJAX
     */
    public function updateAccountManager(Request $request, $id)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk memperbarui data ini.'
            ], 403);
        }

        try {
            $accountManager = AccountManager::findOrFail($id);

            // Validasi data input
            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|unique:account_managers,nama,' . $id,
                'nik' => 'required|digits:5|unique:account_managers,nik,' . $id,
                'witel_id' => 'required|exists:witel,id',
                'regional_id' => 'required|exists:regional,id',
                'divisi_ids' => 'required', // Untuk multiple divisi
            ]);

            if ($validator->fails()) {
                Log::warning('AccountManager update validation failed via AJAX:', $validator->errors()->toArray());

                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update data dasar account manager
            $accountManager->update([
                'nama' => $request->nama,
                'nik' => $request->nik,
                'witel_id' => $request->witel_id,
                'regional_id' => $request->regional_id,
            ]);

            // Sync divisi
            if (!empty($request->divisi_ids)) {
                $divisiIds = explode(',', $request->divisi_ids);
                // Filter untuk memastikan nilai valid
                $divisiIds = array_filter($divisiIds, function($value) {
                    return !empty($value) && is_numeric($value);
                });

                if (!empty($divisiIds)) {
                    $accountManager->divisis()->sync($divisiIds);
                    Log::info('Synced divisi IDs via AJAX:', $divisiIds);
                }
            } else {
                // Jika tidak ada divisi, hapus semua relasi
                $accountManager->divisis()->detach();
                Log::info('Detached all divisi relations via AJAX');
            }

            return response()->json([
                'success' => true,
                'message' => 'Account Manager berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating AccountManager via AJAX: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui Account Manager: ' . $e->getMessage()
            ], 500);
        }
    }
}