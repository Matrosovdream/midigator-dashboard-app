<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Order extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'order_date' => 'datetime',
            'order_amount' => 'integer',
            'refunded' => 'boolean',
            'refunded_amount' => 'integer',
            'subscription_cycle' => 'integer',
            'billing_address' => 'array',
            'sub_marketing_sources' => 'array',
            'items' => 'array',
            'evidence' => 'array',
            'is_hidden' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function emailLogs(): MorphMany
    {
        return $this->morphMany(EmailLog::class, 'emailable');
    }
}
