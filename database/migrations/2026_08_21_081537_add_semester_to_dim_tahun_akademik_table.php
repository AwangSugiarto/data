<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dim_tahun_akademik', function (Blueprint $table) {
            $table->dropUnique('dim_tahun_akademik_tahun_unique');
            $table->enum('semester', ['Ganjil', 'Genap'])->default('Ganjil')->after('tahun');
            $table->unique(['tahun', 'semester'], 'idx_tahun_semester_unique');
        });
    }

    public function down(): void
    {
        Schema::table('dim_tahun_akademik', function (Blueprint $table) {
            $table->dropUnique('idx_tahun_semester_unique');
            $table->dropColumn('semester');
            $table->unique('tahun', 'dim_tahun_akademik_tahun_unique');
        });
    }
};
