<?php

namespace Omdasoft\LaravelWebauthn\Contracts;

interface Webauthn
{
    public static function attestationOptions(): array;

    public static function completeAttestation(array $params): void;

    public static function assertionOptions(): array;

    public static function completeAssertion(array $params): ?string;
}
