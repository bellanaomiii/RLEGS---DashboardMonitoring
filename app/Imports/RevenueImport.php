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
    private $updatedCount = 0;
    private $duplicateCount = 0;
    private $errorCount = 0;
    private $skippedCount = 0;
    private $conflictCount = 0; // ✅ NEW: Track conflicts

    private $errorDetails = [];
    private $warningDetails = [];
    private $successDetails = [];
    private $conflictDetails = []; // ✅ NEW: Track conflict details

    // ✅ IMPROVED: Master data caching dengan lebih efisien
    private $accountManagers = [];
    private $corporateCustomers = [];
    private $divisiList = [];
    private $witelList = [];
    private $regionalList = [];

    private $year;
    private $overwriteMode; // ✅ NEW: Overwrite mode (update, skip, ask)
    private $chunkSize = 50;
    private $processedRows = 0;

    // ✅ IMPROVED: Monthly pairs dengan better tracking
    private $monthlyPairs = [];
    private $detectedColumns = [];

    // ✅ NEW: Conflict tracking
    private $existingDataCache = []; // Cache untuk mengetahui data yang sudah ada

    // ✅ EXPANDED: Alternative column names dengan lebih banyak variasi
    private $alternativeColumns = [
        'nama_am' => [
            'nama am', 'nama_am', 'account_manager', 'account manager', 'NAMA AM',
            'Nama AM', 'Name AM', 'AM Name', 'am_name', 'namaAM', 'nama account manager',
            'account_manager_name', 'AM_NAME', 'nama_account_manager'
        ],
        'nik' => [
            'nik', 'NIK', 'Nik', 'employee_id', 'emp_id', 'id_karyawan', 'NIK_AM',
            'nik_am', 'employee_number', 'emp_number'
        ],
        'standard_name' => [
            'standard_name', 'standard name', 'STANDARD NAME', 'Standard Name',
            'nama customer', 'nama_customer', 'corporate customer', 'corporate_customer',
            'customer_name', 'Customer Name', 'CUSTOMER NAME', 'nama_corporate',
            'nama', 'NAMA', 'Nama', 'name', 'Name', 'NAME', 'company_name',
            'Company Name', 'COMPANY NAME', 'company', 'Company', 'COMPANY',
            'customer', 'Customer', 'CUSTOMER'
        ],
        'nipnas' => [
            'nipnas', 'NIPNAS', 'Nipnas', 'customer_id', 'cust_id', 'id_customer',
            'CUSTOMER_ID', 'CUST_ID', 'ID_CUSTOMER', 'customer code', 'CUSTOMER CODE',
            'customer_code', 'code', 'Code', 'CODE', 'CUSTOMER_CODE'
        ],
        'divisi' => [
            'divisi', 'DIVISI', 'Divisi', 'divisi_id', 'division', 'Division', 'DIVISION',
            'nama_divisi', 'NAMA_DIVISI', 'nama divisi', 'NAMA DIVISI'
        ],
        'witel' => [
            'witel', 'WITEL', 'Witel', 'witel_ho', 'WITEL HO', 'Witel HO',
            'witel_name', 'WITEL_NAME', 'nama_witel', 'NAMA_WITEL'
        ],
        'regional' => [
            'regional', 'REGIONAL', 'Regional', 'treg', 'TREG', 'Treg',
            'nama_regional', 'NAMA_REGIONAL', 'regional_name', 'REGIONAL_NAME'
        ]
    ];

    /**
 * ✅ FIX 8: Safe property initialization in constructor
 * 🎯 Root Cause: Uninitialized arrays causing issues
 */
