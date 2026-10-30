# TASKS.md — RedEast Store Backend

> Read `.agents/AGENTS.md` and `.agents/CONTEXT.md` first.
> App-side tasks live in the parent `.agents/TASKS.md`; keep both in sync.

## Conventions

- IDs are stable (`B-001`, `B-002`, …); one task per row.
- Status: `todo`, `in-progress`, `blocked`, `done`, `cancelled`.
- A task is `done` only when its verification passes.
- Record the date when starting/finishing.
- Do not add tasks without the user’s agreement.

## Phase 0 — completed: docs + skills + hygiene

| ID | Task | Status | Acceptance criteria |
|---|---|---|---|
| B-001 | Create backend `.agents` AGENTS/CONTEXT/TASKS | done | Docs match the installed stack and plan |
| B-002 | Copy the shared brand spec to backend `.agents/design` | done | Identical to the Flutter brand doc |
| B-003 | Add a project pointer outside the Boost block in root `AGENTS.md` | done | Pointer present; Boost block untouched |
| B-004 | Add `skills.paths` to `opencode.json` | done | Backend Boost skills load in opencode |
| B-005 | Keep generated artifacts out of Git; caches on Storage | done | vendor/node_modules stay in project (Composer autoload); ignored by Git |
| B-006 | Ignore local SQLite files | done | `database/*.sqlite` ignored |

## Next — Phase 1: foundation

| ID | Task | Status | Acceptance criteria |
|---|---|---|---|
| B-101 | Run `php artisan install:api` (Sanctum + `routes/api.php`) | done | `/api/v1/health` 200; Sanctum migration ran |
| B-102 | Install Filament 5 panel at `/admin` | done | Admin login 200; panel boots |
| B-103 | Install Shield + Spatie Permission | done | Roles `super_admin`/`admin`/`staff`/`customer`; gate before |
| B-104 | Add R2 filesystem + R2 env placeholders | done | `r2` disk configured; `.env`/`.env.example` documented |
| B-105 | Add API docs package (Scramble) | done | Package installed; docs route available |
| B-106 | Apply RedEast theme to Filament | done | Palette/fonts/logo/login applied; Turkish UI |
| B-107 | Seed admin + customer accounts | done | `admin@redeast.agency` super_admin; customer role |

## Phase 2 — completed: catalog + admin + API

| ID | Task | Status | Acceptance criteria |
|---|---|---|---|
| B-201 | Catalog migrations/models/factories | done | Categories, brands, products, variants, images, inventory movements; specs JSON |
| B-202 | Filament catalog resources | done | CRUD, variants/images repeaters, stock, filters, soft deletes |
| B-203 | Catalog API v1 | done | List/detail/filter/sort/paginate + Resources |
| B-204 | Seed 20 demo products | done | 7 categories, 1 brand, 20 products; reproducible seeder |
| B-205 | Catalog tests | done | Pest 11/11 including admin pages + API filters |
| B-206 | API docs updated | done | Scramble docs + `.agents/CONTEXT.md` |

## Phase 3 — completed: auth, cart, checkout, orders

| ID | Task | Status | Acceptance criteria |
|---|---|---|---|
| B-301 | Customer auth + addresses + wishlist | done | Register/login/logout/me/profile; policies |
| B-302 | Carts + guest merge + coupons | done | Guest cart, merge on login, coupon validation |
| B-303 | Shipping + tax + checkout quote | done | Quote totals in integer cents |
| B-304 | Orders + payments (manual/COD) + refunds | done | Transactional, idempotent order creation |
| B-305 | Inventory movements | done | Reserve/decrement/restore with history |
| B-306 | Filament orders/customers/coupons/shipping | done | Status transitions, notes, refunds |
| B-307 | Commerce tests | done | Auth, cart, checkout, stock, idempotency |

## Phase 4 — in progress: engagement + quality

| ID | Task | Status | Acceptance criteria |
|---|---|---|---|
| B-401 | Reviews + moderation | done | Verified purchase flag; admin moderation |
| B-402 | Notifications (email + database) | done | Order confirmation/status emails queued |
| B-403 | Search/filter tuning + indexes | done | SQLite-compatible search; no N+1 |
| B-404 | Reports + CSV export | done | Sales/products/customers/inventory with filtered CSV |
| B-405 | Settings + activity log | done | Store/currency/tax settings + audit trail |
| B-406 | Security/perf pass | in-progress | Rate limits, signed URLs, image conversions, indexes |
| B-407 | CI (Pest + Pint + Vite build) | done | GitHub Actions green |

## Done

| ID | Task | Completed | Verification |
|---|---|---|---|
| B-001..B-006 | Phase 0: backend docs, brand spec, skills paths, hygiene | 2026-10-24 | `php artisan about` OK; Pest 2/2 |
| B-101..B-107 | Phase 1: Filament, Sanctum, Shield, R2, theme, seed | 2026-10-24 | Admin login 200; Pest 5/5; Vite build OK |
| B-201..B-206 | Phase 2: catalog schema/admin/API/seed | 2026-10-24 | 20 products; Pest 11/11; Flutter loads API |
| B-301..B-307 | Phase 3: auth, cart, checkout, orders, admin commerce | 2026-10-24 | Pest 23/23; transactional idempotent orders |
| B-401..B-407 | Phase 4: reviews, notifications, stats, permissions, CI | 2026-10-24 | Pest 26/26; CI workflow added |
| B-501..B-503 | Phase 5: R2 activation, demo catalog, root redirect | 2026-10-24 | SQLite seeded; R2 signed URLs; `/` → `/admin` |

