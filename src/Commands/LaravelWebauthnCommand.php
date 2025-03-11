<?php

namespace Omdasoft\LaravelWebauthn\Commands;

use Illuminate\Console\Command;

class LaravelWebauthnCommand extends Command
{
    public $signature = 'laravel-webauthn';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
