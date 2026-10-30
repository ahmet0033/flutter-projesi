# AGENTS.md — Mandatory Agent Instructions (Laravel backend)

> **Read this file before executing ANY prompt that touches `backend/`.**
> Reading order: **`AGENTS.md` → `CONTEXT.md` → `TASKS.md`**.
> The generated `<laravel-boost-guidelines>` block in the repository root
> `backend/AGENTS.md` is maintained by Laravel Boost; do not edit inside it.

This is the **RedEast Store** backend: a Laravel 13 API for the Flutter app and a
Filament 5 admin dashboard. RedEast is used for **design and styling only** — do
not add RedEast business data or client project content.

## 1. Session start checklist

1. Read `.agents/CONTEXT.md` (architecture, schema, API, env, current state).
2. Read `.agents/TASKS.md` (agreed backend work).
3. Read `.agents/design/brand.md` before touching the admin UI.
4. Check `git status` and confirm the task before editing.
5. Run `composer show --direct` if you need installed versions.

## 2. Non-negotiable rules

- **Never commit or push** unless the user explicitly asks.
- **Ask before adding a package or changing dependencies.** The approved stack is
  in `CONTEXT.md`.
- **No secrets in code or Git.** R2 credentials live only in `.env`.
- **Money is integer cents** (`unsignedBigInteger`/integer columns, cast helpers).
  Never use floats for money.
- **Use database transactions** for checkout, order creation, stock changes,
  refunds, and coupon usage.
- **Order creation must be idempotent** (client idempotency key + unique index).
- **Validate with Form Requests**; authorize with Policies/Gates; return API
  Resources. Do not put business logic in controllers.
- **Version the API** under `/api/v1`. Never break an existing version.
- **Use Sanctum** for mobile tokens; Filament uses the web session guard.
- **Use the `r2` disk for product media** in production; local `public` for dev.
- **Keep migrations reversible** with proper indexes and foreign keys.
- **Queue slow work** (emails, image conversions, exports) via the database queue.
- **Follow existing conventions** and check sibling files before writing.
- **No nested Git repo** in `backend/`; it is part of the parent repo.
- **One task at a time**; keep unrelated refactors out of the diff.
- Do not fabricate test results or command output.

## 3. Toolchain

- PHP 8.4, Composer 2.9, Laravel 13.38.0
- Pest 5.3, Pint, Laravel Boost 2.2
- Filament 5.10 + Shield 4.3, Sanctum, Scramble, Intervention Image
- Node 20 for Vite; Tailwind CSS 4
- Database: SQLite for local and deployed use
- Storage: local `public` in dev; Cloudflare R2 in production
- Cache/session: file; queue: database

```bash
cd backend
php artisan serve
npm run dev          # or npm run build
php artisan migrate:fresh --seed   # local: resets and seeds demo orders
php artisan test --compact
vendor/bin/pint --dirty --format agent
```

## 4. Skills

Laravel Boost installed these in `.agents/skills/`:

| Skill | Use when |
|---|---|
| `laravel-best-practices` | Any Laravel PHP code, queries, validation, security |
| `testing-best-practices` | Writing/reviewing Pest tests |
| `tailwindcss-development` | Admin/Tailwind UI work |
| `infer-conventions` | Only when the user explicitly asks to infer conventions |

Activate the relevant skill instead of improvising. The Flutter skills in the
parent `.agents/skills/` are for the app, not the backend.

## 5. Architecture rules

- `app/Http/Controllers/Api/V1/` — API controllers (thin).
- `app/Http/Requests/Api/V1/` — validation.
- `app/Http/Resources/Api/V1/` — responses.
- `app/Models/` — Eloquent models, casts, scopes, relationships.
- `app/Services/` — checkout, orders, inventory, payments, media.
- `app/Filament/` — admin resources, pages, widgets, theme.
- `routes/api.php` loads `routes/api/v1.php`; `routes/web.php` stays for web.
- Filament panel at `/admin`; roles `super_admin`, `admin`, `staff`; customers
  use the API and must not access Filament.

## 6. Verification / definition of done

- `php artisan test --compact` for the touched area.
- `vendor/bin/pint --dirty --format agent` after PHP changes.
- `npm run build` after admin theme/Tailwind changes.
- Migrations run on a fresh SQLite DB.
- `.agents/TASKS.md` updated; API changes mirrored in the Flutter `.agents/CONTEXT.md`.
- Final report: changed files, commands run, remaining risks.
