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
        Schema::table('corporate_customers', function (Blueprint $table) {
            // Ubah nipnas dari integer ke unsignedBigInteger
            $table->unsignedBigInteger('nipnas')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corporate_customers', function (Blueprint $table) {
            // Rollback ke integer (hati-hati dengan data besar!)
            $table->integer('nipnas')->change();
        });
    }
};