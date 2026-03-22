# 🤝 Contributing to BOUNDLY

First off, **thank you** for taking the time to contribute! Every contribution — big or small — makes BOUNDLY better for everyone. ❤️

---

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
  - [Reporting Bugs](#reporting-bugs)
  - [Suggesting Features](#suggesting-features)
  - [Submitting Pull Requests](#submitting-pull-requests)
- [Development Setup](#development-setup)
- [Code Style Guidelines](#code-style-guidelines)
- [Versioning & Branching Strategy](#versioning--branching-strategy)
- [Commit Message Convention](#commit-message-convention)

---

## 🧭 Code of Conduct

This project adheres to a simple principle: **be respectful and constructive**.

- Be welcoming to newcomers.
- Provide helpful, specific feedback.
- Assume good intentions.
- No harassment, discrimination, or personal attacks.

Violations can be reported to the maintainers via GitHub Issues (marked as `conduct`).

---

## 🐛 Reporting Bugs

Before reporting, please:
1. **Search existing issues** to avoid duplicates.
2. Make sure you are on the **latest version**.

When creating a bug report, use the **Bug Report** issue template and include:
- Your PHP and Laravel versions
- Steps to reproduce the issue
- Expected vs actual behavior
- Relevant stack trace or error message

---

## ✨ Suggesting Features

We love ideas! To propose a new feature:
1. **Open a Discussion** first (not an Issue) to validate the idea with the community.
2. If the idea gets traction, open a **Feature Request** Issue using the template.
3. Make sure your proposal aligns with BOUNDLY's core philosophy: **Domain-first, Config-minimal**.

---

## 🔧 Submitting Pull Requests

### Before You Start
- Check if there's an open Issue for what you want to work on. If not, create one.
- **Comment on the Issue** to let maintainers know you're working on it, to avoid duplicated effort.

### The PR Process

1. **Fork** the repository.
2. **Create a branch** from `develop` (not `main`):
   ```bash
   git checkout develop
   git pull origin develop
   git checkout -b feature/your-feature-name
   ```
3. **Write your code** following the style guidelines below.
4. **Add or update tests** if applicable.
5. **Run the tests** to make sure everything passes:
   ```bash
   php artisan test
   ```
6. **Commit** using the convention below.
7. **Push** your branch and open a Pull Request targeting `develop`.
8. Fill out the PR template completely.

### PR Checklist
- [ ] My code follows the BOUNDLY code style.
- [ ] I have added/updated tests if applicable.
- [ ] All tests pass locally.
- [ ] I have updated the `CHANGELOG.md` under `[Unreleased]`.
- [ ] I have updated relevant documentation or PHPDoc comments.

---

## 🛠️ Development Setup

```bash
# 1. Clone your fork
git clone https://github.com/YOUR_USERNAME/BOUNDLY.git
cd BOUNDLY

# 2. Install PHP dependencies
composer install

# 3. Copy environment file
cp .env.example .env
php artisan key:generate

# 4. Configure your database in .env, then run the daemon
php artisan core:watch
```

---

## 🎨 Code Style Guidelines

- **Language**: All code, comments, and PHPDoc blocks must be written in **English**.
- **Standard**: Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style.
- **Formatter**: Use [Laravel Pint](https://github.com/laravel/pint) before submitting:
  ```bash
  ./vendor/bin/pint
  ```
- **DDD Principles**: Code in the `Domain` layer must have **zero dependencies** on Laravel or other external packages.
- **Attributes over Config**: Prefer PHP 8 Attributes over traditional configuration for domain-level behavior.

---

## 🌿 Versioning & Branching Strategy

BOUNDLY uses **SemVer** and a **GitFlow-inspired** branching model:

| Branch | Purpose |
|--------|---------|
| `main` | Stable, released versions only. Merged via releases. |
| `develop` | Integration branch. All PRs target here. |
| `feature/*` | New features. Branch from `develop`. |
| `fix/*` | Bug fixes. Branch from `develop` (or `main` for hotfixes). |
| `release/*` | Release preparation. Branch from `develop`. |

---

## 📝 Commit Message Convention

We use [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <short description>

[optional body]
[optional footer]
```

### Types

| Type | When to use |
|------|------------|
| `feat` | A new feature |
| `fix` | A bug fix |
| `docs` | Documentation changes only |
| `refactor` | Code change that's neither a fix nor a feature |
| `test` | Adding or modifying tests |
| `chore` | Build process, dependencies, tooling |

### Examples

```
feat(entity): add TenantAware attribute support
fix(watcher): replace shell hash with pure PHP implementation
docs(readme): update quick start guide
```

---

Thank you again for contributing to BOUNDLY! 🚀

**Made with ❤️ by [EpOpenLabs](https://github.com/EpOpenLabs)**
