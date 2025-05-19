<?php

namespace App\Imports;

use App\Models\AccountManager;
use App\Models\Witel;
use App\Models\Divisi;
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

class AccountManagerImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures, RemembersRowNumber;

    private $importedCount = 0;
    private $updatedCount = 0;
    private $duplicateCount = 0;
    private $processedRows = [];
    private $witels = [];
    private $regionals = [];
    private $divisis = [];

    public function __construct()
    {
        // Preload master data untuk lookup yang lebih cepat
        $this->loadMasterData();
    }

    /**
     * Load master data untuk witel, regional, dan divisi
     */
    private function loadMasterData()
    {
        // Load witel data
        $witels = Witel::all();
        foreach ($witels as $witel) {
            $this->witels[strtoupper($witel->nama)] = $witel->id;
        }

        // Load regional data
        $regionals = Regional::all();
        foreach ($regionals as $regional) {
            $this->regionals[strtoupper($regional->nama)] = $regional->id;
        }

        // Load divisi data
        $divisis = Divisi::all();
        foreach ($divisis as $divisi) {
            $this->divisis[strtoupper($divisi->nama)] = $divisi->id;
        }

        Log::info('Master data loaded', [
            'witels' => count($this->witels),
            'regionals' => count($this->regionals),
            'divisis' => count($this->divisis)
        ]);
    }

    /**
     * Import data as collection for pre-processing
     */
    public function collection(Collection $rows)
    {
        // Track unique entries based on NIK
        $accountManagerData = [];
        $existingEntries = [];

        // Get all existing account managers by NIK
        $existingAccountManagers = AccountManager::all()->pluck('id', 'nik')->toArray();

        Log::info('Starting import of ' . count($rows) . ' rows');

        // Identify column names in the CSV (case-insensitive)
        $columnMap = $this->identifyColumns($rows->first());

        Log::info('Identified columns', $columnMap);

        // LANGKAH 1: Mengumpulkan semua data untuk setiap AM berdasarkan NIK
        foreach ($rows as $index => $row) {
            try {
                // Skip first row if it's a header
                if ($index === 0 && isset($row['NIK']) && $row['NIK'] === 'NIK') {
                    continue;
                }

                // Extract values using the column map
                $nik = $this->extractValue($row, $columnMap, 'nik');
                $nama = $this->extractValue($row, $columnMap, 'nama_am');
                $witelName = $this->extractValue($row, $columnMap, 'witel_ho');
                $regionalName = $this->extractValue($row, $columnMap, 'regional');
                $divisiName = $this->extractValue($row, $columnMap, 'divisi');

                // Skip if any required field is empty
                if (empty($nik) || empty($nama) || empty($witelName) || empty($regionalName) || empty($divisiName)) {
                    Log::warning('Incomplete row in CSV', [
                        'row' => $index + 2, // +2 for Excel row number (1-based index + header)
                        'nik' => $nik,
                        'nama' => $nama,
                        'witel' => $witelName,
                        'regional' => $regionalName,
                        'divisi' => $divisiName
                    ]);
                    continue;
                }

                // Find witel_id by name
                $witelId = $this->findWitelId($witelName);
                if (!$witelId) {
                    Log::warning("Witel '{$witelName}' not found in database", ['row' => $index + 2]);
                    continue;
                }

                // Find regional_id by name
                $regionalId = $this->findRegionalId($regionalName);
                if (!$regionalId) {
                    Log::warning("Regional '{$regionalName}' not found in database", ['row' => $index + 2]);
                    continue;
                }

                // Find divisi_id by name
                $divisiId = $this->findDivisiId($divisiName);
                if (!$divisiId) {
                    Log::warning("Divisi '{$divisiName}' not found in database", ['row' => $index + 2]);
                    continue;
                }

                // Create a unique key for this AM based on NIK
                if (!isset($accountManagerData[$nik])) {
                    // Initialize data for this NIK if it doesn't exist yet
                    $accountManagerData[$nik] = [
                        'nama' => $nama,
                        'witel_id' => $witelId,
                        'regional_id' => $regionalId,
                        'divisi_ids' => [], // Array untuk menyimpan semua ID divisi
                        'is_existing' => array_key_exists($nik, $existingAccountManagers),
                        'account_manager_id' => array_key_exists($nik, $existingAccountManagers) ? $existingAccountManagers[$nik] : null
                    ];
                }

                // Tambahkan divisi_id ke array jika belum ada
                if (!in_array($divisiId, $accountManagerData[$nik]['divisi_ids'])) {
                    $accountManagerData[$nik]['divisi_ids'][] = $divisiId;
                } else {
                    $this->duplicateCount++;
                    Log::info('Duplicate divisi found for NIK in CSV file', ['row' => $index + 2, 'nik' => $nik, 'divisi' => $divisiName]);
                }

                // Tandai baris ini sudah diproses
                $this->processedRows[] = "$nik-$divisiId";

            } catch (\Exception $e) {
                Log::error('Error processing CSV row: ' . $e->getMessage(), [
                    'row' => $index + 2,
                    'exception' => $e
                ]);
                continue;
            }
        }

        // LANGKAH 2: Proses semua data yang sudah dikumpulkan
        foreach ($accountManagerData as $nik => $data) {
            try {
                if ($data['is_existing']) {
                    // Update existing account manager
                    $accountManager = AccountManager::find($data['account_manager_id']);

                    if ($accountManager) {
                        // Update data dasar
                        $accountManager->update([
                            'nama' => $data['nama'],
                            'witel_id' => $data['witel_id'],
                            'regional_id' => $data['regional_id'],
                        ]);

                        // SYNC divisi - akan mengganti semua relasi dengan yang baru
                        $accountManager->divisis()->sync($data['divisi_ids']);

                        $this->updatedCount++;
                        Log::info('Account Manager updated with multiple divisi', [
                            'nik' => $nik,
                            'id' => $data['account_manager_id'],
                            'divisi_count' => count($data['divisi_ids']),
                            'divisi_ids' => $data['divisi_ids']
                        ]);
                    }
                } else {
                    // Create new account manager
                    $newData = [
                        'nama' => $data['nama'],
                        'nik' => $nik,
                        'witel_id' => $data['witel_id'],
                        'regional_id' => $data['regional_id'],
                    ];

                    $accountManager = AccountManager::create($newData);

                    // Attach all divisi
                    $accountManager->divisis()->attach($data['divisi_ids']);

                    $this->importedCount++;
                    Log::info('New Account Manager created with multiple divisi', [
                        'nik' => $nik,
                        'id' => $accountManager->id,
                        'divisi_count' => count($data['divisi_ids']),
                        'divisi_ids' => $data['divisi_ids']
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error saving Account Manager: ' . $e->getMessage(), [
                    'nik' => $nik,
                    'exception' => $e
                ]);
            }
        }

        Log::info('Import completed', [
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
            'nik' => null,
            'nama_am' => null,
            'witel_ho' => null,
            'regional' => null,
            'divisi' => null
        ];

        if (!$firstRow) {
            return $map;
        }

        foreach ($firstRow as $key => $value) {
            $upperKey = strtoupper($key);

            if ($upperKey === 'NIK') {
                $map['nik'] = $key;
            } elseif ($upperKey === 'NAMA AM' || $upperKey === 'NAMA_AM') {
                $map['nama_am'] = $key;
            } elseif ($upperKey === 'WITEL HO' || $upperKey === 'WITEL_HO') {
                $map['witel_ho'] = $key;
            } elseif ($upperKey === 'REGIONAL') {
                $map['regional'] = $key;
            } elseif ($upperKey === 'DIVISI') {
                $map['divisi'] = $key;
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
        if ($field === 'nik' && isset($row['NIK'])) {
            return trim((string)$row['NIK']);
        } elseif ($field === 'nama_am' && isset($row['NAMA AM'])) {
            return trim((string)$row['NAMA AM']);
        } elseif ($field === 'witel_ho' && isset($row['WITEL HO'])) {
            return trim((string)$row['WITEL HO']);
        } elseif ($field === 'regional' && isset($row['REGIONAL'])) {
            return trim((string)$row['REGIONAL']);
        } elseif ($field === 'divisi' && isset($row['DIVISI'])) {
            return trim((string)$row['DIVISI']);
        }

        return null;
    }

    /**
     * Find Witel ID by name
     */
    private function findWitelId($witelName)
    {
        if (empty($witelName)) {
            return null;
        }

        $witelNameUpper = strtoupper(trim($witelName));

        // Direct lookup by exact match
        if (isset($this->witels[$witelNameUpper])) {
            return $this->witels[$witelNameUpper];
        }

        // Fuzzy lookup if exact match failed
        foreach ($this->witels as $name => $id) {
            if (strpos($name, $witelNameUpper) !== false || strpos($witelNameUpper, $name) !== false) {
                return $id;
            }
        }

        // Fallback to database lookup
        $witel = Witel::where('nama', 'like', "%{$witelName}%")->first();
        return $witel ? $witel->id : null;
    }

    /**
     * Find Regional ID by name
     */
    private function findRegionalId($regionalName)
    {
        if (empty($regionalName)) {
            return null;
        }

        $regionalNameUpper = strtoupper(trim($regionalName));

        // Direct lookup by exact match
        if (isset($this->regionals[$regionalNameUpper])) {
            return $this->regionals[$regionalNameUpper];
        }

        // Fuzzy lookup if exact match failed
        foreach ($this->regionals as $name => $id) {
            if (strpos($name, $regionalNameUpper) !== false || strpos($regionalNameUpper, $name) !== false) {
                return $id;
            }
        }

        // Fallback to database lookup
        $regional = Regional::where('nama', 'like', "%{$regionalName}%")->first();
        return $regional ? $regional->id : null;
    }

    /**
     * Find Divisi ID by name
     */
    private function findDivisiId($divisiName)
    {
        if (empty($divisiName)) {
            return null;
        }

        $divisiNameUpper = strtoupper(trim($divisiName));

        // Direct lookup by exact match
        if (isset($this->divisis[$divisiNameUpper])) {
            return $this->divisis[$divisiNameUpper];
        }

        // Fuzzy lookup if exact match failed
        foreach ($this->divisis as $name => $id) {
            if (strpos($name, $divisiNameUpper) !== false || strpos($divisiNameUpper, $name) !== false) {
                return $id;
            }
        }

        // Fallback to database lookup
        $divisi = Divisi::where('nama', 'like', "%{$divisiName}%")->first();
        return $divisi ? $divisi->id : null;
    }

    /**
     * Rules validasi untuk data Excel
     */
    public function rules(): array
    {
        return [
            // Semua kolom dibuat nullable agar validasi bisa flexible
            '*.NIK' => 'nullable',
            '*.NAMA AM' => 'nullable',
            '*.WITEL HO' => 'nullable',
            '*.REGIONAL' => 'nullable',
            '*.DIVISI' => 'nullable',
            // Untuk lowercase
            '*.nik' => 'nullable',
            '*.nama_am' => 'nullable',
            '*.witel_ho' => 'nullable',
            '*.regional' => 'nullable',
            '*.divisi' => 'nullable'
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