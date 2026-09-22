<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportStagingRow extends Model
{
    protected $table = 'import_staging_rows';
    protected $fillable = ['import_batch_id', 'baris_asal', 'status', 'pesan_error'];

    protected $casts = ['baris_asal' => 'array'];

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }
}
