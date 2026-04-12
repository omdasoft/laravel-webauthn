<?php

namespace Omdasoft\LaravelWebauthn\Repositories;

use Illuminate\Support\Facades\Session;
use Omdasoft\LaravelWebauthn\Contracts\ChallengeStorage;
use Omdasoft\LaravelWebauthn\Support\Serializer;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;

class SessionStorage implements ChallengeStorage
{
    public function __construct(
        protected Serializer $serializer
    ) {}

    public function store(
        string $challengeId,
        PublicKeyCredentialCreationOptions|PublicKeyCredentialRequestOptions|null $options,
        int $ttl
    ): void {
        if ($options === null) {
            $this->forget($challengeId);

            return;
        }

        $data = [
            'class' => get_class($options),
            'options' => $this->serializer->toJson($options),
        ];

        Session::put("webauthn_challenge:{$challengeId}", $data);
        Session::put("webauthn_challenge_expiry:{$challengeId}", now()->addSeconds($ttl)->timestamp);
    }

    public function get(string $challengeId): PublicKeyCredentialCreationOptions|PublicKeyCredentialRequestOptions|null
    {
        $key = "webauthn_challenge:{$challengeId}";
        $expiryKey = "webauthn_challenge_expiry:{$challengeId}";

        if (Session::has($expiryKey) && now()->timestamp > Session::get($expiryKey)) {
            $this->forget($challengeId);

            return null;
        }

        $data = Session::get($key);

        if (!is_array($data) || !isset($data['class'], $data['options'])) {
            return null;
        }

        /** @var class-string<PublicKeyCredentialCreationOptions|PublicKeyCredentialRequestOptions> $class */
        $class = $data['class'];

        return $this->serializer->fromJson($data['options'], $class);
    }

    public function forget(string $challengeId): void
    {
        Session::forget([
            "webauthn_challenge:{$challengeId}",
            "webauthn_challenge_expiry:{$challengeId}",
        ]);
    }
}
