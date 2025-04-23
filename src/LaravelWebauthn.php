<?php

namespace Omdasoft\LaravelWebauthn;

use Illuminate\Support\Facades\Auth;
use Omdasoft\LaravelWebauthn\Contracts\Webauthn;
use Omdasoft\LaravelWebauthn\Models\Passkey;

class LaravelWebauthn implements Webauthn
{
    public static function attestationOptions(): array
    {
        $user = Auth::user();

        return Passkey::generateAttestationOptions($user);
    }

    public static function completeAttestation(array $params): void
    {
        $user = Auth::user();
        Passkey::completeAttestation($user, $params);
    }

    public static function assertionOptions(): array
    {
        return Passkey::generateAssertionOptions();
    }

    public static function completeAssertion(array $params): ?string
    {
        $user = Auth::user();

        return Passkey::completeAssertion($user, $params);
    }
}
