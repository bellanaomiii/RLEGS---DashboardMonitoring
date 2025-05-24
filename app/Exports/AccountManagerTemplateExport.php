<?php

namespace App\Exports;

use App\Models\Witel;
use App\Models\Regional;
use App\Models\Divisi;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AccountManagerTemplateExport implements WithMultipleSheets
{
    /**
     * Return multiple sheets
     */
    public function sheets(): array
    {
        return [
            'Template' => new AccountManagerTemplateSheet(),
            'Master Witel' => new WitelMasterSheet(),
            'Master Regional' => new RegionalMasterSheet(),
            'Master Divisi' => new DivisiMasterSheet(),
        ];
    }
}

/**
 * Main template sheet
 */
class AccountManagerTemplateSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function array(): array
    {
        // Return beberapa baris contoh
        return [
            ['12345', 'BAMBANG SUPRIYADI', 'JATIM BARAT', 'TREG 3', 'DSS'],
            ['67890', 'SITI NURHALIZA', 'YOGYA JATENG SELATAN', 'TREG 3', 'DPS'],
            ['11111', 'AHMAD HIDAYAT', 'JATIM TIMUR', 'TREG 3', 'DES'],
            // Tambahkan baris kosong untuk template
            ['', '', '', '', ''],
            ['', '', '', '', ''],
        ];
    }

    public function headings(): array
    {
        return [
            'NIK',
            'NAMA AM',
            'WITEL HO',
            'REGIONAL',
            'DIVISI'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Add some instructions in merged cells
        $sheet->mergeCells('G1:K1');
        $sheet->setCellValue('G1', 'INSTRUKSI PENGISIAN:');
        $sheet->getStyle('G1')->getFont()->setBold(true)->setSize(14);

        $sheet->mergeCells('G2:K2');
        $sheet->setCellValue('G2', '1. NIK harus 5 digit angka');

        $sheet->mergeCells('G3:K3');
        $sheet->setCellValue('G3', '2. Nama AM tidak boleh duplikasi');

        $sheet->mergeCells('G4:K4');
        $sheet->setCellValue('G4', '3. Witel HO harus sesuai master (lihat sheet "Master Witel")');

        $sheet->mergeCells('G5:K5');
        $sheet->setCellValue('G5', '4. Regional harus sesuai master (lihat sheet "Master Regional")');

        $sheet->mergeCells('G6:K6');
        $sheet->setCellValue('G6', '5. Divisi harus sesuai master (lihat sheet "Master Divisi")');

        $sheet->mergeCells('G7:K7');
        $sheet->setCellValue('G7', '6. Satu NIK bisa memiliki multiple divisi (buat baris terpisah)');

        return [
            // Style header row
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
            // Style example rows
            '2:4' => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'E8F4FD'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Template';
    }
}

/**
 * Master Witel sheet
 */
class WitelMasterSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function array(): array
    {
        $witels = Witel::orderBy('nama')->get(['nama'])->toArray();
        return array_map(function($witel) {
            return [$witel['nama']];
        }, $witels);
    }

    public function headings(): array
    {
        return ['NAMA WITEL'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => Color::COLOR_WHITE],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => '28A745'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Master Witel';
    }
}

/**
 * Master Regional sheet
 */
class RegionalMasterSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function array(): array
    {
        $regionals = Regional::orderBy('nama')->get(['nama'])->toArray();
        return array_map(function($regional) {
            return [$regional['nama']];
        }, $regionals);
    }

    public function headings(): array
    {
        return ['NAMA REGIONAL'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => Color::COLOR_WHITE],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFC107'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Master Regional';
    }
}

/**
 * Master Divisi sheet
 */
class DivisiMasterSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function array(): array
    {
        $divisis = Divisi::orderBy('nama')->get(['nama'])->toArray();
        return array_map(function($divisi) {
            return [$divisi['nama']];
        }, $divisis);
    }

    public function headings(): array
    {
        return ['NAMA DIVISI'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => Color::COLOR_WHITE],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'DC3545'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Master Divisi';
    }
}