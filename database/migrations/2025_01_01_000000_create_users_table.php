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
    public function up(): void
    {
        // Periksa apakah tabel sudah ada untuk menghindari error duplikasi
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('role')->default('account_manager'); // Default role
                $table->unsignedBigInteger('account_manager_id')->nullable(); // Harus nullable
                $table->unsignedBigInteger('witel_id')->nullable(); // Untuk user witel
                $table->string('profile_image')->nullable();
                $table->string('admin_code')->nullable(); // Untuk verifikasi admin
                $table->rememberToken();
                $table->timestamps();

                // ✅ FIXED: Don't add foreign key here, will be added in separate migration
                // Foreign key akan ditambahkan di migration terpisah untuk menghindari konflik
            });
        }

        // Periksa apakah tabel sudah ada untuk menghindari error duplikasi
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        // Periksa apakah tabel sudah ada untuk menghindari error duplikasi
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ✅ DISABLE foreign key checks to prevent constraint violations
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // Drop tabel dengan urutan yang benar (tabel dengan foreign key terlebih dahulu)
            Schema::dropIfExists('sessions');
            Schema::dropIfExists('password_reset_tokens');
            Schema::dropIfExists('users');
        } finally {
            // ✅ ALWAYS re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
};