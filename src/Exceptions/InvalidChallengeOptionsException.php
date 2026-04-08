<?php

namespace Omdasoft\LaravelWebauthn\Exceptions;

class InvalidChallengeOptionsException extends LaravelWebauthnException
{
    public function __construct(string $type)
    {
        parent::__construct(__("webauthn::errors.invalid_challenge_options_{$type}"));
    }
}
