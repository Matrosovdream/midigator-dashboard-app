<?php

namespace App\Repositories\Tenant;

use App\Models\SiteSetting;
use App\Repositories\AbstractRepo;

class SiteSettingRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new SiteSetting();
    }

    public function getByKey(?int $tenantId, string $key)
    {
        $item = $this->model
            ->where('tenant_id', $tenantId)
            ->where('key', $key)
            ->first();

        return $this->mapItem($item);
    }

    public function getByGroup(?int $tenantId, string $group)
    {
        return $this->getAll(
            ['tenant_id' => $tenantId, 'group' => $group],
            200,
            ['key' => 'asc'],
        );
    }

    public function getPlatformGrouped(): array
    {
        $items = $this->model
            ->whereNull('tenant_id')
            ->orderBy('group', 'asc')
            ->orderBy('key', 'asc')
            ->get()
            ->map(fn ($i) => $this->mapItem($i))
            ->all();

        $groups = [];
        foreach ($items as $item) {
            $g = $item['group'] ?? 'general';
            if (!isset($groups[$g])) {
                $groups[$g] = ['name' => $g, 'settings' => []];
            }
            unset($item['Model']);
            $groups[$g]['settings'][] = $item;
        }
        return array_values($groups);
    }

    public function setValue(?int $tenantId, string $key, mixed $value, string $type = 'string', string $group = 'general'): array
    {
        $stored = is_array($value) || is_object($value) ? json_encode($value) : (string) $value;

        $item = $this->model->updateOrCreate(
            ['tenant_id' => $tenantId, 'key' => $key],
            ['value' => $stored, 'type' => $type, 'group' => $group],
        );

        return $this->mapItem($item->fresh());
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'tenant_id' => $item->tenant_id,
            'key' => $item->key,
            'value' => $this->castValue($item),
            'type' => $item->type,
            'group' => $item->group,
            'description' => $item->description,
            'Model' => $item,
        ];
    }

    private function castValue(SiteSetting $item): mixed
    {
        $raw = $item->value;
        if ($raw === null) {
            return null;
        }

        return match ($item->type) {
            'integer' => (int) $raw,
            'boolean' => filter_var($raw, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($raw, true),
            default => $raw,
        };
    }
}
