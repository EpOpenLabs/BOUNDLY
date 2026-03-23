# 📋 Changelog

All notable changes to **BOUNDLY** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added
- Nothing yet in this section. Changes are staged here before each release.

---

## [0.3.0-alpha] - 2026-03-22

> 🔗 Deep Relationships, Eager Loading Optimization, and Auto-Syncing engine upgrade.

### Added
- **`#[ManyToMany]` Attribute**: Declarative definition of Many-To-Many relationships directly on domain entities.
- **Auto-Pivot Migrations**: `core:migrate` now automatically detects `#[ManyToMany]` relationships and generates the necessary pivot tables (e.g., `role_user`) along with composite foreign keys, without requiring any manual `Schema::create` code.
- **Eager Loading Optimization (N+1 Fix)**: Completely rewrote `DynamicRepository`'s relationship loading logic. `?include=...` now executes highly efficient `WHERE IN (...)` queries (Eager Loading) for all relationship types (`BelongsTo`, `HasMany`, `HasOne`, `ManyToMany`), fixing the N+1 problem even with deeply nested includes (e.g. `posts.comments.author`).
- **Auto-Syncing Relationships**: When writing data (`POST`, `PUT`, `PATCH`), the `DynamicRepository` will now automatically intercept arrays of IDs sent for `#[ManyToMany]` relationships (e.g. `{"roles": [1, 2]}`) and perform a transparent `sync()` operation on the pivot table.
- **Cascade Pivot Deletes**: When deleting an entity via the `DELETE` endpoint, BOUNDLY now automatically cleans up all associated pivot records to maintain database integrity.
- **Array Validation Support**: `EntityValidator` now automatically whitelists and validates `#[ManyToMany]` properties as nullable arrays of integers in incoming request payloads.

---

## [0.2.0-alpha] - 2026-03-22

> 🔐 Production-ready security, performance, and developer experience upgrade.

### Added
- **`#[Authorize]` Attribute**: Declarative role-based access control (RBAC) directly on entity classes. Supports `roles`, `methods`, and `guard` parameters. Repeatable — multiple rules per class. Compatible with Spatie Laravel Permission and simple `role` column patterns.
- **`ResourceAuthorize` Middleware**: Auto-wired into all generic API routes. Reads `#[Authorize]` at runtime — no manual middleware registration ever needed.
- **`EntityValidator`**: Automatic payload validation and whitelist sanitization on all `POST`, `PUT`, and `PATCH` requests. Validates type, max length, and nullable constraints derived from `#[Column]` attributes. `PATCH` runs in partial mode (only validates fields present in the request).
- **`core:cache` Command**: Generates a static PHP metadata cache (`bootstrap/cache/boundly.php`) for zero-overhead production boot. Supports `--clear` flag.
- **Cache Auto-Switch**: `FrameworkCoreServiceProvider` automatically uses the static cache in production and falls back to live scanning in local environments.
- **`hydrateFromCache()` on Registries**: `EntityRegistry` and `ActionRegistry` can now be hydrated from a flat array — enabling the static cache without reflection.
- **`core:docs` Command**: Auto-generates a full **OpenAPI 3.0 (Swagger)** JSON specification (`storage/app/openapi.json`) from entity and action metadata. No documentation effort required from the programmer.
- **Cursor-Based Pagination**: `DynamicRepository::cursorPaginate()` provides efficient pagination for large datasets. Activated automatically when `?cursor=` is present in the request.
- **Nested Eager Loading (dot-notation)**: `?include=posts.comments.author` now loads arbitrarily nested relationships recursively.
- **Extended Filter Operators**: New `_gte`, `_lte`, `_null` suffixes added. OR filter groups via `?or[field_like]=value` syntax.
- **Sorting Support**: `?sort=field&direction=asc|desc` applied at the repository level with column whitelisting.
- **`getAllActions()` on ActionRegistry**: Exposes the full action map for serialization (used by cache and doc commands).

### Changed
- **`core:migrate` — Migration History**: Tracks applied changes in a `boundly_migrations` table using MD5 fingerprinting of each entity's schema config. Unchanged entities are skipped entirely (idempotent runs).
- **`core:migrate` — Dry Run**: `--dry-run` flag now previews all DDL changes without executing anything. Replaces the old `--force` flag.
- **`core:migrate` — Non-Destructive by Default**: The command never drops columns or tables automatically.
- **`GenericApiController`**: Refactored to use `EntityValidator` on write operations, support `PATCH` as a distinct partial-update method, and auto-switch between cursor and offset pagination.
- **`DynamicRepository`**: Full internal rewrite preserving the same public API. Query building extracted into `applyFilter()`, relation loading extracted into `loadSingleRelation()` with recursion support.
- **`FrameworkCoreServiceProvider`**: Registers `EntityValidator` singleton, wires `ResourceAuthorize` into generic routes, registers `core:cache` and `core:docs` commands.
- **`config/boundly.php`**: Extended with `disable_cache`, `auth.default_guard`, and `pagination` sections. All values support `.env` overrides.
- **`lang/en/messages.php`**: Added `unauthenticated` and `unauthorized` translation keys.

### Architecture
- Security is declarative: `#[Authorize]` on the entity = the route is protected. Zero infrastructure configuration.
- Performance is opt-in: run `core:cache` before deploy = zero reflection at runtime.
- Documentation is automatic: run `core:docs` = full OpenAPI spec ready for Swagger UI or Postman.

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

[Unreleased]: https://github.com/EpOpenLabs/BOUNDLY/compare/v0.3.0-alpha...HEAD
[0.3.0-alpha]: https://github.com/EpOpenLabs/BOUNDLY/compare/v0.2.0-alpha...v0.3.0-alpha
[0.2.0-alpha]: https://github.com/EpOpenLabs/BOUNDLY/compare/v0.1.0-alpha...v0.2.0-alpha
[0.1.0-alpha]: https://github.com/EpOpenLabs/BOUNDLY/releases/tag/v0.1.0-alpha
