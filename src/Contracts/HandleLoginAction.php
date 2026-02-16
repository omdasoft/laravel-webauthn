<?php

namespace Omdasoft\LaravelWebauthn\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface HandleLoginAction
{
    /**
     * Handle the user login and return the desired response data.
     *
     * @return array<string, mixed>
     */
    public function execute(Authenticatable $user): array;
}
