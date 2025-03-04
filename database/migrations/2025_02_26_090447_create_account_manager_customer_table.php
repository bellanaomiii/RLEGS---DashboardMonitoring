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
        Schema::create('account_manager_customer', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('account_manager_id')->constrained('account_managers')->onDelete('cascade');
            $table->foreignId('corporate_customer_id')->constrained('corporate_customers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_manager_customer');
    }

};
