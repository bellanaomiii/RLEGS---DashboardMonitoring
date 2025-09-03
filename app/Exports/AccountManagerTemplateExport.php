<?php

namespace App\Exports;

use App\Models\Witel;
use App\Models\Regional;
use App\Models\Divisi;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * 🔧 COMPLETELY REWRITTEN: AccountManagerTemplateExport untuk CSV Format
 *
 * PERUBAHAN UTAMA:
 * - Nama class TETAP SAMA (sesuai permintaan)
 * - Fungsi diubah total untuk optimal CSV
 * - Hilangkan comment rows yang tidak cocok untuk CSV
 * - Struktur data lebih sederhana dan clean
 * - Contoh data lebih realistic
 */
class AccountManagerTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    /**
     * 🔧 REWRITTEN: Data array untuk CSV template
     * Struktur lebih clean tanpa comment rows
     */
    public function array(): array
    {
        // 🔧 FIX: Data contoh yang realistic dan clean untuk CSV
        $examples = [
            // Contoh data dengan multiple divisi untuk NIK yang sama
            ['12345', 'BAMBANG SUPRIYADI', 'JATIM BARAT', 'TREG 3', 'DSS'],
            ['12345', 'BAMBANG SUPRIYADI', 'JATIM BARAT', 'TREG 3', 'DPS'], // Multiple divisi
            ['67890', 'SITI NURHALIZA', 'YOGYA JATENG SELATAN', 'TREG 3', 'DES'],
            ['11111', 'AHMAD HIDAYAT', 'JATIM TIMUR', 'TREG 3', 'DPS'],
            ['22222', 'RINA WIJAYANTI', 'JATIM SELATAN', 'TREG 3', 'DCS'],
            ['33333', 'DEDI KURNIAWAN', 'JATENG UTARA', 'TREG 3', 'DSS'],
            ['44444', 'MAYA SARI', 'JOGJA DIY', 'TREG 3', 'DES'],
            ['55555', 'RUDI HARTONO', 'SOLO BOYOLALI', 'TREG 3', 'DPS'],
        ];

        return $examples;
    }

    /**
     * 🔧 UPDATED: Headers yang clean untuk CSV
     */
    public function headings(): array
    {
        return [
            'NIK',
            'NAMA_AM',
            'WITEL',
            'REGIONAL',
            'DIVISI'
        ];
    }

    /**
     * 🆕 NEW: Title untuk worksheet (opsional untuk CSV)
     */
    public function title(): string
    {
        return 'Template Account Manager';
    }
}

/**
 * 🆕 NEW: Class terpisah untuk master data Witel (CSV)
 * Bisa digunakan jika ingin export master data terpisah
 */
class WitelMasterCsvExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        // Get semua witel dari database
        $witels = Witel::orderBy('nama')->get(['nama']);

        $data = [];
        foreach ($witels as $witel) {
            $data[] = [$witel->nama];
        }

        return $data;
    }

    public function headings(): array
    {
        return ['NAMA_WITEL'];
    }
}

/**
 * 🆕 NEW: Class terpisah untuk master data Regional (CSV)
 */
class RegionalMasterCsvExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        // Get semua regional dari database
        $regionals = Regional::orderBy('nama')->get(['nama']);

        $data = [];
        foreach ($regionals as $regional) {
            $data[] = [$regional->nama];
        }

        return $data;
    }

    public function headings(): array
    {
        return ['NAMA_REGIONAL'];
    }
}

/**
 * 🆕 NEW: Class terpisah untuk master data Divisi (CSV)
 */
class DivisiMasterCsvExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        // Get semua divisi dari database
        $divisis = Divisi::orderBy('nama')->get(['nama']);

        $data = [];
        foreach ($divisis as $divisi) {
            $data[] = [$divisi->nama];
        }

        return $data;
    }

    public function headings(): array
    {
        return ['NAMA_DIVISI'];
    }
}

