<?php

namespace App\soft\LaravelWebauthn\Traits;

use App\soft\LaravelWebauthn\Assertion\Actions\PrepareAssertionRequest;
use App\soft\LaravelWebauthn\Assertion\Actions\ValidateAssertionRequest;
use Illuminate\Contracts\Auth\Authenticatable as User;
use InvalidArgumentException;
use Omdasoft\LaravelWebauthn\Attestation\Actions\PrepareAttestationCreation;
use Omdasoft\LaravelWebauthn\Attestation\Actions\ValidateAttestationCreation;
use Omdasoft\LaravelWebauthn\Contracts\ChallengeStorage;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\PublicKeyCredentialRequestOptions;

/**
 * Trait HasWebAuthn
 *
 * Requires:
 * - method passkeys(): Relation
 * - model to implement Illuminate\Contracts\Auth\Authenticatable
 */
trait HasWebAuthn
{
    protected ?ChallengeStorage $challengeRepo = null;

    protected function getChallengeRepo(): ChallengeStorage
    {
        if (! $this->challengeRepo) {
            $this->challengeRepo = app(ChallengeStorage::class);
        }

        return $this->challengeRepo;
    }

    public function generateAttestationOptions(User $user): array
    {
        $this->assertValidUser($user);

        $options = app(PrepareAttestationCreation::class)($user);
        $challengeId = $this->generateUniqueChallengeId();

        $this->challengeRepo->store($challengeId, $options, config('webauthn.storage.ttl'));

        return [
            'challenge_id' => $challengeId,
            'passkey' => $options,
        ];
    }

    public function completeAttestation(User $user, array $params): void
    {
        $this->assertValidUser($user);
        $this->assertValidParams($params, ['challenge_id', 'passkey']);

        $challengeId = $params['challenge_id'];
        $publicKeyCredential = $params['passkey'];

        $storedChallenge = $this->challengeRepo->get($challengeId);
        if (! $storedChallenge) {
            throw new NotFoundHttpException('Challenge not found');
        }

        if (! $publicKeyCredential->response instanceof AuthenticatorAttestationResponse) {
            throw new RuntimeException('Not a valid response');
        }

        $decodedChallenge = json_decode($storedChallenge['challenge'], true);

        $publicKeyCredentialSource = app(ValidateAttestationCreation::class)(
            $decodedChallenge,
            $publicKeyCredential->response
        );

        if (! method_exists($this, 'passkeys')) {
            throw new RuntimeException('Missing required passkeys() relationship on model using HasWebAuthn trait.');
        }

        $this->passkeys()->create([
            'credential_id' => $publicKeyCredentialSource->publicKeyCredentialId,
            'data' => $publicKeyCredentialSource,
        ]);

        $this->challengeRepo->forget($storedChallenge['challenge_id']);
    }

    public function generateAssertionOptions(): array
    {
        $options = app(PrepareAssertionRequest::class)();
        $challengeId = $this->generateUniqueChallengeId();

        $this->challengeRepo->store($challengeId, $options, config('webauthn.storage.ttl'));

        return [
            'challenge_id' => $challengeId,
            'passkey' => $options,
        ];
    }

    public function completeAssertion(User $user, array $params): string
    {
        $this->assertValidUser($user);
        $this->assertValidParams($params, ['challenge_id', 'passkey']);

        $publicKeyCredential = $params['passkey'];

        if (! $publicKeyCredential->response instanceof AuthenticatorAssertionResponse) {
            throw new RuntimeException('Invalid authentication response');
        }

        $credentialId = $this->base64urlEncode($publicKeyCredential->rawId);

        if (! method_exists($this, 'passkeys')) {
            throw new RuntimeException('Missing required passkeys() relationship on model using HasWebAuthn trait.');
        }

        $storedCredential = $this->passkeys()->where('credential_id', $credentialId)->firstOrFail();

        $publicKeyCredentialSource = $storedCredential->data;

        $storedChallenge = $this->challengeRepo->get($params['challenge_id']);
        if (! $storedChallenge) {
            throw new NotFoundHttpException('Challenge not found');
        }

        $decodedChallenge = json_decode($storedChallenge['challenge'], true);

        $requestOptions = PublicKeyCredentialRequestOptions::createFromArray($decodedChallenge);

        app(ValidateAssertionRequest::class)(
            $publicKeyCredentialSource,
            $publicKeyCredential->response,
            $requestOptions,
            parse_url(config('webauthn.domain'), PHP_URL_HOST),
            $publicKeyCredentialSource['userHandle']
        );

        $this->challengeRepo->forget($storedChallenge['challenge_id']);

        return $user->createToken('webauthn-token')->plainTextToken;
    }

    protected function generateUniqueChallengeId(): string
    {
        return base64_encode(random_bytes(32));
    }

    protected function assertValidUser($user): void
    {
        if (! $user instanceof User) {
            throw new InvalidArgumentException('Invalid or missing user');
        }
    }

    protected function assertValidParams(array $params, array $requiredKeys): void
    {
        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $params)) {
                throw new InvalidArgumentException("Missing required parameter: {$key}");
            }
        }
    }

    protected function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
