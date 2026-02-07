<?php

use Illuminate\Support\Facades\Route;
use Omdasoft\LaravelWebauthn\Http\Controllers\LaravelWebauthnController;

Route::prefix(config('webauthn.route_prefix'))->middleware('api')->group(function () {
    // Attestation (Registration)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('register/options', [LaravelWebauthnController::class, 'registerOptions'])->name('webauthn.register.options');
        Route::post('register', [LaravelWebauthnController::class, 'register'])->name('webauthn.register');
    });

    // Assertion (Login)
    Route::post('login/options', [LaravelWebauthnController::class, 'loginOptions'])->name('webauthn.login.options');
    Route::post('login', [LaravelWebauthnController::class, 'login'])->name('webauthn.login');
});
