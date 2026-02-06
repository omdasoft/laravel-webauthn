<?php

namespace Omdasoft\LaravelWebauthn;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Request;
use InvalidArgumentException;
use Omdasoft\LaravelWebauthn\Actions\Assertion\PrepareAssertionRequest;
use Omdasoft\LaravelWebauthn\Actions\Assertion\ValidateAssertionRequest;
use Omdasoft\LaravelWebauthn\Actions\Attestation\PrepareAttestationCreation;
use Omdasoft\LaravelWebauthn\Actions\Attestation\ValidateAttestationCreation;
use Omdasoft\LaravelWebauthn\Contracts\ChallengeStorage;
use Omdasoft\LaravelWebauthn\Contracts\Webauthn;
use Omdasoft\LaravelWebauthn\Repositories\EloquentPublicKeyCredentialSourceRepository;
use RuntimeException;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\PublicKeyCredential;

class LaravelWebauthn implements Webauthn
{
    public function __construct(
        protected ChallengeStorage $storage,
        protected PrepareAttestationCreation $prepareAttestation,
        protected ValidateAttestationCreation $validateAttestation,
        protected PrepareAssertionRequest $prepareAssertion,
        protected ValidateAssertionRequest $validateAssertion,
        protected SerializerInterface $serializer,
        protected EloquentPublicKeyCredentialSourceRepository $repository,
    ) {}

    /**
     * @return array{challenge_id: string, passkey: array<string, mixed>}
     */
    public function attestationOptions(Authenticatable $user): array
    {
        $options = ($this->prepareAttestation)($user);

        $passkeyJson = json_encode($options);
        if ($passkeyJson === false) {
            throw new RuntimeException('Unable to encode attestation options');
        }

        $challengeId = $this->generateUniqueChallengeId();

        $this->storage->store($challengeId, $options, config('webauthn.storage.ttl', 3600));

        return [
            'challenge_id' => $challengeId,
            'passkey' => json_decode($passkeyJson, true),
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function completeAttestation(Authenticatable $user, array $params): void
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

        $json = json_encode($params['passkey']);
        if ($json === false) {
            throw new RuntimeException('Unable to encode passkey');
        }
        $publicKeyCredential = $this->serializer->deserialize($json, PublicKeyCredential::class, 'json');
        $response = $publicKeyCredential->response;

        if (!$response instanceof AuthenticatorAttestationResponse) {
            throw new RuntimeException('Invalid response type for attestation');
        }

        $source = ($this->validateAttestation)($storedOptions, $response);

        if (!method_exists($user, 'passkeys')) {
            throw new RuntimeException('The user model must use the HasWebAuthn trait.');
        }

        $user->passkeys()->create([
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
        $options = ($this->prepareAssertion)();

        $passkeyJson = json_encode($options);
        if ($passkeyJson === false) {
            throw new RuntimeException('Unable to encode assertion options');
        }
        $challengeId = $this->generateUniqueChallengeId();

        $this->storage->store($challengeId, $options, config('webauthn.storage.ttl', 3600));

        return [
            'challenge_id' => $challengeId,
            'passkey' => json_decode($passkeyJson, true),
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{token: string}
     */
    public function completeAssertion(array $params): array
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

        $json = json_encode($params['passkey']);
        if ($json === false) {
            throw new RuntimeException('Unable to encode passkey');
        }
        $publicKeyCredential = $this->serializer->deserialize($json, PublicKeyCredential::class, 'json');
        $response = $publicKeyCredential->response;

        if (!$response instanceof AuthenticatorAssertionResponse) {
            throw new RuntimeException('Invalid response type for assertion');
        }

        $credentialId = $publicKeyCredential->rawId;

        $source = $this->repository->findOneByCredentialId($credentialId);
        if (!$source) {
            throw new RuntimeException('Passkey not found');
        }

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $userModel */
        $userModel = config('auth.providers.users.model');
        $user = $userModel::find($source->userHandle);

        if (!$user) {
            throw new RuntimeException('User not found');
        }

        ($this->validateAssertion)(
            $source,
            $response,
            $storedOptions,
            Request::instance()->getHost(),
            $source->userHandle
        );

        $this->storage->forget($challengeId);

        /** @phpstan-ignore-next-line */
        $token = $user->createToken('webauthn-login')->plainTextToken;

        return ['token' => $token];
    }

    protected function generateUniqueChallengeId(): string
    {
        return base64_encode(random_bytes(32));
    }
}
