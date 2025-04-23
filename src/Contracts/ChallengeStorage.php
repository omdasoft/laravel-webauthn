<?php

namespace Omdasoft\LaravelWebauthn\Contracts;

use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;

interface ChallengeStorage
{
    public function store(
        string $challengeId,
        PublicKeyCredentialCreationOptions|PublicKeyCredentialRequestOptions|null $options,
        int $ttl
    ): void;

    public function get(string $challengeId): PublicKeyCredentialCreationOptions|PublicKeyCredentialRequestOptions|null;

    public function forget(string $challengeId): void;
}
