<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Http\Actions\Api\V1\Platform\Tenant\DeleteTenantAction;
use App\Http\Actions\Api\V1\Platform\Tenant\ListTenantActivityAction;
use App\Http\Actions\Api\V1\Platform\Tenant\ListTenantsAction;
use App\Http\Actions\Api\V1\Platform\Tenant\ListTenantUsersAction;
use App\Http\Actions\Api\V1\Platform\Tenant\ListTenantWebhooksAction;
use App\Http\Actions\Api\V1\Platform\Tenant\OverviewTenantAction;
use App\Http\Actions\Api\V1\Platform\Tenant\ShowTenantAction;
use App\Http\Actions\Api\V1\Platform\Tenant\StoreTenantAction;
use App\Http\Actions\Api\V1\Platform\Tenant\TestConnectionAction;
use App\Http\Actions\Api\V1\Platform\Tenant\ToggleActiveAction;
use App\Http\Actions\Api\V1\Platform\Tenant\UpdateTenantAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(Request $request, ListTenantsAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function show(int $id, ShowTenantAction $action): JsonResponse
    {
        return $action->handle($id);
    }

    public function store(Request $request, StoreTenantAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function update(Request $request, int $id, UpdateTenantAction $action): JsonResponse
    {
        return $action->handle($request, $id);
    }

    public function destroy(int $id, DeleteTenantAction $action): JsonResponse
    {
        return $action->handle($id);
    }

    public function overview(int $id, OverviewTenantAction $action): JsonResponse
    {
        return $action->handle($id);
    }

    public function users(Request $request, int $id, ListTenantUsersAction $action): JsonResponse
    {
        return $action->handle($request, $id);
    }

    public function activity(Request $request, int $id, ListTenantActivityAction $action): JsonResponse
    {
        return $action->handle($request, $id);
    }

    public function webhooks(Request $request, int $id, ListTenantWebhooksAction $action): JsonResponse
    {
        return $action->handle($request, $id);
    }

    public function toggleActive(int $id, ToggleActiveAction $action): JsonResponse
    {
        return $action->handle($id);
    }

    public function testConnection(Request $request, TestConnectionAction $action): JsonResponse
    {
        return $action->handle($request);
    }
}
