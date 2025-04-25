<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Regional;
use Illuminate\Support\Facades\DB;

class RegionalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nonaktifkan foreign key checks sementara
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Gunakan delete daripada truncate yang lebih aman
        DB::table('regional')->delete();

        // Data Regional (TREG 1 sampai TREG 7)
        $regionals = [
            ['nama' => 'TREG 1'],
            ['nama' => 'TREG 2'],
            ['nama' => 'TREG 3'],
            ['nama' => 'TREG 4'],
            ['nama' => 'TREG 5'],
            ['nama' => 'TREG 6'],
            ['nama' => 'TREG 7'],
        ];

        // Insert data
        foreach ($regionals as $regional) {
            Regional::create($regional);
        }

        // Aktifkan kembali foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}