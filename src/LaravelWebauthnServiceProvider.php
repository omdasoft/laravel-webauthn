<?php

namespace Omdasoft\LaravelWebauthn;

use Spatie\LaravelPackageTools\Package;
use Omdasoft\LaravelWebauthn\Contracts\Webauthn;
use Omdasoft\LaravelWebauthn\Storage\StorageManager;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Omdasoft\LaravelWebauthn\Contracts\ChallengeStorage;
use Omdasoft\LaravelWebauthn\Commands\LaravelWebauthnCommand;

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
            ->hasRoute('routes')
            ->hasMigration('create_passkey_table')
            ->hasCommand(LaravelWebauthnCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Webauthn::class, function ($app) {
            return new LaravelWebauthn();
        });

        $this->app->singleton(StorageManager::class, function ($app) {
            return new StorageManager($app);
        });
        
        $this->app->bind(ChallengeStorage::class, function ($app) {
            return $app->make(StorageManager::class)->driver();
        });
    }
}
