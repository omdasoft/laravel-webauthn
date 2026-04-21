# Contributing to laravel-webauthn

Thank you for considering a contribution! Before you get started, please read the following guidelines.

---

## Support Questions

Please **do not** use GitHub Issues for support questions. Instead, use:

- [GitHub Discussions](https://github.com/omdasoft/laravel-webauthn/discussions) — for general help, usage questions, and ideas.

Issues are reserved for confirmed bug reports and feature requests.

---

## Reporting Bugs

Use the **Bug Report** issue template. Please include:

- The package version (`composer show omdasoft/laravel-webauthn`)
- PHP and Laravel versions
- A **minimal reproduction** — ideally a failing test or a step-by-step sequence

Incomplete bug reports may be closed without action.

---

## Security Vulnerabilities

**Please do NOT open a public issue for security vulnerabilities.**

Follow the process described in [SECURITY.md](SECURITY.md).

---

## Feature Requests

Use the **Feature Request** issue template and explain:

1. The problem you are trying to solve
2. Your proposed solution
3. Alternatives you have considered

Features that fall outside the package's API-first, action-based scope may be declined.

---

## Pull Requests

We welcome PRs for bug fixes, improvements, and new features. Here is the expected workflow:

### 1. Fork and branch

```bash
git clone https://github.com/your-username/laravel-webauthn.git
cd laravel-webauthn
git checkout -b fix/your-bug-description
# or
git checkout -b feature/your-feature-name
```

Use the following branch prefixes:

| Prefix | Use for |
|---|---|
| `feature/` | New features |
| `fix/` | Bug fixes |
| `chore/` | Maintenance, dependency updates |
| `docs/` | Documentation only |

### 2. Install dependencies

```bash
composer install
```

### 3. Make your changes

- Keep changes focused — one concern per PR.
- Add or update tests to cover your change.
- Follow the existing architecture: new actions must implement the correct contract, new exceptions must extend the base exception hierarchy.

### 4. Run the full CI suite locally

```bash
composer ci
```

This runs static analysis, code style checks, and the test suite. All checks must pass.

### 5. Update the changelog

Add an entry under `## [Unreleased]` in `CHANGELOG.md`:

```markdown
## [Unreleased]
### Added
- Your new feature description
```

### 6. Open the PR

- Target the `main` branch.
- Fill in the pull request template completely.
- Link the related issue with `Closes #123`.

---

## Code Style

We use [Laravel Pint](https://github.com/laravel/pint) (PSR-12 preset). Run the formatter before pushing:

```bash
composer format
```

To check without modifying files:

```bash
composer lint
```

---

## Running Tests

```bash
composer test
```

Tests are written using **PHPUnit** (class-based, extending `TestCase`). Follow the existing style — use `#[Test]` attributes and `$this->assert*()` methods:

```php
use PHPUnit\Framework\Attributes\Test;

class YourTest extends TestCase
{
    #[Test]
    public function it_does_something(): void
    {
        $this->assertTrue(true);
    }
}
```

---

## Static Analysis

```bash
composer analyse
```

We run PHPStan at a strict level. Do not add `@phpstan-ignore` annotations without a clear justification in a code comment.

---

## Commit Messages

Use [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: add JWT login handler action
fix: resolve challenge TTL expiry on cache driver
chore: update web-auth/webauthn-lib to 5.3
docs: add frontend integration examples
```

---

## Review Process

- All PRs require at least one approval.
- The maintainer will review within **7 days** of submission.
- If a PR has no activity for **30 days**, it may be closed.

When reviewing PRs, the maintainer checks that:

- New features have at least one feature test
- Bug fixes include a regression test
- Tests follow the existing PHPUnit class-based style (`extends TestCase`, `#[Test]` attribute, `$this->assert*()`)
- PHPStan passes with no new `@phpstan-ignore` annotations
- `CHANGELOG.md` has been updated

Thank you for helping make this package better! 🙌
