<?php

namespace Omdasoft\LaravelWebauthn\Repositories;

use Omdasoft\LaravelWebauthn\Models\Passkey;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webauthn\PublicKeyCredentialSource;

class EloquentPublicKeyCredentialSourceRepository
{
    public function __construct(
        protected DenormalizerInterface $serializer
    ) {}

    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
    {
        $passkey = Passkey::where('credential_id', Base64UrlSafe::encode($publicKeyCredentialId))->first();

        if (!$passkey) {
            return null;
        }

        /** @var array<string, mixed> $data */
        $data = $passkey->data;

        // Denormalize array to object
        // We use 'json' format assumption for the serializer context if needed, but passing array usually requires 'denormalize'
        // But the serializer instance from library likely handles standard normalization
        return $this->serializer->denormalize($data, PublicKeyCredentialSource::class, 'json');
    }
}
