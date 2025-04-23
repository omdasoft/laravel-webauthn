<?php

namespace Omdasoft\LaravelWebauthn\Models;

use App\soft\LaravelWebauthn\Traits\HasWebAuthn;
use Illuminate\Database\Eloquent\Model;
use Omdasoft\LaravelWebauthn\Models\Casts\Base64;

class Passkey extends Model
{
    use HasWebAuthn;

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
}
