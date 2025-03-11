<?php

namespace Omdasoft\LaravelWebauthn\Contracts;

interface ChallengeStorage
{
    public function store(string $challengeId, array $data, int $ttl): void;
    public function get(string $challengeId): ?array;
    public function forget(string $challengeId): void;
}