/**
 * 🆕 NEW: Class untuk template dengan instruksi dalam comment CSV
 * Alternative jika tetap ingin ada instruksi dalam file
 */
class AccountManagerTemplateWithInstructionsExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        // Instruksi sebagai data rows dengan prefix khusus
        $instructions = [
            ['# INSTRUKSI', 'Pastikan NIK 5 digit unik', '', '', ''],
            ['# INSTRUKSI', 'Nama AM tidak boleh duplikasi', '', '', ''],
            ['# INSTRUKSI', 'Gunakan nama Witel/Regional/Divisi yang persis', '', '', ''],
            ['# INSTRUKSI', 'Satu NIK bisa multiple divisi (buat baris terpisah)', '', '', ''],
            ['# INSTRUKSI', 'Hapus baris instruksi sebelum import', '', '', ''],
            ['', '', '', '', ''], // Empty row separator
        ];

        // Data contoh
        $examples = [
            ['12345', 'BAMBANG SUPRIYADI', 'JATIM BARAT', 'TREG 3', 'DSS'],
            ['12345', 'BAMBANG SUPRIYADI', 'JATIM BARAT', 'TREG 3', 'DPS'],
            ['67890', 'SITI NURHALIZA', 'YOGYA JATENG SELATAN', 'TREG 3', 'DES'],
            ['11111', 'AHMAD HIDAYAT', 'JATIM TIMUR', 'TREG 3', 'DPS'],
        ];

        return array_merge($instructions, $examples);
    }

    public function headings(): array
    {
        return [
            'NIK',
            'NAMA_AM',
            'WITEL',
            'REGIONAL',
            'DIVISI'
        ];
    }
}

/**
 * 🆕 NEW: Class untuk comprehensive template dengan semua master data
 * Menggunakan multiple sheets (tapi dalam CSV akan flat)
 */
class ComprehensiveAccountManagerTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        $data = [];

        // Header section
        $data[] = ['=== TEMPLATE ACCOUNT MANAGER ===', '', '', '', ''];
        $data[] = ['', '', '', '', ''];

        // Sample data
        $examples = [
            ['12345', 'BAMBANG SUPRIYADI', 'JATIM BARAT', 'TREG 3', 'DSS'],
            ['12345', 'BAMBANG SUPRIYADI', 'JATIM BARAT', 'TREG 3', 'DPS'],
            ['67890', 'SITI NURHALIZA', 'YOGYA JATENG SELATAN', 'TREG 3', 'DES'],
            ['11111', 'AHMAD HIDAYAT', 'JATIM TIMUR', 'TREG 3', 'DPS'],
        ];

        $data = array_merge($data, $examples);

        // Separator
        $data[] = ['', '', '', '', ''];
        $data[] = ['=== MASTER DATA REFERENCE ===', '', '', '', ''];
        $data[] = ['', '', '', '', ''];

        // Master Witel
        $data[] = ['WITEL OPTIONS:', '', '', '', ''];
        $witels = Witel::orderBy('nama')->pluck('nama')->take(10); // Limit untuk CSV
        foreach ($witels as $witel) {
            $data[] = [$witel, '', '', '', ''];
        }

        // Master Regional
        $data[] = ['', '', '', '', ''];
        $data[] = ['REGIONAL OPTIONS:', '', '', '', ''];
        $regionals = Regional::orderBy('nama')->pluck('nama')->take(10);
        foreach ($regionals as $regional) {
            $data[] = [$regional, '', '', '', ''];
        }

        // Master Divisi
        $data[] = ['', '', '', '', ''];
        $data[] = ['DIVISI OPTIONS:', '', '', '', ''];
        $divisis = Divisi::orderBy('nama')->pluck('nama');
        foreach ($divisis as $divisi) {
            $data[] = [$divisi, '', '', '', ''];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'NIK',
            'NAMA_AM',
            'WITEL',
            'REGIONAL',
            'DIVISI'
        ];
    }
}