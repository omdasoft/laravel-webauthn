<?php

namespace Omdasoft\LaravelWebauthn\Tests\Feature;

use Illuminate\Contracts\Auth\Authenticatable;
use Omdasoft\LaravelWebauthn\Contracts\Webauthn;
use Omdasoft\LaravelWebauthn\Tests\Fixtures\User;
use Omdasoft\LaravelWebauthn\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class WebauthnRoutesTest extends TestCase
{
    #[Test]
    public function user_can_get_register_options(): void
    {
        $user = User::query()->create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'secret',
        ]);

        $this->app->instance(Webauthn::class, $this->createMockWebauthn([
            'attestationOptions' => [
                'challenge_id' => 'challenge-1',
                'passkey' => ['k' => 'v'],
            ],
        ]));

        $this->withoutMiddleware()
            ->actingAs($user)
            ->postJson('/api/webauthn/register/options')
            ->assertOk()
            ->assertJson([
                'challenge_id' => 'challenge-1',
                'passkey' => ['k' => 'v'],
            ]);
    }

    #[Test]
    public function user_can_register(): void
    {
        $user = User::query()->create([
            'name' => 'Test',
            'email' => 'test2@example.com',
            'password' => 'secret',
        ]);

        $mock = $this->createMock(Webauthn::class);
        $mock->expects($this->once())
            ->method('completeAttestation')
            ->with(
                $this->isInstanceOf(Authenticatable::class),
                ['challenge_id' => 'challenge-2', 'passkey' => ['a' => 'b']]
            );
        $this->app->instance(Webauthn::class, $mock);

        $this->withoutMiddleware()
            ->actingAs($user)
            ->postJson('/api/webauthn/register', [
                'challenge_id' => 'challenge-2',
                'passkey' => ['a' => 'b'],
            ])
            ->assertOk()
            ->assertContent('');
    }

    #[Test]
    public function user_can_get_login_options(): void
    {
        $this->app->instance(Webauthn::class, $this->createMockWebauthn([
            'assertionOptions' => [
                'challenge_id' => 'challenge-3',
                'passkey' => ['x' => 'y'],
            ],
        ]));

        $this->postJson('/api/webauthn/login/options')
            ->assertOk()
            ->assertJson([
                'challenge_id' => 'challenge-3',
                'passkey' => ['x' => 'y'],
            ]);
    }

    #[Test]
    public function user_can_login(): void
    {
        $mock = $this->createMock(Webauthn::class);
        $mock->expects($this->once())
            ->method('completeAssertion')
            ->with(['challenge_id' => 'challenge-4', 'passkey' => ['p' => 'q']])
            ->willReturn(['token' => 'token-123']);
        $this->app->instance(Webauthn::class, $mock);

        $this->postJson('/api/webauthn/login', [
            'challenge_id' => 'challenge-4',
            'passkey' => ['p' => 'q'],
        ])
            ->assertOk()
            ->assertJson(['token' => 'token-123']);
    }

    /**
     * @param  array<string, mixed>  $returns
     */
    private function createMockWebauthn(array $returns): Webauthn
    {
        $mock = $this->createMock(Webauthn::class);

        foreach ($returns as $method => $returnValue) {
            $mock->method($method)->willReturn($returnValue);
        }

        return $mock;
    }
}
