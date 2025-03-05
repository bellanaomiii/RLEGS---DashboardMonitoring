<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountManager;
use App\Models\Witel;
use App\Models\Divisi;

class AccountManagerController extends Controller
{
    // Menampilkan form untuk menambah Account Manager dan mengirim data Witel dan Divisi
    public function create()
    {
        // Ambil data Witel dan Divisi untuk dikirim ke view
        $witels = Witel::all(); // Mengambil semua data Witel
        $divisi = Divisi::all(); // Mengambil semua data Divisi

        // Kirim data Witel dan Divisi ke view
        return view('dashboard', compact('witels', 'divisi'));
    }

    // Menyimpan Account Manager baru
    public function store(Request $request)
    {
        // Validasi data input
        $request->validate([
            'nama' => 'required|string|unique:account_managers,nama',
            'nik' => 'required|digits:5|unique:account_managers,nik', // Validasi NIK 5 digit
            'witel_id' => 'required|exists:witel,id', // Pastikan witel_id ada
            'divisi_id' => 'required|exists:divisi,id', // Pastikan divisi_id ada
        ]);

        // Membuat Account Manager baru
        AccountManager::create([
            'nama' => $request->nama,
            'nik' => $request->nik,
            'witel_id' => $request->witel_id,  // Menambahkan witel_id
            'divisi_id' => $request->divisi_id, // Menambahkan divisi_id
        ]);

        // Mengembalikan response dengan status sukses
        return redirect()->route('dashboard')->with('success', 'Account Manager berhasil ditambahkan!');
    }
}
