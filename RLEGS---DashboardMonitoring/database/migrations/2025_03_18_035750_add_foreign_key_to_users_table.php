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
        // Tambahkan foreign key constraint setelah semua tabel dibuat
        Schema::table('users', function (Blueprint $table) {
            // Pastikan tabel account_managers sudah ada sebelum menambahkan constraint
            if (Schema::hasTable('account_managers')) {
                $table->foreign('account_manager_id')
                    ->references('id')
                    ->on('account_managers')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['account_manager_id']);
        });
    }
};