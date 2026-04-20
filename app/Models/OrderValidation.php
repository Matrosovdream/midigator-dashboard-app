<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasComments;
use App\Models\Concerns\HasWorkflowStages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderValidation extends Model
{
    use BelongsToTenant, HasComments, HasWorkflowStages;

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

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
