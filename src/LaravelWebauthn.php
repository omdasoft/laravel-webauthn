<?php

namespace Omdasoft\LaravelWebauthn;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use InvalidArgumentException;
use Omdasoft\LaravelWebauthn\Actions\Assertion\PrepareAssertionRequest;
use Omdasoft\LaravelWebauthn\Actions\Assertion\ValidateAssertionRequest;
use Omdasoft\LaravelWebauthn\Actions\Attestation\PrepareAttestationCreation;
use Omdasoft\LaravelWebauthn\Actions\Attestation\ValidateAttestationCreation;
use Omdasoft\LaravelWebauthn\Contracts\ChallengeStorage;
use Omdasoft\LaravelWebauthn\Contracts\Webauthn;
use Omdasoft\LaravelWebauthn\Support\Config;
use Omdasoft\LaravelWebauthn\Support\Serializer;
use ParagonIE\ConstantTime\Base64UrlSafe;
use RuntimeException;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\PublicKeyCredential;

class LaravelWebauthn implements Webauthn
{
    public function __construct(
        protected ChallengeStorage $storage,
    ) {}

    /**
     * @return array{challenge_id: string, passkey: array<string, mixed>}
     */
    public function attestationOptions(): array
    {
        $options = app(PrepareAttestationCreation::class)();

        $challengeId = $this->generateUniqueChallengeId();

        $this->storage->store($challengeId, $options, Config::storageTTL());

        return [
            'challenge_id' => $challengeId,
            'passkey' => Serializer::make()->toArray($options),
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function completeAttestation(array $params): void
    {
        $challengeId = $params['challenge_id'] ?? null;

        if (!$challengeId) {
            throw new InvalidArgumentException('Challenge ID is required');
        }

        $storedOptions = $this->storage->get($challengeId);
        if (!$storedOptions) {
            throw new RuntimeException('Challenge not found or expired');
        }

        if (!$storedOptions instanceof \Webauthn\PublicKeyCredentialCreationOptions) {
            throw new RuntimeException('Invalid challenge options for attestation');
        }

        $publicKeyCredential = Serializer::make()->fromArray($params['passkey'], PublicKeyCredential::class);
        $response = $publicKeyCredential->response;

        if (!$response instanceof AuthenticatorAttestationResponse) {
            throw new RuntimeException('Invalid response type for attestation');
        }

        $source = app(ValidateAttestationCreation::class)($storedOptions, $response);

        /** @var \Illuminate\Contracts\Auth\Authenticatable|null $user */
        $user = Request::user();

        if (!$user) {
            throw new RuntimeException('User must be authenticated to register a passkey.');
        }

        if (!method_exists($user, 'passkeys')) {
            throw new RuntimeException('The passkey relationship is missing on the user model.');
        }

        $user->passkeys()->create([
            'name' => $params['name'] ?? null,
            'credential_id' => $source->publicKeyCredentialId,
            'data' => $source,
        ]);

        $this->storage->forget($challengeId);
    }

    /**
     * @return array{challenge_id: string, passkey: array<string, mixed>}
     */
    public function assertionOptions(): array
    {
        $options = app(PrepareAssertionRequest::class)();
        $passkeyJson = Serializer::make()->toJson($options);
        $challengeId = $this->generateUniqueChallengeId();

        $this->storage->store($challengeId, $options, Config::storageTTL());

        return [
            'challenge_id' => $challengeId,
            'passkey' => json_decode($passkeyJson, true),
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function completeAssertion(array $params): Authenticatable
    {
        $challengeId = $params['challenge_id'] ?? null;
        if (!$challengeId) {
            throw new InvalidArgumentException('Challenge ID is required');
        }

        $storedOptions = $this->storage->get($challengeId);
        if (!$storedOptions) {
            throw new RuntimeException('Challenge not found or expired');
        }

        if (!$storedOptions instanceof \Webauthn\PublicKeyCredentialRequestOptions) {
            throw new RuntimeException('Invalid challenge options for assertion');
        }

        $publicKeyCredential = Serializer::make()->fromArray($params['passkey'], PublicKeyCredential::class);
        $response = $publicKeyCredential->response;

        if (!$response instanceof AuthenticatorAssertionResponse) {
            throw new RuntimeException('Invalid response type for assertion');
        }

        $passkey = $this->getPublickeyCredentialSource($publicKeyCredential->rawId);
        if (!$passkey) {
            throw new RuntimeException('Passkey not found');
        }

        /** @var \Webauthn\PublicKeyCredentialSource $source */
        $source = $passkey->getAttribute('data');

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $userModel */
        $userModel = Config::getAuthenticatableModel();
        $user = $userModel::find($source->userHandle);

        if (!$user) {
            throw new RuntimeException('User not found');
        }

        /** @var Authenticatable $user */
        app(ValidateAssertionRequest::class)(
            $source,
            $response,
            $storedOptions,
            Config::relyingPartyId(),
            $source->userHandle
        );

        $this->storage->forget($challengeId);

        return $user;
    }

    /**
     * Generate a unique challenge ID for storing the challenge options.
     */
    protected function generateUniqueChallengeId(): string
    {
        return base64_encode(random_bytes(32));
    }

    /**
     * Get the public key credential source from the database.
     */
    protected function getPublickeyCredentialSource(string $credentialId): ?Model
    {
        /** @var class-string<\Omdasoft\LaravelWebauthn\Models\Passkey> $passkeyModel */
        $passkeyModel = Config::getPassKeyModel();

        return $passkeyModel::query()->where('credential_id', Base64UrlSafe::encode($credentialId))->first();
    }
}
