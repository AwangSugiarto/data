<?php

namespace App\Services\Import;

use App\Models\DimTahunAkademik;
use App\Models\FactKelulusan;
use App\Models\FactKuota;
use App\Models\FactPendaftaran;
use App\Models\FactRegistrasi;
use App\Models\ImportBatch;
use App\Models\ImportStagingRow;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Membaca Excel, memetakan kolom, dan memasukkan data ke tabel fakta.
 *
 * Format Excel yang didukung:
 *   A) "Wide": satu baris per prodi, setiap kolom = jalur+metrik
 *      Contoh: | Prodi | Kuota_SNBP | Daftar_SNBP | Lulus_SNBP | ...
 *
 *   B) "Long": satu baris per kombinasi prodi+jalur
 *      Contoh: | Prodi | Jalur | Kuota | Daftar | Lulus | Registrasi |
 */
class ExcelImportService
{
    public function __construct(
        private readonly MasterDataResolverService $resolver
    ) {}

    /**
     * @param string $path   Absolute path ke file Excel
     * @param array  $mapping Array konfigurasi mapping dari wizard:
     *   [
     *     'tahun'         => '2024',           // nilai tahun langsung
     *     'format'        => 'long',            // 'long' | 'wide'
     *     'col_prodi'     => 'Nama Prodi',
     *     'col_jalur'     => 'Jalur Masuk',     // hanya format long
     *     'col_kuota'     => 'Daya Tampung',
     *     'col_daftar'    => 'Pendaftar',
     *     'col_lulus'     => 'Lulus',
     *     'col_registrasi'=> 'Registrasi',
     *   ]
     * @param  int   $batchId ID ImportBatch yang sudah dibuat
     */
    public function import(string $path, array $mapping, int $batchId): array
    {
        $tahunKode = (int) $mapping['tahun'];
        $tahunLengkap = (int) floor($tahunKode / 10);
        $semester = substr((string)$tahunKode, -1) === '1' ? 'Ganjil' : 'Genap';

        $tahunModel = DimTahunAkademik::firstOrCreate(
            ['tahun' => $tahunKode],
            ['keterangan' => 'Tahun ' . $tahunLengkap . ' ' . $semester]
        );

        $spreadsheet = IOFactory::load($path);
        $sheet       = $spreadsheet->getActiveSheet();
        $highRow     = $sheet->getHighestRow();
        $highCol     = $sheet->getHighestColumn();

        // Baca semua data sebagai array
        $data = $sheet->toArray(null, true, false, false);
        $headers = array_shift($data); // baris pertama = header

        $stats = ['inserted' => 0, 'skipped' => 0, 'antrian' => 0, 'errors' => []];

        DB::transaction(function () use ($data, $headers, $mapping, $tahunModel, $batchId, &$stats) {
            // Buat index header → posisi kolom
            $headerIndex = array_flip(array_map('trim', $headers));

            foreach ($data as $rowIdx => $row) {
                // Simpan ke staging
                ImportStagingRow::create([
                    'import_batch_id' => $batchId,
                    'baris_asal'      => array_combine($headers, $row),
                    'status'          => 'mapped',
                ]);

                if ($mapping['format'] === 'long') {
                    $this->processLongRow($row, $headerIndex, $mapping, $tahunModel, $batchId, $stats);
                } else {
                    $this->processWideRow($row, $headerIndex, $mapping, $tahunModel, $batchId, $stats);
                }
            }
        });

        $spreadsheet->disconnectWorksheets();

        ImportBatch::find($batchId)->update([
            'status' => $stats['antrian'] > 0 ? 'validated' : 'loaded',
            'catatan'=> "Import selesai: {$stats['inserted']} baris berhasil, {$stats['skipped']} dilewati, {$stats['antrian']} antrian.",
        ]);

        return $stats;
    }

