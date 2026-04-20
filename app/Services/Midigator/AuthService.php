<?php

namespace App\Services\Midigator;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AuthService
{
    public function getToken(Tenant $tenant): string
    {
        $key = $this->cacheKey($tenant);

        return Cache::remember($key, $this->ttlFor($tenant), fn () => $this->fetchToken($tenant));
    }

    public function forget(Tenant $tenant): void
    {
        Cache::forget($this->cacheKey($tenant));
    }

    private function fetchToken(Tenant $tenant): string
    {
        $secret = $tenant->midigator_api_secret;
        if (empty($secret)) {
            throw new RuntimeException("Tenant {$tenant->id} is missing Midigator API secret.");
        }

        $env = $tenant->midigator_sandbox_mode ? 'sandbox' : 'production';
        $baseUrl = (string) config("midigator.base_urls.$env");

        $response = Http::baseUrl($baseUrl)
            ->timeout((int) config('midigator.http.timeout', 15))
            ->connectTimeout((int) config('midigator.http.connect_timeout', 5))
            ->acceptJson()
            ->withToken($secret)
            ->post('/auth/v1/auth');

        if (!$response->successful()) {
            throw new RuntimeException('Midigator auth failed: '.$response->status().' '.$response->body());
        }

        $token = (string) $response->json('token');
        if ($token === '') {
            throw new RuntimeException('Midigator auth returned empty token.');
        }

        return $token;
    }

    private function ttlFor(Tenant $tenant): int
    {
        return (int) config('midigator.token_cache.fallback_ttl_seconds', 300);
    }

    private function cacheKey(Tenant $tenant): string
    {
        return config('midigator.token_cache.key_prefix', 'midigator:token:').$tenant->id;
    }
}
