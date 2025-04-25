<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('revenues', function (Blueprint $table) {
            // Tambahkan kolom divisi_id setelah account_manager_id
            $table->foreignId('divisi_id')->after('account_manager_id')->constrained('divisi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('revenues', function (Blueprint $table) {
            // Hapus foreign key dan kolom
            $table->dropForeign(['divisi_id']);
            $table->dropColumn('divisi_id');
        });
    }
};