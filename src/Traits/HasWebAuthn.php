<?php

namespace Omdasoft\LaravelWebauthn\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Omdasoft\LaravelWebauthn\Models\Passkey;

trait HasWebAuthn
{
    public function passkeys(): HasMany
    {
        return $this->hasMany(Passkey::class);
    }
}
