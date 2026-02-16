<?php

namespace Omdasoft\LaravelWebauthn\Actions\Login;

use Illuminate\Contracts\Auth\Authenticatable;
use Omdasoft\LaravelWebauthn\Contracts\HandleLoginAction;

class HandleSanctumLogin implements HandleLoginAction
{
    public function execute(Authenticatable $user): array
    {
        /** @phpstan-ignore-next-line */
        $token = $user->createToken('webauthn-login')->plainTextToken;

        return ['token' => $token];
    }
}
