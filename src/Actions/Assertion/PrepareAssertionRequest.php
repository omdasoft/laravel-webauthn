<?php

namespace App\soft\LaravelWebauthn\Assertion\Actions;

use Webauthn\PublicKeyCredentialRequestOptions;

class PrepareAssertionRequest
{
    public function __invoke(): PublicKeyCredentialRequestOptions
    {
        return new PublicKeyCredentialRequestOptions(
            challenge: random_bytes(32),
            rpId: parse_url(config('webauthn.domain'), PHP_URL_HOST),
            allowCredentials: [],
            userVerification: 'required'
        );
    }
}
