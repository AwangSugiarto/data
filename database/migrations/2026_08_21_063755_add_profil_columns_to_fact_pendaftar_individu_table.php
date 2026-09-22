<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fact_pendaftar_individu', function (Blueprint $table) {
            $table->string('jenis_kelamin')->nullable()->after('status');
            $table->string('asal_sekolah')->nullable()->after('jenis_kelamin');
            $table->string('jenis_sekolah')->nullable()->after('asal_sekolah');
        });
    }

    public function down(): void
    {
        Schema::table('fact_pendaftar_individu', function (Blueprint $table) {
            $table->dropColumn(['jenis_kelamin', 'asal_sekolah', 'jenis_sekolah']);
        });
    }
};
