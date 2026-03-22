# 📋 Changelog

All notable changes to **BOUNDLY** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added
- Nothing yet in this section. Changes are staged here before each release.

---

## [0.1.0-alpha] - 2026-03-22

> 🚀 First public alpha release of BOUNDLY.

### Added
- **Pure DDD Architecture**: Enforced folder structure with `Domain/`, `Application/`, and `Infrastructure/` layers.
- **`Infrastructure/LaravelEngine/`**: All Laravel internals moved to a "basement" folder, keeping the root strictly DDD-focused.
- **`#[Entity]` Attribute**: Declare a database table and API resource via a PHP attribute. Supports `table`, `resource`, `primaryKey`.
- **`#[Column]` Attribute**: Declare a database column with `type`, `length`, `nullable`, `default`, `unique`.
- **`#[Id]` Attribute**: Marks the primary key column.
- **`#[Action]` Attribute**: Declare a Use Case as an API endpoint with `resource`, `method`, `middleware`.
- **`#[Auditable]` Trait**: Automatically injects and populates `created_by` / `updated_by` columns.
- **`#[SoftDelete]` Trait**: Handles `deleted_at` and transparent query filtering.
- **`#[TenantAware]` Trait**: Multi-tenant data isolation at the repository level.
- **Magic Evolution (`core:migrate`)**: Compares entity attributes against the live DB schema and applies only necessary DDL changes (add/modify/remove columns).
- **Development Daemon (`core:watch`)**: File-system watcher using pure PHP (`RecursiveDirectoryIterator`) that triggers `core:migrate` on domain file changes. Cross-platform (Windows/Linux/macOS).
- **Generic API Controller**: Auto-generates CRUD endpoints (`GET`, `POST`, `PUT`, `DELETE`) for all registered entities.
- **Pro Query Engine**: URL-based filtering with `_like`, `_gt`, `_lt`, `_eq` operators, `include` for eager loading, `sort`/`order`, and `per_page` pagination.
- **Entity & Action Registries**: Singleton services that index all discovered entities and actions at boot time.
- **Dynamic Repository**: A generic repository backed by Laravel's Query Builder, respect all behavioral traits automatically.
- **Internationalization (i18n)**: Console output supports `en` and `es` locales. Configured via `config/boundly.php` or overridden per-command with `--lang`.
- **`config/boundly.php`**: Centralized configuration file for locale, API prefix, and discovery paths.

### Architecture
- Laravel acts as a hidden implementation detail inside `Infrastructure/LaravelEngine/`.
- Tests co-located with their respective domain/application layers (not in a root `tests/` folder).
- Routes generated dynamically from `#[Action]` attributes — no `routes/*.php` files needed.

---

[Unreleased]: https://github.com/EpOpenLabs/BOUNDLY/compare/v0.1.0-alpha...HEAD
[0.1.0-alpha]: https://github.com/EpOpenLabs/BOUNDLY/releases/tag/v0.1.0-alpha
