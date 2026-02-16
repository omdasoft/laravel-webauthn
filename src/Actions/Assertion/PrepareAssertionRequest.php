<?php

namespace Omdasoft\LaravelWebauthn\Actions\Assertion;

use Illuminate\Support\Str;
use Omdasoft\LaravelWebauthn\Support\Config;
use Webauthn\PublicKeyCredentialRequestOptions;

class PrepareAssertionRequest
{
    public function __invoke(): PublicKeyCredentialRequestOptions
    {
        return new PublicKeyCredentialRequestOptions(
            challenge: Str::random(32),
            rpId: Config::relyingPartyId(),
            allowCredentials: [],
            userVerification: 'preferred'
        );
    }
}
