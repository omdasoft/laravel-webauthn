<?php

namespace Omdasoft\LaravelWebauthn\Exceptions;

class PasskeyNotFoundException extends LaravelWebauthnException
{
    public function __construct()
    {
        parent::__construct(__('webauthn::errors.passkey_not_found'));
    }
}
