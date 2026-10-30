# RedEast Brand & Design System

Source of truth for the **RedEast Store** Flutter app and the Laravel/Filament
admin dashboard. RedEast is used for **design and styling only**; do not import
RedEast business data or client project content.

## 1. Brand essentials

- Name: **RedEast** / **RedEast Store** (product)
- Domain: `redeast.agency`
- Voice: plain, opinionated, human; not corporate-safe.
- The mark: nine-dot constellation — one solid black center dot, charcoal
  anchors, gray satellites, exactly one red dot on the right.
- Never reconstruct or redraw the logo. Use the supplied assets.

## 2. Palette

| Token | Hex | Usage |
|---|---|---|
| `charcoal` | `#363636` | Primary text, sidebar, primary buttons |
| `gray` | `#9C9C9C` | Secondary text, borders, muted icons |
| `red` | `#ED2839` | Accent, active state, price highlights, destructive/attention |
| `white` | `#FFFFFF` | Light surfaces, text on dark |
| `black` | `#000000` | Logo center dot, maximum-emphasis text |
| `surface-light` | `#F7F7F5` | App/page background (light) |
| `surface-dark` | `#111111` | App/page background (dark) |
| `success` | `#1F8A5B` | Success states only (not a brand color) |
| `warning` | `#B7791F` | Warnings only |

Red is an **accent**, not the default button color. Primary actions use
charcoal with white text; red is for emphasis and destructive/attention actions.
Ensure text contrast; do not put small text directly on red.

## 3. Typography

| Role | Typeface | Weights |
|---|---|---|
| Display / page titles / hero | **Instrument Serif** | 400, italic 400 |
| Body / UI / tables / forms | **Cabinet Grotesk** | 400, 500, 700 |

Rules:
- Use Instrument Serif for large editorial headings only; never for dense UI.
- Use Cabinet Grotesk for navigation, labels, buttons, body, tables, forms.
- Fallbacks: `ui-serif, Georgia, serif` and `ui-sans-serif, system-ui, sans-serif`.
- Numbers in tables should be tabular/monospaced where available.
- Fonts are bundled locally with their license files; no runtime CDN dependency.

## 4. Spacing, radius, elevation

- Spacing scale: 4, 8, 12, 16, 24, 32, 48, 64.
- Page gutter: 16 mobile, 24 tablet, 32 desktop.
- Radius: 8 (inputs/badges), 12 (small cards), 16 (cards), 20 (hero/panels), 999 (pills).
- Shadows: subtle only — `0 2px 8px rgba(0,0,0,.06)` and
  `0 6px 16px rgba(0,0,0,.08)`. Prefer borders over heavy shadows.
- Max content width on the dashboard: 1440 px; forms ~720 px.

## 5. Logo usage

Assets live in:

- Flutter: `assets/brand/`
- Backend: `public/brand/`

Use:
- `logo.alone.light.svg` on dark backgrounds
- `logo.alone.dark.svg` on light backgrounds
- `RedEast.black.svg` / `RedEast.white.svg` for lockups
- PNG dot marks for app icons/splash

Rules:
- Keep clear space equal to the dot diameter on all sides.
- Minimum mark size: 24 px; minimum lockup width: 96 px.
- Never stretch, recolor, rotate, outline, or place on busy imagery.
- On photography, use the white lockup with a dark scrim.
- The red dot is the only red element of the mark; do not add more red dots.

## 6. Components

### Buttons
- Primary: charcoal fill, white text, 12–14 px radius, 44–52 px height.
- Accent/destructive: red fill, white text — use sparingly.
- Secondary: white fill, charcoal text, 1 px gray border.
- Disabled: gray at 40% opacity, no shadow.

### Cards
- White surface, 16 px radius, 1 px `gray` at 20% border, subtle shadow.
- Product image area: `#F8F8FA`; contain images, never crop the product.

### Inputs
- White surface, 1 px gray border, 12 px radius, 44 px height.
- Focus: charcoal border + subtle red ring. Error: red border + message.

### Navigation
- Mobile: bottom bar, charcoal selected, gray unselected, red badge for cart.
- Dashboard: dark charcoal sidebar, white content, red active indicator.

### Tables
- Cabinet Grotesk, 13–14 px, zebra-free, 1 px gray dividers.
- Status pills: charcoal/gray for neutral, red for attention, green for success.

### Empty states
- Dot mark or relevant icon, short title, one sentence, one primary action.

## 7. App vs dashboard

| Area | Direction |
|---|---|
| Flutter app | Light-first, dark supported; charcoal primary buttons; red accents; serif hero headings; product-first imagery; generous spacing |
| Filament dashboard | Dark charcoal sidebar + light content; red active states; serif page headings; grotesk tables/forms; dense but breathable |
| Both | Same tokens, fonts, logo rules, radii, and spacing scale |

## 8. Do / don’t

Do:
- Use charcoal as the default action color.
- Use red for one focal point per screen (price, badge, active state, destructive action).
- Use the supplied SVGs for the mark/lockup.
- Keep layouts quiet and let product photography lead.

Don’t:
- Don’t use red as a large background.
- Don’t combine Instrument Serif with itself for body text.
- Don’t add gradients, glows, or extra brand colors.
- Don’t redraw the nine-dot mark or change the red dot position.

## 9. Asset checklist (Phase 1 — complete)

- [x] Copy logo SVGs/PNGs to Flutter `assets/brand/` and backend `public/brand/`
- [x] Add Instrument Serif (OFL) and Cabinet Grotesk (Fontshare) with licenses
- [x] Register Flutter fonts in `pubspec.yaml` and backend `@font-face`
- [x] Apply tokens in Flutter `ThemeData` and Filament theme
- [x] Brand app icon, splash, dashboard login, and sidebar lockup
