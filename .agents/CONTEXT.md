# CONTEXT.md — RedEast Store

Last verified: 2026-10-24.

## 1. What this project is

**RedEast Store** is a Flutter storefront for Android/iOS/Web, backed by a
Laravel API and admin dashboard in `backend/`. RedEast supplies the **brand and
design system only** — no RedEast business data or client project content is
part of this product.

Current state: the Flutter screens still use the mock catalog at
`https://wantapi.com/products.php`. The Laravel backend exists but is not wired
to the app yet. Migration to the backend API is Phase 2/3 work.

## 2. Repository layout (one repo, two apps)

```text
flutter-projesi/                    # git repo root (one PR)
├── AGENTS.md                       # entry point → .agents/
├── opencode.json                   # skills.paths (.agents/skills)
├── skills-lock.json                # installed skill versions (npx skills update)
├── .agents/
│   ├── AGENTS.md  CONTEXT.md  TASKS.md
│   ├── design/brand.md             # shared RedEast design system
│   └── skills/                     # 5 official Flutter/Dart skills
├── Makefile  local.mk              # Flutter run/build automation (local.mk ignored)
├── assets/brand/                   # logos + fonts (licensed)
├── lib/                            # Flutter app
│   └── theme/redeast_theme.dart    # RedEast ThemeData (light/dark)
├── test/
├── android/  ios/  web/
└── backend/                        # Laravel 13 API + Filament admin
    ├── AGENTS.md                   # entry point + Boost guidelines block
    ├── opencode.json               # skills.paths + laravel-boost MCP
    ├── .agents/
    │   ├── AGENTS.md  CONTEXT.md  TASKS.md
    │   ├── design/brand.md         # same design system
    │   └── skills/                 # Boost Laravel skills
    ├── app/
    │   └── Filament/               # admin resources (Phase 2+)
    ├── routes/api.php + api/v1.php # /api/v1 catalog API
    ├── public/brand/               # logos + fonts served to admin
    ├── resources/css/filament/admin/theme.css
    └── .env                        # local secrets (ignored)
```

The backend is intentionally **inside** this repo so the app and API can ship
in one PR. It has no nested `.git`.

## 3. Tech stack

### Flutter
| Area | Choice |
|---|---|
| Framework | Flutter 3.47.7 (stable), Dart 3.13.5, Material 3 |
| SDK constraint | `^3.12.2` |
| Runtime deps | `dio`, `flutter_secure_storage`, `shared_preferences`, `intl` |
| Dev deps | flutter_test, flutter_lints 6, flutter_launcher_icons, flutter_native_splash |
| State | `ChangeNotifier` + `CartModelScope`/`AuthScope` (`InheritedNotifier`) |
| HTTP | Dio for auth/cart/checkout/orders; legacy conditional client for catalog |
| Platforms | Android, iOS, Web |

### Backend
| Area | Choice |
|---|---|
| Framework | Laravel 13.38.0, PHP 8.4 |
| Testing | Pest 5.3 |
| AI tooling | Laravel Boost 2.2 (MCP + skills) |
| Admin | Filament 5.10 + Shield 4.3 at `/admin` |
| API auth | Laravel Sanctum, `/api/v1` |
| Roles | Filament Shield + spatie/laravel-permission |
| Database | **SQLite**; tests use SQLite in-memory |
| Storage | **Cloudflare R2 active** (`CATALOG_MEDIA_DISK=r2`), signed media URLs; local fallback |
| Queue/cache/session | queue database; cache/session file |
| Build | Vite 8, Tailwind CSS 4 |

### Toolchain paths
```text
Flutter:  /home/jawad/Storage/flutter-3.47.7
Android:  /home/jawad/Storage/Android/sdk-linux
JDK 21:   /home/jawad/Storage/toolchains/jdk-21
Node 22:  /home/jawad/Storage/toolchains/node-22   # skills CLI only
Gradle:   /home/jawad/Storage/gradle
Backend:  PHP 8.4 + Composer 2.9 + system Node 20 for Vite
```

## 4. Design system

