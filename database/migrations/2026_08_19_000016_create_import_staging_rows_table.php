<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_staging_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id')->constrained('import_batches')->cascadeOnDelete();
            $table->json('baris_asal')->comment('Salinan mentah 1 baris/blok dari Excel');
            $table->enum('status', ['pending', 'mapped', 'error'])->default('pending');
            $table->text('pesan_error')->nullable();
            $table->timestamps();

            $table->index(['import_batch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_staging_rows');
    }
};
