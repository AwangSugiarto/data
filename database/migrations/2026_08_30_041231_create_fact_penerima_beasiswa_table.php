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
        Schema::create('fact_penerima_beasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_akademik_id')->constrained('dim_tahun_akademik')->cascadeOnDelete();
            $table->foreignId('dim_beasiswa_id')->constrained('dim_beasiswa')->cascadeOnDelete();
            $table->foreignId('program_studi_id')->constrained('dim_program_studi')->cascadeOnDelete();
            $table->string('nim')->index();
            $table->string('nama_mahasiswa')->nullable();
            $table->timestamps();
            
            $table->unique(['tahun_akademik_id', 'nim', 'dim_beasiswa_id'], 'penerima_unik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fact_penerima_beasiswa');
    }
};
