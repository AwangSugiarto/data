<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jalur_alias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jalur_seleksi_id')->constrained('dim_jalur_seleksi')->cascadeOnDelete();
            $table->string('nama_alias');
            $table->timestamps();

            $table->unique(['jalur_seleksi_id', 'nama_alias']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jalur_alias');
    }
};
