<?php

namespace App\Livewire\Laporan;

use App\Models\DimFakultas;
use App\Models\DimJalurSeleksi;
use App\Models\DimTahunAkademik;
use App\Models\FactKelulusan;
use App\Models\FactKuota;
use App\Models\FactPendaftaran;
use App\Models\FactRegistrasi;
use Livewire\Component;

class LaporanPmbIndex extends Component
{
    public $tahunId = '';
    public $jalurId = '';
    public $fakultasId = '';
    public $prodiId = '';
    
    public $prodiList = [];

    public function updatedFakultasId($value)
    {
        if ($value) {
            $this->prodiList = \App\Models\DimProgramStudi::where('status_terkini', true)
                ->where('fakultas_id', $value)->orderBy('nama_prodi')->get();
        } else {
            $this->prodiList = [];
        }
        $this->prodiId = '';
    }

    public function render()
    {
        $tahunList = DimTahunAkademik::orderBy('tahun', 'desc')->get();
        $jalurList = DimJalurSeleksi::where('status_aktif', true)->orderBy('nama_jalur')->get();
        $fakultasListDropdown = DimFakultas::where('status_terkini', true)->orderBy('nama_fakultas')->get();

        // Ambil data Fakultas beserta Prodinya, lalu hitung agregat berdasarkan filter
        $fakultasList = DimFakultas::where('status_terkini', true)
        ->with(['programStudi' => function($q) {
            $q->where('status_terkini', true)
              ->when($this->prodiId, fn($q2) => $q2->where('id', $this->prodiId))
              ->orderBy('nama_prodi');
        }])
        ->when($this->fakultasId, fn($q) => $q->where('id', $this->fakultasId))
        ->orderBy('nama_fakultas')->get();

        $laporanData = [];

        foreach ($fakultasList as $fakultas) {
            $prodiData = [];
            foreach ($fakultas->programStudi as $prodi) {
                
                $kuota = \App\Models\PmbDayaTampung::where('program_studi_id', $prodi->id)
                    ->when($this->tahunId, fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
                    ->when($this->jalurId, fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
                    ->sum('daya_tampung');

                $daftar = \App\Models\PmbPeminatAgregat::where('program_studi_id', $prodi->id)
                    ->when($this->tahunId, fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
                    ->when($this->jalurId, fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
                    ->sum('jumlah_peminat');
                
                $lulus = \App\Models\CalonMahasiswa::where('program_studi_id', $prodi->id)
                    ->where(function($q) {
                        $q->where('is_lulus', true)
                          ->orWhereHas('mahasiswa');
                    })
                    ->when($this->tahunId, fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
                    ->when($this->jalurId, fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
                    ->count();

                $registrasi = \App\Models\CalonMahasiswa::where('program_studi_id', $prodi->id)
                    ->whereHas('mahasiswa')
                    ->when($this->tahunId, fn($q) => $q->where('tahun_akademik_id', $this->tahunId))
                    ->when($this->jalurId, fn($q) => $q->where('jalur_seleksi_id', $this->jalurId))
                    ->count();

                if ($kuota > 0 || $daftar > 0 || $lulus > 0 || $registrasi > 0) {
                    $prodiData[] = [
                        'nama_prodi' => $prodi->nama_prodi,
                        'jenjang' => $prodi->jenjang,
                        'kuota' => $kuota,
                        'daftar' => $daftar,
                        'lulus' => $lulus,
                        'registrasi' => $registrasi,
                    ];
                }
            }

            if (!empty($prodiData)) {
                $laporanData[] = [
                    'nama_fakultas' => $fakultas->nama_fakultas,
                    'prodi' => $prodiData,
                    'total_kuota' => collect($prodiData)->sum('kuota'),
                    'total_daftar' => collect($prodiData)->sum('daftar'),
                    'total_lulus' => collect($prodiData)->sum('lulus'),
                    'total_registrasi' => collect($prodiData)->sum('registrasi'),
                ];
            }
        }

        return view('livewire.laporan.laporan-pmb-index', compact('tahunList', 'jalurList', 'fakultasListDropdown', 'laporanData'))
            ->layout('layouts.app', ['title' => 'Laporan Rekapitulasi PMB']);
    }
}
