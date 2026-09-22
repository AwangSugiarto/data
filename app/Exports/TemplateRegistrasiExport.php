<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TemplateRegistrasiExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, Export
{
    use Exportable;

    public function headings(): array
    {
        return [
            'nomor_pendaftaran',  // A - Wajib
            'nim',                // B - Opsional
            'kelompok_ukt',       // C - Opsional: 1-8
        ];
    }

    public function array(): array
    {
        return [
            ['2605010001', '261021010001', '3'],
            ['2605010002', '261021010002', '2'],
            ['2605010003', '',             '' ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Header — biru gelap, A merah (wajib)
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A1')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC2626']],
        ]);

        // Data sample
        $sheet->getStyle('A2:C4')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
        ]);

        // Border
        $sheet->getStyle('A1:C15')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'CBD5E1']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // ── Panduan ─────────────────────────────────────────────────────────
        $r = 7;
        $sheet->setCellValue("A{$r}", '-- PANDUAN PENGISIAN --');
        $sheet->getStyle("A{$r}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '64748B']],
        ]);

        $guides = [
            ['nomor_pendaftaran', 'WAJIB',    'Nomor pendaftaran / nomor tes calon mahasiswa'],
            ['nim',               'Opsional', 'Nomor Induk Mahasiswa (diisi jika sudah ada NIM resmi)'],
            ['kelompok_ukt',      'Opsional', 'Angka kelompok UKT: 1, 2, 3, 4, 5, 6, 7, atau 8'],
        ];

        $r++;
        foreach ($guides as $i => [$col, $req, $desc]) {
            $reqColor = $req === 'WAJIB' ? 'DC2626' : '475569';
            $sheet->setCellValue("A{$r}", $col);
            $sheet->setCellValue("B{$r}", $req);
            $sheet->setCellValue("C{$r}", $desc);
            $sheet->mergeCells("C{$r}:C{$r}");
            $bg = $i % 2 === 0 ? 'F8FAFC' : 'FFFFFF';
            $sheet->getStyle("A{$r}:C{$r}")->applyFromArray([
                'font' => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '475569']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
            ]);
            $sheet->getStyle("B{$r}")->applyFromArray([
                'font' => ['color' => ['rgb' => $reqColor], 'bold' => $req === 'WAJIB'],
            ]);
            $r++;
        }

        $sheet->getStyle('A8:C' . ($r - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'E2E8F0']]],
        ]);

        // Catatan: semua baris dianggap registrasi → status_akademik otomatis Aktif
        $sheet->setCellValue("A{$r}", 'CATATAN: Semua baris diproses sebagai Registrasi → Status Akademik otomatis menjadi Aktif. Untuk update status semester berikutnya, gunakan menu yang terpisah.');
        $sheet->mergeCells("A{$r}:C{$r}");
        $sheet->getStyle("A{$r}")->applyFromArray([
            'font'      => ['size' => 9, 'bold' => true, 'color' => ['rgb' => 'B45309']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFBEB']],
            'alignment' => ['wrapText' => true],
        ]);
        $sheet->getRowDimension($r)->setRowHeight(28);

        return [];
    }

    public function columnWidths(): array
    {
        return ['A' => 22, 'B' => 20, 'C' => 14];
    }
}
