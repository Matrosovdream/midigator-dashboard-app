<?php

namespace App\Services\Emails;

use App\Repositories\Email\EmailTemplateRepo;
use Illuminate\Support\Str;

class EmailTemplateService
{
    public function __construct(private EmailTemplateRepo $templateRepo) {}

    public function list(int $tenantId, int $perPage = 50): ?array
    {
        return $this->templateRepo->getAll(['tenant_id' => $tenantId], $perPage, ['name' => 'asc']);
    }

    public function get(int $id): ?array
    {
        return $this->templateRepo->getByID($id);
    }

    public function create(int $tenantId, array $data): array
    {
        return $this->templateRepo->create(array_merge($data, [
            'tenant_id' => $tenantId,
            'is_active' => $data['is_active'] ?? true,
            'variables' => $data['variables'] ?? $this->extractVariables($data['body'] ?? ''),
        ]));
    }

    public function update(int $id, array $data): ?array
    {
        if (isset($data['body']) && !isset($data['variables'])) {
            $data['variables'] = $this->extractVariables($data['body']);
        }
        return $this->templateRepo->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->templateRepo->delete($id);
    }

    private function extractVariables(string $body): array
    {
        preg_match_all('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', $body, $matches);
        return collect($matches[1] ?? [])->unique()->values()->all();
    }
}
