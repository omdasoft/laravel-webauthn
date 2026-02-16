<?php

namespace Omdasoft\LaravelWebauthn\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebauthnLogin
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Authenticatable $user
    ) {}
}
