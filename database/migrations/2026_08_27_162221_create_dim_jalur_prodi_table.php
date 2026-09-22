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
        Schema::create('dim_jalur_prodi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jalur_seleksi_id');
            $table->unsignedBigInteger('program_studi_id');
            $table->timestamps();

            $table->foreign('jalur_seleksi_id')->references('id')->on('dim_jalur_seleksi')->onDelete('cascade');
            $table->foreign('program_studi_id')->references('id')->on('dim_program_studi')->onDelete('cascade');

            $table->unique(['jalur_seleksi_id', 'program_studi_id'], 'jalur_prodi_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dim_jalur_prodi');
    }
};
