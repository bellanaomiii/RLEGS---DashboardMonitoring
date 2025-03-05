<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Divisi;

class DivisiSeeder extends Seeder
{
    public function run()
    {
        // Daftar divisi yang akan ditambahkan
        $divisiList = ['DGS', 'DPS', 'DSS'];

        // Membuat 3 divisi saja tanpa kaitan ke Witel
        foreach ($divisiList as $divisi) {
            Divisi::create([
                'nama' => $divisi
            ]);
        }
    }
}
