<?php

namespace Omdasoft\LaravelWebauthn\Storage;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Manager;
use Omdasoft\LaravelWebauthn\Contracts\ChallengeStorage;
use Omdasoft\LaravelWebauthn\Repositories\CacheStorage;
use Omdasoft\LaravelWebauthn\Repositories\SessionStorage;

class StorageManager extends Manager
{
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    public function getDefaultDriver(): ChallengeStorage
    {
        return config('webauthn.storage.driver', 'cache');
    }

    public function createCacheDriver(): ChallengeStorage
    {
        return new CacheStorage;
    }

    public function createSessionDriver(): ChallengeStorage
    {
        return new SessionStorage;
    }
}
