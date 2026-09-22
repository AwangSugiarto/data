<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->foreignId('kelompok_ukt_id')
                  ->nullable()
                  ->after('status_akademik')
                  ->constrained('dim_kelompok_ukt')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropForeign(['kelompok_ukt_id']);
            $table->dropColumn('kelompok_ukt_id');
        });
    }
};
