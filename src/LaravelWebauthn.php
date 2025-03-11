<?php

namespace Omdasoft\LaravelWebauthn;

use Omdasoft\LaravelWebauthn\Storage\CacheStorage;

class LaravelWebauthn {
    protected $storage;
    public function __construct(CacheStorage $storage)
    {
        $this->storage = $storage;
    }

    public function generateRegistrationOptions()
    {
        //TODO: implement generate registration options
    }

    public function completeRegistration()
    {
        //TODO: implement complete registration
    }

    public function generateLoginOptions()
    {
        //TODO: implementy generate login options
    }

    public function completeLogin()
    {
        //TODO: implement complete login
    }
}
