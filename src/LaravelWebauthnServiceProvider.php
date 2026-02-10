<?php

namespace Omdasoft\LaravelWebauthn;

use Omdasoft\LaravelWebauthn\Contracts\ChallengeStorage;
use Omdasoft\LaravelWebauthn\Contracts\Webauthn;
use Omdasoft\LaravelWebauthn\Repositories\EloquentPublicKeyCredentialSourceRepository;
use Omdasoft\LaravelWebauthn\Storage\StorageManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\Denormalizer\WebauthnSerializerFactory;

class LaravelWebauthnServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-webauthn')
            ->hasConfigFile('webauthn')
            ->hasRoute('api')
            ->hasMigration('create_passkey_table');
    }

    public function packageBooted(): void
    {
        $this->publishes([
            __DIR__.'/../routes/api.php' => base_path('routes/webauthn.php'),
        ], 'laravel-webauthn-routes');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Webauthn::class, function ($app) {
            return $app->make(LaravelWebauthn::class);
        });

        $this->app->bind(StorageManager::class, function ($app) {
            return new StorageManager($app);
        });

        $this->app->bind(ChallengeStorage::class, function ($app) {
            return $app->make(StorageManager::class)->driver();
        });

        // Register the WebAuthn serializer
        $this->app->singleton('webauthn.serializer', function ($app) {
            $attestationStatementSupportManager = AttestationStatementSupportManager::create();
            $attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());

            return (new WebauthnSerializerFactory($attestationStatementSupportManager))->create();
        });

        // Bind SerializerInterface to webauthn.serializer for injection
        $this->app->bind(SerializerInterface::class, function ($app) {
            return $app->make('webauthn.serializer');
        });

        // Register repository with serializer dependency
        $this->app->bind(EloquentPublicKeyCredentialSourceRepository::class, function ($app) {
            return new EloquentPublicKeyCredentialSourceRepository(
                $app->make('webauthn.serializer')
            );
        });

        // Register attestation validator
        $this->app->singleton(AuthenticatorAttestationResponseValidator::class, function ($app) {
            $factory = new CeremonyStepManagerFactory;

            return AuthenticatorAttestationResponseValidator::create($factory->creationCeremony());
        });

        // Register assertion validator
        $this->app->singleton(AuthenticatorAssertionResponseValidator::class, function ($app) {
            $factory = new CeremonyStepManagerFactory;

            $host = parse_url(config('webauthn.domain'), PHP_URL_HOST);
            if ($host) {
                $factory->setAllowedOrigins([config('webauthn.domain')]);
            }

            return AuthenticatorAssertionResponseValidator::create($factory->requestCeremony());
        });
    }
}
