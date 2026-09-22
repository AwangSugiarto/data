<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fact_pendaftar_individu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_akademik_id')->constrained('dim_tahun_akademik');
            $table->foreignId('program_studi_id')->constrained('dim_program_studi');
            $table->foreignId('jalur_seleksi_id')->constrained('dim_jalur_seleksi');
            $table->foreignId('wilayah_id')->nullable()->constrained('dim_wilayah')->nullOnDelete();
            $table->foreignId('kelompok_ukt_id')->nullable()->constrained('dim_kelompok_ukt')->nullOnDelete();
            $table->string('nomor_tes')->nullable()->index();
            $table->string('nama')->nullable();
            $table->enum('status', ['daftar', 'lulus', 'registrasi', 'tidak_registrasi'])->default('daftar');
            $table->timestamps();

            $table->index(['tahun_akademik_id', 'program_studi_id', 'status'], 'idx_individu_tahun_prodi_status');
            $table->index(['tahun_akademik_id', 'jalur_seleksi_id', 'status'], 'idx_individu_tahun_jalur_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fact_pendaftar_individu');
    }
};
