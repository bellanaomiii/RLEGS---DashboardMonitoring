<?php

namespace App\Imports;
use App\Models\AccountManager;
use Maatwebsite\Excel\Concerns\ToModel;
use App\Models\Witel;
use App\Models\Divisi;

class AccountManagerImport implements ToModel
{
    /**
     * Fungsi untuk memetakan setiap baris data pada file Excel ke model AccountManager
     */
    public function model(array $row)
    {
        // Pastikan Witel dan Divisi valid
        $witel = Witel::where('nama', $row[2])->firstOrFail(); // Witel (kolom C)
        $divisi = Divisi::where('nama', $row[3])->firstOrFail(); // Divisi (kolom D)

        return new AccountManager([
            'nama' => $row[0], // Nama Account Manager
            'nik' => $row[1], // NIK
            'witel_id' => $witel->id, // Witel
            'divisi_id' => $divisi->id, // Divisi
        ]);
    }
}
