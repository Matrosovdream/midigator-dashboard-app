<?php

namespace App\Http\Controllers\Api\V1\Export;

use App\Http\Actions\Api\V1\Export\ExportCsvAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function csv(Request $request, string $type, ExportCsvAction $action): StreamedResponse
    {
        return $action->handle($request, $type);
    }
}
