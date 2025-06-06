<?php

namespace App\Http\Controllers;

use App\Models\CorporateCustomer;
use App\Imports\CorporateCustomerImport;
use App\Exports\CorporateCustomerExport;
use App\Exports\CorporateCustomerTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class CorporateCustomerController extends Controller
{
    /**
     * ✅ EXISTING: Display a listing of corporate customers with enhanced search
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
     * ✅ EXISTING: Store a newly created corporate customer with proper NIPNAS validation
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
     * ✅ EXISTING: Show the form for editing the specified corporate customer
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
     * ✅ EXISTING: Update the specified corporate customer with proper NIPNAS validation
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
     * ✅ EXISTING: Remove the specified corporate customer
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
     * ✅ NEW: Bulk delete corporate customers
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
            $deletedDetails = [];

            foreach ($ids as $id) {
                try {
                    $corporateCustomer = CorporateCustomer::findOrFail($id);

                    // Check if has related revenue data
                    if ($corporateCustomer->revenues()->exists()) {
                        $errors[] = "Corporate Customer '{$corporateCustomer->nama}' tidak dapat dihapus karena memiliki data revenue terkait.";
                        continue;
                    }

                    $corporateCustomerInfo = [
                        'id' => $corporateCustomer->id,
                        'nama' => $corporateCustomer->nama,
                        'nipnas' => $corporateCustomer->nipnas,
                        'created_at' => $corporateCustomer->created_at
                    ];

                    $corporateCustomer->delete();
                    $deleted++;
                    $deletedDetails[] = $corporateCustomerInfo;

                } catch (\Exception $e) {
                    $errors[] = "Error menghapus Corporate Customer ID {$id}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Berhasil menghapus {$deleted} Corporate Customer.";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " data gagal dihapus.";
            }

            // ✅ LOG BULK DELETE ACTIVITY
            Log::info('Bulk Delete Corporate Customer Activity', [
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
            Log::error('Corporate Customer Bulk Delete Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data Corporate Customer.'
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Import Corporate Customers dengan detailed error context
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
                    'helper_info' => $this->getImportHelperInfo(),
                    'validation_rules' => $this->getValidationRules()
                ]
            ], 422);
        }

        try {
            // ✅ Use dedicated Import class with detailed tracking
            $import = new CorporateCustomerImport();
            Excel::import($import, $request->file('file'));

            // ✅ Get detailed results from Import class
            $results = $import->getImportResults();

            // ✅ ENHANCED: Add helper context to results
            $results['helper_info'] = $this->getImportHelperInfo();
            $results['validation_rules'] = $this->getValidationRules();
            $results['existing_data_sample'] = $this->getExistingDataSample();

            // ✅ Generate appropriate message
            $message = $this->generateImportMessage(
                $results['imported'],
                $results['updated'],
                $results['errors']
            );

            // Log import summary
            Log::info('Corporate Customer Import completed', [
                'file_name' => $request->file('file')->getClientOriginalName(),
                'results' => $results
            ]);

            return response()->json([
                'success' => $results['errors'] == 0 || ($results['imported'] + $results['updated']) > 0,
                'message' => $message,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Corporate Customer Import Error: ' . $e->getMessage());

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
                    'helper_info' => $this->getImportHelperInfo(),
                    'validation_rules' => $this->getValidationRules()
                ]
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Download template Excel (menggunakan CorporateCustomerTemplateExport)
     */
    public function downloadTemplate()
    {
        try {
            $filename = 'template_corporate_customer_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(new CorporateCustomerTemplateExport(), $filename);

        } catch (\Exception $e) {
            Log::error('Download Template Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mendownload template: ' . $e->getMessage());
        }
    }

    /**
     * ✅ EXISTING: Export Corporate Customer data (menggunakan CorporateCustomerExport)
     */
    public function export(Request $request)
    {
        try {
            $filename = 'corporate_customers_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(new CorporateCustomerExport(), $filename);

        } catch (\Exception $e) {
            Log::error('Export Corporate Customer Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal export data Corporate Customer: ' . $e->getMessage());
        }
    }

    /**
     * ✅ EXISTING: Search Corporate Customers untuk autocomplete dengan debugging comprehensive
     */
    public function search(Request $request)
    {
        try {
            $searchTerm = trim($request->get('search', ''));

            // ✅ DEBUG: Log the incoming request untuk troubleshooting
            Log::info('Corporate Customer Search Request', [
                'search_term' => $searchTerm,
                'request_method' => $request->method(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);

            // ✅ VALIDATION: Check minimum search length
            if (strlen($searchTerm) < 2) {
                Log::info('Search term too short', ['length' => strlen($searchTerm)]);
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Search term must be at least 2 characters'
                ]);
            }

            // ✅ DATABASE CHECK: Verify table exists dan accessible
            if (!Schema::hasTable('corporate_customers')) {
                Log::error('corporate_customers table does not exist');
                return response()->json([
                    'success' => false,
                    'message' => 'Database table not found',
                    'data' => []
                ], 500);
            }

            // ✅ ENHANCED QUERY: More robust search dengan debugging
            $query = CorporateCustomer::query();

            // Count total records untuk debugging
            $totalCount = CorporateCustomer::count();
            Log::info('Total Corporate Customers in database', ['count' => $totalCount]);

            // Apply search filter dengan comprehensive matching
            $corporateCustomers = $query->where(function($q) use ($searchTerm) {
                    $q->where('nama', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('nipnas', 'LIKE', "%{$searchTerm}%");
                })
                ->orderBy('nama')
                ->limit(10)
                ->get(['id', 'nama', 'nipnas', 'created_at']);

            // ✅ DETAILED LOGGING: Log search results untuk debugging
            Log::info('Corporate Customer Search Results', [
                'search_term' => $searchTerm,
                'total_in_db' => $totalCount,
                'found_count' => $corporateCustomers->count(),
                'first_result' => $corporateCustomers->first() ? $corporateCustomers->first()->toArray() : null
            ]);

            // ✅ SUCCESS RESPONSE: Return consistent format
            return response()->json([
                'success' => true,
                'data' => $corporateCustomers->map(function($customer) {
                    return [
                        'id' => $customer->id,
                        'nama' => $customer->nama,
                        'nipnas' => $customer->nipnas
                    ];
                }),
                'meta' => [
                    'search_term' => $searchTerm,
                    'found_count' => $corporateCustomers->count(),
                    'total_in_db' => $totalCount
                ]
            ]);

        } catch (\Exception $e) {
            // ✅ COMPREHENSIVE ERROR HANDLING: Detailed error logging
            Log::error('Corporate Customer Search Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'search_term' => $request->get('search', ''),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Pencarian Corporate Customer gagal. Silakan coba lagi.',
                'data' => [],
                'error_details' => [
                    'message' => $e->getMessage(),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Test method untuk debugging database connection dan data
     */
    public function testSearch()
    {
        try {
            // Test database connection
            DB::connection()->getPdo();

            // Test table existence
            $tableExists = Schema::hasTable('corporate_customers');

            // Get table info
            $columns = Schema::getColumnListing('corporate_customers');

            // Get sample data
            $totalCount = CorporateCustomer::count();
            $sampleData = CorporateCustomer::limit(5)->get(['id', 'nama', 'nipnas']);

            // Test search query
            $testSearch = CorporateCustomer::where('nama', 'LIKE', '%a%')->limit(3)->get(['id', 'nama', 'nipnas']);

            return response()->json([
                'success' => true,
                'message' => 'Corporate Customer test berhasil',
                'data' => [
                    'database_connected' => true,
                    'table_exists' => $tableExists,
                    'table_columns' => $columns,
                    'total_records' => $totalCount,
                    'sample_data' => $sampleData,
                    'test_search_results' => $testSearch,
                    'test_query' => "SELECT * FROM corporate_customers WHERE nama LIKE '%a%' LIMIT 3"
                ],
                'timestamp' => now()->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test gagal: ' . $e->getMessage(),
                'error' => [
                    'message' => $e->getMessage(),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ],
                'timestamp' => now()->toDateTimeString()
            ], 500);
        }
    }

    /**
     * ✅ EXISTING: Get import helper info untuk user guidance
     */
    private function getImportHelperInfo()
    {
        return [
            'template_available' => true,
            'export_available' => true,
            'required_columns' => [
                'NIPNAS' => 'Nomor identifikasi unik (3-20 digit angka)',
                'STANDARD NAME' => 'Nama lengkap corporate customer (min 3 karakter)'
            ],
            'tips' => [
                '💡 Download template untuk melihat format yang benar',
                '💡 NIPNAS harus unik dan berupa angka 3-20 digit',
                '💡 Gunakan export existing data sebagai referensi format',
                '💡 Jika NIPNAS sudah ada, data akan diupdate',
                '💡 Nama customer akan di-trim untuk menghilangkan spasi berlebih'
            ],
            'validation_examples' => [
                'NIPNAS_VALID' => ['4648251', '123456', '999999999'],
                'NIPNAS_INVALID' => ['AB123', '12', '123456789012345678901', '0.123'],
                'NAMA_VALID' => ['PT TELKOM INDONESIA', 'BANK BCA', 'CV MAJU BERSAMA'],
                'NAMA_INVALID' => ['AB', '', '   ']
            ]
        ];
    }

    /**
     * ✅ EXISTING: Get validation rules untuk reference
     */
    private function getValidationRules()
    {
        return [
            'NIPNAS' => [
                'format' => 'Angka saja (0-9)',
                'length' => '3-20 digit',
                'range' => 'Minimal 100, maksimal 99999999999999999999',
                'unique' => 'Tidak boleh duplikasi dengan data existing',
                'examples' => [
                    'valid' => ['12345', '4648251', '999999999'],
                    'invalid' => ['ABC123', '12', '123456789012345678901']
                ]
            ],
            'STANDARD_NAME' => [
                'format' => 'Teks alphanumeric dengan spasi dan karakter khusus',
                'length' => 'Minimal 3 karakter, maksimal 255 karakter',
                'clean' => 'Spasi berlebih akan dibersihkan otomatis',
                'examples' => [
                    'valid' => ['PT TELKOM INDONESIA', 'BANK BCA', 'CV MAJU BERSAMA'],
                    'invalid' => ['AB', '  ', '']
                ]
            ]
        ];
    }

    /**
     * ✅ EXISTING: Get sample existing data untuk reference
     */
    private function getExistingDataSample()
    {
        try {
            $samples = CorporateCustomer::orderBy('nama')
                ->limit(5)
                ->get(['nipnas', 'nama'])
                ->map(function($customer) {
                    return [
                        'nipnas' => $customer->nipnas,
                        'nama' => $customer->nama
                    ];
                });

            return [
                'total_customers' => CorporateCustomer::count(),
                'samples' => $samples->toArray()
            ];

        } catch (\Exception $e) {
            Log::error('Get Existing Data Sample Error: ' . $e->getMessage());
            return [
                'total_customers' => 0,
                'samples' => []
            ];
        }
    }

    /**
     * ✅ EXISTING: Generate import message based on results
     */
    private function generateImportMessage($imported, $updated, $errors)
    {
        $messages = [];

        if ($imported > 0) {
            $messages[] = "{$imported} Corporate Customer baru berhasil ditambahkan";
        }

        if ($updated > 0) {
            $messages[] = "{$updated} Corporate Customer berhasil diperbarui";
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
     * ✅ EXISTING: Get statistics for dashboard
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
     * ✅ EXISTING: Get Corporate Customer data for API (alias untuk edit)
     */
    public function getCorporateCustomerData($id)
    {
        return $this->edit($id);
    }

    /**
     * ✅ EXISTING: Update Corporate Customer via API (alias untuk update)
     */
    public function updateCorporateCustomer(Request $request, $id)
    {
        return $this->update($request, $id);
    }
}