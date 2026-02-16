<?php

namespace Omdasoft\LaravelWebauthn\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Omdasoft\LaravelWebauthn\Contracts\HasPasskey;
use Omdasoft\LaravelWebauthn\Traits\InteractWithPasskeys;

class User extends Authenticatable implements HasPasskey
{
    use InteractWithPasskeys;

    protected $table = 'users';

    protected $guarded = [];
}
