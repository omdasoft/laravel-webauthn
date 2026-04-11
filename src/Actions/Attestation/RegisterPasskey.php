<?php

namespace Omdasoft\LaravelWebauthn\Actions\Attestation;

use Omdasoft\LaravelWebauthn\Contracts\ChallengeStorage;
use Omdasoft\LaravelWebauthn\Contracts\HasPasskey;
use Omdasoft\LaravelWebauthn\Exceptions\ChallengeMissingException;
use Omdasoft\LaravelWebauthn\Exceptions\ChallengeNotFoundException;
use Omdasoft\LaravelWebauthn\Exceptions\InvalidChallengeOptionsException;
use Omdasoft\LaravelWebauthn\Exceptions\InvalidResponseTypeException;
use Omdasoft\LaravelWebauthn\Support\Serializer;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;

class RegisterPasskey
{
    public function __construct(
        protected ChallengeStorage $storage,
        protected ValidateAttestationCreation $validateAttestationCreation,
    ) {}

    /**
     * @param  array<string, mixed>  $params
     */
    public function execute(array $params, HasPasskey $user): void
    {
        $challengeId = $params['challenge_id'] ?? null;

        if (!$challengeId) {
            throw new ChallengeMissingException;
        }

        $storedOptions = $this->storage->get($challengeId);
        if (!$storedOptions) {
            throw new ChallengeNotFoundException;
        }

        if (!$storedOptions instanceof PublicKeyCredentialCreationOptions) {
            throw new InvalidChallengeOptionsException('attestation');
        }

        $publicKeyCredential = Serializer::make()->fromArray($params['passkey'], PublicKeyCredential::class);
        $response = $publicKeyCredential->response;

        if (!$response instanceof AuthenticatorAttestationResponse) {
            throw new InvalidResponseTypeException('attestation');
        }

        $source = ($this->validateAttestationCreation)($storedOptions, $response);

        $user->passkeys()->create([
            'name' => $params['name'] ?? null,
            'credential_id' => $source->publicKeyCredentialId,
            'data' => $source,
        ]);

        $this->storage->forget($challengeId);
    }
}
