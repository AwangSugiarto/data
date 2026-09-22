<?php

namespace App\Exports;

use App\Models\DimWilayah;
use App\Models\DimWarganegara;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TemplateCamaExport implements WithMultipleSheets, Export
{
    use Exportable;
    public function sheets(): array
    {
        return [
            new TemplateCamaDataSheet,
            new TemplateCamaNegaraSheet,
            new TemplateCamaWilayahSheet,
        ];
    }
}

// ── Sheet 1: Template utama ───────────────────────────────────────────────────
class TemplateCamaDataSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string { return 'Template'; }

    public function headings(): array
    {
        return [
            'nomor_pendaftaran',  // A - Wajib
            'nama',               // B - Wajib
            'kode_prodi',         // C - Wajib
            'jalur_seleksi',      // D - Opsional
            'nisn',               // E - Opsional
            'npsn',               // F - Opsional
            'jenis_kelamin',      // G - L atau P
            'asal_sekolah',       // H - Nama sekolah
            'jenis_sekolah',      // I - SMA / SMK / MA / dll
            'kode_negara',        // J - ID, MY, dll
            'provinsi',           // K - Nama provinsi
            'kabupaten_kota',     // L - Nama kab/kota
            'kecamatan',          // M - Nama kecamatan
            'ukt',                // N - Kelompok UKT: 1-8
        ];
    }

    public function array(): array
    {
        return [
            ['2605010001', 'Ahmad Fauzi',    '73201', 'UMPTKIN',    '1234567890', '12345678', 'L', 'SMA Negeri 1 Palembang',   'SMA',  'ID', 'Sumatera Selatan', 'Palembang',  'Ilir Timur I',    '3'],
            ['2605010002', 'Siti Rahma',     '73201', 'UMPTKIN',    '0987654321', '87654321', 'P', 'MAN 1 Prabumulih',         'MA',   'ID', 'Sumatera Selatan', 'Prabumulih', 'Prabumulih Utara', '2'],
            ['2605010003', 'Ahmad Rizki',    '86208', 'SPAN-PTKIN', '',           '',          'L', 'Pondok Pesantren Al-Azhar', 'MA',   'ID', 'Sumatera Selatan', 'Ogan Ilir',  'Pemulutan',        ''],
            ['2605010004', 'Lisa Abdullah',  '73201', 'Internasional','',         '',          'P', 'Islamic University Cairo',  'Univ', 'EG', 'Cairo',            'Cairo',      '',                 '2'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Header baris 1 — merah untuk kolom wajib (A-C), biru untuk sisanya
        $sheet->getStyle('A1:N1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A1:C1')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC2626']],
        ]);

        // Data sample rows
        $sheet->getStyle('A2:N5')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
        ]);

        // Border area data
        $sheet->getStyle('A1:N20')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'BFDBFE']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // ── Panduan pengisian ─────────────────────────────────────────────────
        $r = 7;
        $sheet->setCellValue("A{$r}", '⬇️  PANDUAN PENGISIAN KOLOM');
        $sheet->mergeCells("A{$r}:N{$r}");
        $sheet->getStyle("A{$r}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '1E3A8A']],
        ]);
        $r++;

        $sheet->setCellValue("A{$r}", 'Kolom');
        $sheet->setCellValue("B{$r}", 'Status');
        $sheet->setCellValue("C{$r}", 'Keterangan');
        $sheet->mergeCells("C{$r}:N{$r}");
        $sheet->getStyle("A{$r}:N{$r}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
        ]);
        $r++;

        $notes = [
            ['nomor_pendaftaran', '🔴 WAJIB',    'Nomor pendaftaran / nomor tes peserta'],
            ['nama',              '🔴 WAJIB',    'Nama lengkap sesuai ijazah'],
            ['kode_prodi',        '🔴 WAJIB',    'Kode 5 digit program studi (contoh: 73201, 86208)'],
            ['jalur_seleksi',     '🟡 Opsional', 'Nama jalur: UMPTKIN, SPAN-PTKIN, Mandiri-I (atau dipilih di form)'],
            ['nisn',              '🟡 Opsional', 'Nomor Induk Siswa Nasional (10 digit)'],
            ['npsn',              '🟡 Opsional', 'Nomor Pokok Sekolah Nasional (8 digit)'],
            ['jenis_kelamin',     '🟡 Opsional', 'Isi L (Laki-laki) atau P (Perempuan)'],
            ['asal_sekolah',      '🟡 Opsional', 'Nama lengkap sekolah asal'],
            ['jenis_sekolah',     '🟡 Opsional', 'Contoh: SMA, SMK, MA, MAN, Pesantren, Univ'],
            ['kode_negara',       '🟡 Opsional', 'Kode 2 huruf (ISO 3166). Lihat sheet "Ref Negara". Default: ID'],
            ['provinsi',          '🟡 Opsional', 'Nama provinsi asal. Lihat sheet "Ref Wilayah"'],
            ['kabupaten_kota',    '🟡 Opsional', 'Nama kabupaten/kota. Harus sesuai data di sheet "Ref Wilayah"'],
            ['kecamatan',         '🟡 Opsional', 'Nama kecamatan (jika tersedia di sistem)'],
            ['ukt',               '🟡 Opsional', 'Angka kelompok UKT: 1, 2, 3, 4, 5, 6, 7, atau 8'],
        ];

        foreach ($notes as $i => [$col, $status, $desc]) {
            $sheet->setCellValue("A{$r}", $col);
            $sheet->setCellValue("B{$r}", $status);
            $sheet->setCellValue("C{$r}", $desc);
            $sheet->mergeCells("C{$r}:N{$r}");
            $bg = ($i % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
            $sheet->getStyle("A{$r}:N{$r}")->applyFromArray([
                'font' => ['size' => 9, 'color' => ['rgb' => '374151']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
            ]);
            $r++;
        }

        $sheet->getStyle('A9:N' . ($r - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'E2E8F0']]],
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 22, 'B' => 26, 'C' => 14, 'D' => 18,
            'E' => 14, 'F' => 12, 'G' => 12, 'H' => 28,
            'I' => 14, 'J' => 12, 'K' => 24, 'L' => 22,
            'M' => 20, 'N' => 8,
        ];
    }
}

