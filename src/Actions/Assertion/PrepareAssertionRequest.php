<?php

namespace Omdasoft\LaravelWebauthn\Actions\Assertion;

use Webauthn\PublicKeyCredentialRequestOptions;

class PrepareAssertionRequest
{
    /**
     * @param  array<int, mixed>  $allowCredentials
     */
    public function __invoke(array $allowCredentials = [], string $userVerification = 'preferred'): PublicKeyCredentialRequestOptions
    {
        $rpId = parse_url(config('webauthn.domain'), PHP_URL_HOST);
        $rpId = $rpId === false ? null : $rpId;

        return new PublicKeyCredentialRequestOptions(
            challenge: random_bytes(32),
            rpId: $rpId,
            allowCredentials: $allowCredentials,
            userVerification: $userVerification
        );
    }
}
