<?php 

namespace App\soft\LaravelWebauthn\Assertion\Actions;

use Webauthn\PublicKeyCredentialSource;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\AuthenticatorAssertionResponseValidator;

class ValidateAssertionRequest {
    public function __invoke(
        PublicKeyCredentialSource|string $credentialId, 
        AuthenticatorAssertionResponse $authenticatorAssertionResponse,
        PublicKeyCredentialRequestOptions $requestOptions,
        ServerRequestInterface|string $request,
        ?string $userHandle
    ): void
    {
        AuthenticatorAssertionResponseValidator::create()->check(
            credentialId: $credentialId,
            authenticatorAssertionResponse: $authenticatorAssertionResponse,
            publicKeyCredentialRequestOptions: $requestOptions,
            request: $request,
            userHandle: $userHandle
        );
    }
}