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
- Laravel `10|11|12|13`

## Installation

Install the package via Composer:

```bash
composer require omdasoft/laravel-webauthn
```

Publish the config, migration, and translations:

```bash
php artisan vendor:publish --provider="Omdasoft\LaravelWebauthn\LaravelWebauthnServiceProvider"
```

Or publish individually:

```bash
php artisan vendor:publish --tag="webauthn-config"
php artisan vendor:publish --tag="webauthn-migrations"
php artisan vendor:publish --tag="webauthn-translations"
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
  - The class that handles user login after successful WebAuthn assertion (login).
  - Built-in options:
    - `Omdasoft\LaravelWebauthn\Actions\Login\HandleSanctumLogin` (Default)
    - `Omdasoft\LaravelWebauthn\Actions\Login\HandleSessionLogin`
  - You can also create your own by implementing `HandleLoginAction`.

Example `.env`:

```env
WEBAUTHN_RELYING_PARTY_ID=example.com
WEBAUTHN_RELYING_PARTY_NAME="My Awesome App"
WEBAUTHN_ROUTE_PREFIX=api/webauthn
WEBAUTHN_STORAGE_DRIVER=cache
WEBAUTHN_CHALLENGE_TTL=3600
```

## Translations

The package includes translatable error messages. You can publish them to customize the text:

```bash
php artisan vendor:publish --tag="webauthn-translations"
```

The translations will be available in `resources/lang/vendor/webauthn/en/errors.php`.

## Flexibility and Custom Auth

This package is designed to be flexible. It works with Sanctum (default), Session, JWT, or any other authentication system.

### Customizing Middleware

Update your `config/webauthn.php`:

```php
'middlewares' => [
    'register' => ['auth:sanctum'], // Protect registration
    'login' => [], // Usually public
],
```

### Customizing Login Logic

If you want to use standard Laravel sessions instead of Sanctum tokens, update `config/webauthn.php`:

```php
'actions' => [
    'handle_login' => \Omdasoft\LaravelWebauthn\Actions\Login\HandleSessionLogin::class,
],
```

For completely custom logic (e.g., JWT), create a class that implements `HandleLoginAction`:

```php
namespace App\Actions;

use Omdasoft\LaravelWebauthn\Contracts\HandleLoginAction;
use Illuminate\Contracts\Auth\Authenticatable;

class MyCustomLoginHandler implements HandleLoginAction
{
    public function execute(Authenticatable $user): array
    {
        // Your custom logic here
        return ['status' => 'success', 'custom_field' => 'value'];
    }
}
```

## Model setup

Add the `InteractWithPasskeys` trait and `HasPasskey` contract to your user model:

```php
use Omdasoft\LaravelWebauthn\Contracts\HasPasskey;
use Omdasoft\LaravelWebauthn\Traits\InteractWithPasskeys;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements HasPasskey
{
    use InteractWithPasskeys;
}
```

### Customizing User Identification

If you want to change how the user's name or display name is sent to the authenticator (e.g., for the passkey creation prompt), you can override these methods in your `User` model:

```php
public function getPasskeyIdentifier(): string // Default: $this->id
{
    return (string) $this->uuid;
}

public function getPasskeyName(): string // Default: $this->email
{
    return $this->username;
}

public function getPasskeyDisplayName(): string // Default: $this->name
{
    return $this->full_name;
}
```

## API Routes and Endpoints

The package registers the following routes under your configured prefix (default: `api/webauthn`):

### Registration (Attestation)
- `POST /register/options` - Get creation options.
- `POST /register` - Submit attestation response. Accepts an optional `name` field to label the passkey.

### Login (Assertion)
- `POST /login/options` - Get assertion options.
- `POST /login` - Submit assertion response.

## Error Handling

The package throws specific exceptions when something goes wrong. These exceptions return translatable messages:

- `ChallengeMissingException`: The challenge ID was not provided.
- `ChallengeNotFoundException`: The challenge has expired or does not exist.
- `UserUnauthenticatedException`: Registration attempted without being logged in.
- `PasskeyNotFoundException`: The passkey requested for login was not found.
- `UserNotFoundException`: The user associated with the passkey was not found.

## Events

The package dispatches:
- `Omdasoft\LaravelWebauthn\Events\WebauthnLogin`: Dispatched after a successful login.

```php
public function handle(WebauthnLogin $event)
{
    // $event->user is the logged-in user
}
```

## Frontend Implementation

Since this is an API-first package, you need a frontend library to interact with the browser's WebAuthn API. We recommend using [@simplewebauthn/browser](https://simplewebauthn.io/docs/packages/browser).

### 1. Registering a Passkey

```javascript
import { startRegistration } from '@simplewebauthn/browser';

const registerPasskey = async () => {
    // 1. Get registration options from your Laravel API
    const resp = await axios.post('/api/webauthn/register/options');
    const { challenge_id, passkey: options } = resp.data;

    // 2. Start the browser registration process
    const attestationResponse = await startRegistration(options);

    // 3. Send the response back to your API to complete registration
    await axios.post('/api/webauthn/register', {
        challenge_id,
        passkey: attestationResponse,
        name: 'My MacBook Pro' // Optional name for the passkey
    });
};
```

### 2. Logging in with a Passkey

```javascript
import { startAuthentication } from '@simplewebauthn/browser';

const loginWithPasskey = async () => {
    try {
        // 1. Get authentication options
        const resp = await axios.post('/api/webauthn/login/options');
        const { challenge_id, passkey: options } = resp.data;

        // 2. Pass options to the browser API
        const assertionResponse = await startAuthentication(options);

        // 3. Complete authentication
        const loginResp = await axios.post('/api/webauthn/login', {
            challenge_id,
            passkey: assertionResponse
        });

        // 4. Handle success (e.g., redirect or update state)
        window.location.href = '/dashboard';
    } catch (error) {
        console.error('Passkey authentication failed', error);
    }
};
```

## Testing and quality

```bash
composer ci
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

