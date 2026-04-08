<?php

namespace Omdasoft\LaravelWebauthn\Exceptions;

class PasskeyRelationshipMissingException extends LaravelWebauthnException
{
    public function __construct()
    {
        parent::__construct(__('webauthn::errors.passkey_relationship_missing'));
    }
}
