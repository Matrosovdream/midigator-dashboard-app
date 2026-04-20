<?php

namespace App\Services\Cases;

use App\Repositories\AbstractRepo;
use App\Repositories\Chargeback\ChargebackRepo;
use App\Repositories\Order\OrderValidationRepo;
use App\Repositories\Prevention\PreventionAlertRepo;
use App\Repositories\Rdr\RdrCaseRepo;
use RuntimeException;

class CaseRegistry
{
    public const CHARGEBACK = 'chargeback';
    public const PREVENTION = 'prevention';
    public const ORDER_VALIDATION = 'order_validation';
    public const RDR = 'rdr';

    public function __construct(
        private ChargebackRepo $chargebackRepo,
        private PreventionAlertRepo $preventionRepo,
        private OrderValidationRepo $orderValidationRepo,
        private RdrCaseRepo $rdrRepo,
    ) {}

    public function repoFor(string $type): AbstractRepo
    {
        return match ($type) {
            self::CHARGEBACK => $this->chargebackRepo,
            self::PREVENTION => $this->preventionRepo,
            self::ORDER_VALIDATION => $this->orderValidationRepo,
            self::RDR => $this->rdrRepo,
            default => throw new RuntimeException("Unknown case type: $type"),
        };
    }

    public function allStageableTypes(): array
    {
        return [self::CHARGEBACK, self::PREVENTION, self::ORDER_VALIDATION, self::RDR];
    }
}
