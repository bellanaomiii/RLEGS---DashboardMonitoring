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
        Schema::create('account_managers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nik');
            $table->decimal('target_sust', 15, 2);
            $table->decimal('target_scaling', 15, 2);

            // Kolom untuk NIK dan NAMA AM di setiap bulan
            $table->string('NIK_AM_Jul');
            $table->string('NAMA_AM_Jul');
            $table->string('NIK_AM_Ags');
            $table->string('NAMA_AM_Ags');
            $table->string('NIK_AM_Sep');
            $table->string('NAMA_AM_Sep');
            $table->string('NIK_AM_Okt');
            $table->string('NAMA_AM_Okt');
            $table->string('NIK_AM_Nov');
            $table->string('NAMA_AM_Nov');
            $table->string('NIK_AM_Des');
            $table->string('NAMA_AM_Des');

            // Kolom untuk Proporsi, Witel dan Divisi
            $table->decimal('PROPORSI', 5, 2);
            $table->string('WITEL_HO');
            $table->string('WITEL_ID');
            $table->string('DIVISI');
            $table->string('AREA');
            $table->string('NIK_MGR_AREA');
            $table->string('MGR_AREA');

            // Kolom performa Sustain, Scaling, Revenue untuk setiap bulan
            $table->decimal('T_Sust_Jan', 15, 2);
            $table->decimal('T_Sust_Feb', 15, 2);
            $table->decimal('T_Sust_Mar', 15, 2);
            $table->decimal('T_Sust_Apr', 15, 2);
            $table->decimal('T_Sust_Mei', 15, 2);
            $table->decimal('T_Sust_Jun', 15, 2);
            $table->decimal('T_Sust_Jul', 15, 2);
            $table->decimal('T_Sust_Ags', 15, 2);
            $table->decimal('T_Sust_Sep', 15, 2);
            $table->decimal('T_Sust_Okt', 15, 2);
            $table->decimal('T_Sust_Nov', 15, 2);
            $table->decimal('T_Sust_Des', 15, 2);
            $table->decimal('T_Total_Sustain', 15, 2);

            $table->decimal('T_Scal_Jan', 15, 2);
            $table->decimal('T_Scal_Feb', 15, 2);
            $table->decimal('T_Scal_Mar', 15, 2);
            $table->decimal('T_Scal_Apr', 15, 2);
            $table->decimal('T_Scal_Mei', 15, 2);
            $table->decimal('T_Scal_Jun', 15, 2);
            $table->decimal('T_Scal_Jul', 15, 2);
            $table->decimal('T_Scal_Ags', 15, 2);
            $table->decimal('T_Scal_Sep', 15, 2);
            $table->decimal('T_Scal_Okt', 15, 2);
            $table->decimal('T_Scal_Nov', 15, 2);
            $table->decimal('T_Scal_Des', 15, 2);
            $table->decimal('T_Total_Scaling', 15, 2);

            $table->decimal('T_Revenue_Jan', 15, 2);
            $table->decimal('T_Revenue_Feb', 15, 2);
            $table->decimal('T_Revenue_Mar', 15, 2);
            $table->decimal('T_Revenue_Apr', 15, 2);
            $table->decimal('T_Revenue_Mei', 15, 2);
            $table->decimal('T_Revenue_Jun', 15, 2);
            $table->decimal('T_Revenue_Jul', 15, 2);
            $table->decimal('T_Revenue_Ags', 15, 2);
            $table->decimal('T_Revenue_Sep', 15, 2);
            $table->decimal('T_Revenue_Okt', 15, 2);
            $table->decimal('T_Revenue_Nov', 15, 2);
            $table->decimal('T_Revenue_Des', 15, 2);
            $table->decimal('Total_Target_Revenue', 15, 2);

            $table->decimal('T_NGTMA_Jan', 15, 2);
            $table->decimal('T_NGTMA_Feb', 15, 2);
            $table->decimal('T_NGTMA_Mar', 15, 2);
            $table->decimal('T_NGTMA_Apr', 15, 2);
            $table->decimal('T_NGTMA_Mei', 15, 2);
            $table->decimal('T_NGTMA_Jun', 15, 2);
            $table->decimal('T_NGTMA_Jul', 15, 2);
            $table->decimal('T_NGTMA_Ags', 15, 2);
            $table->decimal('T_NGTMA_Sep', 15, 2);
            $table->decimal('T_NGTMA_Okt', 15, 2);
            $table->decimal('T_NGTMA_Nov', 15, 2);
            $table->decimal('T_NGTMA_Des', 15, 2);
            $table->decimal('Total_Target_NGTMA', 15, 2);

            // Estimasi dan Real Sust
            $table->decimal('Est_Sust_Jan_POTS', 15, 2);
            $table->decimal('Est_Sust_Feb_POTS', 15, 2);
            $table->decimal('Est_Sust_Mar_POTS', 15, 2);
            $table->decimal('Est_Sust_Apr_POTS', 15, 2);
            $table->decimal('Est_Sust_Mei_POTS', 15, 2);
            $table->decimal('Est_Sust_Jun_POTS', 15, 2);
            $table->decimal('Est_Sust_Jul_POTS', 15, 2);
            $table->decimal('Est_Sust_Agu_POTS', 15, 2);
            $table->decimal('Est_Sust_Sep_POTS', 15, 2);
            $table->decimal('Est_Sust_Okt_POTS', 15, 2);
            $table->decimal('Est_Sust_Nov_POTS', 15, 2);
            $table->decimal('Est_Sust_Des_POTS', 15, 2);
            $table->decimal('TOTAL_Est_Sust_POTS', 15, 2);

            $table->decimal('Est_Sust_Jan_NP', 15, 2);
            $table->decimal('Est_Sust_Feb_NP', 15, 2);
            $table->decimal('Est_Sust_Mar_NP', 15, 2);
            $table->decimal('Est_Sust_Apr_NP', 15, 2);
            $table->decimal('Est_Sust_Mei_NP', 15, 2);
            $table->decimal('Est_Sust_Jun_NP', 15, 2);
            $table->decimal('Est_Sust_Jul_NP', 15, 2);
            $table->decimal('Est_Sust_Agu_NP', 15, 2);
            $table->decimal('Est_Sust_Sep_NP', 15, 2);
            $table->decimal('Est_Sust_Okt_NP', 15, 2);
            $table->decimal('Est_Sust_Nov_NP', 15, 2);
            $table->decimal('Est_Sust_Des_NP', 15, 2);
            $table->decimal('TOTAL_Est_Sust_NP', 15, 2);

            $table->decimal('Real_Jan', 15, 2);
            $table->decimal('Real_Feb', 15, 2);
            $table->decimal('Real_Mar', 15, 2);
            $table->decimal('Real_Apr', 15, 2);
            $table->decimal('Real_Mei', 15, 2);
            $table->decimal('Real_Jun', 15, 2);
            $table->decimal('Real_Jul', 15, 2);
            $table->decimal('Real_Ags', 15, 2);
            $table->decimal('Real_Sep', 15, 2);
            $table->decimal('Real_Okt', 15, 2);
            $table->decimal('Real_Nov', 15, 2);
            $table->decimal('Real_Des', 15, 2);
            $table->decimal('Real_Total', 15, 2);

            $table->decimal('Real_NGTMA_Jan', 15, 2);
            $table->decimal('Real_NGTMA_Feb', 15, 2);
            $table->decimal('Real_NGTMA_Mar', 15, 2);
            $table->decimal('Real_NGTMA_Apr', 15, 2);
            $table->decimal('Real_NGTMA_Mei', 15, 2);
            $table->decimal('Real_NGTMA_Jun', 15, 2);
            $table->decimal('Real_NGTMA_Jul', 15, 2);
            $table->decimal('Real_NGTMA_Ags', 15, 2);
            $table->decimal('Real_NGTMA_Sep', 15, 2);
            $table->decimal('Real_NGTMA_Okt', 15, 2);
            $table->decimal('Real_NGTMA_Nov', 15, 2);
            $table->decimal('Real_NGTMA_Des', 15, 2);
            $table->decimal('Real_NGTMA', 15, 2);

            $table->decimal('R_Jan_2023', 15, 2);
            $table->decimal('R_Feb_2023', 15, 2);
            $table->decimal('R_Mar_2023', 15, 2);
            $table->decimal('R_Apr_2023', 15, 2);
            $table->decimal('R_Mei_2023', 15, 2);
            $table->decimal('R_Jun_2023', 15, 2);
            $table->decimal('R_Jul_2023', 15, 2);
            $table->decimal('R_Agu_2023', 15, 2);
            $table->decimal('R_Sep_2023', 15, 2);
            $table->decimal('R_Okt_2023', 15, 2);
            $table->decimal('R_Nov_2023', 15, 2);
            $table->decimal('R_Des_2023', 15, 2);
            $table->decimal('Real_Total_2023', 15, 2);

            $table->decimal('BC_Jan', 15, 2);
            $table->decimal('BC_Feb', 15, 2);
            $table->decimal('BC_Mar', 15, 2);
            $table->decimal('BC_Apr', 15, 2);
            $table->decimal('BC_Mei', 15, 2);
            $table->decimal('BC_Jun', 15, 2);
            $table->decimal('BC_Jul', 15, 2);
            $table->decimal('BC_Agu', 15, 2);
            $table->decimal('BC_Sep', 15, 2);
            $table->decimal('BC_Okt', 15, 2);
            $table->decimal('BC_Nov', 15, 2);
            $table->decimal('BC_Des', 15, 2);
            $table->decimal('TOTAL_BC', 15, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_managers');
    }
};
