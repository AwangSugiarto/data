<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterDataAntrian extends Model
{
    protected $table = 'master_data_antrian';
    protected $fillable = [
        'tipe', 'nilai_mentah', 'import_batch_id', 'status',
        'dipetakan_ke_id', 'ditinjau_oleh',
    ];

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditinjau_oleh');
    }
}
