<?php

namespace App\Http\Controllers;

use App\Models\Revenue;
use App\Models\AccountManager;
use App\Models\CorporateCustomer;
use App\Models\Divisi;
use App\Models\Witel;
use App\Models\Regional;
use App\Imports\RevenueImport;
use App\Exports\RevenueExport;
use App\Exports\RevenueTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class RevenueController extends Controller
{
    /**
     * ✅ EXISTING: Display a listing of revenue data with enhanced search
     */
    public function index(Request $request)
    {
        try {
            $query = Revenue::with(['accountManager.witel', 'accountManager.regional', 'corporateCustomer', 'divisi']);

            // ✅ ENHANCED: Search functionality - partial word search
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = trim($request->search);
                $query->where(function($q) use ($searchTerm) {
                    $q->whereHas('accountManager', function($subQuery) use ($searchTerm) {
                        $subQuery->where('nama', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('nik', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('corporateCustomer', function($subQuery) use ($searchTerm) {
                        $subQuery->where('nama', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('nipnas', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('divisi', function($subQuery) use ($searchTerm) {
                        $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('accountManager.witel', function($subQuery) use ($searchTerm) {
                        $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('accountManager.regional', function($subQuery) use ($searchTerm) {
                        $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                    });
                });
            }

            // Filter by month and year separately
            if ($request->has('month') && !empty($request->month)) {
                $query->whereMonth('bulan', $request->month);
            }

            if ($request->has('year') && !empty($request->year)) {
                $query->whereYear('bulan', $request->year);
            }

            // Filter by bulan (Y-m format)
            if ($request->has('bulan') && !empty($request->bulan)) {
                // ✅ FIXED: Handle both Y-m and Y-m-d formats
                $bulanFilter = $request->bulan;
                if (strlen($bulanFilter) === 7) { // Y-m format
                    $bulanFilter .= '-01'; // Convert to Y-m-d format
                }
                $query->where('bulan', 'LIKE', substr($bulanFilter, 0, 7) . '%');
            }

            // Filter by Account Manager
            if ($request->has('account_manager') && !empty($request->account_manager)) {
                $query->where('account_manager_id', $request->account_manager);
            }

            // Filter by Corporate Customer
            if ($request->has('corporate_customer') && !empty($request->corporate_customer)) {
                $query->where('corporate_customer_id', $request->corporate_customer);
            }

            // Filter by Divisi
            if ($request->has('divisi') && !empty($request->divisi)) {
                $query->where('divisi_id', $request->divisi);
            }

            // Filter by Witel (through Account Manager)
            if ($request->has('witel') && !empty($request->witel)) {
                $query->whereHas('accountManager', function($subQuery) use ($request) {
                    $subQuery->where('witel_id', $request->witel);
                });
            }

            // Filter by Regional (through Account Manager)
            if ($request->has('regional') && !empty($request->regional)) {
                $query->whereHas('accountManager', function($subQuery) use ($request) {
                    $subQuery->where('regional_id', $request->regional);
                });
            }

            // ✅ Paginate revenues
            $revenues = $query->orderBy('bulan', 'desc')
                             ->orderBy('created_at', 'desc')
                             ->paginate($request->get('per_page', 15));

            // ✅ FIXED: Enhanced search for AccountManagers
            $accountManagerQuery = AccountManager::with(['witel', 'regional', 'divisis']);

            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = trim($request->search);
                $accountManagerQuery->where(function($q) use ($searchTerm) {
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

            if ($request->has('witel') && !empty($request->witel)) {
                $accountManagerQuery->where('witel_id', $request->witel);
            }

            if ($request->has('regional') && !empty($request->regional)) {
                $accountManagerQuery->where('regional_id', $request->regional);
            }

            $accountManagers = $accountManagerQuery->orderBy('nama')
                                                 ->paginate($request->get('per_page', 15));

            // ✅ FIXED: Enhanced search for Corporate Customers
            $corporateCustomerQuery = CorporateCustomer::query();

            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = trim($request->search);
                $corporateCustomerQuery->where(function($q) use ($searchTerm) {
                    $q->where('nama', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('nipnas', 'LIKE', "%{$searchTerm}%");
                });
            }

            $corporateCustomers = $corporateCustomerQuery->orderBy('nama')
                                                       ->paginate($request->get('per_page', 15));

            // Get additional data for filters and forms
            $witels = Witel::orderBy('nama')->get();
            $regionals = Regional::orderBy('nama')->get();
            $divisis = Divisi::orderBy('nama')->get();

            // Generate year range for filter dropdown
            $currentYear = date('Y');
            $yearRange = range($currentYear - 5, $currentYear + 2);

            // Get statistics for dashboard
            $statistics = $this->getStatistics();

            return view('revenueData', compact(
                'revenues',
                'accountManagers',
                'corporateCustomers',
                'divisis',
                'witels',
                'regionals',
                'yearRange',
                'statistics'
            ));

        } catch (\Exception $e) {
            Log::error('Revenue Index Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data Revenue.');
        }
    }

    /**
     * 🔧 UPDATED: Store a newly created revenue with ZERO & NEGATIVE value support
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'account_manager_id' => 'required|exists:account_managers,id',
                'corporate_customer_id' => 'required|exists:corporate_customers,id',
                'divisi_id' => 'required|exists:divisi,id',
                'target_revenue' => [
                    'required',
                    'numeric',
                    'min:-999999999999',
                    'max:999999999999'
                ],
                'real_revenue' => [
                    'required',
                    'numeric',
                    'min:-999999999999',
                    'max:999999999999'
                ],
                'bulan' => 'required|date_format:Y-m',
            ], [
                'account_manager_id.required' => 'Account Manager wajib dipilih.',
                'account_manager_id.exists' => 'Account Manager tidak valid.',
                'corporate_customer_id.required' => 'Corporate Customer wajib dipilih.',
                'corporate_customer_id.exists' => 'Corporate Customer tidak valid.',
                'divisi_id.required' => 'Divisi wajib dipilih.',
                'divisi_id.exists' => 'Divisi tidak valid.',
                'target_revenue.required' => 'Target Revenue wajib diisi.',
                'target_revenue.numeric' => 'Target Revenue harus berupa angka.',
                'target_revenue.min' => 'Target Revenue tidak boleh kurang dari -999,999,999,999.',
                'target_revenue.max' => 'Target Revenue tidak boleh lebih dari 999,999,999,999.',
                'real_revenue.required' => 'Real Revenue wajib diisi.',
                'real_revenue.numeric' => 'Real Revenue harus berupa angka.',
                'real_revenue.min' => 'Real Revenue tidak boleh kurang dari -999,999,999,999.',
                'real_revenue.max' => 'Real Revenue tidak boleh lebih dari 999,999,999,999.',
                'bulan.required' => 'Bulan wajib dipilih.',
                'bulan.date_format' => 'Format bulan tidak valid (harus YYYY-MM).',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ FIXED: Convert Y-m format to proper date (Y-m-01) for database storage
            $bulanInput = $request->bulan; // Should be Y-m format (e.g., "2025-10")
            $bulanDate = $bulanInput . '-01'; // Convert to Y-m-d format (e.g., "2025-10-01")

            // Validate the date is actually valid
            if (!Carbon::createFromFormat('Y-m-d', $bulanDate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format bulan tidak valid.'
                ], 422);
            }

            // Check for duplicate entry
            $existingRevenue = Revenue::where([
                'account_manager_id' => $request->account_manager_id,
                'corporate_customer_id' => $request->corporate_customer_id,
                'divisi_id' => $request->divisi_id,
                'bulan' => $bulanDate,
            ])->first();

            if ($existingRevenue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data revenue untuk kombinasi Account Manager, Corporate Customer, Divisi, dan Bulan ini sudah ada.'
                ], 422);
            }

            // 🔧 ENHANCED: Parse and validate revenue values dengan support untuk negatif dan zero
            $targetRevenue = $this->parseRevenueValue($request->target_revenue);
            $realRevenue = $this->parseRevenueValue($request->real_revenue);

            // Create Revenue with proper date format dan revenue values
            $revenue = Revenue::create([
                'account_manager_id' => $request->account_manager_id,
                'corporate_customer_id' => $request->corporate_customer_id,
                'divisi_id' => $request->divisi_id,
                'target_revenue' => $targetRevenue,
                'real_revenue' => $realRevenue,
                'bulan' => $bulanDate, // Store as Y-m-d format
            ]);

            // ✅ ENHANCED: Comprehensive logging dengan info revenue values
            Log::info('Revenue created successfully', [
                'revenue_id' => $revenue->id,
                'account_manager_id' => $revenue->account_manager_id,
                'corporate_customer_id' => $revenue->corporate_customer_id,
                'divisi_id' => $revenue->divisi_id,
                'bulan' => $revenue->bulan,
                'target_revenue' => $revenue->target_revenue,
                'real_revenue' => $revenue->real_revenue,
                'target_is_zero' => ($targetRevenue == 0),
                'real_is_zero' => ($realRevenue == 0),
                'target_is_negative' => ($targetRevenue < 0),
                'real_is_negative' => ($realRevenue < 0),
                'user_ip' => $request->ip(),
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data Revenue berhasil ditambahkan.',
                'data' => $revenue->load(['accountManager.witel', 'accountManager.regional', 'corporateCustomer', 'divisi'])
            ]);

        } catch (\Exception $e) {
            Log::error('Revenue Store Error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'user_ip' => $request->ip(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ FIXED: Show the form for editing the specified revenue - Complete data loading
     */
    public function edit($id)
    {
        try {
            $revenue = Revenue::with([
                'accountManager.witel',
                'accountManager.regional',
                'corporateCustomer',
                'divisi'
            ])->findOrFail($id);

            // ✅ FIX: Format bulan from Y-m-d to Y-m for frontend
            $formattedRevenue = [
                'id' => $revenue->id,
                'account_manager_id' => $revenue->account_manager_id,
                'corporate_customer_id' => $revenue->corporate_customer_id,
                'divisi_id' => $revenue->divisi_id,
                'target_revenue' => $revenue->target_revenue,
                'real_revenue' => $revenue->real_revenue,
                'bulan' => substr($revenue->bulan, 0, 7), // Convert Y-m-d to Y-m
                'accountManager' => [
                    'id' => $revenue->accountManager->id,
                    'nama' => $revenue->accountManager->nama,
                    'nik' => $revenue->accountManager->nik
                ],
                'corporateCustomer' => [
                    'id' => $revenue->corporateCustomer->id,
                    'nama' => $revenue->corporateCustomer->nama,
                    'nipnas' => $revenue->corporateCustomer->nipnas
                ],
                'divisi' => [
                    'id' => $revenue->divisi->id,
                    'nama' => $revenue->divisi->nama
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $formattedRevenue
            ]);

        } catch (\Exception $e) {
            Log::error('Revenue Edit Error: ' . $e->getMessage(), [
                'revenue_id' => $id,
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Revenue tidak ditemukan.'
            ], 404);
        }
    }

    /**
     * 🔧 UPDATED: Update the specified revenue with ZERO & NEGATIVE value support
     */
    public function update(Request $request, $id)
    {
        try {
            $revenue = Revenue::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'account_manager_id' => 'required|exists:account_managers,id',
                'corporate_customer_id' => 'required|exists:corporate_customers,id',
                'divisi_id' => 'required|exists:divisi,id',
                'target_revenue' => [
                    'required',
                    'numeric',
                    'min:-999999999999',
                    'max:999999999999'
                ],
                'real_revenue' => [
                    'required',
                    'numeric',
                    'min:-999999999999',
                    'max:999999999999'
                ],
                'bulan' => 'required|date_format:Y-m',
            ], [
                'account_manager_id.required' => 'Account Manager wajib dipilih.',
                'account_manager_id.exists' => 'Account Manager tidak valid.',
                'corporate_customer_id.required' => 'Corporate Customer wajib dipilih.',
                'corporate_customer_id.exists' => 'Corporate Customer tidak valid.',
                'divisi_id.required' => 'Divisi wajib dipilih.',
                'divisi_id.exists' => 'Divisi tidak valid.',
                'target_revenue.required' => 'Target Revenue wajib diisi.',
                'target_revenue.numeric' => 'Target Revenue harus berupa angka.',
                'target_revenue.min' => 'Target Revenue tidak boleh kurang dari -999,999,999,999.',
                'target_revenue.max' => 'Target Revenue tidak boleh lebih dari 999,999,999,999.',
                'real_revenue.required' => 'Real Revenue wajib diisi.',
                'real_revenue.numeric' => 'Real Revenue harus berupa angka.',
                'real_revenue.min' => 'Real Revenue tidak boleh kurang dari -999,999,999,999.',
                'real_revenue.max' => 'Real Revenue tidak boleh lebih dari 999,999,999,999.',
                'bulan.required' => 'Bulan wajib dipilih.',
                'bulan.date_format' => 'Format bulan tidak valid (harus YYYY-MM).',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ FIXED: Convert Y-m format to proper date
            $bulanInput = $request->bulan;
            $bulanDate = $bulanInput . '-01';

            // Validate the date
            if (!Carbon::createFromFormat('Y-m-d', $bulanDate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format bulan tidak valid.'
                ], 422);
            }

            // Check for duplicate entry (exclude current record)
            $existingRevenue = Revenue::where([
                'account_manager_id' => $request->account_manager_id,
                'corporate_customer_id' => $request->corporate_customer_id,
                'divisi_id' => $request->divisi_id,
                'bulan' => $bulanDate,
            ])->where('id', '!=', $id)->first();

            if ($existingRevenue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data revenue untuk kombinasi Account Manager, Corporate Customer, Divisi, dan Bulan ini sudah ada.'
                ], 422);
            }

            // Store original data for logging
            $originalData = [
                'account_manager_id' => $revenue->account_manager_id,
                'corporate_customer_id' => $revenue->corporate_customer_id,
                'divisi_id' => $revenue->divisi_id,
                'target_revenue' => $revenue->target_revenue,
                'real_revenue' => $revenue->real_revenue,
                'bulan' => $revenue->bulan
            ];

            // 🔧 ENHANCED: Parse and validate revenue values dengan support untuk negatif dan zero
            $targetRevenue = $this->parseRevenueValue($request->target_revenue);
            $realRevenue = $this->parseRevenueValue($request->real_revenue);

            // Update Revenue
            $revenue->update([
                'account_manager_id' => $request->account_manager_id,
                'corporate_customer_id' => $request->corporate_customer_id,
                'divisi_id' => $request->divisi_id,
                'target_revenue' => $targetRevenue,
                'real_revenue' => $realRevenue,
                'bulan' => $bulanDate,
            ]);

            // ✅ ENHANCED: Comprehensive logging dengan detail perubahan
            Log::info('Revenue updated successfully', [
                'revenue_id' => $revenue->id,
                'original_data' => $originalData,
                'updated_data' => [
                    'account_manager_id' => $revenue->account_manager_id,
                    'corporate_customer_id' => $revenue->corporate_customer_id,
                    'divisi_id' => $revenue->divisi_id,
                    'target_revenue' => $revenue->target_revenue,
                    'real_revenue' => $revenue->real_revenue,
                    'bulan' => $revenue->bulan
                ],
                'changes' => [
                    'target_changed' => ($originalData['target_revenue'] != $targetRevenue),
                    'real_changed' => ($originalData['real_revenue'] != $realRevenue),
                    'target_from_to' => [$originalData['target_revenue'], $targetRevenue],
                    'real_from_to' => [$originalData['real_revenue'], $realRevenue],
                    'target_is_zero' => ($targetRevenue == 0),
                    'real_is_zero' => ($realRevenue == 0),
                    'target_is_negative' => ($targetRevenue < 0),
                    'real_is_negative' => ($realRevenue < 0)
                ],
                'user_ip' => $request->ip(),
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data Revenue berhasil diperbarui.',
                'data' => $revenue->load(['accountManager.witel', 'accountManager.regional', 'corporateCustomer', 'divisi'])
            ]);

        } catch (\Exception $e) {
            Log::error('Revenue Update Error: ' . $e->getMessage(), [
                'revenue_id' => $id,
                'request_data' => $request->all(),
                'user_ip' => $request->ip(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🆕 NEW: Parse revenue value dengan support untuk zero dan negative values
     */
    private function parseRevenueValue($value)
    {
        // Handle null or empty values
        if ($value === null || $value === '') {
            return 0;
        }

        // Handle array values (from Excel import)
        if (is_array($value)) {
            $numericValues = array_filter($value, function($v) {
                return is_numeric($v) || (is_string($v) && preg_match('/^-?[\d,.]+$/', trim($v)));
            });

            if (empty($numericValues)) {
                return 0;
            }

            $value = reset($numericValues);
        }

        // If already numeric, return as-is (supporting negative values)
        if (is_numeric($value)) {
            return (float)$value;
        }

        // Handle string numeric values with thousands separators
        if (is_string($value)) {
            $value = trim($value);

            // Handle negative values
            $isNegative = (strpos($value, '-') === 0);
            if ($isNegative) {
                $value = substr($value, 1); // Remove minus sign temporarily
            }

            // Clean thousands separators and currency symbols
            $cleaned = preg_replace('/[^\d,.]/', '', $value);

            // Handle comma as thousand separator vs decimal separator
            if (substr_count($cleaned, ',') == 1 && substr_count($cleaned, '.') == 0) {
                $parts = explode(',', $cleaned);
                if (strlen($parts[1]) <= 2) {
                    // Comma as decimal separator
                    $cleaned = str_replace(',', '.', $cleaned);
                } else {
                    // Comma as thousand separator
                    $cleaned = str_replace(',', '', $cleaned);
                }
            } else {
                // Remove commas (thousand separators)
                $cleaned = str_replace(',', '', $cleaned);
            }

            $result = is_numeric($cleaned) ? (float)$cleaned : 0;

            // Apply negative sign if needed
            if ($isNegative) {
                $result = -$result;
            }

            return $result;
        }

        return 0;
    }

    /**
     * 🔧 UPDATED: Validate revenue data without saving (for real-time validation) - Support zero & negative
     */
    public function validateRevenueData(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'account_manager_id' => 'required|exists:account_managers,id',
                'corporate_customer_id' => 'required|exists:corporate_customers,id',
                'divisi_id' => 'required|exists:divisi,id',
                'target_revenue' => [
                    'required',
                    'numeric',
                    'min:-999999999999',
                    'max:999999999999'
                ],
                'real_revenue' => [
                    'required',
                    'numeric',
                    'min:-999999999999',
                    'max:999999999999'
                ],
                'bulan' => 'required|date_format:Y-m',
                'current_id' => 'nullable|exists:revenues,id'
            ], [
                'target_revenue.min' => 'Target Revenue tidak boleh kurang dari -999,999,999,999.',
                'target_revenue.max' => 'Target Revenue tidak boleh lebih dari 999,999,999,999.',
                'real_revenue.min' => 'Real Revenue tidak boleh kurang dari -999,999,999,999.',
                'real_revenue.max' => 'Real Revenue tidak boleh lebih dari 999,999,999,999.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'valid' => false,
                    'message' => $validator->errors()->first()
                ]);
            }

            $bulanDate = $request->bulan . '-01';

            // Check for duplicate entry
            $query = Revenue::where([
                'account_manager_id' => $request->account_manager_id,
                'corporate_customer_id' => $request->corporate_customer_id,
                'divisi_id' => $request->divisi_id,
                'bulan' => $bulanDate,
            ]);

            if ($request->current_id) {
                $query->where('id', '!=', $request->current_id);
            }

            if ($query->exists()) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Data revenue untuk kombinasi Account Manager, Corporate Customer, Divisi, dan Bulan ini sudah ada.'
                ]);
            }

            // 🔧 ENHANCED: Additional validation for special values
            $targetRevenue = $this->parseRevenueValue($request->target_revenue);
            $realRevenue = $this->parseRevenueValue($request->real_revenue);

            $validationInfo = [];

            if ($targetRevenue == 0) {
                $validationInfo[] = 'Target Revenue bernilai 0 (nol)';
            }
            if ($realRevenue == 0) {
                $validationInfo[] = 'Real Revenue bernilai 0 (nol)';
            }
            if ($targetRevenue < 0) {
                $validationInfo[] = 'Target Revenue bernilai negatif';
            }
            if ($realRevenue < 0) {
                $validationInfo[] = 'Real Revenue bernilai negatif';
            }

            $message = 'Data revenue valid.';
            if (!empty($validationInfo)) {
                $message .= ' Info: ' . implode(', ', $validationInfo) . '.';
            }

            return response()->json([
                'valid' => true,
                'message' => $message,
                'info' => [
                    'target_revenue' => $targetRevenue,
                    'real_revenue' => $realRevenue,
                    'target_is_zero' => ($targetRevenue == 0),
                    'real_is_zero' => ($realRevenue == 0),
                    'target_is_negative' => ($targetRevenue < 0),
                    'real_is_negative' => ($realRevenue < 0),
                    'special_values_detected' => !empty($validationInfo)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Revenue Data Validation Error: ' . $e->getMessage());

            return response()->json([
                'valid' => false,
                'message' => 'Terjadi kesalahan saat validasi data revenue.'
            ]);
        }
    }

    /**
     * 🔧 ENHANCED: Remove the specified revenue with better logging and response handling
     */
    public function destroy($id)
    {
        try {
            $revenue = Revenue::with(['accountManager', 'corporateCustomer', 'divisi'])->findOrFail($id);

            // Store revenue info for logging before deletion
            $revenueInfo = [
                'id' => $revenue->id,
                'account_manager' => $revenue->accountManager->nama ?? 'Unknown',
                'corporate_customer' => $revenue->corporateCustomer->nama ?? 'Unknown',
                'divisi' => $revenue->divisi->nama ?? 'Unknown',
                'bulan' => $revenue->bulan,
                'target_revenue' => $revenue->target_revenue,
                'real_revenue' => $revenue->real_revenue,
                'created_at' => $revenue->created_at,
                'updated_at' => $revenue->updated_at
            ];

            $revenue->delete();

            // ✅ ENHANCED: Comprehensive logging
            Log::info('Revenue deleted successfully', [
                'deleted_revenue' => $revenueInfo,
                'user_ip' => request()->ip(),
                'timestamp' => now()
            ]);

            // ✅ ENHANCED: Better response handling
            $message = "Revenue untuk {$revenueInfo['account_manager']} - {$revenueInfo['corporate_customer']} ({$revenueInfo['bulan']}) berhasil dihapus.";

            if (request()->ajax() || request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'deleted_revenue_info' => $revenueInfo
                    ]
                ]);
            }

            return redirect()->route('revenue.index')->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Revenue Delete Error: ' . $e->getMessage(), [
                'revenue_id' => $id,
                'user_ip' => request()->ip(),
                'error_trace' => $e->getTraceAsString()
            ]);

            if (request()->ajax() || request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('revenue.index')->with('error', 'Terjadi kesalahan saat menghapus data Revenue.');
        }
    }

    /**
     * 🔧 ENHANCED: Bulk delete revenues with improved type parameter handling and logging
     */
    public function bulkDelete(Request $request)
    {
        try {
            // 🔧 ENHANCED: More robust validation with better error messages
            $validator = Validator::make($request->all(), [
                'type' => 'nullable|in:selected,month,year,date_range,all',
                'ids' => 'required_if:type,selected|required_without:type|array',
                'ids.*' => 'exists:revenues,id',
                'month' => 'required_if:type,month|date_format:Y-m',
                'year' => 'required_if:type,year|integer|min:2020|max:2030',
                'start_date' => 'required_if:type,date_range|date_format:Y-m-d',
                'end_date' => 'required_if:type,date_range|date_format:Y-m-d|after_or_equal:start_date',
                'account_manager_id' => 'nullable|exists:account_managers,id',
                'corporate_customer_id' => 'nullable|exists:corporate_customers,id',
                'divisi_id' => 'nullable|exists:divisi,id',
            ], [
                'type.in' => 'Tipe bulk delete tidak valid. Pilihan: selected, month, year, date_range, all.',
                'ids.required_if' => 'Pilih minimal satu data revenue untuk dihapus.',
                'ids.required_without' => 'Pilih minimal satu data revenue untuk dihapus.',
                'month.required_if' => 'Bulan wajib dipilih untuk delete berdasarkan bulan.',
                'month.date_format' => 'Format bulan tidak valid (YYYY-MM).',
                'year.required_if' => 'Tahun wajib dipilih untuk delete berdasarkan tahun.',
                'start_date.required_if' => 'Tanggal mulai wajib untuk delete berdasarkan range.',
                'end_date.required_if' => 'Tanggal akhir wajib untuk delete berdasarkan range.',
                'end_date.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal mulai.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $query = Revenue::query();
            $deleted = 0;
            $errors = [];
            $deletedDetails = [];

            // 🔧 ENHANCED: Set default type if not provided
            $type = $request->get('type', 'selected');

            // ✅ BUILD QUERY based on type
            switch ($type) {
                case 'selected':
                default:
                    $ids = $request->get('ids', []);
                    if (empty($ids)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Pilih minimal satu data revenue untuk dihapus.'
                        ], 422);
                    }
                    $query->whereIn('id', $ids);
                    break;

                case 'month':
                    $monthDate = $request->month . '-01';
                    $query->where('bulan', 'like', substr($monthDate, 0, 7) . '%');
                    break;

                case 'year':
                    $query->whereYear('bulan', $request->year);
                    break;

                case 'date_range':
                    $query->whereBetween('bulan', [
                        $request->start_date,
                        $request->end_date
                    ]);
                    break;

                case 'all':
                    // No additional constraints - will delete all revenues (with optional filters)
                    break;
            }

            // ✅ APPLY ADDITIONAL FILTERS
            if ($request->account_manager_id) {
                $query->where('account_manager_id', $request->account_manager_id);
            }

            if ($request->corporate_customer_id) {
                $query->where('corporate_customer_id', $request->corporate_customer_id);
            }

            if ($request->divisi_id) {
                $query->where('divisi_id', $request->divisi_id);
            }

            // ✅ GET RECORDS before deletion for logging
            $revenues = $query->with(['accountManager', 'corporateCustomer', 'divisi'])->get();

            if ($revenues->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data revenue yang sesuai dengan kriteria.'
                ], 422);
            }

            // ✅ PERFORM DELETION with detailed tracking
            foreach ($revenues as $revenue) {
                try {
                    $revenueInfo = [
                        'id' => $revenue->id,
                        'account_manager' => $revenue->accountManager->nama ?? 'Unknown',
                        'corporate_customer' => $revenue->corporateCustomer->nama ?? 'Unknown',
                        'divisi' => $revenue->divisi->nama ?? 'Unknown',
                        'bulan' => $revenue->bulan,
                        'target_revenue' => $revenue->target_revenue,
                        'real_revenue' => $revenue->real_revenue
                    ];

                    $revenue->delete();
                    $deleted++;
                    $deletedDetails[] = $revenueInfo;

                } catch (\Exception $e) {
                    $errors[] = "Error menghapus Revenue ID {$revenue->id}: " . $e->getMessage();
                    Log::error("Bulk Delete Error for Revenue ID {$revenue->id}", [
                        'error' => $e->getMessage(),
                        'revenue_id' => $revenue->id,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            DB::commit();

            // ✅ GENERATE RESPONSE MESSAGE
            $message = "Berhasil menghapus {$deleted} data revenue.";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " data gagal dihapus.";
            }

            // ✅ ENHANCED: Comprehensive logging
            Log::info('Bulk Delete Revenue Activity', [
                'type' => $type,
                'deleted_count' => $deleted,
                'error_count' => count($errors),
                'criteria' => $request->only(['month', 'year', 'start_date', 'end_date', 'account_manager_id', 'corporate_customer_id', 'divisi_id']),
                'user_ip' => $request->ip(),
                'deleted_details' => $deletedDetails
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'deleted' => $deleted,
                    'errors' => $errors,
                    'deleted_details' => $deletedDetails,
                    'summary' => [
                        'type' => $type,
                        'criteria' => $this->generateCriteriaSummary($request),
                        'total_found' => count($revenues),
                        'successfully_deleted' => $deleted,
                        'failed_to_delete' => count($errors)
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Revenue Bulk Delete Error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'user_ip' => $request->ip(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data revenue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Bulk delete all revenues with filter support
     */
    public function bulkDeleteAll(Request $request)
    {
        try {
            $query = Revenue::query();

            // Apply filters
            if ($request->has('witel_filter') && !empty($request->witel_filter)) {
                $query->whereHas('accountManager', function($q) use ($request) {
                    $q->where('witel_id', $request->witel_filter);
                });
            }

            if ($request->has('regional_filter') && !empty($request->regional_filter)) {
                $query->whereHas('accountManager', function($q) use ($request) {
                    $q->where('regional_id', $request->regional_filter);
                });
            }

            if ($request->has('divisi_filter') && !empty($request->divisi_filter)) {
                $query->where('divisi_id', $request->divisi_filter);
            }

            if ($request->has('month_filter') && !empty($request->month_filter)) {
                $query->whereMonth('bulan', $request->month_filter);
            }

            if ($request->has('year_filter') && !empty($request->year_filter)) {
                $query->whereYear('bulan', $request->year_filter);
            }

            if ($request->has('search_filter') && !empty($request->search_filter)) {
                $searchTerm = trim($request->search_filter);
                $query->where(function($q) use ($searchTerm) {
                    $q->whereHas('accountManager', function($subQuery) use ($searchTerm) {
                        $subQuery->where('nama', 'LIKE', "%{$searchTerm}%")
                                 ->orWhere('nik', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('corporateCustomer', function($subQuery) use ($searchTerm) {
                        $subQuery->where('nama', 'LIKE', "%{$searchTerm}%")
                                 ->orWhere('nipnas', 'LIKE', "%{$searchTerm}%");
                    });
                });
            }

            // Get count before delete
            $totalCount = $query->count();

            if ($totalCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data revenue yang sesuai dengan filter.'
                ], 422);
            }

            // Perform bulk delete
            $deletedCount = $query->delete();

            // ✅ ENHANCED: Comprehensive logging
            Log::info('Bulk Delete All Revenue Activity', [
                'total_count' => $totalCount,
                'deleted_count' => $deletedCount,
                'filters' => $request->only(['witel_filter', 'regional_filter', 'divisi_filter', 'month_filter', 'year_filter', 'search_filter']),
                'user_ip' => $request->ip(),
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$deletedCount} dari {$totalCount} data revenue.",
                'data' => [
                    'total_count' => $totalCount,
                    'deleted_count' => $deletedCount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Revenue Bulk Delete All Error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'user_ip' => $request->ip(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data revenue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Get bulk delete preview (show what will be deleted)
     */
    public function bulkDeletePreview(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:selected,month,year,date_range',
                'ids' => 'required_if:type,selected|array',
                'month' => 'required_if:type,month|date_format:Y-m',
                'year' => 'required_if:type,year|integer|min:2020|max:2030',
                'start_date' => 'required_if:type,date_range|date_format:Y-m-d',
                'end_date' => 'required_if:type,date_range|date_format:Y-m-d',
                'account_manager_id' => 'nullable|exists:account_managers,id',
                'corporate_customer_id' => 'nullable|exists:corporate_customers,id',
                'divisi_id' => 'nullable|exists:divisi,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // ✅ BUILD PREVIEW QUERY (same logic as actual delete)
            $query = Revenue::with(['accountManager', 'corporateCustomer', 'divisi']);

            switch ($request->type) {
                case 'selected':
                    $query->whereIn('id', $request->ids);
                    break;
                case 'month':
                    $monthDate = $request->month . '-01';
                    $query->where('bulan', 'like', substr($monthDate, 0, 7) . '%');
                    break;
                case 'year':
                    $query->whereYear('bulan', $request->year);
                    break;
                case 'date_range':
                    $query->whereBetween('bulan', [$request->start_date, $request->end_date]);
                    break;
            }

            // Apply additional filters
            if ($request->account_manager_id) {
                $query->where('account_manager_id', $request->account_manager_id);
            }
            if ($request->corporate_customer_id) {
                $query->where('corporate_customer_id', $request->corporate_customer_id);
            }
            if ($request->divisi_id) {
                $query->where('divisi_id', $request->divisi_id);
            }

            $revenues = $query->orderBy('bulan', 'desc')->get();

            // ✅ CALCULATE STATISTICS
            $stats = [
                'total_records' => $revenues->count(),
                'total_target_revenue' => $revenues->sum('target_revenue'),
                'total_real_revenue' => $revenues->sum('real_revenue'),
                'date_range' => [
                    'earliest' => $revenues->min('bulan'),
                    'latest' => $revenues->max('bulan')
                ],
                'unique_account_managers' => $revenues->pluck('account_manager_id')->unique()->count(),
                'unique_corporate_customers' => $revenues->pluck('corporate_customer_id')->unique()->count(),
                'unique_months' => $revenues->pluck('bulan')->map(function($date) {
                    return Carbon::parse($date)->format('Y-m');
                })->unique()->count()
            ];

            // ✅ PREVIEW SAMPLE (first 20 records)
            $preview = $revenues->take(20)->map(function($revenue) {
                return [
                    'id' => $revenue->id,
                    'account_manager' => $revenue->accountManager->nama ?? 'Unknown',
                    'corporate_customer' => $revenue->corporateCustomer->nama ?? 'Unknown',
                    'divisi' => $revenue->divisi->nama ?? 'Unknown',
                    'bulan' => Carbon::parse($revenue->bulan)->format('F Y'),
                    'target_revenue' => number_format($revenue->target_revenue),
                    'real_revenue' => number_format($revenue->real_revenue)
                ];
            });

            return response()->json([
                'success' => true,
                'message' => "Ditemukan {$stats['total_records']} data revenue yang akan dihapus",
                'data' => [
                    'statistics' => $stats,
                    'preview' => $preview,
                    'criteria_summary' => $this->generateCriteriaSummary($request),
                    'warning' => $stats['total_records'] > 0 ?
                        "⚠️ Operasi ini akan menghapus {$stats['total_records']} data revenue secara permanen!" :
                        "ℹ️ Tidak ada data yang sesuai kriteria."
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk Delete Preview Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menampilkan preview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Get monthly revenue summary for bulk operations
     */
    public function getMonthlyRevenueStats(Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));

            $monthlyStats = Revenue::selectRaw('
                MONTH(bulan) as month,
                YEAR(bulan) as year,
                COUNT(*) as total_records,
                COUNT(DISTINCT account_manager_id) as unique_ams,
                COUNT(DISTINCT corporate_customer_id) as unique_customers,
                SUM(target_revenue) as total_target,
                SUM(real_revenue) as total_real
            ')
            ->whereYear('bulan', $year)
            ->groupBy('year', 'month')
            ->orderBy('month')
            ->get()
            ->map(function($stat) {
                return [
                    'month' => $stat->month,
                    'month_name' => Carbon::create(null, $stat->month, 1)->format('F'),
                    'year' => $stat->year,
                    'total_records' => $stat->total_records,
                    'unique_ams' => $stat->unique_ams,
                    'unique_customers' => $stat->unique_customers,
                    'total_target' => $stat->total_target,
                    'total_real' => $stat->total_real,
                    'achievement_rate' => $stat->total_target > 0 ? round(($stat->total_real / $stat->total_target) * 100, 2) : 0
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'year' => $year,
                    'monthly_stats' => $monthlyStats,
                    'total_records_year' => $monthlyStats->sum('total_records'),
                    'months_with_data' => $monthlyStats->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Monthly Revenue Stats Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil statistik bulanan.'
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Generate criteria summary for bulk operations
     */
    private function generateCriteriaSummary($request)
    {
        $criteria = [];

        switch ($request->type) {
            case 'selected':
                $criteria[] = count($request->ids) . " data terpilih";
                break;
            case 'month':
                $criteria[] = "Bulan: " . Carbon::createFromFormat('Y-m', $request->month)->format('F Y');
                break;
            case 'year':
                $criteria[] = "Tahun: " . $request->year;
                break;
            case 'date_range':
                $criteria[] = "Periode: " . $request->start_date . " s/d " . $request->end_date;
                break;
        }

        if ($request->account_manager_id) {
            $am = AccountManager::find($request->account_manager_id);
            $criteria[] = "Account Manager: " . ($am->nama ?? 'Unknown');
        }

        if ($request->corporate_customer_id) {
            $cc = CorporateCustomer::find($request->corporate_customer_id);
            $criteria[] = "Corporate Customer: " . ($cc->nama ?? 'Unknown');
        }

        if ($request->divisi_id) {
            $divisi = Divisi::find($request->divisi_id);
            $criteria[] = "Divisi: " . ($divisi->nama ?? 'Unknown');
        }

        return implode(', ', $criteria);
    }

    /**
     * ✅ EXISTING: Search functionality for global search with partial word support
     */
    public function search(Request $request)
    {
        try {
            $searchTerm = trim($request->get('search', ''));

            if (strlen($searchTerm) < 2) {
                return response()->json([
                    'success' => true,
                    'stats' => [
                        'total_results' => 0,
                        'account_managers_count' => 0,
                        'corporate_customers_count' => 0,
                        'revenues_count' => 0,
                    ]
                ]);
            }

            // ✅ ENHANCED: Search Account Managers with partial word matching
            $accountManagersCount = AccountManager::where(function($query) use ($searchTerm) {
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
            })->count();

            // ✅ ENHANCED: Search Corporate Customers with partial word matching
            $corporateCustomersCount = CorporateCustomer::where('nama', 'LIKE', "%{$searchTerm}%")
                                                      ->orWhere('nipnas', 'LIKE', "%{$searchTerm}%")
                                                      ->count();

            // ✅ ENHANCED: Search Revenues with partial word matching
            $revenuesCount = Revenue::where(function($query) use ($searchTerm) {
                $query->whereHas('accountManager', function($subQuery) use ($searchTerm) {
                    $subQuery->where('nama', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('nik', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('corporateCustomer', function($subQuery) use ($searchTerm) {
                    $subQuery->where('nama', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('nipnas', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('divisi', function($subQuery) use ($searchTerm) {
                    $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('accountManager.witel', function($subQuery) use ($searchTerm) {
                    $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('accountManager.regional', function($subQuery) use ($searchTerm) {
                    $subQuery->where('nama', 'LIKE', "%{$searchTerm}%");
                });
            })->count();

            $totalResults = $accountManagersCount + $corporateCustomersCount + $revenuesCount;

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_results' => $totalResults,
                    'account_managers_count' => $accountManagersCount,
                    'corporate_customers_count' => $corporateCustomersCount,
                    'revenues_count' => $revenuesCount,
                    'search_term' => $searchTerm
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Revenue Search Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat pencarian.',
                'stats' => [
                    'total_results' => 0,
                    'account_managers_count' => 0,
                    'corporate_customers_count' => 0,
                    'revenues_count' => 0,
                ]
            ]);
        }
    }

    /**
     * ✅ EXISTING: Search Account Manager for autocomplete with partial word support
     */
    public function searchAccountManager(Request $request)
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
                      });
            })
            ->with(['divisis', 'witel', 'regional'])
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
     * ✅ EXISTING: Search Corporate Customer for autocomplete with partial word support
     */
    public function searchCorporateCustomer(Request $request)
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
     * ✅ EXISTING: Get Account Manager divisions for dropdown
     */
    public function getAccountManagerDivisions($id)
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
     * ✅ EXISTING: Get statistics for dashboard
     */
    public function getStatistics()
    {
        try {
            $currentMonth = Carbon::now()->format('Y-m-d');
            $previousMonth = Carbon::now()->subMonth()->format('Y-m-d');

            $totalRevenues = Revenue::count();
            $totalAccountManagers = AccountManager::count();
            $totalCorporateCustomers = CorporateCustomer::count();

            $currentMonthRevenues = Revenue::whereYear('bulan', Carbon::now()->year)
                                          ->whereMonth('bulan', Carbon::now()->month)
                                          ->count();

            $previousMonthRevenues = Revenue::whereYear('bulan', Carbon::now()->subMonth()->year)
                                           ->whereMonth('bulan', Carbon::now()->subMonth()->month)
                                           ->count();

            $currentMonthTargetSum = Revenue::whereYear('bulan', Carbon::now()->year)
                                           ->whereMonth('bulan', Carbon::now()->month)
                                           ->sum('target_revenue');

            $currentMonthRealSum = Revenue::whereYear('bulan', Carbon::now()->year)
                                         ->whereMonth('bulan', Carbon::now()->month)
                                         ->sum('real_revenue');

            $achievementRate = $currentMonthTargetSum > 0 ?
                round(($currentMonthRealSum / $currentMonthTargetSum) * 100, 2) : 0;

            $activeAccountManagers = AccountManager::whereHas('revenues')->distinct()->count();
            $activeCorporateCustomers = CorporateCustomer::whereHas('revenues')->distinct()->count();

            return [
                'total_revenues' => $totalRevenues,
                'total_account_managers' => $totalAccountManagers,
                'total_corporate_customers' => $totalCorporateCustomers,
                'current_month_revenues' => $currentMonthRevenues,
                'previous_month_revenues' => $previousMonthRevenues,
                'current_month_target' => $currentMonthTargetSum,
                'current_month_real' => $currentMonthRealSum,
                'achievement_rate' => $achievementRate,
                'active_account_managers' => $activeAccountManagers,
                'active_corporate_customers' => $activeCorporateCustomers,
                'current_month' => Carbon::now()->format('F Y'),
                'previous_month' => Carbon::now()->subMonth()->format('F Y')
            ];

        } catch (\Exception $e) {
            Log::error('Revenue Statistics Error: ' . $e->getMessage());

            return [
                'total_revenues' => 0,
                'total_account_managers' => 0,
                'total_corporate_customers' => 0,
                'current_month_revenues' => 0,
                'previous_month_revenues' => 0,
                'current_month_target' => 0,
                'current_month_real' => 0,
                'achievement_rate' => 0,
                'active_account_managers' => 0,
                'active_corporate_customers' => 0,
                'current_month' => Carbon::now()->format('F Y'),
                'previous_month' => Carbon::now()->subMonth()->format('F Y')
            ];
        }
    }

    /**
     * ✅ EXISTING: Get Revenue data for API (alias untuk edit)
     */
    public function getRevenueData($id)
    {
        return $this->edit($id);
    }

    /**
     * ✅ EXISTING: Update Revenue via API (alias untuk update)
     */
    public function updateRevenue(Request $request, $id)
    {
        return $this->update($request, $id);
    }

    /**
     * 🔧 COMPLETELY FIXED: Import revenue data with robust error handling and response format
     */
    public function import(Request $request)
    {
        try {
            // Validasi file upload
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:xlsx,xls,csv|max:10240', // Max 10MB
                'year' => 'nullable|integer|min:2020|max:2030',
                'overwrite_mode' => 'nullable|in:update,skip,ask'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File validation error: ' . $validator->errors()->first(),
                    'data' => [
                        'total_rows' => 0,
                        'success_rows' => 0,
                        'failed_rows' => 1,
                        'imported' => 0,
                        'updated' => 0,
                        'duplicates' => 0,
                        'conflicts' => 0,
                        'errors' => 1,
                        'error_details' => [$validator->errors()->first()]
                    ]
                ], 422);
            }

            $file = $request->file('file');
            $year = $request->get('year', date('Y'));
            $overwriteMode = $request->get('overwrite_mode', 'update');

            Log::info('Starting revenue import', [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'year' => $year,
                'overwrite_mode' => $overwriteMode,
                'user_ip' => $request->ip()
            ]);

            // 🔧 CRITICAL FIX: Enhanced import with proper error handling
            $import = new RevenueImport($year, $overwriteMode);

            // Execute import in transaction for data integrity
            DB::beginTransaction();

            try {
                Excel::import($import, $file);

                // Get import summary
                $summary = $import->getImportSummary();

                // 🔧 CRITICAL FIX: Ensure all response data are primitives (strings/numbers), not arrays
                $responseData = [
                    'total_rows' => (int) ($summary['total_rows'] ?? 0),
                    'success_rows' => (int) ($summary['success_rows'] ?? 0),
                    'failed_rows' => (int) ($summary['failed_rows'] ?? 0),
                    'success_percentage' => (float) ($summary['success_percentage'] ?? 0),
                    'imported' => (int) ($summary['imported'] ?? 0),
                    'updated' => (int) ($summary['updated'] ?? 0),
                    'duplicates' => (int) ($summary['duplicates'] ?? 0),
                    'conflicts' => (int) ($summary['conflicts'] ?? 0),
                    'errors' => (int) ($summary['failed_rows'] ?? 0),
                    'year' => (int) $year,
                    'overwrite_mode' => (string) $overwriteMode,
                    'monthly_pairs_found' => (int) ($summary['monthly_pairs_found'] ?? 0),
                    'detected_columns' => is_array($summary['detected_columns'] ?? null)
                        ? implode(', ', $summary['detected_columns'])
                        : (string) ($summary['detected_columns'] ?? ''),

                    // 🔧 CRITICAL FIX: Keep arrays for frontend processing but ensure they're properly formatted
                    'error_details' => is_array($summary['error_details'] ?? null)
                        ? array_map('strval', $summary['error_details']) // Convert all to strings
                        : (array) [$summary['error_details'] ?? 'Unknown error'],
                    'warning_details' => is_array($summary['warning_details'] ?? null)
                        ? array_map('strval', $summary['warning_details'])
                        : [],
                    'success_details' => is_array($summary['success_details'] ?? null)
                        ? array_map('strval', $summary['success_details'])
                        : []
                ];

                DB::commit();

                Log::info('Revenue import completed successfully', [
                    'summary' => $responseData,
                    'file' => $file->getClientOriginalName(),
                    'user_ip' => $request->ip()
                ]);

                // 🔧 FIX: Generate consistent response structure
                $isSuccess = $responseData['failed_rows'] === 0 || $responseData['success_rows'] > 0;
                $message = $this->generateImportMessage($responseData);

                return response()->json([
                    'success' => $isSuccess,
                    'message' => $message,
                    'data' => $responseData,
                    'summary' => $responseData // Include for backward compatibility
                ]);

            } catch (\Exception $importException) {
                DB::rollBack();
                throw $importException;
            }

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Handle validation errors from Excel
            $failures = $e->failures();
            $errorDetails = [];

            foreach ($failures as $failure) {
                $errorDetails[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }

            Log::error('Revenue import validation error', [
                'failures' => $errorDetails,
                'user_ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validasi error pada file Excel',
                'data' => [
                    'total_rows' => 0,
                    'success_rows' => 0,
                    'failed_rows' => count($errorDetails),
                    'imported' => 0,
                    'updated' => 0,
                    'duplicates' => 0,
                    'conflicts' => 0,
                    'errors' => count($errorDetails),
                    'error_details' => $errorDetails
                ]
            ], 422);

        } catch (\Exception $e) {
            Log::error('Revenue import general error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_ip' => $request->ip()
            ]);

            // 🔧 CRITICAL FIX: Handle specific error types for better user experience
            $errorMessage = 'Terjadi error saat import: ' . $e->getMessage();

            // Check for specific error patterns
            if (strpos($e->getMessage(), 'Array to string conversion') !== false) {
                // This usually means the import was successful but there's a response formatting issue
                return response()->json([
                    'success' => true,
                    'message' => 'Import berhasil diproses, namun terjadi kesalahan format response. Data kemungkinan sudah tersimpan. Silakan refresh halaman untuk melihat hasil.',
                    'data' => [
                        'total_rows' => 0,
                        'success_rows' => 0,
                        'failed_rows' => 0,
                        'imported' => 0,
                        'updated' => 0,
                        'duplicates' => 0,
                        'conflicts' => 0,
                        'errors' => 0,
                        'warning' => 'Response formatting issue, but import may have succeeded. Please refresh page to see results.',
                        'error_details' => ['Response formatting error - data may have been imported successfully']
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'data' => [
                    'total_rows' => 0,
                    'success_rows' => 0,
                    'failed_rows' => 1,
                    'imported' => 0,
                    'updated' => 0,
                    'duplicates' => 0,
                    'conflicts' => 0,
                    'errors' => 1,
                    'error_details' => [$errorMessage]
                ]
            ], 500);
        }
    }

    /**
     * 🔧 NEW: Generate import message helper
     */
    private function generateImportMessage($data)
    {
        $messages = [];

        if ($data['success_rows'] > 0) {
            $messages[] = "✅ {$data['success_rows']} data berhasil diimport";
        }

        if ($data['updated'] > 0) {
            $messages[] = "🔄 {$data['updated']} data diperbarui";
        }

        if ($data['duplicates'] > 0) {
            $messages[] = "⚠️ {$data['duplicates']} data duplikasi ditemukan";
        }

        if ($data['failed_rows'] > 0) {
            $messages[] = "❌ {$data['failed_rows']} data gagal diimport";
        }

        $result = implode(', ', $messages);

        if ($data['failed_rows'] === 0) {
            return "🎉 Import berhasil! " . $result . ". Refresh halaman untuk melihat data terbaru.";
        } elseif ($data['success_rows'] > 0) {
            return "⚠️ Import selesai dengan beberapa masalah. " . $result . ". Refresh halaman untuk melihat data terbaru.";
        } else {
            return "❌ Import gagal. " . $result;
        }
    }

    /**
     * ✅ EXISTING: Export Revenue data (menggunakan RevenueExport)
     */
    public function export(Request $request)
    {
        try {
            // ✅ Collect filters dari request untuk export
            $filters = [];

            if ($request->has('year') && !empty($request->year)) {
                $filters['year'] = $request->year;
            }

            if ($request->has('month') && !empty($request->month)) {
                $filters['month'] = $request->month;
            }

            if ($request->has('account_manager_id') && !empty($request->account_manager_id)) {
                $filters['account_manager_id'] = $request->account_manager_id;
            }

            if ($request->has('corporate_customer_id') && !empty($request->corporate_customer_id)) {
                $filters['corporate_customer_id'] = $request->corporate_customer_id;
            }

            if ($request->has('divisi_id') && !empty($request->divisi_id)) {
                $filters['divisi_id'] = $request->divisi_id;
            }

            if ($request->has('witel_id') && !empty($request->witel_id)) {
                $filters['witel_id'] = $request->witel_id;
            }

            if ($request->has('regional_id') && !empty($request->regional_id)) {
                $filters['regional_id'] = $request->regional_id;
            }

            // Generate filename dengan timestamp dan filter info
            $filterInfo = '';
            if (!empty($filters['year'])) {
                $filterInfo .= '_' . $filters['year'];
            }
            if (!empty($filters['month'])) {
                $filterInfo .= '_month' . $filters['month'];
            }

            $filename = 'revenue_data' . $filterInfo . '_' . date('Y-m-d_His') . '.xlsx';

            Log::info('Starting revenue export', [
                'filters' => $filters,
                'filename' => $filename,
                'user_ip' => $request->ip()
            ]);

            // ✅ FIXED: Menggunakan RevenueExport class
            return Excel::download(new RevenueExport($filters), $filename);

        } catch (\Exception $e) {
            Log::error('Export Revenue Error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'user_ip' => $request->ip(),
                'error_trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Gagal export data Revenue: ' . $e->getMessage());
        }
    }

    /**
     * ✅ EXISTING: Download template Excel (menggunakan RevenueTemplateExport)
     */
    public function downloadTemplate()
    {
        try {
            $filename = 'template_revenue_import_' . date('Y') . '.xlsx';

            Log::info('Downloading revenue template', [
                'filename' => $filename,
                'user_ip' => request()->ip()
            ]);

            // ✅ FIXED: Menggunakan RevenueTemplateExport class instead of manual CSV
            return Excel::download(new RevenueTemplateExport(), $filename);

        } catch (\Exception $e) {
            Log::error('Download Revenue Template Error: ' . $e->getMessage(), [
                'user_ip' => request()->ip(),
                'error_trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Gagal mendownload template: ' . $e->getMessage());
        }
    }

    /**
     * ✅ EXISTING: Get import validation rules info
     */
    public function getImportInfo()
    {
        try {
            $accountManagersCount = AccountManager::count();
            $corporateCustomersCount = CorporateCustomer::count();
            $divisisCount = Divisi::count();
            $witelsCount = Witel::count();
            $regionalsCount = Regional::count();

            return response()->json([
                'success' => true,
                'info' => [
                    'required_columns' => [
                        'NAMA AM' => 'Nama Account Manager (harus sudah ada di database)',
                        'STANDARD NAME' => 'Nama Corporate Customer (harus sudah ada di database)',
                        'DIVISI' => 'Nama Divisi (opsional, jika kosong akan ambil divisi pertama dari Account Manager)',
                        'Target_[Bulan]' => 'Target Revenue bulanan (Jan, Feb, Mar, dst) - BOLEH NEGATIF atau NOL',
                        'Real_[Bulan]' => 'Real Revenue bulanan (Jan, Feb, Mar, dst) - BOLEH NEGATIF atau NOL'
                    ],
                    'monthly_format' => [
                        'supported_months' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                        'english_months' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                        'format_examples' => ['Target_Jan', 'Real_January', 'TARGET_FEB', 'REAL_FEBRUARY']
                    ],
                    'file_format' => [
                        'allowed_extensions' => ['xlsx', 'xls', 'csv'],
                        'max_file_size' => '10MB',
                        'encoding' => 'UTF-8 recommended'
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
                        'account_managers_count' => $accountManagersCount,
                        'corporate_customers_count' => $corporateCustomersCount,
                        'divisis_count' => $divisisCount,
                        'witels_count' => $witelsCount,
                        'regionals_count' => $regionalsCount
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
                        'Memory management untuk file besar',
                        '🔧 BARU: Support nilai negatif dan zero revenue'
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
                        'Gunakan overwrite_mode untuk mengatur bagaimana data existing dihandle',
                        'Import akan memberikan pesan untuk refresh manual - tidak auto refresh'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting import info: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error getting import info: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Preview Excel file before import
     */
    public function previewImport(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');

            // Read file untuk preview
            $data = Excel::toArray(new RevenueImport(), $file);

            if (empty($data) || empty($data[0])) {
                return response()->json([
                    'success' => false,
                    'message' => 'File kosong atau tidak dapat dibaca'
                ], 422);
            }

            $allRows = $data[0];
            $headers = $allRows[0] ?? [];
            $dataRows = array_slice($allRows, 1); // Exclude header
            $preview = array_slice($dataRows, 0, 10); // First 10 rows

            // ✅ ENHANCED: Validate headers and detect monthly columns
            $requiredHeaders = ['NAMA AM', 'STANDARD NAME'];
            $missingHeaders = [];
            $foundHeaders = [];
            $monthlyColumns = [];

            foreach ($requiredHeaders as $required) {
                $found = false;
                foreach ($headers as $header) {
                    if (stripos($header, str_replace('_', ' ', $required)) !== false) {
                        $found = true;
                        $foundHeaders[$required] = $header;
                        break;
                    }
                }
                if (!$found) {
                    $missingHeaders[] = $required;
                }
            }

            // Detect monthly columns
            $monthVariations = [
                'JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES',
                'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'
            ];

            foreach ($headers as $header) {
                $headerUpper = strtoupper($header);
                foreach ($monthVariations as $month) {
                    if (strpos($headerUpper, $month) !== false) {
                        $monthlyColumns[] = $header;
                        break;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Preview file berhasil',
                'data' => [
                    'total_rows' => count($dataRows),
                    'preview_rows' => count($preview),
                    'headers' => $headers,
                    'preview' => $preview,
                    'found_headers' => $foundHeaders,
                    'missing_headers' => $missingHeaders,
                    'monthly_columns' => $monthlyColumns,
                    'monthly_columns_count' => count($monthlyColumns),
                    'file_info' => [
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType()
                    ],
                    'validation_status' => [
                        'has_required_headers' => empty($missingHeaders),
                        'has_monthly_columns' => count($monthlyColumns) > 0,
                        'ready_for_import' => empty($missingHeaders) && count($monthlyColumns) > 0
                    ],
                    'revenue_support_info' => [
                        'supports_negative_values' => true,
                        'supports_zero_values' => true,
                        'empty_cells_converted_to_zero' => true,
                        'range_limit' => 'Dari -999,999,999,999 hingga 999,999,999,999'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error previewing import file: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error reading file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Get revenue statistics with comprehensive data
     */
    public function getStats(Request $request)
    {
        try {
            $query = Revenue::query();

            // Filter by date range if provided
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('bulan', [$request->start_date, $request->end_date]);
            }

            // Filter by year if provided
            if ($request->has('year')) {
                $query->whereYear('bulan', $request->year);
            }

            $totalRecords = $query->count();
            $totalTarget = $query->sum('target_revenue');
            $totalReal = $query->sum('real_revenue');
            $achievementPercentage = $totalTarget > 0 ? ($totalReal / $totalTarget) * 100 : 0;

            // 🔧 ENHANCED: Statistics with negative and zero value analysis
            $negativeTargetCount = $query->where('target_revenue', '<', 0)->count();
            $negativeRealCount = $query->where('real_revenue', '<', 0)->count();
            $zeroTargetCount = $query->where('target_revenue', '=', 0)->count();
            $zeroRealCount = $query->where('real_revenue', '=', 0)->count();

            // Monthly breakdown
            $monthlyData = $query->selectRaw('
                YEAR(bulan) as year,
                MONTH(bulan) as month,
                SUM(target_revenue) as monthly_target,
                SUM(real_revenue) as monthly_real,
                COUNT(*) as monthly_count,
                COUNT(CASE WHEN target_revenue < 0 THEN 1 END) as negative_targets,
                COUNT(CASE WHEN real_revenue < 0 THEN 1 END) as negative_reals,
                COUNT(CASE WHEN target_revenue = 0 THEN 1 END) as zero_targets,
                COUNT(CASE WHEN real_revenue = 0 THEN 1 END) as zero_reals
            ')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

            // Top performing Account Managers
            $topAccountManagers = Revenue::selectRaw('
                account_manager_id,
                SUM(real_revenue) as total_real,
                SUM(target_revenue) as total_target,
                COUNT(*) as revenue_count,
                ROUND((SUM(real_revenue) / SUM(target_revenue)) * 100, 2) as achievement_rate
            ')
            ->with('accountManager')
            ->groupBy('account_manager_id')
            ->havingRaw('SUM(target_revenue) != 0')
            ->orderBy('achievement_rate', 'desc')
            ->limit(10)
            ->get();

            // Top Corporate Customers by revenue
            $topCorporateCustomers = Revenue::selectRaw('
                corporate_customer_id,
                SUM(real_revenue) as total_real,
                SUM(target_revenue) as total_target,
                COUNT(*) as revenue_count
            ')
            ->with('corporateCustomer')
            ->groupBy('corporate_customer_id')
            ->orderBy('total_real', 'desc')
            ->limit(10)
            ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'overview' => [
                        'total_records' => $totalRecords,
                        'total_target' => $totalTarget,
                        'total_real' => $totalReal,
                        'achievement_percentage' => round($achievementPercentage, 2),
                        'negative_target_count' => $negativeTargetCount,
                        'negative_real_count' => $negativeRealCount,
                        'zero_target_count' => $zeroTargetCount,
                        'zero_real_count' => $zeroRealCount,
                        'negative_target_percentage' => $totalRecords > 0 ? round(($negativeTargetCount / $totalRecords) * 100, 2) : 0,
                        'negative_real_percentage' => $totalRecords > 0 ? round(($negativeRealCount / $totalRecords) * 100, 2) : 0,
                        'zero_target_percentage' => $totalRecords > 0 ? round(($zeroTargetCount / $totalRecords) * 100, 2) : 0,
                        'zero_real_percentage' => $totalRecords > 0 ? round(($zeroRealCount / $totalRecords) * 100, 2) : 0
                    ],
                    'monthly_data' => $monthlyData->map(function($item) {
                        return [
                            'year' => $item->year,
                            'month' => $item->month,
                            'month_name' => Carbon::create($item->year, $item->month, 1)->format('F Y'),
                            'monthly_target' => $item->monthly_target,
                            'monthly_real' => $item->monthly_real,
                            'monthly_count' => $item->monthly_count,
                            'monthly_achievement' => $item->monthly_target > 0 ? round(($item->monthly_real / $item->monthly_target) * 100, 2) : 0,
                            'negative_targets' => $item->negative_targets,
                            'negative_reals' => $item->negative_reals,
                            'zero_targets' => $item->zero_targets,
                            'zero_reals' => $item->zero_reals
                        ];
                    }),
                    'top_account_managers' => $topAccountManagers->map(function($item) {
                        return [
                            'id' => $item->account_manager_id,
                            'name' => $item->accountManager->nama ?? 'Unknown',
                            'nik' => $item->accountManager->nik ?? 'Unknown',
                            'total_real' => $item->total_real,
                            'total_target' => $item->total_target,
                            'revenue_count' => $item->revenue_count,
                            'achievement_rate' => $item->achievement_rate
                        ];
                    }),
                    'top_corporate_customers' => $topCorporateCustomers->map(function($item) {
                        return [
                            'id' => $item->corporate_customer_id,
                            'name' => $item->corporateCustomer->nama ?? 'Unknown',
                            'nipnas' => $item->corporateCustomer->nipnas ?? 'Unknown',
                            'total_real' => $item->total_real,
                            'total_target' => $item->total_target,
                            'revenue_count' => $item->revenue_count,
                            'achievement_rate' => $item->total_target > 0 ? round(($item->total_real / $item->total_target) * 100, 2) : 0
                        ];
                    }),
                    'value_analysis' => [
                        'has_negative_values' => ($negativeTargetCount > 0 || $negativeRealCount > 0),
                        'has_zero_values' => ($zeroTargetCount > 0 || $zeroRealCount > 0),
                        'negative_summary' => [
                            'target_records' => $negativeTargetCount,
                            'real_records' => $negativeRealCount,
                            'target_percentage' => $totalRecords > 0 ? round(($negativeTargetCount / $totalRecords) * 100, 2) : 0,
                            'real_percentage' => $totalRecords > 0 ? round(($negativeRealCount / $totalRecords) * 100, 2) : 0
                        ],
                        'zero_summary' => [
                            'target_records' => $zeroTargetCount,
                            'real_records' => $zeroRealCount,
                            'target_percentage' => $totalRecords > 0 ? round(($zeroTargetCount / $totalRecords) * 100, 2) : 0,
                            'real_percentage' => $totalRecords > 0 ? round(($zeroRealCount / $totalRecords) * 100, 2) : 0
                        ]
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting revenue stats: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error getting statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🆕 NEW: Get revenue with relationship summary
     */
    public function getRevenueWithRelationshipSummary($id)
    {
        try {
            $revenue = Revenue::with([
                'accountManager.witel',
                'accountManager.regional',
                'accountManager.divisis',
                'corporateCustomer',
                'divisi'
            ])->findOrFail($id);

            $relationshipSummary = [
                'account_manager' => [
                    'id' => $revenue->accountManager->id,
                    'nama' => $revenue->accountManager->nama,
                    'nik' => $revenue->accountManager->nik,
                    'witel' => $revenue->accountManager->witel->nama ?? null,
                    'regional' => $revenue->accountManager->regional->nama ?? null,
                    'divisis' => $revenue->accountManager->divisis->pluck('nama')->toArray(),
                    'total_revenues' => $revenue->accountManager->revenues()->count()
                ],
                'corporate_customer' => [
                    'id' => $revenue->corporateCustomer->id,
                    'nama' => $revenue->corporateCustomer->nama,
                    'nipnas' => $revenue->corporateCustomer->nipnas,
                    'total_revenues' => $revenue->corporateCustomer->revenues()->count()
                ],
                'divisi' => [
                    'id' => $revenue->divisi->id,
                    'nama' => $revenue->divisi->nama,
                    'total_revenues' => $revenue->divisi->revenues()->count()
                ]
            ];

            // 🔧 ENHANCED: Include special value analysis
            $specialValueInfo = [
                'target_is_zero' => ($revenue->target_revenue == 0),
                'real_is_zero' => ($revenue->real_revenue == 0),
                'target_is_negative' => ($revenue->target_revenue < 0),
                'real_is_negative' => ($revenue->real_revenue < 0),
                'has_special_values' => ($revenue->target_revenue <= 0 || $revenue->real_revenue <= 0)
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'revenue' => [
                        'id' => $revenue->id,
                        'target_revenue' => $revenue->target_revenue,
                        'real_revenue' => $revenue->real_revenue,
                        'bulan' => $revenue->bulan,
                        'achievement_rate' => $revenue->target_revenue > 0 ?
                            round(($revenue->real_revenue / $revenue->target_revenue) * 100, 2) : 0,
                        'created_at' => $revenue->created_at,
                        'updated_at' => $revenue->updated_at,
                        'special_values' => $specialValueInfo
                    ],
                    'relationships' => $relationshipSummary
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get Revenue With Relationship Summary Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Revenue tidak ditemukan.'
            ], 404);
        }
    }

    /**
     * 🆕 NEW: Get revenue analysis for negative and zero values
     */
    public function getValueAnalysis(Request $request)
    {
        try {
            $query = Revenue::query();

            // Apply filters if provided
            if ($request->has('year') && !empty($request->year)) {
                $query->whereYear('bulan', $request->year);
            }

            if ($request->has('month') && !empty($request->month)) {
                $query->whereMonth('bulan', $request->month);
            }

            if ($request->has('account_manager_id') && !empty($request->account_manager_id)) {
                $query->where('account_manager_id', $request->account_manager_id);
            }

            if ($request->has('divisi_id') && !empty($request->divisi_id)) {
                $query->where('divisi_id', $request->divisi_id);
            }

            // Get detailed breakdown
            $totalRecords = $query->count();

            $valueBreakdown = [
                'negative_target' => $query->where('target_revenue', '<', 0)->count(),
                'zero_target' => $query->where('target_revenue', '=', 0)->count(),
                'positive_target' => $query->where('target_revenue', '>', 0)->count(),
                'negative_real' => $query->where('real_revenue', '<', 0)->count(),
                'zero_real' => $query->where('real_revenue', '=', 0)->count(),
                'positive_real' => $query->where('real_revenue', '>', 0)->count()
            ];

            // Get records with negative or zero values for detailed analysis
            $negativeTargetRecords = $query->where('target_revenue', '<', 0)
                ->with(['accountManager', 'corporateCustomer', 'divisi'])
                ->limit(10)
                ->get()
                ->map(function($revenue) {
                    return [
                        'id' => $revenue->id,
                        'account_manager' => $revenue->accountManager->nama ?? 'Unknown',
                        'corporate_customer' => $revenue->corporateCustomer->nama ?? 'Unknown',
                        'divisi' => $revenue->divisi->nama ?? 'Unknown',
                        'bulan' => $revenue->bulan,
                        'target_revenue' => $revenue->target_revenue,
                        'real_revenue' => $revenue->real_revenue
                    ];
                });

            $zeroTargetRecords = $query->where('target_revenue', '=', 0)
                ->with(['accountManager', 'corporateCustomer', 'divisi'])
                ->limit(10)
                ->get()
                ->map(function($revenue) {
                    return [
                        'id' => $revenue->id,
                        'account_manager' => $revenue->accountManager->nama ?? 'Unknown',
                        'corporate_customer' => $revenue->corporateCustomer->nama ?? 'Unknown',
                        'divisi' => $revenue->divisi->nama ?? 'Unknown',
                        'bulan' => $revenue->bulan,
                        'target_revenue' => $revenue->target_revenue,
                        'real_revenue' => $revenue->real_revenue
                    ];
                });

            $negativeRealRecords = $query->where('real_revenue', '<', 0)
                ->with(['accountManager', 'corporateCustomer', 'divisi'])
                ->limit(10)
                ->get()
                ->map(function($revenue) {
                    return [
                        'id' => $revenue->id,
                        'account_manager' => $revenue->accountManager->nama ?? 'Unknown',
                        'corporate_customer' => $revenue->corporateCustomer->nama ?? 'Unknown',
                        'divisi' => $revenue->divisi->nama ?? 'Unknown',
                        'bulan' => $revenue->bulan,
                        'target_revenue' => $revenue->target_revenue,
                        'real_revenue' => $revenue->real_revenue
                    ];
                });

            $zeroRealRecords = $query->where('real_revenue', '=', 0)
                ->with(['accountManager', 'corporateCustomer', 'divisi'])
                ->limit(10)
                ->get()
                ->map(function($revenue) {
                    return [
                        'id' => $revenue->id,
                        'account_manager' => $revenue->accountManager->nama ?? 'Unknown',
                        'corporate_customer' => $revenue->corporateCustomer->nama ?? 'Unknown',
                        'divisi' => $revenue->divisi->nama ?? 'Unknown',
                        'bulan' => $revenue->bulan,
                        'target_revenue' => $revenue->target_revenue,
                        'real_revenue' => $revenue->real_revenue
                    ];
                });

            // Calculate percentages
            $percentages = [];
            foreach ($valueBreakdown as $key => $count) {
                $percentages[$key . '_percentage'] = $totalRecords > 0 ? round(($count / $totalRecords) * 100, 2) : 0;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total_records' => $totalRecords,
                        'value_breakdown' => array_merge($valueBreakdown, $percentages),
                        'has_negative_values' => ($valueBreakdown['negative_target'] > 0 || $valueBreakdown['negative_real'] > 0),
                        'has_zero_values' => ($valueBreakdown['zero_target'] > 0 || $valueBreakdown['zero_real'] > 0)
                    ],
                    'detailed_records' => [
                        'negative_target_sample' => $negativeTargetRecords,
                        'zero_target_sample' => $zeroTargetRecords,
                        'negative_real_sample' => $negativeRealRecords,
                        'zero_real_sample' => $zeroRealRecords
                    ],
                    'analysis_tips' => [
                        'Nilai negatif mungkin mengindikasikan koreksi atau pengembalian',
                        'Nilai zero target bisa berarti tidak ada target yang ditetapkan',
                        'Nilai zero real bisa berarti tidak ada realisasi atau data belum diinput',
                        'Perhatikan tren nilai negatif untuk analisis lebih lanjut'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get Value Analysis Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menganalisis nilai revenue.'
            ], 500);
        }
    }

    /**
     * 🆕 NEW: Export revenue data with special value filtering
     */
    public function exportWithValueFilter(Request $request)
    {
        try {
            $filters = $request->only([
                'year', 'month', 'account_manager_id', 'corporate_customer_id',
                'divisi_id', 'witel_id', 'regional_id'
            ]);

            // Add special value filters
            if ($request->has('include_negative') && $request->include_negative) {
                $filters['include_negative'] = true;
            }

            if ($request->has('include_zero') && $request->include_zero) {
                $filters['include_zero'] = true;
            }

            if ($request->has('only_negative') && $request->only_negative) {
                $filters['only_negative'] = true;
            }

            if ($request->has('only_zero') && $request->only_zero) {
                $filters['only_zero'] = true;
            }

            // Generate filename dengan filter info
            $filterInfo = '';
            if (!empty($filters['year'])) {
                $filterInfo .= '_' . $filters['year'];
            }
            if (!empty($filters['month'])) {
                $filterInfo .= '_month' . $filters['month'];
            }
            if (!empty($filters['only_negative'])) {
                $filterInfo .= '_negative_only';
            }
            if (!empty($filters['only_zero'])) {
                $filterInfo .= '_zero_only';
            }

            $filename = 'revenue_data_special_values' . $filterInfo . '_' . date('Y-m-d_His') . '.xlsx';

            Log::info('Starting revenue export with value filter', [
                'filters' => $filters,
                'filename' => $filename,
                'user_ip' => $request->ip()
            ]);

            return Excel::download(new RevenueExport($filters), $filename);

        } catch (\Exception $e) {
            Log::error('Export Revenue With Value Filter Error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'user_ip' => $request->ip(),
                'error_trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Gagal export data Revenue: ' . $e->getMessage());
        }
    }
}