// ── Sheet 2: Referensi Negara ─────────────────────────────────────────────────
class TemplateCamaNegaraSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string { return 'Ref Negara'; }

    public function headings(): array
    {
        return ['kode_negara', 'nama_negara'];
    }

    public function array(): array
    {
        $data = DimWarganegara::orderBy('nama_negara')->get(['kode_negara', 'nama_negara']);
        if ($data->isEmpty()) {
            return [['ID', 'Indonesia'], ['MY', 'Malaysia'], ['EG', 'Mesir'], ['SA', 'Arab Saudi']];
        }
        return $data->map(fn($r) => [$r->kode_negara, $r->nama_negara])->toArray();
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065F46']],
        ]);
        return [];
    }

    public function columnWidths(): array { return ['A' => 16, 'B' => 30]; }
}

// ── Sheet 3: Referensi Wilayah ────────────────────────────────────────────────
class TemplateCamaWilayahSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string { return 'Ref Wilayah'; }

    public function headings(): array
    {
        return ['provinsi', 'kabupaten_kota', 'kecamatan'];
    }

    public function array(): array
    {
        $data = DimWilayah::orderBy('provinsi')->orderBy('kabupaten_kota')
            ->get(['provinsi', 'kabupaten_kota', 'kecamatan']);
        if ($data->isEmpty()) {
            return [['Sumatera Selatan', 'Palembang', 'Ilir Timur I']];
        }
        return $data->map(fn($r) => [$r->provinsi, $r->kabupaten_kota, $r->kecamatan ?? ''])->toArray();
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C3AED']],
        ]);
        return [];
    }

    public function columnWidths(): array { return ['A' => 24, 'B' => 24, 'C' => 24]; }
}
