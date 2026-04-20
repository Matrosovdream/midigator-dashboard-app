<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Cache;

trait HasRights
{
    public function isPlatformAdmin(): bool
    {
        return (bool) $this->is_platform_admin;
    }

    public function allRights(): array
    {
        if ($this->isPlatformAdmin()) {
            return ['*'];
        }

        return Cache::rememberForever($this->rightsCacheKey(), function () {
            return $this->roles()
                ->with('rights:id,slug')
                ->get()
                ->flatMap(fn ($role) => $role->rights->pluck('slug'))
                ->unique()
                ->values()
                ->all();
        });
    }

    public function hasRight(string $slug): bool
    {
        if ($this->isPlatformAdmin()) {
            return true;
        }

        return in_array($slug, $this->allRights(), true);
    }

    public function hasAnyRight(array $slugs): bool
    {
        if ($this->isPlatformAdmin()) {
            return true;
        }

        $rights = $this->allRights();
        foreach ($slugs as $slug) {
            if (in_array($slug, $rights, true)) {
                return true;
            }
        }
        return false;
    }

    public function hasAllRights(array $slugs): bool
    {
        if ($this->isPlatformAdmin()) {
            return true;
        }

        $rights = $this->allRights();
        foreach ($slugs as $slug) {
            if (!in_array($slug, $rights, true)) {
                return false;
            }
        }
        return true;
    }

    public function flushRightsCache(): void
    {
        Cache::forget($this->rightsCacheKey());
    }

    protected function rightsCacheKey(): string
    {
        return "user:{$this->getKey()}:rights";
    }
}
