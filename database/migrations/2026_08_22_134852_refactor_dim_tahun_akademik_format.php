<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update data existing (e.g. 2024 -> 20241)
        // Use raw SQL because if we alter table first, data might be truncated or unique constraints might fail
        DB::statement("UPDATE dim_tahun_akademik SET tahun = (tahun * 10) + IF(semester = 'Genap', 2, 1) WHERE tahun < 10000");

        Schema::table('dim_tahun_akademik', function (Blueprint $table) {
            // Drop composite unique index
            $table->dropUnique('idx_tahun_semester_unique');
            // Drop semester column
            $table->dropColumn('semester');
        });

        // 2. Modify `tahun` to INT UNSIGNED and add unique constraint
        DB::statement("ALTER TABLE dim_tahun_akademik MODIFY COLUMN tahun INT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE dim_tahun_akademik ADD UNIQUE INDEX dim_tahun_akademik_tahun_unique (tahun)");
    }

    public function down(): void
    {
        Schema::table('dim_tahun_akademik', function (Blueprint $table) {
            $table->enum('semester', ['Ganjil', 'Genap'])->default('Ganjil')->after('tahun');
            $table->dropUnique('dim_tahun_akademik_tahun_unique');
        });

        // Restore data
        DB::statement("UPDATE dim_tahun_akademik SET semester = IF(tahun % 10 = 2, 'Genap', 'Ganjil'), tahun = FLOOR(tahun / 10)");
        
        DB::statement("ALTER TABLE dim_tahun_akademik MODIFY COLUMN tahun SMALLINT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE dim_tahun_akademik ADD UNIQUE INDEX idx_tahun_semester_unique (tahun, semester)");
    }
};
