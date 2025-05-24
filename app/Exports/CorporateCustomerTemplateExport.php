<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CorporateCustomerTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function array(): array
    {
        // Return beberapa baris contoh
        return [
            ['4648251', 'PT APIDT INFRASTRUCTURE'],
            ['5587309', 'BANK BPD DIY'],
            ['1234567', 'PT TELKOM INDONESIA'],
            ['9876543', 'BANK CENTRAL ASIA'],
            ['5555555', 'PT INDOSAT OOREDOO'],
            // Tambahkan baris kosong untuk template
            ['', ''],
            ['', ''],
            ['', ''],
            ['', ''],
            ['', ''],
        ];
    }

    public function headings(): array
    {
        return [
            'NIPNAS',
            'STANDARD NAME'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Add some instructions in merged cells
        $sheet->mergeCells('D1:H1');
        $sheet->setCellValue('D1', 'INSTRUKSI PENGISIAN:');
        $sheet->getStyle('D1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB(Color::COLOR_DARKBLUE);

        $sheet->mergeCells('D2:H2');
        $sheet->setCellValue('D2', '1. NIPNAS harus berupa angka (1-9999999)');
        $sheet->getStyle('D2')->getFont()->setSize(11);

        $sheet->mergeCells('D3:H3');
        $sheet->setCellValue('D3', '2. NIPNAS tidak boleh duplikasi');
        $sheet->getStyle('D3')->getFont()->setSize(11);

        $sheet->mergeCells('D4:H4');
        $sheet->setCellValue('D4', '3. Standard Name tidak boleh duplikasi');
        $sheet->getStyle('D4')->getFont()->setSize(11);

        $sheet->mergeCells('D5:H5');
        $sheet->setCellValue('D5', '4. Kedua kolom wajib diisi');
        $sheet->getStyle('D5')->getFont()->setSize(11);

        $sheet->mergeCells('D6:H6');
        $sheet->setCellValue('D6', '5. Jika NIPNAS sudah ada, data akan diupdate');
        $sheet->getStyle('D6')->getFont()->setSize(11);

        $sheet->mergeCells('D8:H8');
        $sheet->setCellValue('D8', 'Contoh format NIPNAS yang benar:');
        $sheet->getStyle('D8')->getFont()->setBold(true)->setSize(11);

        $sheet->mergeCells('D9:H9');
        $sheet->setCellValue('D9', '• 4648251 (angka 7 digit)');
        $sheet->getStyle('D9')->getFont()->setSize(10);

        $sheet->mergeCells('D10:H10');
        $sheet->setCellValue('D10', '• 123456 (angka 6 digit)');
        $sheet->getStyle('D10')->getFont()->setSize(10);

        $sheet->mergeCells('D11:H11');
        $sheet->setCellValue('D11', '• 12345 (angka 5 digit)');
        $sheet->getStyle('D11')->getFont()->setSize(10);

        // Add border to instruction area
        $sheet->getStyle('D1:H11')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('D1:H11')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F8F9FA');

        return [
            // Style header row
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
            // Style example rows
            '2:6' => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'E1F5FE'], // Light teal
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Template Corporate Customer';
    }
}