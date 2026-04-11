<?php

namespace Omdasoft\LaravelWebauthn\Actions\Assertion;

use Omdasoft\LaravelWebauthn\Contracts\ChallengeStorage;
use Omdasoft\LaravelWebauthn\Support\Challenge;
use Omdasoft\LaravelWebauthn\Support\Config;
use Omdasoft\LaravelWebauthn\Support\Serializer;

class GenerateLoginOptions
{
    public function __construct(
        protected ChallengeStorage $storage,
        protected PrepareAssertionRequest $prepareAssertionRequest,
    ) {}

    /**
     * @return array{challenge_id: string, passkey: array<string, mixed>}
     */
    public function execute(): array
    {
        $options = ($this->prepareAssertionRequest)();
        $passkeyJson = Serializer::make()->toJson($options);
        $challengeId = Challenge::generate();

        $this->storage->store($challengeId, $options, Config::storageTTL());

        return [
            'challenge_id' => $challengeId,
            'passkey' => json_decode($passkeyJson, true),
        ];
    }
}
