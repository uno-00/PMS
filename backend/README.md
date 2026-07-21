# Backend (Laravel API + Livewire)

PHP application: routes, controllers, models, services, migrations, and business logic.

## Run locally

```bash
cd backend
composer install
cp .env.example .env   # if needed
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

From repo root you can also use:

```bash
cd backend && composer serve
```

## Key paths

| Path | Purpose |
|------|---------|
| `app/` | Application code (Livewire, models, services) |
| `routes/` | Web, API, and console routes |
| `database/` | Migrations, seeders, factories |
| `config/` | Laravel configuration |
| `public/` | Web root (`index.php`, built assets) |
| `storage/` | Logs, cache, uploads |
| `tests/` | PHPUnit feature and unit tests |

UI templates and frontend assets live in [`../frontend/`](../frontend/).
