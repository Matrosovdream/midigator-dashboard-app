<?php

namespace App\Repositories\Email;

use App\Models\EmailTemplate;
use App\Repositories\AbstractRepo;

class EmailTemplateRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new EmailTemplate();
    }

    public function getActiveForTenant(int $tenantId, $paginate = 50)
    {
        return $this->getAll(['tenant_id' => $tenantId, 'is_active' => true], $paginate, ['name' => 'asc']);
    }

    public function getByNameForTenant(int $tenantId, string $name)
    {
        $item = $this->model
            ->where('tenant_id', $tenantId)
            ->where('name', $name)
            ->first();

        return $this->mapItem($item);
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
            'subject' => $item->subject,
            'body' => $item->body,
            'variables' => $item->variables ?? [],
            'is_active' => (bool) $item->is_active,
            'created_at' => $item->created_at,
            'Model' => $item,
        ];
    }
}
