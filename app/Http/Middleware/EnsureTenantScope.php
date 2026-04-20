<?php

namespace App\Http\Middleware;

use App\Repositories\Tenant\TenantRepo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantScope
{
    public function __construct(private TenantRepo $tenantRepo) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->tenant_id && !$user->isPlatformAdmin()) {
            $record = $this->tenantRepo->getByID($user->tenant_id);
            if ($record) {
                app()->instance('tenant', $record['Model']);
            }
        }
        return $next($request);
    }
}
