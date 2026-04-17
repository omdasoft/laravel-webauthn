<?php

use Omdasoft\LaravelWebauthn\Actions\Login\HandleSanctumLogin;
use Omdasoft\LaravelWebauthn\Models\Passkey;

// config for Omdasoft/LaravelWebauthn
return [
    'route_prefix' => env('WEBAUTHN_ROUTE_PREFIX', 'webauthn'),

    'middlewares' => [
        /*
        * to register a passkey, user must be authenticated the default middleware is auth:sanctum
        * but you can override it by specifying your own middleware eg auth for web
        * to login with a passkey, no authentication is required
        */
        'register' => ['auth:sanctum'],
        'login' => [],
    ],

    /* The storage configuration for challenges.
    *  You can choose between 'cache' or 'session', or implement your own by creating a class that implements the ChallengeStorage contract.
    */
    'storage' => [
        'driver' => env('WEBAUTHN_STORAGE_DRIVER', 'cache'),
        'ttl' => env('WEBAUTHN_CHALLENGE_TTL', 3600),
    ],

    /*
     * These classes are responsible for performing core tasks regarding WebAuthn.
     * You can customize them by creating a class that implements the contract,
     * and by specifying your custom class name here.
     *
     * Note: A WebauthnLogin event is also dispatched upon successful authentication,
     * which you can listen to instead of using an action.
     */
    'actions' => [
        // Options: HandleSanctumLogin::class (default), HandleSessionLogin::class, or null
        'handle_login' => HandleSanctumLogin::class,
    ],

    /*
    * The models used by the package.
    * You can override this by specifying your own models
    */
    'models' => [
        'passkey' => Passkey::class,
        'authenticatable' => env('AUTH_MODEL', 'App\Models\User'),
    ],

    /*
     * These properties will be used to generate the passkey.
     * The relying party ID should be the domain of your application.
     * If the frontend and backend are on the same domain,
     * you can leave it as the default which will parse the domain from the app URL.
     * otherwise, you should specify it in the .env file.
     * The name is a user-friendly name for your application.
     */
    'relying_party' => [
        'name' => env('WEBAUTHN_RELYING_PARTY_NAME', config('app.name')),
        'id' => env('WEBAUTHN_RELYING_PARTY_ID', config('app.url')),
        'icon' => null,
    ],
];
