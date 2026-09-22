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
        Schema::table('dim_program_studi', function (Blueprint $table) {
            $table->string('kode_dikti')->nullable()->after('kode_prodi');
            $table->string('nama_jenjang_pendidikan')->nullable()->after('jenjang');
            $table->string('gelar')->nullable()->after('nama_jenjang_pendidikan');
            $table->string('akreditasi')->nullable()->after('gelar');
            $table->string('no_sk_dikti')->nullable()->after('akreditasi');
            $table->date('tgl_sk_dikti')->nullable()->after('no_sk_dikti');
            $table->date('tgl_akhir_sk_dikti')->nullable()->after('tgl_sk_dikti');
            $table->string('no_sk_ban')->nullable()->after('tgl_akhir_sk_dikti');
            $table->date('tgl_sk_ban')->nullable()->after('no_sk_ban');
            $table->date('tgl_akhir_sk_ban')->nullable()->after('tgl_sk_ban');
            $table->date('tanggal_pendirian')->nullable()->after('tgl_akhir_sk_ban');
            $table->string('telepon')->nullable()->after('tanggal_pendirian');
            $table->string('email')->nullable()->after('telepon');
            $table->string('pejabat')->nullable()->after('email');
            $table->string('jabatan')->nullable()->after('pejabat');
            $table->string('nama_sekprodi')->nullable()->after('jabatan');
            $table->string('nip_sekprodi')->nullable()->after('nama_sekprodi');
            $table->text('keterangan')->nullable()->after('nip_sekprodi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dim_program_studi', function (Blueprint $table) {
            $table->dropColumn([
                'kode_dikti', 'nama_jenjang_pendidikan', 'gelar', 'akreditasi',
                'no_sk_dikti', 'tgl_sk_dikti', 'tgl_akhir_sk_dikti',
                'no_sk_ban', 'tgl_sk_ban', 'tgl_akhir_sk_ban',
                'tanggal_pendirian', 'telepon', 'email', 'pejabat', 'jabatan',
                'nama_sekprodi', 'nip_sekprodi', 'keterangan'
            ]);
        });
    }
};
