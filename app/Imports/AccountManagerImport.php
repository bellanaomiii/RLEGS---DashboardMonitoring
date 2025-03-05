<?php

namespace App\Imports;

use App\Models\AccountManager;
use App\Models\Witel;
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

class AccountManagerImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
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
        // Track unique entries based on nama and nik
        $uniqueRows = [];
        $existingEntries = [];

        // Get all existing account managers
        $existingAccountManagers = AccountManager::all()->pluck('nik', 'nama')->toArray();

        foreach ($rows as $index => $row) {
            try {
                // Standardize row keys
                $nama = $row['nama'] ?? null;
                $nik = $row['nik'] ?? null;

                // Get witel and divisi names from the appropriate columns
                $witelName = $row['witel'] ?? $row['witel_id'] ?? null;
                $divisiName = $row['divisi'] ?? $row['divisi_id'] ?? null;

                // Skip if any required field is empty
                if (empty($nama) || empty($nik) || empty($witelName) || empty($divisiName)) {
                    Log::warning('Baris Excel tidak lengkap', ['row' => $row]);
                    continue;
                }

                // Create a unique key for this row
                $rowKey = trim($nama) . '|' . trim($nik);

                // Skip if this exact row is already in our processed list (duplicate in Excel)
                if (in_array($rowKey, $this->processedRows)) {
                    $this->duplicateCount++;
                    Log::info('Duplicate row found in Excel file', ['row' => $row]);
                    continue;
                }

                // Check if nama or nik already exists in database
                if (array_key_exists(trim($nama), $existingAccountManagers) ||
                    in_array(trim($nik), $existingAccountManagers)) {
                    $existingEntries[] = $index;
                    Log::info('Account Manager already exists in database', ['nama' => $nama, 'nik' => $nik]);
                    continue;
                }

                // Find witel and divisi by name
                $witel = Witel::where('nama', $witelName)->first();
                $divisi = Divisi::where('nama', $divisiName)->first();

                if (!$witel) {
                    Log::warning("Witel '$witelName' tidak ditemukan di database");
                    continue;
                }

                if (!$divisi) {
                    Log::warning("Divisi '$divisiName' tidak ditemukan di database");
                    continue;
                }

                // Add to unique rows for processing
                $uniqueRows[] = [
                    'nama' => trim($nama),
                    'nik' => trim($nik),
                    'witel_id' => $witel->id,
                    'divisi_id' => $divisi->id
                ];
                $this->processedRows[] = $rowKey;
            } catch (\Exception $e) {
                Log::error('Error saat memproses baris Excel: ' . $e->getMessage());
                continue;
            }
        }

        // Insert only the unique rows
        foreach ($uniqueRows as $row) {
            try {
                AccountManager::create($row);
                $this->importedCount++;
                Log::info('Account Manager created successfully', $row);
            } catch (\Exception $e) {
                Log::error('Error saat menyimpan Account Manager: ' . $e->getMessage(), $row);
            }
        }

        return collect([
            'imported' => $this->importedCount,
            'duplicates' => $this->duplicateCount,
            'existing' => count($existingEntries)
        ]);
    }

    /**
     * Rules validasi untuk data Excel
     */
    public function rules(): array
    {
        return [
            '*.nama' => 'required',
            '*.nik' => 'required',
            // Dinamis - menerima kolom witel/divisi atau witel_id/divisi_id
            '*.witel' => 'required_without:*.witel_id',
            '*.divisi' => 'required_without:*.divisi_id',
            '*.witel_id' => 'required_without:*.witel',
            '*.divisi_id' => 'required_without:*.divisi',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            '*.nama.required' => 'Kolom nama harus diisi',
            '*.nik.required' => 'Kolom NIK harus diisi',
            '*.witel.required_without' => 'Kolom witel harus diisi',
            '*.divisi.required_without' => 'Kolom divisi harus diisi',
            '*.witel_id.required_without' => 'Kolom witel_id harus diisi',
            '*.divisi_id.required_without' => 'Kolom divisi_id harus diisi',
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
