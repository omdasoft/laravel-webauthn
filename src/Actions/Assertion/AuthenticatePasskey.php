<?php

namespace Omdasoft\LaravelWebauthn\Actions\Assertion;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Omdasoft\LaravelWebauthn\Contracts\ChallengeStorage;
use Omdasoft\LaravelWebauthn\Exceptions\ChallengeMissingException;
use Omdasoft\LaravelWebauthn\Exceptions\ChallengeNotFoundException;
use Omdasoft\LaravelWebauthn\Exceptions\InvalidChallengeOptionsException;
use Omdasoft\LaravelWebauthn\Exceptions\InvalidResponseTypeException;
use Omdasoft\LaravelWebauthn\Exceptions\PasskeyNotFoundException;
use Omdasoft\LaravelWebauthn\Exceptions\UserNotFoundException;
use Omdasoft\LaravelWebauthn\Repositories\PasskeyRepository;
use Omdasoft\LaravelWebauthn\Support\Config;
use Omdasoft\LaravelWebauthn\Support\Serializer;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialSource;

class AuthenticatePasskey
{
    public function __construct(
        protected ChallengeStorage $storage,
        protected PasskeyRepository $repository,
        protected ValidateAssertionRequest $validateAssertionRequest,
    ) {}

    /**
     * @param  array<string, mixed>  $params
     */
    public function execute(array $params): Authenticatable
    {
        $challengeId = $params['challenge_id'] ?? null;
        if (!$challengeId) {
            throw new ChallengeMissingException;
        }

        $storedOptions = $this->storage->get($challengeId);
        if (!$storedOptions) {
            throw new ChallengeNotFoundException;
        }

        if (!$storedOptions instanceof PublicKeyCredentialRequestOptions) {
            throw new InvalidChallengeOptionsException('assertion');
        }

        $publicKeyCredential = Serializer::make()->fromArray($params['passkey'], PublicKeyCredential::class);
        $response = $publicKeyCredential->response;

        if (!$response instanceof AuthenticatorAssertionResponse) {
            throw new InvalidResponseTypeException('assertion');
        }

        $passkey = $this->repository->findByCredentialId($publicKeyCredential->rawId);
        if (!$passkey) {
            throw new PasskeyNotFoundException;
        }

        /** @var PublicKeyCredentialSource $source */
        $source = $passkey->getAttribute('data');

        /** @var class-string<Model> $userModel */
        $userModel = Config::getAuthenticatableModel();
        $user = $userModel::find($source->userHandle);

        if (!$user) {
            throw new UserNotFoundException;
        }

        /** @var Authenticatable $user */
        ($this->validateAssertionRequest)(
            $source,
            $response,
            $storedOptions,
            Config::relyingPartyId(),
            $source->userHandle
        );

        $this->storage->forget($challengeId);

        return $user;
    }
}
