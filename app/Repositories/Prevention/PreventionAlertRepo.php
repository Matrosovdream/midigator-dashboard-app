<?php

namespace App\Repositories\Prevention;

use App\Models\PreventionAlert;
use App\Repositories\AbstractRepo;

class PreventionAlertRepo extends AbstractRepo
{
    protected $withRelations = ['assignee'];

    public function __construct()
    {
        $this->model = new PreventionAlert();
    }

    public function getByGuid(string $guid)
    {
        $item = $this->model->where('prevention_guid', $guid)->with($this->withRelations)->first();
        return $this->mapItem($item);
    }

    public function getForTenant(int $tenantId, array $filter = [], $paginate = 25, array $sorting = [])
    {
        return $this->getAll(array_merge(['tenant_id' => $tenantId], $filter), $paginate, $sorting);
    }

    public function recordResolution(int $id, string $resolutionType): ?array
    {
        return $this->update($id, [
            'resolution_type' => $resolutionType,
            'resolution_submitted_at' => now(),
        ]);
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'tenant_id' => $item->tenant_id,
            'prevention_guid' => $item->prevention_guid,
            'prevention_type' => $item->prevention_type,
            'mid' => $item->mid,
            'amount' => (int) $item->amount,
            'currency' => $item->currency,
            'resolution_type' => $item->resolution_type,
            'resolution_submitted_at' => $item->resolution_submitted_at,
            'stage' => $item->stage,
            'is_hidden' => (bool) $item->is_hidden,
            'assigned_to' => $item->assigned_to,
            'assignee' => $item->relationLoaded('assignee') && $item->assignee
                ? ['id' => $item->assignee->id, 'name' => $item->assignee->name]
                : null,
            'created_at' => $item->created_at,
            'Model' => $item,
        ];
    }
}
