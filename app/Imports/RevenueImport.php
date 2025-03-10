<?php

namespace App\Imports;

use App\Models\Revenue;
use App\Models\AccountManager;
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

class RevenueImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures, RemembersRowNumber;

    private $importedCount = 0;
    private $duplicateCount = 0;
    private $errorCount = 0;
    private $errorDetails = [];
    private $processedRows = [];

    /**
     * Import data as collection for pre-processing
     */
    public function collection(Collection $rows)
    {
        // Ambil semua Account Manager dan Corporate Customer yang ada di database
        // Menggunakan nama sebagai key untuk mempermudah pencarian
        $accountManagers = AccountManager::all()->pluck('id', 'nama')->toArray();
        $corporateCustomers = CorporateCustomer::all()->pluck('id', 'nama')->toArray();

        // Log jumlah data yang ditemukan
        Log::info('Ditemukan ' . count($accountManagers) . ' Account Manager dan ' . count($corporateCustomers) . ' Corporate Customer di database');

        foreach ($rows as $index => $row) {
            try {
                // Get values from excel
                $accountManagerName = trim($row['account_manager'] ?? '');
                $corporateCustomerName = trim($row['corporate_customer'] ?? '');
                $targetRevenue = floatval($row['target_revenue'] ?? 0);
                $realRevenue = floatval($row['real_revenue'] ?? 0);
                $bulan = $this->formatMonthYear($row['bulan'] ?? '');

                // Skip jika ada field yang kosong
                if (empty($accountManagerName) || empty($corporateCustomerName) || empty($bulan)) {
                    $this->errorCount++;
                    $this->errorDetails[] = "Baris " . ($index + 2) . ": Data tidak lengkap. Pastikan semua kolom diisi.";
                    continue;
                }

                // Cari ID Account Manager berdasarkan nama
                $accountManagerId = $accountManagers[$accountManagerName] ?? null;
                if (!$accountManagerId) {
                    $this->errorCount++;
                    $this->errorDetails[] = "Baris " . ($index + 2) . ": Account Manager '$accountManagerName' tidak ditemukan dalam database.";
                    continue;
                }

                // Cari ID Corporate Customer berdasarkan nama
                $corporateCustomerId = $corporateCustomers[$corporateCustomerName] ?? null;
                if (!$corporateCustomerId) {
                    $this->errorCount++;
                    $this->errorDetails[] = "Baris " . ($index + 2) . ": Corporate Customer '$corporateCustomerName' tidak ditemukan dalam database.";
                    continue;
                }

                // Buat key unik untuk cek duplikasi dalam file Excel yang sama
                $rowKey = $accountManagerId . '|' . $corporateCustomerId . '|' . $bulan;

                // Cek apakah ada data yang sama dalam file Excel ini
                if (in_array($rowKey, $this->processedRows)) {
                    $this->duplicateCount++;
                    $this->errorDetails[] = "Baris " . ($index + 2) . ": Duplikat data (kombinasi Account Manager, Corporate Customer, dan bulan sudah ada dalam file ini).";
                    continue;
                }

                // Ekstrak tahun dan bulan dari tanggal yang diformat
                $dateParts = explode('-', $bulan);
                $year = $dateParts[0];
                $month = $dateParts[1];

                // Cek apakah data sudah ada di database
                $existingRevenue = Revenue::where('account_manager_id', $accountManagerId)
                    ->where('corporate_customer_id', $corporateCustomerId)
                    ->whereYear('bulan', $year)
                    ->whereMonth('bulan', $month)
                    ->first();

                if ($existingRevenue) {
                    // Update data yang sudah ada
                    $existingRevenue->update([
                        'target_revenue' => $targetRevenue,
                        'real_revenue' => $realRevenue
                    ]);
                    $this->importedCount++;

                    // Untuk logging
                    Log::info("Updated revenue: AM ID $accountManagerId, CC ID $corporateCustomerId, Bulan $bulan");
                } else {
                    // Buat data baru dengan tanggal lengkap (YYYY-MM-DD)
                    Revenue::create([
                        'account_manager_id' => $accountManagerId,
                        'corporate_customer_id' => $corporateCustomerId,
                        'target_revenue' => $targetRevenue,
                        'real_revenue' => $realRevenue,
                        'bulan' => $bulan . '-01' // Tambahkan hari (01) untuk format tanggal lengkap
                    ]);
                    $this->importedCount++;

                    // Untuk logging
                    Log::info("Created new revenue: AM ID $accountManagerId, CC ID $corporateCustomerId, Bulan $bulan-01");
                }

                // Tandai row ini sudah diproses
                $this->processedRows[] = $rowKey;

            } catch (\Exception $e) {
                $this->errorCount++;
                $this->errorDetails[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
                Log::error('Error importing revenue row: ' . $e->getMessage(), [
                    'row' => $row,
                    'index' => $index
                ]);
            }
        }

        Log::info('Selesai import revenue. Berhasil: ' . $this->importedCount . ', Duplikat: ' . $this->duplicateCount . ', Error: ' . $this->errorCount);

        return collect([
            'imported' => $this->importedCount,
            'duplicates' => $this->duplicateCount,
            'errors' => $this->errorCount,
            'error_details' => $this->errorDetails
        ]);
    }

    /**
     * Format string bulan/tahun menjadi format YYYY-MM
     */
    private function formatMonthYear($monthYear)
    {
        // Coba format MM/YYYY
        if (preg_match('/^(\d{1,2})\/(\d{4})$/', $monthYear, $matches)) {
            $month = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $year = $matches[2];
            return $year . '-' . $month;
        }

        // Coba format YYYY-MM
        if (preg_match('/^(\d{4})-(\d{1,2})$/', $monthYear, $matches)) {
            $year = $matches[1];
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            return $year . '-' . $month;
        }

        // Coba format MM-YYYY
        if (preg_match('/^(\d{1,2})-(\d{4})$/', $monthYear, $matches)) {
            $month = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $year = $matches[2];
            return $year . '-' . $month;
        }

        // Jika tidak bisa di-parse, throw exception
        throw new \Exception("Format bulan tidak valid: $monthYear. Gunakan format MM/YYYY (contoh: 01/2023)");
    }

    /**
     * Aturan validasi
     */
    public function rules(): array
    {
        return [
            '*.account_manager' => 'required|string',
            '*.corporate_customer' => 'required|string',
            '*.target_revenue' => 'required|numeric|min:0',
            '*.real_revenue' => 'required|numeric|min:0',
            '*.bulan' => 'required|string',
        ];
    }

    /**
     * Pesan validasi kustom
     */
    public function customValidationMessages()
    {
        return [
            '*.account_manager.required' => 'Kolom account_manager wajib diisi.',
            '*.corporate_customer.required' => 'Kolom corporate_customer wajib diisi.',
            '*.target_revenue.required' => 'Kolom target_revenue wajib diisi.',
            '*.target_revenue.numeric' => 'Kolom target_revenue harus berupa angka.',
            '*.target_revenue.min' => 'Kolom target_revenue tidak boleh negatif.',
            '*.real_revenue.required' => 'Kolom real_revenue wajib diisi.',
            '*.real_revenue.numeric' => 'Kolom real_revenue harus berupa angka.',
            '*.real_revenue.min' => 'Kolom real_revenue tidak boleh negatif.',
            '*.bulan.required' => 'Kolom bulan wajib diisi.',
        ];
    }

    /**
     * Mendapatkan hasil import
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
