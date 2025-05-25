<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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


    public function down()
    {
        Schema::dropIfExists('account_managers');
    }
};
