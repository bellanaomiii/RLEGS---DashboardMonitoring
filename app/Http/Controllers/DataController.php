<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountManager;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AccountManagerImport;

class DataController extends Controller
{
    // Fungsi untuk upload data
    public function uploadData(Request $request)
    {
        // Validasi file
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048', // Batasi jenis file dan ukuran
        ]);

        // Proses upload dan import file Excel
        if ($request->file('file')->isValid()) {
            $file = $request->file('file');
            // Menggunakan import untuk membaca data dari Excel
            Excel::import(new AccountManagerImport, $file);

            return back()->with('success', 'File uploaded and data imported successfully!');
        } else {
            return back()->withErrors('Invalid file.');
        }
    }

    // Menampilkan data yang telah diupload
    public function viewUploadedData()
    {
        // Ambil data dari model AccountManager
        $data = AccountManager::all();  // Jika data kosong, ini akan mengirimkan koleksi kosong

        // Kirim data ke view
        return view('dashboard', compact('data'));  // Pastikan 'data' dikirim dengan benar
    }



}
