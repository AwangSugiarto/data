<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dim_program_studi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fakultas_id')->constrained('dim_fakultas');
            $table->string('nama_prodi');
            $table->string('kode_prodi')->nullable()->comment('Kode dari sumber, bukan PK');
            $table->enum('jenjang', ['D3', 'S1', 'S2', 'S3'])->default('S1');
            $table->boolean('status_terkini')->default(true);
            $table->date('berlaku_dari')->nullable();
            $table->date('berlaku_sampai')->nullable();
            $table->timestamps();

            $table->index(['fakultas_id', 'jenjang']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dim_program_studi');
    }
};
