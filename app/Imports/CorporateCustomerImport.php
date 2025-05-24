<?php

namespace App\Imports;

use App\Models\CorporateCustomer;
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

class CorporateCustomerImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures, RemembersRowNumber;

    private $importedCount = 0;
    private $updatedCount = 0;
    private $duplicateCount = 0;
    private $errorCount = 0;
    private $skippedCount = 0;

    // ✅ IMPROVED: Detailed tracking seperti RevenueImport
    private $errorDetails = [];
    private $warningDetails = [];
    private $successDetails = [];
    private $processedRows = 0;

    // ✅ IMPROVED: Master data caching dengan normalisasi
    private $existingCustomers = [];
    private $chunkSize = 100;

    // ✅ EXPANDED: Alternative column names dengan lebih banyak variasi
    private $alternativeColumns = [
        'standard_name' => [
            'standard name', 'STANDARD NAME', 'standard_name', 'Standard Name',
            'nama customer', 'NAMA CUSTOMER', 'nama_customer', 'Nama Customer',
            'corporate customer', 'CORPORATE CUSTOMER', 'corporate_customer',
            'customer_name', 'Customer Name', 'CUSTOMER NAME', 'nama_corporate',
            'nama', 'NAMA', 'Nama', 'name', 'Name', 'NAME'
        ],
        'nipnas' => [
            'nipnas', 'NIPNAS', 'Nipnas', 'customer_id', 'CUSTOMER_ID', 'customer id',
            'cust_id', 'CUST_ID', 'id_customer', 'ID_CUSTOMER', 'id customer'
        ]
    ];

    public function __construct()
    {
        $this->loadMasterData();

        // ✅ Set memory dan timeout untuk file besar
        ini_set('memory_limit', '1024M');
        set_time_limit(300); // 5 minutes
    }

    /**
     * ✅ IMPROVED: Load master data dengan normalisasi string
     */
    private function loadMasterData()
    {
        try {
            // Load existing Corporate Customers
            $existingCustomers = CorporateCustomer::all();
            foreach ($existingCustomers as $customer) {
                $this->existingCustomers['nama:' . $this->normalizeString($customer->nama)] = $customer;
                $this->existingCustomers['nipnas:' . trim($customer->nipnas)] = $customer;
            }

            Log::info('✅ Master data loaded for CorporateCustomer import', [
                'existing_customers' => count($existingCustomers)
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error loading master data: ' . $e->getMessage());
            throw new \Exception('Gagal memuat master data: ' . $e->getMessage());
        }
    }

    /**
     * ✅ NEW: Normalize string untuk konsistensi
     */
    private function normalizeString($string)
    {
        return strtolower(trim($string));
    }

    /**
     * ✅ IMPROVED: Collection processing dengan chunking dan better error handling
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

        Log::info('📊 Starting CorporateCustomer import', [
            'total_rows' => $rows->count(),
            'columns_found' => array_keys($columnMap)
        ]);

        // Process data dengan chunking
        $rows->slice(1)->chunk($this->chunkSize)->each(function ($chunk, $chunkIndex) use ($columnMap) {
            $this->processChunk($chunk, $chunkIndex, $columnMap);
        });

        Log::info('✅ CorporateCustomer import completed', [
            'imported' => $this->importedCount,
            'updated' => $this->updatedCount,
            'duplicates' => $this->duplicateCount,
            'errors' => $this->errorCount,
            'skipped' => $this->skippedCount
        ]);
    }

    /**
     * ✅ NEW: Validate required columns
     */
    private function validateRequiredColumns($columnMap)
    {
        $requiredColumns = ['standard_name', 'nipnas'];
        $missingColumns = [];

        foreach ($requiredColumns as $required) {
            if (!isset($columnMap[$required])) {
                $missingColumns[] = $required;
            }
        }

        if (!empty($missingColumns)) {
            $error = "❌ Kolom wajib tidak ditemukan: " . implode(', ', $missingColumns);
            $this->errorDetails[] = $error;
            throw new \Exception($error);
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
                $actualRowIndex = $chunkIndex * $this->chunkSize + $rowIndex + 2; // +2 for Excel row number
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
            // ✅ Extract and validate data
            $rowData = $this->extractRowData($row, $columnMap, $rowNumber);

            if (!$rowData) {
                return; // Skip jika data tidak valid
            }

            // ✅ Check if customer already exists
            $existingCustomer = $this->findExistingCustomer($rowData['nama'], $rowData['nipnas'], $rowNumber);

            if ($existingCustomer) {
                // ✅ Update existing customer
                $existingCustomer->update([
                    'nama' => $rowData['nama'],
                    'nipnas' => $rowData['nipnas']
                ]);

                $this->updatedCount++;
                $this->successDetails[] = "✅ Baris {$rowNumber}: Corporate Customer '{$rowData['nama']}' diperbarui (NIPNAS: {$rowData['nipnas']})";

            } else {
                // ✅ Create new customer
                $newCustomer = CorporateCustomer::create([
                    'nama' => $rowData['nama'],
                    'nipnas' => $rowData['nipnas']
                ]);

                // Add to cache untuk row selanjutnya
                $this->existingCustomers['nama:' . $this->normalizeString($newCustomer->nama)] = $newCustomer;
                $this->existingCustomers['nipnas:' . trim($newCustomer->nipnas)] = $newCustomer;

                $this->importedCount++;
                $this->successDetails[] = "✅ Baris {$rowNumber}: Corporate Customer baru '{$rowData['nama']}' dibuat (NIPNAS: {$rowData['nipnas']})";
            }

        } catch (\Exception $e) {
            $this->errorCount++;
            $errorMsg = "❌ Baris {$rowNumber}: " . $e->getMessage();
            $this->errorDetails[] = $errorMsg;
            Log::error($errorMsg, ['exception' => $e]);
        }
    }

    /**
     * ✅ NEW: Extract and validate row data
     */
    private function extractRowData($row, $columnMap, $rowNumber)
    {
        $data = [
            'nama' => $this->extractValue($row, $columnMap, 'standard_name'),
            'nipnas' => $this->extractValue($row, $columnMap, 'nipnas')
        ];

        // ✅ Validate required fields
        if (empty($data['nama'])) {
            $this->errorDetails[] = "❌ Baris {$rowNumber}: Nama Corporate Customer kosong";
            return null;
        }

        if (empty($data['nipnas'])) {
            $this->errorDetails[] = "❌ Baris {$rowNumber}: NIPNAS kosong";
            return null;
        }

        // ✅ Validate NIPNAS format (should be numeric)
        if (!is_numeric($data['nipnas'])) {
            $this->errorDetails[] = "❌ Baris {$rowNumber}: Format NIPNAS tidak valid: '{$data['nipnas']}' (harus berupa angka)";
            return null;
        }

        // ✅ Validate NIPNAS range (reasonable limits)
        $nipnas = (int)$data['nipnas'];
        if ($nipnas <= 0 || $nipnas > 9999999) {
            $this->errorDetails[] = "❌ Baris {$rowNumber}: NIPNAS di luar range valid: '{$data['nipnas']}' (harus 1-9999999)";
            return null;
        }

        return $data;
    }

    /**
     * ✅ IMPROVED: Find existing customer dengan fuzzy matching
     */
    private function findExistingCustomer($nama, $nipnas, $rowNumber)
    {
        // Try by NIPNAS first (most accurate)
        $nipnasKey = 'nipnas:' . trim($nipnas);
        $existingCustomer = $this->existingCustomers[$nipnasKey] ?? null;

        if ($existingCustomer) {
            // Check jika nama berbeda
            if ($this->normalizeString($existingCustomer->nama) !== $this->normalizeString($nama)) {
                $this->warningDetails[] = "⚠️ Baris {$rowNumber}: NIPNAS {$nipnas} sudah ada dengan nama berbeda - Existing: '{$existingCustomer->nama}', Baru: '{$nama}'";
            }
            return $existingCustomer;
        }

        // Try by name (secondary check)
        $namaKey = 'nama:' . $this->normalizeString($nama);
        $existingCustomer = $this->existingCustomers[$namaKey] ?? null;

        if ($existingCustomer) {
            // Check jika NIPNAS berbeda
            if ($existingCustomer->nipnas != $nipnas) {
                $this->warningDetails[] = "⚠️ Baris {$rowNumber}: Nama '{$nama}' sudah ada dengan NIPNAS berbeda - Existing: '{$existingCustomer->nipnas}', Baru: '{$nipnas}'";
                // Dalam kasus ini, kita anggap sebagai customer berbeda
                return null;
            }
            return $existingCustomer;
        }

        // ✅ Fallback to database dengan fuzzy matching
        $existingCustomer = CorporateCustomer::where('nipnas', $nipnas)
            ->orWhere('nama', 'like', "%{$nama}%")
            ->first();

        if ($existingCustomer) {
            // Add to cache
            $this->existingCustomers['nama:' . $this->normalizeString($existingCustomer->nama)] = $existingCustomer;
            $this->existingCustomers['nipnas:' . trim($existingCustomer->nipnas)] = $existingCustomer;

            if ($existingCustomer->nipnas == $nipnas) {
                $this->warningDetails[] = "⚠️ Baris {$rowNumber}: Customer ditemukan di database dengan NIPNAS: {$nipnas}";
            } else {
                $this->warningDetails[] = "⚠️ Baris {$rowNumber}: Customer ditemukan dengan fuzzy search: '{$nama}' → '{$existingCustomer->nama}'";
            }
        }

        return $existingCustomer;
    }

    /**
     * ✅ IMPROVED: Column identification dengan flexible matching
     */
    private function identifyColumns($firstRow)
    {
        $map = [];
        $excelColumns = array_keys($firstRow->toArray());

        foreach ($this->alternativeColumns as $standardKey => $alternatives) {
            foreach ($alternatives as $altName) {
                $foundColumn = collect($excelColumns)->first(function ($col) use ($altName) {
                    return strtolower(trim($col)) === strtolower(trim($altName));
                });

                if ($foundColumn) {
                    $map[$standardKey] = $foundColumn;
                    break;
                }
            }
        }

        Log::info('📋 CorporateCustomer column mapping', $map);
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
     * Rules validasi
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
            'updated' => $this->updatedCount,
            'duplicates' => $this->duplicateCount,
            'errors' => $this->errorCount,
            'skipped' => $this->skippedCount,
            'processed' => $this->processedRows,
            'error_details' => $this->errorDetails,
            'warning_details' => $this->warningDetails,
            'success_details' => $this->successDetails,
            'summary' => [
                'total_processed' => $this->processedRows,
                'success_rate' => $this->processedRows > 0 ? round(($this->importedCount + $this->updatedCount) / $this->processedRows * 100, 2) : 0,
                'error_rate' => $this->processedRows > 0 ? round($this->errorCount / $this->processedRows * 100, 2) : 0
            ]
        ];
    }
}