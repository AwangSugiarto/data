<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dim_jalur_seleksi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jalur');
            $table->enum('kelompok_jalur', ['nasional', 'mandiri', 'beasiswa', 'afirmasi', 'lainnya'])->default('lainnya');
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dim_jalur_seleksi');
    }
};
