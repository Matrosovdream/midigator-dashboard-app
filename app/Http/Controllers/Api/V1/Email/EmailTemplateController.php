<?php

namespace App\Http\Controllers\Api\V1\Email;

use App\Http\Actions\Api\V1\Email\DeleteTemplateAction;
use App\Http\Actions\Api\V1\Email\ListTemplatesAction;
use App\Http\Actions\Api\V1\Email\StoreTemplateAction;
use App\Http\Actions\Api\V1\Email\UpdateTemplateAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index(Request $request, ListTemplatesAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function store(Request $request, StoreTemplateAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function update(Request $request, int $id, UpdateTemplateAction $action): JsonResponse
    {
        return $action->handle($request, $id);
    }

    public function destroy(int $id, DeleteTemplateAction $action): JsonResponse
    {
        return $action->handle($id);
    }
}
