<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE import_staging_rows MODIFY COLUMN status ENUM('pending', 'mapped', 'processed', 'error') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE import_staging_rows MODIFY COLUMN status ENUM('pending', 'mapped', 'error') DEFAULT 'pending'");
    }
};
