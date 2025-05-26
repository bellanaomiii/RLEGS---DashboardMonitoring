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
        Schema::table('revenues', function (Blueprint $table) {
            // Ubah target_revenue dari integer ke unsignedBigInteger
            $table->unsignedBigInteger('target_revenue')->change();

            // Ubah real_revenue dari integer ke unsignedBigInteger
            $table->unsignedBigInteger('real_revenue')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('revenues', function (Blueprint $table) {
            // Rollback ke integer (hati-hati dengan data besar!)
            $table->integer('target_revenue')->change();
            $table->integer('real_revenue')->change();
        });
    }
};