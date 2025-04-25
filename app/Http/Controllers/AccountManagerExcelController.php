<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AccountManagerImport;
use Illuminate\Support\Facades\Log;
use App\Models\Witel;
use App\Models\Divisi;
use App\Models\Regional;

class AccountManagerExcelController extends Controller
{
    /**
     * Menangani proses impor data Account Manager dari Excel/CSV
     */
    public function import(Request $request)
    {
        try {
            // Validasi file yang diupload
            $request->validate([
                'file' => 'required|mimes:txt,xlsx,xls,csv|max:10240', // Max 10MB
            ]);

            // Debug log untuk tracking
            Log::info('Starting Excel/CSV import process', [
                'file_name' => $request->file('file')->getClientOriginalName(),
                'file_size' => $request->file('file')->getSize(),
                'file_type' => $request->file('file')->getMimeType()
            ]);

            // Pastikan master data tersedia
            $this->checkMasterData();

            // Impor data dari file Excel/CSV menggunakan Maatwebsite Excel
            $import = new AccountManagerImport;
            Excel::import($import, $request->file('file'));

            // Dapatkan informasi hasil import
            $results = $import->getImportResults();
            $importedCount = $results['imported'];
            $updatedCount = $results['updated'];
            $duplicateCount = $results['duplicates'];

            $message = "Data Account Manager berhasil diproses: $importedCount baru ditambahkan, $updatedCount diperbarui.";
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
            Log::info('Excel/CSV import completed successfully', $results);
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
            Log::error('Excel/CSV validation error', ['errors' => $errorMessages]);

            // Return JSON response untuk AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $errorMessages
                ], 422);
            }

            return redirect()->route('dashboard')->with('error', 'Error validasi Excel/CSV: ' . $errorMessage);

        } catch (\Exception $e) {
            // Untuk error umum lainnya
            Log::error('Import error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

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
     * Download template Excel/CSV
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

    /**
     * Memeriksa keberadaan master data yang diperlukan
     */
    private function checkMasterData()
    {
        $witelCount = Witel::count();
        $divisiCount = Divisi::count();
        $regionalCount = Regional::count();

        Log::info('Master data check', [
            'witel_count' => $witelCount,
            'divisi_count' => $divisiCount,
            'regional_count' => $regionalCount
        ]);

        if ($witelCount == 0) {
            throw new \Exception('Data Witel tidak tersedia. Harap tambahkan data Witel terlebih dahulu.');
        }

        if ($divisiCount == 0) {
            throw new \Exception('Data Divisi tidak tersedia. Harap tambahkan data Divisi terlebih dahulu.');
        }

        if ($regionalCount == 0) {
            throw new \Exception('Data Regional tidak tersedia. Harap tambahkan data Regional terlebih dahulu.');
        }
    }
}