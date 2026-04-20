<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'score',
    'notes',
    'assigned_mids',
])]
class ManagerProfile extends Model
{
    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'assigned_mids' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
