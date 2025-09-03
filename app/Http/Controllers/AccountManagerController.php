<?php

namespace App\Http\Controllers;

use App\Models\AccountManager;
use App\Models\User;
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
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class AccountManagerController extends Controller
{
    // ✅ NEW: Memory management constants
    const MAX_ERROR_DETAILS_DISPLAY = 500;     // Increased from 100 to 500
    const MAX_WARNING_DETAILS_DISPLAY = 250;   // Increased from 50 to 250
    const MAX_BATCH_SIZE = 2500;               // Increased from 1000 to 2500
    const MEMORY_LIMIT_MB = 1024;              // Increased from 512MB to 1GB
    const MAX_EXECUTION_TIME = 10000;            // Increased from 5 to 10 minutes
    const MAX_IMPORT_ROWS = 10000;             // New: Maximum allowed rows per import

    /**
     * ✅ EXISTING: Display a listing of account managers with comprehensive search
     */
    public function index(Request $request)
    {
        try {
            $query = AccountManager::with(['witel', 'regional', 'divisis', 'user']);

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

            // ✅ NEW: Filter by user registration status
            if ($request->has('user_status')) {
                if ($request->user_status === 'registered') {
                    $query->whereHas('user');
                } elseif ($request->user_status === 'not_registered') {
                    $query->whereDoesntHave('user');
                }
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
     * ✅ EXISTING: Show the form for creating new account manager
     */
    public function create()
    {
        try {
            $witels = Witel::orderBy('nama')->get();
            $regionals = Regional::orderBy('nama')->get();
            $divisis = Divisi::orderBy('nama')->get();

            return view('account-managers.create', compact('witels', 'regionals', 'divisis'));

        } catch (\Exception $e) {
            Log::error('Account Manager Create Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat form.');
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
                'data' => $accountManager->load(['witel', 'regional', 'divisis', 'user'])
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
            $accountManager = AccountManager::with(['witel', 'regional', 'divisis', 'user'])->findOrFail($id);

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
                'data' => $accountManager->load(['witel', 'regional', 'divisis', 'user'])
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
     * 🔧 FIXED: Remove the specified account manager with CASCADE DELETE
     *
     * MAJOR CHANGE: Sekarang akan menghapus semua revenue terkait terlebih dahulu (CASCADE DELETE)
     * bukan menolak penghapusan seperti sebelumnya
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $accountManager = AccountManager::findOrFail($id);

            // 🔧 CRITICAL FIX: CASCADE DELETE - Hapus revenue terkait dulu, bukan tolak penghapusan
            $relatedRevenuesCount = $accountManager->revenues()->count();

            if ($relatedRevenuesCount > 0) {
                // Delete all related revenues first (CASCADE DELETE)
                $accountManager->revenues()->delete();
                Log::info("CASCADE DELETE: Deleted {$relatedRevenuesCount} related revenues for Account Manager ID: {$id}");
            }

            // Check if Account Manager has user account - masih perlu dicek untuk keamanan
            $hasUserAccount = $accountManager->user ? true : false;
            $userEmail = null;

            if ($hasUserAccount) {
                $userEmail = $accountManager->user->email;
                // Delete user account as well (CASCADE DELETE)
                $accountManager->user->delete();
                Log::info("CASCADE DELETE: Deleted user account {$userEmail} for Account Manager ID: {$id}");
            }

            // Detach divisions first
            $accountManager->divisis()->detach();

            // Delete the account manager
            $accountManagerName = $accountManager->nama;
            $accountManager->delete();

            DB::commit();

            // 🔧 ENHANCED SUCCESS MESSAGE: Informasikan apa saja yang dihapus
            $message = "Account Manager '{$accountManagerName}' berhasil dihapus";

            if ($relatedRevenuesCount > 0) {
                $message .= " beserta {$relatedRevenuesCount} data revenue terkait";
            }

            if ($hasUserAccount) {
                $message .= " dan akun user ({$userEmail})";
            }

            $message .= ".";

            // Log successful deletion
            Log::info('Account Manager CASCADE DELETE completed', [
                'account_manager_id' => $id,
                'account_manager_name' => $accountManagerName,
                'deleted_revenues_count' => $relatedRevenuesCount,
                'deleted_user_account' => $hasUserAccount ? $userEmail : 'none',
                'user_ip' => request()->ip(),
                'timestamp' => now()
            ]);

            // Handle different response types
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'deleted_account_manager' => $accountManagerName,
                        'deleted_revenues_count' => $relatedRevenuesCount,
                        'deleted_user_account' => $hasUserAccount
                    ]
                ]);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Account Manager Delete Error: ' . $e->getMessage(), [
                'account_manager_id' => $id,
                'error_trace' => $e->getTraceAsString()
            ]);

            if (request()->ajax() || request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghapus Account Manager: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Terjadi kesalahan saat menghapus Account Manager.');
        }
    }

    /**
     * ✅ EXISTING: Check if Account Manager has registered user account
     */
    public function checkUserStatus($id)
    {
        try {
            $accountManager = AccountManager::with('user')->findOrFail($id);
            $hasUser = $accountManager->user ? true : false;

            $userData = null;
            if ($hasUser) {
                $userData = [
                    'id' => $accountManager->user->id,
                    'name' => $accountManager->user->name,
                    'email' => $accountManager->user->email,
                    'role' => $accountManager->user->role,
                    'created_at' => $accountManager->user->created_at,
                    'last_login_at' => $accountManager->user->last_login_at
                ];
            }

            return response()->json([
                'success' => true,
                'account_manager' => [
                    'id' => $accountManager->id,
                    'nama' => $accountManager->nama,
                    'nik' => $accountManager->nik
                ],
                'has_user' => $hasUser,
                'user_data' => $userData
            ]);

        } catch (\Exception $e) {
            Log::error('Check User Status Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Account Manager tidak ditemukan.'
            ], 404);
        }
    }

    /**
     * ✅ EXISTING: Change password for Account Manager's user account
     */
    public function changePassword(Request $request, $id)
    {
        try {
            $accountManager = AccountManager::with('user')->findOrFail($id);

            if (!$accountManager->user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account Manager belum memiliki akun user terdaftar.'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'new_password' => 'required|min:8|confirmed',
                'new_password_confirmation' => 'required'
            ], [
                'new_password.required' => 'Password baru wajib diisi.',
                'new_password.min' => 'Password minimal 8 karakter.',
                'new_password.confirmed' => 'Konfirmasi password tidak cocok.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Update password
            $accountManager->user->update([
                'password' => Hash::make($request->new_password)
            ]);

            DB::commit();

            // Log password change activity
            Log::info('Password changed for Account Manager', [
                'account_manager_id' => $accountManager->id,
                'account_manager_name' => $accountManager->nama,
                'user_id' => $accountManager->user->id,
                'admin_ip' => $request->ip(),
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah untuk ' . $accountManager->nama . ' (' . $accountManager->user->email . ')'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Change Password Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengubah password: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Get Account Manager user status for password change feature (alternative endpoint)
     */
    public function getUserStatus($id)
    {
        try {
            $accountManager = AccountManager::with('user')->findOrFail($id);

            return response()->json([
                'success' => true,
                'account_manager' => [
                    'id' => $accountManager->id,
                    'nama' => $accountManager->nama,
                    'nik' => $accountManager->nik,
                    'witel' => $accountManager->witel->nama ?? null,
                    'regional' => $accountManager->regional->nama ?? null
                ],
                'has_user_account' => $accountManager->user ? true : false,
                'user_email' => $accountManager->user ? $accountManager->user->email : null,
                'user_created_at' => $accountManager->user ? $accountManager->user->created_at->format('d M Y H:i') : null,
                'can_change_password' => $accountManager->user ? true : false
            ]);

        } catch (\Exception $e) {
            Log::error('Get User Status Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Account Manager tidak ditemukan.'
            ], 404);
        }
    }

    /**
     * ✅ EXISTING: Reset/Delete user account for Account Manager
     */
    public function resetUserAccount($id)
    {
        try {
            $accountManager = AccountManager::with('user')->findOrFail($id);

            if (!$accountManager->user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account Manager tidak memiliki akun user yang terdaftar.'
                ], 422);
            }

            DB::beginTransaction();

            $userEmail = $accountManager->user->email;

            // Delete user account
            $accountManager->user->delete();

            DB::commit();

            // Log account deletion
            Log::info('User account deleted for Account Manager', [
                'account_manager_id' => $accountManager->id,
                'account_manager_name' => $accountManager->nama,
                'deleted_email' => $userEmail,
                'admin_ip' => request()->ip(),
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Akun user berhasil dihapus untuk ' . $accountManager->nama . ' (' . $userEmail . ')'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reset User Account Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus akun user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Bulk delete account managers (for selected items)
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
            $totalDeletedRevenues = 0;
            $totalDeletedUsers = 0;

            foreach ($ids as $id) {
                try {
                    $accountManager = AccountManager::with('user')->findOrFail($id);

                    // 🔧 CRITICAL FIX: CASCADE DELETE logic - tidak lagi reject, tapi delete semua
                    $relatedRevenuesCount = $accountManager->revenues()->count();
                    $hasUserAccount = $accountManager->user ? true : false;
                    $userEmail = $hasUserAccount ? $accountManager->user->email : null;

                    // Delete related revenues (CASCADE DELETE)
                    if ($relatedRevenuesCount > 0) {
                        $accountManager->revenues()->delete();
                        $totalDeletedRevenues += $relatedRevenuesCount;
                    }

                    // Delete user account if exists (CASCADE DELETE)
                    if ($hasUserAccount) {
                        $accountManager->user->delete();
                        $totalDeletedUsers++;
                    }

                    $accountManagerInfo = [
                        'id' => $accountManager->id,
                        'nama' => $accountManager->nama,
                        'nik' => $accountManager->nik,
                        'witel' => $accountManager->witel->nama ?? 'Unknown',
                        'regional' => $accountManager->regional->nama ?? 'Unknown',
                        'divisis' => $accountManager->divisis->pluck('nama')->implode(', '),
                        'deleted_revenues_count' => $relatedRevenuesCount,
                        'deleted_user_account' => $userEmail
                    ];

                    // Detach divisions first
                    $accountManager->divisis()->detach();

                    // Delete account manager
                    $accountManager->delete();
                    $deleted++;
                    $deletedDetails[] = $accountManagerInfo;

                } catch (\Exception $e) {
                    $errors[] = "Error menghapus Account Manager ID {$id}: " . $e->getMessage();
                    Log::error("Bulk Delete Error for Account Manager ID {$id}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            DB::commit();

            // 🔧 ENHANCED SUCCESS MESSAGE
            $message = "Berhasil menghapus {$deleted} Account Manager";
            if ($totalDeletedRevenues > 0) {
                $message .= " beserta {$totalDeletedRevenues} data revenue terkait";
            }
            if ($totalDeletedUsers > 0) {
                $message .= " dan {$totalDeletedUsers} akun user";
            }
            $message .= ".";

            if (!empty($errors)) {
                $message .= " " . count($errors) . " data gagal dihapus.";
            }

            // ✅ LOG BULK DELETE ACTIVITY
            Log::info('Bulk Delete Account Manager Activity', [
                'deleted_count' => $deleted,
                'total_deleted_revenues' => $totalDeletedRevenues,
                'total_deleted_users' => $totalDeletedUsers,
                'error_count' => count($errors),
                'user_ip' => $request->ip(),
                'deleted_details' => $deletedDetails
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'deleted' => $deleted,
                    'total_deleted_revenues' => $totalDeletedRevenues,
                    'total_deleted_users' => $totalDeletedUsers,
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
     * 🆕 NEW: Bulk delete ALL account managers with filter support
     *
     * Route: POST /account-manager/bulk-delete-all
     * Function: Menghapus SEMUA Account Manager sesuai filter yang aktif
     */
    public function bulkDeleteAll(Request $request)
    {
        try {
            // 🆕 ENHANCED: Memory monitoring untuk data besar
            $this->monitorMemoryUsage('bulk_delete_all_start');

            $query = AccountManager::query();

            // Apply filters from request
            if ($request->has('witel_filter') && !empty($request->witel_filter)) {
                $query->where('witel_id', $request->witel_filter);
            }

            if ($request->has('regional_filter') && !empty($request->regional_filter)) {
                $query->where('regional_id', $request->regional_filter);
            }

            if ($request->has('divisi_filter') && !empty($request->divisi_filter)) {
                $query->whereHas('divisis', function($q) use ($request) {
                    $q->where('divisi.id', $request->divisi_filter);
                });
            }

            if ($request->has('search_filter') && !empty($request->search_filter)) {
                $searchTerm = trim($request->search_filter);
                $query->where(function($q) use ($searchTerm) {
                    $q->where('nama', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('nik', 'LIKE', "%{$searchTerm}%");
                });
            }

            if ($request->has('user_status_filter') && !empty($request->user_status_filter)) {
                if ($request->user_status_filter === 'registered') {
                    $query->whereHas('user');
                } elseif ($request->user_status_filter === 'not_registered') {
                    $query->whereDoesntHave('user');
                }
            }

            // Get count and preview before delete
            $totalCount = $query->count();

            if ($totalCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data Account Manager yang sesuai dengan filter.'
                ], 422);
            }

            // 🆕 ENHANCED: Handle large datasets dengan chunking
            if ($totalCount > self::MAX_BATCH_SIZE) {
                return $this->handleLargeBulkDeleteAll($query, $totalCount, $request);
            }

            // For smaller datasets, use regular approach
            $accountManagers = $query->with(['user', 'revenues'])->get();

            // Calculate what will be deleted (CASCADE info)
            $totalRevenueCount = 0;
            $totalUserCount = 0;
            $deletedDetails = [];

            foreach ($accountManagers as $am) {
                $revenueCount = $am->revenues()->count();
                $hasUser = $am->user ? true : false;

                $totalRevenueCount += $revenueCount;
                if ($hasUser) {
                    $totalUserCount++;
                }

                $deletedDetails[] = [
                    'id' => $am->id,
                    'nama' => $am->nama,
                    'nik' => $am->nik,
                    'witel' => $am->witel->nama ?? 'Unknown',
                    'regional' => $am->regional->nama ?? 'Unknown',
                    'revenue_count' => $revenueCount,
                    'has_user' => $hasUser,
                    'user_email' => $hasUser ? $am->user->email : null
                ];
            }

            DB::beginTransaction();

            $deletedCount = 0;
            $deletedRevenuesTotal = 0;
            $deletedUsersTotal = 0;

            // Perform CASCADE DELETE for each Account Manager
            foreach ($accountManagers as $am) {
                try {
                    // Delete related revenues first (CASCADE DELETE)
                    $revenueCount = $am->revenues()->count();
                    if ($revenueCount > 0) {
                        $am->revenues()->delete();
                        $deletedRevenuesTotal += $revenueCount;
                    }

                    // Delete user account if exists (CASCADE DELETE)
                    if ($am->user) {
                        $am->user->delete();
                        $deletedUsersTotal++;
                    }

                    // Detach divisions
                    $am->divisis()->detach();

                    // Delete account manager
                    $am->delete();
                    $deletedCount++;

                } catch (\Exception $e) {
                    Log::error("Error deleting Account Manager ID {$am->id} in bulk delete all", [
                        'error' => $e->getMessage(),
                        'am_id' => $am->id,
                        'am_name' => $am->nama
                    ]);
                    // Continue with other deletions
                }
            }

            DB::commit();

            // Generate comprehensive success message
            $message = "Berhasil menghapus {$deletedCount} dari {$totalCount} Account Manager";

            if ($deletedRevenuesTotal > 0) {
                $message .= " beserta {$deletedRevenuesTotal} data revenue terkait";
            }

            if ($deletedUsersTotal > 0) {
                $message .= " dan {$deletedUsersTotal} akun user";
            }

            $message .= ".";

            // Log bulk delete all activity
            Log::info('Bulk Delete All Account Manager Activity', [
                'total_count' => $totalCount,
                'deleted_count' => $deletedCount,
                'deleted_revenues_total' => $deletedRevenuesTotal,
                'deleted_users_total' => $deletedUsersTotal,
                'filters' => $request->only(['witel_filter', 'regional_filter', 'divisi_filter', 'search_filter', 'user_status_filter']),
                'user_ip' => $request->ip(),
                'deleted_preview' => array_slice($deletedDetails, 0, 10) // First 10 for preview
            ]);

            $this->monitorMemoryUsage('bulk_delete_all_end');

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'total_count' => $totalCount,
                    'deleted_count' => $deletedCount,
                    'deleted_revenues_total' => $deletedRevenuesTotal,
                    'deleted_users_total' => $deletedUsersTotal,
                    'preview_sample' => array_slice($deletedDetails, 0, 10) // First 10 for preview
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Account Manager Bulk Delete All Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data Account Manager: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🆕 NEW: Handle large bulk delete with chunking untuk data ribuan
     */
    private function handleLargeBulkDeleteAll($query, $totalCount, $request)
    {
        try {
            $deletedCount = 0;
            $deletedRevenuesTotal = 0;
            $deletedUsersTotal = 0;
            $batchSize = min(self::MAX_BATCH_SIZE, 500); // Use smaller chunks for safety

            Log::info("Starting large bulk delete for {$totalCount} Account Managers in chunks of {$batchSize}");

            DB::beginTransaction();

            // Process in chunks to avoid memory issues
            $query->chunk($batchSize, function ($accountManagers) use (&$deletedCount, &$deletedRevenuesTotal, &$deletedUsersTotal) {
                foreach ($accountManagers as $am) {
                    try {
                        // Delete related revenues first (CASCADE DELETE)
                        $revenueCount = $am->revenues()->count();
                        if ($revenueCount > 0) {
                            $am->revenues()->delete();
                            $deletedRevenuesTotal += $revenueCount;
                        }

                        // Delete user account if exists (CASCADE DELETE)
                        if ($am->user) {
                            $am->user->delete();
                            $deletedUsersTotal++;
                        }

                        // Detach divisions
                        $am->divisis()->detach();

                        // Delete account manager
                        $am->delete();
                        $deletedCount++;

                        // Monitor memory every 100 deletions
                        if ($deletedCount % 100 === 0) {
                            $this->monitorMemoryUsage("bulk_delete_progress_{$deletedCount}");
                        }

                    } catch (\Exception $e) {
                        Log::error("Error deleting Account Manager ID {$am->id} in large bulk delete", [
                            'error' => $e->getMessage(),
                            'am_id' => $am->id,
                            'am_name' => $am->nama ?? 'Unknown'
                        ]);
                        // Continue with other deletions
                    }
                }

                // Force garbage collection after each chunk
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            });

            DB::commit();

            $message = "Berhasil menghapus {$deletedCount} dari {$totalCount} Account Manager (proses chunking)";

            if ($deletedRevenuesTotal > 0) {
                $message .= " beserta {$deletedRevenuesTotal} data revenue terkait";
            }

            if ($deletedUsersTotal > 0) {
                $message .= " dan {$deletedUsersTotal} akun user";
            }

            $message .= ".";

            // Log large bulk delete activity
            Log::info('Large Bulk Delete All Account Manager Activity', [
                'total_count' => $totalCount,
                'deleted_count' => $deletedCount,
                'deleted_revenues_total' => $deletedRevenuesTotal,
                'deleted_users_total' => $deletedUsersTotal,
                'batch_size' => $batchSize,
                'filters' => $request->only(['witel_filter', 'regional_filter', 'divisi_filter', 'search_filter', 'user_status_filter']),
                'user_ip' => $request->ip(),
                'processing_method' => 'chunked'
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'total_count' => $totalCount,
                    'deleted_count' => $deletedCount,
                    'deleted_revenues_total' => $deletedRevenuesTotal,
                    'deleted_users_total' => $deletedUsersTotal,
                    'processing_method' => 'chunked',
                    'batch_size' => $batchSize
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Large Account Manager Bulk Delete All Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 🔧 COMPLETELY REWRITTEN: Import Account Managers dengan robust error handling dan optimal response untuk data besar
     */
    public function import(Request $request)
    {
        // 🆕 ENHANCED: Set memory dan execution time limits untuk data besar
        $this->setMemoryAndTimeConfiguration();
        $this->monitorMemoryUsage('import_start');

        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // Max 10MB
        ], [
            'file.required' => 'File Excel wajib diupload.',
            'file.mimes' => 'File harus berformat Excel (.xlsx, .xls) atau CSV.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        if ($validator->fails()) {
            return $this->createOptimizedErrorResponse([
                'validation_error' => $validator->errors()->first()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $fileSize = $file->getSize();
            $fileName = $file->getClientOriginalName();

            // 🆕 ENHANCED: Pre-validate file size dan estimate processing time
            $estimatedRows = $this->estimateRowCount($fileSize);

            Log::info('Starting Account Manager import', [
                'filename' => $fileName,
                'size_bytes' => $fileSize,
                'size_mb' => round($fileSize / 1024 / 1024, 2),
                'estimated_rows' => $estimatedRows,
                'user_ip' => $request->ip()
            ]);

            // 🆕 ENHANCED: Use optimized import class dengan memory management
            $import = new AccountManagerImport();

            // 🔧 CRITICAL FIX: Execute import in transaction for data integrity
            DB::beginTransaction();

            try {
                // Start import process
                Excel::import($import, $file);

                // 🆕 ENHANCED: Get comprehensive import results
                $rawResults = $import->getImportResults();

                $this->monitorMemoryUsage('import_after_processing');

                // 🔧 CRITICAL FIX: Format response untuk data besar dengan primitives only
                $optimizedResults = $this->formatImportResultsForLargeData($rawResults, $estimatedRows);

                DB::commit();

                // 🆕 ENHANCED: Log successful import dengan detail
                Log::info('Account Manager import completed successfully', [
                    'file' => $fileName,
                    'file_size_mb' => round($fileSize / 1024 / 1024, 2),
                    'results' => [
                        'total_rows' => $optimizedResults['total_rows'],
                        'imported' => $optimizedResults['imported'],
                        'updated' => $optimizedResults['updated'],
                        'errors' => $optimizedResults['errors'],
                        'duplicates' => $optimizedResults['duplicates'],
                        'success_rate' => $optimizedResults['success_percentage']
                    ],
                    'user_ip' => $request->ip(),
                    'processing_time_seconds' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
                ]);

                $this->monitorMemoryUsage('import_success_end');

                // 🔧 FIX: Generate appropriate success message
                $message = $this->generateOptimizedImportMessage($optimizedResults);

                return response()->json([
                    'success' => ($optimizedResults['errors'] === 0 || $optimizedResults['success_rows'] > 0),
                    'message' => $message,
                    'data' => $optimizedResults
                ]);

            } catch (\Exception $importException) {
                DB::rollBack();

                // 🆕 ENHANCED: Handle specific import errors
                if ($this->isMemoryLimitError($importException)) {
                    return $this->handleMemoryLimitError($fileName, $fileSize);
                } elseif ($this->isTimeoutError($importException)) {
                    return $this->handleTimeoutError($fileName, $fileSize);
                } else {
                    throw $importException;
                }
            }

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // 🆕 ENHANCED: Handle Excel validation errors dengan optimal response
            $failures = collect($e->failures());
            $limitedErrors = $failures->take(self::MAX_ERROR_DETAILS_DISPLAY)->map(function ($failure) {
                return "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            })->toArray();

            Log::error('Account Manager import validation error', [
                'total_failures' => $failures->count(),
                'file' => $fileName ?? 'unknown',
                'user_ip' => $request->ip()
            ]);

            return $this->createOptimizedErrorResponse([
                'validation_failures' => $limitedErrors,
                'total_failures' => $failures->count(),
                'has_more_errors' => $failures->count() > self::MAX_ERROR_DETAILS_DISPLAY
            ], 422);

        } catch (\Exception $e) {
            $this->monitorMemoryUsage('import_error_end');

            Log::error('Account Manager import general error: ' . $e->getMessage(), [
                'file' => $fileName ?? 'unknown',
                'user_ip' => $request->ip(),
                'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'error_trace' => $e->getTraceAsString()
            ]);

            // 🔧 CRITICAL FIX: Handle array to string conversion error yang umum terjadi
            if (strpos($e->getMessage(), 'Array to string conversion') !== false) {
                return response()->json([
                    'success' => true,
                    'message' => 'Import kemungkinan berhasil, namun terjadi error formatting response. Silakan refresh halaman untuk melihat hasil.',
                    'data' => [
                        'total_rows' => 0,
                        'success_rows' => 0,
                        'failed_rows' => 0,
                        'imported' => 0,
                        'updated' => 0,
                        'duplicates' => 0,
                        'errors' => 0,
                        'warning' => 'Response formatting issue - data mungkin sudah berhasil diimport',
                        'error_details' => ['Response formatting error - refresh halaman untuk melihat hasil']
                    ]
                ]);
            }

            return $this->createOptimizedErrorResponse([
                'general_error' => $e->getMessage(),
                'suggestion' => 'Coba dengan file yang lebih kecil atau refresh halaman jika import mungkin sudah berhasil'
            ], 500);
        }
    }

    /**
     * 🆕 NEW: Format import results optimized untuk data besar
     */
    private function formatImportResultsForLargeData($rawResults, $estimatedRows = 0)
    {
        // 🔧 CRITICAL: Ensure all values are primitives (tidak ada nested arrays)
        $optimized = [
            'total_rows' => (int) ($rawResults['total_rows'] ?? $estimatedRows ?? 0),
            'success_rows' => (int) (($rawResults['imported'] ?? 0) + ($rawResults['updated'] ?? 0)),
            'failed_rows' => (int) ($rawResults['errors'] ?? 0),
            'imported' => (int) ($rawResults['imported'] ?? 0),
            'updated' => (int) ($rawResults['updated'] ?? 0),
            'duplicates' => (int) ($rawResults['duplicates'] ?? 0),
            'errors' => (int) ($rawResults['errors'] ?? 0),
            'conflicts' => (int) ($rawResults['conflicts'] ?? 0),

            // 🔧 CRITICAL: Calculate success percentage
            'success_percentage' => 0,

            // 🆕 ENHANCED: Limit error details untuk data besar
            'error_details' => [],
            'warning_details' => [],
            'success_details' => [],

            // 🆕 NEW: Additional metadata untuk large data
            'has_more_errors' => false,
            'has_more_warnings' => false,
            'total_error_count' => 0,
            'total_warning_count' => 0,
            'processing_method' => 'optimized_for_large_data',
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),

            // 🆕 NEW: Data master information
            'master_data_available' => $this->getMasterDataSummary(),
            'helper_info' => $this->getImportHelperInfo()
        ];

        // Calculate success percentage
        if ($optimized['total_rows'] > 0) {
            $optimized['success_percentage'] = round(($optimized['success_rows'] / $optimized['total_rows']) * 100, 2);
        }

        // 🔧 CRITICAL: Process error details dengan limit untuk memory efficiency
        if (isset($rawResults['error_details']) && is_array($rawResults['error_details'])) {
            $totalErrors = count($rawResults['error_details']);
            $optimized['total_error_count'] = $totalErrors;
            $optimized['has_more_errors'] = $totalErrors > self::MAX_ERROR_DETAILS_DISPLAY;

            // Limit error details dan convert ke string
            $limitedErrors = array_slice($rawResults['error_details'], 0, self::MAX_ERROR_DETAILS_DISPLAY);
            $optimized['error_details'] = array_map('strval', $limitedErrors);
        }

        // 🔧 CRITICAL: Process warning details dengan limit
        if (isset($rawResults['warning_details']) && is_array($rawResults['warning_details'])) {
            $totalWarnings = count($rawResults['warning_details']);
            $optimized['total_warning_count'] = $totalWarnings;
            $optimized['has_more_warnings'] = $totalWarnings > self::MAX_WARNING_DETAILS_DISPLAY;

            // Limit warning details dan convert ke string
            $limitedWarnings = array_slice($rawResults['warning_details'], 0, self::MAX_WARNING_DETAILS_DISPLAY);
            $optimized['warning_details'] = array_map('strval', $limitedWarnings);
        }

        // 🆕 NEW: Add success details (limited)
        if (isset($rawResults['success_details']) && is_array($rawResults['success_details'])) {
            $limitedSuccess = array_slice($rawResults['success_details'], 0, 10); // Max 10 success details
            $optimized['success_details'] = array_map('strval', $limitedSuccess);
        }

        return $optimized;
    }

    /**
     * 🆕 NEW: Generate optimized import message untuk large data
     */
    private function generateOptimizedImportMessage($data)
    {
        $messages = [];

        if ($data['success_rows'] > 0) {
            $messages[] = "✅ {$data['success_rows']} data berhasil diproses";
        }

        if ($data['imported'] > 0) {
            $messages[] = "🆕 {$data['imported']} data baru ditambahkan";
        }

        if ($data['updated'] > 0) {
            $messages[] = "🔄 {$data['updated']} data diperbarui";
        }

        if ($data['duplicates'] > 0) {
            $messages[] = "⚠️ {$data['duplicates']} data duplikat dilewati";
        }

        if ($data['errors'] > 0) {
            $errorMsg = "❌ {$data['errors']} data gagal";
            if ($data['has_more_errors']) {
                $errorMsg .= " (total {$data['total_error_count']})";
            }
            $messages[] = $errorMsg;
        }

        $result = implode(', ', $messages);

        if ($data['errors'] === 0) {
            return "🎉 Import berhasil sempurna! " . $result . ". Refresh halaman untuk melihat data terbaru.";
        } elseif ($data['success_rows'] > 0) {
            $successRate = round($data['success_percentage'], 1);
            return "⚠️ Import selesai dengan tingkat keberhasilan {$successRate}%. " . $result . ". Refresh halaman untuk melihat data terbaru.";
        } else {
            return "❌ Import gagal. " . $result;
        }
    }

    /**
     * 🆕 NEW: Create optimized error response untuk large data scenarios
     */
    private function createOptimizedErrorResponse($errorData, $statusCode = 500)
    {
        $baseData = [
            'total_rows' => 0,
            'success_rows' => 0,
            'failed_rows' => 1,
            'imported' => 0,
            'updated' => 0,
            'duplicates' => 0,
            'errors' => 1,
            'success_percentage' => 0,
            'error_details' => [],
            'warning_details' => [],
            'success_details' => [],
            'has_more_errors' => false,
            'has_more_warnings' => false,
            'master_data_available' => $this->getMasterDataSummary(),
            'helper_info' => $this->getImportHelperInfo()
        ];

        // Handle different error types
        if (isset($errorData['validation_error'])) {
            $message = 'File validation error: ' . $errorData['validation_error'];
            $baseData['error_details'] = [$errorData['validation_error']];
        } elseif (isset($errorData['validation_failures'])) {
            $totalFailures = $errorData['total_failures'] ?? count($errorData['validation_failures']);
            $message = "Validasi Excel gagal pada {$totalFailures} baris";
            $baseData['error_details'] = $errorData['validation_failures'];
            $baseData['has_more_errors'] = $errorData['has_more_errors'] ?? false;
            $baseData['total_error_count'] = $totalFailures;
            $baseData['failed_rows'] = $totalFailures;
        } elseif (isset($errorData['memory_limit'])) {
            $message = 'File terlalu besar - ' . $errorData['memory_limit'];
            $baseData['error_details'] = [
                'File size: ' . ($errorData['file_size_mb'] ?? 'unknown') . 'MB',
                $errorData['memory_limit'],
                'Solusi: Bagi file menjadi bagian lebih kecil (maksimal 1000 baris per file)'
            ];
        } elseif (isset($errorData['timeout'])) {
            $message = 'Import timeout - ' . $errorData['timeout'];
            $baseData['error_details'] = [
                'Processing time exceeded limit',
                $errorData['timeout'],
                'Solusi: Gunakan file yang lebih kecil atau coba saat traffic rendah'
            ];
        } else {
            $message = 'Terjadi kesalahan: ' . ($errorData['general_error'] ?? 'Unknown error');
            $baseData['error_details'] = [
                $errorData['general_error'] ?? 'Unknown error occurred',
                $errorData['suggestion'] ?? 'Silakan coba lagi dengan file yang berbeda'
            ];
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $baseData
        ], $statusCode);
    }

    /**
     * 🆕 NEW: Monitor memory usage untuk debugging dan optimization
     */
    private function monitorMemoryUsage($checkpoint)
    {
        $memoryMB = round(memory_get_usage(true) / 1024 / 1024, 2);
        $peakMB = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

        Log::info("Memory checkpoint: {$checkpoint}", [
            'current_mb' => $memoryMB,
            'peak_mb' => $peakMB,
            'limit_mb' => ini_get('memory_limit')
        ]);

        // Warning jika memory usage tinggi
        if ($memoryMB > self::MEMORY_LIMIT_MB * 0.8) {
            Log::warning("High memory usage detected at {$checkpoint}", [
                'current_mb' => $memoryMB,
                'limit_mb' => self::MEMORY_LIMIT_MB
            ]);
        }

        return $memoryMB;
    }

    /**
     * 🆕 NEW: Set memory dan execution time configuration untuk data besar
     */
    private function setMemoryAndTimeConfiguration()
    {
        // Increase memory limit jika belum cukup
        $currentLimit = ini_get('memory_limit');
        if (intval($currentLimit) < self::MEMORY_LIMIT_MB) {
            ini_set('memory_limit', self::MEMORY_LIMIT_MB . 'M');
        }

        // Increase execution time untuk data besar
        $currentTime = ini_get('max_execution_time');
        if (intval($currentTime) < self::MAX_EXECUTION_TIME) {
            set_time_limit(self::MAX_EXECUTION_TIME);
        }

        Log::info('Memory and time configuration set', [
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'current_memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
        ]);
    }

    /**
     * 🆕 NEW: Estimate row count dari file size
     */
    private function estimateRowCount($fileSize)
    {
        // Rough estimate: 100 bytes per row average
        $averageBytesPerRow = 100;
        return max(1, intval($fileSize / $averageBytesPerRow));
    }

    /**
     * 🆕 NEW: Check if error is memory limit related
     */
    private function isMemoryLimitError($exception)
    {
        $message = $exception->getMessage();
        return (
            strpos($message, 'memory') !== false ||
            strpos($message, 'Allowed memory size') !== false ||
            strpos($message, 'out of memory') !== false
        );
    }

    /**
     * 🆕 NEW: Check if error is timeout related
     */
    private function isTimeoutError($exception)
    {
        $message = $exception->getMessage();
        return (
            strpos($message, 'timeout') !== false ||
            strpos($message, 'Maximum execution time') !== false ||
            strpos($message, 'time limit') !== false
        );
    }

    /**
     * 🆕 NEW: Handle memory limit error
     */
    private function handleMemoryLimitError($fileName, $fileSize)
    {
        $fileSizeMB = round($fileSize / 1024 / 1024, 2);

        return $this->createOptimizedErrorResponse([
            'memory_limit' => "Memory limit exceeded untuk file {$fileSizeMB}MB",
            'file_size_mb' => $fileSizeMB
        ], 413);
    }

/**
     * 🆕 NEW: Handle timeout error
     */
    private function handleTimeoutError($fileName, $fileSize)
    {
        $fileSizeMB = round($fileSize / 1024 / 1024, 2);

        return $this->createOptimizedErrorResponse([
            'timeout' => "Processing timeout untuk file {$fileSizeMB}MB - file terlalu besar",
            'file_size_mb' => $fileSizeMB
        ], 408);
    }

    public function downloadTemplate()
{
    try {
        // 🔧 FIX: Ubah ekstensi dari .xlsx ke .csv
        $filename = 'template_account_manager_' . date('Y-m-d_His') . '.csv';

        // 🔧 FIX: Tambahkan parameter format CSV ke Excel::download()
        return Excel::download(
            new AccountManagerTemplateExport(),
            $filename,
            \Maatwebsite\Excel\Excel::CSV,  // 🆕 PENTING: Format CSV explicitly
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );

    } catch (\Exception $e) {
        Log::error('Download Template CSV Error: ' . $e->getMessage());
        return back()->with('error', 'Gagal mendownload template CSV: ' . $e->getMessage());
    }
}

/**
 * 🆕 OPTIONAL: Method alternatif jika ingin support kedua format
 * Route: GET /account-manager/download-template/{format?}
 * Format: csv atau xlsx (default: csv)
 */
public function downloadTemplateWithFormat($format = 'csv')
{
    try {
        $allowedFormats = ['csv', 'xlsx'];

        if (!in_array(strtolower($format), $allowedFormats)) {
            $format = 'csv'; // Default ke CSV
        }

        $format = strtolower($format);
        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $filename = 'template_account_manager_' . date('Y-m-d_His') . '.' . $extension;

        // Tentukan format Excel berdasarkan parameter
        $excelFormat = $format === 'csv'
            ? \Maatwebsite\Excel\Excel::CSV
            : \Maatwebsite\Excel\Excel::XLSX;

        // Set headers sesuai format
        $headers = $format === 'csv'
            ? [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
              ]
            : [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
              ];

        return Excel::download(
            new AccountManagerTemplateExport(),
            $filename,
            $excelFormat,
            $headers
        );

    } catch (\Exception $e) {
        Log::error("Download Template {$format} Error: " . $e->getMessage());
        return back()->with('error', "Gagal mendownload template {$format}: " . $e->getMessage());
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
            'validation_rules' => [
                'Account Manager dan Corporate Customer harus sudah ada di database',
                'Format kolom bulanan: Target_[Bulan] dan Real_[Bulan]',
                'Minimal satu pasangan Target-Real bulanan harus diisi',
                '🔧 BARU: Nilai revenue BOLEH negatif, nol, atau kosong',
                '🔧 BARU: Nilai kosong akan disimpan sebagai 0',
                '🔧 BARU: Nilai negatif akan tetap disimpan sebagai negatif',
                'Fuzzy matching tersedia untuk nama yang mirip (80% similarity)',
                'Case insensitive matching untuk semua nama'
            ],
            'overwrite_modes' => [
                'update' => 'Data existing akan diperbarui otomatis (default)',
                'skip' => 'Data existing akan dilewati, hanya import data baru',
                'ask' => 'Data existing akan diperbarui tapi dengan warning konfirmasi'
            ],
            'database_stats' => [
                'account_managers_count' => AccountManager::count(),
                'witels_count' => Witel::count(),
                'regionals_count' => Regional::count(),
                'divisis_count' => Divisi::count()
            ],
            'features' => [
                'Batch processing dengan chunks untuk performa optimal',
                'Master data caching untuk lookup cepat',
                'Fuzzy matching untuk nama Account Manager dan Corporate Customer',
                'Auto-detect kolom bulanan dengan berbagai format nama',
                'Comprehensive error reporting dengan detail baris yang gagal',
                'Support multiple tahun dengan parameter year',
                'Advanced conflict resolution dengan berbagai mode',
                'Transaction rollback untuk data integrity',
                '🆕 ENHANCED: Memory management untuk file besar',
                '🆕 ENHANCED: Chunked processing untuk data ribuan',
                '🆕 ENHANCED: Optimized response formatting untuk large data'
            ],
            'tips' => [
                'Pastikan nama Account Manager dan Corporate Customer persis sama dengan data di database',
                'Format kolom bulanan: Target_Jan, Real_Jan, Target_Feb, Real_Feb, dst',
                'Bisa gunakan nama bulan Indonesia (Jan, Feb, Mar) atau Inggris (January, February, March)',
                '🔧 BARU: Nilai revenue bisa negatif (misal: -50000), nol (0), atau kosong',
                '🔧 BARU: Sel kosong akan otomatis diisi dengan nilai 0',
                'Jika ada error, perhatikan detail error yang menunjukkan baris dan jenis kesalahan',
                'Gunakan fuzzy matching akan otomatis mencari nama yang mirip 80%',
                'File Excel akan diproses dalam chunks untuk menghindari timeout',
                '🆕 BARU: Untuk file >1000 baris, sistem akan otomatis menggunakan chunked processing',
                '🆕 BARU: Maksimal 10MB per file - file lebih besar akan ditolak',
                'Import akan memberikan pesan untuk refresh manual - tidak auto refresh'
            ]
        ];
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
            ->with(['divisis', 'witel', 'regional', 'user'])
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
     * ✅ ENHANCED: Get statistics for dashboard with user account information dan memory-efficient queries
     */
    public function getStatistics()
    {
        try {
            // 🆕 ENHANCED: Use more efficient queries untuk large datasets
            $totalAccountManagers = AccountManager::count();
            $recentAccountManagers = AccountManager::where('created_at', '>=', now()->subDays(30))->count();

            // Optimize query untuk active account managers
            $activeAccountManagers = AccountManager::whereHas('revenues')->distinct('id')->count();

            // ✅ ENHANCED: Count Account Managers with and without user accounts (optimized)
            $accountManagersWithUsers = AccountManager::whereHas('user')->count();
            $accountManagersWithoutUsers = $totalAccountManagers - $accountManagersWithUsers;

            // Count total revenues linked (optimized join)
            $totalRevenuesLinked = DB::table('revenues')
                ->join('account_managers', 'revenues.account_manager_id', '=', 'account_managers.id')
                ->count();

            // Count by divisi (optimized with limit)
            $divisiStats = DB::table('account_manager_divisi')
                ->join('divisi', 'account_manager_divisi.divisi_id', '=', 'divisi.id')
                ->select('divisi.nama', DB::raw('count(*) as total'))
                ->groupBy('divisi.id', 'divisi.nama')
                ->orderBy('total', 'desc')
                ->limit(10) // Limit untuk performance
                ->get();

            // Count by regional (optimized query)
            $regionalStats = DB::table('account_managers')
                ->join('regional', 'account_managers.regional_id', '=', 'regional.id')
                ->select('regional.nama', DB::raw('count(*) as total'))
                ->groupBy('regional.id', 'regional.nama')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->pluck('total', 'nama');

            // Count by witel (optimized with limit)
            $witelStats = DB::table('account_managers')
                ->join('witel', 'account_managers.witel_id', '=', 'witel.id')
                ->select('witel.nama', DB::raw('count(*) as total'))
                ->groupBy('witel.id', 'witel.nama')
                ->orderBy('total', 'desc')
                ->limit(10) // Top 10 witel only
                ->pluck('total', 'nama');

            // ✅ ENHANCED: User registration statistics (optimized)
            $userRegistrationStats = [
                'total_with_accounts' => $accountManagersWithUsers,
                'total_without_accounts' => $accountManagersWithoutUsers,
                'registration_percentage' => $totalAccountManagers > 0 ? round(($accountManagersWithUsers / $totalAccountManagers) * 100, 2) : 0,
                'recent_registrations' => AccountManager::whereHas('user', function($query) {
                    $query->where('created_at', '>=', now()->subDays(30));
                })->count()
            ];

            return [
                'total_account_managers' => $totalAccountManagers,
                'recent_account_managers' => $recentAccountManagers,
                'active_account_managers' => $activeAccountManagers,
                'inactive_account_managers' => $totalAccountManagers - $activeAccountManagers,
                'total_revenues_linked' => $totalRevenuesLinked,
                'divisi_stats' => $divisiStats,
                'regional_stats' => $regionalStats,
                'witel_stats' => $witelStats,
                'user_registration_stats' => $userRegistrationStats,
                'accounts_with_users' => $accountManagersWithUsers,
                'accounts_without_users' => $accountManagersWithoutUsers
            ];

        } catch (\Exception $e) {
            Log::error('Account Manager Statistics Error: ' . $e->getMessage());

            return [
                'total_account_managers' => 0,
                'recent_account_managers' => 0,
                'active_account_managers' => 0,
                'inactive_account_managers' => 0,
                'total_revenues_linked' => 0,
                'divisi_stats' => collect(),
                'regional_stats' => collect(),
                'witel_stats' => collect(),
                'user_registration_stats' => [
                    'total_with_accounts' => 0,
                    'total_without_accounts' => 0,
                    'registration_percentage' => 0,
                    'recent_registrations' => 0
                ],
                'accounts_with_users' => 0,
                'accounts_without_users' => 0
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

            // Check uniqueness (optimized query)
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
     * ✅ EXISTING: Get all Account Managers with user status for admin panel
     */
    public function getAccountManagersWithUserStatus(Request $request)
    {
        try {
            $query = AccountManager::with(['witel', 'regional', 'divisis', 'user']);

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = trim($request->search);
                $query->where(function($q) use ($searchTerm) {
                    $q->where('nama', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('nik', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Filter by user status
            if ($request->has('user_status')) {
                if ($request->user_status === 'registered') {
                    $query->whereHas('user');
                } elseif ($request->user_status === 'not_registered') {
                    $query->whereDoesntHave('user');
                }
            }

            $accountManagers = $query->orderBy('nama')
                                   ->paginate($request->get('per_page', 15));

            // Transform data to include user status (memory efficient)
            $accountManagers->getCollection()->transform(function ($am) {
                return [
                    'id' => $am->id,
                    'nama' => $am->nama,
                    'nik' => $am->nik,
                    'witel' => $am->witel->nama ?? null,
                    'regional' => $am->regional->nama ?? null,
                    'divisis' => $am->divisis->pluck('nama')->implode(', '),
                    'has_user_account' => $am->user ? true : false,
                    'user_email' => $am->user ? $am->user->email : null,
                    'user_created_at' => $am->user ? $am->user->created_at->format('d M Y H:i') : null,
                    'user_last_login' => $am->user && $am->user->last_login_at ? $am->user->last_login_at->format('d M Y H:i') : null,
                    'can_change_password' => $am->user ? true : false,
                    'revenue_count' => $am->revenues()->count()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $accountManagers
            ]);

        } catch (\Exception $e) {
            Log::error('Get Account Managers With User Status Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Account Manager.',
                'data' => []
            ]);
        }
    }

    /**
     * ✅ EXISTING: Bulk password reset for multiple Account Managers
     */
    public function bulkPasswordReset(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'account_manager_ids' => 'required|array|min:1',
                'account_manager_ids.*' => 'exists:account_managers,id',
                'new_password' => 'required|min:8|confirmed',
                'new_password_confirmation' => 'required'
            ], [
                'account_manager_ids.required' => 'Pilih minimal satu Account Manager.',
                'account_manager_ids.array' => 'Format data tidak valid.',
                'account_manager_ids.min' => 'Pilih minimal satu Account Manager.',
                'account_manager_ids.*.exists' => 'Account Manager tidak ditemukan.',
                'new_password.required' => 'Password baru wajib diisi.',
                'new_password.min' => 'Password minimal 8 karakter.',
                'new_password.confirmed' => 'Konfirmasi password tidak cocok.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $accountManagerIds = $request->account_manager_ids;
            $newPassword = Hash::make($request->new_password);
            $updated = 0;
            $errors = [];
            $updatedDetails = [];

            foreach ($accountManagerIds as $id) {
                try {
                    $accountManager = AccountManager::with('user')->findOrFail($id);

                    if (!$accountManager->user) {
                        $errors[] = "Account Manager '{$accountManager->nama}' tidak memiliki akun user terdaftar.";
                        continue;
                    }

                    // Update password
                    $accountManager->user->update(['password' => $newPassword]);

                    $updated++;
                    $updatedDetails[] = [
                        'id' => $accountManager->id,
                        'nama' => $accountManager->nama,
                        'nik' => $accountManager->nik,
                        'email' => $accountManager->user->email
                    ];

                } catch (\Exception $e) {
                    $errors[] = "Error mengubah password Account Manager ID {$id}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Berhasil mengubah password {$updated} Account Manager.";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " data gagal diproses.";
            }

            // Log bulk password reset activity
            Log::info('Bulk Password Reset Activity', [
                'updated_count' => $updated,
                'error_count' => count($errors),
                'admin_ip' => $request->ip(),
                'updated_details' => $updatedDetails,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'updated' => $updated,
                    'errors' => $errors,
                    'updated_details' => $updatedDetails
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk Password Reset Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengubah password: ' . $e->getMessage()
            ], 500);
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