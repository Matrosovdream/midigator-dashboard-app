<?php

return [
    'base_urls' => [
        'production' => env('MIDIGATOR_PROD_URL', 'https://api.midigator.com'),
        'sandbox' => env('MIDIGATOR_SANDBOX_URL', 'https://api-sandbox.midigator.com'),
    ],

    'http' => [
        'timeout' => (int) env('MIDIGATOR_HTTP_TIMEOUT', 15),
        'connect_timeout' => (int) env('MIDIGATOR_HTTP_CONNECT_TIMEOUT', 5),
    ],

    'token_cache' => [
        'key_prefix' => 'midigator:token:',
        'ttl_buffer_seconds' => (int) env('MIDIGATOR_TOKEN_TTL_BUFFER', 60),
        'fallback_ttl_seconds' => (int) env('MIDIGATOR_TOKEN_FALLBACK_TTL', 300),
    ],

    'event_types' => [
        'chargeback.new',
        'chargeback.match',
        'chargeback.result',
        'chargeback.dnf',
        'prevention.new',
        'prevention.match',
        'order_validation.new',
        'order_validation.match',
        'rdr.new',
        'rdr.match',
        'registration.new',
    ],
];
