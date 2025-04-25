<?php

namespace App\Exports;

use App\Models\AccountManager;
use App\Models\CorporateCustomer;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;

class RevenueTemplateExport implements WithMultipleSheets
{
    use Exportable;

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        // Sheet 1: Template for data import
        $sheets[] = new class implements WithTitle, WithHeadings, ShouldAutoSize, WithStyles {
            public function title(): string
            {
                return 'Template Revenue';
            }

            public function headings(): array
            {
                return [
                    'NAMA AM',
                    'NIK',
                    'STANDARD NAME',
                    'NIPNAS',
                    'DIVISI',
                    'Target_Jan',
                    'Target_Feb',
                    'Target_Mar',
                    'Target_Apr',
                    'Target_Mei',
                    'Target_Jun',
                    'Target_Jul',
                    'Target_Agu',
                    'Target_Sep',
                    'Target_Okt',
                    'Target_Nov',
                    'Target_Des',
                    'Real_Jan',
                    'Real_Feb',
                    'Real_Mar',
                    'Real_Apr',
                    'Real_Mei',
                    'Real_Jun',
                    'Real_Jul',
                    'Real_Agu',
                    'Real_Sep',
                    'Real_Okt',
                    'Real_Nov',
                    'Real_Des'
                ];
            }

            public function styles(Worksheet $sheet)
            {
                // Style header row
                $lastColumn = 'AC'; // Column for Real_Des
                $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
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

                // Add freeze pane
                $sheet->freezePane('A2');

                // Add sample data in row 2
                $sheet->setCellValue('A2', 'John Doe');
                $sheet->setCellValue('B2', '12345');
                $sheet->setCellValue('C2', 'ABCDE Corporation');
                $sheet->setCellValue('D2', '1234567');
                $sheet->setCellValue('E2', 'DPS');

                // Add some sample values for target and real
                $sheet->setCellValue('F2', '10000000');  // Target_Jan
                $sheet->setCellValue('G2', '15000000');  // Target_Feb
                $sheet->setCellValue('R2', '9500000');   // Real_Jan
                $sheet->setCellValue('S2', '14800000');  // Real_Feb

                return $sheet;
            }
        };

        // Sheet 2: Available Account Managers List
        $sheets[] = new class implements WithTitle, FromCollection, WithHeadings, ShouldAutoSize, WithStyles {
            public function title(): string
            {
                return 'Account Managers';
            }

            public function collection()
            {
                return AccountManager::select('nama', 'nik')
                    ->orderBy('nama')
                    ->get()
                    ->map(function ($am) {
                        return [
                            'nama' => $am->nama,
                            'nik' => $am->nik
                        ];
                    });
            }

            public function headings(): array
            {
                return [
                    'NAMA AM',
                    'NIK'
                ];
            }

            public function styles(Worksheet $sheet)
            {
                // Style header row
                $sheet->getStyle('A1:B1')->applyFromArray([
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

                return $sheet;
            }
        };

        // Sheet 3: Available Corporate Customers List
        $sheets[] = new class implements WithTitle, FromCollection, WithHeadings, ShouldAutoSize, WithStyles {
            public function title(): string
            {
                return 'Corporate Customers';
            }

            public function collection()
            {
                return CorporateCustomer::select('nama', 'nipnas')
                    ->orderBy('nama')
                    ->get()
                    ->map(function ($cc) {
                        return [
                            'nama' => $cc->nama,
                            'nipnas' => $cc->nipnas
                        ];
                    });
            }

            public function headings(): array
            {
                return [
                    'STANDARD NAME',
                    'NIPNAS'
                ];
            }

            public function styles(Worksheet $sheet)
            {
                // Style header row
                $sheet->getStyle('A1:B1')->applyFromArray([
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

                return $sheet;
            }
        };

        // Sheet 4: Instructions
        $sheets[] = new class implements WithTitle, FromCollection, ShouldAutoSize, WithStyles {
            public function title(): string
            {
                return 'Petunjuk';
            }

            public function collection()
            {
                return new Collection([
                    ['Petunjuk Pengisian Template Revenue'],
                    [''],
                    ['1. Pastikan data Account Manager yang dimasukkan sudah terdaftar dalam sistem (lihat di sheet "Account Managers")'],
                    ['2. Pastikan data Corporate Customer yang dimasukkan sudah terdaftar dalam sistem (lihat di sheet "Corporate Customers")'],
                    ['3. NIK dan NIPNAS harus sesuai dengan yang terdaftar di sistem'],
                    ['4. Jika NIK atau NAMA AM tidak ditemukan, baris tersebut akan dilewati saat import'],
                    ['5. Jika NIPNAS atau STANDARD NAME tidak ditemukan, baris tersebut akan dilewati saat import'],
                    ['6. Divisi diisi dengan nama divisi yang tersedia (DGS, DPS, DSS)'],
                    ['7. Format nilai Target dan Real Revenue harus berupa angka tanpa titik atau koma'],
                    ['8. Kolom Target dan Real diisi untuk setiap bulan dengan format:'],
                    ['   - Target_Jan, Target_Feb, ... Target_Des untuk target bulanan'],
                    ['   - Real_Jan, Real_Feb, ... Real_Des untuk realisasi bulanan'],
                    ['9. Jika suatu bulan tidak memiliki data, bisa dikosongkan atau diisi dengan 0'],
                    ['10. Template ini menggunakan tahun ' . date('Y') . ' sebagai default untuk semua data bulanan'],
                ]);
            }

            public function styles(Worksheet $sheet)
            {
                // Style the title
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                ]);

                // Style the numbered list
                $lastRow = $sheet->getHighestRow();
                for ($i = 3; $i <= $lastRow; $i++) {
                    if (substr($sheet->getCell('A'.$i)->getValue(), 0, 1) !== ' ') {
                        $sheet->getStyle('A'.$i)->applyFromArray([
                            'font' => [
                                'bold' => true,
                            ],
                        ]);
                    }
                }

                return $sheet;
            }
        };

        return $sheets;
    }
}