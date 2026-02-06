<?php

namespace Omdasoft\LaravelWebauthn\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Omdasoft\LaravelWebauthn\Traits\HasWebAuthn;

class User extends Authenticatable
{
    use HasWebAuthn;

    protected $table = 'users';

    protected $guarded = [];
}
