<?php

namespace Omdasoft\LaravelWebauthn\Actions\Login;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Omdasoft\LaravelWebauthn\Contracts\HandleLoginAction;

class HandleSessionLogin implements HandleLoginAction
{
    public function execute(Authenticatable $user): array
    {
        Auth::login($user);

        return ['status' => 'success'];
    }
}
