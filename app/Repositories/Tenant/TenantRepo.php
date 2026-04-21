<?php

namespace App\Repositories\Tenant;

use App\Models\Tenant;
use App\Repositories\AbstractRepo;
use Illuminate\Support\Str;

class TenantRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new Tenant();
    }

    public function beforeCreate($data)
    {
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = $this->uniqueSlug(Str::slug($data['name']));
        }
        return $data;
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base !== '' ? $base : 'tenant';
        $i = 2;
        while ($this->model->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
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

    public function getBySlug($slug)
    {
        $item = $this->model->where('slug', $slug)->first();
        return $this->mapItem($item);
    }

    public function getAll($filter = [], $paginate = 20, array $sorting = [])
    {
        $search = $filter['search'] ?? null;
        unset($filter['search']);

        $query = $this->model->with($this->withRelations)->withCount('users');
        $query = $this->applyFilter($query, $filter);

        if (!empty($search)) {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'LIKE', $like)
                    ->orWhere('slug', 'LIKE', $like)
                    ->orWhere('domain', 'LIKE', $like);
            });
        }

        $query = $this->applySorting($query, $sorting);

        return $this->mapItems($query->paginate($paginate));
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
            'midigator_webhook_username' => $item->midigator_webhook_username,
            'is_active' => (bool) $item->is_active,
            'settings' => $item->settings ?? [],
            'users_count' => $item->users_count ?? 0,
            'has_api_secret' => !empty($item->getRawOriginal('midigator_api_secret')),
            'has_webhook_password' => !empty($item->getRawOriginal('midigator_webhook_password')),
            'created_at' => $item->created_at,
            'Model' => $item,
        ];
    }
}
