<?php

namespace App\Imports;

use App\Models\Revenue;
use App\Models\AccountManager;
use App\Models\CorporateCustomer;
use App\Models\Divisi;
use App\Models\Witel;
use App\Models\Regional;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RevenueImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures, RemembersRowNumber;

    private $importedCount = 0;
    private $duplicateCount = 0;
    private $errorCount = 0;
    private $skippedCount = 0;
    private $errorDetails = [];
    private $warningDetails = [];
    private $successDetails = [];

    // ✅ IMPROVED: Master data caching dengan lebih efisien
    private $accountManagers = [];
    private $corporateCustomers = [];
    private $divisiList = [];
    private $witelList = [];
    private $regionalList = [];

    private $year;
    private $chunkSize = 100; // ✅ Increased chunk size
    private $processedRows = 0;

    // ✅ IMPROVED: Column mapping dengan lebih fleksibel
    private $realRevenueColumns = [
        'Real_Jan', 'Real_Feb', 'Real_Mar', 'Real_Apr', 'Real_Mei', 'Real_Jun',
        'Real_Jul', 'Real_Agu', 'Real_Sep', 'Real_Okt', 'Real_Nov', 'Real_Des'
    ];

    private $targetRevenueColumns = [
        'Target_Jan', 'Target_Feb', 'Target_Mar', 'Target_Apr', 'Target_Mei', 'Target_Jun',
        'Target_Jul', 'Target_Ags', 'Target_Sep', 'Target_Okt', 'Target_Nov', 'Target_Des'
    ];

    // ✅ EXPANDED: Alternative column names dengan lebih banyak variasi
    private $alternativeColumns = [
        'nama_am' => [
            'nama am', 'nama_am', 'account_manager', 'account manager', 'NAMA AM',
            'Nama AM', 'Name AM', 'AM Name', 'am_name', 'namaAM'
        ],
        'nik' => [
            'nik', 'NIK', 'Nik', 'employee_id', 'emp_id', 'id_karyawan'
        ],
        'standard_name' => [
            'standard_name', 'standard name', 'STANDARD NAME', 'Standard Name',
            'nama customer', 'nama_customer', 'corporate customer', 'corporate_customer',
            'customer_name', 'Customer Name', 'CUSTOMER NAME', 'nama_corporate'
        ],
        'nipnas' => [
            'nipnas', 'NIPNAS', 'Nipnas', 'customer_id', 'cust_id', 'id_customer'
        ],
        'divisi' => [
            'divisi', 'DIVISI', 'Divisi', 'divisi_id', 'division', 'Division', 'DIVISION'
        ],
        'witel' => [
            'witel', 'WITEL', 'Witel', 'witel_ho', 'WITEL HO', 'Witel HO'
        ],
        'regional' => [
            'regional', 'REGIONAL', 'Regional', 'treg', 'TREG', 'Treg'
        ]
    ];

    public function __construct($year = null)
    {
        $this->year = $year ?: date('Y');
        $this->loadMasterData();

        // ✅ Set memory dan timeout untuk file besar
        ini_set('memory_limit', '2048M');
        set_time_limit(600); // 10 minutes
    }

    /**
     * ✅ IMPROVED: Load master data dengan caching yang lebih efisien
     */
    private function loadMasterData()
    {
        try {
            // Load Account Managers dengan semua relasi
            $accountManagers = AccountManager::with(['divisis', 'witel', 'regional'])->get();
            foreach ($accountManagers as $am) {
                $this->accountManagers['nama:' . $this->normalizeString($am->nama)] = $am;
                $this->accountManagers['nik:' . trim($am->nik)] = $am;
            }

            // Load Corporate Customers
            $corporateCustomers = CorporateCustomer::all();
            foreach ($corporateCustomers as $cc) {
                $this->corporateCustomers['nama:' . $this->normalizeString($cc->nama)] = $cc;
                if (!empty($cc->nipnas)) {
                    $this->corporateCustomers['nipnas:' . trim($cc->nipnas)] = $cc;
                }
            }

            // Load Divisi
            $divisiList = Divisi::all();
            foreach ($divisiList as $divisi) {
                $this->divisiList['nama:' . $this->normalizeString($divisi->nama)] = $divisi;
                $this->divisiList['id:' . $divisi->id] = $divisi;
            }

            // ✅ NEW: Load Witel dan Regional untuk validasi
            $witelList = Witel::all();
            foreach ($witelList as $witel) {
                $this->witelList['nama:' . $this->normalizeString($witel->nama)] = $witel;
            }

            $regionalList = Regional::all();
            foreach ($regionalList as $regional) {
                $this->regionalList['nama:' . $this->normalizeString($regional->nama)] = $regional;
            }

            Log::info('✅ Master data loaded successfully', [
                'account_managers' => count($accountManagers),
                'corporate_customers' => count($corporateCustomers),
                'divisi' => count($divisiList),
                'witel' => count($witelList),
                'regional' => count($regionalList)
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error loading master data: ' . $e->getMessage());
            throw new \Exception('Gagal memuat master data: ' . $e->getMessage());
        }
    }

    /**
     * ✅ NEW: Normalize string untuk konsistensi pencarian
     */
    private function normalizeString($string)
    {
        return strtolower(trim($string));
    }

    /**
     * ✅ IMPROVED: Collection processing dengan better error handling
     */
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            $this->errorDetails[] = "❌ File Excel kosong atau tidak memiliki data";
            return;
        }

        // ✅ IMPROVED: Column identification dengan validasi
        $firstRow = $rows->first();
        $columnMap = $this->identifyColumns($firstRow);

        // ✅ NEW: Validate required columns
        $this->validateRequiredColumns($columnMap);

        Log::info('📊 Starting import process', [
            'total_rows' => $rows->count(),
            'year' => $this->year,
            'columns_found' => array_keys($columnMap)
        ]);

        // Process data dengan chunking
        $rows->slice(1)->chunk($this->chunkSize)->each(function ($chunk, $chunkIndex) use ($columnMap) {
            $this->processChunk($chunk, $chunkIndex, $columnMap);
        });

        Log::info('✅ Import process completed', [
            'imported' => $this->importedCount,
            'duplicates' => $this->duplicateCount,
            'errors' => $this->errorCount,
            'skipped' => $this->skippedCount,
            'processed_rows' => $this->processedRows
        ]);
    }

    /**
     * ✅ NEW: Validate required columns ada di Excel
     */
    private function validateRequiredColumns($columnMap)
    {
        $requiredColumns = ['nama_am', 'standard_name'];
        $missingColumns = [];

        foreach ($requiredColumns as $required) {
            if (!isset($columnMap[$required])) {
                $missingColumns[] = $required;
            }
        }

        if (!empty($missingColumns)) {
            $this->errorDetails[] = "❌ Kolom wajib tidak ditemukan: " . implode(', ', $missingColumns);
            throw new \Exception('Kolom wajib tidak ditemukan: ' . implode(', ', $missingColumns));
        }

        // ✅ Check monthly columns
        $monthlyFound = 0;
        foreach (array_merge($this->realRevenueColumns, $this->targetRevenueColumns) as $monthCol) {
            if (isset($columnMap[$monthCol])) {
                $monthlyFound++;
            }
        }

        if ($monthlyFound === 0) {
            $this->warningDetails[] = "⚠️ Tidak ada kolom revenue bulanan yang ditemukan";
        }
    }

    /**
     * ✅ IMPROVED: Process chunk dengan better transaction handling
     */
    private function processChunk($chunk, $chunkIndex, $columnMap)
    {
        DB::beginTransaction();
        try {
            foreach ($chunk as $rowIndex => $row) {
                $actualRowIndex = $chunkIndex * $this->chunkSize + $rowIndex + 2; // +2 karena header dan 0-based
                $this->processedRows++;

                if ($this->isEmptyRow($row)) {
                    $this->skippedCount++;
                    continue;
                }

                $this->processRow($row, $columnMap, $actualRowIndex);
            }

            DB::commit();

            // ✅ Memory cleanup setiap chunk
            if ($chunkIndex % 10 === 0) {
                gc_collect_cycles();
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorCount++;
            $errorMsg = "❌ Error chunk {$chunkIndex}: " . $e->getMessage();
            $this->errorDetails[] = $errorMsg;
            Log::error($errorMsg, ['exception' => $e]);
        }
    }

    /**
     * ✅ IMPROVED: Process individual row dengan comprehensive validation
     */
    private function processRow($row, $columnMap, $rowNumber)
    {
        try {
            // ✅ Extract dan validate data
            $rowData = $this->extractRowData($row, $columnMap, $rowNumber);

            if (!$rowData) {
                return; // Skip jika data tidak valid
            }

            // ✅ Find entities dengan detailed error reporting
            $accountManager = $this->findAccountManager($rowData['am_name'], $rowData['nik'], $rowNumber);
            if (!$accountManager) return;

            $corporateCustomer = $this->findCorporateCustomer($rowData['cc_name'], $rowData['nipnas'], $rowNumber);
            if (!$corporateCustomer) return;

            $divisi = $this->findAndValidateDivisi($rowData['divisi_name'], $accountManager, $rowNumber);
            if (!$divisi) return;

            // ✅ Process monthly revenue
            $this->processMonthlyRevenue($row, $columnMap, $accountManager->id, $divisi->id, $corporateCustomer->id, $rowNumber);

        } catch (\Exception $e) {
            $this->errorCount++;
            $errorMsg = "❌ Baris {$rowNumber}: " . $e->getMessage();
            $this->errorDetails[] = $errorMsg;
            Log::error($errorMsg, ['exception' => $e]);
        }
    }

    /**
     * ✅ NEW: Extract row data dengan validation
     */
    private function extractRowData($row, $columnMap, $rowNumber)
    {
        $data = [
            'am_name' => $this->extractValue($row, $columnMap, 'nama_am'),
            'nik' => $this->extractValue($row, $columnMap, 'nik'),
            'cc_name' => $this->extractValue($row, $columnMap, 'standard_name'),
            'nipnas' => $this->extractValue($row, $columnMap, 'nipnas'),
            'divisi_name' => $this->extractValue($row, $columnMap, 'divisi'),
            'witel_name' => $this->extractValue($row, $columnMap, 'witel'),
            'regional_name' => $this->extractValue($row, $columnMap, 'regional')
        ];

        // ✅ Validate minimal required data
        if (empty($data['am_name']) && empty($data['nik'])) {
            $this->errorDetails[] = "❌ Baris {$rowNumber}: Account Manager kosong (nama dan NIK)";
            return null;
        }

        if (empty($data['cc_name']) && empty($data['nipnas'])) {
            $this->errorDetails[] = "❌ Baris {$rowNumber}: Corporate Customer kosong (nama dan NIPNAS)";
            return null;
        }

        return $data;
    }

    /**
     * ✅ IMPROVED: Find Account Manager dengan better error messaging
     */
    private function findAccountManager($name, $nik, $rowNumber)
    {
        $accountManager = null;

        // Try by name first
        if (!empty($name)) {
            $nameKey = 'nama:' . $this->normalizeString($name);
            $accountManager = $this->accountManagers[$nameKey] ?? null;
        }

        // Try by NIK if name not found
        if (!$accountManager && !empty($nik)) {
            $nikKey = 'nik:' . trim($nik);
            $accountManager = $this->accountManagers[$nikKey] ?? null;
        }

        // ✅ Fallback to database dengan fuzzy matching
        if (!$accountManager && !empty($name)) {
            $accountManager = AccountManager::with(['divisis', 'witel', 'regional'])
                ->where('nama', 'like', "%{$name}%")
                ->first();

            if ($accountManager) {
                // Add to cache
                $this->accountManagers['nama:' . $this->normalizeString($accountManager->nama)] = $accountManager;
                $this->accountManagers['nik:' . trim($accountManager->nik)] = $accountManager;
                $this->warningDetails[] = "⚠️ Baris {$rowNumber}: AM ditemukan dengan fuzzy search: '{$name}' → '{$accountManager->nama}'";
            }
        }

        if (!$accountManager) {
            $this->errorCount++;
            $this->errorDetails[] = "❌ Baris {$rowNumber}: Account Manager tidak ditemukan - Nama: '{$name}', NIK: '{$nik}'";
            return null;
        }

        return $accountManager;
    }

    /**
     * ✅ IMPROVED: Find Corporate Customer dengan better error messaging
     */
    private function findCorporateCustomer($name, $nipnas, $rowNumber)
    {
        $corporateCustomer = null;

        // Try by name first
        if (!empty($name)) {
            $nameKey = 'nama:' . $this->normalizeString($name);
            $corporateCustomer = $this->corporateCustomers[$nameKey] ?? null;
        }

        // Try by NIPNAS if name not found
        if (!$corporateCustomer && !empty($nipnas)) {
            $nipnasKey = 'nipnas:' . trim($nipnas);
            $corporateCustomer = $this->corporateCustomers[$nipnasKey] ?? null;
        }

        // ✅ Fallback to database dengan fuzzy matching
        if (!$corporateCustomer && !empty($name)) {
            $corporateCustomer = CorporateCustomer::where('nama', 'like', "%{$name}%")->first();

            if ($corporateCustomer) {
                // Add to cache
                $this->corporateCustomers['nama:' . $this->normalizeString($corporateCustomer->nama)] = $corporateCustomer;
                if (!empty($corporateCustomer->nipnas)) {
                    $this->corporateCustomers['nipnas:' . trim($corporateCustomer->nipnas)] = $corporateCustomer;
                }
                $this->warningDetails[] = "⚠️ Baris {$rowNumber}: CC ditemukan dengan fuzzy search: '{$name}' → '{$corporateCustomer->nama}'";
            }
        }

        if (!$corporateCustomer) {
            $this->errorCount++;
            $this->errorDetails[] = "❌ Baris {$rowNumber}: Corporate Customer tidak ditemukan - Nama: '{$name}', NIPNAS: '{$nipnas}'";
            return null;
        }

        return $corporateCustomer;
    }

    /**
     * ✅ IMPROVED: Find dan validate divisi dengan AM
     */
    private function findAndValidateDivisi($divisiName, $accountManager, $rowNumber)
    {
        $divisi = null;

        // If divisi specified in Excel, find it
        if (!empty($divisiName)) {
            $divisiKey = 'nama:' . $this->normalizeString($divisiName);
            $divisi = $this->divisiList[$divisiKey] ?? null;

            // ✅ Fallback fuzzy search
            if (!$divisi) {
                $divisi = Divisi::where('nama', 'like', "%{$divisiName}%")->first();
                if ($divisi) {
                    $this->divisiList['nama:' . $this->normalizeString($divisi->nama)] = $divisi;
                    $this->warningDetails[] = "⚠️ Baris {$rowNumber}: Divisi ditemukan dengan fuzzy search: '{$divisiName}' → '{$divisi->nama}'";
                }
            }

            // ✅ Validate divisi terkait dengan AM
            if ($divisi) {
                $isDivisiLinked = $accountManager->divisis()->where('divisi.id', $divisi->id)->exists();
                if (!$isDivisiLinked) {
                    $this->warningDetails[] = "⚠️ Baris {$rowNumber}: Divisi '{$divisi->nama}' tidak terkait dengan AM '{$accountManager->nama}', menggunakan divisi pertama AM";
                    $divisi = null; // Reset untuk menggunakan divisi pertama AM
                }
            }
        }

        // ✅ Use first divisi dari AM jika tidak ada divisi spesifik
        if (!$divisi) {
            $divisi = $accountManager->divisis()->first();
            if (!$divisi) {
                $this->errorCount++;
                $this->errorDetails[] = "❌ Baris {$rowNumber}: Account Manager '{$accountManager->nama}' tidak memiliki divisi terkait";
                return null;
            }

            if (empty($divisiName)) {
                $this->warningDetails[] = "ℹ️ Baris {$rowNumber}: Menggunakan divisi pertama dari AM: '{$divisi->nama}'";
            }
        }

        return $divisi;
    }

    /**
     * ✅ IMPROVED: Process monthly revenue dengan better validation
     */
    private function processMonthlyRevenue($row, $columnMap, $accountManagerId, $divisiId, $corporateCustomerId, $rowNumber)
    {
        $monthMapping = [
            1 => ['real' => 'Real_Jan', 'target' => 'Target_Jan'],
            2 => ['real' => 'Real_Feb', 'target' => 'Target_Feb'],
            3 => ['real' => 'Real_Mar', 'target' => 'Target_Mar'],
            4 => ['real' => 'Real_Apr', 'target' => 'Target_Apr'],
            5 => ['real' => 'Real_Mei', 'target' => 'Target_Mei'],
            6 => ['real' => 'Real_Jun', 'target' => 'Target_Jun'],
            7 => ['real' => 'Real_Jul', 'target' => 'Target_Jul'],
            8 => ['real' => 'Real_Agu', 'target' => 'Target_Ags'],
            9 => ['real' => 'Real_Sep', 'target' => 'Target_Sep'],
            10 => ['real' => 'Real_Okt', 'target' => 'Target_Okt'],
            11 => ['real' => 'Real_Nov', 'target' => 'Target_Nov'],
            12 => ['real' => 'Real_Des', 'target' => 'Target_Des'],
        ];

        $monthlyDataFound = false;

        for ($month = 1; $month <= 12; $month++) {
            $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);
            $bulan = $this->year . '-' . $monthFormatted . '-01';

            $realColumn = $monthMapping[$month]['real'];
            $targetColumn = $monthMapping[$month]['target'];

            $realRevenue = 0;
            $targetRevenue = 0;

            // Extract values
            if (isset($columnMap[$realColumn])) {
                $realRevenue = $this->parseNumericValue($row[$columnMap[$realColumn]] ?? 0);
            }

            if (isset($columnMap[$targetColumn])) {
                $targetRevenue = $this->parseNumericValue($row[$columnMap[$targetColumn]] ?? 0);
            }

            // Skip jika kedua nilai kosong
            if ($realRevenue == 0 && $targetRevenue == 0) {
                continue;
            }

            $monthlyDataFound = true;

            try {
                $revenue = Revenue::updateOrCreate(
                    [
                        'account_manager_id' => $accountManagerId,
                        'divisi_id' => $divisiId,
                        'corporate_customer_id' => $corporateCustomerId,
                        'bulan' => $bulan
                    ],
                    [
                        'target_revenue' => $targetRevenue,
                        'real_revenue' => $realRevenue
                    ]
                );

                if ($revenue->wasRecentlyCreated) {
                    $this->importedCount++;
                } else {
                    $this->duplicateCount++;
                }

            } catch (\Exception $e) {
                $this->errorCount++;
                $this->errorDetails[] = "❌ Baris {$rowNumber}, Bulan {$month}: Gagal menyimpan revenue - " . $e->getMessage();
                throw $e;
            }
        }

        // ✅ Log jika tidak ada data monthly ditemukan
        if (!$monthlyDataFound) {
            $this->warningDetails[] = "⚠️ Baris {$rowNumber}: Tidak ada data revenue bulanan ditemukan";
        }
    }

    /**
     * ✅ IMPROVED: Column identification dengan better matching
     */
    private function identifyColumns($firstRow)
    {
        $map = [];
        $excelColumns = array_keys($firstRow->toArray());

        // ✅ Exact match untuk revenue columns
        foreach (array_merge($this->realRevenueColumns, $this->targetRevenueColumns) as $monthCol) {
            if (in_array($monthCol, $excelColumns)) {
                $map[$monthCol] = $monthCol;
            }
        }

        // ✅ Flexible matching untuk kolom lainnya
        foreach ($this->alternativeColumns as $standardKey => $alternatives) {
            foreach ($alternatives as $altName) {
                // Case-insensitive matching
                $foundColumn = collect($excelColumns)->first(function ($col) use ($altName) {
                    return strtolower(trim($col)) === strtolower(trim($altName));
                });

                if ($foundColumn) {
                    $map[$standardKey] = $foundColumn;
                    break;
                }
            }
        }

        Log::info('📋 Column mapping result', $map);
        return $map;
    }

    /**
     * Extract value dari row
     */
    private function extractValue($row, $columnMap, $field)
    {
        $key = $columnMap[$field] ?? null;
        if ($key && isset($row[$key])) {
            return trim((string)$row[$key]);
        }
        return null;
    }

    /**
     * Check if row is empty
     */
    private function isEmptyRow($row)
    {
        foreach ($row as $value) {
            if (!empty($value) && $value !== null && trim($value) !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * ✅ IMPROVED: Parse numeric value dengan better format handling
     */
    private function parseNumericValue($value)
    {
        if (empty($value) || $value === null) return 0;

        if (is_numeric($value)) {
            return (float)$value;
        }

        // ✅ Handle berbagai format angka Indonesia/International
        $cleaned = preg_replace('/[^\d,.-]/', '', trim($value));

        // Handle comma as thousand separator vs decimal separator
        if (substr_count($cleaned, ',') == 1 && substr_count($cleaned, '.') == 0) {
            // Likely decimal comma (European format)
            $cleaned = str_replace(',', '.', $cleaned);
        } else {
            // Remove comma as thousand separator
            $cleaned = str_replace(',', '', $cleaned);
        }

        return is_numeric($cleaned) ? (float)$cleaned : 0;
    }

    /**
     * Rules validasi (kosong karena validasi manual)
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * ✅ IMPROVED: Get comprehensive import results
     */
    public function getImportResults()
    {
        return [
            'imported' => $this->importedCount,
            'duplicates' => $this->duplicateCount,
            'errors' => $this->errorCount,
            'skipped' => $this->skippedCount,
            'processed' => $this->processedRows,
            'error_details' => $this->errorDetails,
            'warning_details' => $this->warningDetails,
            'success_details' => $this->successDetails,
            'summary' => [
                'total_processed' => $this->processedRows,
                'success_rate' => $this->processedRows > 0 ? round(($this->importedCount + $this->duplicateCount) / $this->processedRows * 100, 2) : 0,
                'error_rate' => $this->processedRows > 0 ? round($this->errorCount / $this->processedRows * 100, 2) : 0
            ]
        ];
    }
}