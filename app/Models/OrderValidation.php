<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class OrderValidation extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'order_validation_timestamp' => 'datetime',
            'transaction_timestamp' => 'datetime',
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
