<?php

namespace Omdasoft\LaravelWebauthn\Storage;

use Illuminate\Support\Facades\Cache;
use Omdasoft\LaravelWebauthn\Contracts\ChallengeStorage;

class CacheStorage implements ChallengeStorage
{
    public function store(string $challengeId, array $data, int $ttl): void
    {
        Cache::put("webauthn_challenge:{$challengeId}", $data, now()->addSeconds($ttl));
    }

    public function get(string $challengeId): ?array
    {
        return Cache::get("webauthn_challenge:{$challengeId}");
    }

    public function forget(string $challengeId): void
    {
        Cache::forget("webauthn_challenge:{$challengeId}");
    }
}
