<?php

namespace Omdasoft\LaravelWebauthn\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Omdasoft\LaravelWebauthn\Models\Casts\Base64;

/**
 * @property int $id
 * @property int $user_id
 * @property string $credential_id
 * @property array<string, mixed> $data
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
        'data' => 'array',
        'credential_id' => Base64::class,
    ];

    /**
     * @return BelongsTo<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function user(): BelongsTo
    {
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $userModel */
        $userModel = config('auth.providers.users.model');

        return $this->belongsTo($userModel);
    }
}
