<?php

namespace Omdasoft\LaravelWebauthn\Support;

class Config
{
    /**
     * @return class-string<\Illuminate\Database\Eloquent\Model>
     */
    public static function getAuthenticatableModel(): string
    {
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $model */
        $model = config('webauthn.models.authenticatable');

        return $model;
    }

    /**
     * @return class-string<\Illuminate\Database\Eloquent\Model>
     */
    public static function getPassKeyModel(): string
    {
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $model */
        $model = config('webauthn.models.passkey', \Omdasoft\LaravelWebauthn\Models\Passkey::class);

        return $model;
    }

    public static function storageTTL(): int
    {
        return config('webauthn.storage.ttl', 3600);
    }

    public static function relyingPartyId(): string
    {
        return config('webauthn.relying_party.id');
    }

    public static function relyingPartyName(): string
    {
        return config('webauthn.relying_party.name');
    }
}
