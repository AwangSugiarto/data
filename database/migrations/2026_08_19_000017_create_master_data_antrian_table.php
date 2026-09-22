<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_data_antrian', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['prodi', 'fakultas', 'jalur']);
            $table->string('nilai_mentah')->comment('Teks asli yang terdeteksi dari Excel');
            $table->foreignId('import_batch_id')->nullable()->constrained('import_batches')->nullOnDelete();
            $table->enum('status', ['menunggu', 'dikonfirmasi_baru', 'dijadikan_alias'])->default('menunggu');
            $table->unsignedBigInteger('dipetakan_ke_id')->nullable()->comment('ID entitas tujuan bila dijadikan alias');
            $table->foreignId('ditinjau_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tipe', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_data_antrian');
    }
};
