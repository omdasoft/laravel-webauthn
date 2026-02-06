<?php

namespace Omdasoft\LaravelWebauthn\Actions\Attestation;

use Illuminate\Contracts\Auth\Authenticatable as User;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

class PrepareAttestationCreation
{
    public function __invoke(User $user): PublicKeyCredentialCreationOptions
    {
        /** @phpstan-ignore-next-line */
        $name = $user->name ?? $user->email ?? 'User';
        /** @phpstan-ignore-next-line */
        $id = (string) $user->getAuthIdentifier();

        $rpId = parse_url(config('webauthn.domain'), PHP_URL_HOST);
        $rpId = $rpId === false ? null : $rpId;

        return new PublicKeyCredentialCreationOptions(
            rp: new PublicKeyCredentialRpEntity(
                name: config('app.name'),
                id: $rpId,
            ),
            user: new PublicKeyCredentialUserEntity(
                name: $name,
                id: $id,
                displayName: $name,
            ),
            challenge: random_bytes(32),
            authenticatorSelection: new AuthenticatorSelectionCriteria(
                userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED,
                residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED
            ),
            attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE
        );
    }
}
