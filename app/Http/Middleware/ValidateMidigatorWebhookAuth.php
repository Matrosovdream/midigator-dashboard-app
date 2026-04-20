<?php

namespace App\Http\Middleware;

use App\Repositories\Tenant\TenantRepo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateMidigatorWebhookAuth
{
    public function __construct(private TenantRepo $tenantRepo) {}

    public function handle(Request $request, Closure $next): Response
    {
        $slug = (string) $request->query('tenant', '');
        if ($slug === '') {
            abort(401, 'Missing tenant parameter.');
        }

        $record = $this->tenantRepo->getBySlug($slug);
        if (!$record || !$record['is_active']) {
            abort(401, 'Unknown or inactive tenant.');
        }

        /** @var \App\Models\Tenant $tenant */
        $tenant = $record['Model'];

        $username = $request->getUser();
        $password = $request->getPassword();
        $expectedUser = $tenant->midigator_webhook_username;
        $expectedPass = $tenant->midigator_webhook_password;

        if (
            !is_string($username) || !is_string($password)
            || !is_string($expectedUser) || !is_string($expectedPass)
            || !hash_equals($expectedUser, $username)
            || !hash_equals($expectedPass, $password)
        ) {
            abort(401, 'Invalid webhook credentials.');
        }

        $request->attributes->set('webhook_tenant', $tenant);

        return $next($request);
    }
}
