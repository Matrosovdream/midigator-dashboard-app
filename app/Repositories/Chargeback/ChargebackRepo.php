<?php

namespace App\Repositories\Chargeback;

use App\Models\Chargeback;
use App\Repositories\AbstractRepo;

class ChargebackRepo extends AbstractRepo
{
    protected $withRelations = ['assignee'];

    public function __construct()
    {
        $this->model = new Chargeback();
    }

    public function getByGuid(string $guid)
    {
        $item = $this->model->where('chargeback_guid', $guid)->with($this->withRelations)->first();
        return $this->mapItem($item);
    }

    public function getForTenant(int $tenantId, array $filter = [], $paginate = 25, array $sorting = [])
    {
        return $this->getAll(array_merge(['tenant_id' => $tenantId], $filter), $paginate, $sorting);
    }

    public function changeStage(int $id, string $stage): ?array
    {
        return $this->update($id, ['stage' => $stage]);
    }

    public function assign(int $id, ?int $userId): ?array
    {
        return $this->update($id, ['assigned_to' => $userId]);
    }

    public function setHidden(int $id, bool $hidden): ?array
    {
        return $this->update($id, ['is_hidden' => $hidden]);
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'tenant_id' => $item->tenant_id,
            'chargeback_guid' => $item->chargeback_guid,
            'case_number' => $item->case_number,
            'mid' => $item->mid,
            'amount' => (int) $item->amount,
            'currency' => $item->currency,
            'card_brand' => $item->card_brand,
            'reason_code' => $item->reason_code,
            'chargeback_date' => $item->chargeback_date,
            'due_date' => $item->due_date,
            'result' => $item->result,
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
