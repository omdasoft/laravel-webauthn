<?php

namespace Omdasoft\LaravelWebauthn\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Omdasoft\LaravelWebauthn\LaravelWebauthn
 */
class LaravelWebauthn extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Omdasoft\LaravelWebauthn\LaravelWebauthn::class;
    }
}
