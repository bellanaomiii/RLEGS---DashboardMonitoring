<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Nonaktifkan foreign key checks untuk menghindari masalah constraint
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // Jalankan seeder dasar terlebih dahulu (yang tidak memiliki dependensi)
            $this->call([
                WitelSeeder::class,
                DivisiSeeder::class,
                RegionalSeeder::class,
            ]);

            // Jika ada AdminUserSeeder, jalankan terakhir karena mungkin bergantung pada data lain
            if (class_exists(\Database\Seeders\AdminUserSeeder::class)) {
                $this->call(AdminUserSeeder::class);
            }
        } catch (\Exception $e) {
            // Log error
            \Illuminate\Support\Facades\Log::error("Error saat menjalankan seeder: " . $e->getMessage());

            // Tampilkan error
            $this->command->error("Error: " . $e->getMessage());
        } finally {
            // Pastikan foreign key checks selalu diaktifkan kembali, bahkan jika terjadi error
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}