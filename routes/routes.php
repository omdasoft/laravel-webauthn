<?php 

use Illuminate\Support\Facades\Route;
use Omdasoft\LaravelWebauthn\Http\Controllers\LaravelWebauthnController;

Route::prefix('webauthn')->group(function() {
    //Attestation
    Route::middleware('auth:sanctum')->group(function() {
        Route::get('register/options', [LaravelWebauthnController::class, 'registerOptions'])->name('webauthn.register.options');
        Route::get('register', [LaravelWebauthnController::class, 'register'])->name('webauthn.register');
    });

    //Assertion
    Route::get('login/options', [LaravelWebauthnController::class, 'loginOptions'])->name('webauthn.login.options');
    Route::get('login', [LaravelWebauthnController::class, 'login'])->name('webauthn.login');
});