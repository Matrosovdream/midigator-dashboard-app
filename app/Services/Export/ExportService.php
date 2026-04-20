<?php

namespace App\Services\Export;

use App\Services\Cases\CaseRegistry;
use App\Repositories\Order\OrderRepo;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    public function __construct(
        private CaseRegistry $registry,
        private OrderRepo $orderRepo,
    ) {}

    public function streamCsv(string $caseType, int $tenantId, array $filter = []): StreamedResponse
    {
        $repo = $caseType === 'order' ? $this->orderRepo : $this->registry->repoFor($caseType);
        $model = $repo->getModel();

        $query = $model->where('tenant_id', $tenantId);
        foreach ($filter as $key => $value) {
            if ($value !== null && $value !== '') {
                $query->where($key, $value);
            }
        }

        $filename = sprintf('%s-export-%s.csv', $caseType, now()->format('Ymd-His'));

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            $first = true;

            $query->chunk(500, function ($chunk) use (&$first, $handle) {
                foreach ($chunk as $row) {
                    $attrs = $row->getAttributes();
                    if ($first) {
                        fputcsv($handle, array_keys($attrs));
                        $first = false;
                    }
                    fputcsv($handle, array_map(fn ($v) => is_scalar($v) ? $v : json_encode($v), $attrs));
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
