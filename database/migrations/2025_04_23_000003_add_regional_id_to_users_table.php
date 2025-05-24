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
        Schema::table('users', function (Blueprint $table) {
            // Check if column doesn't already exist
            if (!Schema::hasColumn('users', 'regional_id')) {
                // Add regional_id column after witel_id
                $table->unsignedBigInteger('regional_id')->nullable()->after('witel_id');
            }
        });

        // Add foreign key constraint in separate step
        Schema::table('users', function (Blueprint $table) {
            // Only add foreign key if regional table exists and FK doesn't exist
            if (Schema::hasTable('regional') && !$this->foreignKeyExists('users', 'regional_id')) {
                $table->foreign('regional_id')
                    ->references('id')
                    ->on('regional')
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
            // ✅ SAFELY drop foreign key first
            try {
                if ($this->foreignKeyExists('users', 'regional_id')) {
                    $table->dropForeign(['regional_id']);
                }
            } catch (\Exception $e) {
                // Foreign key doesn't exist, continue
            }

            // ✅ Drop column if exists
            if (Schema::hasColumn('users', 'regional_id')) {
                $table->dropColumn('regional_id');
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