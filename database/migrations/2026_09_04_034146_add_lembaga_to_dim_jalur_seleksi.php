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
            $table->string('lembaga')->nullable()->after('nama_jalur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dim_jalur_seleksi', function (Blueprint $table) {
            $table->dropColumn('lembaga');
        });
    }
};
