<?php

namespace App\Repositories\User;

use App\Models\Role;
use App\Repositories\AbstractRepo;

class RoleRepo extends AbstractRepo
{
    protected $withRelations = ['rights'];

    public function __construct()
    {
        $this->model = new Role();
    }

    public function getByTenant(?int $tenantId, $paginate = 50, array $sorting = [])
    {
        return $this->getAll(['tenant_id' => $tenantId], $paginate, $sorting);
    }

    public function getBySlugForTenant(string $slug, ?int $tenantId)
    {
        $item = $this->model
            ->where('slug', $slug)
            ->where('tenant_id', $tenantId)
            ->with($this->withRelations)
            ->first();

        return $this->mapItem($item);
    }

    public function syncRights(int $roleId, array $rightIds): void
    {
        $role = $this->model->find($roleId);
        if (!$role) {
            return;
        }

        $role->rights()->sync($rightIds);
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'tenant_id' => $item->tenant_id,
            'name' => $item->name,
            'slug' => $item->slug,
            'description' => $item->description,
            'is_system' => (bool) $item->is_system,
            'rights' => $item->relationLoaded('rights')
                ? $item->rights->map(fn ($r) => [
                    'id' => $r->id,
                    'slug' => $r->slug,
                    'name' => $r->name,
                    'group' => $r->group,
                ])->values()->toArray()
                : [],
            'created_at' => $item->created_at,
            'Model' => $item,
        ];
    }
}
