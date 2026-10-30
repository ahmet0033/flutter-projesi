# AGENTS.md — Mandatory Agent Instructions (Flutter app)

> **Read this file before executing ANY prompt in this repository.**
> Reading order: **`AGENTS.md` → `CONTEXT.md` → `TASKS.md`**.
> For backend work, also read **`backend/.agents/AGENTS.md`**.

This repository is **RedEast Store**: a Flutter storefront plus a Laravel API/admin
backend in `backend/`. RedEast is used for **brand and design only** — do not
import RedEast business data, client projects, or internal notes.

If anything below conflicts with a one-off user request, ask first.

## 1. Session start checklist

1. Read `.agents/CONTEXT.md` (structure, stack, API, design, known issues).
2. Read `.agents/TASKS.md` (current task plan; do not invent tasks).
3. Read `.agents/design/brand.md` before any UI change.
4. For backend changes, read `backend/.agents/AGENTS.md` and `backend/.agents/CONTEXT.md`.
5. Confirm the task and check `git status` before editing.

## 2. Rules of engagement

- **Never commit or push** unless the user explicitly asks.
- **Ask before adding a dependency.** The default approved Flutter set is listed in
  `CONTEXT.md`; anything else needs approval.
- **Keep the UI language Turkish** (`Ana Sayfa`, `Sepet`, `Sepete Ekle`, `Satın Al`).
  Localization will add English later; do not remove Turkish copy.
- **RedEast design only.** Use the palette, fonts, and logos in
  `.agents/design/brand.md`. Never redraw the logo or invent brand colors.
- **Preserve the platform split** in `lib/http/` (`dart:io` vs `dart:html`).
  Do not import `dart:io` into web-shared code or vice versa.
- **Do not edit generated/vendor directories:** `.dart_tool/`, `build/`,
  `.idea/`, `android/local.properties`, `backend/vendor/`, `backend/node_modules/`.
- **Do not run `flutter upgrade`, `flutter channel`, or reinstall the SDK.**
- **Do not create a nested Git repo inside `backend/`.** The backend is part of
  this repo and of the same PR.
- **No secrets in code.** R2 credentials live in `backend/.env`.
- **Money is stored as integer cents** in the backend; never floats.
- **Prefer the smallest correct change.** Match existing formatting and naming.
- **After every change**, run the relevant verification (section 5). Update
  `.agents/TASKS.md` if the plan changes.
- **One task at a time**; keep unrelated refactors out of the diff.
- Do not fabricate test results or command output.

## 3. Toolchain / environment

Use the repository `Makefile`; it reads machine paths from the gitignored `local.mk`.

- Flutter **3.47.7**, Dart **3.13.5**, DevTools 2.60.0
- Project SDK constraint: `^3.12.2` (`pubspec.yaml`)
- Android SDK: `/home/jawad/Storage/Android/sdk-linux`
- JDK 21: `/home/jawad/Storage/toolchains/jdk-21`
- AVD: `Pixel_5_API33_Linux`, 480x1040 @195 dpi, 4 vCPU, 2 GB (Storage)
- KVM enabled persistently (`/home/jawad/Storage/enable-kvm.sh`)
- Gradle home: `/home/jawad/Storage/gradle`
- `build/` is a symlink to `/home/jawad/Storage/flutter-build/flutter-projesi/build`
- Node **22.20+** for the skills CLI: `/home/jawad/Storage/toolchains/node-22`
- Backend: PHP 8.4, Laravel 13, Composer, Pest 5, Laravel Boost 2 — see
  `backend/.agents/CONTEXT.md`
- Do not use the old Windows Flutter 3.38.5 at `/home/jawad/Storage/flutter`.

## 4. Skills

Official Flutter/Dart skills live in `.agents/skills/`:

| Skill | Use when |
|---|---|
| `flutter-add-integration-test` | Adding Flutter Driver/integration tests |
| `flutter-setup-localization` | Setting up `flutter_localizations`/ARB |
| `flutter-build-responsive-layout` | Phone/tablet/desktop layouts |
| `dart-use-pattern-matching` | Refactoring with switch/patterns |
| `dart-collect-coverage` | Collecting LCOV coverage |

Activate the relevant skill instead of improvising. Backend work uses Laravel
Boost skills in `backend/.agents/skills/` (`laravel-best-practices`,
`testing-best-practices`, `tailwindcss-development`, `infer-conventions`).

Skills are registered through `opencode.json` (`skills.paths`) and recorded in
`skills-lock.json`. After installing/updating skills, **restart opencode** so the
loader picks them up. Use Node 22 for `npx skills`; system Node 20 is too old.

## 5. Standard verification

```bash
# Flutter (from repo root)
make analyze           # --no-fatal-infos; 2 accepted dart:html infos
make test              # currently fails on the stale widget test
make build             # debug APK

# Backend (from backend/)
php artisan test --compact
vendor/bin/pint --dirty --format agent
```

Known accepted output: the two `dart:html` info notices in
`lib/http/http_client_web.dart`. `test/widget_test.dart` still expects
`Home`/`Cart` instead of the Turkish UI; fix it only as an agreed task.

When a check fails, state the failure, the likely cause, and whether it is
pre-existing or caused by your change.

## 6. Definition of done

- Requested behavior implemented and matches existing conventions.
- `make analyze` has no new issues; backend tests/Pint pass for backend changes.
- Relevant tests pass, or the failure is documented as pre-existing.
- `.agents/TASKS.md` (and the backend `TASKS.md` when API changes) is updated.
- Final report lists changed files, commands run, and remaining risks.
