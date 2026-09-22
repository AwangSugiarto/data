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
        Schema::table('fact_pendaftar_individu', function (Blueprint $table) {
            $table->string('nisn')->nullable()->after('nomor_tes');
            $table->string('npsn')->nullable()->after('asal_sekolah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fact_pendaftar_individu', function (Blueprint $table) {
            $table->dropColumn(['nisn', 'npsn']);
        });
    }
};
