<?php

namespace Omdasoft\LaravelWebauthn;

use Omdasoft\LaravelWebauthn\Commands\LaravelWebauthnCommand;
use Omdasoft\LaravelWebauthn\Contracts\ChallengeStorage;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelWebauthnServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-webauthn')
            ->hasConfigFile('webauthn')
            ->hasViews()
            ->hasMigration('create_passkey_table')
            ->hasCommand(LaravelWebauthnCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(ChallengeStorage::class, function () {
            return match (config('webauthn.storage.driver', 'cache')) {
                'cache' => new CacheStorage,
                'session' => new SessionStorage,
                'database' => new DatabaseStorage,
                default => throw new \InvalidArgumentException('Invalid WebAuthn storage driver'),
            };
        });

        $this->app->singleton(WebAuthnService::class, function ($app) {
            return new WebAuthnService($app->make(ChallengeStorage::class));
        });
    }
}
