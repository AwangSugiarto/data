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
        Schema::table('sekolah', function (Blueprint $table) {
            $table->string('akreditasi', 50)->nullable();
            $table->string('kode_pos', 20)->nullable();
            $table->string('telepon', 50)->nullable();
            $table->string('fax', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('web', 150)->nullable();
            $table->integer('jumlah_siswa')->nullable();
            $table->string('kurikulum', 100)->nullable();
            $table->string('nama_kepsek', 100)->nullable();
            $table->string('hp_kepsek', 50)->nullable();
            $table->string('email_kepsek', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            $table->dropColumn([
                'akreditasi',
                'kode_pos',
                'telepon',
                'fax',
                'email',
                'web',
                'jumlah_siswa',
                'kurikulum',
                'nama_kepsek',
                'hp_kepsek',
                'email_kepsek'
            ]);
        });
    }
};
