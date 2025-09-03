<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * UPDATED: Support decimal + negatif + skala besar (requirement atasan)
     */
    public function up(): void
    {
        Schema::table('revenues', function (Blueprint $table) {
            // ✅ UBAH ke DECIMAL untuk support:
            // - Nilai negatif ✅
            // - Nilai desimal ✅
            // - Skala besar (triliun) ✅
            //
            // DECIMAL(20,2) breakdown:
            // - 20 digit total (18 sebelum koma + 2 sesudah koma)
            // - Maksimal: 999,999,999,999,999,999.99 (hampir 1 quintillion)
            // - Support negatif: -999,999,999,999,999,999.99
            $table->decimal('target_revenue', 20, 2)->change();
            $table->decimal('real_revenue', 20, 2)->change();
        });

        // ✅ Tambah index untuk optimasi query agregasi (opsional, tapi recommended)
        Schema::table('revenues', function (Blueprint $table) {
            // Index untuk query dashboard bulanan
            if (!$this->indexExists('revenues', 'idx_revenues_bulan_am')) {
                $table->index(['bulan', 'account_manager_id'], 'idx_revenues_bulan_am');
            }

            // ⚠️ HATI-HATI: Jangan tambah index di divisi_id karena sudah ada foreign key
            // Foreign key otomatis membuat index, jadi tidak perlu manual
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ✅ DISABLE foreign key checks untuk menghindari constraint issues
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // Drop indexes first (yang aman untuk di-drop)
            Schema::table('revenues', function (Blueprint $table) {
                try {
                    // Hanya drop index yang TIDAK digunakan oleh foreign key
                    if ($this->indexExists('revenues', 'idx_revenues_bulan_am')) {
                        $table->dropIndex('idx_revenues_bulan_am');
                    }

                    // ⚠️ JANGAN drop idx_revenues_divisi_bulan karena dibutuhkan foreign key
                    // Index ini akan otomatis terhapus ketika foreign key di-drop
                } catch (\Exception $e) {
                    echo "⚠️ Could not drop some indexes: " . $e->getMessage() . "\n";
                }
            });

            // Rollback column types
            Schema::table('revenues', function (Blueprint $table) {
                // Rollback ke bigInteger (masih support negatif tapi tidak desimal)
                $table->bigInteger('target_revenue')->change();
                $table->bigInteger('real_revenue')->change();
            });

        } finally {
            // ✅ ALWAYS re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Check if index exists
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