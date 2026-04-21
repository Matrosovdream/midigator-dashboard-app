<?php

namespace App\Services\Platform;

use App\Repositories\Email\EmailTemplateRepo;
use RuntimeException;

class EmailTemplateService
{
    public function __construct(private EmailTemplateRepo $templateRepo) {}

    public function listGlobal(int $perPage = 50): array
    {
        $result = $this->templateRepo->getGlobal([], $perPage);

        // Decorate each with override count
        $items = ($result['items'] ?? collect())->map(function ($item) {
            $item['tenant_override_count'] = $this->templateRepo->countTenantOverrides($item['name']);
            unset($item['Model']);
            return $item;
        });

        return [
            'items' => $items,
            'Model' => $result['Model'] ?? null,
        ];
    }

    public function show(int $id): ?array
    {
        $item = $this->templateRepo->getByID($id);
        if (!$item || $item['Model']->tenant_id !== null) {
            return null;
        }
        $item['tenant_override_count'] = $this->templateRepo->countTenantOverrides($item['name']);
        unset($item['Model']);
        return $item;
    }

    public function create(array $data): array
    {
        if ($this->templateRepo->getGlobalByName($data['name'])) {
            throw new RuntimeException("Global template '{$data['name']}' already exists.");
        }
        $data['tenant_id'] = null;
        $item = $this->templateRepo->create($data);
        unset($item['Model']);
        return $item;
    }

    public function update(int $id, array $data): ?array
    {
        $existing = $this->templateRepo->getByID($id);
        if (!$existing || $existing['Model']->tenant_id !== null) {
            return null;
        }
        unset($data['tenant_id']);
        $item = $this->templateRepo->update($id, $data);
        if ($item) unset($item['Model']);
        return $item;
    }

    public function delete(int $id): bool
    {
        $existing = $this->templateRepo->getByID($id);
        if (!$existing || $existing['Model']->tenant_id !== null) {
            return false;
        }
        return $this->templateRepo->delete($id);
    }
}
