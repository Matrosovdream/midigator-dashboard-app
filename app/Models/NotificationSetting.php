<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'channel',
    'chargeback_new',
    'chargeback_result',
    'prevention_new',
    'daily_digest',
    'weekly_report',
])]
class NotificationSetting extends Model
{
    protected function casts(): array
    {
        return [
            'chargeback_new' => 'boolean',
            'chargeback_result' => 'boolean',
            'prevention_new' => 'boolean',
            'daily_digest' => 'boolean',
            'weekly_report' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
