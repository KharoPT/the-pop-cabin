# DESIGN.md — Design rules and Figma-to-CSS mapping

Updated Nov 17, 2026. Sections 1-6 are rules; sections 7-10 record the Run 1 audit of the Figma `High-Fi` page.

## 1. Source of truth
Figma hi-fi (file "Pop Cabin Design": https://www.figma.com/design/VoPylafW12yk2UBAoHnZsJ/Pop-Cabin-Design, variable collection "High-Fi (Wyn)") wins for the screens it covers, over the old `style.css` (from the wireframe pass). Differences between Figma and the code are resolved by the bulk rule in `AGENTS.md` "Decisions" (no row-by-row `COMPARISON.md`; the agent logs differences in `docs/REPORT.md`). Agents that cannot open Figma work from this file, `tokens.css`, and the PNGs in `docs/figma/`.

## 2. The binding rule
| Figma element | CSS to write |
| --- | --- |
| Uses a **variable** | The matching token, e.g. `var(--color-surface-page)` |
| Uses a **text style** | The matching type tokens |
| Uses a **semantic alias** | The semantic token, never the primitive behind it |
| **Not bound** | The **exact raw value** + a row in `docs/UNBOUND.md` |

Never round a raw value to the "closest" token. The user decides later whether to bind it in Figma or add a token.

## 3. Palette and fonts (DECIDED by the user)
| Role | Color | Hex |
| --- | --- | --- |
| Primary (pair) | Walnut | `#5A3A29` |
| Accent (pair) | Honey | `#D9A441` |
| Opposite | Deep teal | `#1F5560` |
| Text | Ink | `#241812` |
| Background | Cream | `#F3E8D3` |
| Error / success | Figma variables `--color-feedback-error` / `--color-feedback-success` | `#B3261E` / `#3E7B4F` (verify contrast with a checker) |

**Fonts:** Fredoka (headings) + Nunito (body). Weights in the Figma tokens: 400, 500, 600, 700. Figma text styles: Fredoka 21, Nunito 19, **Inter 8** (Inter is not a decided font: see `docs/SESSION.md` Open). **Self-host** the font files in `assets/fonts/` (`.ttf` is fine; `.woff2` is only smaller). In `@font-face` use `format("truetype")` for `.ttf`. Static files = one `@font-face` per weight (400/500/600/700); a variable file = one `@font-face` with `font-weight: 400 700` (the demo runs on localhost and must not depend on Wi-Fi). Remove the old Google Fonts links (Cabin, Zilla Slab) from BOTH `includes/header.php` and `admin/includes/admin_header.php`; `--font-body` and `--font-display` in `style.css` still name the old fonts.

**Contrast (hand-calculated, verify with a checker; WCAG AA needs 4.5:1 for normal text):**
| Pair | Approx. ratio | Use |
| --- | --- | --- |
| Ink on cream | 14:1 | Body text |
| Walnut on cream | 8:1 | Headings, links |
| Teal on cream | 6.8:1 | Links, accents |
| Ink on honey | 7.7:1 | Text on honey buttons |
| Cream on walnut / teal | 8:1 / 6.8:1 | Text on dark fills |
| **Honey on cream** | **1.9:1, FAILS** | Never for text. Fills and decoration only |
| Honey on walnut | 4.5:1, borderline | Large or bold text only |

**Main combo:** honey fill + dark brown text. Use Ink `#241812` on honey (about 7.7:1). Walnut `#5A3A29` on honey is about 4.5:1, so large or bold text only.

## 4. Token plan (`assets/css/tokens.css`)
- Loaded **before** `style.css`; the only place token values live.
- **Name = Figma variable name**: `color/surface/page` → `--color-surface-page` (slash to hyphen, lowercase).
- Two layers: **primitives** (raw palette) → **semantic** (purpose). Components use semantic tokens only.
- Final names are in `assets/css/tokens.css` (exported from Figma Nov 17, then cleaned): primitives `--primitive-walnut-600`, `--primitive-honey-400`, `--primitive-teal-500`, `--primitive-cream-200`, `--primitive-walnut-900`; semantic `--color-bg-page`, `--color-text-primary`, `--color-action-primary`, `--color-action-brand`, `--color-highlight-default`, `--color-text-link`, `--color-feedback-error`, `--color-feedback-success`. A few ad-hoc names are flagged in the file for review in Figma. `--font-family-heading` and `--font-family-body` were added by hand (Figma has no font-family variable).
- Groups: color, space, radius, font-size/weight/line-height, stroke, shadow, z-index, motion.
- Old `:root` names in `style.css` today: `--birch`, `--pine-100/200/300`, `--oak`, `--cedar`, `--bark`, `--walnut` (`#3b2a1f`), `--espresso`, `--ember`, `--moss`, `--cream`, `--ink`, `--muted`, plus `--font-body`, `--font-display`, `--wrap`, `--radius` and the wood textures `--grain`, `--planks`, `--plank-dark`, `--log-wall`. They stay as **temporary aliases** pointing at new tokens. Note: old `--walnut` was `#3b2a1f`; it will shift to the new walnut. Delete aliases once no page uses them.
- Dark theme only if Figma has modes.

## 5. Naming (Figma and CSS)
- Components: `Component/Variant` (`Button/Primary`, `Card/Product`). Variant props `Property=Value`: `State=Default|Hover|Focus|Disabled|Error`.
- Frames: `Page / Breakpoint / State`, e.g. `Contact / Mobile / Invalid`. No default names (`Frame 214`).
- Auto layout on every container; spacing from variables.
- CSS: flat, lowercase, hyphenated from the component name (`Card/Product` → `.card-product`). State classes use `is-`. Breakpoints `700px` and `1000px` (Figma's 390 / 768 / 1280 are frame widths, not breakpoints; recommended, see `docs/SESSION.md` Open).

## 6. Required and derived states
default, **empty**, **invalid**, **success** (+ blocked/loading). Current list: `README.md` section 9.5. Known gap: per-field errors (red border, message under field, `aria-invalid`). Today only a top banner exists.

**Figma state tokens (Run 1):** `--opacity-hover-overlay` 0.08, `--opacity-pressed-overlay` 0.12, `--opacity-disabled` 0.4, `--color-action-primary-hover` / `-pressed`, `--color-action-brand-hover`, `--color-highlight-hover` / `-pressed`; the `Sign In` component has Default and Pressed variants. Where Figma defines a state, Figma wins; the percentages below are the fallback (pending decision: `docs/SESSION.md` Open 3).

**Derived states** (only where Figma shows none; where Figma has a state, Figma wins). They are **dynamic**: one shared rule set, and each button variant only sets variables, so light-surface and dark-surface buttons both work.

```css
.btn { --btn-bg: …; --btn-fg: …; --btn-mix: black; }  /* button on a light surface */
.btn-on-dark { --btn-mix: white; }                     /* button on a dark surface */
@media (hover: hover) {
  .btn:hover { background: color-mix(in srgb, var(--btn-bg) 92%, var(--btn-mix)); }
}
.btn:active { background: color-mix(in srgb, var(--btn-bg) 88%, var(--btn-mix)); transform: translateY(1px); }
```
`color-mix()` blends the button's own color with black or white, so hover and pressed follow whatever color the variant uses. Outline, ghost, and text buttons: hover fill = `color-mix(in srgb, var(--btn-fg) 8%, transparent)`. If the site ever gets a dark theme, only the variables change, not the rules.

| State | Rule |
| --- | --- |
| Focus (`:focus-visible`) | 2px outline, 2px offset, on every interactive element; teal on light surfaces, cream on dark. Never remove it. |
| Disabled | `--opacity-disabled` (0.4 in Figma; 50% was the pre-audit guess), `not-allowed` cursor, no hover or pressed change. |
| Transition | 120-150 ms on color, background, transform; none under `prefers-reduced-motion`. |

Every derived color must keep 4.5:1 text contrast; check each one and tune the mix percentage if it fails. List the final values in a "Derived" table here for review.

## 7. Figma audit results

Audit scope: the `High-Fi` page only. Variable and style inventories are file-level because Figma stores them outside individual pages.

| Resource | Count / details |
| --- | --- |
| Variable collections | 2 total: `High-Fi (Wyn)` (252 variables, 1 mode: `Mode 1`) and `Wireframe Colors` (5 variables, 1 mode: `Mode 1`). |
| Exported token collection | `High-Fi (Wyn)` only; 252 CSS declarations in `assets/css/tokens.css`. Variable aliases remain `var(...)` references. |
| Local text styles | 48: Fredoka 21, Nunito 19, Inter 8. Style weights were read from `fontName.style`. |
| Local paint / effect styles | 0 paint styles; 0 effect styles. |
| Text style variable bindings | 0 of 48 text styles expose variable bindings. |
| High-Fi page nodes | 1,789 total; 1,787 visible. The page contains 648 frames, 201 instances, 29 components, and 3 component sets. |
| Component sets / variants | `Trash`: `Property 1=Default`, `small`; `Sign In`: `Property 1=Default`, `Pressed`; `Product Card`: `Property 1=Default`. |
| State reference components | Standalone `Warning`, `Edit`, `Error`, and `Success` icon references are present; they are not state pages. |
| Text nodes | 454 visible; 330 have named text styles and 124 do not. One visible text style reference could not be resolved. |

## 8. Principles not followed in the hi-fi

| Principle | Finding | Run 1 record |
| --- | --- | --- |
| Bind values to variables or styles | The page has 24 grouped raw paint/stroke values, 11 corner radii, 76 layout-spacing values, 4 stroke widths, 20 effect stacks, and 16 groups of raw typography values. Exact values and sample layers are in `docs/UNBOUND.md`; none were rounded. | `docs/UNBOUND.md` |
| Use auto layout for containers | The layout scan found 6 nodes with no auto-layout mode and 2 using grid. Review these against the container rule in section 5. | High-Fi page inventory |
| Use descriptive frame and layer names | 136 frames use generic `Frame NN` names; 141 generic layer names include `Object`. | High-Fi page inventory |
| Cover desktop and required UI states | All 9 top-level screen frames are 390px wide mobile frames. No desktop, empty, invalid, success, blocked, or loading screen frames are present. Component references include only the variants listed in section 7. | Section 9; state reference components are not pages |
| Keep named style coverage complete | 124 visible text nodes have no named text style, and one style reference was unresolved. | Section 7 |

## 9. Screen map

All listed artboards are mobile-only. Dimensions are the exact Figma frame dimensions; no desktop or dedicated state-page exports exist.

| Screen | Figma frame (node id; exact dimensions) | PNG | PHP file | Status |
| --- | --- | --- | --- | --- |
| Home | `Mobile/Public` (`243:5030`; 390 × 2834.515380859375px) | `docs/figma/Home-Mobile.png` | `index.php` | Mobile frame |
| Sign in | `Mobile/Sign In` (`304:1512`; 390 × 1104.26953125px) | `docs/figma/Sign-In-Mobile.png` | `login.php` | Mobile frame |
| Sign up | `Mobile/Sign Up` (`304:1204`; 390 × 1252.26953125px) | `docs/figma/Sign-Up-Mobile.png` | `register.php` | Mobile frame |
| Admin dashboard | `Mobile/Admin/Dashboard` (`330:5234`; 390 × 844px) | `docs/figma/Admin-Dashboard-Mobile.png` | `admin/index.php` | Mobile frame |
| Product profile | `Mobile/Product Profile` (`370:7680`; 390 × 3276.26953125px) | `docs/figma/Product-Profile-Mobile.png` | `product.php` | Mobile frame |
| Basket | `Mobile/Basket` (`377:9313`; 390 × 1520.26953125px) | `docs/figma/Basket-Mobile.png` | `cart.php` | Mobile frame |
| Checkout | `Mobile/Checkout` (`384:9698`; 390 × 1493.26953125px) | `docs/figma/Checkout-Mobile.png` | `checkout.php` | Mobile frame |
| Admin add product | `Mobile/Add/Product` (`348:5750`; 390 × 1164px) | `docs/figma/Admin-Add-Product-Mobile.png` | `admin/product_form.php` | Mobile frame |
| Collection | `Mobile/Collection` (`365:6802`; 390 × 4231.26953125px) | `docs/figma/Collection-Mobile.png` | `shop.php` | Additional populated frame; see Questions in `docs/REPORT.md` |

## 10. Images

Product photos are Figma placeholders and are not exported as separate assets. The PNGs in `docs/figma/` are whole-frame references only.

| Figma frame | Image | Kind (product placeholder / background / other) | Code today | Decision (user) |
| --- | --- | --- | --- | --- |
| Public, Basket, Product Profile, Collection | Product-card and product-detail photo placeholders (part of 58 visible image fills across the page; 17 unique source hashes across all image fills) | Product placeholder | Real product photos are served from `uploads/products/`; empty slots use `.img-ph`. | Placeholder; do not export separately |
| `Mobile/Public` | Hero Background | Background | Existing site backgrounds use CSS gradients / inline SVG patterns. | Pending |
| `Mobile/Public` | Devs Background (two fills on one visible layer) | Background | Existing site backgrounds use CSS gradients / inline SVG patterns. | Pending |
| `Mobile/Sign In`, `Mobile/Sign Up` | Shared sign-in / sign-up backdrop | Background | Existing site backgrounds use CSS gradients / inline SVG patterns. | Pending |
| `Mobile/Basket`, `Mobile/Checkout` | Shared basket / checkout backdrop | Background | Existing site backgrounds use CSS gradients / inline SVG patterns. | Pending |
| `Mobile/Product Profile` | Product-profile backdrop | Background | Existing site backgrounds use CSS gradients / inline SVG patterns. | Pending |
| `Mobile/Collection` | Collection backdrop | Background | Existing site backgrounds use CSS gradients / inline SVG patterns. | Pending |
| `Mobile/Public` | Team portraits | Other | Team content comes from `siteContent('team')`; `.img-ph` is the empty fallback. | Pending |
| `Mobile/Product Profile` | Review avatars | Other | Review authors currently render as initials. | Pending |
| `Mobile/Admin/Dashboard`, `Mobile/Add/Product` | Admin mascot illustrations | Other | No matching code image identified in the image audit. | Pending |
| `Mobile/Public` | Overview illustration (`Object`) | Other | No matching code image identified in the image audit. | Pending |

## 11. Orchestrator decisions (Nov 17, delegated by the user)
- **Do not use** `--space-32` (112 px) and `--space-48` (161 px): they break the 4 px scale. Use `--space-24` or `--section-gap-*`.
- **Text contrast fixes** (hand-calculated, verify with a checker). Use for TEXT only:
  | Pair | Ratio on cream | Verdict | Use instead |
  | --- | --- | --- | --- |
  | `--color-text-muted` (walnut-400) | ~3.9:1 | FAILS small text | `--color-text-muted-aa` (walnut-500, ~6.1:1) |
  | `--color-feedback-success` (green-600) | ~4.2:1 | FAILS small text | `--color-feedback-success-text` (green-700, ~6.2:1); green-600 stays for fills/icons |
  | `--color-feedback-error` (red-600) | ~5.4:1 | OK | keep |
- **Ad-hoc tokens at the bottom of `tokens.css`:** `--color-text-secondary-orig` is honey, which fails as text on cream; use only for fills or on dark surfaces. Leave other ad-hoc names unused unless a frame needs them.
- **Hover/pressed percentages** (92% / 88% mix, outline 8%) are the Figma 0.08 / 0.12 overlays expressed with `color-mix()`.
- **Fonts:** Inter (8 Figma text styles) is accidental; use Nunito. Self-hosted files are in `assets/fonts/`; use the file names found there.
- **Buttons (default mapping, unless a PNG or Figma shows otherwise):** `.btn-dark` = walnut fill (`--color-action-brand`) + cream text; `.btn-outline` = teal border/text (`--color-action-outline-border`); the "Dibs!" call to action = honey fill (`--color-highlight-default`) + ink text; header = `--color-bg-header`, footer = `--color-bg-footer`.
- **Hover/pressed/disabled:** Figma overlay tokens (0.08 / 0.12 / disabled 0.4) in the dynamic rule of section 6.
- **Backgrounds/images:** stay CSS (token colors, gradients) tonight. Image export is the optional EXPORT card (Codex).