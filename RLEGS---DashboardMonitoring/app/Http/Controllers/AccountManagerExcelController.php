<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AccountManagerImport;
use Illuminate\Support\Facades\Log;

class AccountManagerExcelController extends Controller
{
    /**
     * Menangani proses impor data Account Manager dari Excel
     */
    public function import(Request $request)
    {
        try {
            // Validasi file Excel yang diupload
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv',
            ]);

            // Debug log untuk tracking
            Log::info('Mulai proses import Excel');

            // Impor data dari file Excel menggunakan Maatwebsite Excel
            $import = new AccountManagerImport;
            Excel::import($import, $request->file('file'));

            // Dapatkan informasi hasil import
            $results = $import->getImportResults();
            $importedCount = $results['imported'];
            $duplicateCount = $results['duplicates'];

            $message = "$importedCount data Account Manager berhasil diimpor.";
            if ($duplicateCount > 0) {
                $message .= " $duplicateCount data duplikat dilewati.";
            }

            // Return JSON response untuk AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $results
                ]);
            }

            // Mengembalikan response setelah impor selesai
            Log::info('Import Excel berhasil');
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

            return redirect()->route('dashboard')->with('error', 'Error validasi Excel: ' . $errorMessage);

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
     * Download template Excel
     */
    public function downloadTemplate()
    {
        $filePath = public_path('templates/Template_Account_Manager.xlsx');

        if (file_exists($filePath)) {
            return response()->download($filePath);
        } else {
            return redirect()->route('dashboard')->with('error', 'Template tidak ditemukan.');
        }
    }
}
