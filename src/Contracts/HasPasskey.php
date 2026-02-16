<?php

namespace Omdasoft\LaravelWebauthn\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface HasPasskey
{
    /**
     * @return HasMany<\Omdasoft\LaravelWebauthn\Models\Passkey, \Illuminate\Database\Eloquent\Model>
     */
    public function passkeys(): HasMany;

    public function getPasskeyIdentifier(): string;

    public function getPasskeyName(): string;

    public function getPasskeyDisplayName(): string;
}
