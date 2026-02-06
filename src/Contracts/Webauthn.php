<?php

namespace Omdasoft\LaravelWebauthn\Contracts;

interface Webauthn
{
    /**
     * @return array{challenge_id: string, passkey: array<string, mixed>}
     */
    public function attestationOptions(\Illuminate\Contracts\Auth\Authenticatable $user): array;

    /**
     * @param  array<string, mixed>  $params
     */
    public function completeAttestation(\Illuminate\Contracts\Auth\Authenticatable $user, array $params): void;

    /**
     * @return array{challenge_id: string, passkey: array<string, mixed>}
     */
    public function assertionOptions(): array;

    /**
     * @param  array<string, mixed>  $params
     * @return array{token: string}
     */
    public function completeAssertion(array $params): array; // Returns token or status
}
