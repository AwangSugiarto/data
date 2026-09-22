<?php

namespace App\Services\Import;

use App\Models\DimKelompokUkt;
use App\Models\FactPendaftarIndividu;
use App\Models\ImportBatch;
use App\Models\ImportStagingRow;
use App\Models\MasterMahasiswa;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelImportMahasiswaAktifService
{
    public function import(string $path, array $mapping, int $batchId): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet       = $spreadsheet->getActiveSheet();

        $data = $sheet->toArray(null, true, false, false);
        $headers = array_shift($data);

        $stats = ['updated' => 0, 'skipped' => 0, 'errors' => 0];

        $kelompokUktList = DimKelompokUkt::all();

        DB::transaction(function () use ($data, $headers, $mapping, $batchId, $kelompokUktList, &$stats) {
            $headerIndex = array_flip(array_map('trim', $headers));

            foreach ($data as $row) {
                ImportStagingRow::create([
                    'import_batch_id' => $batchId,
                    'baris_asal'      => array_combine($headers, $row),
                    'status'          => 'mapped',
                ]);

                $this->processRow($row, $headerIndex, $mapping, $kelompokUktList, $stats);
            }
        });

        $spreadsheet->disconnectWorksheets();

        ImportBatch::find($batchId)->update([
            'status' => 'loaded',
            'catatan'=> "Import Mahasiswa Aktif selesai: {$stats['updated']} berhasil, {$stats['skipped']} dilewati, {$stats['errors']} error.",
        ]);

        return $stats;
    }

    private function processRow(
        array $row, array $idx, array $mapping,
        $kelompokUktList, array &$stats
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

        $nim         = $getString($mapping['col_nim']);
        $statusStr   = $getString($mapping['col_status']);
        $uktNominal  = $getString($mapping['col_ukt_nominal']);

        $calon = \App\Models\CalonMahasiswa::where('nomor_pendaftaran', $nomorTes)->first();

        if (!$calon) {
            $stats['skipped']++;
            return;
        }

        try {
            // Update Status Akademik
            $newStatus = 'Aktif';
            if ($statusStr) {
                $statusLower = strtolower($statusStr);
                if (str_contains($statusLower, 'alumni') || str_contains($statusLower, 'lulus')) {
                    $newStatus = 'Lulus';
                } elseif (str_contains($statusLower, 'stop')) {
                    $newStatus = 'Stop Out';
                } elseif (str_contains($statusLower, 'drop') || str_contains($statusLower, 'do')) {
                    $newStatus = 'Drop Out';
                } elseif (str_contains($statusLower, 'undur')) {
                    $newStatus = 'Mengundurkan Diri';
                } elseif (str_contains($statusLower, 'meninggal')) {
                    $newStatus = 'Meninggal';
                }
            }
            
            // Match UKT Nominal
            if ($uktNominal !== null) {
                $nominalFloat = (float) preg_replace('/[^0-9.]/', '', $uktNominal);
                $matchedUktId = null;

                foreach ($kelompokUktList as $ukt) {
                    if ($nominalFloat >= (float)$ukt->nominal_min && $nominalFloat <= (float)$ukt->nominal_max) {
                        $matchedUktId = $ukt->id;
                        break;
                    }
                }
                
                if ($matchedUktId) {
                    $calon->kelompok_ukt_id = $matchedUktId;
                }
            }

            $calon->save();
            
            $tanggalLulus = null;
            if ($newStatus === 'Lulus') {
                $tanggalStr = $getString($mapping['col_tanggal_lulus'] ?? '');
                if ($tanggalStr) {
                    $tanggalLulus = date('Y-m-d', strtotime($tanggalStr));
                }
            }

            if ($nim) {
                \App\Models\Mahasiswa::updateOrCreate(
                    ['calon_mahasiswa_id' => $calon->id],
                    [
                        'nim' => $nim,
                        'tahun_masuk_id' => $calon->tahun_akademik_id,
                        'status_akademik' => $newStatus,
                        'tanggal_lulus' => $tanggalLulus,
                    ]
                );
            }

            $stats['updated']++;
        } catch (\Exception $e) {
            $stats['errors']++;
        }
    }
}
