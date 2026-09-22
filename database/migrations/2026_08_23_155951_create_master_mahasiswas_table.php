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
        Schema::create('master_mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fact_pendaftar_individu_id')->nullable()->constrained('fact_pendaftar_individu')->nullOnDelete();
            $table->string('nim')->unique();
            $table->string('nama');
            $table->foreignId('program_studi_id')->constrained('dim_program_studi');
            $table->foreignId('tahun_masuk_id')->constrained('dim_tahun_akademik');
            $table->enum('status_akademik', ['aktif', 'cuti', 'non_aktif', 'do', 'alumni'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_mahasiswas');
    }
};
