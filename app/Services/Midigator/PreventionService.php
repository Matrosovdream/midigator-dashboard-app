<?php

namespace App\Services\Midigator;

use App\Models\Tenant;
use App\Repositories\Prevention\PreventionAlertRepo;
use Illuminate\Http\Client\Response;
use RuntimeException;

class PreventionService
{
    public const RESOLUTION_TYPES = [
        'could_not_find_order',
        'declined_or_canceled_nothing_to_do',
        'issued_full_refund',
        'issued_refund_for_remaining_amount',
        '3ds_authorized_successfully',
        'previously_refunded_nothing_to_do',
        'unable_to_refund_merchant_account_closed',
        'other',
    ];

    public function __construct(
        private MidigatorClient $client,
        private PreventionAlertRepo $preventionRepo,
    ) {}

    public function submitResolution(Tenant $tenant, int $alertId, string $resolutionType, ?string $description = null): Response
    {
        if (!in_array($resolutionType, self::RESOLUTION_TYPES, true)) {
            throw new RuntimeException("Unknown resolution type: $resolutionType");
        }

        $record = $this->preventionRepo->getByID($alertId);
        if (!$record) {
            throw new RuntimeException("Prevention alert $alertId not found.");
        }

        $guid = $record['prevention_guid'];
        $payload = array_filter([
            'resolution_type' => $resolutionType,
            'description' => $description,
        ], fn ($v) => $v !== null);

        $response = $this->client->post($tenant, "/prevention/v1/prevention/$guid/resolution", $payload);

        if ($response->successful()) {
            $this->preventionRepo->recordResolution($alertId, $resolutionType);
        }

        return $response;
    }
}
