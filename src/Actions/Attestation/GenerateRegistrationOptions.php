<?php

namespace Omdasoft\LaravelWebauthn\Actions\Attestation;

use Omdasoft\LaravelWebauthn\Contracts\ChallengeStorage;
use Omdasoft\LaravelWebauthn\Contracts\HasPasskey;
use Omdasoft\LaravelWebauthn\Support\Challenge;
use Omdasoft\LaravelWebauthn\Support\Config;
use Omdasoft\LaravelWebauthn\Support\Serializer;

class GenerateRegistrationOptions
{
    public function __construct(
        protected ChallengeStorage $storage,
        protected PrepareAttestationCreation $prepareAttestationCreation,
    ) {}

    /**
     * @return array{challenge_id: string, passkey: array<string, mixed>}
     */
    public function execute(HasPasskey $user): array
    {
        $options = ($this->prepareAttestationCreation)($user);

        $challengeId = Challenge::generate();

        $this->storage->store($challengeId, $options, Config::storageTTL());

        return [
            'challenge_id' => $challengeId,
            'passkey' => Serializer::make()->toArray($options),
        ];
    }
}
