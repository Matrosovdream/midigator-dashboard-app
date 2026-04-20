<?php

namespace App\Repositories\Tenant;

use App\Models\Tenant;
use App\Repositories\AbstractRepo;

class TenantRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new Tenant();
    }

    public function getActive($paginate = 50, array $sorting = [])
    {
        return $this->getAll(['is_active' => true], $paginate, $sorting);
    }

    public function getByDomain(string $domain)
    {
        $item = $this->model->where('domain', $domain)->first();
        return $this->mapItem($item);
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'name' => $item->name,
            'slug' => $item->slug,
            'domain' => $item->domain,
            'midigator_sandbox_mode' => (bool) $item->midigator_sandbox_mode,
            'is_active' => (bool) $item->is_active,
            'settings' => $item->settings ?? [],
            'created_at' => $item->created_at,
            'Model' => $item,
        ];
    }
}
