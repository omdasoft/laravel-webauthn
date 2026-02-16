<?php

namespace Omdasoft\LaravelWebauthn\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Omdasoft\LaravelWebauthn\Models\Casts\Base64;
use Omdasoft\LaravelWebauthn\Support\Config;
use Omdasoft\LaravelWebauthn\Support\Serializer;
use Webauthn\PublicKeyCredentialSource;

/**
 * @property int $id
 * @property int $user_id
 * @property string $credential_id
 * @property \Webauthn\PublicKeyCredentialSource $data
 * @property \Illuminate\Contracts\Auth\Authenticatable $user
 */
class Passkey extends Model
{
    protected $table = 'passkeys';

    protected $fillable = [
        'user_id',
        'credential_id',
        'data',
    ];

    protected $casts = [
        'credential_id' => Base64::class,
    ];

    /**
     * @return BelongsTo<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function authenticatable(): BelongsTo
    {
        $authenticatableModel = Config::getAuthenticatableModel();

        return $this->belongsTo($authenticatableModel);
    }

    /**
     * @return Attribute<PublicKeyCredentialSource, PublicKeyCredentialSource>
     */
    public function data(): Attribute
    {
        $serializer = Serializer::make();

        return new Attribute(
            get: fn (string $value) => $serializer->fromJson(
                $value,
                PublicKeyCredentialSource::class,
            ),
            /**
             * @param  PublicKeyCredentialSource  $value
             * @return array{credential_id: string, data: string}
             */
            set: fn (PublicKeyCredentialSource $value) => [
                'data' => $serializer->toJson($value),
            ],
        );
    }
}
