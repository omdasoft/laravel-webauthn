<?php

namespace Omdasoft\LaravelWebauthn\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface Webauthn
{
    /**
     * @return array{challenge_id: string, passkey: array<string, mixed>}
     */
    public function attestationOptions(HasPasskey $user): array;

    /**
     * @param  array<string, mixed>  $params
     */
    public function completeAttestation(array $params, HasPasskey $user): void;

    /**
     * @return array{challenge_id: string, passkey: array<string, mixed>}
     */
    public function assertionOptions(): array;

    /**
     * @param  array<string, mixed>  $params
     */
    public function completeAssertion(array $params): Authenticatable;
}
