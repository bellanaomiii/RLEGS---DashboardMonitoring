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
        Schema::table('revenues', function (Blueprint $table) {
            // Check if column doesn't already exist
            if (!Schema::hasColumn('revenues', 'divisi_id')) {
                // Add divisi_id column after account_manager_id (nullable first)
                $table->unsignedBigInteger('divisi_id')->nullable()->after('account_manager_id');
            }
        });

        // Populate divisi_id with data from account_manager_divisi pivot table or default
        $this->populateDivisiIdInRevenues();

        // Make divisi_id NOT NULL and add foreign key constraint
        Schema::table('revenues', function (Blueprint $table) {
            // Make column NOT NULL
            $table->unsignedBigInteger('divisi_id')->nullable(false)->change();

            // Add foreign key constraint
            $table->foreign('divisi_id')
                ->references('id')
                ->on('divisi')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('revenues', function (Blueprint $table) {
            // Check if foreign key exists before trying to drop it
            try {
                $table->dropForeign(['divisi_id']);
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }

            // Check if column exists before trying to drop it
            if (Schema::hasColumn('revenues', 'divisi_id')) {
                $table->dropColumn('divisi_id');
            }
        });
    }

    /**
     * Populate divisi_id in revenues table
     */
    private function populateDivisiIdInRevenues()
    {
        // Get revenues that don't have divisi_id set
        $revenuesWithoutDivisi = DB::table('revenues')
            ->whereNull('divisi_id')
            ->select('id', 'account_manager_id')
            ->get();

        foreach ($revenuesWithoutDivisi as $revenue) {
            $divisiId = null;

            // Try to get divisi_id from pivot table first
            if (Schema::hasTable('account_manager_divisi')) {
                $divisiId = DB::table('account_manager_divisi')
                    ->where('account_manager_id', $revenue->account_manager_id)
                    ->value('divisi_id');
            }

            // If no divisi found in pivot table, try from account_managers table (if column still exists)
            if (!$divisiId && Schema::hasColumn('account_managers', 'divisi_id')) {
                $divisiId = DB::table('account_managers')
                    ->where('id', $revenue->account_manager_id)
                    ->value('divisi_id');
            }

            // If still no divisi found, use first available divisi as default
            if (!$divisiId) {
                $divisiId = DB::table('divisi')->first()->id ?? 1;
            }

            // Update the revenue record
            DB::table('revenues')
                ->where('id', $revenue->id)
                ->update(['divisi_id' => $divisiId]);
        }

        // Remove any revenues with invalid divisi_id (where divisi doesn't exist)
        $validDivisiIds = DB::table('divisi')->pluck('id')->toArray();
        if (!empty($validDivisiIds)) {
            DB::table('revenues')
                ->whereNotIn('divisi_id', $validDivisiIds)
                ->delete();
        }

        // Log for debugging
        $totalRevenues = DB::table('revenues')->count();
        $revenuesWithDivisi = DB::table('revenues')->whereNotNull('divisi_id')->count();

        echo "✅ Populated divisi_id for revenues: {$revenuesWithDivisi}/{$totalRevenues}\n";
    }
};