# RedEast Store

RedEast Store is the RedEast-branded version of the original Flutter
E-Ticaret application. The Flutter client remains the storefront, and the
repository now also contains a Laravel API and Filament admin dashboard so the
app can run end to end.

## Overview

- **Flutter** storefront for Android, iOS, and web
- **Laravel 13** JSON API at `/api/v1`
- **Filament 5** admin dashboard at `/admin`
- **SQLite** local database with demo catalog and orders
- RedEast branding, responsive UI, cart, checkout, orders, reviews, wishlist,
  notifications, and account management
- Dashboard KPIs, revenue chart, recent orders, top products, reports with CSV
  export, store settings, activity log, staff management, and optional 2FA

## Stack

| Area | Technology |
|---|---|
| App | Flutter 3.47.7 / Dart 3.12 |
| Backend | PHP 8.4, Laravel 13.38 |
| Admin | Filament 5.10, Shield, Sanctum |
| Database | SQLite |
| Media | Local disk or Cloudflare R2 |
| Build | Vite 8, Tailwind CSS 4 |

## Run the backend

Requirements: PHP 8.4, Composer 2, Node 20.

```bash
cd backend

composer install
cp .env.example .env
php artisan key:generate

touch database/database.sqlite
npm ci
npm run build

php artisan migrate:fresh --seed
php artisan serve
```

Open:

- Store/dashboard: `http://127.0.0.1:8000`
- Admin: `http://127.0.0.1:8000/admin`
- API health check: `http://127.0.0.1:8000/api/v1/health`

Demo accounts:

- Admin: `admin@redeast.agency` / `password`
- Customer: `customer@redeast.agency` / `password`

`migrate:fresh --seed` resets the local SQLite database and creates the demo
catalog plus six demo orders. Demo orders are seeded only when `APP_ENV=local`.

## Run the Flutter app

```bash
flutter pub get
flutter run
```

Other useful commands:

```bash
flutter run -d chrome       # web
flutter build apk           # Android
flutter build web           # web release
flutter build ios           # iOS, macOS only
```

The API URL defaults to:

- Web: `http://127.0.0.1:8000/api/v1`
- Android emulator: `http://10.0.2.2:8000/api/v1`

For a physical device, pass your computer's LAN address:

```bash
flutter run --dart-define=API_BASE_URL=http://192.168.1.10:8000/api/v1
```

## Tests

Backend:

```bash
cd backend
php artisan test --compact
vendor/bin/pint --test
```

Flutter:

```bash
flutter analyze --no-fatal-infos
flutter test
```

Tests use SQLite in-memory. No emulator or screenshots are required.

## Project layout

```text
lib/                 Flutter app
assets/              RedEast brand assets
test/                Flutter tests
backend/             Laravel API + Filament admin
backend/database/    Migrations, seeders, local SQLite file
.github/workflows/   CI
```

## Backend documentation

See [backend/README.md](backend/README.md) for the backend-specific quick start.
The API is versioned under `/api/v1`; the admin dashboard is web-only.

## Notes

- Change the seeded passwords before any real deployment.
- R2 is optional; configure `CATALOG_MEDIA_DISK` and R2 credentials in
  `backend/.env` when needed.
- Local `.env`, SQLite files, build output, and dependencies are ignored by Git.
- The original Flutter app structure and platform support are unchanged.
