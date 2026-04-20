<?php

namespace App\Services\Midigator;

use App\Models\Tenant;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class MidigatorClient
{
    public function __construct(private AuthService $auth) {}

    public function request(Tenant $tenant): PendingRequest
    {
        return Http::baseUrl($this->baseUrl($tenant))
            ->timeout((int) config('midigator.http.timeout', 15))
            ->connectTimeout((int) config('midigator.http.connect_timeout', 5))
            ->acceptJson()
            ->asJson()
            ->withToken($this->auth->getToken($tenant));
    }

    public function get(Tenant $tenant, string $path, array $query = []): Response
    {
        return $this->request($tenant)->get($path, $query);
    }

    public function post(Tenant $tenant, string $path, array $data = []): Response
    {
        return $this->request($tenant)->post($path, $data);
    }

    public function put(Tenant $tenant, string $path, array $data = []): Response
    {
        return $this->request($tenant)->put($path, $data);
    }

    public function delete(Tenant $tenant, string $path): Response
    {
        return $this->request($tenant)->delete($path);
    }

    public function baseUrl(Tenant $tenant): string
    {
        $env = $tenant->midigator_sandbox_mode ? 'sandbox' : 'production';
        return (string) config("midigator.base_urls.$env");
    }
}
