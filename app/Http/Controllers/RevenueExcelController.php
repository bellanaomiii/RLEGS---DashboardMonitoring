<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RevenueImport;
use App\Exports\RevenueExport;
use App\Exports\RevenueTemplateExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\AccountManager;
use App\Models\CorporateCustomer;
use App\Models\Divisi;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ImportRevenueJob;
use Illuminate\Support\Str;

class RevenueExcelController extends Controller
{
    /**
     * Import data dari file Excel menggunakan queue
     */
    public function import(Request $request)
    {
        // Tingkatkan batas eksekusi dan memori
        ini_set('max_execution_time', 300); // 5 menit
        ini_set('memory_limit', '512M');    // Tingkatkan memori

        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengimpor data revenue.'
                ], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengimpor data revenue.');
        }

        try {
            // Validasi file yang diupload
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv|max:10240', // Max 10MB
            ]);

            // Ambil file
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();

            // Log informasi file untuk debugging
            Log::info('File Excel diupload', [
                'file_name' => $originalName,
                'file_size' => $file->getSize(),
                'file_type' => $file->getMimeType()
            ]);

            // Pastikan master data tersedia
            $this->checkMasterData();

            // Buat nama file yang aman untuk disimpan
            $safeName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME), '_') . '.' . $file->getClientOriginalExtension();

            // Simpan file ke sistem file
            // Menggunakan public path agar lebih mudah diakses
            $publicPath = 'imports/' . $safeName;
            $file->move(public_path('imports'), $safeName);

            // Verifikasi file telah disimpan
            if (!file_exists(public_path($publicPath))) {
                throw new \Exception("File gagal disimpan ke public/imports");
            }

            Log::info('File telah disimpan', [
                'public_path' => $publicPath,
                'full_path' => public_path($publicPath),
                'exists' => file_exists(public_path($publicPath))
            ]);

            // Opsi 1: Gunakan Queue (uncomment untuk menggunakan)
            ImportRevenueJob::dispatch($publicPath, Auth::id(), $originalName);
            $message = 'File excel sedang diproses di background. Proses ini bisa memakan waktu beberapa menit tergantung ukuran file.';

            // Opsi 2: Proses langsung tanpa queue (uncomment untuk debugging)
            /*
            $import = new RevenueImport();
            Excel::import($import, public_path($publicPath));
            $results = $import->getImportResults();

            $importedCount = $results['imported'];
            $duplicateCount = $results['duplicates'];
            $errorCount = $results['errors'];

            $message = "$importedCount data Revenue berhasil diimpor.";
            if ($duplicateCount > 0) {
                $message .= " $duplicateCount data duplikat diperbarui.";
            }
            if ($errorCount > 0) {
                $message .= " $errorCount data gagal diimpor.";
            }
            */

            // Return JSON response untuk AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'processing' => true
                ]);
            }

            // Mengembalikan response
            Log::info('Import diproses', [
                'public_path' => $publicPath,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('revenue.data')->with('info', $message);

        } catch (\Exception $e) {
            // Log error detail
            Log::error('Error saat memulai import: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return JSON response untuk AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memulai import data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('revenue.data')->with('error', 'Gagal memulai import data: ' . $e->getMessage());
        }
    }

    /**
     * Endpoint untuk memeriksa status import
     */
    public function checkImportStatus()
    {
        $userId = Auth::id();

        // Cek apakah ada hasil import di cache
        if (\Illuminate\Support\Facades\Cache::has("import_result_" . $userId)) {
            $result = \Illuminate\Support\Facades\Cache::pull("import_result_" . $userId); // get and delete

            return response()->json([
                'status' => 'completed',
                'success' => true,
                'message' => $result['message'],
                'error_details' => $result['error_details'] ?? []
            ]);
        }

        // Cek apakah ada error import di cache
        if (\Illuminate\Support\Facades\Cache::has("import_error_" . $userId)) {
            $error = \Illuminate\Support\Facades\Cache::pull("import_error_" . $userId); // get and delete

            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => $error['message']
            ]);
        }

        // Cek apakah ada kegagalan import di cache
        if (\Illuminate\Support\Facades\Cache::has("import_failed_" . $userId)) {
            $failed = \Illuminate\Support\Facades\Cache::pull("import_failed_" . $userId); // get and delete

            return response()->json([
                'status' => 'failed',
                'success' => false,
                'message' => $failed['message']
            ]);
        }

        // Jika tidak ada hasil, berarti masih diproses
        return response()->json([
            'status' => 'processing',
            'message' => 'Import masih diproses. Silakan coba lagi dalam beberapa saat.'
        ]);
    }

    /**
     * Export data revenue ke Excel
     */
    public function export()
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('revenue.data')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengekspor data revenue.');
        }

        try {
            Log::info('Memulai proses export Excel Revenue');
            return Excel::download(new RevenueExport, 'revenue-data-' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            return redirect()->route('revenue.data')->with('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }

    /**
     * Download template Excel untuk Revenue
     */
    public function downloadTemplate()
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('revenue.data')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mendownload template.');
        }

        try {
            Log::info('Mendownload template Excel Revenue');
            return Excel::download(new RevenueTemplateExport, 'template-revenue-' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Template error: ' . $e->getMessage());
            return redirect()->route('revenue.data')->with('error', 'Gagal mendownload template: ' . $e->getMessage());
        }
    }

    /**
     * Memeriksa keberadaan master data yang diperlukan
     */
    private function checkMasterData()
    {
        $accountManagerCount = AccountManager::count();
        $corporateCustomerCount = CorporateCustomer::count();
        $divisiCount = Divisi::count();

        Log::info('Master data check', [
            'account_manager_count' => $accountManagerCount,
            'corporate_customer_count' => $corporateCustomerCount,
            'divisi_count' => $divisiCount
        ]);

        if ($accountManagerCount == 0) {
            throw new \Exception('Data Account Manager tidak tersedia. Harap tambahkan data Account Manager terlebih dahulu.');
        }

        if ($corporateCustomerCount == 0) {
            throw new \Exception('Data Corporate Customer tidak tersedia. Harap tambahkan data Corporate Customer terlebih dahulu.');
        }

        if ($divisiCount == 0) {
            throw new \Exception('Data Divisi tidak tersedia. Harap tambahkan data Divisi terlebih dahulu.');
        }
    }
}