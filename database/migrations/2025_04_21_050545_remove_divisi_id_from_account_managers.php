<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('account_managers', function (Blueprint $table) {
            $table->dropForeign(['divisi_id']);
            $table->dropColumn('divisi_id');
        });
    }

    public function down()
    {
        Schema::table('account_managers', function (Blueprint $table) {
            $table->foreignId('divisi_id')->constrained('divisi')->onDelete('cascade');
        });
    }
};