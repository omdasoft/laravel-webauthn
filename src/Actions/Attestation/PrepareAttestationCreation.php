<?php

namespace Omdasoft\LaravelWebauthn\Actions\Attestation;

use Illuminate\Support\Str;
use Omdasoft\LaravelWebauthn\Contracts\HasPasskey;
use Omdasoft\LaravelWebauthn\Exceptions\UserUnauthenticatedException;
use Omdasoft\LaravelWebauthn\Support\Config;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

class PrepareAttestationCreation
{
    public function __invoke(): PublicKeyCredentialCreationOptions
    {
        /** @var HasPasskey|null $user */
        $user = request()->user();

        if (!$user) {
            throw new UserUnauthenticatedException;
        }

        return new PublicKeyCredentialCreationOptions(
            rp: new PublicKeyCredentialRpEntity(
                name: Config::relyingPartyName(),
                id: Config::relyingPartyId(),
            ),
            user: new PublicKeyCredentialUserEntity(
                name: $user->getPasskeyName(),
                id: (string) $user->getPasskeyIdentifier(),
                displayName: $user->getPasskeyDisplayName(),
            ),
            challenge: Str::random(32),
            authenticatorSelection: new AuthenticatorSelectionCriteria(
                userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED,
                residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED
            ),
            attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE
        );
    }
}
