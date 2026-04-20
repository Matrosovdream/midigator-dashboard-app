<?php

namespace App\Repositories\Rdr;

use App\Models\RdrCase;
use App\Repositories\AbstractRepo;

class RdrCaseRepo extends AbstractRepo
{
    protected $withRelations = ['assignee'];

    public function __construct()
    {
        $this->model = new RdrCase();
    }

    public function getByGuid(string $guid)
    {
        $item = $this->model->where('rdr_guid', $guid)->with($this->withRelations)->first();
        return $this->mapItem($item);
    }

    public function getForTenant(int $tenantId, array $filter = [], $paginate = 25, array $sorting = [])
    {
        return $this->getAll(array_merge(['tenant_id' => $tenantId], $filter), $paginate, $sorting);
    }

    public function recordResolution(int $id, string $resolution): ?array
    {
        return $this->update($id, ['rdr_resolution' => $resolution]);
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'tenant_id' => $item->tenant_id,
            'rdr_guid' => $item->rdr_guid,
            'rdr_case_number' => $item->rdr_case_number,
            'rdr_date' => $item->rdr_date,
            'rdr_resolution' => $item->rdr_resolution,
            'amount' => (int) $item->amount,
            'currency' => $item->currency,
            'stage' => $item->stage,
            'is_hidden' => (bool) $item->is_hidden,
            'assigned_to' => $item->assigned_to,
            'created_at' => $item->created_at,
            'Model' => $item,
        ];
    }
}
