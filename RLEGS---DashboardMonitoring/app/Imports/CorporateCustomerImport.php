<?php

namespace App\Imports;

use App\Models\CorporateCustomer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CorporateCustomerImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures, RemembersRowNumber;

    private $importedCount = 0;
    private $duplicateCount = 0;
    private $processedRows = [];

    /**
     * Import data as collection for pre-processing
     */
    public function collection(Collection $rows)
    {
        // Track unique entries based on nama and nipnas
        $uniqueRows = [];
        $existingEntries = [];

        // Get all existing corporate customers
        $existingCustomers = CorporateCustomer::all()->pluck('nipnas', 'nama')->toArray();

        foreach ($rows as $index => $row) {
            $rowKey = trim($row['nama']) . '|' . trim($row['nipnas']);

            // Skip if this exact row is already in our processed list (duplicate in Excel)
            if (in_array($rowKey, $this->processedRows)) {
                $this->duplicateCount++;
                continue;
            }

            // Check if nama or nipnas already exists in database
            if (array_key_exists(trim($row['nama']), $existingCustomers) ||
                in_array(trim($row['nipnas']), $existingCustomers)) {
                $existingEntries[] = $index;
                continue;
            }

            // Add to unique rows for processing
            $uniqueRows[] = $row;
            $this->processedRows[] = $rowKey;
        }

        // Insert only the unique rows
        foreach ($uniqueRows as $row) {
            CorporateCustomer::create([
                'nama' => trim($row['nama']),
                'nipnas' => trim($row['nipnas']),
            ]);
            $this->importedCount++;
        }

        return collect([
            'imported' => $this->importedCount,
            'duplicates' => $this->duplicateCount,
            'existing' => count($existingEntries)
        ]);
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'nama' => 'required|string',
            'nipnas' => 'required|numeric|max:9999999',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'nama.required' => 'Nama Corporate Customer wajib diisi.',
            'nipnas.required' => 'NIPNAS wajib diisi.',
            'nipnas.numeric' => 'NIPNAS harus berupa angka.',
            'nipnas.max' => 'NIPNAS maksimal 7 digit.',
        ];
    }

    /**
     * Get import results
     */
    public function getImportResults()
    {
        return [
            'imported' => $this->importedCount,
            'duplicates' => $this->duplicateCount
        ];
    }
}
