<?php

namespace Omdasoft\LaravelWebauthn\Storage;

use Illuminate\Support\Facades\Session;
use Omdasoft\LaravelWebauthn\Contracts\ChallengeStorage;

class SessionStorage implements ChallengeStorage
{
    public function store(string $challengeId, array $data, int $ttl): void
    {
        Session::put("webauthn_challenge:{$challengeId}", $data);
        Session::put("webauthn_challenge_expiry:{$challengeId}", now()->addSeconds($ttl)->timestamp);
    }

    public function get(string $challengeId): ?array
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
