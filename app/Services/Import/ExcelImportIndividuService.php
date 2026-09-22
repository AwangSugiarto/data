<?php

namespace App\Services\Import;

use App\Models\DimTahunAkademik;
use App\Models\FactPendaftarIndividu;
use App\Models\ImportBatch;
use App\Models\ImportStagingRow;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelImportIndividuService
{
    public function __construct(
        private readonly MasterDataResolverService $resolver
    ) {}

    public function stageImport(string $path, int $batchId): int
    {
        $spreadsheet = IOFactory::load($path);
        $sheet       = $spreadsheet->getActiveSheet();

        $data = $sheet->toArray(null, true, false, false);
        $headers = array_shift($data);

        $totalRows = count($data);

        // Bulk insert to staging
        $chunks = array_chunk($data, 500);
        foreach ($chunks as $chunk) {
            $insertData = [];
            foreach ($chunk as $row) {
                // Ensure the row has the same number of elements as headers
                // Sometimes PhpSpreadsheet returns fewer columns if trailing cells are empty.
                $rowData = [];
                foreach ($headers as $index => $header) {
                    $rowData[trim($header)] = $row[$index] ?? null;
                }

                $insertData[] = [
                    'import_batch_id' => $batchId,
                    'baris_asal'      => json_encode($rowData),
                    'status'          => 'mapped',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
            ImportStagingRow::insert($insertData);
        }

        $spreadsheet->disconnectWorksheets();

        return $totalRows;
    }

    public function processChunk(int $batchId, array $mapping, int $limit, array &$stats): int
    {
        $tahunKode = (int) $mapping['tahun'];
        $tahunLengkap = (int) floor($tahunKode / 10);
        $semester = substr((string)$tahunKode, -1) === '1' ? 'Ganjil' : 'Genap';

        $tahunModel = DimTahunAkademik::firstOrCreate(
            ['tahun' => $tahunKode],
            ['keterangan' => 'Tahun ' . $tahunLengkap . ' ' . $semester]
        );

        $rowsToProcess = ImportStagingRow::where('import_batch_id', $batchId)
            ->where('status', 'mapped')
            ->limit($limit)
            ->get();

        if ($rowsToProcess->isEmpty()) {
            return 0; // Done
        }

        DB::transaction(function () use ($rowsToProcess, $mapping, $tahunModel, $batchId, &$stats) {
            foreach ($rowsToProcess as $stagingRow) {
                $rowArray = $stagingRow->baris_asal; // It's cast to array
                // processRow requires $row (indexed array) and $idx (associative mapping).
                // Wait, processRow currently uses indexed array and associative headerIndex.
                // We stored it as associative array directly in stageImport. 
                // So we need to adapt processRow to use associative array, or rebuild the indexed one.
                // Let's rebuild the indexed one to minimize changes in processRow.
                $headers = array_keys($rowArray);
                $rowValues = array_values($rowArray);
                $headerIndex = array_flip($headers);

                $this->processRow($rowValues, $headerIndex, $mapping, $tahunModel, $batchId, $stats);

                $stagingRow->update(['status' => 'processed']);
            }
        });

        return $rowsToProcess->count();
    }

    public function finalizeImport(int $batchId, array $stats): void
    {
        ImportBatch::find($batchId)->update([
            'status' => $stats['antrian'] > 0 ? 'validated' : 'loaded',
            'catatan'=> "Import selesai: {$stats['inserted']} baris berhasil/update, {$stats['skipped']} dilewati, {$stats['antrian']} antrian.",
        ]);
    }

    private function processRow(
        array $row, array $idx, array $mapping,
        $tahunModel, int $batchId, array &$stats
    ): void {
        $getString = function (?string $colName) use ($row, $idx): ?string {
            if (!$colName || !isset($idx[$colName])) return null;
            $v = $row[$idx[$colName]] ?? null;
            return $v !== null && trim($v) !== '' ? trim($v) : null;
        };

        $nomorTes = $getString($mapping['col_nomor_tes']);
        if (!$nomorTes) {
            $stats['skipped']++;
            return;
        }

        $prodiStr = $getString($mapping['col_prodi']);
        $jalurStr = $getString($mapping['col_jalur']);

        if (!$prodiStr || !$jalurStr) {
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
        
        $wilayahId = null;
        $provStr = $getString($mapping['col_provinsi'] ?? '');
        $kabStr = $getString($mapping['col_kabupaten'] ?? '');
        $kecStr = $getString($mapping['col_kecamatan'] ?? '');
        
        if ($provStr || $kabStr || $kecStr) {
            $wilayahId = $this->resolver->resolveWilayah($provStr, $kabStr, $kecStr);
            if (!$wilayahId) {
                $gabungan = implode(' | ', array_filter([$provStr, $kabStr, $kecStr]));
                $this->resolver->enqueueAntrian('wilayah', $gabungan, $batchId);
                $stats['antrian']++;
            }
        }
        
        $uktId = null;
        $uktStr = $getString($mapping['col_ukt']);
        if ($uktStr) {
            $uktId = $this->resolver->resolveUkt($uktStr);
            if (!$uktId) {
                $this->resolver->enqueueAntrian('ukt', $uktStr, $batchId);
                $stats['antrian']++;
            }
        }

        $warganegaraId = null;
        $wnStr = $getString($mapping['col_warganegara'] ?? '');
        $wnKodeStr = $getString($mapping['col_kode_negara'] ?? '');
        if ($wnStr || $wnKodeStr) {
            $warganegaraId = $this->resolver->resolveWarganegara($wnStr, $wnKodeStr);
            if (!$warganegaraId) {
                $gabungan = implode(' | ', array_filter([$wnStr, $wnKodeStr]));
                $this->resolver->enqueueAntrian('warganegara', $gabungan, $batchId);
                $stats['antrian']++;
            }
        }

        if (!$prodiId || !$jalurId) {
            $stats['skipped']++;
            return;
        }

        $statusStr = $getString($mapping['col_status']);
        // Parse status to enum ('daftar','lulus','registrasi','tidak_registrasi','alumni')
        $status = 'daftar';
        if ($statusStr) {
            $statusLower = strtolower($statusStr);
            if (str_contains($statusLower, 'lulus')) $status = 'lulus';
            if (str_contains($statusLower, 'registrasi') || str_contains($statusLower, 'daftar ulang')) $status = 'registrasi';
            if (str_contains($statusLower, 'tidak') && str_contains($statusLower, 'registrasi')) $status = 'tidak_registrasi';
            if (str_contains($statusLower, 'alumni')) $status = 'alumni';
        }

        $isLulus = $status !== 'daftar';

        \App\Models\CalonMahasiswa::updateOrCreate(
            [
                'nomor_pendaftaran' => $nomorTes,
                'tahun_akademik_id' => $tahunModel->id,
            ],
            [
                'program_studi_id' => $prodiId,
                'jalur_seleksi_id' => $jalurId,
                'wilayah_id' => $wilayahId,
                'kelompok_ukt_id' => $uktId,
                'nama' => $getString($mapping['col_nama']),
                'jenis_kelamin' => $getString($mapping['col_jk'] ?? ''),
                'asal_sekolah' => $getString($mapping['col_asal_sekolah'] ?? ''),
                'jenis_sekolah' => $getString($mapping['col_jenis_sekolah'] ?? ''),
                'nisn' => $getString($mapping['col_nisn'] ?? ''),
                'npsn' => $getString($mapping['col_npsn'] ?? ''),
                'is_lulus' => $isLulus,
                'warganegara_id' => $warganegaraId,
            ]
        );

        $stats['inserted']++;
    }
}
