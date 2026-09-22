<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dim_fakultas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_fakultas');
            $table->string('kode_fakultas')->nullable();
            $table->boolean('status_terkini')->default(true);
            $table->date('berlaku_dari')->nullable();
            $table->date('berlaku_sampai')->nullable();
            $table->foreignId('fakultas_induk_id')->nullable()->constrained('dim_fakultas')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dim_fakultas');
    }
};
