<?php

namespace Omdasoft\LaravelWebauthn;

use Illuminate\Support\Facades\Auth;
use Omdasoft\LaravelWebauthn\Contracts\ChallengeStorage;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

class LaravelWebauthn
{
    protected $challengeStorage;

    public function __construct(ChallengeStorage $challengeStorage)
    {
        $this->challengeStorage = $challengeStorage;
    }

    public function generateRegistrationOptions(array $params): array
    {
        $user = Auth::user();

        $options = new PublicKeyCredentialCreationOptions(
            rp: new PublicKeyCredentialRpEntity(
                name: config('app.name'),
                id: parse_url(config('webauthn.domain'), PHP_URL_HOST),
            ),
            user: new PublicKeyCredentialUserEntity(
                name: $user->name,
                id: $user->id,
                displayName: $user->name,
            ),
            challenge: random_bytes(32),
            authenticatorSelection: new AuthenticatorSelectionCriteria(
                userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED,
                requireResidentKey: true
            ),
            attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE
        );

        $challengeId = $this->generateUniqueChallengeId();

        $this->storeChallenge($challengeId, $options, $params['device_name']);

        return [
            'challenge_id' => $challengeId,
            'passkey' => $options,
        ];
    }

    public function completeRegistration(array $params): void
    {
        $user = Auth::user();

        $storedChallenge = $this->getChallenge($params['challenge_id']);

        if (! $storedChallenge) {
            throw new \Exception('Challenge not found');
        }

        $publicKeyCredential = $params['passkey']; // Assume JSON is decoded by the caller

        if (! $publicKeyCredential->response instanceof AuthenticatorAttestationResponse) {
            throw new \Exception('Not a valid response');
        }

        $optionsArray = json_decode($storedChallenge['challenge'], true);

        $publicKeyCredentialSource = AuthenticatorAttestationResponseValidator::create()->check(
            $publicKeyCredential->response,
            PublicKeyCredentialCreationOptions::createFromArray($optionsArray),
            parse_url(config('webauthn.domain'), PHP_URL_HOST)
        );

        // Store the passkey credentials (assumes a Passkey model exists in the app)
        $user->passkeys()->updateOrCreate(
            ['device_name' => $storedChallenge['device_name']],
            [
                'credential_id' => $this->base64urlEncode($publicKeyCredentialSource->publicKeyCredentialId),
                'is_enabled' => true,
                'data' => json_encode($publicKeyCredentialSource),
            ]
        );

        $this->removeChallenge($storedChallenge['challenge_id']);
    }

    public function generateLoginOptions(): array
    {
        $options = new PublicKeyCredentialRequestOptions(
            challenge: random_bytes(32),
            rpId: parse_url(config('webauthn.domain'), PHP_URL_HOST),
            allowCredentials: [],
            userVerification: 'required'
        );

        $challengeId = $this->generateUniqueChallengeId();

        $this->storeChallenge($challengeId, $options);

        return [
            'challenge_id' => $challengeId,
            'passkey' => $options,
        ];
    }

    public function completeLogin(array $params): array
    {
        $publicKeyCredential = $params['passkey']; // Assume JSON decoded

        if (! $publicKeyCredential->response instanceof AuthenticatorAssertionResponse) {
            throw new \Exception('Invalid authentication');
        }

        $storedCredential = Auth::user()->passkeys()
            ->where('credential_id', $this->base64urlEncode($publicKeyCredential->rawId))
            ->firstOrFail();

        if (! $storedCredential->is_enabled) {
            throw new \Exception('This passkey is disabled');
        }

        $publicKeyCredentialSource = json_decode($storedCredential->data, true);
        $storedChallenge = $this->getChallenge($params['challenge_id']);

        if (! $storedChallenge) {
            throw new \Exception('No challenge found');
        }

        $publicKeyCredentialRequestOptions = PublicKeyCredentialRequestOptions::createFromArray(
            json_decode($storedChallenge['challenge'], true)
        );

        AuthenticatorAssertionResponseValidator::create()->check(
            credentialId: $publicKeyCredentialSource,
            authenticatorAssertionResponse: $publicKeyCredential->response,
            publicKeyCredentialRequestOptions: $publicKeyCredentialRequestOptions,
            request: parse_url(config('webauthn.domain'), PHP_URL_HOST),
            userHandle: $publicKeyCredentialSource['userHandle']
        );

        $this->removeChallenge($storedChallenge['challenge_id']);

        $user = Auth::user();
        $token = $user->createToken('webauthn-token')->plainTextToken;

        return ['token' => $token];
    }

    private function generateUniqueChallengeId()
    {
        return base64_encode(random_bytes(32));
    }

    private function storeChallenge(
        string $challengeId,
        PublicKeyCredentialCreationOptions|PublicKeyCredentialRequestOptions $options,
        ?string $deviceName = null
    ): void {
        $this->challengeStorage->store($challengeId, $options, $deviceName);
    }

    private function getChallenge(mixed $challenge_id)
    {
        return $this->challengeStorage->get($challenge_id);
    }

    private function base64urlEncode(string $publicKeyCredentialId)
    {
        return rtrim(strtr(base64_encode($publicKeyCredentialId), '+/', '-_'), '=');
    }

    private function removeChallenge(mixed $challenge_id)
    {
        $this->challengeStorage->forget($challenge_id);
    }
}