## Planning log

| Date | Decision | Notes |
|---|---|---|
| 2026-10-24 | Backend created inside repo | Laravel 13.38.0, Pest 5.3, Boost 2.2; no nested Git |
| 2026-10-24 | Phase 0 complete | `.agents` docs, brand spec, skills paths, SQLite ignored |
| 2026-10-24 | vendor/node_modules correction | Composer autoload requires vendor inside the project; keep deps there, caches on Storage |
| 2026-10-24 | Phase 1 complete | Filament 5 + Sanctum + Shield + R2 + Scramble; branded admin; seeded roles/users |
| 2026-10-24 | Phase 2 complete | Catalog schema/admin/API/seed; Flutter fetches backend catalog |
| 2026-10-24 | Phase 3 complete | Auth/cart/checkout/orders/coupons/shipping/manual payments + admin |
| 2026-10-24 | Phase 4 progress | Reviews/notifications/stats/CI; settings/activity log + CSV report pending |
| 2026-10-24 | Phase 5 complete | SQLite migrated/seeded; R2 active with signed URLs; demo catalog; root redirect |
| 2026-10-24 | Dashboard widgets | KPI stats, 7-day revenue chart, recent orders, top products; money inputs in product form |

## Dashboard UI — implementation plan (Filament, web-only)

Ground rules:
- Dashboard stays **inside Filament at `/admin`**; `/` redirects there. No custom public dashboard route.
- Web-only: staff use the Filament session guard; customers never access the panel; admin screens are not exposed through `/api/v1`.
- Keep the RedEast theme; run `npm run build` after admin CSS changes.
- Each task is done only with `php artisan test --compact`, `vendor/bin/pint --test`, and a smoke check against SQLite/R2.

### Completed

| ID | Task | Verification |
|---|---|---|
| D-001 | `/` → `/admin` redirect; remove welcome page | `/` 302; `/admin/login` 200 |
| D-002 | Branded dashboard shell (sidebar/topbar/logo/fonts/colors) | Panel renders with RedEast theme |
| D-003 | Dashboard widgets: KPIs, 7-day revenue, recent orders, top products | `/admin` renders; chart and empty states verified |
| D-004 | Reports: sales, products, customers, inventory; filtered CSV | All report pages + CSV downloads tested |
| D-005 | Store settings + activity log | Settings drive checkout currency/tax; audit entries recorded |
| D-006 | Staff management, access matrix, app 2FA | Role matrix tested; 2FA available on profile |
| D-007 | Web-only + responsive/dark pass | Root redirect, panel guard, responsive CSS, dark mode verified |
| D-008 | Admin UX polish | Bulk actions, order payments/statuses, customer history, filters |
| D-009 | Dashboard tests + CI | Pest 38/38; Pint; Vite build; CI workflow configured |

### D-004 — Reports + CSV (done)

- [x] **D-004.1 Sales report**: date range (default: last 30 days), order table, revenue summary, CSV.
- [x] **D-004.2 Product report**: units sold and revenue by product/category, date-filtered.
- [x] **D-004.3 Customer report**: new customers, order count, total spend.
- [x] **D-004.4 Inventory report**: low-stock and out-of-stock variants.
- [x] **D-004.5 CSV export** on every report using `streamDownload`; respects filters.
- **Verification:** `DashboardUiTest` covers page rendering + all four CSV downloads.

### D-005 — Store settings + activity log (done)

- [x] **D-005.1 Settings table/model** (`store_settings`) with currency, tax, order prefix, contact, socials.
- [x] **D-005.2 Filament Settings page** with validation and `ManageSettings` permission.
- [x] **D-005.3 Replace config reads** with `App\Support\StoreSettings` in checkout/order/cart resources.
- [x] **D-005.4 Activity log** (`activity_logs`) with observer-based create/update/delete audit and read-only Filament resource.
- [x] **D-005.5 Tests** for settings persistence, checkout tax/currency, settings audit, and activity views.
- **Verification:** settings page save + checkout quote test; activity create/update/view test.

### D-006 — Roles, permissions, 2FA (done)

- [x] **D-006.1 Access matrix tests**: admin full dashboard access; staff denied settings/reports/staff/audit; customer denied panel.
- [x] **D-006.2 Staff management**: `Staff` Filament resource (admin only) assigns admin/staff roles.
- [x] **D-006.3 Two-factor authentication** via Filament's built-in app provider; recovery codes available on `/admin/profile`.
- [x] **D-006.4 Permissions**: custom permissions seeded; `ShieldPermissionSeeder` keeps admin full and staff scoped.
- **Note:** 2FA is available but not forced; set `isRequired: true` in `AdminPanelProvider` to enforce it.

