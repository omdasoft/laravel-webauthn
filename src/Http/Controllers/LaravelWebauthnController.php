<?php

namespace Omdasoft\LaravelWebauthn\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Omdasoft\LaravelWebauthn\Contracts\Webauthn;
use Omdasoft\LaravelWebauthn\Http\Requests\RegisterRequest;

class LaravelWebauthnController extends Controller
{
    public function __construct(
        protected Webauthn $webauthn
    ) {}

    public function registerOptions(Request $request): JsonResponse
    {
        $options = $this->webauthn->attestationOptions($request->user());

        return response()->json($options);
    }

    public function register(RegisterRequest $request): void
    {
        $this->webauthn->completeAttestation($request->user(), $request->validated());
    }

    public function loginOptions(): JsonResponse
    {
        $options = $this->webauthn->assertionOptions();

        return response()->json($options);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'challenge_id' => 'required|string',
            'passkey' => 'required|array',
        ]);

        $result = $this->webauthn->completeAssertion($data);

        return response()->json($result);
    }
}
