<?php

namespace Omdasoft\LaravelWebauthn\Support;

class Challenge
{
    /**
     * Generate a unique challenge ID.
     */
    public static function generate(): string
    {
        return base64_encode(random_bytes(32));
    }
}
