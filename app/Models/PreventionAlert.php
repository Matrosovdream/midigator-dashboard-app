<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PreventionAlert extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'prevention_timestamp' => 'datetime',
            'transaction_timestamp' => 'datetime',
            'resolution_submitted_at' => 'datetime',
            'is_hidden' => 'boolean',
            'amount' => 'integer',
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

    public function emailLogs(): MorphMany
    {
        return $this->morphMany(EmailLog::class, 'emailable');
    }
}
