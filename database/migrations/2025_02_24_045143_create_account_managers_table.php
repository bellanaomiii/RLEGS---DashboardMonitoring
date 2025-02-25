<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_managers', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Kolom Identitas
            $table->string('nipnas')->nullable();
            $table->string('corporate_customer')->nullable();
            $table->string('segmen')->nullable();
            $table->string('treg_ho')->nullable();
            $table->string('group_konglo')->nullable();

            // Kolom Account Manager per bulan
            foreach (['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'ags', 'sep', 'okt', 'nov', 'des'] as $bulan) {
                $table->string("nik_am_$bulan")->nullable();
                $table->string("nama_am_$bulan")->nullable();
            }

            // Kolom lainnya
            $table->double('proporsi')->nullable();
            $table->string('witel_ho')->nullable();
            $table->string('witel_id')->nullable();
            $table->string('divisi')->nullable();
            $table->string('area')->nullable();
            $table->string('nik_mgr_area')->nullable();
            $table->string('mgr_area')->nullable();

            // Kolom performa Sustain
            foreach (['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'ags', 'sep', 'okt', 'nov', 'des'] as $bulan) {
                $table->double("t_sust_$bulan")->nullable();
            }
            $table->double('t_total_sustain')->nullable();

            // Kolom performa Scaling
            foreach (['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'ags', 'sep', 'okt', 'nov', 'des'] as $bulan) {
                $table->double("t_scal_$bulan")->nullable();
            }
            $table->double('t_total_scaling')->nullable();

            // Kolom Revenue
            foreach (['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'ags', 'sep', 'okt', 'nov', 'des'] as $bulan) {
                $table->double("t_revenue_$bulan")->nullable();
            }
            $table->double('total_target_revenue')->nullable();

            // Kolom Target NGTMA
            foreach (['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'ags', 'sep', 'okt', 'nov', 'des'] as $bulan) {
                $table->double("t_ngtma_$bulan")->nullable();
            }
            $table->double('total_target_ngtma')->nullable();

            // Estimasi Sustain POTS
            foreach (['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'] as $bulan) {
                $table->double("est_sust_{$bulan}_pots")->nullable();
            }
            $table->double('total_est_sust_pots')->nullable();

            // Estimasi Sustain NP
            foreach (['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'] as $bulan) {
                $table->double("est_sust_{$bulan}_np")->nullable();
            }
            $table->double('total_est_sust_np')->nullable();

            // Realisasi Sustain
            foreach (['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'ags', 'sep', 'okt', 'nov', 'des'] as $bulan) {
                $table->double("real_$bulan")->nullable();
            }
            $table->double('real_total')->nullable();

            // Realisasi NGTMA
            foreach (['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'ags', 'sep', 'okt', 'nov', 'des'] as $bulan) {
                $table->double("real_ngtma_$bulan")->nullable();
            }
            $table->double('real_ngtma')->nullable();

            // Data BC
            foreach (['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'] as $bulan) {
                $table->double("bc_$bulan")->nullable();
            }
            $table->double('total_bc')->nullable();

            // Timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_managers');
    }
};
