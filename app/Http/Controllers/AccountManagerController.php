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
/**
     * 🆕 NEW: Bulk delete ALL account managers with filter support
     *
     * Route: POST /account-manager/bulk-delete-all
     * Function: Menghapus SEMUA Account Manager sesuai filter yang aktif
     */
    public function bulkDeleteAll(Request $request)
    {
        try {
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
            $accountManagers = $query->with(['user', 'revenues'])->get();
            $totalCount = $accountManagers->count();

            if ($totalCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data Account Manager yang sesuai dengan filter.'
                ], 422);
            }

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
                'deleted_preview' => $deletedDetails
            ]);

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
                '💡 Satu NIK bisa memiliki multiple divisi (buat baris terpisah)',
                '💡 Import akan menghapus auto refresh - refresh manual setelah melihat hasil'
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
     * ✅ ENHANCED: Get statistics for dashboard with user account information
     */
    public function getStatistics()
    {
        try {
            $totalAccountManagers = AccountManager::count();
            $recentAccountManagers = AccountManager::where('created_at', '>=', now()->subDays(30))->count();
            $activeAccountManagers = AccountManager::whereHas('revenues')->distinct()->count();

            // ✅ ENHANCED: Count Account Managers with and without user accounts
            $accountManagersWithUsers = AccountManager::whereHas('user')->count();
            $accountManagersWithoutUsers = $totalAccountManagers - $accountManagersWithUsers;

            // Count total revenues related to Account Managers
            $totalRevenuesLinked = DB::table('revenues')
                ->join('account_managers', 'revenues.account_manager_id', '=', 'account_managers.id')
                ->count();

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

            // Count by witel
            $witelStats = AccountManager::with('witel')
                ->get()
                ->groupBy('witel.nama')
                ->map(function ($items) {
                    return $items->count();
                })
                ->sortDesc()
                ->take(10); // Top 10 witel

            // ✅ ENHANCED: User registration statistics
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

            // Transform data to include user status
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