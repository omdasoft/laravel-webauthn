<?php

namespace Omdasoft\LaravelWebauthn;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
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
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_webauthn_table')
            ->hasCommand(LaravelWebauthnCommand::class);
    }
}
