<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountManager extends Model
{
    protected $primaryKey = 'id';

    protected $fillable = [
        'nipnas',  // Ini yang menyebabkan error jika tidak ada
        'corporate_customer',
        'segmen',
        'treg_ho',
        'group_konglo',

        // Data Account Manager per bulan
        'nik_am_jan', 'nama_am_jan',
        'nik_am_feb', 'nama_am_feb',
        'nik_am_mar', 'nama_am_mar',
        'nik_am_apr', 'nama_am_apr',
        'nik_am_mei', 'nama_am_mei',
        'nik_am_jun', 'nama_am_jun',
        'nik_am_jul', 'nama_am_jul',
        'nik_am_ags', 'nama_am_ags',
        'nik_am_sep', 'nama_am_sep',
        'nik_am_okt', 'nama_am_okt',
        'nik_am_nov', 'nama_am_nov',
        'nik_am_des', 'nama_am_des',

        // Kolom tambahan
        'proporsi',
        'witel_ho',
        'witel_id',
        'divisi',
        'area',
        'nik_mgr_area',
        'mgr_area',

        // Kolom performa
        't_sust_jan', 't_sust_feb', 't_sust_mar', 't_sust_apr', 't_sust_mei',
        't_sust_jun', 't_sust_jul', 't_sust_ags', 't_sust_sep', 't_sust_okt',
        't_sust_nov', 't_sust_des', 't_total_sustain',

        't_scal_jan', 't_scal_feb', 't_scal_mar', 't_scal_apr', 't_scal_mei',
        't_scal_jun', 't_scal_jul', 't_scal_ags', 't_scal_sep', 't_scal_okt',
        't_scal_nov', 't_scal_des', 't_total_scaling',

        't_revenue_jan', 't_revenue_feb', 't_revenue_mar', 't_revenue_apr',
        't_revenue_mei', 't_revenue_jun', 't_revenue_jul', 't_revenue_ags',
        't_revenue_sep', 't_revenue_okt', 't_revenue_nov', 't_revenue_des',
        'total_target_revenue',

        't_ngtma_jan', 't_ngtma_feb', 't_ngtma_mar', 't_ngtma_apr', 't_ngtma_mei',
        't_ngtma_jun', 't_ngtma_jul', 't_ngtma_ags', 't_ngtma_sep', 't_ngtma_okt',
        't_ngtma_nov', 't_ngtma_des', 'total_target_ngtma',

        'real_jan', 'real_feb', 'real_mar', 'real_apr',
        'real_mei', 'real_jun', 'real_jul', 'real_ags',
        'real_sep', 'real_okt', 'real_nov', 'real_des', 'real_total',

        'bc_jan', 'bc_feb', 'bc_mar', 'bc_apr', 'bc_mei',
        'bc_jun', 'bc_jul', 'bc_agu', 'bc_sep', 'bc_okt', 'bc_nov', 'bc_des',
        'total_bc',
    ];
}
