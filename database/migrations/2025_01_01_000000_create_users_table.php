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

                // Foreign key untuk witel hanya ditambahkan jika tabel witel sudah ada
                if (Schema::hasTable('witel')) {
                    $table->foreign('witel_id')
                        ->references('id')
                        ->on('witel')
                        ->onDelete('set null');
                }
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
        // Hapus tabel dengan urutan yang benar (tabel dengan foreign key terlebih dahulu)
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');

        // Hapus foreign key terlebih dahulu jika ada
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Cek apakah foreign key constraint ada sebelum mencoba menghapusnya
                if (Schema::hasColumn('users', 'witel_id')) {
                    // Gunakan try-catch untuk menangani jika foreign key tidak ada
                    try {
                        $table->dropForeign(['witel_id']);
                    } catch (\Exception $e) {
                        // Foreign key tidak ada, lanjutkan
                    }
                }

                // Hapus foreign key account_manager_id jika ada
                if (Schema::hasColumn('users', 'account_manager_id')) {
                    try {
                        $table->dropForeign(['account_manager_id']);
                    } catch (\Exception $e) {
                        // Foreign key tidak ada, lanjutkan
                    }
                }
            });
        }

        // Hapus tabel users
        Schema::dropIfExists('users');
    }
};