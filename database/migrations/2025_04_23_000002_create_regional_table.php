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
        Schema::create('regional', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique(); // Nama regional (TREG 1, TREG 2, dst)
            $table->timestamps();
        });

        // Add regional_id column to account_managers table
        Schema::table('account_managers', function (Blueprint $table) {
            // Check if column doesn't already exist
            if (!Schema::hasColumn('account_managers', 'regional_id')) {
                $table->unsignedBigInteger('regional_id')->nullable()->after('witel_id');
            }
        });

        // Add foreign key constraint in separate step
        Schema::table('account_managers', function (Blueprint $table) {
            // Only add foreign key if it doesn't exist
            if (!$this->foreignKeyExists('account_managers', 'regional_id')) {
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
        // ✅ SAFELY drop foreign key and column from account_managers first
        Schema::table('account_managers', function (Blueprint $table) {
            try {
                if ($this->foreignKeyExists('account_managers', 'regional_id')) {
                    $table->dropForeign(['regional_id']);
                }
            } catch (\Exception $e) {
                // Foreign key doesn't exist, continue
            }

            if (Schema::hasColumn('account_managers', 'regional_id')) {
                $table->dropColumn('regional_id');
            }
        });

        // ✅ Drop regional table
        Schema::dropIfExists('regional');
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