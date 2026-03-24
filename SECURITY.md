# Security Policy

## Supported Versions

BOUNDLY is currently in **active development (Alpha)**. We recommend always using the latest version from the `main` branch.

| Version | Supported          |
| ------- | ------------------ |
| v0.7.x-alpha | :white_check_mark: |
| v0.8.x-alpha | :white_check_mark: |
| < v0.7.0-alpha | :x:                |

## Security Features

BOUNDLY includes the following security features out of the box:

- **Input Sanitization**: Whitelist approach - only declared columns are accepted
- **SQL Injection Prevention**: Column whitelist validation in DynamicRepository
- **Rate Limiting**: Built-in `#[RateLimit]` attribute with per-IP/user tracking
- **Authorization**: `#[Authorize]` attribute with role-based access control
- **Sensitive Data Protection**: `#[Hidden]`, `#[Encrypted]`, `#[Hashed]` attributes
- **Soft Delete**: Logical deletion prevents permanent data loss

## Reporting a Vulnerability

If you discover a security vulnerability within BOUNDLY, please **do not open a public issue**. Instead, use the **[GitHub Private Vulnerability Reporting](https://github.com/EpOpenLabs/BOUNDLY/security/advisories/new)** feature in this repository.

1. Go to the "Security" tab of the repository.
2. Select "Reporting a vulnerability".
3. Provide a detailed description and reproduction steps.

We will acknowledge your report within **48 hours** and coordinate a fix.

Thank you for helping us keep BOUNDLY secure! ❤️
