<?php

namespace Omdasoft\LaravelWebauthn\Attestation\Actions;

use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialSource;

class ValidateAttestationCreation
{
    public function __invoke(array $challengeArray, AuthenticatorAttestationResponse $response): PublicKeyCredentialSource
    {
        return AuthenticatorAttestationResponseValidator::create()->check(
            $response,
            PublicKeyCredentialCreationOptions::createFromArray($challengeArray),
            parse_url(config('webauthn.domain'), PHP_URL_HOST)
        );
    }
}
