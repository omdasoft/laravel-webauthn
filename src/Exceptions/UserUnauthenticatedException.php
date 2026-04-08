<?php

namespace Omdasoft\LaravelWebauthn\Exceptions;

class UserUnauthenticatedException extends LaravelWebauthnException
{
    public function __construct()
    {
        parent::__construct(__('webauthn::errors.user_unauthenticated'));
    }
}
