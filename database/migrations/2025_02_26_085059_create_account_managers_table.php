<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('account_managers', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('nama'); // Nama Account Manager
            $table->string('nik')->unique(); // Nomor Induk Karyawan (unik)
            $table->foreignId('witel_id')->constrained('witel')->onDelete('cascade'); // Mengacu ke tabel 'witel'
            $table->foreignId('divisi_id')->constrained('divisi')->onDelete('cascade'); // Foreign key ke 'divisi'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // ✅ DISABLE foreign key checks to prevent constraint violation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // Drop the table
            Schema::dropIfExists('account_managers');
        } finally {
            // ✅ ALWAYS re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
};