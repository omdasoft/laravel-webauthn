# Laravel WebAuthn

[![Latest Version on Packagist](https://img.shields.io/packagist/v/omdasoft/laravel-webauthn.svg?style=flat-square)](https://packagist.org/packages/omdasoft/laravel-webauthn)
[![Total Downloads](https://img.shields.io/packagist/dt/omdasoft/laravel-webauthn.svg?style=flat-square)](https://packagist.org/packages/omdasoft/laravel-webauthn)

A Laravel package that adds a simple server-side WebAuthn (passkeys) flow for:

- Attestation (registration)
- Assertion (login)

The package provides:

- A service (`Omdasoft\LaravelWebauthn\LaravelWebauthn`) implementing the `Omdasoft\LaravelWebauthn\Contracts\Webauthn` contract
- A `HasWebAuthn` trait and a `passkeys` table migration
- Routes + controller endpoints for requesting options and completing registration/login

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
php artisan vendor:publish --tag="laravel-webauthn-config"
php artisan vendor:publish --tag="laravel-webauthn-migrations"
```

Run migrations:

```bash
php artisan migrate
```

## Configuration

After publishing, you can configure the package in `config/webauthn.php`.

- **`domain`**
  - The relying party origin / domain used by the WebAuthn ceremony.
  - Set `WEBAUTHN_DOMAIN` in your `.env`.
  - Example: `https://example.com`

- **`storage.driver`**
  - Where challenges are stored.
  - Supported values:
    - `cache`
    - `session`

- **`storage.ttl`**
  - Challenge time-to-live in seconds.

Example `.env`:

```env
WEBAUTHN_DOMAIN=https://example.com
WEBAUTHN_STORAGE_DRIVER=cache
WEBAUTHN_CHALLENGE_TTL=3600
```

## Model setup

Add the `HasWebAuthn` trait to your authenticatable user model:

```php
use Omdasoft\LaravelWebauthn\Traits\HasWebAuthn;

class User extends Authenticatable
{
    use HasWebAuthn;
}
```

This adds a `passkeys()` relationship backed by the `passkeys` table.

## Routes

The package registers the following routes under the `webauthn` prefix:

### Attestation

- `GET /webauthn/register/options` (requires `auth:sanctum`)
- `GET /webauthn/register` (requires `auth:sanctum`)

### Assertion

- `GET /webauthn/login/options`
- `GET /webauthn/login`

Note: using `GET` for state-changing actions is not ideal. Consider changing these to `POST` in a future release.

## Endpoints overview

### `GET /webauthn/register/options`

Returns JSON with:

- `challenge_id`
- `passkey` (PublicKeyCredentialCreationOptions as array)

### `GET /webauthn/register`

Expects:

- `challenge_id` (string)
- `passkey` (array)

Completes attestation and stores the credential in the authenticated user's `passkeys()` relationship.

### `GET /webauthn/login/options`

Returns JSON with:

- `challenge_id`
- `passkey` (PublicKeyCredentialRequestOptions as array)

### `GET /webauthn/login`

Expects:

- `challenge_id` (string)
- `passkey` (array)

Returns JSON with:

- `token` (string)

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
