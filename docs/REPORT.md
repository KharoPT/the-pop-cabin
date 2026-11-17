# Card A report — re-skin (`slice-a`)

- **Done:** `tokens.css` loaded before `style.css` on public + admin; Google Fonts links removed; Fredoka/Nunito self-hosted (8 static faces, 400/500/600/700); every old `:root` variable aliased to a token by role; all public + admin pages checked at 390 px.
- **Files changed:** `includes/header.php`, `admin/includes/admin_header.php`, `assets/css/style.css`. Handoff commit `68d009a` carries AGENTS.md, tokens.css, DESIGN.md, SESSION.md, PROMPTS.md and `assets/fonts/`.
- **Git preflight:** Handoff files committed first. `origin/main` moved to `a6db94c` (duplicate `CREATE DATABASE` fix) — merged clean. `slice-a` was branched from `design-setup` (Run 1: tokens/DESIGN/PNGs), because Run 1 is not on `main` yet; merging `origin/main` brought only the SQL fix.
- **Mapping (old `:root` → token):** birch→bg-page · pine-100→bg-surface-alt · pine-200→brand-soft · pine-300→border-default · oak→border-strong · bark→action-brand · walnut→action-brand · espresso→bg-inverse · ember→action-primary · moss→feedback-success · cream→bg-surface · ink→text-primary · muted→text-muted-aa · font-body→font-family-body · font-display→font-family-heading · wrap→container-max.
- **Removed / kept raw:** unused `--cedar` removed (0 usages). `--radius` kept at `6px`: no 6 px token exists (xs 4 / sm 8), not rounded.
- **Tokens/unbound added:** none. No new raw values in components.
- **JS added:** none.
- **Tested (Chrome, 390 px viewport):** index, shop, login, register, cart (empty), contact, about, mechanics, terms → `scrollWidth` 390, no page overflow (home carousel scrolls by design); empty shop search renders. Admin as seeded admin: dashboard, products, add/edit product, users, delete confirm, oop_demo → no overflow. Focus outline on header search + admin photo input = 3 px teal `rgb(31,85,96)`. Checkout/account/order detail redirect guests as before. All pages 200, no PHP warnings; `tokens.css`, `style.css` and all 8 fonts return 200.
- **Differences found and done (per page):** no Figma frame for this card, so no visual compare; alias re-skin only.
- **Not done:** per-field invalid/empty/success states (card F); per-page Figma restyle (B1–B3); desktop polish.
- **LAYOUT NOT VERIFIED:** none for A (no frame read).
- **Questions:** 1) `--wrap` 1100→1200 (`--container-max`) widens desktop; keep? 2) `--ember`→teal action-primary (hover/focus/active), honey left for CTAs — OK? 3) `--radius` has no exact token: keep 6 px or move to 4/8? 4) `.status-shipped` (also the admin-role pill) is still hardcoded purple; restyle in B3?
- **Backend needs:** none.
