<?php

namespace Omdasoft\LaravelWebauthn\Repositories;

use Illuminate\Support\Facades\Cache;
use Omdasoft\LaravelWebauthn\Contracts\ChallengeStorage;
use Omdasoft\LaravelWebauthn\Support\Serializer;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;

class CacheStorage implements ChallengeStorage
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

        Cache::put("webauthn_challenge:{$challengeId}", $data, now()->addSeconds($ttl));
    }

    public function get(string $challengeId): PublicKeyCredentialCreationOptions|PublicKeyCredentialRequestOptions|null
    {
        $data = Cache::get("webauthn_challenge:{$challengeId}");

        if (!is_array($data) || !isset($data['class'], $data['options'])) {
            return null;
        }

        /** @var class-string<PublicKeyCredentialCreationOptions|PublicKeyCredentialRequestOptions> $class */
        $class = $data['class'];

        return $this->serializer->fromJson($data['options'], $class);
    }

    public function forget(string $challengeId): void
    {
        Cache::forget("webauthn_challenge:{$challengeId}");
    }
}
