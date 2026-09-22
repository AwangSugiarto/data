<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', \App\Livewire\Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// ─── Master Data (admin-pmb dan super-admin) ─────────────────────────────────
Route::middleware(['auth', 'can:view_master_data'])->prefix('master')->name('master.')->group(function () {
    Route::get('fakultas',       \App\Livewire\MasterData\FakultasIndex::class)->name('fakultas');
    Route::get('prodi',          \App\Livewire\MasterData\ProdiIndex::class)->name('prodi');
    Route::get('jalur',          \App\Livewire\MasterData\JalurSeleksiIndex::class)->name('jalur');
    Route::get('kelompok-jalur', \App\Livewire\MasterData\KelompokJalurIndex::class)->name('kelompok-jalur');
    Route::get('wilayah',        \App\Livewire\MasterData\WilayahIndex::class)->name('wilayah');
    Route::get('negara',         \App\Livewire\MasterData\NegaraIndex::class)->name('negara');
    Route::get('lembaga',        \App\Livewire\MasterData\LembagaIndex::class)->name('lembaga');
    Route::get('sekolah',        \App\Livewire\MasterData\SekolahIndex::class)->name('sekolah');

    Route::get('tarif-ukt',       \App\Livewire\MasterData\TarifUktIndex::class)->name('tarif-ukt');
    Route::get('antrian',         \App\Livewire\MasterData\AntrianIndex::class)->name('antrian');
    Route::get('beasiswa',        \App\Livewire\MasterData\BeasiswaIndex::class)->name('beasiswa');
    Route::get('status-mahasiswa',\App\Livewire\MasterData\StatusMahasiswaIndex::class)->name('status-mahasiswa');
});

// ─── Import Excel & Entri Manual (admin-pmb dan super-admin) ─────────────────────────────────
Route::middleware(['auth', 'can:view_import'])->prefix('entri-data')->name('entri-data.')->group(function () {
    Route::get('master-pmb', \App\Livewire\MasterPmb\MasterPmbIndex::class)->name('master-pmb');
    Route::get('agregat', \App\Livewire\Input\InputAgregat::class)->name('agregat');
    Route::get('manual', \App\Livewire\Input\InputManual::class)->name('manual');
});

Route::middleware(['auth', 'can:view_import'])->prefix('import')->name('import.')->group(function () {
    Route::get('/',      \App\Livewire\Import\ImportWizard::class)->name('wizard');
    Route::get('riwayat', \App\Livewire\Import\ImportRiwayat::class)->name('riwayat');
});

// ─── Laporan PMB ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'can:view_laporan'])->prefix('laporan')->name('laporan.')->group(function () {
    Route::get('pmb', \App\Livewire\Laporan\LaporanPmbIndex::class)->name('pmb');
});

// ─── Pengaturan ────────────────────────────────────────────
Route::middleware(['auth', 'can:manage_roles_permissions'])->prefix('pengaturan')->name('pengaturan.')->group(function () {
    Route::get('pengguna', \App\Livewire\Pengaturan\UserIndex::class)->name('users');
    Route::get('roles', \App\Livewire\Pengaturan\RoleIndex::class)->name('roles');
    Route::get('permissions', \App\Livewire\Pengaturan\PermissionIndex::class)->name('permissions');
});

// ─── Manajemen PMB ────────────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('manajemen-pmb')->name('manajemen-pmb.')->group(function () {
    // Halaman baru: gabungan Import Data Kelulusan + Entri Manual
    Route::get('entri-data', \App\Livewire\ManajemenPmb\EntriDataPmb::class)->name('entri-data');
    // Redirect lama → baru (backward compatibility)
    Route::redirect('import-cama', '/manajemen-pmb/entri-data')->name('import-cama');
    Route::get('penetapan-registrasi', \App\Livewire\ManajemenPmb\PenetapanRegistrasiIndex::class)->name('penetapan-registrasi');
});

// ─── Akademik ─────────────────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('akademik')->name('akademik.')->group(function () {
    Route::get('mahasiswa',  \App\Livewire\Akademik\MahasiswaIndex::class)->name('mahasiswa');
    Route::get('dashboard',  \App\Livewire\Akademik\MahasiswaDashboard::class)->name('dashboard');
});

// ─── Data Individu ────────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('data-individu', \App\Livewire\DataIndividu::class)->name('data-individu');
});

require __DIR__.'/auth.php';

