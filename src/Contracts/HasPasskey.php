<?php

namespace Omdasoft\LaravelWebauthn\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Omdasoft\LaravelWebauthn\Models\Passkey;

interface HasPasskey
{
    /**
     * @return HasMany<Passkey, Model>
     */
    public function passkeys(): HasMany;

    public function getPasskeyIdentifier(): string;

    public function getPasskeyName(): string;

    public function getPasskeyDisplayName(): string;
}
