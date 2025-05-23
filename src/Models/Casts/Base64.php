<?php

namespace Omdasoft\LaravelWebauthn\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Webauthn\Util\Base64 as Base64Webauthn;

/**
 * @implements CastsAttributes<string, string>
 */
class Base64 implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  mixed  $value
     */
    #[\Override]
    public function get($model, string $key, $value, array $attributes): ?string
    {
        return $value !== null ? Base64Webauthn::decode($value) : null;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string|null  $value
     */
    #[\Override]
    public function set($model, string $key, mixed $value, array $attributes): ?string
    {
        return $value !== null ? Base64UrlSafe::encode($value) : null;
    }
}
