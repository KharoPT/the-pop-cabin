# AGENTS.md — The Pop Cabin (design → code)

Updated Nov 18, 2026. Read this file, then `docs/DESIGN.md` when touching styles.
The agent reads the design and builds; the user reviews the branch and merges.

## Source of truth
1. **FigCraft (the design app)** — MCP at `127.0.0.1:45777`. The hi-fi frames live on
   the `High-Fi` page (`Mobile/Public`, `Mobile/Collection`, `Mobile/Product Profile`,
   `Mobile/Basket`, `Mobile/Checkout`, `Mobile/Sign In`, `Mobile/Sign Up`,
   `Mobile/Admin/Dashboard`, `Mobile/Add/Product`). Read sizes, gaps, colors and type
   from `inspect_document` / `get_node` — never guess from a thumbnail.
2. **`HTMLREF.md` / `CSSREF.md`** — when the user supplies them, they are the exact
   translation reference for that frame. Follow them for values and structure, but
   keep the codebase's PHP flow, data and behaviour. They are references, not files
   to ship.
3. **The codebase** — features, pages, data, forms and queries. Never change the flow
   to fit a mockup.
4. **`assets/css/tokens.css`** — the design tokens (colors, space, radius, type).
   Use a token when the measured value matches it exactly; otherwise use the measured
   raw value. Do not round to the nearest token.

## Workflow (one page or one group of frames per run)
1. **Git.** `git switch main && git pull` → `git switch -c design-<pages>`. Never
   commit to `main`, never force-push. Commit each finished page, push the branch,
   stop for the user to review and merge. If `main` moved while you worked, merge it
   into your branch and re-test before pushing.
2. **Read the frame.** `inspect_document` (tree), `get_node` (sizes/gaps/fills/type),
   `screenshot` (look), then export the assets the page needs: backgrounds, hero art,
   illustrations, logos, icons. Product photos stay in `uploads/products/` — never
   export them.
3. **Translate, don't transplant.** Keep the existing template, PHP data and form
   handling; replace the presentation. Section order and copy follow the frame; real
   data wins over placeholder numbers; lorem ipsum becomes realistic Pop Cabin copy
   with the same length. Keep behaviour when JS is off.
4. **Test** the page at `390`, `768` and `1280` px (normal, empty, invalid, success,
   keyboard focus). Screenshot with headless Chrome and compare section by section.
5. **Commit + push.** No REPORT.md, no run log. The branch and a short chat summary
   are the handoff.

## Naming
- **Never keep the design export's layer names in the code.** `frame-63`, `group-2`,
   `vector2`, `sign-in-content` and friends describe the design tree, not this page.
  Name every class, id and variable for what it does here (`auth-card`, `field-row`,
  `auth-switch`, `tile-nav`). The same applies to the HTML/CSS references the user
  supplies — read their values, rewrite their names.
- One file per page group: `assets/css/auth.css`, `assets/css/shop.css`, … Loaded by
  the page with `$pageStyles = ['css/auth.css'];` before `includes/header.php`.
  `base.css` holds the shared foundations (fonts, aliases, reset, buttons, forms,
  page chrome); `header.css`, `footer.css` and `product-card.css` are components.

## Components (used by every public page, admin has its own)
- Header: `includes/header.php` + `assets/css/header.css`.
- Footer: `includes/footer.php` + `assets/css/footer.css`.
- Page sections: `assets/css/<page>.css`, loaded through `$pageStyles`.
- Pop card: `includes/product_card.php` + `assets/css/product-card.css`, loaded by
  `shop.php` and `product.php` before their own page stylesheet.
- Shared scripts: `assets/js/main.js` (small vanilla JS, commented).
Changing the header or footer means changing the component files once — never
override them per page.

## Layout rules
- Mobile-first at **390 px** (the hi-fi width), then **700 px** and **1000 px**; fluid
  between. The desktop layout is an extrapolation of the mobile frame: same order,
  same components, wider containers and scaled type.
- Page padding 16 px mobile, 32 px at 700, 40 px at 1000; content max 1520 px.
- Section gap: 48 px mobile, 80 px at 700, 96 px at 1000, 112 px at 1400.
- Every screen covers default, empty, invalid and success. Invalid = per-field error:
  red border, message under the field, `aria-invalid="true"`, `aria-describedby`, plus
  the existing top banner. Every input has a `<label>`.

## Hard rules
1. **Never edit:** `classes/`, `config/` (read it), `actions/`, `sql/`, table or
   column names, a form field `name=`, a `csrf_token` input, or a page/route.
2. **Escape output** with `e()`; every POST form keeps its CSRF token.
3. **Images:** export from FigCraft and store in `assets/img/` named by use
   (`bg-hero.jpg`, `logo-mark.svg`). Vectors stay `.svg`; rasters ≈150–250 KB max.
   Never replace real art with a CSS gradient or a plain box.
4. **States:** where the frame shows one, the frame wins; otherwise hover 0.08 /
   pressed 0.12 overlay, disabled 0.4, `:focus-visible` outline, transitions off
   under `prefers-reduced-motion`.
5. **Small text** uses `--color-text-muted-aa` and `--color-feedback-success-text`
   (the base muted/success colors fail 4.5:1 on cream).
6. **Do not use** `--space-32` or `--space-48` (they break the 4 px scale).
7. **Team data:** the Figma/FigCraft names, roles, quotes and portraits are real —
   use them exactly.
8. Keep the bundle clean: no frameworks, no CDN, self-hosted fonts only.

## Pages
Public: `index.php` (Mobile/Public), `shop.php` (Mobile/Collection),
`product.php` (Mobile/Product Profile), `cart.php` (Mobile/Basket),
`checkout.php` (Mobile/Checkout), `login.php` (Mobile/Sign In),
`register.php` (Mobile/Sign Up), plus `about.php`, `contact.php`, `mechanics.php`,
`terms.php`, `account.php`, `order_detail.php`. Admin: `admin/` (own header/footer).
