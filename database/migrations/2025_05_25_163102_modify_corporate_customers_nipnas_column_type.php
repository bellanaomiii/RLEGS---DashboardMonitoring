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
        // ✅ Check if table exists before modifying
        if (!Schema::hasTable('corporate_customers')) {
            throw new \Exception('Table corporate_customers does not exist!');
        }

        // ✅ Check if column exists before modifying
        if (!Schema::hasColumn('corporate_customers', 'nipnas')) {
            throw new \Exception('Column nipnas does not exist in corporate_customers table!');
        }

        // ✅ Check current data to ensure no data loss
        $maxValue = DB::table('corporate_customers')->max('nipnas');
        if ($maxValue && $maxValue > 2147483647) { // Max integer value
            echo "⚠️ Warning: Found NIPNAS values larger than integer max. Conversion needed.\n";
        }

        // ✅ Modify column type
        Schema::table('corporate_customers', function (Blueprint $table) {
            // Change nipnas from integer to unsignedBigInteger
            $table->unsignedBigInteger('nipnas')->change();
        });

        echo "✅ Successfully changed nipnas column to unsignedBigInteger\n";

        // ✅ Optional: Add index for better performance if not exists
        if (!$this->indexExists('corporate_customers', 'corporate_customers_nipnas_index')) {
            Schema::table('corporate_customers', function (Blueprint $table) {
                $table->index('nipnas', 'corporate_customers_nipnas_index');
            });
            echo "✅ Added index on nipnas column\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('corporate_customers')) {
            return; // Table doesn't exist, nothing to rollback
        }

        if (!Schema::hasColumn('corporate_customers', 'nipnas')) {
            return; // Column doesn't exist, nothing to rollback
        }

        // ✅ Check if data can fit in integer before rollback
        $maxValue = DB::table('corporate_customers')->max('nipnas');
        if ($maxValue && $maxValue > 2147483647) { // Max integer value
            throw new \Exception(
                "Cannot rollback: Found NIPNAS values ($maxValue) larger than integer max (2147483647). " .
                "Data truncation would occur!"
            );
        }

        // ✅ Drop index if exists
        try {
            if ($this->indexExists('corporate_customers', 'corporate_customers_nipnas_index')) {
                Schema::table('corporate_customers', function (Blueprint $table) {
                    $table->dropIndex('corporate_customers_nipnas_index');
                });
                echo "✅ Dropped nipnas index\n";
            }
        } catch (\Exception $e) {
            echo "⚠️ Could not drop index: " . $e->getMessage() . "\n";
        }

        // ✅ Rollback column type
        Schema::table('corporate_customers', function (Blueprint $table) {
            // Rollback to integer (with data safety check above)
            $table->integer('nipnas')->change();
        });

        echo "✅ Successfully rolled back nipnas column to integer\n";
    }

    /**
     * ✅ Helper: Check if index exists
     */
    private function indexExists($tableName, $indexName)
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$tableName}` WHERE Key_name = ?", [$indexName]);
            return count($indexes) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
};