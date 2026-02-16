<?php

namespace Omdasoft\LaravelWebauthn\Actions\Attestation;

use Omdasoft\LaravelWebauthn\Support\Config;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialSource;

class ValidateAttestationCreation
{
    public function __construct(
        protected AuthenticatorAttestationResponseValidator $validator
    ) {}

    public function __invoke(PublicKeyCredentialCreationOptions $options, AuthenticatorAttestationResponse $response): PublicKeyCredentialSource
    {
        return $this->validator->check(
            $response,
            $options,
            Config::relyingPartyId()
        );
    }
}
