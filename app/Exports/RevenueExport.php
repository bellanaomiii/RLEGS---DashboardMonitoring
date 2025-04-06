<?php

namespace App\Exports;

use App\Models\Revenue;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class RevenueExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Revenue::with(['accountManager', 'corporateCustomer'])->get();
    }

    public function map($revenue): array
    {
        return [
            $revenue->accountManager->nama ?? 'N/A',
            $revenue->corporateCustomer->nama ?? 'N/A',
            $revenue->target_revenue,
            $revenue->real_revenue,
            $this->formatDate($revenue->bulan),
            $this->calculateAchievement($revenue)
        ];
    }

    public function headings(): array
    {
        return [
            'Account Manager',
            'Corporate Customer',
            'Target Revenue',
            'Real Revenue',
            'Bulan',
            'Achievement (%)'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function formatDate($date)
    {
        return Carbon::parse($date)->format('m/Y');
    }

    private function calculateAchievement($revenue)
    {
        if ($revenue->target_revenue > 0) {
            return round(($revenue->real_revenue / $revenue->target_revenue) * 100, 2);
        }
        return 0;
    }
}