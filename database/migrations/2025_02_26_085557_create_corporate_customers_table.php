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
        Schema::create('corporate_customers', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('nama'); // Nama Corporate Customer
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('corporate_customers');
    }

};
