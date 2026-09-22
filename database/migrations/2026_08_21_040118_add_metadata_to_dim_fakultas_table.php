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
        Schema::table('dim_fakultas', function (Blueprint $table) {
            $table->string('pejabat')->nullable()->after('berlaku_sampai');
            $table->string('nip_pejabat')->nullable()->after('pejabat');
            $table->string('telepon')->nullable()->after('nip_pejabat');
            $table->string('email')->nullable()->after('telepon');
            $table->text('keterangan')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dim_fakultas', function (Blueprint $table) {
            $table->dropColumn(['pejabat', 'nip_pejabat', 'telepon', 'email', 'keterangan']);
        });
    }
};
