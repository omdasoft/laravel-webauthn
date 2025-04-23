<?php

namespace App\soft\LaravelWebauthn\Assertion\Actions;

use Psr\Http\Message\ServerRequestInterface;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialSource;

class ValidateAssertionRequest
{
    public function __invoke(
        PublicKeyCredentialSource|string $credentialId,
        AuthenticatorAssertionResponse $authenticatorAssertionResponse,
        PublicKeyCredentialRequestOptions $requestOptions,
        ServerRequestInterface|string $request,
        ?string $userHandle
    ): void {
        AuthenticatorAssertionResponseValidator::create()->check(
            credentialId: $credentialId,
            authenticatorAssertionResponse: $authenticatorAssertionResponse,
            publicKeyCredentialRequestOptions: $requestOptions,
            request: $request,
            userHandle: $userHandle
        );
    }
}
