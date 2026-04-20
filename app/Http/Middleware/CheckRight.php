<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRight
{
    public function handle(Request $request, Closure $next, string $right): Response
    {
        $user = $request->user();
        if (!$user || !method_exists($user, 'hasRight') || !$user->hasRight($right)) {
            abort(403, 'Forbidden: missing right '.$right);
        }
        return $next($request);
    }
}
