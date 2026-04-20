<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RdrCase extends Model
{
    protected $table = 'rdr_cases';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'rdr_date' => 'date',
            'transaction_date' => 'date',
            'amount' => 'integer',
            'is_hidden' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function stageTransitions(): MorphMany
    {
        return $this->morphMany(StageTransition::class, 'trackable');
    }
}
