<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fact_pendaftaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_akademik_id')->constrained('dim_tahun_akademik');
            $table->foreignId('program_studi_id')->constrained('dim_program_studi');
            $table->foreignId('jalur_seleksi_id')->constrained('dim_jalur_seleksi');
            $table->tinyInteger('nomor_pilihan')->unsigned()->nullable()->comment('Pilihan 1/2/3/4 bila relevan');
            $table->integer('jumlah_daftar')->default(0);
            $table->timestamps();

            $table->index(['tahun_akademik_id', 'program_studi_id']);
            $table->index(['tahun_akademik_id', 'jalur_seleksi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fact_pendaftaran');
    }
};
