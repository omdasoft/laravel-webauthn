<?php

namespace Omdasoft\LaravelWebauthn\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Omdasoft\LaravelWebauthn\Actions\Assertion\AuthenticatePasskey;
use Omdasoft\LaravelWebauthn\Actions\Assertion\GenerateLoginOptions;
use Omdasoft\LaravelWebauthn\Actions\Attestation\GenerateRegistrationOptions;
use Omdasoft\LaravelWebauthn\Actions\Attestation\RegisterPasskey;
use Omdasoft\LaravelWebauthn\Contracts\HandleLoginAction;
use Omdasoft\LaravelWebauthn\Contracts\HasPasskey;
use Omdasoft\LaravelWebauthn\Events\WebauthnLogin;

class LaravelWebauthnController extends Controller
{
    public function registerOptions(Request $request, GenerateRegistrationOptions $action): JsonResponse
    {
        /** @var HasPasskey $user */
        $user = $request->user();

        return response()->json($action->execute($user));
    }

    public function register(Request $request, RegisterPasskey $action): void
    {
        $validated = $request->validate([
            'challenge_id' => 'required|string',
            'passkey' => 'required|array',
            'name' => 'nullable|string|max:255',
        ]);

        /** @var HasPasskey $user */
        $user = $request->user();

        $action->execute($validated, $user);
    }

    public function loginOptions(GenerateLoginOptions $action): JsonResponse
    {
        return response()->json($action->execute());
    }

    public function login(Request $request, AuthenticatePasskey $action): JsonResponse
    {
        $validated = $request->validate([
            'challenge_id' => 'required|string',
            'passkey' => 'required|array',
        ]);

        $user = $action->execute($validated);

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
