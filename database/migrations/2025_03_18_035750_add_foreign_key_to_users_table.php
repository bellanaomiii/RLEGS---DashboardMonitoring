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
        // Tambahkan foreign key constraint setelah semua tabel dibuat
        Schema::table('users', function (Blueprint $table) {
            // Pastikan tabel account_managers sudah ada sebelum menambahkan constraint
            if (Schema::hasTable('account_managers')) {
                // Cek apakah foreign key sudah ada
                $foreignKeyExists = $this->foreignKeyExists('users', 'account_manager_id');

                if (!$foreignKeyExists) {
                    $table->foreign('account_manager_id')
                        ->references('id')
                        ->on('account_managers')
                        ->onDelete('set null');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Cek dan hapus foreign key untuk account_manager_id
            try {
                $foreignKeys = Schema::getConnection()
                    ->getDoctrineSchemaManager()
                    ->listTableForeignKeys('users');

                $amFKExists = false;
                $amFKName = null;

                foreach ($foreignKeys as $foreignKey) {
                    if (in_array('account_manager_id', $foreignKey->getLocalColumns())) {
                        $amFKExists = true;
                        $amFKName = $foreignKey->getName();
                        break;
                    }
                }

                if ($amFKExists) {
                    if ($amFKName) {
                        // Gunakan nama FK yang ditemukan
                        DB::statement("ALTER TABLE users DROP FOREIGN KEY {$amFKName}");
                    } else {
                        // Fallback ke metode umum
                        $table->dropForeign(['account_manager_id']);
                    }
                }
            } catch (\Exception $e) {
                // Log error tapi jangan berhenti proses rollback
                \Illuminate\Support\Facades\Log::error("Error checking foreign key: " . $e->getMessage());
            }
        });
    }

    /**
     * Cek apakah foreign key sudah ada
     */
    private function foreignKeyExists($table, $column)
    {
        // Dapatkan nama foreign key yang diharapkan
        $expectedFKName = $table . '_' . $column . '_foreign';

        // Cara 1: Gunakan Laravel Schema Builder
        try {
            $foreignKeys = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableForeignKeys($table);

            foreach ($foreignKeys as $foreignKey) {
                if ($foreignKey->getName() === $expectedFKName) {
                    return true;
                }

                // Cek juga jika nama FK berisi nama kolom
                if (in_array($column, $foreignKey->getLocalColumns())) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            // Fallback ke raw query jika Doctrine gagal
            $constraintExists = DB::select(
                "SELECT COUNT(*) as count FROM information_schema.table_constraints
                WHERE table_name = ?
                AND constraint_name = ?",
                [$table, $expectedFKName]
            );

            return $constraintExists[0]->count > 0;
        }
    }
};