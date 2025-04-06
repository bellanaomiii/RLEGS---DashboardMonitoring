<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RevenueTemplateExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        // Template kosong, hanya contoh data
        return collect([
            [
                'account_manager' => 'Nama Account Manager',
                'corporate_customer' => 'Nama Corporate Customer',
                'target_revenue' => 1000000,
                'real_revenue' => 1200000,
                'bulan' => '01/2025'
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'account_manager',
            'corporate_customer',
            'target_revenue',
            'real_revenue',
            'bulan'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}