<?php

namespace Omdasoft\LaravelWebauthn\Actions\Attestation;

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
        $host = parse_url(config('webauthn.domain'), PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            throw new \RuntimeException('Invalid webauthn.domain configuration');
        }

        return $this->validator->check(
            $response,
            $options,
            $host
        );
    }
}
