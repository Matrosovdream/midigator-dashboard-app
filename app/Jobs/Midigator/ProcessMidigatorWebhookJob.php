<?php

namespace App\Jobs\Midigator;

use App\Services\Midigator\WebhookProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMidigatorWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $backoff = 30;

    public function __construct(
        public int $webhookLogId,
        public int $tenantId,
        public string $eventType,
        public array $payload,
    ) {}

    public function handle(WebhookProcessor $processor): void
    {
        $processor->process($this->webhookLogId, $this->tenantId, $this->eventType, $this->payload);
    }
}
