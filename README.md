<div align="center">

# 🪄 BOUNDLY

**The Meta-Driven DDD PHP Framework**

> *Build enterprise-grade APIs by defining only your Domain.*

[![License: MIT](https://img.shields.io/badge/License-MIT-violet.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13%2B-red.svg)](https://laravel.com/)
[![Version](https://img.shields.io/badge/version-v0.5.2--alpha-blue.svg)](https://github.com/EpOpenLabs/BOUNDLY/releases)

</div>

---

## 🧠 What is BOUNDLY?

**BOUNDLY** is a high-performance PHP framework inspired by **Domain-Driven Design (DDD)** and **Convention-over-Configuration**. It eliminates boilerplate code by using PHP 8+ **Attributes** as the single source of truth for your infrastructure.

You define your **Domain**. BOUNDLY handles the rest.

- ✅ **Zero manual migrations** — Your DB schema evolves with your code.
- ✅ **Zero route files** — Your endpoints are declared on your Use Cases.
- ✅ **Zero boilerplate repositories** — Generic CRUD is handled automatically.
- ✅ **Zero validation rules** — Payloads are validated against your entity attributes.
- ✅ **Enterprise features in one line** — Auditing, Soft Delete, Multi-Tenancy, Authorization.
- ✅ **Production-ready** — Static metadata cache, migration history, and OpenAPI docs included.

---

## 🏛️ Architecture: Pure DDD

BOUNDLY enforces a clean, screaming architecture where the folder structure tells you *what the system does*, not *what framework it uses*.

```
/
├── Application/          # Use Cases (Actions, DTOs)
│   └── Users/
│       ├── Actions/      # #[Action] defines the API endpoint
│       └── DTOs/
├── Domain/               # Pure Business Logic
│   └── Users/
│       ├── Entities/     # #[Entity] defines the DB table
│       ├── Events/
│       └── ValueObjects/
├── Infrastructure/       # Technical Adapters
│   ├── FrameworkCore/    # The BOUNDLY Engine (CLI, Repos, Attributes)
│   └── LaravelEngine/   # Laravel internals (config, storage, routes...)
├── bootstrap/            # Framework ignition point
├── config/               # Your project config (boundly.php)
├── public/               # Web entry point
└── artisan               # CLI entry point
```

---

## 🔥 Key Features

### 🧬 1. Magic Evolution (Auto DB Sync)
Forget `php artisan migrate`. Define your entity, run the daemon, and your database evolves automatically.

```php
#[Entity(table: 'users', resource: 'users')]
#[Auditable]
#[SoftDelete]
class User extends AggregateRoot
{
    #[Id]
    private int $id;

    #[Column(type: 'string', length: 150)]
    private string $name;

    #[Column(type: 'string', nullable: true, default: '555-0000')]
    private string $phone;
}
```

Run `php artisan core:watch` and your `/api/users` endpoint is live. ✨

---

### 🔐 2. Declarative Authorization
Protect any resource with a single attribute — no route middleware, no guards to register manually:

```php
// Only admins and managers can access this resource
#[Entity(table: 'salaries', resource: 'salaries')]
#[Authorize(roles: ['admin', 'manager'])]
class Salary extends AggregateRoot { ... }

// Public reads, auth required for writes
#[Entity(table: 'articles', resource: 'articles')]
#[Authorize(roles: [], methods: ['POST', 'PUT', 'PATCH', 'DELETE'])]
class Article extends AggregateRoot { ... }
```

---

### 🛡️ 3. Declarative Behavioral Traits
Add enterprise features with a single attribute:

| Attribute | What it does |
|-----------|-------------|
| `#[Auditable]` | Injects `created_by` / `updated_by` — auto-populated from the request |
| `#[SoftDelete]` | Handles `deleted_at` and filters queries silently |
| `#[TenantAware]` | Multi-tenant data isolation at the repository level |
| `#[Authorize]` | Role-based access control — reads PHP Attributes, not config files |

---

### 🔎 4. Pro Query Engine
Complex filtering, nested eager loading, and dual pagination out-of-the-box:

```bash
# Partial search
GET /api/users?name_like=boundly

# Range filtering with new operators
GET /api/users?age_gte=18&score_lte=100

# NOT and IN operators
GET /api/products?category_not=5&id_in=1,2,3

# OR filter groups
GET /api/users?or[name_like]=john&or[email_like]=john

# Nested eager loading (dot-notation)
GET /api/users?include=posts.comments.author

# Cursor pagination (efficient for large datasets)
GET /api/events?cursor=250&per_page=20

# Sorting & standard pagination
GET /api/users?sort=name&direction=asc&per_page=20
```

---

### 🌍 5. Full Internationalization (i18n)
Console output speaks your language:

```bash
php artisan core:watch --lang=es
```

---

## 🚀 Quick Start

### 1. Clone the repository

> 📦 Packagist support (`composer create-project`) is planned for `v1.0.0`. For now, install via Git:

```bash
git clone https://github.com/EpOpenLabs/BOUNDLY.git my-project
cd my-project
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Configure your database

```dotenv
# .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=my_project
DB_USERNAME=root
DB_PASSWORD=secret
```

### 3. Define your Entity

```php
// Domain/Users/Entities/User.php
#[Entity(table: 'users', resource: 'users')]
#[Auditable]
class User extends AggregateRoot
{
    #[Id]
    private int $id;

    #[Column(type: 'string', length: 150)]
    private string $name;

    #[Column(type: 'string', unique: true)]
    private string $email;
}
```

### 4. Start the daemon

```bash
php artisan core:watch
```

🎉 Your API is live at `http://localhost:8000/api/users`.

### 5. Before deploying to production

```bash
# Preview schema changes
php artisan core:migrate --dry-run

# Apply schema
php artisan core:migrate

# Cache metadata for zero-overhead boot
php artisan core:cache

# Generate OpenAPI documentation
php artisan core:docs
```

---

## ⚙️ Configuration Reference

| Key | Default | Env Variable | Description |
|-----|---------|-------------|-------------|
| `locale` | `en` | `BOUNDLY_LOCALE` | Default language for CLI output |
| `api_prefix` | `api` | `BOUNDLY_API_PREFIX` | URL prefix for all auto-generated routes |
| `disable_cache` | `true` in local | `BOUNDLY_DISABLE_CACHE` | Forces scan mode; set `false` in production |
| `auth.default_guard` | `sanctum` | `BOUNDLY_AUTH_GUARD` | Guard used by `#[Authorize]` middleware |
| `pagination.default_per_page` | `15` | `BOUNDLY_PER_PAGE` | Default page size |
| `pagination.max_per_page` | `100` | `BOUNDLY_MAX_PER_PAGE` | Hard cap on page size |
| `paths.domain` | `Domain/` | — | Where BOUNDLY scans for `#[Entity]` |
| `paths.application` | `Application/` | — | Where BOUNDLY scans for `#[Action]` |

---

## 🛠️ Requirements

- PHP **8.2+**
- Laravel **13+** (core dependency, hidden in `Infrastructure/LaravelEngine`)
- MySQL / PostgreSQL / SQLite

---

## 🤝 Contributing

BOUNDLY is an open source project and **contributions are welcome!**

Please read [`CONTRIBUTING.md`](CONTRIBUTING.md) for details on:
- How to report bugs
- How to propose new features
- The Pull Request process
- Code style guidelines

---

## 📋 Versioning

BOUNDLY follows [Semantic Versioning (SemVer)](https://semver.org/):

- `MAJOR` → Breaking changes in the API or architecture
- `MINOR` → New backward-compatible features
- `PATCH` → Bug fixes

See the full history in [`CHANGELOG.md`](CHANGELOG.md).

---

## 📜 License

BOUNDLY is open-sourced software licensed under the [MIT License](LICENSE).

---

## ☕ Support the Project

If BOUNDLY has been useful to you or you like what we're building, you can support us to keep creating and maintaining open source software:

<div>
  <a href="https://www.buymeacoffee.com/epolabs" target="_blank">
    <img src="https://cdn.buymeacoffee.com/buttons/v2/default-violet.png"
         alt="Buy us a coffee"
         style="height: 55px; width: 200px;">
  </a>
</div>

A coffee = more time for open source code ❤️

---

<div align="center">

⭐ **If you like BOUNDLY, give it a star on GitHub!** ⭐

[GitHub](https://github.com/EpOpenLabs/BOUNDLY) · [Issues](https://github.com/EpOpenLabs/BOUNDLY/issues) · [Discussions](https://github.com/EpOpenLabs/BOUNDLY/discussions)

**Made with ❤️ by [EpOpenLabs](https://github.com/EpOpenLabs)**

</div>
