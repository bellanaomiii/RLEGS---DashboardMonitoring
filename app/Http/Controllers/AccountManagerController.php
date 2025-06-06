<?php

namespace App\Http\Controllers;

use App\Models\AccountManager;
use App\Models\Witel;
use App\Models\Regional;
use App\Models\Divisi;
use App\Imports\AccountManagerImport;
use App\Exports\AccountManagerExport;
use App\Exports\AccountManagerTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AccountManagerController extends Controller
{
    /**
     * ✅ EXISTING: Display a listing of account managers with comprehensive search
     */
    public function index(Request $request)
    {
        try {
            $query = AccountManager::with(['witel', 'regional', 'divisis']);

            // ✅ ENHANCED: Search functionality - partial word search
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = trim($request->search);
                $query->where(function($q) use ($searchTerm) {
                    $q->where('nama', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('nik', 'LIKE', "%{$searchTerm}%")
                      ->orWhereHas('witel', function($subQuery) use ($searchTerm) {
                          $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                      })
                      ->orWhereHas('regional', function($subQuery) use ($searchTerm) {
                          $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                      })
                      ->orWhereHas('divisis', function($subQuery) use ($searchTerm) {
                          $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                      });
                });
            }

            // Filter by witel
            if ($request->has('witel') && !empty($request->witel)) {
                $query->where('witel_id', $request->witel);
            }

            // Filter by regional
            if ($request->has('regional') && !empty($request->regional)) {
                $query->where('regional_id', $request->regional);
            }

            // Filter by divisi (through many-to-many relationship)
            if ($request->has('divisi') && !empty($request->divisi)) {
                $query->whereHas('divisis', function($subQuery) use ($request) {
                    $subQuery->where('divisi.id', $request->divisi);
                });
            }

            $accountManagers = $query->orderBy('nama', 'asc')
                                   ->paginate($request->get('per_page', 15));

            // Get additional data for filters and forms
            $witels = Witel::orderBy('nama')->get();
            $regionals = Regional::orderBy('nama')->get();
            $divisis = Divisi::orderBy('nama')->get();

            // Get statistics
            $statistics = $this->getStatistics();

            return view('account-managers.index', compact(
                'accountManagers',
                'witels',
                'regionals',
                'divisis',
                'statistics'
            ));

        } catch (\Exception $e) {
            Log::error('Account Manager Index Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data Account Manager.');
        }
    }

    /**
     * ✅ EXISTING: Show add modal (called from routes)
     */
    public function showAddModal()
    {
        try {
            $witels = Witel::orderBy('nama')->get();
            $regionals = Regional::orderBy('nama')->get();
            $divisis = Divisi::orderBy('nama')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'witels' => $witels,
                    'regionals' => $regionals,
                    'divisis' => $divisis
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Account Manager Add Modal Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat form data.'
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Get form data for dropdowns (called from routes)
     */
    public function getFormData()
    {
        try {
            $witels = Witel::orderBy('nama')->get();
            $regionals = Regional::orderBy('nama')->get();
            $divisis = Divisi::orderBy('nama')->get();

            return response()->json([
                'success' => true,
                'witels' => $witels,
                'regionals' => $regionals,
                'divisis' => $divisis
            ]);

        } catch (\Exception $e) {
            Log::error('Get Form Data Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data form.',
                'witels' => [],
                'regionals' => [],
                'divisis' => []
            ]);
        }
    }

    /**
     * ✅ EXISTING: Store a newly created account manager with better validation
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|max:255|min:3',
                'nik' => 'required|string|min:4|max:10|regex:/^\d{4,10}$/|unique:account_managers,nik',
                'witel_id' => 'required|exists:witel,id',
                'regional_id' => 'required|exists:regional,id',
                'divisi_ids' => 'required|string|min:1',
            ], [
                'nama.required' => 'Nama Account Manager wajib diisi.',
                'nama.min' => 'Nama Account Manager minimal 3 karakter.',
                'nama.max' => 'Nama Account Manager maksimal 255 karakter.',
                'nik.required' => 'NIK wajib diisi.',
                'nik.min' => 'NIK minimal 4 digit.',
                'nik.max' => 'NIK maksimal 10 digit.',
                'nik.regex' => 'NIK harus berupa angka 4-10 digit.',
                'nik.unique' => 'NIK sudah terdaftar, gunakan NIK lain.',
                'witel_id.required' => 'Witel wajib dipilih.',
                'witel_id.exists' => 'Witel tidak valid.',
                'regional_id.required' => 'Regional wajib dipilih.',
                'regional_id.exists' => 'Regional tidak valid.',
                'divisi_ids.required' => 'Minimal pilih satu divisi.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // ✅ ENHANCED: Additional validation for divisi_ids
            $divisiIds = explode(',', $request->divisi_ids);
            $divisiIds = array_filter(array_map('trim', $divisiIds)); // Remove empty values

            if (empty($divisiIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimal pilih satu divisi.'
                ], 422);
            }

            $validDivisiIds = Divisi::whereIn('id', $divisiIds)->pluck('id')->toArray();

            if (count($validDivisiIds) !== count($divisiIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Beberapa divisi yang dipilih tidak valid.'
                ], 422);
            }

            // Create Account Manager
            $accountManager = AccountManager::create([
                'nama' => trim($request->nama),
                'nik' => trim($request->nik),
                'witel_id' => $request->witel_id,
                'regional_id' => $request->regional_id,
            ]);

            // Attach divisions
            $accountManager->divisis()->attach($validDivisiIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Account Manager berhasil ditambahkan dengan ' . count($validDivisiIds) . ' divisi.',
                'data' => $accountManager->load(['witel', 'regional', 'divisis'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Account Manager Store Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Show the form for editing the specified account manager
     */
    public function edit($id)
    {
        try {
            $accountManager = AccountManager::with(['witel', 'regional', 'divisis'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $accountManager
            ]);

        } catch (\Exception $e) {
            Log::error('Account Manager Edit Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Account Manager tidak ditemukan.'
            ], 404);
        }
    }

    /**
     * ✅ EXISTING: Update the specified account manager
     */
    public function update(Request $request, $id)
    {
        try {
            $accountManager = AccountManager::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|max:255|min:3',
                'nik' => 'required|string|min:4|max:10|regex:/^\d{4,10}$/|unique:account_managers,nik,' . $id,
                'witel_id' => 'required|exists:witel,id',
                'regional_id' => 'required|exists:regional,id',
                'divisi_ids' => 'required|string|min:1',
            ], [
                'nama.required' => 'Nama Account Manager wajib diisi.',
                'nama.min' => 'Nama Account Manager minimal 3 karakter.',
                'nama.max' => 'Nama Account Manager maksimal 255 karakter.',
                'nik.required' => 'NIK wajib diisi.',
                'nik.min' => 'NIK minimal 4 digit.',
                'nik.max' => 'NIK maksimal 10 digit.',
                'nik.regex' => 'NIK harus berupa angka 4-10 digit.',
                'nik.unique' => 'NIK sudah terdaftar, gunakan NIK lain.',
                'witel_id.required' => 'Witel wajib dipilih.',
                'witel_id.exists' => 'Witel tidak valid.',
                'regional_id.required' => 'Regional wajib dipilih.',
                'regional_id.exists' => 'Regional tidak valid.',
                'divisi_ids.required' => 'Minimal pilih satu divisi.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // ✅ ENHANCED: Additional validation for divisi_ids
            $divisiIds = explode(',', $request->divisi_ids);
            $divisiIds = array_filter(array_map('trim', $divisiIds));

            if (empty($divisiIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimal pilih satu divisi.'
                ], 422);
            }

            $validDivisiIds = Divisi::whereIn('id', $divisiIds)->pluck('id')->toArray();

            if (count($validDivisiIds) !== count($divisiIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Beberapa divisi yang dipilih tidak valid.'
                ], 422);
            }

            // Update Account Manager
            $accountManager->update([
                'nama' => trim($request->nama),
                'nik' => trim($request->nik),
                'witel_id' => $request->witel_id,
                'regional_id' => $request->regional_id,
            ]);

            // Sync divisions (replace all existing relations)
            $accountManager->divisis()->sync($validDivisiIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Account Manager berhasil diperbarui dengan ' . count($validDivisiIds) . ' divisi.',
                'data' => $accountManager->load(['witel', 'regional', 'divisis'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Account Manager Update Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Remove the specified account manager
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $accountManager = AccountManager::findOrFail($id);

            // Check if Account Manager has related revenue data
            if ($accountManager->revenues()->exists()) {
                return back()->with('error', 'Account Manager tidak dapat dihapus karena masih memiliki data revenue terkait.');
            }

            // Detach divisions first
            $accountManager->divisis()->detach();

            // Delete the account manager
            $accountManager->delete();

            DB::commit();

            return back()->with('success', 'Account Manager berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Account Manager Delete Error: ' . $e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat menghapus Account Manager.');
        }
    }

    /**
     * ✅ NEW: Bulk delete account managers
     */
    public function bulkDelete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array|min:1',
                'ids.*' => 'exists:account_managers,id'
            ], [
                'ids.required' => 'Pilih minimal satu Account Manager untuk dihapus.',
                'ids.array' => 'Format data tidak valid.',
                'ids.min' => 'Pilih minimal satu Account Manager untuk dihapus.',
                'ids.*.exists' => 'Account Manager tidak ditemukan.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            DB::beginTransaction();

            $ids = $request->ids;
            $deleted = 0;
            $errors = [];
            $deletedDetails = [];

            foreach ($ids as $id) {
                try {
                    $accountManager = AccountManager::findOrFail($id);

                    // Check if has related revenue data
                    if ($accountManager->revenues()->exists()) {
                        $errors[] = "Account Manager '{$accountManager->nama}' tidak dapat dihapus karena memiliki data revenue terkait.";
                        continue;
                    }

                    $accountManagerInfo = [
                        'id' => $accountManager->id,
                        'nama' => $accountManager->nama,
                        'nik' => $accountManager->nik,
                        'witel' => $accountManager->witel->nama ?? 'Unknown',
                        'regional' => $accountManager->regional->nama ?? 'Unknown',
                        'divisis' => $accountManager->divisis->pluck('nama')->implode(', ')
                    ];

                    // Detach divisions first
                    $accountManager->divisis()->detach();

                    // Delete account manager
                    $accountManager->delete();
                    $deleted++;
                    $deletedDetails[] = $accountManagerInfo;

                } catch (\Exception $e) {
                    $errors[] = "Error menghapus Account Manager ID {$id}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Berhasil menghapus {$deleted} Account Manager.";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " data gagal dihapus.";
            }

            // ✅ LOG BULK DELETE ACTIVITY
            Log::info('Bulk Delete Account Manager Activity', [
                'deleted_count' => $deleted,
                'error_count' => count($errors),
                'user_ip' => $request->ip(),
                'deleted_details' => $deletedDetails
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'deleted' => $deleted,
                    'errors' => $errors,
                    'deleted_details' => $deletedDetails
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Account Manager Bulk Delete Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data Account Manager.'
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Import Account Managers dengan master data context
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // Max 10MB
        ], [
            'file.required' => 'File Excel wajib diupload.',
            'file.mimes' => 'File harus berformat Excel (.xlsx, .xls) atau CSV.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data' => [
                    'imported' => 0,
                    'updated' => 0,
                    'duplicates' => 0,
                    'errors' => 1,
                    'error_details' => [$validator->errors()->first()],
                    'master_data_available' => $this->getMasterDataSummary()
                ]
            ], 422);
        }

        try {
            // ✅ Use dedicated Import class with detailed tracking
            $import = new AccountManagerImport();
            Excel::import($import, $request->file('file'));

            // ✅ Get detailed results from Import class
            $results = $import->getImportResults();

            // ✅ ENHANCED: Add master data context to results
            $results['master_data_available'] = $this->getMasterDataSummary();
            $results['helper_info'] = $this->getImportHelperInfo();

            // ✅ Generate appropriate message
            $message = $this->generateImportMessage(
                $results['imported'],
                $results['updated'],
                $results['errors']
            );

            // Log import summary
            Log::info('Account Manager Import completed', [
                'file_name' => $request->file('file')->getClientOriginalName(),
                'results' => $results
            ]);

            return response()->json([
                'success' => $results['errors'] == 0 || ($results['imported'] + $results['updated']) > 0,
                'message' => $message,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Account Manager Import Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses file: ' . $e->getMessage(),
                'data' => [
                    'imported' => 0,
                    'updated' => 0,
                    'duplicates' => 0,
                    'errors' => 1,
                    'error_details' => [
                        'Error sistem: ' . $e->getMessage(),
                        'Pastikan file Excel dalam format yang benar dan tidak corrupt.'
                    ],
                    'master_data_available' => $this->getMasterDataSummary(),
                    'helper_info' => $this->getImportHelperInfo()
                ]
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Download template Excel (menggunakan AccountManagerTemplateExport)
     */
    public function downloadTemplate()
    {
        try {
            $filename = 'template_account_manager_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(new AccountManagerTemplateExport(), $filename);

        } catch (\Exception $e) {
            Log::error('Download Template Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mendownload template: ' . $e->getMessage());
        }
    }

    /**
     * ✅ EXISTING: Export Account Manager data (menggunakan AccountManagerExport)
     */
    public function export(Request $request)
    {
        try {
            $filename = 'account_managers_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(new AccountManagerExport(), $filename);

        } catch (\Exception $e) {
            Log::error('Export Account Manager Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal export data Account Manager: ' . $e->getMessage());
        }
    }

    /**
     * ✅ EXISTING: Get master data summary untuk error context
     */
    public function getMasterDataSummary()
    {
        try {
            return [
                'witels' => [
                    'count' => Witel::count(),
                    'sample' => Witel::orderBy('nama')->limit(5)->pluck('nama')->toArray()
                ],
                'regionals' => [
                    'count' => Regional::count(),
                    'sample' => Regional::orderBy('nama')->limit(5)->pluck('nama')->toArray()
                ],
                'divisis' => [
                    'count' => Divisi::count(),
                    'sample' => Divisi::orderBy('nama')->limit(5)->pluck('nama')->toArray()
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Get Master Data Summary Error: ' . $e->getMessage());
            return [
                'witels' => ['count' => 0, 'sample' => []],
                'regionals' => ['count' => 0, 'sample' => []],
                'divisis' => ['count' => 0, 'sample' => []]
            ];
        }
    }

    /**
     * ✅ EXISTING: Get import helper info
     */
    private function getImportHelperInfo()
    {
        return [
            'template_available' => true,
            'master_data_sheets' => [
                'Master Witel' => 'Berisi daftar semua Witel yang valid',
                'Master Regional' => 'Berisi daftar semua Regional yang valid',
                'Master Divisi' => 'Berisi daftar semua Divisi yang valid'
            ],
            'tips' => [
                '💡 Download template untuk melihat format yang benar',
                '💡 Periksa sheet "Master Data" untuk data yang valid',
                '💡 Gunakan export existing data sebagai referensi',
                '💡 Satu NIK bisa memiliki multiple divisi (buat baris terpisah)'
            ]
        ];
    }

    /**
     * ✅ EXISTING: Generate import message based on results
     */
    private function generateImportMessage($imported, $updated, $errors)
    {
        $messages = [];

        if ($imported > 0) {
            $messages[] = "{$imported} Account Manager baru berhasil ditambahkan";
        }

        if ($updated > 0) {
            $messages[] = "{$updated} Account Manager berhasil diperbarui";
        }

        if ($errors > 0) {
            $messages[] = "{$errors} baris gagal diproses";
        }

        if (empty($messages)) {
            return 'Tidak ada data yang diproses.';
        }

        $result = implode(', ', $messages) . '.';

        if ($errors === 0) {
            return 'Import berhasil! ' . $result;
        } elseif ($imported > 0 || $updated > 0) {
            return 'Import selesai dengan beberapa error. ' . $result;
        } else {
            return 'Import gagal. ' . $result;
        }
    }

    /**
     * ✅ EXISTING: Search Account Managers for autocomplete with comprehensive search
     */
    public function search(Request $request)
    {
        try {
            $searchTerm = trim($request->get('search', ''));

            if (strlen($searchTerm) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $accountManagers = AccountManager::where(function($query) use ($searchTerm) {
                $query->where('nama', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('nik', 'LIKE', "%{$searchTerm}%")
                      ->orWhereHas('witel', function($subQuery) use ($searchTerm) {
                          $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                      })
                      ->orWhereHas('regional', function($subQuery) use ($searchTerm) {
                          $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                      })
                      ->orWhereHas('divisis', function($subQuery) use ($searchTerm) {
                          $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                      });
            })
            ->with(['divisis', 'witel', 'regional'])
            ->orderBy('nama')
            ->limit(10)
            ->get(['id', 'nama', 'nik']);

            return response()->json([
                'success' => true,
                'data' => $accountManagers
            ]);

        } catch (\Exception $e) {
            Log::error('Account Manager Search Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat pencarian.',
                'data' => []
            ]);
        }
    }

    /**
     * ✅ EXISTING: Get Account Manager divisions for dropdown
     */
    public function getDivisions($id)
    {
        try {
            $accountManager = AccountManager::with('divisis')->findOrFail($id);

            return response()->json([
                'success' => true,
                'divisis' => $accountManager->divisis
            ]);

        } catch (\Exception $e) {
            Log::error('Get Account Manager Divisions Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Account Manager tidak ditemukan.',
                'divisis' => []
            ]);
        }
    }

    /**
     * ✅ EXISTING: Get dropdown data for forms
     */
    public function getDivisi()
    {
        try {
            $divisis = Divisi::orderBy('nama')->get(['id', 'nama']);

            return response()->json([
                'success' => true,
                'data' => $divisis
            ]);

        } catch (\Exception $e) {
            Log::error('Get Divisi Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'data' => []
            ]);
        }
    }

    /**
     * ✅ EXISTING: Get regional data for forms
     */
    public function getRegional()
    {
        try {
            $regionals = Regional::orderBy('nama')->get(['id', 'nama']);

            return response()->json([
                'success' => true,
                'data' => $regionals
            ]);

        } catch (\Exception $e) {
            Log::error('Get Regional Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'data' => []
            ]);
        }
    }

    /**
     * ✅ EXISTING: Get statistics for dashboard
     */
    public function getStatistics()
    {
        try {
            $totalAccountManagers = AccountManager::count();
            $recentAccountManagers = AccountManager::where('created_at', '>=', now()->subDays(30))->count();
            $activeAccountManagers = AccountManager::whereHas('revenues')->distinct()->count();

            // Count by divisi
            $divisiStats = DB::table('account_manager_divisi')
                ->join('divisi', 'account_manager_divisi.divisi_id', '=', 'divisi.id')
                ->select('divisi.nama', DB::raw('count(*) as total'))
                ->groupBy('divisi.id', 'divisi.nama')
                ->orderBy('total', 'desc')
                ->get();

            // Count by regional
            $regionalStats = AccountManager::with('regional')
                ->get()
                ->groupBy('regional.nama')
                ->map(function ($items) {
                    return $items->count();
                })
                ->sortDesc();

            return [
                'total_account_managers' => $totalAccountManagers,
                'recent_account_managers' => $recentAccountManagers,
                'active_account_managers' => $activeAccountManagers,
                'inactive_account_managers' => $totalAccountManagers - $activeAccountManagers,
                'divisi_stats' => $divisiStats,
                'regional_stats' => $regionalStats
            ];

        } catch (\Exception $e) {
            Log::error('Account Manager Statistics Error: ' . $e->getMessage());

            return [
                'total_account_managers' => 0,
                'recent_account_managers' => 0,
                'active_account_managers' => 0,
                'inactive_account_managers' => 0,
                'divisi_stats' => collect(),
                'regional_stats' => collect()
            ];
        }
    }

    /**
     * ✅ EXISTING: Validate NIK in real-time
     */
    public function validateNik(Request $request)
    {
        try {
            $nik = trim($request->get('nik', ''));
            $currentId = $request->get('current_id', null);

            if (empty($nik)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'NIK tidak boleh kosong.'
                ]);
            }

            // Check format (4-10 digits)
            if (!preg_match('/^\d{4,10}$/', $nik)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'NIK harus berupa angka 4-10 digit.'
                ]);
            }

            // Check uniqueness
            $query = AccountManager::where('nik', $nik);
            if ($currentId) {
                $query->where('id', '!=', $currentId);
            }

            if ($query->exists()) {
                return response()->json([
                    'valid' => false,
                    'message' => 'NIK sudah terdaftar dalam sistem.'
                ]);
            }

            return response()->json([
                'valid' => true,
                'message' => 'NIK valid.'
            ]);

        } catch (\Exception $e) {
            Log::error('NIK Validation Error: ' . $e->getMessage());

            return response()->json([
                'valid' => false,
                'message' => 'Terjadi kesalahan saat validasi NIK.'
            ]);
        }
    }

    /**
     * ✅ EXISTING: Get Account Manager data for API (alias untuk edit)
     */
    public function getAccountManagerData($id)
    {
        return $this->edit($id);
    }

    /**
     * ✅ EXISTING: Update Account Manager via API (alias untuk update)
     */
    public function updateAccountManager(Request $request, $id)
    {
        return $this->update($request, $id);
    }
}