Read `.agents/design/brand.md` before any UI work. Summary:

- Charcoal `#363636` primary, gray `#9C9C9C` secondary, red `#ED2839` accent,
  white/black neutrals. Light surface `#F7F7F5`; dark surface `#111111`.
- Instrument Serif for display headings; Cabinet Grotesk for UI/body.
- Logos use the supplied SVGs; never redraw.
- Flutter: light-first with dark support; charcoal primary buttons, red accents.
- Filament: dark charcoal sidebar, light content, red active states.
- Assets go in `assets/brand/` (Flutter) and `backend/public/brand/` (backend).
- Implemented in Flutter: `lib/theme/redeast_theme.dart`, bundled fonts, native
  icon/splash, "RedEast Store" labels, bundle id `agency.redeast.store`.
- Implemented in Filament: brand logo/name/favicon, charcoal primary, Red East
  fonts in `resources/css/filament/admin/theme.css`.

## 5. Architecture (current Flutter)

```text
main()
  └── MainApp
        ├── CartModel
        ├── CartModelScope (InheritedNotifier)
        └── MaterialApp
              └── IndexedStack
                    ├── HomePage (mock API)
                    └── CartPage

HomePage → ApiService.fetchProducts() → HttpClientService
         → FutureBuilder → ProductCard → ProductDetailPage
```

Target architecture adds auth, catalog, cart, checkout, orders, wishlist,
reviews, and notifications backed by `/api/v1`.

## 6. API contract

- Local emulator: `http://10.0.2.2:8000/api/v1`
- Production: `https://api.redeast.agency/api/v1`
- Auth: Sanctum bearer tokens (customer abilities), JSON resources.
- **Live now:**
  - `/api/v1/health`
  - `/api/v1/products` (`category`, `brand`, `search`, `min_price`,
    `max_price`, `featured`, `sort`, `per_page`), `/products/{slug}`
  - `/api/v1/categories` / `{slug}`, `/api/v1/brands` / `{slug}`
  - `/api/v1/auth/*` (register, login, logout, me, profile, password,
    forgot/reset)
  - `/api/v1/cart` + `/cart/items` + `/cart/merge` (guest token via
    `X-Cart-Token`)
  - `/api/v1/shipping/methods`, `/coupons/validate`, `/checkout/quote`
  - `/api/v1/orders` (+ detail/cancel), `/addresses`, `/wishlist`
  - `/api/v1/products/{slug}/reviews` (GET/POST), `/api/v1/notifications`
- Flutter now calls the Laravel API (`ApiConfig`), not `wantapi.com` directly.

## 7. Commands

```bash
# Flutter (repo root; Makefile exports the environment)
make run            # boot emulator if needed + flutter run
make app            # install prebuilt APK
make analyze        # --no-fatal-infos; 2 accepted dart:html infos
make test           # fails on stale widget test
make build          # debug APK
make stop

# Backend (from backend/)
php artisan serve
npm run dev
npm run build
php artisan test --compact
vendor/bin/pint --dirty --format agent
php artisan migrate --seed
```

## 8. Current backend state

- Laravel 13.38.0, Pest 5.3, Boost 2.2 installed.
- Boost generated root `AGENTS.md`, `CLAUDE.md`, `boost.json`, and skills in
  `.agents/skills/` (`infer-conventions`, `laravel-best-practices`,
  `testing-best-practices`, `tailwindcss-development`).
- Phase 0 added backend `.agents` docs, the shared brand spec, `skills.paths` in
  `backend/opencode.json`, and SQLite ignore rules.
- `backend/vendor` and `backend/node_modules` stay inside the project:
  Composer's autoloader resolves paths relative to `vendor/`, so moving it to
  Storage breaks `artisan`. They are Git-ignored; npm/Composer caches live on
  Storage.
- Phase 1 installed Filament 5.10 + Shield, Sanctum (`/api/v1`), R2 S3 driver,
  Scramble, and Intervention Image.
- `.env` uses SQLite + local disk; R2 placeholders are present in
  `.env`/`.env.example`; the `r2` disk is configured.
