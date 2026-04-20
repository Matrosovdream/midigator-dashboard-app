<?php

namespace App\Services\Emails;

use App\Jobs\Emails\SendEmailJob;
use App\Models\User;
use App\Repositories\Email\EmailLogRepo;
use App\Repositories\Email\EmailTemplateRepo;
use App\Repositories\Order\OrderRepo;
use App\Services\Cases\CaseRegistry;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class EmailSendService
{
    public function __construct(
        private EmailLogRepo $emailLogRepo,
        private EmailTemplateRepo $templateRepo,
        private EmailRenderService $renderer,
        private CaseRegistry $registry,
        private OrderRepo $orderRepo,
    ) {}

    public function queue(User $sender, string $caseType, int $caseId, array $data): array
    {
        $model = $this->resolveModel($caseType, $caseId);

        [$subject, $body, $templateId] = $this->composeContent($data, $caseType, $caseId);

        $log = $this->emailLogRepo->logForModel($model, $sender->tenant_id, $sender->id, [
            'to_email' => $data['to_email'],
            'subject' => $subject,
            'body' => $body,
            'template_id' => $templateId,
            'status' => 'queued',
        ]);

        SendEmailJob::dispatch($log['id']);

        return $log;
    }

    public function logsFor(string $caseType, int $caseId): ?array
    {
        $model = $this->resolveModel($caseType, $caseId);
        return $this->emailLogRepo->getForModel($model);
    }

    public function listForTenant(int $tenantId, int $perPage = 50): ?array
    {
        return $this->emailLogRepo->getAll(['tenant_id' => $tenantId], $perPage, ['created_at' => 'desc']);
    }

    private function composeContent(array $data, string $caseType, int $caseId): array
    {
        $templateId = $data['template_id'] ?? null;
        if ($templateId) {
            $template = $this->templateRepo->getByID($templateId);
            if (!$template) {
                throw new RuntimeException('Template not found.');
            }
            $record = $caseType === 'order'
                ? $this->orderRepo->getByID($caseId)
                : $this->registry->repoFor($caseType)->getByID($caseId);

            $subject = $this->renderer->renderFromCase($template['subject'], $record ?? []);
            $body = $this->renderer->renderFromCase($template['body'], $record ?? []);
            return [$subject, $body, $templateId];
        }

        return [$data['subject'] ?? '', $data['body'] ?? '', null];
    }

    private function resolveModel(string $caseType, int $id): Model
    {
        $record = $caseType === 'order'
            ? $this->orderRepo->getByID($id)
            : $this->registry->repoFor($caseType)->getByID($id);

        if (!$record) {
            throw new RuntimeException("$caseType#$id not found.");
        }
        return $record['Model'];
    }
}