### D-007 — Web-only + responsive verification (done)

- [x] **D-007.1 Web-only audit**: `/` redirects to `/admin`; admin routes are not under `/api/v1`; customer panel access is 403.
- [x] **D-007.2 Responsive pass**: desktop/tablet layouts, collapsible sidebar, table overflow, page width.
- [x] **D-007.3 Dark-mode pass** plus RedEast dark sidebar.
- [x] **D-007.4 Visual pass** completed; screenshot artifacts intentionally not committed.
- **Verification:** responsive CSS built by Vite; route/access tests.

### D-008 — Admin UX polish (done)

- [x] **D-008.1 Product form**: dollar price inputs, variant price inputs, stock badges, bulk activate/deactivate/feature/unfeature/price adjust.
- [x] **D-008.2 Order detail**: payment history, status transition actions, notes/refunds.
- [x] **D-008.3 Customer detail**: profile, addresses, order history, total spend.
- [x] **D-008.4 Coupons, shipping, reviews**: filters and clearer empty states.
- [x] **D-008.5 Dashboard**: quick-action links and explicit empty states.
- **Verification:** Pest admin page tests; manual responsive/dark pass.

### D-009 — Dashboard tests + CI (done)

- [x] **D-009.1 Pest feature tests** for settings, reports, CSV, staff, access matrix, activity, 2FA.
- [x] **D-009.2 CI** runs Pint, Pest, and Vite build; local equivalents pass.
- [x] **D-009.3 SQLite smoke test**: migrations, demo orders, settings seed, custom permissions, dashboard load.
- **Verification:** `php artisan test --compact` → 38/38, 310 assertions; Pint passed; `npm run build` passed.

### D-010 — Backlog (not scheduled)

- [ ] PDF invoices / packing slips
- [ ] Email template previews
- [ ] Stock reservation alerts
- [ ] Scheduled report exports
- [ ] Media library view for R2

### Recommended order

1. D-005 settings + activity log
2. D-004 reports + CSV
3. D-006 roles/permissions/2FA
4. D-008 admin UX polish
5. D-007 responsive/dark/web-only pass
6. D-009 tests + CI
7. D-010 backlog only if requested

## Done

| ID | Task | Completed | Verification |
|---|---|---|---|
| B-001..B-006 | Phase 0: backend docs, brand spec, skills paths, hygiene | 2026-10-24 | `php artisan about` OK; Pest 2/2 |
| B-101..B-107 | Phase 1: Filament, Sanctum, Shield, R2, theme, seed | 2026-10-24 | Admin login 200; Pest 5/5; Vite build OK |
| B-201..B-206 | Phase 2: catalog schema/admin/API/seed | 2026-10-24 | 20 products; Pest 11/11; Flutter loads API |
| B-301..B-307 | Phase 3: auth, cart, checkout, orders, admin commerce | 2026-10-24 | Pest 23/23; transactional idempotent orders |
| B-401..B-407 | Phase 4: reviews, notifications, stats, permissions, CI | 2026-10-24 | Pest 26/26; CI workflow added |
| B-501..B-503 | Phase 5: R2 activation, demo catalog, root redirect | 2026-10-24 | SQLite seeded; R2 signed URLs; `/` → `/admin` |
| D-001..D-009 | Dashboard UI implementation | 2026-10-24 | Pest 38/38; Pint; Vite build; SQLite smoke |

## Planning log

| Date | Decision | Notes |
|---|---|---|
| 2026-10-24 | Backend created inside repo | Laravel 13.38.0, Pest 5.3, Boost 2.2; no nested Git |
| 2026-10-24 | Phase 0 complete | `.agents` docs, brand spec, skills paths, SQLite ignored |
| 2026-10-24 | vendor/node_modules correction | Composer autoload requires vendor inside the project; keep deps there, caches on Storage |
| 2026-10-24 | Phase 1 complete | Filament 5 + Sanctum + Shield + R2 + Scramble; branded admin; seeded roles/users |
| 2026-10-24 | Phase 2 complete | Catalog schema/admin/API/seed; Flutter fetches backend catalog |
| 2026-10-24 | Phase 3 complete | Auth/cart/checkout/orders/coupons/shipping/manual payments + admin |
| 2026-10-24 | Phase 4 progress | Reviews/notifications/stats/CI; settings/activity log + CSV report pending |
| 2026-10-24 | Phase 5 complete | SQLite migrated/seeded; R2 active with signed URLs; demo catalog; root redirect |
| 2026-10-24 | Dashboard Phase 1 | KPI stats, 7-day revenue chart, recent orders, top products; money inputs |
| 2026-10-24 | Dashboard plan frozen | Remaining work captured as D-004..D-010 for the post-compaction session |
| 2026-10-24 | Dashboard complete | Settings, audit, reports/CSV, staff/2FA, UX polish, responsive pass, tests |
| 2026-10-24 | SQLite rollback | Local DB is SQLite only; demo orders seed dashboard/reports; file cache/session |
| 2026-10-24 | Pre-push cleanup | README refreshed; unused scaffold/test/Vite inputs removed; ignore rules tightened |
