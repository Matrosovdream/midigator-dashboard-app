<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasComments;
use App\Models\Concerns\HasWorkflowStages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RdrCase extends Model
{
    use BelongsToTenant, HasComments, HasWorkflowStages;

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

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
