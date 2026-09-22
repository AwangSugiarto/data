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
        Schema::create('pmb_daya_tampung', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_akademik_id')->constrained('dim_tahun_akademik')->cascadeOnDelete();
            $table->foreignId('program_studi_id')->constrained('dim_program_studi')->cascadeOnDelete();
            $table->foreignId('jalur_seleksi_id')->constrained('dim_jalur_seleksi')->cascadeOnDelete();
            $table->integer('daya_tampung')->default(0);
            $table->string('sk_daya_tampung')->nullable();
            $table->timestamps();
            
            // Untuk memastikan tidak ada duplikat kombinasi tahun, prodi, dan jalur
            $table->unique(['tahun_akademik_id', 'program_studi_id', 'jalur_seleksi_id'], 'uk_pmb_daya_tampung');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pmb_daya_tampung');
    }
};
