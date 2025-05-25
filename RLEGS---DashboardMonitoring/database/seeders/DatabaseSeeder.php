<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Urutan seeder sangat penting!
        $this->call([
            // Admin user pertama
            AdminUserSeeder::class,

            // Data master untuk Witel dan Divisi
            WitelSeeder::class,
            DivisiSeeder::class,

            // Seeder lain jika diperlukan
        ]);
    }
}