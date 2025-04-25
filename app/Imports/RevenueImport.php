<?php

namespace App\Imports;

use App\Models\Revenue;
use App\Models\AccountManager;
use App\Models\CorporateCustomer;
use App\Models\Divisi;
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

class RevenueImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures, RemembersRowNumber;

    private $importedCount = 0;
    private $duplicateCount = 0;
    private $errorCount = 0;
    private $errorDetails = [];
    private $accountManagers = [];
    private $corporateCustomers = [];
    private $divisiDefault = null;
    private $year;

    // Ukuran chunk untuk memproses data dalam batches
    private $chunkSize = 50;

    // Kolom bulanan untuk real revenue
    private $realRevenueColumns = [
        'real_jan', 'real_feb', 'real_mar', 'real_apr', 'real_mei', 'real_jun',
        'real_jul', 'real_agu', 'real_sep', 'real_okt', 'real_nov', 'real_des'
    ];

    // Kolom bulanan untuk target revenue
    private $targetRevenueColumns = [
        
        'target_jan', 'target_feb', 'target_mar', 'target_apr', 'target_mei', 'target_jun',
        'target_jul', 'target_agu', 'target_sep', 'target_okt', 'target_nov', 'target_des'
    ];

    // Alternatif nama kolom (dalam berbagai format kapitalisasi)
    private $alternativeColumns = [
        // Kolom Account Manager
        'nama_am' => ['nama am', 'nama_am', 'account_manager', 'account manager'],
        'nik' => ['nik'],

        // Kolom Corporate Customer
        'standard_name' => ['standard_name', 'standard name', 'nama customer', 'nama_customer', 'corporate customer', 'corporate_customer'],
        'nipnas' => ['nipnas'],

        // Kolom Divisi
        'divisi' => ['divisi', 'divisi_id'],
    ];

    public function __construct($year = null)
    {
        // Set tahun untuk revenue, default ke tahun saat ini
        $this->year = $year ?: date('Y');

        // Preload data untuk lookup yang lebih cepat
        $this->loadMasterData();

        // Coba mendapatkan divisi default (jika ada)
        $this->divisiDefault = Divisi::first();

        if (!$this->divisiDefault) {
            Log::warning('Tidak ada data divisi di database. Mohon tambahkan minimal satu divisi.');
        }
    }

    /**
     * Load data Account Manager dan Corporate Customer untuk lookup cepat
     */
    private function loadMasterData()
    {
        // Load data Account Manager
        $accountManagers = AccountManager::with('divisis')->get();
        foreach ($accountManagers as $am) {
            // Indeks dengan nama (lowercase untuk case-insensitive search)
            $this->accountManagers['nama:' . strtolower($am->nama)] = $am;

            // Indeks dengan NIK
            $this->accountManagers['nik:' . $am->nik] = $am;
        }

        // Load data Corporate Customer
        $corporateCustomers = CorporateCustomer::all();
        foreach ($corporateCustomers as $cc) {
            // Indeks dengan nama (lowercase untuk case-insensitive search)
            $this->corporateCustomers['nama:' . strtolower($cc->nama)] = $cc;

            // Indeks dengan NIPNAS
            if (!empty($cc->nipnas)) {
                $this->corporateCustomers['nipnas:' . $cc->nipnas] = $cc;
            }
        }

        Log::info('Master data loaded', [
            'account_managers' => count($this->accountManagers) / 2, // karena setiap AM diindeks 2 kali
            'corporate_customers' => count($this->corporateCustomers)
        ]);
    }

    /**
     * Import data as collection with chunking for large files
     */
    public function collection(Collection $rows)
    {
        // Identifikasi kolom dari Excel
        if ($rows->isEmpty()) {
            Log::error('File Excel kosong atau tidak memiliki data yang valid');
            $this->errorDetails[] = "File Excel kosong atau tidak memiliki data yang valid";
            return;
        }

        $firstRow = $rows->first();
        $columnMap = $this->identifyColumns($firstRow);

        Log::info('Identified columns', $columnMap);

        // Proses data dalam chunks untuk menghindari memory issues
        $totalRows = $rows->count();
        Log::info("Processing {$totalRows} rows in chunks of {$this->chunkSize}");

        // Skip baris pertama jika itu adalah header
        $rows = $rows->slice(1);

        // Proses dalam chunks
        $rows->chunk($this->chunkSize)->each(function ($chunk, $chunkIndex) use ($columnMap, $totalRows) {
            Log::info("Processing chunk {$chunkIndex} (rows " . ($chunkIndex * $this->chunkSize + 1) . " to " .
                    min(($chunkIndex + 1) * $this->chunkSize, $totalRows) . ")");

            DB::beginTransaction();
            try {
                foreach ($chunk as $rowIndex => $row) {
                    $actualRowIndex = $chunkIndex * $this->chunkSize + $rowIndex;

                    // Skip baris kosong
                    if ($this->isEmptyRow($row)) {
                        continue;
                    }

                    // Proses baris
                    $this->processRow($row, $columnMap, $actualRowIndex);
                }

                DB::commit();

                // Clean up memory
                gc_collect_cycles();

            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('Error processing chunk: ' . $e->getMessage(), [
                    'chunk' => $chunkIndex,
                    'exception' => $e
                ]);

                $this->errorCount++;
                $this->errorDetails[] = "Error saat memproses chunk {$chunkIndex}: " . $e->getMessage();
            }
        });

        Log::info('Import completed', [
            'imported' => $this->importedCount,
            'duplicates' => $this->duplicateCount,
            'errors' => $this->errorCount
        ]);
    }

    /**
     * Process an individual row
     */
    private function processRow($row, $columnMap, $rowIndex)
    {
        try {
            // Extract Account Manager data
            $amName = $this->extractValue($row, $columnMap, 'nama_am');
            $nik = $this->extractValue($row, $columnMap, 'nik');

            // Extract Corporate Customer data
            $ccName = $this->extractValue($row, $columnMap, 'standard_name');
            $nipnas = $this->extractValue($row, $columnMap, 'nipnas');

            // Extract Divisi (jika ada di file)
            $divisiName = $this->extractValue($row, $columnMap, 'divisi');

            // Skip baris jika tidak ada Account Manager dan Corporate Customer
            if (empty($amName) && empty($nik) && empty($ccName) && empty($nipnas)) {
                Log::info('Skipping row with no AM and CC information', ['row' => $rowIndex + 2]);
                return;
            }

            // Cari Account Manager
            $accountManager = $this->findAccountManager($amName, $nik);
            if (!$accountManager) {
                $this->errorCount++;
                $errorMsg = "Account Manager tidak ditemukan: Nama='$amName', NIK='$nik'";
                $this->errorDetails[] = "Baris " . ($rowIndex + 2) . ": $errorMsg";
                Log::warning($errorMsg, ['row' => $rowIndex + 2]);
                return;
            }

            // Cari Corporate Customer
            $corporateCustomer = $this->findCorporateCustomer($ccName, $nipnas);
            if (!$corporateCustomer) {
                $this->errorCount++;
                $errorMsg = "Corporate Customer tidak ditemukan: Nama='$ccName', NIPNAS='$nipnas'";
                $this->errorDetails[] = "Baris " . ($rowIndex + 2) . ": $errorMsg";
                Log::warning($errorMsg, ['row' => $rowIndex + 2]);
                return;
            }

            // Cari divisi
            $divisi = null;
            if (!empty($divisiName)) {
                // Coba cari divisi berdasarkan nama
                $divisi = Divisi::where('nama', 'like', "%{$divisiName}%")->first();
            }

            // Jika divisi tidak ditemukan dari file, gunakan divisi pertama dari account manager
            if (!$divisi) {
                $divisi = $accountManager->divisis()->first();
            }

            // Jika masih tidak ada divisi, gunakan divisi default
            if (!$divisi && $this->divisiDefault) {
                $divisi = $this->divisiDefault;
            }

            if (!$divisi) {
                $this->errorCount++;
                $errorMsg = "Tidak dapat menemukan divisi untuk account manager: " . $accountManager->nama;
                $this->errorDetails[] = "Baris " . ($rowIndex + 2) . ": $errorMsg";
                Log::warning($errorMsg, ['row' => $rowIndex + 2]);
                return;
            }

            // Dapatkan ID
            $accountManagerId = $accountManager->id;
            $corporateCustomerId = $corporateCustomer->id;
            $divisiId = $divisi->id;

            // Proses data revenue untuk 12 bulan
            $this->processMonthlyRevenue(
                $row,
                $columnMap,
                $accountManagerId,
                $divisiId,
                $corporateCustomerId,
                $rowIndex + 2
            );

        } catch (\Exception $e) {
            $this->errorCount++;
            $errorMsg = "Error pada baris " . ($rowIndex + 2) . ": " . $e->getMessage();
            $this->errorDetails[] = $errorMsg;
            Log::error($errorMsg, [
                'exception' => $e,
                'row' => $rowIndex + 2
            ]);

            // Re-throw untuk ditangkap oleh transaction
            throw $e;
        }
    }

    /**
     * Proses data revenue bulanan dan simpan ke database
     */
    private function processMonthlyRevenue($row, $columnMap, $accountManagerId, $divisiId, $corporateCustomerId, $excelRow)
    {
        $year = $this->year;

        // Proses setiap bulan (Januari - Desember)
        for ($month = 1; $month <= 12; $month++) {
            // Format nomor bulan dengan leading zero
            $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);

            // Format bulan untuk nama kolom (jan, feb, dll)
            $monthName = strtolower(date('M', mktime(0, 0, 0, $month, 1)));
            if ($monthName == 'may') $monthName = 'mei'; // Khusus untuk Mei dalam bahasa Indonesia
            if ($monthName == 'aug') $monthName = 'agu'; // Khusus untuk Agustus dalam bahasa Indonesia
            if ($monthName == 'oct') $monthName = 'okt'; // Khusus untuk Oktober dalam bahasa Indonesia
            if ($monthName == 'dec') $monthName = 'des'; // Khusus untuk Desember dalam bahasa Indonesia

            // Dapatkan nama kolom untuk target dan real revenue
            $targetColumn = 'target_' . $monthName;
            $realColumn = 'real_' . $monthName;

            // Dapatkan nilai dari Excel (dengan handling error jika kolom tidak ada)
            $targetRevenue = 0;
            $realRevenue = 0;

            // Cek apakah kolom target ada di map
            if (isset($columnMap[$targetColumn]) && isset($row[$columnMap[$targetColumn]])) {
                $targetRevenue = $this->parseNumericValue($row[$columnMap[$targetColumn]]);
            }

            // Cek apakah kolom real ada di map
            if (isset($columnMap[$realColumn]) && isset($row[$columnMap[$realColumn]])) {
                $realRevenue = $this->parseNumericValue($row[$columnMap[$realColumn]]);
            }

            // Skip jika kedua nilai kosong/nol (tidak perlu menyimpan data kosong)
            if ($targetRevenue == 0 && $realRevenue == 0) {
                continue;
            }

            // Format bulan dalam bentuk YYYY-MM-DD
            $bulan = $year . '-' . $monthFormatted . '-01';

            // Batch updates to be more efficient - instead of querying DB for each row
            // Use updateOrCreate to reduce query count
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

                // Jika wasRecentlyCreated property ada dan true, berarti baru dibuat
                if (isset($revenue->wasRecentlyCreated) && $revenue->wasRecentlyCreated) {
                    $this->importedCount++;
                } else {
                    $this->duplicateCount++;
                }

                Log::debug("Data revenue saved: AM ID=$accountManagerId, CC ID=$corporateCustomerId, Bulan=$bulan", [
                    'row' => $excelRow,
                    'month' => $month,
                    'is_new' => $revenue->wasRecentlyCreated ?? false
                ]);
            } catch (\Exception $e) {
                Log::error("Error saving revenue data: " . $e->getMessage(), [
                    'account_manager_id' => $accountManagerId,
                    'divisi_id' => $divisiId,
                    'corporate_customer_id' => $corporateCustomerId,
                    'bulan' => $bulan,
                    'row' => $excelRow
                ]);
                throw $e;
            }
        }
    }

    /**
     * Identifikasi kolom dalam file Excel
     */
    private function identifyColumns($firstRow)
    {
        $map = [];

        if (!$firstRow) {
            return $map;
        }

        // Cari kolom wajib terlebih dahulu
        foreach ($firstRow as $key => $value) {
            $lowerKey = strtolower($key);

            // Cek untuk setiap kategori kolom
            foreach ($this->alternativeColumns as $standardKey => $alternatives) {
                if (in_array($lowerKey, $alternatives)) {
                    $map[$standardKey] = $key;
                }
            }

            // Cek untuk kolom target revenue bulanan
            foreach ($this->targetRevenueColumns as $month) {
                if (strpos($lowerKey, strtolower($month)) !== false ||
                    strpos($lowerKey, str_replace('_', ' ', strtolower($month))) !== false) {
                    $map[$month] = $key;
                }
            }

            // Cek untuk kolom real revenue bulanan
            foreach ($this->realRevenueColumns as $month) {
                if (strpos($lowerKey, strtolower($month)) !== false ||
                    strpos($lowerKey, str_replace('_', ' ', strtolower($month))) !== false) {
                    $map[$month] = $key;
                }
            }
        }

        return $map;
    }

    /**
     * Extract value from row using column map
     */
    private function extractValue($row, $columnMap, $field)
    {
        $key = $columnMap[$field] ?? null;

        if ($key && isset($row[$key])) {
            return trim((string)$row[$key]);
        }

        // Try direct access for common column names (case insensitive)
        foreach ($this->alternativeColumns[$field] ?? [] as $altField) {
            if (isset($row[strtoupper($altField)])) {
                return trim((string)$row[strtoupper($altField)]);
            }
            if (isset($row[strtolower($altField)])) {
                return trim((string)$row[strtolower($altField)]);
            }
            if (isset($row[ucfirst(strtolower($altField))])) {
                return trim((string)$row[ucfirst(strtolower($altField))]);
            }
        }

        return null;
    }

    /**
     * Find Account Manager by name or NIK
     */
    private function findAccountManager($name, $nik)
    {
        if (!empty($name)) {
            $nameKey = 'nama:' . strtolower($name);
            if (isset($this->accountManagers[$nameKey])) {
                return $this->accountManagers[$nameKey];
            }
        }

        if (!empty($nik)) {
            $nikKey = 'nik:' . $nik;
            if (isset($this->accountManagers[$nikKey])) {
                return $this->accountManagers[$nikKey];
            }
        }

        // Fallback to database search if not found in preloaded data
        if (!empty($name)) {
            $am = AccountManager::where('nama', 'like', "%{$name}%")->first();
            if ($am) {
                // Add to cache for future lookups
                $this->accountManagers['nama:' . strtolower($am->nama)] = $am;
                $this->accountManagers['nik:' . $am->nik] = $am;
                return $am;
            }
        }

        if (!empty($nik)) {
            $am = AccountManager::where('nik', $nik)->first();
            if ($am) {
                // Add to cache for future lookups
                $this->accountManagers['nama:' . strtolower($am->nama)] = $am;
                $this->accountManagers['nik:' . $am->nik] = $am;
                return $am;
            }
        }

        return null;
    }

    /**
     * Find Corporate Customer by name or NIPNAS
     */
    private function findCorporateCustomer($name, $nipnas)
    {
        if (!empty($name)) {
            $nameKey = 'nama:' . strtolower($name);
            if (isset($this->corporateCustomers[$nameKey])) {
                return $this->corporateCustomers[$nameKey];
            }
        }

        if (!empty($nipnas)) {
            $nipnasKey = 'nipnas:' . $nipnas;
            if (isset($this->corporateCustomers[$nipnasKey])) {
                return $this->corporateCustomers[$nipnasKey];
            }
        }

        // Fallback to database search if not found in preloaded data
        if (!empty($name)) {
            $cc = CorporateCustomer::where('nama', 'like', "%{$name}%")->first();
            if ($cc) {
                // Add to cache for future lookups
                $this->corporateCustomers['nama:' . strtolower($cc->nama)] = $cc;
                if (!empty($cc->nipnas)) {
                    $this->corporateCustomers['nipnas:' . $cc->nipnas] = $cc;
                }
                return $cc;
            }
        }

        if (!empty($nipnas)) {
            $cc = CorporateCustomer::where('nipnas', $nipnas)->first();
            if ($cc) {
                // Add to cache for future lookups
                $this->corporateCustomers['nama:' . strtolower($cc->nama)] = $cc;
                $this->corporateCustomers['nipnas:' . $cc->nipnas] = $cc;
                return $cc;
            }
        }

        return null;
    }

    /**
     * Check if a row is empty (all values are null or empty strings)
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
     * Parse numeric value, handling various formats
     */
    private function parseNumericValue($value)
    {
        if (empty($value)) return 0;

        // Handle different numeric formats
        if (is_numeric($value)) {
            return (int)$value;
        }

        // Clean string and convert to number
        $cleaned = preg_replace('/[^0-9]/', '', $value);
        return empty($cleaned) ? 0 : (int)$cleaned;
    }

    /**
     * Rules validasi untuk data Excel
     */
    public function rules(): array
    {
        // Semua kolom dibuat nullable agar validasi bisa flexible
        return [];
    }

    /**
     * Get import results
     */
    public function getImportResults()
    {
        return [
            'imported' => $this->importedCount,
            'duplicates' => $this->duplicateCount,
            'errors' => $this->errorCount,
            'error_details' => $this->errorDetails
        ];
    }
}