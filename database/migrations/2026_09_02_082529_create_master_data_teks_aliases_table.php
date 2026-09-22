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
        Schema::create('master_data_teks_aliases', function (Blueprint $table) {
            $table->id();
            $table->string('tipe', 50)->comment('Contoh: wilayah_prov, wilayah_kab, wilayah_kec');
            $table->string('nilai_mentah');
            $table->string('dipetakan_ke_teks');
            $table->timestamps();
            
            // Indeks agar lookup cepat
            $table->index(['tipe', 'nilai_mentah']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_data_teks_aliases');
    }
};
