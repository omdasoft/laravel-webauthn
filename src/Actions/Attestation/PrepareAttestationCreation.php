<?php

namespace Omdasoft\LaravelWebauthn\Attestation\Actions;

use Illuminate\Contracts\Auth\Authenticatable as User;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

class PrepareAttestationCreation
{
    public function __invoke(User $user): PublicKeyCredentialCreationOptions
    {
        if (! $user) {
            throw new \RuntimeException('An authenticated user is required for WebAuthn registration.');
        }

        return new PublicKeyCredentialCreationOptions(
            rp: new PublicKeyCredentialRpEntity(
                name: config('app.name'),
                id: parse_url(config('webauthn.domain'), PHP_URL_HOST),
            ),
            user: new PublicKeyCredentialUserEntity(
                name: $user->name,
                id: $user->id,
                displayName: $user->name,
            ),
            challenge: random_bytes(32),
            authenticatorSelection: new AuthenticatorSelectionCriteria(
                userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED,
                requireResidentKey: true
            ),
            attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE
        );
    }
}
