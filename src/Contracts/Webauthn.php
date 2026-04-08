<?php

namespace Omdasoft\LaravelWebauthn\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface Webauthn
{
    /**
     * @return array{challenge_id: string, passkey: array<string, mixed>}
     */
    public function attestationOptions(): array;

    /**
     * @param  array<string, mixed>  $params
     */
    public function completeAttestation(array $params): void;

    /**
     * @return array{challenge_id: string, passkey: array<string, mixed>}
     */
    public function assertionOptions(): array;

    /**
     * @param  array<string, mixed>  $params
     */
    public function completeAssertion(array $params): Authenticatable;
}
