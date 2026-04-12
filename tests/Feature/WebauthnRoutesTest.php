<?php

namespace Omdasoft\LaravelWebauthn\Tests\Feature;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Event;
use Omdasoft\LaravelWebauthn\Actions\Assertion\AuthenticatePasskey;
use Omdasoft\LaravelWebauthn\Actions\Assertion\GenerateLoginOptions;
use Omdasoft\LaravelWebauthn\Actions\Attestation\GenerateRegistrationOptions;
use Omdasoft\LaravelWebauthn\Actions\Attestation\RegisterPasskey;
use Omdasoft\LaravelWebauthn\Actions\Login\HandleSanctumLogin;
use Omdasoft\LaravelWebauthn\Contracts\HandleLoginAction;
use Omdasoft\LaravelWebauthn\Events\WebauthnLogin;
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

        $mock = $this->createMock(GenerateRegistrationOptions::class);
        $mock->expects($this->once())
            ->method('execute')
            ->willReturn([
                'challenge_id' => 'challenge-1',
                'passkey' => ['k' => 'v'],
            ]);
        $this->app->instance(GenerateRegistrationOptions::class, $mock);

        $this->withoutMiddleware()
            ->actingAs($user)
            ->postJson('/webauthn/register/options')
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

        $mock = $this->createMock(RegisterPasskey::class);
        $mock->expects($this->once())
            ->method('execute')
            ->with(
                ['challenge_id' => 'challenge-2', 'passkey' => ['a' => 'b'], 'name' => null],
                $this->callback(fn ($u) => $u->is($user))
            );
        $this->app->instance(RegisterPasskey::class, $mock);

        $this->withoutMiddleware()
            ->actingAs($user)
            ->postJson('/webauthn/register', [
                'challenge_id' => 'challenge-2',
                'passkey' => ['a' => 'b'],
                'name' => null,
            ])
            ->assertOk()
            ->assertContent('');
    }

    #[Test]
    public function user_can_get_login_options(): void
    {
        $mock = $this->createMock(GenerateLoginOptions::class);
        $mock->expects($this->once())
            ->method('execute')
            ->willReturn([
                'challenge_id' => 'challenge-3',
                'passkey' => ['x' => 'y'],
            ]);
        $this->app->instance(GenerateLoginOptions::class, $mock);

        $this->postJson('/webauthn/login/options')
            ->assertOk()
            ->assertJson([
                'challenge_id' => 'challenge-3',
                'passkey' => ['x' => 'y'],
            ]);
    }

    #[Test]
    public function user_can_login(): void
    {
        Event::fake();

        $user = User::query()->create([
            'name' => 'Test',
            'email' => 'login@example.com',
            'password' => 'secret',
        ]);

        $mock = $this->createMock(AuthenticatePasskey::class);
        $mock->expects($this->once())
            ->method('execute')
            ->with(['challenge_id' => 'challenge-4', 'passkey' => ['p' => 'q']])
            ->willReturn($user);
        $this->app->instance(AuthenticatePasskey::class, $mock);

        // Mock the action to avoid needing Sanctum setup in this test
        $this->app->bind(HandleSanctumLogin::class, function () {
            return new class implements HandleLoginAction
            {
                public function execute(Authenticatable $user): array
                {
                    return ['token' => 'mocked-token'];
                }
            };
        });

        $this->postJson('/webauthn/login', [
            'challenge_id' => 'challenge-4',
            'passkey' => ['p' => 'q'],
        ])
            ->assertOk()
            ->assertJson(['token' => 'mocked-token']);

        Event::assertDispatched(WebauthnLogin::class, function ($event) use ($user) {
            return $event->user->is($user);
        });
    }
}
