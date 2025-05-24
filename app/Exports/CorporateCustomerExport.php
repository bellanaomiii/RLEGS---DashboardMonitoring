<?php

namespace App\Exports;

use App\Models\CorporateCustomer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CorporateCustomerExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    /**
     * Export all Corporate Customers
     */
    public function collection()
    {
        return CorporateCustomer::orderBy('nama')->get();
    }

    /**
     * Define the headings for the Excel file
     */
    public function headings(): array
    {
        return [
            'NIPNAS',
            'STANDARD NAME',
            'CREATED AT',
            'UPDATED AT'
        ];
    }

    /**
     * Map data untuk setiap row
     */
    public function map($corporateCustomer): array
    {
        return [
            $corporateCustomer->nipnas,
            $corporateCustomer->nama,
            $corporateCustomer->created_at ? $corporateCustomer->created_at->format('Y-m-d H:i:s') : '',
            $corporateCustomer->updated_at ? $corporateCustomer->updated_at->format('Y-m-d H:i:s') : ''
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => Color::COLOR_WHITE],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => '17A2B8'], // Teal color
                ],
            ],
        ];
    }

    /**
     * Set the title of the worksheet
     */
    public function title(): string
    {
        return 'Corporate Customers';
    }
}