<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dim_status_mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_status')->unique();        // Aktif, Stop Out, dll
            $table->string('kode_status')->unique();        // aktif, stop_out, dll (untuk value di kode)
            $table->string('warna')->default('gray');       // green, amber, red, blue, purple, gray
            $table->string('deskripsi')->nullable();
            $table->boolean('is_aktif_akademik')->default(false); // apakah dihitung sebagai "mahasiswa aktif"
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // Seed data default
        $now = now();
        DB::table('dim_status_mahasiswas')->insert([
            ['nama_status' => 'Aktif',             'kode_status' => 'Aktif',             'warna' => 'green',  'deskripsi' => 'Mahasiswa sedang menempuh studi (Semester 1–14)', 'is_aktif_akademik' => true,  'urutan' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['nama_status' => 'Stop Out',          'kode_status' => 'Stop Out',          'warna' => 'amber',  'deskripsi' => 'Mahasiswa cuti / berhenti sementara',              'is_aktif_akademik' => false, 'urutan' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['nama_status' => 'Drop Out',          'kode_status' => 'Drop Out',          'warna' => 'red',    'deskripsi' => 'Mahasiswa dikeluarkan atau melebihi batas studi',   'is_aktif_akademik' => false, 'urutan' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['nama_status' => 'Lulus',             'kode_status' => 'Lulus',             'warna' => 'blue',   'deskripsi' => 'Mahasiswa telah menyelesaikan studi',               'is_aktif_akademik' => false, 'urutan' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['nama_status' => 'Alumni',            'kode_status' => 'Alumni',            'warna' => 'blue',   'deskripsi' => 'Lulusan yang telah terdaftar sebagai alumni',        'is_aktif_akademik' => false, 'urutan' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['nama_status' => 'Mengundurkan Diri', 'kode_status' => 'Mengundurkan Diri', 'warna' => 'purple', 'deskripsi' => 'Mahasiswa mengundurkan diri secara sukarela',       'is_aktif_akademik' => false, 'urutan' => 6, 'created_at' => $now, 'updated_at' => $now],
            ['nama_status' => 'Meninggal',         'kode_status' => 'Meninggal',         'warna' => 'gray',   'deskripsi' => 'Mahasiswa meninggal dunia',                         'is_aktif_akademik' => false, 'urutan' => 7, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('dim_status_mahasiswas');
    }
};
