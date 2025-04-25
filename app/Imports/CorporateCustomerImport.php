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

class CorporateCustomerImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures, RemembersRowNumber;

    private $importedCount = 0;
    private $updatedCount = 0;
    private $duplicateCount = 0;
    private $processedRows = [];

    /**
     * Import data as collection for pre-processing
     */
    public function collection(Collection $rows)
    {
        // Track unique entries based on nipnas
        $uniqueRows = [];
        $existingEntries = [];

        // Get all existing corporate customers by NIPNAS
        $existingCustomers = CorporateCustomer::all()->pluck('id', 'nipnas')->toArray();

        Log::info('Starting import of ' . count($rows) . ' corporate customer rows');

        // Identify column names in the CSV (case-insensitive)
        $columnMap = $this->identifyColumns($rows->first());

        Log::info('Identified corporate customer columns', $columnMap);

        foreach ($rows as $index => $row) {
            try {
                // Skip first row if it's a header
                if ($index === 0 && isset($row['NIPNAS']) && $row['NIPNAS'] === 'NIPNAS') {
                    continue;
                }

                // Extract values using the column map
                $nama = $this->extractValue($row, $columnMap, 'standard_name');
                $nipnas = $this->extractValue($row, $columnMap, 'nipnas');

                // Skip if any required field is empty
                if (empty($nama) || empty($nipnas)) {
                    Log::warning('Incomplete corporate customer row in CSV', [
                        'row' => $index + 2, // +2 for Excel row number (1-based index + header)
                        'nama' => $nama,
                        'nipnas' => $nipnas
                    ]);
                    continue;
                }

                // Create a unique key for this row
                $rowKey = $nipnas;

                // Skip if this exact row is already in our processed list (duplicate in Excel)
                if (in_array($rowKey, $this->processedRows)) {
                    $this->duplicateCount++;
                    Log::info('Duplicate NIPNAS found in CSV file', ['row' => $index + 2, 'nipnas' => $nipnas]);
                    continue;
                }

                // Check if Corporate Customer already exists by NIPNAS
                if (array_key_exists($nipnas, $existingCustomers)) {
                    // Get existing corporate customer
                    $customerId = $existingCustomers[$nipnas];
                    $customer = CorporateCustomer::find($customerId);

                    if ($customer) {
                        // Update existing corporate customer
                        $customer->update([
                            'nama' => $nama,
                        ]);

                        $this->updatedCount++;
                        Log::info('Corporate Customer updated', ['row' => $index + 2, 'nipnas' => $nipnas, 'id' => $customerId]);
                    }
                } else {
                    // This is a new corporate customer
                    $uniqueRows[] = [
                        'nama' => $nama,
                        'nipnas' => $nipnas,
                    ];
                }

                $this->processedRows[] = $rowKey;
            } catch (\Exception $e) {
                Log::error('Error processing corporate customer CSV row: ' . $e->getMessage(), [
                    'row' => $index + 2,
                    'exception' => $e
                ]);
                continue;
            }
        }

        // Insert the new corporate customers
        foreach ($uniqueRows as $row) {
            try {
                CorporateCustomer::create($row);

                $this->importedCount++;
                Log::info('New Corporate Customer created', ['nipnas' => $row['nipnas']]);
            } catch (\Exception $e) {
                Log::error('Error saving Corporate Customer: ' . $e->getMessage(), $row);
            }
        }

        Log::info('Corporate Customer import completed', [
            'imported_new' => $this->importedCount,
            'updated' => $this->updatedCount,
            'duplicates_skipped' => $this->duplicateCount,
        ]);

        return collect([
            'imported' => $this->importedCount,
            'updated' => $this->updatedCount,
            'duplicates' => $this->duplicateCount
        ]);
    }

    /**
     * Identify column names in the CSV
     */
    private function identifyColumns($firstRow)
    {
        $map = [
            'standard_name' => null,
            'nipnas' => null,
        ];

        if (!$firstRow) {
            return $map;
        }

        foreach ($firstRow as $key => $value) {
            $upperKey = strtoupper($key);

            if ($upperKey === 'STANDARD NAME' || $upperKey === 'STANDARD_NAME') {
                $map['standard_name'] = $key;
            } elseif ($upperKey === 'NIPNAS') {
                $map['nipnas'] = $key;
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

        // Try direct access for common column names
        if ($field === 'standard_name' && isset($row['STANDARD NAME'])) {
            return trim((string)$row['STANDARD NAME']);
        } elseif ($field === 'nipnas' && isset($row['NIPNAS'])) {
            return trim((string)$row['NIPNAS']);
        }

        return null;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            // Semua kolom dibuat nullable agar validasi bisa flexible
            '*.STANDARD NAME' => 'nullable',
            '*.NIPNAS' => 'nullable',
            // Untuk lowercase atau variasi nama lain
            '*.standard_name' => 'nullable',
            '*.nipnas' => 'nullable',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            '*.STANDARD NAME.required' => 'Nama Corporate Customer wajib diisi.',
            '*.NIPNAS.required' => 'NIPNAS wajib diisi.',
        ];
    }

    /**
     * Get import results
     */
    public function getImportResults()
    {
        return [
            'imported' => $this->importedCount,
            'updated' => $this->updatedCount,
            'duplicates' => $this->duplicateCount
        ];
    }
}