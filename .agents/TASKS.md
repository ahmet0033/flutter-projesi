# TASKS.md — RedEast Store

> Read `.agents/AGENTS.md` and `.agents/CONTEXT.md` first.
> Backend tasks live in `backend/.agents/TASKS.md`; keep both in sync.

## Conventions

- IDs are stable (`T-001`, `T-002`, …); one task per row.
- Status: `todo`, `in-progress`, `blocked`, `done`, `cancelled`.
- A task is `done` only when the verification passes.
- Record the date when starting/finishing.
- Do not add tasks without the user’s agreement.

## Phase 0 — completed: docs + skills

| ID | Task | Status | Acceptance criteria |
|---|---|---|---|
| T-001 | Rename `.agent/` → `.agents/` and update root/docs references | done | Root `AGENTS.md` points to `.agents/`; no stale `.agent/` references |
| T-002 | Create shared `design/brand.md` in Flutter and backend | done | Identical file in both repos with palette/typography/logo rules |
| T-003 | Write backend `.agents` AGENTS/CONTEXT/TASKS | done | Docs match the backend stack and plan |
| T-004 | Add `opencode.json` skills paths to both apps | done | Flutter + backend opencode configs load `.agents/skills` |
| T-005 | Install Node 22.20+ on Storage | done | Node 22.23.4 at `/home/jawad/Storage/toolchains/node-22` |
| T-006 | Install the 5 official skills into `.agents/skills` | done | 5 `SKILL.md` files present; `npx skills list` shows them |
| T-007 | Keep generated artifacts out of Git; caches on Storage | done | vendor/node_modules stay in project (Composer autoload); sqlite ignored |
| T-008 | Verify Phase 0 | done | `make analyze` exit 0; backend Pest 2/2; docs consistent |

## Next — Phase 1: brand + backend foundation

| ID | Task | Status | Acceptance criteria |
|---|---|---|---|
| T-101 | Copy RedEast logos/fonts into both apps | done | Assets + licenses present; fonts registered |
| T-102 | Apply RedEast theme to Flutter | done | Colors/typography/logo applied; light/dark |
| T-103 | Install Filament 5 panel at `/admin` | done | Admin login 200; branded panel boots |
| T-104 | Install Sanctum + `routes/api.php` | done | `/api/v1/health` 200; token auth ready |
| T-105 | Add R2 placeholders and `r2` disk | done | `.env`/`.env.example` placeholders; `r2` disk configured |
| T-106 | Add Shield/Spatie roles + admin seed | done | `super_admin` accesses `/admin`; customer 403 |

## Phase 2 — completed: catalog + admin + API

| ID | Task | Status | Acceptance criteria |
|---|---|---|---|
| T-201 | Catalog schema/models/seed | done | 7 categories, 1 brand, 20 products/variants/images |
| T-202 | Filament catalog resources | done | Product/category/brand CRUD; admin pages render |
| T-203 | Catalog API v1 | done | List/detail/filter/sort/paginate + 11 Pest tests |
| T-204 | Flutter catalog integration | done | App fetches backend products + category chips |

## Phase 3 — completed: commerce

| T-301 | Auth + addresses + wishlist API | done | Sanctum auth flows + tests |
| T-302 | Cart/checkout/orders API | done | Guest cart + merge, coupons, shipping, tax, orders, stock, manual payment |
| T-303 | Admin order/customer/coupon/shipping | done | Order status actions, customers, coupons, shipping |
| T-304 | Flutter auth/cart/checkout/orders/profile | done | Login/register, server cart, checkout, orders; address/wishlist UI deferred |

## Dashboard UI (backend, tracked separately)

The Filament dashboard build is complete; details and verification live in
`backend/.agents/TASKS.md` (D-001…D-009 done, D-010 backlog only). Dashboard work is
web-only and does not change the Flutter app.

## Phase 4 — in progress

| T-401 | Reviews/wishlist/notifications/search | done | Review moderation + API; wishlist/notifications UI; search field |
| T-402 | Reports/roles/settings/activity log | in-progress | Stats widget + permission seeding done; settings/activity log pending |
| T-403 | Localization + responsive | in-progress | Responsive grids + Flutter delegates done; ARB translations pending |
| T-404 | Tests/coverage/CI/security/perf | done | Pest 26/26, Flutter 5/5, coverage run, GitHub Actions added |

## Done

| ID | Task | Completed | Verification |
|---|---|---|---|
| T-001..T-008 | Phase 0: `.agents` refactor, brand spec, docs, skills, hygiene | 2026-10-24 | `make analyze` 0; Pest 2/2; 5 skills listed |
| T-101..T-106 | Phase 1: brand assets/theme, Filament, Sanctum, R2, roles | 2026-10-24 | Flutter APK built; admin login 200; Pest 5/5 |
| T-201..T-204 | Phase 2: catalog schema/admin/API + Flutter integration | 2026-10-24 | 20 products seeded; Pest 11/11; app loads backend catalog |
| T-301..T-304 | Phase 3: auth, carts, checkout, orders, admin commerce | 2026-10-24 | Pest 23/23; Flutter APK builds; manual guest cart/login UI verified |
| T-401..T-404 | Phase 4: reviews, notifications, search, wishlist UI, stats, tests/CI | 2026-10-24 | Pest 26/26; Flutter 5/5; CI added |
| T-501 | Phase 5: storefront redesign, demo catalog, R2 activation | 2026-10-24 | R2 signed media + SQLite seeded; `/` → `/admin` |

## Planning log

| Date | Decision | Notes |
|---|---|---|
| 2026-10-23 | Docs initialized | `.agent/` created |
| 2026-10-23 | Android environment ready | SDK/JDK/KVM/AVD on Storage; Makefile; app verified |
| 2026-10-24 | Backend scaffolded inside repo | Laravel 13.38.0 + Pest 5.3 + Boost 2.2; no nested git |
| 2026-10-24 | Phase 0 complete | `.agents` docs, brand spec, Node 22, 5 skills, opencode configs |
| 2026-10-24 | vendor/node_modules correction | Composer autoload requires `backend/vendor` in place; keep deps in project, caches on Storage |
| 2026-10-24 | Phase 1 complete | Brand assets/fonts/theme, icons/splash, bundle id, Filament/Sanctum/Shield/R2, API health, admin seed |
| 2026-10-24 | Phase 2 complete | Catalog schema/admin/API, 20 demo products, Flutter backend integration |
| 2026-10-24 | Phase 3 complete | Auth/cart/checkout/orders, coupons/shipping/tax, admin commerce; wishlist/address UI deferred |
| 2026-10-24 | Phase 4 progress | Reviews, notifications, wishlist/search UI, stats, permissions, tests/CI; ARB + settings/activity log pending |
| 2026-10-24 | Phase 5 | Storefront redesigned, demo catalog, R2 signed media, SQLite active, root redirect |
