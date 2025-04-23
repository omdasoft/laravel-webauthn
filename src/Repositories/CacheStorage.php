<?php

namespace Omdasoft\LaravelWebauthn\Repositories;

use Illuminate\Support\Facades\Cache;
use Omdasoft\LaravelWebauthn\Contracts\ChallengeStorage;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;

class CacheStorage implements ChallengeStorage
{
    public function store(
        string $challengeId,
        PublicKeyCredentialCreationOptions|PublicKeyCredentialRequestOptions|null $options,
        int $ttl
    ): void {
        Cache::put("webauthn_challenge:{$challengeId}", $options, now()->addSeconds($ttl));
    }

    public function get(string $challengeId): PublicKeyCredentialCreationOptions|PublicKeyCredentialRequestOptions|null
    {
        return Cache::get("webauthn_challenge:{$challengeId}");
    }

    public function forget(string $challengeId): void
    {
        Cache::forget("webauthn_challenge:{$challengeId}");
    }
}
