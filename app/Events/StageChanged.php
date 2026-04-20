<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StageChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $tenantId,
        public string $caseType,
        public int $caseId,
        public string $fromStage,
        public string $toStage,
        public ?int $userId = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("tenant.{$this->tenantId}")];
    }

    public function broadcastAs(): string
    {
        return 'stage.changed';
    }
}
