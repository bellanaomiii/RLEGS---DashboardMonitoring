<?php

namespace App\Http\Controllers;

use App\Models\CorporateCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CorporateCustomerController extends Controller
{
    /**
     * ✅ ENHANCED: Display a listing of corporate customers with enhanced search
     */
    public function index(Request $request)
    {
        try {
            $query = CorporateCustomer::query();

            // ✅ ENHANCED: Search functionality - partial word search
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = trim($request->search);
                $query->where(function($q) use ($searchTerm) {
                    $q->where('nama', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('nipnas', 'LIKE', "%{$searchTerm}%");
                });
            }

            $corporateCustomers = $query->orderBy('nama', 'asc')
                                       ->paginate($request->get('per_page', 15));

            return view('corporate-customers.index', compact('corporateCustomers'));

        } catch (\Exception $e) {
            Log::error('Corporate Customer Index Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data Corporate Customer.');
        }
    }

    /**
     * ✅ FIXED: Store a newly created corporate customer with proper NIPNAS validation
     */
    public function store(Request $request)
    {
        try {
            // ✅ FIXED: Enhanced NIPNAS validation to handle large numbers
            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|max:255|min:3',
                'nipnas' => [
                    'required',
                    'string',
                    'min:3',
                    'max:20',
                    'regex:/^[0-9]+$/',
                    'unique:corporate_customers,nipnas'
                ],
            ], [
                'nama.required' => 'Nama Corporate Customer wajib diisi.',
                'nama.min' => 'Nama Corporate Customer minimal 3 karakter.',
                'nama.max' => 'Nama Corporate Customer maksimal 255 karakter.',
                'nipnas.required' => 'NIPNAS wajib diisi.',
                'nipnas.min' => 'NIPNAS minimal 3 digit.',
                'nipnas.max' => 'NIPNAS maksimal 20 digit.',
                'nipnas.regex' => 'NIPNAS harus berupa angka saja.',
                'nipnas.unique' => 'NIPNAS sudah terdaftar, gunakan NIPNAS lain.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ ADDITIONAL: Validate NIPNAS numeric range
            $nipnas = trim($request->nipnas);

            // Check if NIPNAS is too small (less than 100)
            if (bccomp($nipnas, '100', 0) < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIPNAS minimal 100 (3 digit).'
                ], 422);
            }

            // Check if NIPNAS is too large (more than 20 digits)
            if (strlen($nipnas) > 20) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIPNAS maksimal 20 digit.'
                ], 422);
            }

            // ✅ FIXED: Create Corporate Customer with proper validation
            $corporateCustomer = CorporateCustomer::create([
                'nama' => trim($request->nama),
                'nipnas' => $nipnas,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Corporate Customer berhasil ditambahkan.',
                'data' => $corporateCustomer
            ]);

        } catch (\Exception $e) {
            Log::error('Corporate Customer Store Error: ' . $e->getMessage());

            // ✅ ENHANCED: Better error handling for database constraints
            $errorMessage = $e->getMessage();

            if (strpos($errorMessage, 'Duplicate entry') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIPNAS sudah terdaftar dalam sistem.'
                ], 422);
            }

            if (strpos($errorMessage, 'Data too long') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data terlalu panjang untuk disimpan.'
                ], 422);
            }

            if (strpos($errorMessage, 'Out of range') !== false || strpos($errorMessage, '22003') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIPNAS terlalu besar. Gunakan NIPNAS dengan maksimal 20 digit.'
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified corporate customer
     */
    public function edit($id)
    {
        try {
            $corporateCustomer = CorporateCustomer::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $corporateCustomer
            ]);

        } catch (\Exception $e) {
            Log::error('Corporate Customer Edit Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Corporate Customer tidak ditemukan.'
            ], 404);
        }
    }

    /**
     * ✅ FIXED: Update the specified corporate customer with proper NIPNAS validation
     */
    public function update(Request $request, $id)
    {
        try {
            $corporateCustomer = CorporateCustomer::findOrFail($id);

            // ✅ FIXED: Enhanced NIPNAS validation for update
            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|max:255|min:3',
                'nipnas' => [
                    'required',
                    'string',
                    'min:3',
                    'max:20',
                    'regex:/^[0-9]+$/',
                    'unique:corporate_customers,nipnas,' . $id
                ],
            ], [
                'nama.required' => 'Nama Corporate Customer wajib diisi.',
                'nama.min' => 'Nama Corporate Customer minimal 3 karakter.',
                'nama.max' => 'Nama Corporate Customer maksimal 255 karakter.',
                'nipnas.required' => 'NIPNAS wajib diisi.',
                'nipnas.min' => 'NIPNAS minimal 3 digit.',
                'nipnas.max' => 'NIPNAS maksimal 20 digit.',
                'nipnas.regex' => 'NIPNAS harus berupa angka saja.',
                'nipnas.unique' => 'NIPNAS sudah terdaftar, gunakan NIPNAS lain.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ ADDITIONAL: Validate NIPNAS numeric range
            $nipnas = trim($request->nipnas);

            if (bccomp($nipnas, '100', 0) < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIPNAS minimal 100 (3 digit).'
                ], 422);
            }

            if (strlen($nipnas) > 20) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIPNAS maksimal 20 digit.'
                ], 422);
            }

            // Update Corporate Customer
            $corporateCustomer->update([
                'nama' => trim($request->nama),
                'nipnas' => $nipnas,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Corporate Customer berhasil diperbarui.',
                'data' => $corporateCustomer
            ]);

        } catch (\Exception $e) {
            Log::error('Corporate Customer Update Error: ' . $e->getMessage());

            // ✅ ENHANCED: Better error handling for database constraints
            $errorMessage = $e->getMessage();

            if (strpos($errorMessage, 'Duplicate entry') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIPNAS sudah terdaftar dalam sistem.'
                ], 422);
            }

            if (strpos($errorMessage, 'Out of range') !== false || strpos($errorMessage, '22003') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIPNAS terlalu besar. Gunakan NIPNAS dengan maksimal 20 digit.'
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified corporate customer
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $corporateCustomer = CorporateCustomer::findOrFail($id);

            // Check if Corporate Customer has related revenue data
            if ($corporateCustomer->revenues()->exists()) {
                return back()->with('error', 'Corporate Customer tidak dapat dihapus karena masih memiliki data revenue terkait.');
            }

            // Delete the corporate customer
            $corporateCustomer->delete();

            DB::commit();

            return back()->with('success', 'Corporate Customer berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Corporate Customer Delete Error: ' . $e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat menghapus Corporate Customer.');
        }
    }

    /**
     * ✅ ENHANCED: Search Corporate Customers for autocomplete with partial word support
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

            $corporateCustomers = CorporateCustomer::where('nama', 'LIKE', "%{$searchTerm}%")
                                               ->orWhere('nipnas', 'LIKE', "%{$searchTerm}%")
                                               ->limit(10)
                                               ->get(['id', 'nama', 'nipnas']);

            return response()->json([
                'success' => true,
                'data' => $corporateCustomers
            ]);

        } catch (\Exception $e) {
            Log::error('Corporate Customer Search Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat pencarian.',
                'data' => []
            ]);
        }
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics()
    {
        try {
            $totalCustomers = CorporateCustomer::count();
            $recentCustomers = CorporateCustomer::where('created_at', '>=', now()->subDays(30))->count();
            $activeCustomers = CorporateCustomer::whereHas('revenues')->distinct()->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_customers' => $totalCustomers,
                    'recent_customers' => $recentCustomers,
                    'active_customers' => $activeCustomers,
                    'inactive_customers' => $totalCustomers - $activeCustomers
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Corporate Customer Statistics Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil statistik.',
                'data' => []
            ]);
        }
    }

    /**
     * ✅ ENHANCED: Bulk delete corporate customers
     */
    public function bulkDelete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array|min:1',
                'ids.*' => 'exists:corporate_customers,id'
            ], [
                'ids.required' => 'Pilih minimal satu Corporate Customer untuk dihapus.',
                'ids.array' => 'Format data tidak valid.',
                'ids.min' => 'Pilih minimal satu Corporate Customer untuk dihapus.',
                'ids.*.exists' => 'Corporate Customer tidak ditemukan.'
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

            foreach ($ids as $id) {
                try {
                    $corporateCustomer = CorporateCustomer::findOrFail($id);

                    // Check if has related revenue data
                    if ($corporateCustomer->revenues()->exists()) {
                        $errors[] = "Corporate Customer '{$corporateCustomer->nama}' tidak dapat dihapus karena memiliki data revenue terkait.";
                        continue;
                    }

                    $corporateCustomer->delete();
                    $deleted++;

                } catch (\Exception $e) {
                    $errors[] = "Error menghapus Corporate Customer ID {$id}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Berhasil menghapus {$deleted} Corporate Customer.";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " data gagal dihapus.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'deleted' => $deleted,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Corporate Customer Bulk Delete Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data Corporate Customer.'
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Validate NIPNAS without saving (for real-time validation)
     */
    public function validateNipnas(Request $request)
    {
        try {
            $nipnas = trim($request->get('nipnas', ''));
            $currentId = $request->get('current_id', null);

            if (empty($nipnas)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'NIPNAS tidak boleh kosong.'
                ]);
            }

            // Check format
            if (!preg_match('/^[0-9]+$/', $nipnas)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'NIPNAS harus berupa angka saja.'
                ]);
            }

            // Check length
            if (strlen($nipnas) < 3) {
                return response()->json([
                    'valid' => false,
                    'message' => 'NIPNAS minimal 3 digit.'
                ]);
            }

            if (strlen($nipnas) > 20) {
                return response()->json([
                    'valid' => false,
                    'message' => 'NIPNAS maksimal 20 digit.'
                ]);
            }

            // Check range
            if (bccomp($nipnas, '100', 0) < 0) {
                return response()->json([
                    'valid' => false,
                    'message' => 'NIPNAS minimal 100.'
                ]);
            }

            // Check uniqueness
            $query = CorporateCustomer::where('nipnas', $nipnas);
            if ($currentId) {
                $query->where('id', '!=', $currentId);
            }

            if ($query->exists()) {
                return response()->json([
                    'valid' => false,
                    'message' => 'NIPNAS sudah terdaftar dalam sistem.'
                ]);
            }

            return response()->json([
                'valid' => true,
                'message' => 'NIPNAS valid.'
            ]);

        } catch (\Exception $e) {
            Log::error('NIPNAS Validation Error: ' . $e->getMessage());

            return response()->json([
                'valid' => false,
                'message' => 'Terjadi kesalahan saat validasi NIPNAS.'
            ]);
        }
    }

    /**
     * ✅ NEW: Get Corporate Customer data for API (alias untuk edit)
     */
    public function getCorporateCustomerData($id)
    {
        return $this->edit($id);
    }

    /**
     * ✅ NEW: Update Corporate Customer via API (alias untuk update)
     */
    public function updateCorporateCustomer(Request $request, $id)
    {
        return $this->update($request, $id);
    }
}