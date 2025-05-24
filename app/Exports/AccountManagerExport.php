<?php

namespace App\Exports;

use App\Models\AccountManager;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AccountManagerExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    /**
     * Export all Account Managers with their relations
     */
    public function collection()
    {
        return AccountManager::with(['witel', 'regional', 'divisis'])
            ->orderBy('nik')
            ->get();
    }

    /**
     * Define the headings for the Excel file
     */
    public function headings(): array
    {
        return [
            'NIK',
            'NAMA AM',
            'WITEL HO',
            'REGIONAL',
            'DIVISI',
            'CREATED AT',
            'UPDATED AT'
        ];
    }

    /**
     * Map data untuk setiap row
     */
    public function map($accountManager): array
    {
        // Handle multiple divisi - join dengan separator
        $divisiNames = $accountManager->divisis->pluck('nama')->join(', ');

        return [
            $accountManager->nik,
            $accountManager->nama,
            $accountManager->witel ? $accountManager->witel->nama : '',
            $accountManager->regional ? $accountManager->regional->nama : '',
            $divisiNames,
            $accountManager->created_at ? $accountManager->created_at->format('Y-m-d H:i:s') : '',
            $accountManager->updated_at ? $accountManager->updated_at->format('Y-m-d H:i:s') : ''
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
                    'startColor' => ['argb' => '366092'],
                ],
            ],
        ];
    }

    /**
     * Set the title of the worksheet
     */
    public function title(): string
    {
        return 'Account Managers';
    }
}