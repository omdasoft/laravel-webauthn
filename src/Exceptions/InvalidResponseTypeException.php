<?php

namespace Omdasoft\LaravelWebauthn\Exceptions;

class InvalidResponseTypeException extends LaravelWebauthnException
{
    public function __construct(string $type)
    {
        parent::__construct(__("webauthn::errors.invalid_response_type_{$type}"));
    }
}
