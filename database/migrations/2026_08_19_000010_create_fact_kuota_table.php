<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fact_kuota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_akademik_id')->constrained('dim_tahun_akademik');
            $table->foreignId('program_studi_id')->constrained('dim_program_studi');
            $table->foreignId('jalur_seleksi_id')->constrained('dim_jalur_seleksi');
            $table->integer('kuota_awal')->default(0);
            $table->integer('kuota_perubahan')->nullable();
            $table->integer('kuota_akhir')->default(0);
            $table->timestamps();

            $table->unique(['tahun_akademik_id', 'program_studi_id', 'jalur_seleksi_id'], 'uq_kuota_tahun_prodi_jalur');
            $table->index(['tahun_akademik_id', 'program_studi_id'], 'idx_kuota_tahun_prodi');
            $table->index(['tahun_akademik_id', 'jalur_seleksi_id'], 'idx_kuota_tahun_jalur');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fact_kuota');
    }
};
