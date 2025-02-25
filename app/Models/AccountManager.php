<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountManager extends Model
{
    protected $fillable = [
        'name', 'nik', 'witel_id', 'divisi_id', 'area',
        'target_sust', 'target_scaling',
        'T_Sust_Jan', 'T_Sust_Feb', 'T_Sust_Mar', 'T_Sust_Apr', 'T_Sust_Mei',
        'T_Sust_Jun', 'T_Sust_Jul', 'T_Sust_Ags', 'T_Sust_Sep', 'T_Sust_Okt',
        'T_Sust_Nov', 'T_Sust_Des', 'T_Total_Sustain',
        'T_Scal_Jan', 'T_Scal_Feb', 'T_Scal_Mar', 'T_Scal_Apr', 'T_Scal_Mei',
        'T_Scal_Jun', 'T_Scal_Jul', 'T_Scal_Ags', 'T_Scal_Sep', 'T_Scal_Okt',
        'T_Scal_Nov', 'T_Scal_Des', 'T_Total_Scaling',
        'T_Revenue_Jan', 'T_Revenue_Feb', 'T_Revenue_Mar', 'T_Revenue_Apr',
        'T_Revenue_Mei', 'T_Revenue_Jun', 'T_Revenue_Jul', 'T_Revenue_Ags',
        'T_Revenue_Sep', 'T_Revenue_Okt', 'T_Revenue_Nov', 'T_Revenue_Des',
        'Total_Target_Revenue',
        'T_NGTMA_Jan', 'T_NGTMA_Feb', 'T_NGTMA_Mar', 'T_NGTMA_Apr', 'T_NGTMA_Mei',
        'T_NGTMA_Jun', 'T_NGTMA_Jul', 'T_NGTMA_Ags', 'T_NGTMA_Sep', 'T_NGTMA_Okt',
        'T_NGTMA_Nov', 'T_NGTMA_Des', 'Total_Target_NGTMA',
        'Est_Sust_Jan_POTS', 'Est_Sust_Feb_POTS', 'Est_Sust_Mar_POTS', 'Est_Sust_Apr_POTS',
        'Est_Sust_Mei_POTS', 'Est_Sust_Jun_POTS', 'Est_Sust_Jul_POTS', 'Est_Sust_Agu_POTS',
        'Est_Sust_Sep_POTS', 'Est_Sust_Okt_POTS', 'Est_Sust_Nov_POTS', 'Est_Sust_Des_POTS',
        'TOTAL_Est_Sust_POTS', 'Est_Sust_Jan_NP', 'Est_Sust_Feb_NP', 'Est_Sust_Mar_NP',
        'Est_Sust_Apr_NP', 'Est_Sust_Mei_NP', 'Est_Sust_Jun_NP', 'Est_Sust_Jul_NP',
        'Est_Sust_Agu_NP', 'Est_Sust_Sep_NP', 'Est_Sust_Okt_NP', 'Est_Sust_Nov_NP',
        'Est_Sust_Des_NP', 'TOTAL_Est_Sust_NP', 'Real_Jan', 'Real_Feb', 'Real_Mar', 'Real_Apr',
        'Real_Mei', 'Real_Jun', 'Real_Jul', 'Real_Ags', 'Real_Sep', 'Real_Okt', 'Real_Nov',
        'Real_Des', 'Real_Total', 'Real_NGTMA_Jan', 'Real_NGTMA_Feb', 'Real_NGTMA_Mar',
        'Real_NGTMA_Apr', 'Real_NGTMA_Mei', 'Real_NGTMA_Jun', 'Real_NGTMA_Jul', 'Real_NGTMA_Ags',
        'Real_NGTMA_Sep', 'Real_NGTMA_Okt', 'Real_NGTMA_Nov', 'Real_NGTMA_Des', 'Real_NGTMA',
        'R_Jan_2023', 'R_Feb_2023', 'R_Mar_2023', 'R_Apr_2023', 'R_Mei_2023', 'R_Jun_2023',
        'R_Jul_2023', 'R_Agu_2023', 'R_Sep_2023', 'R_Okt_2023', 'R_Nov_2023', 'R_Des_2023',
        'Real_Total_2023', 'BC_Jan', 'BC_Feb', 'BC_Mar', 'BC_Apr', 'BC_Mei', 'BC_Jun', 'BC_Jul',
        'BC_Agu', 'BC_Sep', 'BC_Okt', 'BC_Nov', 'BC_Des', 'TOTAL_BC',
    ];

}
