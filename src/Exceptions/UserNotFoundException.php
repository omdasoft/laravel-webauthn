<?php

namespace Omdasoft\LaravelWebauthn\Exceptions;

class UserNotFoundException extends LaravelWebauthnException
{
    public function __construct()
    {
        parent::__construct(__('webauthn::errors.user_not_found'));
    }
}
