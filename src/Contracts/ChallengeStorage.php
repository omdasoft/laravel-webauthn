<?php

namespace Omdasoft\LaravelWebauthn\Contracts;

use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;

interface ChallengeStorage
{
    public function store(
        string $challengeId,
        PublicKeyCredentialCreationOptions|PublicKeyCredentialRequestOptions $options,
        string $deviceName,
        int $ttl
    ): void;

    public function get(string $challengeId): ?array;

    public function forget(string $challengeId): void;
}
