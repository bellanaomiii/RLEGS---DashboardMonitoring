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
        Schema::create('regional', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique(); // Nama regional (TREG 1, TREG 2, dst)
            $table->timestamps();
        });

        // Tambahkan kolom regional_id ke tabel account_managers
        Schema::table('account_managers', function (Blueprint $table) {
            $table->foreignId('regional_id')->nullable()->after('witel_id')
                  ->constrained('regional')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus foreign key dan kolom terlebih dahulu
        Schema::table('account_managers', function (Blueprint $table) {
            $table->dropForeign(['regional_id']);
            $table->dropColumn('regional_id');
        });

        Schema::dropIfExists('regional');
    }
};