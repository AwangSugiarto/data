<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dim_tahun_akademik', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun')->unique()->comment('Contoh: 2013, 2014, dst');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dim_tahun_akademik');
    }
};
