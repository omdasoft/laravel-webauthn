<?php

namespace Omdasoft\LaravelWebauthn\Repositories;

use Illuminate\Database\Eloquent\Model;
use Omdasoft\LaravelWebauthn\Models\Passkey;
use Omdasoft\LaravelWebauthn\Support\Config;
use ParagonIE\ConstantTime\Base64UrlSafe;

class PasskeyRepository
{
    /**
     * Get the public key credential source from the database.
     */
    public function findByCredentialId(string $credentialId): ?Model
    {
        /** @var class-string<Passkey> $passkeyModel */
        $passkeyModel = Config::getPassKeyModel();

        return $passkeyModel::query()
            ->where('credential_id', Base64UrlSafe::encode($credentialId))
            ->first();
    }
}
