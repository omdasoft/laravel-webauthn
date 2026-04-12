<?php

namespace Omdasoft\LaravelWebauthn\Support;

use Illuminate\Database\Eloquent\Model;
use Omdasoft\LaravelWebauthn\Models\Passkey;

class Config
{
    /**
     * @return class-string<Model>
     */
    public static function getAuthenticatableModel(): string
    {
        /** @var class-string<Model> $model */
        $model = config('webauthn.models.authenticatable');

        return $model;
    }

    /**
     * @return class-string<Model>
     */
    public static function getPassKeyModel(): string
    {
        /** @var class-string<Model> $model */
        $model = config('webauthn.models.passkey', Passkey::class);

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

    public static function routePrefix(): string
    {
        return config('webauthn.route_prefix', 'webauthn');
    }
}
