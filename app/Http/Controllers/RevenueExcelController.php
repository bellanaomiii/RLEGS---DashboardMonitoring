<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RevenueImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\AccountManager;
use App\Models\CorporateCustomer;

class RevenueExcelController extends Controller
{
    /**
     * Import data dari file Excel
     */
    public function import(Request $request)
    {
        try {
            // Validasi file Excel yang diupload
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv',
            ]);

            // Debug log untuk tracking
            Log::info('Mulai proses import Excel Revenue');

            // Impor data dari file Excel menggunakan Maatwebsite Excel
            $import = new RevenueImport;
            Excel::import($import, $request->file('file'));

            // Dapatkan informasi hasil import
            $results = $import->getImportResults();
            $importedCount = $results['imported'];
            $duplicateCount = $results['duplicates'];
            $errorCount = $results['errors'];
            $errorDetails = $results['error_details'] ?? [];

            $message = "$importedCount data Revenue berhasil diimpor.";
            if ($duplicateCount > 0) {
                $message .= " $duplicateCount data duplikat dilewati.";
            }
            if ($errorCount > 0) {
                $message .= " $errorCount data gagal diimpor.";
            }

            // Return JSON response untuk AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $results,
                    'error_details' => $errorDetails
                ]);
            }

            // Mengembalikan response setelah impor selesai
            Log::info('Import Excel Revenue berhasil: ' . $message);
            return redirect()->route('dashboard')->with('success', $message);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Khusus untuk error validasi Excel
            $failures = $e->failures();

            $errorMessages = [];
            foreach ($failures as $failure) {
                $row = $failure->row();
                $column = $failure->attribute();
                $error = $failure->errors()[0];
                $errorMessages[] = "Baris $row, kolom $column: $error";
            }

            $errorMessage = implode("<br>", $errorMessages);
            Log::error('Excel validation error: ' . $errorMessage);

            // Return JSON response untuk AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $errorMessages
                ], 422);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Error validasi Excel: ' . $errorMessage);

        } catch (\Exception $e) {
            // Untuk error umum lainnya
            Log::error('Import error: ' . $e->getMessage());

            // Return JSON response untuk AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengimpor data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('dashboard')->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    /**
     * Download template Excel untuk Revenue
     */
    public function downloadTemplate()
    {
        try {
            // Cek apakah file template sudah ada
            $filePath = public_path('templates/Template_Revenue.xlsx');
            $templateDirectory = public_path('templates');

            // Create the directory if it doesn't exist
            if (!file_exists($templateDirectory)) {
                mkdir($templateDirectory, 0755, true);
            }

            // Dapatkan beberapa contoh Account Manager dan Corporate Customer yang ada di database
            $accountManagerExample = AccountManager::first() ? AccountManager::first()->nama : 'Nama Account Manager';
            $corporateCustomerExample = CorporateCustomer::first() ? CorporateCustomer::first()->nama : 'Nama Corporate Customer';

            // Create example Excel template
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $sheet->setCellValue('A1', 'account_manager');
            $sheet->setCellValue('B1', 'corporate_customer');
            $sheet->setCellValue('C1', 'target_revenue');
            $sheet->setCellValue('D1', 'real_revenue');
            $sheet->setCellValue('E1', 'bulan');

            // Add example data
            $sheet->setCellValue('A2', $accountManagerExample);
            $sheet->setCellValue('B2', $corporateCustomerExample);
            $sheet->setCellValue('C2', '100000000');
            $sheet->setCellValue('D2', '95000000');
            $sheet->setCellValue('E2', '01/2023');

            // Add notes
            $sheet->setCellValue('A4', 'Catatan:');
            $sheet->setCellValue('A5', '- Kolom account_manager harus sama persis dengan nama Account Manager yang ada di database');
            $sheet->setCellValue('A6', '- Kolom corporate_customer harus sama persis dengan nama Corporate Customer yang ada di database');
            $sheet->setCellValue('A7', '- Format bulan adalah MM/YYYY (contoh: 01/2023 untuk Januari 2023)');
            $sheet->setCellValue('A8', '- Data tidak boleh duplikat (kombinasi account_manager, corporate_customer, dan bulan harus unik)');

            // Style the header
            $sheet->getStyle('A1:E1')->getFont()->setBold(true);
            $sheet->getStyle('A1:E1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('DDEBF7');

            // Style the notes
            $sheet->getStyle('A4')->getFont()->setBold(true);
            $sheet->mergeCells('A5:E5');
            $sheet->mergeCells('A6:E6');
            $sheet->mergeCells('A7:E7');
            $sheet->mergeCells('A8:E8');

            // Auto-size columns
            foreach(range('A','E') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Save the file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filePath);

            if (file_exists($filePath)) {
                return response()->download($filePath);
            } else {
                return redirect()->route('dashboard')->with('error', 'Template tidak dapat dibuat.');
            }
        } catch (\Exception $e) {
            Log::error('Error generating template: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Gagal membuat template: ' . $e->getMessage());
        }
    }
}
