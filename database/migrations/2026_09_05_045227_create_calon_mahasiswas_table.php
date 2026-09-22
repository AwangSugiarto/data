<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calon_mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pendaftaran')->unique();
            $table->string('nama');
            $table->foreignId('tahun_akademik_id')->constrained('dim_tahun_akademik')->cascadeOnDelete();
            $table->foreignId('program_studi_id')->constrained('dim_program_studi')->cascadeOnDelete();
            $table->foreignId('jalur_seleksi_id')->constrained('dim_jalur_seleksi')->cascadeOnDelete();
            $table->foreignId('wilayah_id')->nullable()->constrained('dim_wilayah')->nullOnDelete();
            $table->foreignId('kelompok_ukt_id')->nullable()->constrained('dim_kelompok_ukt')->nullOnDelete();
            $table->foreignId('warganegara_id')->nullable()->constrained('dim_warganegara')->nullOnDelete();
            $table->string('jenis_kelamin')->nullable();
            $table->string('asal_sekolah')->nullable();
            $table->string('jenis_sekolah')->nullable();
            $table->string('nisn')->nullable();
            $table->string('npsn')->nullable();
            $table->boolean('is_lulus')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calon_mahasiswas');
    }
};
