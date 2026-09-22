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
        Schema::create('dim_tarif_spp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tahun_akademik_id');
            $table->unsignedBigInteger('program_studi_id');
            $table->decimal('nominal', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('tahun_akademik_id')->references('id')->on('dim_tahun_akademik')->onDelete('cascade');
            $table->foreign('program_studi_id')->references('id')->on('dim_program_studi')->onDelete('cascade');

            $table->unique(['tahun_akademik_id', 'program_studi_id'], 'tarif_spp_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dim_tarif_spp');
    }
};
