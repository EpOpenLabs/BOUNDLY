<div align="center">

# 🪄 BOUNDLY

**The Meta-Driven DDD PHP Framework**

> *Build enterprise-grade APIs by defining only your Domain.*

[![License: MIT](https://img.shields.io/badge/License-MIT-violet.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13%2B-red.svg)](https://laravel.com/)
[![Version](https://img.shields.io/badge/version-0.1.0--alpha-orange.svg)](https://github.com/EpOpenLabs/BOUNDLY/releases)

</div>

---

## 🧠 What is BOUNDLY?

**BOUNDLY** is a high-performance PHP framework inspired by **Domain-Driven Design (DDD)** and **Convention-over-Configuration**. It eliminates boilerplate code by using PHP 8+ **Attributes** as the single source of truth for your infrastructure.

You define your **Domain**. BOUNDLY handles the rest.

- ✅ **Zero manual migrations** — Your DB schema evolves with your code.
- ✅ **Zero route files** — Your endpoints are declared on your Use Cases.
- ✅ **Zero boilerplate repositories** — Generic CRUD is handled automatically.
- ✅ **Enterprise features in one line** — Auditing, Soft Delete, Multi-Tenancy.

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

### 🛡️ 2. Declarative Behavioral Traits
Add enterprise features with a single attribute:

| Attribute | What it does |
|-----------|-------------|
| `#[Auditable]` | Injects `created_by` / `updated_by` — auto-populated from the request |
| `#[SoftDelete]` | Handles `deleted_at` and filters queries silently |
| `#[TenantAware]` | Multi-tenant data isolation at the repository level |

---

### 🔎 3. Pro Query Engine
Complex filtering out-of-the-box via URL parameters:

```bash
# Partial search
GET /api/users?name_like=boundly

# Range filtering
GET /api/users?id_gt=100&id_lt=200

# Eager loading relationships
GET /api/users?include=profile,posts

# Sorting & Pagination
GET /api/users?sort=name&order=asc&per_page=20
```

---

### 🌍 4. Full Internationalization (i18n)
Console output speaks your language. Manage the locale from config:

```php
// config/boundly.php
return [
    'locale' => 'en', // 'en' | 'es'
    'api_prefix' => 'api',
    'paths' => [
        'domain'      => base_path('Domain'),
        'application' => base_path('Application'),
    ],
];
```

Or override per-command with the `--lang` flag:
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

---

## ⚙️ Configuration Reference

| Key | Default | Description |
|-----|---------|-------------|
| `locale` | `en` | Default language for CLI output (`en` or `es`) |
| `api_prefix` | `api` | URL prefix for all auto-generated routes |
| `paths.domain` | `Domain/` | Where BOUNDLY scans for `#[Entity]` classes |
| `paths.application` | `Application/` | Where BOUNDLY scans for `#[Action]` classes |

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
