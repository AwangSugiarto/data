<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE fact_pendaftar_individu MODIFY COLUMN status ENUM('daftar', 'lulus', 'registrasi', 'tidak_registrasi', 'alumni') DEFAULT 'daftar'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE fact_pendaftar_individu MODIFY COLUMN status ENUM('daftar', 'lulus', 'registrasi', 'tidak_registrasi') DEFAULT 'daftar'");
    }
};
