# Laravel WebAuthn

[![Latest Version on Packagist](https://img.shields.io/packagist/v/omdasoft/laravel-webauthn.svg?style=flat-square)](https://packagist.org/packages/omdasoft/laravel-webauthn)
[![Total Downloads](https://img.shields.io/packagist/dt/omdasoft/laravel-webauthn.svg?style=flat-square)](https://packagist.org/packages/omdasoft/laravel-webauthn)

A Laravel package that provides a **backend API implementation** for WebAuthn (passkeys) authentication, designed for **API-first applications** with separate frontends.

Perfect for:
- Single Page Applications (SPAs)
- Mobile applications
- Headless Laravel setups

The package provides:

- **API-only WebAuthn endpoints** - Pure JSON API suitable for SPAs and mobile apps.
- **Action-based architecture** - Core logic is separated into dedicated Action classes for easy customization.
- **Configurable models** - Support for custom Passkey and User models.
- **Event-driven** - Dispatches `WebauthnLogin` upon successful authentication.
- **InteractWithPasskeys trait** - Easy integration with your User (Authenticatable) model.
- **Configurable API routes** - Customizable prefix and middleware support.

## Project status

This package is **in progress** and is **not ready for production use**.

- The API surface (routes, request methods, responses) may change.
- The current implementation focuses on wiring and integration; you should perform a full security review before using in a real system.

## Requirements

- PHP `^8.3`
- Laravel `10|11|12`

## Installation

Install the package via Composer:

```bash
composer require omdasoft/laravel-webauthn
```

Publish the config and migration:

```bash
php artisan vendor:publish --provider="Omdasoft\LaravelWebauthn\LaravelWebauthnServiceProvider"
```

Run migrations:

```bash
php artisan migrate
```

## Configuration

After publishing, you can configure the package in `config/webauthn.php`.

- **`relying_party.id`**
  - The relying party ID used for WebAuthn (usually the domain without protocol).
  - Set `WEBAUTHN_RELYING_PARTY_ID` in your `.env`.
  - Example: `example.com`

- **`models.passkey`**
  - The model class used for storing passkeys.
  - Default: `Omdasoft\LaravelWebauthn\Models\Passkey`

- **`models.authenticatable`**
  - Your application's user model class.
  - Default: `App\Models\User`

- **`actions.handle_login`**
  - The class that handles user login after successful WebAuthn assertion.
  - Default: `Omdasoft\LaravelWebauthn\Actions\Login\HandleSanctumLogin`

Example `.env`:

```env
WEBAUTHN_RELYING_PARTY_ID=example.com
WEBAUTHN_RELYING_PARTY_NAME="My Awesome App"
WEBAUTHN_ROUTE_PREFIX=api/webauthn
WEBAUTHN_STORAGE_DRIVER=cache
WEBAUTHN_CHALLENGE_TTL=3600
```

## Flexibility and Custom Auth

This package is designed to be flexible. You can use it with Sanctum (default), Session, JWT, or any other authentication system.

### Customizing Middleware

Update your `config/webauthn.php`:

```php
'middlewares' => [
    'register' => ['auth:sanctum'], // Protect registration
    'login' => [], // Usually public
],
```

### Customizing Login Logic

If you don't use Sanctum, create a class that implements `HandleLoginAction`:

```php
namespace App\Actions;

use Omdasoft\LaravelWebauthn\Contracts\HandleLoginAction;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class SessionLoginHandler implements HandleLoginAction
{
    public function execute(Authenticatable $user): array
    {
        Auth::login($user);
        return ['status' => 'success'];
    }
}
```

Then register it in `config/webauthn.php`:

```php
'actions' => [
    'handle_login' => \App\Actions\SessionLoginHandler::class,
],
```

### Manual Route Registration

Register routes in your `routes/api.php` or `routes/web.php`:

```php
Route::webauthn();
```

Or with a custom prefix:

```php
Route::webauthn('auth/passkeys'); 
```

### Usage with Inertia or Web Sessions

If you are building an Inertia.js application or a standard Blade app, you usually want the routes to be in the `web` middleware group to handle cookies and CSRF.

1.  **Register routes manually** in `routes/web.php`:
    ```php
    // This will use the 'web' middleware group by default
    Route::middleware(['web'])->group(function () {
        Route::webauthn('auth/passkeys'); 
    });
    ```

3.  **Configure for Sessions** in `config/webauthn.php`:
    ```php
    'middlewares' => [
        'register' => ['auth'], // Use standard web auth
        'login' => [],
    ],
    'actions' => [
        'handle_login' => \App\Actions\SessionLoginHandler::class, // Your custom handler
    ],
    ```

## Model setup

Add the `InteractWithPasskeys` trait and `HasPasskey` contract to your authenticatable user model:

```php
use Omdasoft\LaravelWebauthn\Contracts\HasPasskey;
use Omdasoft\LaravelWebauthn\Traits\InteractWithPasskeys;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements HasPasskey
{
    use InteractWithPasskeys;
}
```

This adds a `passkeys()` relationship backed by the `passkeys` table.

## API Routes

The package registers the following API routes under your configured prefix (default: `api/webauthn`):

### Registration (Attestation)

- `POST /api/webauthn/register/options` (requires `auth:sanctum`)
- `POST /api/webauthn/register` (requires `auth:sanctum`)

### Login (Assertion)

- `POST /api/webauthn/login/options`
- `POST /api/webauthn/login`

### Custom Routes

If you need full control over the routes, you can define them manually instead of using `Route::webauthn()`:

```php
Route::post('register/options', [\Omdasoft\LaravelWebauthn\Http\Controllers\LaravelWebauthnController::class, 'registerOptions'])->name('webauthn.register.options');
// ...
```

## API Endpoints

### `POST /api/webauthn/register/options`

Returns JSON with:

- `challenge_id`
- `passkey` (PublicKeyCredentialCreationOptions as array)

### `POST /api/webauthn/register`

Expects:

- `challenge_id` (string)
- `passkey` (array)

Completes attestation and stores the credential in the authenticated user's `passkeys()` relationship.

### `POST /api/webauthn/login/options`

Returns JSON with:

- `challenge_id`
- `passkey` (PublicKeyCredentialRequestOptions as array)

### `POST /api/webauthn/login`

Expects:

- `challenge_id` (string)
- `passkey` (array)

Returns JSON with:

- `token` (string) - If using `HandleSanctumLogin`.

### Events

The package dispatches the following events:

- `Omdasoft\LaravelWebauthn\Events\WebauthnLogin`: Dispatched after a user successfully logs in via passkey.

```php
use Omdasoft\LaravelWebauthn\Events\WebauthnLogin;

// In your EventServiceProvider or dedicated listener
public function handle(WebauthnLogin $event)
{
    // $event->user is the authenticated user
}
```

## Testing and quality

Run the test suite:

```bash
composer test
```

Run static analysis, formatting check, and tests (recommended for CI):

```bash
composer ci
```

## Security notes

WebAuthn is security-sensitive.

- Always serve your app over HTTPS.
- Ensure `WEBAUTHN_DOMAIN` matches your real origin.
- Review token issuing (`createToken`) and authentication middleware configuration.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
