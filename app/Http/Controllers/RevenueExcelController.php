<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RevenueImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
            $errorDetails = $results['error_details'];

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
        // Cek apakah file template sudah ada
        $filePath = public_path('templates/Template_Revenue.xlsx');

        // Jika file belum ada, generate template
        if (!file_exists($filePath)) {
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
            $sheet->setCellValue('A2', 'Nama Account Manager');
            $sheet->setCellValue('B2', 'Nama Corporate Customer');
            $sheet->setCellValue('C2', '100000000');
            $sheet->setCellValue('D2', '95000000');
            $sheet->setCellValue('E2', '01/2023');

            // Style the header
            $sheet->getStyle('A1:E1')->getFont()->setBold(true);
            $sheet->getStyle('A1:E1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('DDEBF7');

            // Auto-size columns
            foreach(range('A','E') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Create the directory if it doesn't exist
            if (!file_exists(public_path('templates'))) {
                mkdir(public_path('templates'), 0755, true);
            }

            // Save the file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filePath);
        }

        if (file_exists($filePath)) {
            return response()->download($filePath);
        } else {
            return redirect()->route('dashboard')->with('error', 'Template tidak ditemukan.');
        }
    }
}
