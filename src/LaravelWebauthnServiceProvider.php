<?php

namespace Omdasoft\LaravelWebauthn;

use Illuminate\Support\Facades\Route;
use Omdasoft\LaravelWebauthn\Contracts\ChallengeStorage;
use Omdasoft\LaravelWebauthn\Contracts\Webauthn;
use Omdasoft\LaravelWebauthn\Storage\StorageManager;
use Omdasoft\LaravelWebauthn\Support\Config;
use Omdasoft\LaravelWebauthn\Support\Serializer;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;

class LaravelWebauthnServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-webauthn')
            ->hasConfigFile('webauthn')
            ->hasMigration('create_passkey_table')
            ->hasTranslations();
    }

    public function packageBooted(): void
    {
        $this->registerRouteMacros();
    }

    protected function registerRouteMacros(): void
    {
        Route::macro('webauthn', function (?string $prefix = null) {
            $prefix = $prefix ?? config('webauthn.route_prefix', 'api/webauthn');

            Route::prefix($prefix)->group(function () {
                // Attestation (Registration)
                Route::middleware(config('webauthn.middlewares.register', []))->group(function () {
                    Route::post('register/options', [\Omdasoft\LaravelWebauthn\Http\Controllers\LaravelWebauthnController::class, 'registerOptions'])->name('webauthn.register.options');
                    Route::post('register', [\Omdasoft\LaravelWebauthn\Http\Controllers\LaravelWebauthnController::class, 'register'])->name('webauthn.register');
                });

                // Assertion (Login)
                Route::middleware(config('webauthn.middlewares.login', []))->group(function () {
                    Route::post('login/options', [\Omdasoft\LaravelWebauthn\Http\Controllers\LaravelWebauthnController::class, 'loginOptions'])->name('webauthn.login.options');
                    Route::post('login', [\Omdasoft\LaravelWebauthn\Http\Controllers\LaravelWebauthnController::class, 'login'])->name('webauthn.login');
                });
            });
        });
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Webauthn::class, function ($app) {
            return $app->make(LaravelWebauthn::class);
        });

        $this->app->bind(ChallengeStorage::class, function ($app) {
            return $app->make(StorageManager::class)->driver();
        });

        // Register attestation validator
        $this->app->singleton(AuthenticatorAttestationResponseValidator::class, function ($app) {
            $factory = new CeremonyStepManagerFactory;

            return AuthenticatorAttestationResponseValidator::create($factory->creationCeremony());
        });

        // Register assertion validator
        $this->app->singleton(AuthenticatorAssertionResponseValidator::class, function ($app) {
            $factory = new CeremonyStepManagerFactory;

            $host = Config::relyingPartyId();
            if ($host) {
                $factory->setAllowedOrigins([$host]);
            }

            return AuthenticatorAssertionResponseValidator::create($factory->requestCeremony());
        });

        $this->app->singleton(Serializer::class, function ($app) {
            return Serializer::create();
        });
    }
}
