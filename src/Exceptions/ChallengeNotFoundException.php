<?php

namespace Omdasoft\LaravelWebauthn\Exceptions;

class ChallengeNotFoundException extends LaravelWebauthnException
{
    public function __construct()
    {
        parent::__construct(__('webauthn::errors.challenge_not_found_or_expired'));
    }
}
