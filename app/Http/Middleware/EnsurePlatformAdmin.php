<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || !method_exists($user, 'isPlatformAdmin') || !$user->isPlatformAdmin()) {
            abort(403, 'Forbidden: platform admin access required');
        }
        return $next($request);
    }
}
