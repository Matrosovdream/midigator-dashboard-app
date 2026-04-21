<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\AbstractRepo;

class UserRepo extends AbstractRepo
{
    protected $withRelations = ['roles', 'managerProfile'];

    public function getAllWithTenant($filter = [], $paginate = 20, array $sorting = [])
    {
        $search = $filter['search'] ?? null;
        unset($filter['search']);

        $query = $this->model->with(array_merge($this->withRelations, ['tenant']));
        $query = $this->applyFilter($query, $filter);

        if (!empty($search)) {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'LIKE', $like)
                    ->orWhere('email', 'LIKE', $like);
            });
        }

        $query = $this->applySorting($query, $sorting);

        return $this->mapItems($query->paginate($paginate));
    }

    public function __construct()
    {
        $this->model = new User();
    }

    public function getByEmail(string $email)
    {
        $item = $this->model
            ->where('email', $email)
            ->with($this->withRelations)
            ->first();

        return $this->mapItem($item);
    }

    public function getByTenant(int $tenantId, $paginate = 20, array $sorting = [])
    {
        return $this->getAll(['tenant_id' => $tenantId], $paginate, $sorting);
    }

    public function findActiveUserMatchingPin(string $pin): ?User
    {
        return $this->model
            ->where('is_active', true)
            ->whereNotNull('pin')
            ->get()
            ->first(fn (User $user) => \Hash::check($pin, $user->pin));
    }

    public function touchLastLogin(int $id): void
    {
        $this->model->where('id', $id)->update(['last_login_at' => now()]);
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'tenant_id' => $item->tenant_id,
            'tenant' => $item->relationLoaded('tenant') && $item->tenant
                ? ['id' => $item->tenant->id, 'name' => $item->tenant->name, 'slug' => $item->tenant->slug]
                : null,
            'email' => $item->email,
            'name' => $item->name,
            'avatar' => $item->avatar,
            'is_active' => (bool) $item->is_active,
            'is_platform_admin' => (bool) $item->is_platform_admin,
            'last_login_at' => $item->last_login_at,
            'roles' => $item->relationLoaded('roles')
                ? $item->roles->map(fn ($r) => [
                    'id' => $r->id,
                    'name' => $r->name,
                    'slug' => $r->slug,
                ])->values()->toArray()
                : [],
            'manager_profile' => $item->relationLoaded('managerProfile') && $item->managerProfile
                ? [
                    'id' => $item->managerProfile->id,
                    'score' => $item->managerProfile->score,
                ]
                : null,
            'created_at' => $item->created_at,
            'Model' => $item,
        ];
    }
}
