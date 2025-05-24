<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Step 1: Create the pivot table
        Schema::create('account_manager_divisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_manager_id')->constrained('account_managers')->onDelete('cascade');
            $table->foreignId('divisi_id')->constrained('divisi')->onDelete('cascade');
            $table->timestamps();

            // Prevent duplicate relationships
            $table->unique(['account_manager_id', 'divisi_id']);
        });

        // Step 2: Migrate existing data from account_managers.divisi_id to pivot table
        if (Schema::hasColumn('account_managers', 'divisi_id')) {
            $existingRelations = DB::table('account_managers')
                ->whereNotNull('divisi_id')
                ->select('id', 'divisi_id')
                ->get();

            foreach ($existingRelations as $relation) {
                // Insert relationship into pivot table
                try {
                    DB::table('account_manager_divisi')->insert([
                        'account_manager_id' => $relation->id,
                        'divisi_id' => $relation->divisi_id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } catch (\Exception $e) {
                    // Relationship might already exist, continue
                }
            }
        }
    }

    public function down()
    {
        // Step 1: Before dropping pivot table, migrate data back to account_managers.divisi_id (if column exists)
        if (Schema::hasColumn('account_managers', 'divisi_id')) {
            // Get first divisi for each account manager from pivot table
            $pivotData = DB::table('account_manager_divisi')
                ->select('account_manager_id', DB::raw('MIN(divisi_id) as divisi_id'))
                ->groupBy('account_manager_id')
                ->get();

            // Update account_managers table
            foreach ($pivotData as $data) {
                DB::table('account_managers')
                    ->where('id', $data->account_manager_id)
                    ->update(['divisi_id' => $data->divisi_id]);
            }
        } else {
            // If divisi_id column doesn't exist, create it temporarily for data migration
            Schema::table('account_managers', function (Blueprint $table) {
                $table->unsignedBigInteger('divisi_id')->nullable()->after('witel_id');
            });

            // Get data from pivot table
            $pivotData = DB::table('account_manager_divisi')
                ->select('account_manager_id', DB::raw('MIN(divisi_id) as divisi_id'))
                ->groupBy('account_manager_id')
                ->get();

            // Populate the divisi_id column
            foreach ($pivotData as $data) {
                DB::table('account_managers')
                    ->where('id', $data->account_manager_id)
                    ->update(['divisi_id' => $data->divisi_id]);
            }

            // Set default for any NULL values
            $defaultDivisiId = DB::table('divisi')->first()->id ?? 1;
            DB::table('account_managers')
                ->whereNull('divisi_id')
                ->update(['divisi_id' => $defaultDivisiId]);
        }

        // Step 2: Drop the pivot table
        Schema::dropIfExists('account_manager_divisi');
    }
};