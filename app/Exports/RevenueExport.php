<?php

namespace App\Exports;

use App\Models\Revenue;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class RevenueExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Get revenue data with relationships
        return Revenue::with(['accountManager', 'corporateCustomer', 'divisi'])
            ->orderBy('account_manager_id')
            ->orderBy('corporate_customer_id')
            ->orderBy('bulan')
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'NIK',
            'Nama Account Manager',
            'Divisi',
            'NIPNAS',
            'Corporate Customer',
            'Bulan',
            'Target Revenue',
            'Real Revenue',
            'Achievement (%)'
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        // Calculate achievement percentage
        $achievement = 0;
        if ($row->target_revenue > 0) {
            $achievement = round(($row->real_revenue / $row->target_revenue) * 100, 2);
        }

        // Format date to show only month and year
        $bulan = date('F Y', strtotime($row->bulan));

        return [
            $row->accountManager ? $row->accountManager->nik : 'N/A',
            $row->accountManager ? $row->accountManager->nama : 'N/A',
            $row->divisi ? $row->divisi->nama : 'N/A',
            $row->corporateCustomer ? $row->corporateCustomer->nipnas : 'N/A',
            $row->corporateCustomer ? $row->corporateCustomer->nama : 'N/A',
            $bulan,
            $row->target_revenue,
            $row->real_revenue,
            $achievement . '%'
        ];
    }

    /**
     * @param Worksheet $sheet
     *
     * @return Worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '4472C4',
                ],
            ],
        ]);

        // Auto-filter for all columns
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        return $sheet;
    }
}