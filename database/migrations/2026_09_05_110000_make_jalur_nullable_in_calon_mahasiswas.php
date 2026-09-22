<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calon_mahasiswas', function (Blueprint $table) {
            // Ubah jalur_seleksi_id menjadi nullable agar bisa import tanpa spesifikasi jalur
            $table->foreignId('jalur_seleksi_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('calon_mahasiswas', function (Blueprint $table) {
            $table->foreignId('jalur_seleksi_id')->nullable(false)->change();
        });
    }
};
