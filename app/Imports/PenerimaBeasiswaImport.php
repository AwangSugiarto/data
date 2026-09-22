<?php

namespace App\Imports;

use App\Models\FactPenerimaBeasiswa;
use App\Models\DimBeasiswa;
use App\Models\DimProgramStudi;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PenerimaBeasiswaImport implements ToCollection, WithHeadingRow
{
    protected $tahunAkademikId;

    public function __construct($tahunAkademikId)
    {
        $this->tahunAkademikId = $tahunAkademikId;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (!isset($row['nim']) || !isset($row['kode_prodi']) || !isset($row['kode_beasiswa']) || !isset($row['tahun_akademik'])) {
                continue; // Skip invalid rows
            }

            // Find Program Studi
            $prodi = DimProgramStudi::where('kode_prodi', $row['kode_prodi'])->first();
            
            // Find Beasiswa
            $beasiswa = DimBeasiswa::where('kode_beasiswa', strtoupper($row['kode_beasiswa']))->first();
            
            // Find Tahun Akademik
            $tahunAkademik = \App\Models\DimTahunAkademik::where('tahun', $row['tahun_akademik'])->first();

            if ($prodi && $beasiswa && $tahunAkademik) {
                FactPenerimaBeasiswa::updateOrCreate(
                    [
                        'tahun_akademik_id' => $tahunAkademik->id,
                        'nim' => $row['nim'],
                        'dim_beasiswa_id' => $beasiswa->id,
                    ],
                    [
                        'program_studi_id' => $prodi->id,
                        'nama_mahasiswa' => $row['nama_mahasiswa'] ?? null,
                    ]
                );
            }
        }
    }
}
