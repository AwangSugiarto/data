<?php

namespace App\Livewire\MasterData;

use App\Models\DimStatusMahasiswa;
use Livewire\Component;

class StatusMahasiswaIndex extends Component
{
    // ── Form fields ───────────────────────────────────────────────────────────
    public ?int   $editingId          = null;
    public string $nama_status        = '';
    public string $kode_status        = '';
    public string $warna              = 'gray';
    public string $deskripsi          = '';
    public bool   $is_aktif_akademik  = false;
    public int    $urutan             = 0;

    public bool   $showForm           = false;
    public string $confirmDelete      = '';

    // Warna yang tersedia
    public array $warnaOptions = [
        'green'  => 'Hijau (Aktif)',
        'amber'  => 'Kuning (Perhatian)',
        'red'    => 'Merah (Kritis)',
        'blue'   => 'Biru (Info)',
        'purple' => 'Ungu',
        'gray'   => 'Abu-abu (Lainnya)',
    ];

    public function rules(): array
    {
        $uniqueNama  = 'unique:dim_status_mahasiswas,nama_status' . ($this->editingId ? ",{$this->editingId}" : '');
        $uniqueKode  = 'unique:dim_status_mahasiswas,kode_status' . ($this->editingId ? ",{$this->editingId}" : '');
        return [
            'nama_status'       => "required|string|max:60|{$uniqueNama}",
            'kode_status'       => "required|string|max:60|{$uniqueKode}",
            'warna'             => 'required|in:green,amber,red,blue,purple,gray',
            'deskripsi'         => 'nullable|string|max:255',
            'is_aktif_akademik' => 'boolean',
            'urutan'            => 'integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_status.unique' => 'Nama status sudah ada.',
            'kode_status.unique' => 'Kode status sudah ada.',
        ];
    }

    public function openCreate(): void
    {
        $this->reset(['editingId','nama_status','kode_status','deskripsi','is_aktif_akademik']);
        $this->warna  = 'gray';
        $this->urutan = DimStatusMahasiswa::max('urutan') + 1;
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $s = DimStatusMahasiswa::findOrFail($id);
        $this->editingId         = $id;
        $this->nama_status       = $s->nama_status;
        $this->kode_status       = $s->kode_status;
        $this->warna             = $s->warna;
        $this->deskripsi         = $s->deskripsi ?? '';
        $this->is_aktif_akademik = $s->is_aktif_akademik;
        $this->urutan            = $s->urutan;
        $this->showForm          = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            DimStatusMahasiswa::findOrFail($this->editingId)->update($data);
            session()->flash('success', "Status '{$this->nama_status}' berhasil diperbarui.");
        } else {
            DimStatusMahasiswa::create($data);
            session()->flash('success', "Status '{$this->nama_status}' berhasil ditambahkan.");
        }

        $this->showForm  = false;
        $this->editingId = null;
        $this->reset(['nama_status','kode_status','deskripsi','is_aktif_akademik']);
    }

    public function delete(int $id): void
    {
        $s = DimStatusMahasiswa::findOrFail($id);
        $nama = $s->nama_status;
        $s->delete();
        session()->flash('success', "Status '{$nama}' berhasil dihapus.");
    }

    public function cancel(): void
    {
        $this->showForm  = false;
        $this->editingId = null;
        $this->resetValidation();
    }

    // Auto-isi kode_status dari nama_status
    public function updatedNamaStatus(string $val): void
    {
        if (!$this->editingId) {
            // Hapus karakter tidak valid, pertahankan spasi jadi underscore
            $this->kode_status = $val;
        }
    }

    public function render()
    {
        $statusList = DimStatusMahasiswa::ordered()->get();
        return view('livewire.master-data.status-mahasiswa-index', compact('statusList'))
            ->layout('layouts.app', ['title' => 'Master Status Mahasiswa']);
    }
}
