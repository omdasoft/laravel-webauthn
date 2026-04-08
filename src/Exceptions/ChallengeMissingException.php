<?php

namespace Omdasoft\LaravelWebauthn\Exceptions;

class ChallengeMissingException extends LaravelWebauthnException
{
    public function __construct()
    {
        parent::__construct(__('webauthn::errors.challenge_missing'));
    }
}
