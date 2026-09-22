<?php

namespace App\Imports;

use App\Models\DimProgramStudi;
use App\Models\DimKelompokUkt;
use App\Models\DimTarifUkt;
use App\Models\DimTarifSpp;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TarifBiayaImport implements ToCollection, WithHeadingRow
{
    protected $tahunAkademikId;

    public function __construct($tahunAkademikId)
    {
        $this->tahunAkademikId = $tahunAkademikId;
    }

    public function collection(Collection $rows): void
    {
        $ignoreHeaders = ['kode_prodi', 'nama_prodi', 'jenjang', 'fakultas', 'spp'];

        foreach ($rows as $row) {
            // Find Prodi by Kode Prodi
            $kodeProdi = $row['kode_prodi'] ?? null;
            if (!$kodeProdi) continue;

            $prodi = DimProgramStudi::where('kode_prodi', $kodeProdi)
                ->where('status_terkini', true)
                ->first();
                
            if (!$prodi) continue;
            
            $jenjang = strtoupper($prodi->jenjang);

            if (in_array($jenjang, ['S1', 'D3', 'D4'])) {
                // Process UKT
                foreach ($row as $header => $value) {
                    if (in_array($header, $ignoreHeaders) || is_null($value) || $value === '') continue;

                    // The header is the Kelompok UKT name (e.g. "ukt_1" or "ukt 1", excel header formatting usually makes it lowercase and underscores)
                    // But we want the display name, maybe we should just uppercase it and replace underscores with space.
                    $kelompokName = strtoupper(str_replace('_', ' ', $header));
                    if (str_starts_with($kelompokName, 'UKT ')) {
                        $kelompokName = str_replace('UKT ', '', $kelompokName);
                    }

                    $kelompok = DimKelompokUkt::firstOrCreate([
                        'tahun_akademik_id' => $this->tahunAkademikId,
                        'kelompok' => $kelompokName
                    ]);

                    $cleanValue = preg_replace('/[^0-9.]/', '', $value);
                    if ($cleanValue === '') continue;

                    DimTarifUkt::updateOrCreate(
                        [
                            'tahun_akademik_id' => $this->tahunAkademikId,
                            'program_studi_id' => $prodi->id,
                            'kelompok_ukt_id' => $kelompok->id,
                        ],
                        [
                            'nominal' => $cleanValue
                        ]
                    );
                }
            } else {
                // Process SPP
                $sppValue = $row['spp'] ?? null;
                if ($sppValue !== null && $sppValue !== '') {
                    $cleanSppValue = preg_replace('/[^0-9.]/', '', $sppValue);
                    if ($cleanSppValue !== '') {
                        DimTarifSpp::updateOrCreate(
                            [
                                'tahun_akademik_id' => $this->tahunAkademikId,
                                'program_studi_id' => $prodi->id,
                            ],
                            [
                                'nominal' => $cleanSppValue
                            ]
                        );
                    }
                }
            }
        }
    }
}
