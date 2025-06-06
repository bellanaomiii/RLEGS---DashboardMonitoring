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
     * ✅ EXISTING: Store a newly created revenue with proper date handling
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'account_manager_id' => 'required|exists:account_managers,id',
                'corporate_customer_id' => 'required|exists:corporate_customers,id',
                'divisi_id' => 'required|exists:divisi,id',
                'target_revenue' => 'required|numeric|min:0|max:999999999999',
                'real_revenue' => 'required|numeric|min:0|max:999999999999',
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
                'target_revenue.min' => 'Target Revenue tidak boleh negatif.',
                'target_revenue.max' => 'Target Revenue terlalu besar.',
                'real_revenue.required' => 'Real Revenue wajib diisi.',
                'real_revenue.numeric' => 'Real Revenue harus berupa angka.',
                'real_revenue.min' => 'Real Revenue tidak boleh negatif.',
                'real_revenue.max' => 'Real Revenue terlalu besar.',
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

            // Create Revenue with proper date format
            $revenue = Revenue::create([
                'account_manager_id' => $request->account_manager_id,
                'corporate_customer_id' => $request->corporate_customer_id,
                'divisi_id' => $request->divisi_id,
                'target_revenue' => $request->target_revenue,
                'real_revenue' => $request->real_revenue,
                'bulan' => $bulanDate, // Store as Y-m-d format
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data Revenue berhasil ditambahkan.',
                'data' => $revenue->load(['accountManager.witel', 'accountManager.regional', 'corporateCustomer', 'divisi'])
            ]);

        } catch (\Exception $e) {
            Log::error('Revenue Store Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Show the form for editing the specified revenue
     */
    public function edit($id)
    {
        try {
            $revenue = Revenue::with(['accountManager.witel', 'accountManager.regional', 'corporateCustomer', 'divisi'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $revenue
            ]);

        } catch (\Exception $e) {
            Log::error('Revenue Edit Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Revenue tidak ditemukan.'
            ], 404);
        }
    }

    /**
     * ✅ EXISTING: Update the specified revenue with proper date handling
     */
    public function update(Request $request, $id)
    {
        try {
            $revenue = Revenue::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'account_manager_id' => 'required|exists:account_managers,id',
                'corporate_customer_id' => 'required|exists:corporate_customers,id',
                'divisi_id' => 'required|exists:divisi,id',
                'target_revenue' => 'required|numeric|min:0|max:999999999999',
                'real_revenue' => 'required|numeric|min:0|max:999999999999',
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
                'target_revenue.min' => 'Target Revenue tidak boleh negatif.',
                'target_revenue.max' => 'Target Revenue terlalu besar.',
                'real_revenue.required' => 'Real Revenue wajib diisi.',
                'real_revenue.numeric' => 'Real Revenue harus berupa angka.',
                'real_revenue.min' => 'Real Revenue tidak boleh negatif.',
                'real_revenue.max' => 'Real Revenue terlalu besar.',
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

            // Update Revenue
            $revenue->update([
                'account_manager_id' => $request->account_manager_id,
                'corporate_customer_id' => $request->corporate_customer_id,
                'divisi_id' => $request->divisi_id,
                'target_revenue' => $request->target_revenue,
                'real_revenue' => $request->real_revenue,
                'bulan' => $bulanDate,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data Revenue berhasil diperbarui.',
                'data' => $revenue->load(['accountManager.witel', 'accountManager.regional', 'corporateCustomer', 'divisi'])
            ]);

        } catch (\Exception $e) {
            Log::error('Revenue Update Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Remove the specified revenue
     */
    public function destroy($id)
    {
        try {
            $revenue = Revenue::findOrFail($id);
            $revenue->delete();

            return back()->with('success', 'Data Revenue berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Revenue Delete Error: ' . $e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat menghapus data Revenue.');
        }
    }

    /**
     * ✅ NEW: Bulk delete revenues with advanced filtering
     */
    public function bulkDelete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:selected,month,year,date_range',
                'ids' => 'required_if:type,selected|array',
                'ids.*' => 'exists:revenues,id',
                'month' => 'required_if:type,month|date_format:Y-m',
                'year' => 'required_if:type,year|integer|min:2020|max:2030',
                'start_date' => 'required_if:type,date_range|date_format:Y-m-d',
                'end_date' => 'required_if:type,date_range|date_format:Y-m-d|after_or_equal:start_date',
                'account_manager_id' => 'nullable|exists:account_managers,id',
                'corporate_customer_id' => 'nullable|exists:corporate_customers,id',
                'divisi_id' => 'nullable|exists:divisi,id',
            ], [
                'type.required' => 'Tipe bulk delete wajib dipilih.',
                'type.in' => 'Tipe bulk delete tidak valid.',
                'ids.required_if' => 'Pilih minimal satu data revenue untuk dihapus.',
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
                    'message' => $validator->errors()->first()
                ], 422);
            }

            DB::beginTransaction();

            $query = Revenue::query();
            $deleted = 0;
            $errors = [];
            $deletedDetails = [];

            // ✅ BUILD QUERY based on type
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
                    $query->whereBetween('bulan', [
                        $request->start_date,
                        $request->end_date
                    ]);
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
                }
            }

            DB::commit();

            // ✅ GENERATE RESPONSE MESSAGE
            $message = "Berhasil menghapus {$deleted} data revenue.";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " data gagal dihapus.";
            }

            // ✅ LOG BULK DELETE ACTIVITY
            Log::info('Bulk Delete Revenue Activity', [
                'type' => $request->type,
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
                        'type' => $request->type,
                        'criteria' => $this->generateCriteriaSummary($request),
                        'total_found' => count($revenues),
                        'successfully_deleted' => $deleted,
                        'failed_to_delete' => count($errors)
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Revenue Bulk Delete Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data revenue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NEW: Get bulk delete preview (show what will be deleted)
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
     * ✅ NEW: Get monthly revenue summary for bulk operations
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
     * ✅ NEW: Generate criteria summary for bulk operations
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
     * ✅ ENHANCED: Import revenue data from Excel with detailed error tracking and conflict resolution
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
                    'message' => 'File validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');
            $year = $request->get('year', date('Y'));
            $overwriteMode = $request->get('overwrite_mode', 'update'); // ✅ NEW: Overwrite mode

            Log::info('Starting revenue import', [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'year' => $year,
                'overwrite_mode' => $overwriteMode
            ]);

            // ✅ ENHANCED: Buat instance import dengan year dan overwrite mode parameter
            $import = new RevenueImport($year, $overwriteMode);

            // Jalankan import
            Excel::import($import, $file);

            // ✅ ENHANCED: Dapatkan summary hasil import dengan detailed error tracking dan conflict details
            $summary = $import->getImportSummary();

            Log::info('Revenue import completed', [
                'summary' => $summary,
                'file' => $file->getClientOriginalName()
            ]);

            // ✅ ENHANCED: Generate conflict summary for better reporting
            $conflictSummary = $this->generateConflictSummary($summary);

            // ✅ DETAILED ERROR REPORTING: Jika ada error, return response dengan detail error per kategori
            if ($summary['failed_rows'] > 0) {
                return response()->json([
                    'success' => false,
                    'message' => $this->generateEnhancedImportMessage($summary, $conflictSummary),
                    'summary' => $summary,
                    'conflicts' => $conflictSummary,
                    'data' => [
                        'total_rows' => $summary['total_rows'],
                        'success_rows' => $summary['success_rows'],
                        'failed_rows' => $summary['failed_rows'],
                        'success_percentage' => $summary['success_percentage'],
                        'overwrite_mode' => $overwriteMode,

                        // ✅ DETAILED ERROR BREAKDOWN
                        'missing_account_managers' => $summary['missing_account_managers'],
                        'missing_corporate_customers' => $summary['missing_corporate_customers'],
                        'missing_divisi' => $summary['missing_divisi'],
                        'validation_errors' => $summary['validation_errors'],
                        'duplicates' => $summary['duplicates'],

                        // ✅ CONFLICT INFORMATION
                        'conflicts_info' => $conflictSummary,

                        // ✅ ERROR SUMMARY COUNTS
                        'error_details' => $summary['error_details'],

                        // ✅ ADDITIONAL DETAILS
                        'year' => $summary['year'],
                        'monthly_pairs_found' => $summary['monthly_pairs_found'],
                        'detected_columns' => $summary['detected_columns'],

                        // ✅ COMPREHENSIVE ERROR LOG
                        'all_error_details' => $summary['all_error_details'] ?? [],
                        'warning_details' => $summary['warning_details'] ?? [],
                        'success_details' => $summary['success_details'] ?? []
                    ]
                ], 422);
            }

            // Jika semua berhasil
            return response()->json([
                'success' => true,
                'message' => $this->generateEnhancedImportMessage($summary, $conflictSummary),
                'summary' => $summary,
                'conflicts' => $conflictSummary,
                'data' => [
                    'total_rows' => $summary['total_rows'],
                    'success_rows' => $summary['success_rows'],
                    'failed_rows' => $summary['failed_rows'],
                    'success_percentage' => $summary['success_percentage'],
                    'year' => $summary['year'],
                    'overwrite_mode' => $overwriteMode,
                    'monthly_pairs_found' => $summary['monthly_pairs_found'],
                    'conflicts_resolved' => $conflictSummary['total_conflicts'] ?? 0
                ]
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Handle validation errors dari Excel
            $failures = $e->failures();
            $errorDetails = [];

            foreach ($failures as $failure) {
                $errorDetails[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values()
                ];
            }

            Log::error('Revenue import validation error', [
                'failures' => $errorDetails
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation error pada file Excel',
                'errors' => $errorDetails,
                'data' => [
                    'total_rows' => 0,
                    'success_rows' => 0,
                    'failed_rows' => count($errorDetails),
                    'validation_errors' => $errorDetails
                ]
            ], 422);

        } catch (\Exception $e) {
            Log::error('Revenue import general error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi error saat import: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'data' => [
                    'total_rows' => 0,
                    'success_rows' => 0,
                    'failed_rows' => 1,
                    'error_details' => [
                        'system_error' => $e->getMessage()
                    ]
                ]
            ], 500);
        }
    }

    /**
     * ✅ NEW: Generate conflict summary for import
     */
    private function generateConflictSummary($summary)
    {
        $conflicts = [
            'has_conflicts' => false,
            'total_conflicts' => 0,
            'update_conflicts_count' => 0,
            'skip_conflicts_count' => 0,
            'ask_conflicts_count' => 0,
            'update_conflicts' => [],
            'recommendations' => []
        ];

        // Extract conflict information if available
        if (isset($summary['conflicts'])) {
            $conflicts['has_conflicts'] = $summary['conflicts']['total_conflicts'] > 0;
            $conflicts['total_conflicts'] = $summary['conflicts']['total_conflicts'];

            if (isset($summary['conflicts']['conflicts_by_action'])) {
                $conflicts['update_conflicts_count'] = $summary['conflicts']['conflicts_by_action']['updated'] ?? 0;
                $conflicts['skip_conflicts_count'] = $summary['conflicts']['conflicts_by_action']['skipped'] ?? 0;
                $conflicts['ask_conflicts_count'] = $summary['conflicts']['conflicts_by_action']['needs_confirmation'] ?? 0;
            }

            if (isset($summary['conflicts']['conflict_details'])) {
                $conflicts['update_conflicts'] = $summary['conflicts']['conflict_details'];
            }
        }

        // Generate recommendations
        if ($conflicts['total_conflicts'] > 0) {
            $conflicts['recommendations'][] = "💡 {$conflicts['total_conflicts']} konflik data ditemukan";

            if ($conflicts['update_conflicts_count'] > 0) {
                $conflicts['recommendations'][] = "✅ {$conflicts['update_conflicts_count']} data telah diperbarui";
            }

            if ($conflicts['skip_conflicts_count'] > 0) {
                $conflicts['recommendations'][] = "⚠️ {$conflicts['skip_conflicts_count']} data dilewati";
            }

            if ($conflicts['ask_conflicts_count'] > 0) {
                $conflicts['recommendations'][] = "❓ {$conflicts['ask_conflicts_count']} data butuh konfirmasi";
            }

            $conflicts['recommendations'][] = "💡 Periksa log detail untuk melihat perubahan yang dilakukan";
        }

        return $conflicts;
    }

    /**
     * ✅ NEW: Generate enhanced import message
     */
    private function generateEnhancedImportMessage($summary, $conflictSummary)
    {
        $messages = [];

        if ($summary['success_rows'] > 0) {
            $messages[] = "✅ {$summary['success_rows']} data berhasil diimport";
        }

        if ($conflictSummary['update_conflicts_count'] > 0) {
            $messages[] = "🔄 {$conflictSummary['update_conflicts_count']} data diperbarui";
        }

        if ($conflictSummary['skip_conflicts_count'] > 0) {
            $messages[] = "⚠️ {$conflictSummary['skip_conflicts_count']} data dilewati";
        }

        if (isset($summary['duplicates']) && $summary['duplicates'] > 0) {
            $messages[] = "⚠️ {$summary['duplicates']} data duplikasi ditemukan";
        }

        if ($summary['failed_rows'] > 0) {
            $messages[] = "❌ {$summary['failed_rows']} data gagal diimport";
        }

        $result = implode(', ', $messages);

        if ($summary['failed_rows'] == 0) {
            return "🎉 Import berhasil! " . $result;
        } elseif ($summary['success_rows'] > 0) {
            return "⚠️ Import selesai dengan beberapa masalah. " . $result;
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
                'filename' => $filename
            ]);

            // ✅ FIXED: Menggunakan RevenueExport class
            return Excel::download(new RevenueExport($filters), $filename);

        } catch (\Exception $e) {
            Log::error('Export Revenue Error: ' . $e->getMessage());
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
                'filename' => $filename
            ]);

            // ✅ FIXED: Menggunakan RevenueTemplateExport class instead of manual CSV
            return Excel::download(new RevenueTemplateExport(), $filename);

        } catch (\Exception $e) {
            Log::error('Download Revenue Template Error: ' . $e->getMessage());
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
                        'Target_[Bulan]' => 'Target Revenue bulanan (Jan, Feb, Mar, dst)',
                        'Real_[Bulan]' => 'Real Revenue bulanan (Jan, Feb, Mar, dst)'
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
                        'Nilai revenue harus berupa angka (bisa dengan format currency)',
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
                        'Memory management untuk file besar'
                    ],
                    'tips' => [
                        'Pastikan nama Account Manager dan Corporate Customer persis sama dengan data di database',
                        'Format kolom bulanan: Target_Jan, Real_Jan, Target_Feb, Real_Feb, dst',
                        'Bisa gunakan nama bulan Indonesia (Jan, Feb, Mar) atau Inggris (January, February, March)',
                        'Nilai revenue bisa menggunakan format dengan koma atau titik sebagai pemisah ribuan',
                        'Jika ada error, perhatikan detail error yang menunjukkan baris dan jenis kesalahan',
                        'Gunakan fuzzy matching akan otomatis mencari nama yang mirip 80%',
                        'File Excel akan diproses dalam chunks untuk menghindari timeout',
                        'Gunakan overwrite_mode untuk mengatur bagaimana data existing dihandle'
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

            // Monthly breakdown
            $monthlyData = $query->selectRaw('
                YEAR(bulan) as year,
                MONTH(bulan) as month,
                SUM(target_revenue) as monthly_target,
                SUM(real_revenue) as monthly_real,
                COUNT(*) as monthly_count
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
            ->havingRaw('SUM(target_revenue) > 0')
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
                        'achievement_percentage' => round($achievementPercentage, 2)
                    ],
                    'monthly_data' => $monthlyData->map(function($item) {
                        return [
                            'year' => $item->year,
                            'month' => $item->month,
                            'month_name' => Carbon::create($item->year, $item->month, 1)->format('F Y'),
                            'monthly_target' => $item->monthly_target,
                            'monthly_real' => $item->monthly_real,
                            'monthly_count' => $item->monthly_count,
                            'monthly_achievement' => $item->monthly_target > 0 ? round(($item->monthly_real / $item->monthly_target) * 100, 2) : 0
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
                    })
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
}