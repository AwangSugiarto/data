<?php

namespace App\Exports;

use App\Models\FactPendaftaran;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AgregatExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    protected $tahunId;
    protected $fakultasId;
    protected $prodiId;
    protected $jalurId;

    public function __construct($tahunId, $fakultasId, $prodiId, $jalurId)
    {
        $this->tahunId = $tahunId;
        $this->fakultasId = $fakultasId;
        $this->prodiId = $prodiId;
        $this->jalurId = $jalurId;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query(): \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation
    {
        return FactPendaftaran::query()
            ->select([
                'fact_pendaftaran.tahun_akademik_id',
                'fact_pendaftaran.program_studi_id',
                'fact_pendaftaran.jalur_seleksi_id',
                'fact_pendaftaran.jumlah_daftar',
                'fact_kuota.kuota_akhir',
                'fact_kelulusan.jumlah_lulus',
                'fact_registrasi.jumlah_registrasi'
            ])
            ->leftJoin('fact_kuota', function($join) {
                $join->on('fact_pendaftaran.tahun_akademik_id', '=', 'fact_kuota.tahun_akademik_id')
                     ->on('fact_pendaftaran.program_studi_id', '=', 'fact_kuota.program_studi_id')
                     ->on('fact_pendaftaran.jalur_seleksi_id', '=', 'fact_kuota.jalur_seleksi_id');
            })
            ->leftJoin('fact_kelulusan', function($join) {
                $join->on('fact_pendaftaran.tahun_akademik_id', '=', 'fact_kelulusan.tahun_akademik_id')
                     ->on('fact_pendaftaran.program_studi_id', '=', 'fact_kelulusan.program_studi_id')
                     ->on('fact_pendaftaran.jalur_seleksi_id', '=', 'fact_kelulusan.jalur_seleksi_id');
            })
            ->leftJoin('fact_registrasi', function($join) {
                $join->on('fact_pendaftaran.tahun_akademik_id', '=', 'fact_registrasi.tahun_akademik_id')
                     ->on('fact_pendaftaran.program_studi_id', '=', 'fact_registrasi.program_studi_id')
                     ->on('fact_pendaftaran.jalur_seleksi_id', '=', 'fact_registrasi.jalur_seleksi_id');
            })
            ->with(['programStudi.fakultas', 'jalurSeleksi', 'tahunAkademik'])
            ->where('fact_pendaftaran.tahun_akademik_id', $this->tahunId)
            ->when($this->fakultasId, function($q) {
                $q->whereHas('programStudi', function($q2) {
                    $q2->where('fakultas_id', $this->fakultasId);
                });
            })
            ->when($this->prodiId, fn($q) => $q->where('fact_pendaftaran.program_studi_id', $this->prodiId))
            ->when($this->jalurId, fn($q) => $q->where('fact_pendaftaran.jalur_seleksi_id', $this->jalurId));
    }

    public function headings(): array
    {
        return [
            'Tahun Akademik',
            'Program Studi',
            'Kode Prodi',
            'Jenjang',
            'Jalur Seleksi',
            'Kuota',
            'Pendaftar',
            'Lulus',
            'Registrasi',
        ];
    }

    public function map($row): array
    {
        return [
            $row->tahunAkademik->tahun ?? '-',
            $row->programStudi->nama_prodi ?? '-',
            $row->programStudi->kode_prodi ?? '-',
            $row->programStudi->jenjang ?? '-',
            $row->jalurSeleksi->nama_jalur ?? '-',
            $row->kuota_akhir ?? 0,
            $row->jumlah_daftar ?? 0,
            $row->jumlah_lulus ?? 0,
            $row->jumlah_registrasi ?? 0,
        ];
    }
}
