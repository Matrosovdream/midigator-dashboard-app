<?php

namespace App\Services\Platform;

use App\Repositories\Tenant\SiteSettingRepo;

class SettingsService
{
    public function __construct(private SiteSettingRepo $siteSettingRepo) {}

    public function list(): array
    {
        return ['groups' => $this->siteSettingRepo->getPlatformGrouped()];
    }

    public function bulkUpsert(array $settings): array
    {
        foreach ($settings as $s) {
            if (empty($s['key'])) continue;

            $this->siteSettingRepo->setValue(
                null,
                $s['key'],
                $s['value'] ?? null,
                $s['type'] ?? 'string',
                $s['group'] ?? 'general',
            );
        }

        return $this->list();
    }

    public function delete(string $key): bool
    {
        $item = $this->siteSettingRepo->getByKey(null, $key);
        if (!$item) {
            return false;
        }
        return $this->siteSettingRepo->delete($item['id']);
    }
}
