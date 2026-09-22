<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dim_kelompok_ukt', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('kelompok')->unsigned()->unique()->comment('1 s.d. 8');
            $table->decimal('nominal_min', 12, 2)->nullable();
            $table->decimal('nominal_max', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dim_kelompok_ukt');
    }
};
