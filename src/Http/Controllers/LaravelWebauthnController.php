<?php

namespace Omdasoft\LaravelWebauthn\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Omdasoft\LaravelWebauthn\Contracts\HandleLoginAction;
use Omdasoft\LaravelWebauthn\Contracts\Webauthn;
use Omdasoft\LaravelWebauthn\Events\WebauthnLogin;

class LaravelWebauthnController extends Controller
{
    public function __construct(
        protected Webauthn $webauthn
    ) {}

    public function registerOptions(Request $request): JsonResponse
    {
        $options = $this->webauthn->attestationOptions();

        return response()->json($options);
    }

    public function register(Request $request): void
    {
        $validated = $request->validate([
            'challenge_id' => 'required|string',
            'passkey' => 'required|array',
            'name' => 'nullable|string|max:255',
        ]);

        $this->webauthn->completeAttestation($validated);
    }

    public function loginOptions(Request $request): JsonResponse
    {
        $options = $this->webauthn->assertionOptions();

        return response()->json($options);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'challenge_id' => 'required|string',
            'passkey' => 'required|array',
        ]);

        $user = $this->webauthn->completeAssertion($validated);

        WebauthnLogin::dispatch($user);

        $handlerClass = config('webauthn.actions.handle_login');

        if ($handlerClass) {
            /** @var HandleLoginAction $handler */
            $handler = app($handlerClass);

            return response()->json($handler->execute($user));
        }

        return response()->json(['status' => 'success']);
    }
}
