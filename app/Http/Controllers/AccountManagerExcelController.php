<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AccountManagerImport;

class AccountManagerExcelController extends Controller
{
    // Menangani proses impor data Account Manager dari Excel
    public function import(Request $request)
    {
        // Validasi file Excel yang diupload
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        // Impor data dari file Excel menggunakan Maatwebsite Excel
        Excel::import(new AccountManagerImport, $request->file('file'));

        // Mengembalikan response setelah impor selesai
        return redirect()->route('dashboard')->with('success', 'Data Account Manager berhasil diimpor!');
    }
}
