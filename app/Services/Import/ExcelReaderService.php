<?php

namespace App\Services\Import;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Membaca file Excel (.xlsx, .xls, .csv) dan mengembalikan
 * header kolom serta baris preview (maksimal 10 baris).
 */
class ExcelReaderService
{
    /**
     * @param string $path  Absolute path ke file yang sudah disimpan
     * @param string $ext   Ekstensi file (xlsx, xls, csv)
     * @return array{headers: array, rows: array, total_rows: int}
     */
    public function preview(string $path, string $ext = 'xlsx'): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);

        // Untuk xlsx: memuat hanya sheet aktif tanpa format
        if (method_exists($reader, 'setLoadSheetsOnly')) {
            $spreadsheet = $reader->load($path);
        } else {
            $spreadsheet = $reader->load($path);
        }

        $sheet    = $spreadsheet->getActiveSheet();
        $highRow  = $sheet->getHighestRow();
        $highCol  = $sheet->getHighestColumn();

        // Baris pertama = header
        $headers = [];
        foreach ($sheet->getRowIterator(1, 1) as $row) {
            foreach ($row->getCellIterator('A', $highCol) as $cell) {
                $v = trim((string) $cell->getValue());
                if ($v !== '') {
                    $headers[] = $v;
                }
            }
        }

        // Preview hingga 100 baris
        $rows = [];
        $maxPreview = min($highRow, 101);
        foreach ($sheet->getRowIterator(2, $maxPreview) as $rowObj) {
            $row = [];
            foreach ($rowObj->getCellIterator('A', $highCol) as $cell) {
                $row[] = $cell->getValue();
            }
            $rows[] = $row;
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return [
            'headers'    => $headers,
            'rows'       => $rows,
            'total_rows' => $highRow - 1, // exclude header
        ];
    }
}
