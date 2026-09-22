<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prodi_alias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_studi_id')->constrained('dim_program_studi')->cascadeOnDelete();
            $table->string('nama_alias');
            $table->timestamps();

            $table->unique(['program_studi_id', 'nama_alias']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prodi_alias');
    }
};
