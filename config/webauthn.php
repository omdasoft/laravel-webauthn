<?php

// config for Omdasoft/LaravelWebauthn
return [
    'domain' => env('WEBAUTHN_DOMAIN', 'localhost'),
    'storage' => [
        'driver' => env('WEBAUTHN_STORAGE_DRIVER', 'cache'), // cache, session
        'ttl' => env('WEBAUTHN_CHALLENGE_TTL', 300), // 5 minutes in seconds
    ],
];
