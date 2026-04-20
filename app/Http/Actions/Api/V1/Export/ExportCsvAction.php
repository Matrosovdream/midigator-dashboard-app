<?php

namespace App\Http\Actions\Api\V1\Export;

use App\Services\Export\ExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportCsvAction
{
    public function __construct(private ExportService $export) {}

    public function handle(Request $request, string $caseType): StreamedResponse
    {
        $filter = (array) $request->input('filter', []);
        return $this->export->streamCsv($caseType, $request->user()->tenant_id, $filter);
    }
}
