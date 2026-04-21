# Changelog

All notable changes to `laravel-webauthn` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-04-21

### Added
- Action-based architecture for WebAuthn registration and authentication
- Support for Laravel 10, 11, 12, and 13
- Support for PHP 8.3+
- `InteractWithPasskeys` trait and `HasPasskey` contract for User models
- `HandleSanctumLogin` action — default login handler using Laravel Sanctum tokens
- `HandleSessionLogin` action — session-based login for stateful apps (Inertia, Blade)
- Custom `HandleLoginAction` contract for fully custom authentication (JWT, etc.)
- Cache and Session challenge storage drivers, configurable via `WEBAUTHN_STORAGE_DRIVER`
- Full exception hierarchy with translatable error messages
  - `ChallengeMissingException`
  - `ChallengeNotFoundException`
  - `UserUnauthenticatedException`
  - `PasskeyNotFoundException`
  - `UserNotFoundException`
  - `InvalidResponseTypeException`
- `WebauthnLogin` event dispatched after successful authentication
- Configurable relying party ID, name, route prefix, and middleware
- Publishable config, migrations, and translations (`webauthn-config`, `webauthn-migrations`, `webauthn-translations`)
- PHPStan static analysis at strict level
- Full PHPUnit test suite (class-based, using `#[Test]` attributes)

[Unreleased]: https://github.com/omdasoft/laravel-webauthn/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/omdasoft/laravel-webauthn/releases/tag/v1.0.0
