<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'tenant_id',
    'evidenceable_type',
    'evidenceable_id',
    'uploaded_by',
    'name',
    'path',
    'mime_type',
    'size_bytes',
])]
class EvidenceFile extends Model
{
    use BelongsToTenant;

    protected $table = 'evidence_files';

    public function evidenceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
