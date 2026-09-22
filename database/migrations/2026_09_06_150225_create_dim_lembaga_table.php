<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dim_lembaga', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lembaga');               // Nama lengkap lembaga
            $table->string('singkatan')->nullable();      // Singkatan / akronim
            $table->string('keterangan')->nullable();     // Keterangan tambahan
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dim_lembaga');
    }
};
