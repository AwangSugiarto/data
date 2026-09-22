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
        Schema::table('master_mahasiswas', function (Blueprint $table) {
            $table->date('tanggal_lulus')->nullable();
            $table->foreignId('tahun_akademik_lulus_id')->nullable()->constrained('dim_tahun_akademik')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_mahasiswas', function (Blueprint $table) {
            $table->dropForeign(['tahun_akademik_lulus_id']);
            $table->dropColumn(['tanggal_lulus', 'tahun_akademik_lulus_id']);
        });
    }
};