    private function processLongRow(
        array $row, array $idx, array $mapping,
        $tahunModel, int $batchId, array &$stats
    ): void {
        $prodiStr = trim($row[$idx[$mapping['col_prodi']] ?? -1] ?? '');
        $jalurStr = trim($row[$idx[$mapping['col_jalur']] ?? -1] ?? '');

        if ($prodiStr === '' || $jalurStr === '') {
            $stats['skipped']++;
            return;
        }

        $prodiId = $this->resolver->resolveProdi($prodiStr);
        $jalurId = $this->resolver->resolveJalur($jalurStr);

        if (!$prodiId) {
            $this->resolver->enqueueAntrian('prodi', $prodiStr, $batchId);
            $stats['antrian']++;
        }
        if (!$jalurId) {
            $this->resolver->enqueueAntrian('jalur', $jalurStr, $batchId);
            $stats['antrian']++;
        }
        if (!$prodiId || !$jalurId) {
            $stats['skipped']++;
            return;
        }

        $key = ['tahun_akademik_id' => $tahunModel->id, 'program_studi_id' => $prodiId, 'jalur_seleksi_id' => $jalurId];

        $this->upsertFakta($key, $row, $idx, $mapping);
        $stats['inserted']++;
    }

    private function processWideRow(
        array $row, array $idx, array $mapping,
        $tahunModel, int $batchId, array &$stats
    ): void {
        $prodiStr = trim($row[$idx[$mapping['col_prodi']] ?? -1] ?? '');
        if ($prodiStr === '') { $stats['skipped']++; return; }

        $prodiId = $this->resolver->resolveProdi($prodiStr);
        if (!$prodiId) {
            $this->resolver->enqueueAntrian('prodi', $prodiStr, $batchId);
            $stats['antrian']++;
            $stats['skipped']++;
            return;
        }

        foreach ($mapping['jalur_kolom'] as $jalurKolom) {
            $jalurId = $this->resolver->resolveJalur($jalurKolom['jalur']);
            if (!$jalurId) {
                $this->resolver->enqueueAntrian('jalur', $jalurKolom['jalur'], $batchId);
                $stats['antrian']++;
                continue;
            }

            $key = ['tahun_akademik_id' => $tahunModel->id, 'program_studi_id' => $prodiId, 'jalur_seleksi_id' => $jalurId];
            $customMapping = [
                'col_kuota'     => $jalurKolom['kuota'] ?? null,
                'col_pil1'      => $jalurKolom['pil1'] ?? null,
                'col_pil2'      => $jalurKolom['pil2'] ?? null,
                'col_pil3'      => $jalurKolom['pil3'] ?? null,
                'col_pil4'      => $jalurKolom['pil4'] ?? null,
                'col_lulus'     => $jalurKolom['lulus'] ?? null,
                'col_registrasi'=> $jalurKolom['registrasi'] ?? null,
            ];
            $this->upsertFakta($key, $row, $idx, $customMapping);
            $stats['inserted']++;
        }
    }

    private function upsertFakta(array $key, array $row, array $idx, array $mapping): void
    {
        $getInt = function (?string $colName) use ($row, $idx): ?int {
            if (!$colName || !isset($idx[$colName])) return null;
            $v = $row[$idx[$colName]] ?? null;
            return is_numeric($v) ? (int) $v : null;
        };

        $kuota     = $getInt($mapping['col_kuota'] ?? null);
        $pil1      = $getInt($mapping['col_pil1'] ?? null) ?? 0;
        $pil2      = $getInt($mapping['col_pil2'] ?? null) ?? 0;
        $pil3      = $getInt($mapping['col_pil3'] ?? null) ?? 0;
        $pil4      = $getInt($mapping['col_pil4'] ?? null) ?? 0;
        $pendaftar = $getInt($mapping['col_daftar'] ?? null);

        $totalPendaftar = $pendaftar !== null ? $pendaftar : ($pil1 + $pil2 + $pil3 + $pil4);

        if ($kuota !== null) {
            \App\Models\PmbDayaTampung::updateOrCreate($key, ['daya_tampung' => $kuota]);
        }
        if ($totalPendaftar > 0) {
            \App\Models\PmbPeminatAgregat::updateOrCreate($key, ['jumlah_peminat' => $totalPendaftar]);
        }
    }
}
