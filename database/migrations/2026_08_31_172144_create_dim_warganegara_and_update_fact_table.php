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
        Schema::create('dim_warganegara', function (Blueprint $table) {
            $table->id();
            $table->string('nama_negara');
            $table->string('kode_negara')->nullable();
            $table->timestamps();
        });

        Schema::table('fact_pendaftar_individu', function (Blueprint $table) {
            $table->unsignedBigInteger('warganegara_id')->nullable()->after('jenis_sekolah');
            
            $table->foreign('warganegara_id')->references('id')->on('dim_warganegara')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fact_pendaftar_individu', function (Blueprint $table) {
            $table->dropForeign(['warganegara_id']);
            $table->dropColumn('warganegara_id');
        });

        Schema::dropIfExists('dim_warganegara');
    }
};
