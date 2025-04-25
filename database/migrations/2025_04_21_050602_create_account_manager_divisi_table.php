<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('account_manager_divisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_manager_id')->constrained('account_managers')->onDelete('cascade');
            $table->foreignId('divisi_id')->constrained('divisi')->onDelete('cascade');
            $table->timestamps();

            // Batasi agar tidak ada duplikasi relasi antara AM dan divisi
            $table->unique(['account_manager_id', 'divisi_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_manager_divisi');
    }
};