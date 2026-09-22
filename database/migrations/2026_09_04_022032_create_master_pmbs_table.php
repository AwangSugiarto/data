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
        Schema::create('master_pmbs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_akademik_id')->constrained('dim_tahun_akademik')->cascadeOnDelete();
            $table->foreignId('jalur_seleksi_id')->constrained('dim_jalur_seleksi')->cascadeOnDelete();
            $table->foreignId('fakultas_id')->constrained('dim_fakultas')->cascadeOnDelete();
            $table->foreignId('program_studi_id')->constrained('dim_program_studi')->cascadeOnDelete();
            $table->integer('daya_tampung')->default(0);
            $table->integer('pendaftar')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_pmbs');
    }
};
