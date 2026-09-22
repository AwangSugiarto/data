<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\DimTahunAkademik;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $activeYear = DimTahunAkademik::orderBy('id', 'desc')->first();
        $defaultYearId = $activeYear ? $activeYear->id : null;
        if (!$defaultYearId) {
            $firstYear = DimTahunAkademik::first();
            $defaultYearId = $firstYear ? $firstYear->id : null;
        }

        Schema::table('dim_kelompok_ukt', function (Blueprint $table) {
            $table->string('kelompok', 50)->change();
            $table->unsignedBigInteger('tahun_akademik_id')->nullable()->after('id');
        });

        // Set default year for existing data
        if ($defaultYearId) {
            DB::table('dim_kelompok_ukt')->update(['tahun_akademik_id' => $defaultYearId]);
        }

        // Now make it not nullable and add foreign key
        Schema::table('dim_kelompok_ukt', function (Blueprint $table) use ($defaultYearId) {
            if ($defaultYearId) {
                $table->unsignedBigInteger('tahun_akademik_id')->nullable(false)->change();
            }
            $table->foreign('tahun_akademik_id')->references('id')->on('dim_tahun_akademik')->onDelete('cascade');
            // Adding unique constraint might fail if there are duplicates, but currently there shouldn't be
            // Let's add it anyway
            $table->unique(['tahun_akademik_id', 'kelompok'], 'ukt_tahun_kelompok_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dim_kelompok_ukt', function (Blueprint $table) {
            $table->dropForeign(['tahun_akademik_id']);
            $table->dropUnique('ukt_tahun_kelompok_unique');
            $table->dropColumn('tahun_akademik_id');
            // Can't easily revert string to int if there's text data, so we leave it as string or force integer
            // $table->integer('kelompok')->change();
        });
    }
};
