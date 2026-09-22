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
        Schema::create('mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calon_mahasiswa_id')->unique()->constrained('calon_mahasiswas')->cascadeOnDelete();
            $table->string('nim')->unique();
            $table->foreignId('tahun_masuk_id')->constrained('dim_tahun_akademik')->cascadeOnDelete();
            $table->enum('status_akademik', ['Aktif', 'Stop Out', 'Drop Out', 'Meninggal', 'Mengundurkan Diri', 'Lulus', 'Alumni', 'Lainnya'])->default('Aktif');
            $table->date('tanggal_lulus')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswas');
    }
};