public function __construct($year = null, $overwriteMode = 'update')
{
    // ✅ FIX: Ensure all arrays are properly initialized
    $this->errorDetails = [];
    $this->warningDetails = [];
    $this->successDetails = [];
    $this->conflictDetails = [];
    $this->accountManagers = [];
    $this->corporateCustomers = [];
    $this->divisiList = [];
    $this->witelList = [];
    $this->regionalList = [];
    $this->monthlyPairs = [];
    $this->detectedColumns = [];
    $this->existingDataCache = [];

    // Initialize counters
    $this->importedCount = 0;
    $this->updatedCount = 0;
    $this->duplicateCount = 0;
    $this->errorCount = 0;
    $this->skippedCount = 0;
    $this->conflictCount = 0;
    $this->processedRows = 0;

    // Smart year detection
    if ($year) {
        if (is_numeric($year) && $year >= 2020 && $year <= 2030) {
            $this->year = (int)$year;
        } else {
            Log::warning("Invalid year provided: {$year}, using current year");
            $this->year = (int)date('Y');
        }
    } else {
        $this->year = (int)date('Y');
    }

    // Set overwrite mode
    $this->overwriteMode = in_array($overwriteMode, ['update', 'skip', 'ask']) ? $overwriteMode : 'update';

    Log::info("RevenueImport initialized", [
        'year' => $this->year,
        'overwrite_mode' => $this->overwriteMode
    ]);

    try {
        $this->loadMasterData();
        $this->loadExistingDataCache();
    } catch (\Exception $e) {
        Log::error("Failed to initialize RevenueImport: " . $e->getMessage());
        throw $e;
    }

    // Set memory dan timeout untuk file besar
    ini_set('memory_limit', '2048M');
    set_time_limit(600);
}

    /**
     * ✅ NEW: Load existing revenue data for conflict detection
     */
    private function loadExistingDataCache()
    {
        try {
            // Load existing revenue data untuk year ini
            $existingRevenues = Revenue::whereYear('bulan', $this->year)
                ->with(['accountManager', 'corporateCustomer', 'divisi'])
                ->get();

            foreach ($existingRevenues as $revenue) {
                $key = sprintf('%d_%d_%d_%s',
                    $revenue->account_manager_id,
                    $revenue->corporate_customer_id,
                    $revenue->divisi_id,
                    $revenue->bulan
                );

                $this->existingDataCache[$key] = [
                    'id' => $revenue->id,
                    'target_revenue' => $revenue->target_revenue,
                    'real_revenue' => $revenue->real_revenue,
                    'account_manager' => $revenue->accountManager->nama ?? 'Unknown',
                    'corporate_customer' => $revenue->corporateCustomer->nama ?? 'Unknown',
                    'divisi' => $revenue->divisi->nama ?? 'Unknown',
                    'bulan' => $revenue->bulan,
                    'created_at' => $revenue->created_at
                ];
            }

            Log::info('✅ Existing revenue data cache loaded', [
                'year' => $this->year,
                'existing_records' => count($existingRevenues)
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error loading existing data cache: ' . $e->getMessage());
            $this->existingDataCache = [];
        }
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
                $normalizedName = $this->normalizeString($am->nama);
                $this->accountManagers['nama:' . $normalizedName] = $am;
                $this->accountManagers['nik:' . trim($am->nik)] = $am;
                $this->accountManagers['id:' . $am->id] = $am;
            }

            // Load Corporate Customers
            $corporateCustomers = CorporateCustomer::all();
            foreach ($corporateCustomers as $cc) {
                $normalizedName = $this->normalizeString($cc->nama);
                $this->corporateCustomers['nama:' . $normalizedName] = $cc;
                if (!empty($cc->nipnas)) {
                    $this->corporateCustomers['nipnas:' . trim($cc->nipnas)] = $cc;
                }
                $this->corporateCustomers['id:' . $cc->id] = $cc;
            }

            // Load Divisi
            $divisiList = Divisi::all();
            foreach ($divisiList as $divisi) {
                $normalizedName = $this->normalizeString($divisi->nama);
                $this->divisiList['nama:' . $normalizedName] = $divisi;
                $this->divisiList['id:' . $divisi->id] = $divisi;
            }

            // Load Witel dan Regional untuk validasi
            $witelList = Witel::all();
            foreach ($witelList as $witel) {
                $normalizedName = $this->normalizeString($witel->nama);
                $this->witelList['nama:' . $normalizedName] = $witel;
                $this->witelList['id:' . $witel->id] = $witel;
            }

            $regionalList = Regional::all();
            foreach ($regionalList as $regional) {
                $normalizedName = $this->normalizeString($regional->nama);
                $this->regionalList['nama:' . $normalizedName] = $regional;
                $this->regionalList['id:' . $regional->id] = $regional;
            }

            Log::info('✅ Master data loaded successfully for Revenue Import', [
                'year' => $this->year,
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
     * ✅ IMPROVED: Normalize string untuk konsistensi pencarian
     */
    private function normalizeString($string)
    {
        if (empty($string)) return '';

        $normalized = strtolower(trim($string));
        // Remove extra spaces
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        // Remove special characters that might cause issues
        $normalized = preg_replace('/[^\w\s-]/', '', $normalized);

        return $normalized;
    }

    /**
     * ✅ IMPROVED: Collection processing dengan better error handling
     */
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            $this->errorDetails[] = "❌ File Excel kosong atau tidak memiliki data";
            $this->errorCount++;
            return;
        }

        try {
            // ✅ IMPROVED: Column identification dengan validasi
            $firstRow = $rows->first();
            $this->detectedColumns = array_keys($firstRow->toArray());
            $columnMap = $this->identifyColumns($firstRow);

            // ✅ ENHANCED: Detect monthly column pairs dengan better logic
            $this->monthlyPairs = $this->detectMonthlyColumns($this->detectedColumns);

            // ✅ NEW: Validate required columns
            $this->validateRequiredColumns($columnMap);

            Log::info('📊 Starting Revenue import process', [
                'total_rows' => $rows->count(),
                'year' => $this->year,
                'overwrite_mode' => $this->overwriteMode,
                'columns_found' => array_keys($columnMap),
                'detected_columns' => $this->detectedColumns,
                'monthly_pairs' => count($this->monthlyPairs)
            ]);

            // ✅ IMPROVED: Process data dengan chunking yang lebih smart
            $dataRows = $rows->slice(1); // Skip header row

            if ($dataRows->isEmpty()) {
                $this->warningDetails[] = "⚠️ File hanya berisi header, tidak ada data untuk diproses";
                return;
            }

            $dataRows->chunk($this->chunkSize)->each(function ($chunk, $chunkIndex) use ($columnMap) {
                $this->processChunk($chunk, $chunkIndex, $columnMap);
            });

            Log::info('✅ Revenue import process completed', [
                'year' => $this->year,
                'overwrite_mode' => $this->overwriteMode,
                'imported' => $this->importedCount,
                'updated' => $this->updatedCount,
                'duplicates' => $this->duplicateCount,
                'conflicts' => $this->conflictCount,
                'errors' => $this->errorCount,
                'skipped' => $this->skippedCount,
                'processed_rows' => $this->processedRows
            ]);

        } catch (\Exception $e) {
            $this->errorCount++;
            $this->errorDetails[] = "❌ Error processing file: " . $e->getMessage();
            Log::error('Revenue Import Processing Error: ' . $e->getMessage());
        }
    }

    /**
     * ✅ ENHANCED: Detect monthly column pairs dengan lebih fleksibel
     */
    private function detectMonthlyColumns($headers)
    {
        $monthlyPairs = [];

        // ✅ EXPANDED: Month variations yang bisa muncul di Excel (Indonesia + English)
        $monthVariations = [
            1 => ['JAN', 'JANUARI', 'JANUARY', '01', 'JANUARY', 'Jan'],
            2 => ['FEB', 'FEBRUARI', 'FEBRUARY', '02', 'Feb'],
            3 => ['MAR', 'MARET', 'MARCH', '03', 'Mar'],
            4 => ['APR', 'APRIL', '04', 'Apr'],
            5 => ['MEI', 'MAY', '05', 'Mei'],
            6 => ['JUN', 'JUNI', 'JUNE', '06', 'Jun'],
            7 => ['JUL', 'JULI', 'JULY', '07', 'Jul'],
            8 => ['AGU', 'AGS', 'AGUSTUS', 'AUGUST', '08', 'Aug'],
            9 => ['SEP', 'SEPTEMBER', '09', 'Sep'],
            10 => ['OKT', 'OKTOBER', 'OCTOBER', '10', 'Oct'],
            11 => ['NOV', 'NOVEMBER', '11', 'Nov'],
            12 => ['DES', 'DESEMBER', 'DECEMBER', 'DEC', '12', 'Dec']
        ];

        // ✅ ENHANCED: Look for Real-Target pairs dengan pattern yang lebih fleksibel
        foreach ($monthVariations as $monthNum => $monthNames) {
            $realColumn = null;
            $targetColumn = null;

            // ✅ IMPROVED: Try multiple patterns untuk Real dan Target
            foreach ($headers as $header) {
                $normalizedHeader = strtoupper(trim($header));

                foreach ($monthNames as $monthName) {
                    // Pattern untuk Real columns
                    $realPatterns = [
                        '/^REAL[_\s]*' . $monthName . '$/i',
                        '/^' . $monthName . '[_\s]*REAL$/i',
                        '/^REVENUE[_\s]*' . $monthName . '$/i',
                        '/^' . $monthName . '[_\s]*REVENUE$/i',
                        '/^ACTUAL[_\s]*' . $monthName . '$/i',
                        '/^' . $monthName . '[_\s]*ACTUAL$/i'
                    ];

                    // Pattern untuk Target columns
                    $targetPatterns = [
                        '/^TARGET[_\s]*' . $monthName . '$/i',
                        '/^' . $monthName . '[_\s]*TARGET$/i',
                        '/^BUDGET[_\s]*' . $monthName . '$/i',
                        '/^' . $monthName . '[_\s]*BUDGET$/i',
                        '/^PLAN[_\s]*' . $monthName . '$/i',
                        '/^' . $monthName . '[_\s]*PLAN$/i'
                    ];

                    // Check Real patterns
                    foreach ($realPatterns as $pattern) {
                        if (preg_match($pattern, $normalizedHeader)) {
                            $realColumn = $header;
                            break 2;
                        }
                    }

                    // Check Target patterns
                    foreach ($targetPatterns as $pattern) {
                        if (preg_match($pattern, $normalizedHeader)) {
                            $targetColumn = $header;
                            break 2;
                        }
                    }
                }
            }

            // ✅ FLEXIBLE: Add pair even if only one column found (with warning)
            if ($realColumn !== null || $targetColumn !== null) {
                $monthlyPairs[] = [
                    'month' => $monthNum,
                    'month_name' => $monthNames[0],
                    'real_column' => $realColumn,
                    'target_column' => $targetColumn
                ];

                // ✅ WARNING: Log if only one column found
                if ($realColumn === null) {
                    $this->warningDetails[] = "⚠️ Bulan {$monthNames[0]}: Hanya ditemukan kolom Target, kolom Real tidak ada";
                }
                if ($targetColumn === null) {
                    $this->warningDetails[] = "⚠️ Bulan {$monthNames[0]}: Hanya ditemukan kolom Real, kolom Target tidak ada";
                }
            }
        }

        // ✅ Sort by month number
        usort($monthlyPairs, function($a, $b) {
            return $a['month'] - $b['month'];
        });

        Log::info('📅 Monthly column pairs detected', [
            'year' => $this->year,
            'pairs_found' => count($monthlyPairs),
            'pairs' => array_map(function($pair) {
                $real = $pair['real_column'] ?? 'N/A';
                $target = $pair['target_column'] ?? 'N/A';
                return $pair['month_name'] . ': Real=' . $real . ', Target=' . $target;
            }, $monthlyPairs)
        ]);

        return $monthlyPairs;
    }

    /**
     * ✅ ENHANCED: Validate required columns ada di Excel
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
            $error = "❌ Kolom wajib tidak ditemukan: " . implode(', ', $missingColumns);
            $this->errorDetails[] = $error;
            $this->errorCount++;
            throw new \Exception($error);
        }

        // ✅ ENHANCED: Check monthly pairs dengan warning yang lebih informatif
        if (empty($this->monthlyPairs)) {
            $warning = "⚠️ Tidak ditemukan pasangan kolom Real-Target bulanan yang valid";
            $this->warningDetails[] = $warning;
            $this->warningDetails[] = sprintf("Kolom yang terdeteksi: %s", implode(', ', $this->detectedColumns));
            $this->warningDetails[] = "Format yang diharapkan: Real_Jan + Target_Jan, Real_February + Target_February, dll.";
            throw new \Exception('Tidak ada pasangan Real-Target bulanan ditemukan. Periksa format kolom bulan dalam file Excel.');
        }

        // ✅ INFO: Log successful validation
        $this->successDetails[] = sprintf("✅ Validasi berhasil: %d kolom wajib dan %d pasangan bulan ditemukan",
            count($requiredColumns), count($this->monthlyPairs));
    }

    /**
     * ✅ IMPROVED: Process chunk dengan better transaction handling dan error recovery
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
            if ($chunkIndex % 5 === 0) {
                gc_collect_cycles();
                Log::info("Memory cleanup after chunk {$chunkIndex}, processed rows: {$this->processedRows}");
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

            // ✅ Process monthly revenue dengan flexible pairs dan conflict resolution
            $processedMonths = $this->processMonthlyRevenue($row, $accountManager->id, $divisi->id, $corporateCustomer->id, $rowNumber);

            if ($processedMonths > 0) {
                $this->successDetails[] = "✅ Baris {$rowNumber}: Berhasil memproses {$processedMonths} bulan untuk '{$accountManager->nama}' - '{$corporateCustomer->nama}'";
            } else {
                $this->warningDetails[] = "⚠️ Baris {$rowNumber}: Tidak ada data revenue bulanan yang diproses";
            }

        } catch (\Exception $e) {
            $this->errorCount++;
            $errorMsg = "❌ Baris {$rowNumber}: " . $e->getMessage();
            $this->errorDetails[] = $errorMsg;
            Log::error($errorMsg, ['exception' => $e]);
        }
    }

    /**
 * ✅ FIX 5: processMonthlyRevenue() - Enhanced error handling
 * 🎯 Root Cause: Cache key generation and array access issues
 */
private function processMonthlyRevenue($row, $accountManagerId, $divisiId, $corporateCustomerId, $rowNumber)
{
    $monthlyDataFound = 0;
    $processedMonths = 0;

    foreach ($this->monthlyPairs as $monthPair) {
        $month = $monthPair['month'];
        $realColumn = $monthPair['real_column'];
        $targetColumn = $monthPair['target_column'];

        // ✅ FIX: Safe array access for Excel data
        $realRevenue = 0;
        $targetRevenue = 0;

        if ($realColumn && isset($row[$realColumn])) {
            $realRevenue = $this->parseNumericValue($row[$realColumn]);
        }

        if ($targetColumn && isset($row[$targetColumn])) {
            $targetRevenue = $this->parseNumericValue($row[$targetColumn]);
        }

        if ($realRevenue == 0 && $targetRevenue == 0) {
            continue;
        }

        $monthlyDataFound++;
        $bulan = sprintf('%s-%02d-01', $this->year, $month);

        try {
            // ✅ FIX: Safe cache key generation
            $cacheKey = sprintf('%s_%s_%s_%s', 
                (string)$accountManagerId, 
                (string)$corporateCustomerId, 
                (string)$divisiId, 
                (string)$bulan
            );
            
            $existingData = isset($this->existingDataCache[$cacheKey]) ? $this->existingDataCache[$cacheKey] : null;

            if ($existingData) {
                $conflictResult = $this->handleExistingDataConflict(
                    $existingData,
                    $targetRevenue,
                    $realRevenue,
                    $monthPair['month_name'],
                    $rowNumber
                );

                if ($conflictResult['action'] === 'updated') {
                    $this->updatedCount++;
                    $processedMonths++;
                } elseif ($conflictResult['action'] === 'skipped') {
                    $this->skippedCount++;
                } elseif ($conflictResult['action'] === 'duplicate') {
                    $this->duplicateCount++;
                }

            } else {
                // Create new revenue record
                $newRevenue = Revenue::create([
                    'account_manager_id' => $accountManagerId,
                    'corporate_customer_id' => $corporateCustomerId,
                    'divisi_id' => $divisiId,
                    'target_revenue' => $targetRevenue,
                    'real_revenue' => $realRevenue,
                    'bulan' => $bulan,
                ]);

                // ✅ FIX: Safe cache update
                $this->existingDataCache[$cacheKey] = [
                    'id' => $newRevenue->id,
                    'target_revenue' => $targetRevenue,
                    'real_revenue' => $realRevenue,
                    'bulan' => $bulan,
                    'account_manager' => '', // Will be filled if needed
                    'corporate_customer' => '',
                    'divisi' => '',
                    'created_at' => $newRevenue->created_at
                ];

                $this->importedCount++;
                $processedMonths++;
                $this->successDetails[] = "✅ Baris {$rowNumber}, {$monthPair['month_name']}: Data baru dibuat - Target: " . number_format($targetRevenue) . ", Real: " . number_format($realRevenue);
            }

        } catch (\Exception $e) {
            $this->errorCount++;
            $this->errorDetails[] = "❌ Baris {$rowNumber}, Bulan {$monthPair['month_name']}: Gagal menyimpan revenue - " . $e->getMessage();
            Log::error("Revenue save error", [
                'row' => $rowNumber,
                'month' => $monthPair['month_name'],
                'error' => $e->getMessage(),
                'account_manager_id' => $accountManagerId,
                'corporate_customer_id' => $corporateCustomerId,
                'divisi_id' => $divisiId,
                'target' => $targetRevenue,
                'real' => $realRevenue
            ]);
            continue; // Continue with next month instead of throwing
        }
    }

    if ($monthlyDataFound === 0) {
        $this->warningDetails[] = "⚠️ Baris {$rowNumber}: Tidak ada data revenue bulanan ditemukan";
    }

    return $processedMonths;
}
    /**
 * ✅ FIX 4: handleExistingDataConflict() - Safe array access
 * 🎯 Root Cause: Array access without proper validation
 */
private function handleExistingDataConflict($existingData, $newTargetRevenue, $newRealRevenue, $monthName, $rowNumber)
{
    $hasChanges = false;
    $changes = [];

    // ✅ FIX: Safe array access with isset checks
    $existingTarget = isset($existingData['target_revenue']) ? $existingData['target_revenue'] : 0;
    $existingReal = isset($existingData['real_revenue']) ? $existingData['real_revenue'] : 0;

    // CHECK for actual changes
    if ($existingTarget != $newTargetRevenue) {
        $changes[] = "Target: " . number_format($existingTarget) . " → " . number_format($newTargetRevenue);
        $hasChanges = true;
    }

    if ($existingReal != $newRealRevenue) {
        $changes[] = "Real: " . number_format($existingReal) . " → " . number_format($newRealRevenue);
        $hasChanges = true;
    }

    // NO CHANGES: Data identical
    if (!$hasChanges) {
        $this->warningDetails[] = "⚠️ Baris {$rowNumber}, {$monthName}: Data sama, tidak ada perubahan";
        return ['action' => 'duplicate', 'changes' => []];
    }

    // CONFLICT RESOLUTION berdasarkan mode
    switch ($this->overwriteMode) {
        case 'skip':
            $this->conflictCount++;
            $this->conflictDetails[] = [
                'row' => $rowNumber,
                'month' => $monthName,
                'action' => 'skipped',
                'reason' => 'Data sudah ada (mode: skip)',
                'existing' => [
                    'target' => $existingTarget,
                    'real' => $existingReal
                ],
                'new' => [
                    'target' => $newTargetRevenue,
                    'real' => $newRealRevenue
                ],
                'changes' => $changes
            ];

            $this->warningDetails[] = "⚠️ Baris {$rowNumber}, {$monthName}: Dilewati (mode skip) - " . implode(', ', $changes);
            return ['action' => 'skipped', 'changes' => $changes];

        case 'ask':
            $this->conflictCount++;
            $this->conflictDetails[] = [
                'row' => $rowNumber,
                'month' => $monthName,
                'action' => 'needs_confirmation',
                'reason' => 'Data sudah ada (butuh konfirmasi)',
                'existing' => [
                    'target' => $existingTarget,
                    'real' => $existingReal,
                    'created_at' => isset($existingData['created_at']) ? $existingData['created_at'] : null
                ],
                'new' => [
                    'target' => $newTargetRevenue,
                    'real' => $newRealRevenue
                ],
                'changes' => $changes
            ];

            // ✅ FIX: Safe ID access
            $existingId = isset($existingData['id']) ? $existingData['id'] : null;
            if ($existingId) {
                $this->updateExistingRevenue($existingId, $newTargetRevenue, $newRealRevenue);
            }
            
            $this->warningDetails[] = "🔄 Baris {$rowNumber}, {$monthName}: Diperbarui (butuh konfirmasi) - " . implode(', ', $changes);
            return ['action' => 'updated', 'changes' => $changes];

        case 'update':
        default:
            $this->conflictCount++;
            $this->conflictDetails[] = [
                'row' => $rowNumber,
                'month' => $monthName,
                'action' => 'updated',
                'reason' => 'Data diperbarui otomatis',
                'existing' => [
                    'target' => $existingTarget,
                    'real' => $existingReal
                ],
                'new' => [
                    'target' => $newTargetRevenue,
                    'real' => $newRealRevenue
                ],
                'changes' => $changes
            ];

            // ✅ FIX: Safe ID access
            $existingId = isset($existingData['id']) ? $existingData['id'] : null;
            if ($existingId) {
                $this->updateExistingRevenue($existingId, $newTargetRevenue, $newRealRevenue);
            }
            
            $this->successDetails[] = "✅ Baris {$rowNumber}, {$monthName}: Diperbarui - " . implode(', ', $changes);
            return ['action' => 'updated', 'changes' => $changes];
    }
}

    /**
     * ✅ NEW: Update existing revenue record
     */
    /**
     * ✅ FIX 1: updateExistingRevenue() - Ensure safe array access
     * 🎯 Root Cause: $this->existingDataCache[$key] might be accessed incorrectly
     */
    private function updateExistingRevenue($revenueId, $targetRevenue, $realRevenue)
    {
        try {
            $revenue = Revenue::findOrFail($revenueId);
            $revenue->update([
                'target_revenue' => $targetRevenue,
                'real_revenue' => $realRevenue,
            ]);

            // ✅ FIX: Safe cache update with existence check
            foreach ($this->existingDataCache as $key => &$cached) {
                if (isset($cached['id']) && $cached['id'] == $revenueId) {
                    $cached['target_revenue'] = $targetRevenue;
                    $cached['real_revenue'] = $realRevenue;
                    break;
                }
            }
            unset($cached); // Clear reference

        } catch (\Exception $e) {
            Log::error("Failed to update existing revenue: " . $e->getMessage(), [
                'revenue_id' => $revenueId,
                'target' => $targetRevenue,
                'real' => $realRevenue
            ]);
            throw $e;
        }
    }


    /**
     * ✅ ENHANCED: Extract row data dengan validation yang lebih ketat
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

        // ✅ ENHANCED: Validate minimal required data dengan prioritas
        $hasAM = !empty($data['am_name']) || !empty($data['nik']);
        $hasCC = !empty($data['cc_name']) || !empty($data['nipnas']);

        if (!$hasAM && !$hasCC) {
            $this->errorDetails[] = "❌ Baris {$rowNumber}: Data kosong - minimal harus ada Account Manager dan Corporate Customer";
            return null;
        }

        if (!$hasAM) {
            $this->errorDetails[] = "❌ Baris {$rowNumber}: Account Manager kosong (nama dan NIK)";
            return null;
        }

        if (!$hasCC) {
            $this->errorDetails[] = "❌ Baris {$rowNumber}: Corporate Customer kosong (nama dan NIPNAS)";
            return null;
        }

        // ✅ Clean data
        foreach ($data as $key => $value) {
            $data[$key] = trim($value);
        }

        return $data;
    }

    /**
     * ✅ IMPROVED: Find Account Manager dengan better error messaging dan fuzzy matching
     */
    private function findAccountManager($name, $nik, $rowNumber)
    {
        $accountManager = null;

        // Try by NIK first (most reliable)
        if (!empty($nik)) {
            $nikKey = 'nik:' . trim($nik);
            $accountManager = $this->accountManagers[$nikKey] ?? null;
        }

        // Try by name if NIK not found
        if (!$accountManager && !empty($name)) {
            $nameKey = 'nama:' . $this->normalizeString($name);
            $accountManager = $this->accountManagers[$nameKey] ?? null;
        }

        // ✅ ENHANCED: Fuzzy matching untuk nama yang mirip
        if (!$accountManager && !empty($name)) {
            $normalizedSearchName = $this->normalizeString($name);

            foreach ($this->accountManagers as $key => $am) {
                if (strpos($key, 'nama:') === 0) {
                    $existingNormalized = substr($key, 5);

                    // Check for partial match
                    if (strpos($existingNormalized, $normalizedSearchName) !== false ||
                        strpos($normalizedSearchName, $existingNormalized) !== false) {
                        $accountManager = $am;
                        $this->warningDetails[] = "⚠️ Baris {$rowNumber}: AM ditemukan dengan fuzzy search: '{$name}' → '{$am->nama}'";
                        break;
                    }

                    // Check similarity percentage
                    $similarity = 0;
                    similar_text($existingNormalized, $normalizedSearchName, $similarity);
                    if ($similarity > 80) {
                        $accountManager = $am;
                        $this->warningDetails[] = "⚠️ Baris {$rowNumber}: AM ditemukan dengan similarity {$similarity}%: '{$name}' → '{$am->nama}'";
                        break;
                    }
                }
            }
        }

        // ✅ FALLBACK: Database search
        if (!$accountManager && (!empty($name) || !empty($nik))) {
            $query = AccountManager::with(['divisis', 'witel', 'regional']);

            if (!empty($nik)) {
                $query->where('nik', $nik);
            } elseif (!empty($name)) {
                $query->where('nama', 'like', "%{$name}%");
            }

            $accountManager = $query->first();

            if ($accountManager) {
                // Add to cache untuk subsequent lookups
                $this->accountManagers['nama:' . $this->normalizeString($accountManager->nama)] = $accountManager;
                $this->accountManagers['nik:' . trim($accountManager->nik)] = $accountManager;
                $this->warningDetails[] = "⚠️ Baris {$rowNumber}: AM ditemukan di database: '{$accountManager->nama}'";
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
     * ✅ IMPROVED: Find Corporate Customer dengan better error messaging dan fuzzy matching
     */
    private function findCorporateCustomer($name, $nipnas, $rowNumber)
    {
        $corporateCustomer = null;

        // Try by NIPNAS first (most reliable)
        if (!empty($nipnas)) {
            $nipnasKey = 'nipnas:' . trim($nipnas);
            $corporateCustomer = $this->corporateCustomers[$nipnasKey] ?? null;
        }

        // Try by name if NIPNAS not found
        if (!$corporateCustomer && !empty($name)) {
            $nameKey = 'nama:' . $this->normalizeString($name);
            $corporateCustomer = $this->corporateCustomers[$nameKey] ?? null;
        }

        // ✅ ENHANCED: Fuzzy matching untuk nama yang mirip
        if (!$corporateCustomer && !empty($name)) {
            $normalizedSearchName = $this->normalizeString($name);

            foreach ($this->corporateCustomers as $key => $cc) {
                if (strpos($key, 'nama:') === 0) {
                    $existingNormalized = substr($key, 5);

                    // Check for partial match
                    if (strpos($existingNormalized, $normalizedSearchName) !== false ||
                        strpos($normalizedSearchName, $existingNormalized) !== false) {
                        $corporateCustomer = $cc;
                        $this->warningDetails[] = "⚠️ Baris {$rowNumber}: CC ditemukan dengan fuzzy search: '{$name}' → '{$cc->nama}'";
                        break;
                    }

                    // Check similarity percentage
                    $similarity = 0;
                    similar_text($existingNormalized, $normalizedSearchName, $similarity);
                    if ($similarity > 80) {
                        $corporateCustomer = $cc;
                        $this->warningDetails[] = "⚠️ Baris {$rowNumber}: CC ditemukan dengan similarity {$similarity}%: '{$name}' → '{$cc->nama}'";
                        break;
                    }
                }
            }
        }

        // ✅ FALLBACK: Database search
        if (!$corporateCustomer && (!empty($name) || !empty($nipnas))) {
            $query = CorporateCustomer::query();

            if (!empty($nipnas)) {
                $query->where('nipnas', $nipnas);
            } elseif (!empty($name)) {
                $query->where('nama', 'like', "%{$name}%");
            }

            $corporateCustomer = $query->first();

            if ($corporateCustomer) {
                // Add to cache
                $this->corporateCustomers['nama:' . $this->normalizeString($corporateCustomer->nama)] = $corporateCustomer;
                if (!empty($corporateCustomer->nipnas)) {
                    $this->corporateCustomers['nipnas:' . trim($corporateCustomer->nipnas)] = $corporateCustomer;
                }
                $this->warningDetails[] = "⚠️ Baris {$rowNumber}: CC ditemukan di database: '{$corporateCustomer->nama}'";
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

            // ✅ ENHANCED: Fuzzy search untuk divisi
            if (!$divisi) {
                foreach ($this->divisiList as $key => $storedDivisi) {
                    if (strpos($key, 'nama:') === 0) {
                        $storedName = substr($key, 5);
                        if (strpos($storedName, $this->normalizeString($divisiName)) !== false ||
                            strpos($this->normalizeString($divisiName), $storedName) !== false) {
                            $divisi = $storedDivisi;
                            $this->warningDetails[] = "⚠️ Baris {$rowNumber}: Divisi ditemukan dengan fuzzy search: '{$divisiName}' → '{$storedDivisi->nama}'";
                            break;
                        }
                    }
                }
            }

            // ✅ Database fallback
            if (!$divisi) {
                $divisi = Divisi::where('nama', 'like', "%{$divisiName}%")->first();
                if ($divisi) {
                    $this->divisiList['nama:' . $this->normalizeString($divisi->nama)] = $divisi;
                    $this->warningDetails[] = "⚠️ Baris {$rowNumber}: Divisi ditemukan di database: '{$divisiName}' → '{$divisi->nama}'";
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
     * ✅ IMPROVED: Column identification dengan better matching dan logging
     */
    private function identifyColumns($firstRow)
    {
        $map = [];
        $excelColumns = array_keys($firstRow->toArray());

        // ✅ Flexible matching untuk kolom basic
        foreach ($this->alternativeColumns as $standardKey => $alternatives) {
            foreach ($alternatives as $altName) {
                // Case-insensitive matching dengan trim
                $foundColumn = collect($excelColumns)->first(function ($col) use ($altName) {
                    return $this->normalizeString($col) === $this->normalizeString($altName);
                });

                if ($foundColumn) {
                    $map[$standardKey] = $foundColumn;
                    break;
                }
            }
        }

        Log::info('📋 Revenue column mapping result', [
            'mapped_columns' => $map,
            'available_columns' => $excelColumns,
            'year' => $this->year
        ]);

        return $map;
    }

    private function extractValue($row, $columnMap, $field)
    {
        $key = $columnMap[$field] ?? null;
        if ($key && isset($row[$key])) {
            $value = $row[$key];
            
            // ✅ FIX: Handle array values safely
            if (is_array($value)) {
                Log::warning("Array value detected in extractValue", [
                    'field' => $field,
                    'key' => $key,
                    'value' => $value
                ]);
                // Convert array to string (take first non-empty value)
                $nonEmpty = array_filter($value, function($v) {
                    return !empty($v) && $v !== null && trim($v) !== '';
                });
                $value = !empty($nonEmpty) ? reset($nonEmpty) : '';
            }
            
            return trim((string)$value);
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
 * ✅ FIX 3: parseNumericValue() - Enhanced array handling
 * 🎯 Root Cause: Excel cells might contain array data
 */
private function parseNumericValue($value)
{
    if (empty($value) || $value === null) return 0;

    // ✅ FIX: Handle array input
    if (is_array($value)) {
        Log::warning("Array value in parseNumericValue", ['value' => $value]);
        // Get first non-empty numeric value from array
        $numericValues = array_filter($value, function($v) {
            return is_numeric($v) || (is_string($v) && preg_match('/[\d,.-]/', $v));
        });
        
        if (empty($numericValues)) {
            return 0;
        }
        
        $value = reset($numericValues); // Get first valid value
    }

    if (is_numeric($value)) {
        return max(0, (float)$value);
    }

    // ✅ Handle string numeric values
    if (!is_string($value)) {
        $value = (string)$value;
    }

    // Handle berbagai format angka Indonesia/International
    $cleaned = preg_replace('/[^\d,.-]/', '', trim($value));

    // Handle comma as thousand separator vs decimal separator
    if (substr_count($cleaned, ',') == 1 && substr_count($cleaned, '.') == 0) {
        $parts = explode(',', $cleaned);
        if (strlen($parts[1]) <= 2) {
            $cleaned = str_replace(',', '.', $cleaned);
        } else {
            $cleaned = str_replace(',', '', $cleaned);
        }
    } else {
        $cleaned = str_replace(',', '', $cleaned);
    }

    $result = is_numeric($cleaned) ? max(0, (float)$cleaned) : 0;

    if ($result > 999999999999) {
        Log::warning("Very large revenue value detected: " . $result);
    }

    return $result;
}

    /**
     * Rules validasi (kosong karena validasi manual)
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * ✅ ENHANCED: Get comprehensive import results dengan conflict details
     */
    public function getImportResults()
    {
        return [
            'imported' => $this->importedCount,
            'updated' => $this->updatedCount,
            'duplicates' => $this->duplicateCount,
            'errors' => $this->errorCount,
            'skipped' => $this->skippedCount,
            'conflicts' => $this->conflictCount, // ✅ NEW
            'processed' => $this->processedRows,
            'year' => $this->year,
            'overwrite_mode' => $this->overwriteMode, // ✅ NEW
            'error_details' => $this->errorDetails,
            'warning_details' => $this->warningDetails,
            'success_details' => $this->successDetails,
            'conflict_details' => $this->conflictDetails, // ✅ NEW
            'monthly_pairs_found' => count($this->monthlyPairs),
            'monthly_pairs' => $this->monthlyPairs,
            'detected_columns' => $this->detectedColumns,
            'existing_data_found' => count($this->existingDataCache), // ✅ NEW
            'summary' => [
                'total_processed' => $this->processedRows,
                'successfully_processed' => $this->importedCount + $this->updatedCount,
                'failed_processed' => $this->errorCount,
                'success_rate' => $this->processedRows > 0 ? round((($this->importedCount + $this->updatedCount) / $this->processedRows) * 100, 2) : 0,
                'error_rate' => $this->processedRows > 0 ? round(($this->errorCount / $this->processedRows) * 100, 2) : 0,
                'duplicate_rate' => $this->processedRows > 0 ? round(($this->duplicateCount / $this->processedRows) * 100, 2) : 0,
                'conflict_rate' => $this->processedRows > 0 ? round(($this->conflictCount / $this->processedRows) * 100, 2) : 0, // ✅ NEW
                'has_warnings' => count($this->warningDetails) > 0,
                'has_errors' => $this->errorCount > 0,
                'has_conflicts' => $this->conflictCount > 0, // ✅ NEW
                'import_year' => $this->year,
                'columns_detected' => count($this->detectedColumns),
                'monthly_columns_detected' => count($this->monthlyPairs)
            ]
        ];
    }

    /**
 * ✅ FIX 6: getImportSummary() - Ensure safe array operations
 * 🎯 Root Cause: Array operations without proper validation
 */
public function getImportSummary()
{
    $results = $this->getImportResults();

    // Safe array initialization
    $missingAccountManagers = [];
    $missingCorporateCustomers = [];
    $missingDivisi = [];
    $validationErrors = [];
    $duplicates = [];

    // ✅ FIX: Safe array access for error categorization
    $errorDetails = is_array($this->errorDetails) ? $this->errorDetails : [];
    foreach ($errorDetails as $error) {
        if (!is_string($error)) {
            $error = (string)$error; // Ensure string conversion
        }

        if (strpos($error, 'Account Manager tidak ditemukan') !== false) {
            $missingAccountManagers[] = [
                'error' => $error,
                'nama' => $this->extractNameFromError($error, 'Nama:')
            ];
        } elseif (strpos($error, 'Corporate Customer tidak ditemukan') !== false) {
            $missingCorporateCustomers[] = [
                'error' => $error,
                'nama' => $this->extractNameFromError($error, 'Nama:')
            ];
        } elseif (strpos($error, 'tidak memiliki divisi') !== false) {
            $missingDivisi[] = [
                'error' => $error
            ];
        } else {
            $validationErrors[] = [
                'error' => $error
            ];
        }
    }

    // ✅ FIX: Safe array access for success details
    $successDetails = is_array($this->successDetails) ? $this->successDetails : [];
    foreach ($successDetails as $success) {
        if (!is_string($success)) {
            $success = (string)$success;
        }

        if (strpos($success, 'Updated') !== false || strpos($success, 'Diperbarui') !== false) {
            $duplicates[] = [
                'message' => $success
            ];
        }
    }

    // ✅ FIX: Safe conflict information handling
    $conflicts = [
        'total_conflicts' => (int)$this->conflictCount,
        'conflicts_by_action' => [],
        'conflicts_by_month' => [],
        'conflict_details' => is_array($this->conflictDetails) ? $this->conflictDetails : []
    ];

    // Safe conflict categorization
    foreach ($conflicts['conflict_details'] as $conflict) {
        if (!is_array($conflict)) {
            continue; // Skip invalid conflict data
        }

        $action = isset($conflict['action']) ? (string)$conflict['action'] : 'unknown';
        $month = isset($conflict['month']) ? (string)$conflict['month'] : 'unknown';

        if (!isset($conflicts['conflicts_by_action'][$action])) {
            $conflicts['conflicts_by_action'][$action] = 0;
        }
        $conflicts['conflicts_by_action'][$action]++;

        if (!isset($conflicts['conflicts_by_month'][$month])) {
            $conflicts['conflicts_by_month'][$month] = 0;
        }
        $conflicts['conflicts_by_month'][$month]++;
    }

    // ✅ SAFE return with proper array structure
    return [
        'total_rows' => (int)($results['processed'] ?? 0),
        'success_rows' => (int)(($results['imported'] ?? 0) + ($results['updated'] ?? 0)),
        'failed_rows' => (int)($results['errors'] ?? 0),
        'missing_account_managers' => $missingAccountManagers,
        'missing_corporate_customers' => $missingCorporateCustomers,
        'missing_divisi' => $missingDivisi,
        'validation_errors' => $validationErrors,
        'duplicates' => $duplicates,
        'conflicts' => $conflicts,
        'error_details' => [
            'missing_account_managers_count' => count($missingAccountManagers),
            'missing_corporate_customers_count' => count($missingCorporateCustomers),
            'missing_divisi_count' => count($missingDivisi),
            'validation_errors_count' => count($validationErrors),
            'duplicates_count' => count($duplicates),
            'conflicts_count' => (int)$this->conflictCount,
            'total_errors' => (int)($results['errors'] ?? 0)
        ],
        'success_percentage' => (float)($results['summary']['success_rate'] ?? 0),
        'year' => (int)$this->year,
        'overwrite_mode' => (string)$this->overwriteMode,
        'monthly_pairs_found' => count($this->monthlyPairs),
        'detected_columns' => is_array($this->detectedColumns) ? $this->detectedColumns : [],
        'existing_data_found' => count($this->existingDataCache),

        // Additional detailed info with safe access
        'detailed_results' => $results,
        'warning_details' => is_array($this->warningDetails) ? $this->warningDetails : [],
        'success_details' => is_array($this->successDetails) ? $this->successDetails : [],
        'all_error_details' => is_array($this->errorDetails) ? $this->errorDetails : [],
        'conflict_summary' => [
            'total' => (int)$this->conflictCount,
            'by_action' => $conflicts['conflicts_by_action'],
            'by_month' => $conflicts['conflicts_by_month'],
            'recommendations' => $this->generateConflictRecommendations()
        ]
    ];
}

    /**
     * ✅ NEW: Generate recommendations based on conflicts
     */
    private function generateConflictRecommendations()
    {
        $recommendations = [];

        if ($this->conflictCount > 0) {
            $recommendations[] = "💡 Ditemukan {$this->conflictCount} konflik data yang sudah ada";

            if ($this->overwriteMode === 'update') {
                $recommendations[] = "✅ Mode 'update': Data yang sudah ada telah diperbarui otomatis";
                $recommendations[] = "💡 Gunakan mode 'skip' jika ingin melewati data yang sudah ada";
            } elseif ($this->overwriteMode === 'skip') {
                $recommendations[] = "⚠️ Mode 'skip': Data yang sudah ada dilewati";
                $recommendations[] = "💡 Gunakan mode 'update' jika ingin memperbarui data yang sudah ada";
            } elseif ($this->overwriteMode === 'ask') {
                $recommendations[] = "❓ Mode 'ask': Data diperbarui tapi butuh konfirmasi manual";
                $recommendations[] = "💡 Periksa detail konflik untuk memutuskan tindakan selanjutnya";
            }

            $recommendations[] = "📊 Periksa 'conflict_details' untuk melihat perubahan spesifik per baris";
        }

        if ($this->duplicateCount > 0) {
            $recommendations[] = "ℹ️ {$this->duplicateCount} data identik ditemukan (tidak ada perubahan)";
        }

        return $recommendations;
    }

    /**
     * ✅ FIX 7: extractNameFromError() - Safe string operations
     * 🎯 Root Cause: String operations without proper validation
     */
    private function extractNameFromError($errorMessage, $marker)
    {
        if (!is_string($errorMessage) || !is_string($marker)) {
            return '';
        }

        $pos = strpos($errorMessage, $marker);
        if ($pos === false) {
            return '';
        }

        $nameStartPos = $pos + strlen($marker);
        $nameEndPos = strpos($errorMessage, ',', $nameStartPos);
        
        if ($nameEndPos === false) {
            $nameEndPos = strlen($errorMessage);
        }

        $name = substr($errorMessage, $nameStartPos, $nameEndPos - $nameStartPos);
        $name = trim(str_replace("'", "", $name));
        
        return (string)$name;
    }

}