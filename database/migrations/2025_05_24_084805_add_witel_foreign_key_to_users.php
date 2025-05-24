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
        // Add witel_id foreign key to users table
        Schema::table('users', function (Blueprint $table) {
            // Only add foreign key if witel table exists and FK doesn't exist
            if (Schema::hasTable('witel') && !$this->foreignKeyExists('users', 'witel_id')) {
                $table->foreign('witel_id')
                    ->references('id')
                    ->on('witel')
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
            // ✅ SAFELY drop foreign key
            try {
                if ($this->foreignKeyExists('users', 'witel_id')) {
                    $table->dropForeign(['witel_id']);
                }
            } catch (\Exception $e) {
                // Foreign key doesn't exist, continue
            }
        });
    }

    /**
     * Check if foreign key exists
     */
    private function foreignKeyExists($table, $column)
    {
        try {
            $foreignKeys = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableForeignKeys($table);

            foreach ($foreignKeys as $foreignKey) {
                if (in_array($column, $foreignKey->getLocalColumns())) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            // Fallback to raw query if Doctrine fails
            try {
                $constraintExists = DB::select(
                    "SELECT COUNT(*) as count FROM information_schema.table_constraints tc
                    JOIN information_schema.key_column_usage kcu ON tc.constraint_name = kcu.constraint_name
                    WHERE tc.table_name = ? AND kcu.column_name = ? AND tc.constraint_type = 'FOREIGN KEY'",
                    [$table, $column]
                );

                return $constraintExists[0]->count > 0;
            } catch (\Exception $e2) {
                return false;
            }
        }
    }
};