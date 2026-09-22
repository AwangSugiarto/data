<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fakultas_alias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fakultas_id')->constrained('dim_fakultas')->cascadeOnDelete();
            $table->string('nama_alias')->comment('Variasi penulisan nama fakultas dari sumber Excel');
            $table->timestamps();

            $table->unique(['fakultas_id', 'nama_alias']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fakultas_alias');
    }
};
