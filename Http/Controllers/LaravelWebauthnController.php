<?php

namespace Omdasoft\LaravelWebauthn\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Omdasoft\LaravelWebauthn\Contracts\Webauthn;
use Omdasoft\LaravelWebauthn\Http\Requests\RegisterRequest;
use Orchestra\Workbench\Http\Requests\Auth\LoginRequest;

class LaravelWebauthnController extends Controller
{
    public function registerOptions(): JsonResponse
    {
        $options = Webauthn::attestationOptions();

        return response()->json($options);
    }

    public function register(RegisterRequest $request): void
    {
        Webauthn::completeAttestation($request->validated());
    }

    public function loginOptions(): JsonResponse
    {
        $options = Webauthn::assertionOptions();

        return response()->json($options);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $token = Webauthn::completeAssertion($request->validated());

        return response()->json(['token' => $token]);
    }
}
