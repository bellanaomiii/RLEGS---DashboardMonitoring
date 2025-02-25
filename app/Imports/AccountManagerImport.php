<?php

namespace App\Imports;

use App\Models\AccountManager;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class AccountManagerImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Log data untuk debugging
        Log::info('Data yang diproses: ', $row);

        return new AccountManager([
            'nipnas'                => $row['nipnas'] ?? null,
            'corporate_customer'    => $row['corporate_customer'] ?? null,
            'segmen'                => $row['segmen'] ?? null,
            'treg_ho'               => $row['treg_ho'] ?? null,
            'group_konglo'          => $row['group_konglo'] ?? null,

            // Account Manager per bulan
            ...$this->getMonthlyData($row, 'nik_am_'),
            ...$this->getMonthlyData($row, 'nama_am_'),

            // Kolom lain
            'proporsi'              => $this->cleanNumber($row['proporsi'] ?? null),
            'witel_ho'              => $row['witel_ho'] ?? null,
            'witel_id'              => $row['witel_id'] ?? null,
            'divisi'                => $row['divisi'] ?? null,
            'area'                  => $row['area'] ?? null,
            'nik_mgr_area'          => $row['nik_mgr_area'] ?? null,
            'mgr_area'              => $row['mgr_area'] ?? null,

            // Performa Sustain, Scaling, Revenue, NGTMA, dan BC
            ...$this->getMonthlyNumbers($row, 't_sust_'),
            't_total_sustain'       => $this->cleanNumber($row['t_total_sustain'] ?? null),

            ...$this->getMonthlyNumbers($row, 't_scal_'),
            't_total_scaling'       => $this->cleanNumber($row['t_total_scaling'] ?? null),

            ...$this->getMonthlyNumbers($row, 't_revenue_'),
            'total_target_revenue'  => $this->cleanNumber($row['total_target_revenue'] ?? null),

            ...$this->getMonthlyNumbers($row, 't_ngtma_'),
            'total_target_ngtma'    => $this->cleanNumber($row['total_target_ngtma'] ?? null),

            ...$this->getMonthlyNumbers($row, 'est_sust_', '_pots'),
            'total_est_sust_pots'   => $this->cleanNumber($row['total_est_sust_pots'] ?? null),

            ...$this->getMonthlyNumbers($row, 'est_sust_', '_np'),
            'total_est_sust_np'     => $this->cleanNumber($row['total_est_sust_np'] ?? null),

            ...$this->getMonthlyNumbers($row, 'real_'),
            'real_total'            => $this->cleanNumber($row['real_total'] ?? null),

            ...$this->getMonthlyNumbers($row, 'bc_'),
            'total_bc'              => $this->cleanNumber($row['total_bc'] ?? null),
        ]);
    }

    /**
     * Membersihkan angka dari format tidak valid
     */
    private function cleanNumber($value)
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $value = str_replace([',', ' '], '', $value);
        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Mengambil data angka per bulan untuk kelompok kolom tertentu
     */
    private function getMonthlyNumbers(array $row, string $prefix, string $suffix = '')
    {
        $months = ['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'ags', 'sep', 'okt', 'nov', 'des'];
        $data = [];
        foreach ($months as $month) {
            $key = "{$prefix}{$month}{$suffix}";
            $data[$key] = isset($row[$key]) ? $this->cleanNumber($row[$key]) : null;
        }
        return $data;
    }

    /**
     * Mengambil data string per bulan untuk kelompok kolom tertentu
     */
    private function getMonthlyData(array $row, string $prefix)
    {
        $months = ['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'ags', 'sep', 'okt', 'nov', 'des'];
        $data = [];
        foreach ($months as $month) {
            $key = "{$prefix}{$month}";
            $data[$key] = $row[$key] ?? null;
        }
        return $data;
    }
}
