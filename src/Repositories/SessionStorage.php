<?php

namespace Omdasoft\LaravelWebauthn\Repositories;

use Illuminate\Support\Facades\Session;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialCreationOptions;
use Omdasoft\LaravelWebauthn\Contracts\ChallengeStorage;

class SessionStorage implements ChallengeStorage
{
    public function store(
        string $challengeId,
        PublicKeyCredentialCreationOptions|PublicKeyCredentialRequestOptions|null $options,
        int $ttl
    ): void
    {
        Session::put("webauthn_challenge:{$challengeId}", $options);
        Session::put("webauthn_challenge_expiry:{$challengeId}", now()->addSeconds($ttl)->timestamp);
    }

    public function get(string $challengeId): PublicKeyCredentialCreationOptions|PublicKeyCredentialRequestOptions|null
    {
        $key = "webauthn_challenge:{$challengeId}";
        $expiryKey = "webauthn_challenge_expiry:{$challengeId}";

        if (Session::has($expiryKey) && now()->timestamp > Session::get($expiryKey)) {
            Session::forget([$key, $expiryKey]);
            return null;
        }

        return Session::get($key);
    }

    public function forget(string $challengeId): void
    {
        Session::forget([
            "webauthn_challenge:{$challengeId}",
            "webauthn_challenge_expiry:{$challengeId}",
        ]);
    }
}
