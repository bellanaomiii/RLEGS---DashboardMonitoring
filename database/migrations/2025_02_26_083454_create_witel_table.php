<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('witel', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('nama'); // Nama WITEL
            $table->timestamps(); // created_at & updated_at
        });
    }


    public function down()
    {
        Schema::dropIfExists('witel');
    }

};
