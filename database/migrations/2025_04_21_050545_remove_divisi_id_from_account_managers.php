<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Step 1: Migrate existing divisi_id data to pivot table (if pivot table exists)
        if (Schema::hasTable('account_manager_divisi') && Schema::hasColumn('account_managers', 'divisi_id')) {
            // Get existing relationships
            $existingRelations = DB::table('account_managers')
                ->whereNotNull('divisi_id')
                ->select('id', 'divisi_id')
                ->get();

            // Insert into pivot table
            foreach ($existingRelations as $relation) {
                // Check if relationship doesn't already exist in pivot table
                $exists = DB::table('account_manager_divisi')
                    ->where('account_manager_id', $relation->id)
                    ->where('divisi_id', $relation->divisi_id)
                    ->exists();

                if (!$exists) {
                    DB::table('account_manager_divisi')->insert([
                        'account_manager_id' => $relation->id,
                        'divisi_id' => $relation->divisi_id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }

        // Step 2: Remove the divisi_id column and its foreign key
        Schema::table('account_managers', function (Blueprint $table) {
            // Check if foreign key exists before trying to drop it
            try {
                $table->dropForeign(['divisi_id']);
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }

            // Check if column exists before trying to drop it
            if (Schema::hasColumn('account_managers', 'divisi_id')) {
                $table->dropColumn('divisi_id');
            }
        });
    }

    public function down()
    {
        // Step 1: Add divisi_id column back (nullable first)
        Schema::table('account_managers', function (Blueprint $table) {
            if (!Schema::hasColumn('account_managers', 'divisi_id')) {
                $table->unsignedBigInteger('divisi_id')->nullable()->after('witel_id');
            }
        });

        // Step 2: Migrate data back from pivot table to divisi_id column
        if (Schema::hasTable('account_manager_divisi')) {
            // Get data from pivot table (take first divisi for each account manager)
            $pivotData = DB::table('account_manager_divisi')
                ->select('account_manager_id', DB::raw('MIN(divisi_id) as divisi_id'))
                ->groupBy('account_manager_id')
                ->get();

            // Update account_managers with divisi_id
            foreach ($pivotData as $data) {
                DB::table('account_managers')
                    ->where('id', $data->account_manager_id)
                    ->update(['divisi_id' => $data->divisi_id]);
            }
        }

        // Step 3: Set default divisi_id for any NULL values
        $defaultDivisiId = DB::table('divisi')->first()->id ?? 1;
        DB::table('account_managers')
            ->whereNull('divisi_id')
            ->update(['divisi_id' => $defaultDivisiId]);

        // Step 4: Make divisi_id NOT NULL and add foreign key constraint
        Schema::table('account_managers', function (Blueprint $table) {
            // Make column NOT NULL
            $table->unsignedBigInteger('divisi_id')->nullable(false)->change();

            // Add foreign key constraint
            $table->foreign('divisi_id')
                ->references('id')
                ->on('divisi')
                ->onDelete('cascade');
        });
    }
};