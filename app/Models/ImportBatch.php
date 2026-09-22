<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    protected $table = 'import_batches';
    protected $fillable = ['nama_file_asal', 'diupload_oleh', 'status', 'catatan'];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diupload_oleh');
    }

    public function stagingRows(): HasMany
    {
        return $this->hasMany(ImportStagingRow::class, 'import_batch_id');
    }

    public function masterDataAntrian(): HasMany
    {
        return $this->hasMany(MasterDataAntrian::class, 'import_batch_id');
    }
}
