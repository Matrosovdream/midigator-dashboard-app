<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasComments;
use App\Models\Concerns\HasWorkflowStages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Chargeback extends Model
{
    use BelongsToTenant, HasComments, HasWorkflowStages;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'chargeback_date' => 'date',
            'date_received' => 'date',
            'due_date' => 'date',
            'processor_transaction_date' => 'date',
            'dnf_timestamp' => 'datetime',
            'is_hidden' => 'boolean',
            'amount' => 'integer',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function emailLogs(): MorphMany
    {
        return $this->morphMany(EmailLog::class, 'emailable');
    }
}
