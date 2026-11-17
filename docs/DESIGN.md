# DESIGN.md — Design rules and Figma-to-CSS mapping

Updated Nov 17, 2026 (Figma audit and `tokens.css` not started yet). Status: sections 1-5 are rules. Sections 6-8 are filled by the Codex Figma audit.

## 1. Source of truth
Figma hi-fi (file "Pop Cabin Design": https://www.figma.com/design/VoPylafW12yk2UBAoHnZsJ/Pop-Cabin-Design, variable collection "High-Fi (Wyn)") wins for the screens it covers, over the old `style.css` (from the wireframe pass). Differences between Figma and the code are listed in `docs/COMPARISON.md` and decided by the user row by row (see `AGENTS.md`). Agents that cannot open Figma work from this file, `tokens.css`, and the PNGs in `docs/figma/`.

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
| Error / success | Already defined in Figma as variables (values come from the audit) | TBD |

**Fonts:** Fredoka (headings) + Nunito (body). Weights come from the Figma audit. **Self-host** the `.woff2` files in `assets/fonts/` (the demo runs on localhost and must not depend on Wi-Fi). Remove the old Google Fonts links (Cabin, Zilla Slab) from BOTH `includes/header.php` and `admin/includes/admin_header.php`; `--font-body` and `--font-display` in `style.css` still name the old fonts.

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
- Seed (final names come from Figma): primitives `--color-walnut-700`, `--color-honey-500`, `--color-teal-700`, `--color-ink-900`, `--color-cream-100`. Semantic: `--color-text-default`, `--color-surface-page`, `--color-brand-primary`, `--color-accent`, `--color-link`.
- Groups: color, space, radius, font-size/weight/line-height, stroke, shadow, z-index, motion.
- Old `:root` names in `style.css` today: `--birch`, `--pine-100/200/300`, `--oak`, `--cedar`, `--bark`, `--walnut` (`#3b2a1f`), `--espresso`, `--ember`, `--moss`, `--cream`, `--ink`, `--muted`, plus `--font-body`, `--font-display`, `--wrap`, `--radius` and the wood textures `--grain`, `--planks`, `--plank-dark`, `--log-wall`. They stay as **temporary aliases** pointing at new tokens. Note: old `--walnut` was `#3b2a1f`; it will shift to the new walnut. Delete aliases once no page uses them.
- Dark theme only if Figma has modes.

## 5. Naming (Figma and CSS)
- Components: `Component/Variant` (`Button/Primary`, `Card/Product`). Variant props `Property=Value`: `State=Default|Hover|Focus|Disabled|Error`.
- Frames: `Page / Breakpoint / State`, e.g. `Contact / Mobile / Invalid`. No default names (`Frame 214`).
- Auto layout on every container; spacing from variables.
- CSS: flat, lowercase, hyphenated from the component name (`Card/Product` → `.card-product`). State classes use `is-`. Breakpoints `700px` and `1000px` unless Figma differs.

## 6. Required and derived states
default, **empty**, **invalid**, **success** (+ blocked/loading). Current list: `README.md` section 9.5. Known gap: per-field errors (red border, message under field, `aria-invalid`). Today only a top banner exists.

**Derived states** (only where Figma shows none; where Figma has a state, Figma wins). They are **dynamic**: one shared rule set, and each button variant only sets variables, so light-surface and dark-surface buttons both work.

```css
.btn { --btn-bg: …; --btn-fg: …; --btn-mix: black; }  /* button on a light surface */
.btn-on-dark { --btn-mix: white; }                     /* button on a dark surface */
@media (hover: hover) {
  .btn:hover { background: color-mix(in srgb, var(--btn-bg) 90%, var(--btn-mix)); }
}
.btn:active { background: color-mix(in srgb, var(--btn-bg) 80%, var(--btn-mix)); transform: translateY(1px); }
```
`color-mix()` blends the button's own color with black or white, so hover and pressed follow whatever color the variant uses. Outline, ghost, and text buttons: hover fill = `color-mix(in srgb, var(--btn-fg) 10%, transparent)`. If the site ever gets a dark theme, only the variables change, not the rules.

| State | Rule |
| --- | --- |
| Focus (`:focus-visible`) | 2px outline, 2px offset, on every interactive element; teal on light surfaces, cream on dark. Never remove it. |
| Disabled | 50% opacity, `not-allowed` cursor, no hover or pressed change. |
| Transition | 120-150 ms on color, background, transform; none under `prefers-reduced-motion`. |

Every derived color must keep 4.5:1 text contrast; check each one and tune the mix percentage if it fails. List the final values in a "Derived" table here for review.

## 7. Figma audit results (Codex fills)
- Variable collections and counts: _TBD_
- Text / effect styles: _TBD_
- Components and variants: _TBD_
- Naming problems: _TBD_

## 8. Principles not followed in the hi-fi (Codex fills)
Each place the design skips its own variables, styles, or semantics, and what the code does about it. _TBD_

## 9. Screen map (Codex fills)
| Screen | Figma frame | PNG | PHP file | Status |
| --- | --- | --- | --- | --- |
| _TBD_ | | | | |

## 10. Images (Codex fills; rules in `AGENTS.md` "Images")
Product photos in Figma are placeholders for real photos (do not export). Backgrounds need a decision each.
| Figma frame | Image | Kind (product placeholder / background / other) | Code today | Decision (user) |
| --- | --- | --- | --- | --- |
| _TBD_ | | | | |
