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
        // Get all existing account managers and corporate customers
        $accountManagers = AccountManager::all()->pluck('id', 'nama')->toArray();
        $corporateCustomers = CorporateCustomer::all()->pluck('id', 'nama')->toArray();

        foreach ($rows as $index => $row) {
            try {
                // Get values from excel
                $accountManagerName = trim($row['account_manager'] ?? '');
                $corporateCustomerName = trim($row['corporate_customer'] ?? '');
                $targetRevenue = floatval($row['target_revenue'] ?? 0);
                $realRevenue = floatval($row['real_revenue'] ?? 0);
                $bulan = $this->formatMonthYear($row['bulan'] ?? '');

                // Skip if any required field is empty
                if (empty($accountManagerName) || empty($corporateCustomerName) || empty($bulan)) {
                    $this->errorCount++;
                    $this->errorDetails[] = "Baris " . ($index + 2) . ": Data tidak lengkap";
                    continue;
                }

                // Look up account manager and corporate customer IDs
                $accountManagerId = $accountManagers[$accountManagerName] ?? null;
                $corporateCustomerId = $corporateCustomers[$corporateCustomerName] ?? null;

                if (!$accountManagerId) {
                    $this->errorCount++;
                    $this->errorDetails[] = "Baris " . ($index + 2) . ": Account Manager '$accountManagerName' tidak ditemukan";
                    continue;
                }

                if (!$corporateCustomerId) {
                    $this->errorCount++;
                    $this->errorDetails[] = "Baris " . ($index + 2) . ": Corporate Customer '$corporateCustomerName' tidak ditemukan";
                    continue;
                }

                // Create a unique key for this row to detect duplicates
                $rowKey = $accountManagerId . '|' . $corporateCustomerId . '|' . $bulan;

                // Check if this is a duplicate within the Excel file
                if (in_array($rowKey, $this->processedRows)) {
                    $this->duplicateCount++;
                    continue;
                }

                // Check if data already exists in the database
                $existingRevenue = Revenue::where('account_manager_id', $accountManagerId)
                    ->where('corporate_customer_id', $corporateCustomerId)
                    ->where('bulan', $bulan)
                    ->first();

                if ($existingRevenue) {
                    // Update existing record
                    $existingRevenue->update([
                        'target_revenue' => $targetRevenue,
                        'real_revenue' => $realRevenue
                    ]);
                    $this->importedCount++;
                } else {
                    // Create new record
                    Revenue::create([
                        'account_manager_id' => $accountManagerId,
                        'corporate_customer_id' => $corporateCustomerId,
                        'target_revenue' => $targetRevenue,
                        'real_revenue' => $realRevenue,
                        'bulan' => $bulan
                    ]);
                    $this->importedCount++;
                }

                // Mark this row as processed
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

        return collect([
            'imported' => $this->importedCount,
            'duplicates' => $this->duplicateCount,
            'errors' => $this->errorCount,
            'error_details' => $this->errorDetails
        ]);
    }

    /**
     * Format month/year string to YYYY-MM format
     */
    private function formatMonthYear($monthYear)
    {
        // Try MM/YYYY format
        if (preg_match('/^(\d{1,2})\/(\d{4})$/', $monthYear, $matches)) {
            $month = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $year = $matches[2];
            return $year . '-' . $month;
        }

        // Try YYYY-MM format
        if (preg_match('/^(\d{4})-(\d{1,2})$/', $monthYear, $matches)) {
            $year = $matches[1];
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            return $year . '-' . $month;
        }

        // Try MM-YYYY format
        if (preg_match('/^(\d{1,2})-(\d{4})$/', $monthYear, $matches)) {
            $month = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $year = $matches[2];
            return $year . '-' . $month;
        }

        // If can't parse, throw exception
        throw new \Exception("Format bulan tidak valid: $monthYear. Gunakan format MM/YYYY");
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            '*.account_manager' => 'required|string',
            '*.corporate_customer' => 'required|string',
            '*.target_revenue' => 'required|numeric',
            '*.real_revenue' => 'required|numeric',
            '*.bulan' => 'required|string',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            '*.account_manager.required' => 'Kolom account_manager wajib diisi.',
            '*.corporate_customer.required' => 'Kolom corporate_customer wajib diisi.',
            '*.target_revenue.required' => 'Kolom target_revenue wajib diisi.',
            '*.target_revenue.numeric' => 'Kolom target_revenue harus berupa angka.',
            '*.real_revenue.required' => 'Kolom real_revenue wajib diisi.',
            '*.real_revenue.numeric' => 'Kolom real_revenue harus berupa angka.',
            '*.bulan.required' => 'Kolom bulan wajib diisi.',
        ];
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
