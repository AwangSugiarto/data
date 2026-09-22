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
        Schema::table('dim_jalur_seleksi', function (Blueprint $table) {
            $table->foreignId('kelompok_jalur_id')->nullable()->constrained('dim_kelompok_jalurs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dim_jalur_seleksi', function (Blueprint $table) {
            $table->dropForeign(['kelompok_jalur_id']);
            $table->dropColumn('kelompok_jalur_id');
        });
    }
};
