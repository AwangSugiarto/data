<?php

namespace App\Exports;

use App\Models\DimProgramStudi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Enumerable;

class TarifBiayaTemplateExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Enumerable
    */
    public function collection(): Enumerable
    {
        return DimProgramStudi::where('status_terkini', true)
            ->orderBy('fakultas_id')
            ->orderBy('nama_prodi')
            ->get()
            ->map(function ($prodi) {
                return [
                    'kode_prodi' => $prodi->kode_prodi,
                    'nama_prodi' => $prodi->nama_prodi,
                    'jenjang' => $prodi->jenjang,
                    'spp' => '',
                    'ukt_1' => '',
                    'ukt_2' => '',
                    'ukt_3' => '',
                    'ukt_4' => '',
                    'ukt_5' => '',
                    'ukt_6' => '',
                    'ukt_7' => '',
                    'kip' => '',
                    'mahasiswa_asing' => '',
                    'kerjasama' => '',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Kode Prodi',
            'Nama Prodi',
            'Jenjang',
            'SPP',
            'UKT 1',
            'UKT 2',
            'UKT 3',
            'UKT 4',
            'UKT 5',
            'UKT 6',
            'UKT 7',
            'KIP',
            'Mahasiswa Asing',
            'Kerjasama'
        ];
    }
}
