<?php

namespace Omdasoft\LaravelWebauthn\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Omdasoft\LaravelWebauthn\Support\Config;

trait InteractWithPasskeys
{
    public function passkeys(): HasMany
    {
        $passkeyModel = Config::getPassKeyModel();

        return $this->hasMany($passkeyModel, 'authenticatable_id');
    }

    public function getPasskeyIdentifier(): string
    {
        return (string) $this->id;
    }

    public function getPasskeyName(): string
    {
        return $this->email;
    }

    public function getPasskeyDisplayName(): string
    {
        return $this->name;
    }
}
