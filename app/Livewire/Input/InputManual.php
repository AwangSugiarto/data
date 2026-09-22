<?php

namespace App\Livewire\Input;

use App\Models\DimFakultas;
use App\Models\DimJalurSeleksi;
use App\Models\DimKelompokUkt;
use App\Models\DimProgramStudi;
use App\Models\DimTahunAkademik;
use App\Models\DimWilayah;
use App\Models\FactKelulusan;
use App\Models\FactPendaftaran;
use App\Models\FactPendaftarIndividu;
use App\Models\FactRegistrasi;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class InputManual extends Component
{
    use WithPagination;
    // Form fields
    public $tahun_akademik_id = '';
    public $fakultas_id = '';
    public $program_studi_id = '';
    public $jalur_seleksi_id = '';
    public $wilayah_id = '';
    public $kelompok_ukt_id = '';
    
    public $nomor_tes = '';
    public $nisn = '';
    public $npsn = '';
    public $nama = '';
    public $jenis_kelamin = '';
    public $asal_sekolah = '';
    public $jenis_sekolah = '';
    public $status = 'lulus';
    
    public $editId = null;

    // Bulk actions & pagination
    public $selectedRows = [];
    public $selectAll = false;

    // Collections for select dropdowns
    public $tahunList;
    public $fakultasList;
    public $prodiList = [];
    public $jalurList;
    public $wilayahList;
    public $uktList;

    protected function rules()
    {
        return [
            'tahun_akademik_id' => 'required|exists:dim_tahun_akademik,id',
            'program_studi_id'  => 'required|exists:dim_program_studi,id',
            'jalur_seleksi_id'  => 'required|exists:dim_jalur_seleksi,id',
            'wilayah_id'        => 'required|exists:dim_wilayah,id',
            'kelompok_ukt_id'   => 'required|exists:dim_kelompok_ukt,id',
            'nomor_tes'         => 'required|string|max:50|unique:fact_pendaftar_individu,nomor_tes,' . $this->editId,
            'nisn'              => 'nullable|string|max:50',
            'npsn'              => 'nullable|string|max:50',
            'nama'              => 'required|string|max:255',
            'jenis_kelamin'     => 'required|in:L,P',
            'asal_sekolah'      => 'required|string|max:255',
            'jenis_sekolah'     => 'required|string|max:50',
            'status'            => 'required|in:pendaftar,lulus,registrasi,alumni',
        ];
    }

    public function mount()
    {
        $this->tahunList   = DimTahunAkademik::orderBy('tahun', 'desc')->get();
        $this->fakultasList = DimFakultas::where('status_terkini', true)->orderBy('nama_fakultas')->get();
        $this->jalurList   = DimJalurSeleksi::where('status_aktif', true)->orderBy('nama_jalur')->get();
        $this->wilayahList = DimWilayah::orderBy('provinsi')->get();
        $this->uktList     = DimKelompokUkt::orderBy('kelompok')->get();
    }

    public function updatedFakultasId($value)
    {
        if ($value) {
            $this->prodiList = DimProgramStudi::where('status_terkini', true)
                ->where('fakultas_id', $value)->orderBy('nama_prodi')->get();
        } else {
            $this->prodiList = [];
        }
        $this->program_studi_id = '';
    }

    public function updatedTahunAkademikId() { $this->resetPage(); }
    public function updatedJalurSeleksiId() { $this->resetPage(); }
    public function updatedProgramStudiId() { $this->resetPage(); }

    public function save()
    {
        $this->validate();

        DB::beginTransaction();

        try {
            // 1. Simpan/Update Data Individu
            $data = [
                'tahun_akademik_id' => $this->tahun_akademik_id,
                'program_studi_id'  => $this->program_studi_id,
                'jalur_seleksi_id'  => $this->jalur_seleksi_id,
                'wilayah_id'        => $this->wilayah_id,
                'kelompok_ukt_id'   => $this->kelompok_ukt_id,
                'nomor_tes'         => $this->nomor_tes,
                'nisn'              => $this->nisn,
                'npsn'              => $this->npsn,
                'nama'              => $this->nama,
                'status'            => $this->status,
                'jenis_kelamin'     => $this->jenis_kelamin,
                'asal_sekolah'      => $this->asal_sekolah,
                'jenis_sekolah'     => $this->jenis_sekolah,
            ];

            if ($this->editId) {
                $pendaftar = FactPendaftarIndividu::findOrFail($this->editId);
                $pendaftar->update($data);
                $message = 'Data Pendaftar "' . $this->nama . '" berhasil diperbarui.';
            } else {
                FactPendaftarIndividu::create($data);
                $message = 'Data Pendaftar "' . $this->nama . '" berhasil disimpan.';
            }

            // Note: Sinkronisasi Agregat sederhana (Bisa diperbaiki jika butuh akurasi tinggi saat update/delete)
            $agregatConditions = [
                'tahun_akademik_id' => $this->tahun_akademik_id,
                'program_studi_id'  => $this->program_studi_id,
                'jalur_seleksi_id'  => $this->jalur_seleksi_id,
            ];

            if (!$this->editId) {
                if (in_array($this->status, ['pendaftar', 'lulus', 'registrasi', 'alumni'])) {
                    $factDaftar = FactPendaftaran::firstOrCreate($agregatConditions, ['jumlah_daftar' => 0]);
                    $factDaftar->increment('jumlah_daftar');
                }
                if (in_array($this->status, ['lulus', 'registrasi', 'alumni'])) {
                    $factLulus = FactKelulusan::firstOrCreate($agregatConditions, ['jumlah_lulus' => 0]);
                    $factLulus->increment('jumlah_lulus');
                }
                if (in_array($this->status, ['registrasi', 'alumni'])) {
                    $factReg = FactRegistrasi::firstOrCreate($agregatConditions, ['jumlah_registrasi' => 0]);
                    $factReg->increment('jumlah_registrasi');
                }
            }

            DB::commit();

            session()->flash('success', $message);
            $this->resetForm();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $pendaftar = FactPendaftarIndividu::findOrFail($id);
        
        $this->editId = $pendaftar->id;
        $this->tahun_akademik_id = $pendaftar->tahun_akademik_id;
        
        $prodi = DimProgramStudi::find($pendaftar->program_studi_id);
        if ($prodi) {
            $this->fakultas_id = $prodi->fakultas_id;
            $this->updatedFakultasId($this->fakultas_id);
        }
        
        $this->program_studi_id = $pendaftar->program_studi_id;
        $this->jalur_seleksi_id = $pendaftar->jalur_seleksi_id;
        $this->wilayah_id = $pendaftar->wilayah_id;
        $this->kelompok_ukt_id = $pendaftar->kelompok_ukt_id;
        $this->nomor_tes = $pendaftar->nomor_tes;
        $this->nisn = $pendaftar->nisn;
        $this->npsn = $pendaftar->npsn;
        $this->nama = $pendaftar->nama;
        $this->jenis_kelamin = $pendaftar->jenis_kelamin;
        $this->asal_sekolah = $pendaftar->asal_sekolah;
        $this->jenis_sekolah = $pendaftar->jenis_sekolah;
        $this->status = $pendaftar->status;
    }

    public function delete($id)
    {
        FactPendaftarIndividu::findOrFail($id)->delete();
        session()->flash('success', 'Data pendaftar berhasil dihapus.');
    }

    public function cancelEdit()
    {
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'editId', 'fakultas_id', 'program_studi_id', 'nomor_tes', 'nisn', 'npsn', 'nama', 
            'jenis_kelamin', 'asal_sekolah', 'jenis_sekolah', 'status'
        ]);
        $this->prodiList = [];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            // Kita ambil ID dari current page saja
            $recentData = $this->getRecentDataQuery()->paginate(10);
            $this->selectedRows = collect($recentData->items())->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function updatedSelectedRows()
    {
        // Auto-uncheck 'select all' if not all are selected (simple UX)
        $this->selectAll = false;
    }

    public function bulkDelete()
    {
        if (empty($this->selectedRows)) return;

        FactPendaftarIndividu::whereIn('id', $this->selectedRows)->delete();
        session()->flash('success', count($this->selectedRows) . ' data pendaftar berhasil dihapus.');
        
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    private function getRecentDataQuery()
    {
        $query = FactPendaftarIndividu::with(['programStudi', 'jalurSeleksi']);

        // Filter berdasarkan isian form agar tabel relevan
        if ($this->tahun_akademik_id) {
            $query->where('tahun_akademik_id', $this->tahun_akademik_id);
        }
        if ($this->jalur_seleksi_id) {
            $query->where('jalur_seleksi_id', $this->jalur_seleksi_id);
        }
        if ($this->program_studi_id) {
            $query->where('program_studi_id', $this->program_studi_id);
        }

        return $query->orderBy('updated_at', 'desc');
    }

    public function render()
    {
        $recentData = $this->getRecentDataQuery()->paginate(10);

        return view('livewire.input.input-manual', [
            'recentData' => $recentData
        ])->layout('layouts.app', ['title' => 'Entri Manual Data PMB']);
    }
}
