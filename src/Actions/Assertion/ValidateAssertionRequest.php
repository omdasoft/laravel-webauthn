<?php

namespace Omdasoft\LaravelWebauthn\Actions\Assertion;

use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialSource;

class ValidateAssertionRequest
{
    public function __construct(
        protected AuthenticatorAssertionResponseValidator $validator
    ) {}

    public function __invoke(
        PublicKeyCredentialSource $credentialSource,
        AuthenticatorAssertionResponse $authenticatorAssertionResponse,
        PublicKeyCredentialRequestOptions $requestOptions,
        string $host,
        ?string $userHandle
    ): PublicKeyCredentialSource {
        return $this->validator->check(
            publicKeyCredentialSource: $credentialSource,
            authenticatorAssertionResponse: $authenticatorAssertionResponse,
            publicKeyCredentialRequestOptions: $requestOptions,
            host: $host,
            userHandle: $userHandle
        );
    }
}
