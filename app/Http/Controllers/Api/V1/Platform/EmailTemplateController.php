<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Http\Actions\Api\V1\Platform\EmailTemplate\DeleteEmailTemplateAction;
use App\Http\Actions\Api\V1\Platform\EmailTemplate\ListEmailTemplatesAction;
use App\Http\Actions\Api\V1\Platform\EmailTemplate\ShowEmailTemplateAction;
use App\Http\Actions\Api\V1\Platform\EmailTemplate\StoreEmailTemplateAction;
use App\Http\Actions\Api\V1\Platform\EmailTemplate\UpdateEmailTemplateAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index(Request $request, ListEmailTemplatesAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function show(int $id, ShowEmailTemplateAction $action): JsonResponse
    {
        return $action->handle($id);
    }

    public function store(Request $request, StoreEmailTemplateAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function update(Request $request, int $id, UpdateEmailTemplateAction $action): JsonResponse
    {
        return $action->handle($request, $id);
    }

    public function destroy(int $id, DeleteEmailTemplateAction $action): JsonResponse
    {
        return $action->handle($id);
    }
}