- Admin: `/admin`, branded login; `admin@redeast.agency` (super_admin) and
  `customer@redeast.agency` (customer) seeded with local password `password`.
- Phase 2 added catalog schema/models/factories, 7 categories, 1 brand, 20
  seeded products/variants/images, catalog API, and Filament Catalog resources.
- Phase 3 added auth, addresses, wishlist, server carts (guest + auth merge),
  coupons, shipping methods, checkout quotes, orders, inventory movements,
  manual payments, and Filament order/customer/coupon/shipping resources.
- Flutter now has auth (login/register/profile), server-backed cart, checkout
  with shipping/coupon/manual payment, and an orders list.
- Guest carts merge into the user cart on login; checkout requires auth.
- Phase 4 added reviews + moderation, database/mail notifications, dashboard
  commerce stats, staff/admin permission seeding, Flutter wishlist/notifications
  UI, product search, responsive grids, Flutter unit/widget tests, coverage and
  GitHub Actions CI.
- Phase 5 moved catalog media to R2 signed URLs, replaced the catalog with 20
  RedEast demo products, redesigned the storefront, and made `/` redirect to the
  Filament dashboard at `/admin`.
- Tests: Pest 38/38 and Flutter 5/5; `make analyze` exit 0. Local demo orders
  populate the dashboard and reports. Local DB is SQLite; no external database
  service is required.

## 9. Planned feature phases

| Phase | Scope |
|---|---|
| 0 | ✅ `.agents` refactor, 5 skills, design spec, git/disk hygiene |
| 1 | ✅ Brand assets/theme, Filament + Sanctum, R2 env, SQLite foundation |
| 2 | ✅ Catalog schema, admin CRUD, API, seed data, Flutter catalog |
| 3 | ✅ Auth, cart, checkout, orders, inventory, coupons, shipping/tax, manual payments |
| 4 | ✅ Reviews, wishlist/notifications UI, search, stats, permissions, tests/CI (ARB translation + settings/activity log remain) |
| 5 | ✅ Storefront redesign, demo catalog, R2 signed media, SQLite demo data, `/` → `/admin` |

## 10. Known issues / technical debt

| # | Issue | Location | Impact |
|---|---|---|---|
| 1 | Detail button label uses `cart.totalItems` | `product_detail_page.dart` | Misleading |
| 2 | Flutter auth/cart/checkout widget/integration tests still thin | `test/` | 5 tests; coverage gap |
| 3 | TLS accepts any certificate in the legacy catalog client | `http_client_io.dart:9` | Demo only; remove before production |
| 4 | `dart:html` deprecated | `http_client_web.dart:2` | 2 accepted analyzer infos |
| 5 | Guest checkout not supported | `/orders` requires Sanctum | Login required before ordering |
| 6 | Address-book UI still deferred (wishlist UI is done) | backend APIs ready | Follow-up |
| 8 | Localization only wires Flutter delegates; ARB translations pending | `lib/`, `pubspec.yaml` | UI remains Turkish |
| 10 | Admin price inputs are labeled in cents | `ProductForm` | Usability; add a money input later |
| 11 | API base URL defaults to `10.0.2.2` for the emulator | `lib/config/api_config.dart` | Physical device needs `--dart-define=API_BASE_URL=...` |
| 12 | iOS build impossible on Linux | `ios/` | Environment |
| 13 | Emulator is slow under host load | AVD | Use 480x1040/4-vCPU AVD; prefer `make app` |
| 14 | `make run` rebuilds via Gradle (~10 min first time) | Makefile | Use `make app` for quick checks |

Do not fix these unless they are in `TASKS.md`.

## 11. Decisions to preserve

- RedEast is design-only; no business data import.
- Backend stays inside this repo for a single PR; no nested Git repo.
- Keep one API version prefix `/api/v1`.
- Money is stored as integer cents in the backend.
- Keep the Flutter app dependency-light until each dependency is approved.
- Keep the Turkish UI; add English through localization, not by replacing Turkish.
- Keep assets/fonts bundled; do not rely on runtime font CDNs.
