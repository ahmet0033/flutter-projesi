# RedEast Backend

Laravel 13 API and Filament 5 admin dashboard for RedEast Store.

See the [root README](../README.md) for full setup, demo accounts, and test
commands.

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
npm ci && npm run build
php artisan migrate:fresh --seed
php artisan serve
```

- API: `http://127.0.0.1:8000/api/v1`
- Admin: `http://127.0.0.1:8000/admin`

## Test

```bash
php artisan test --compact
vendor/bin/pint --test
```

The app uses SQLite locally. Demo orders are seeded in `local` only.
