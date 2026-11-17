# PROMPTS.md — task cards (agents work from these; the chat only names the card)

**Launcher (change only the card name; the user adds the "Claude down" line if needed):**
`Read AGENTS.md, then docs/SESSION.md, then run task card "<CARD>" in docs/PROMPTS.md. Panel: Wed Nov 18, 1-5 pm (order unknown), code freeze 10:00 AM. Claude is available.`

**Every card:** Git preflight (`AGENTS.md` "Git") → branch from up-to-date `main` → build, one commit per finished page/step → write `docs/REPORT.md` (max 30 lines, questions go there) → add one Run log line in `docs/SESSION.md` → push the branch → **stop**. Never touch `main`. Cards that read a frame run the Figma MCP check first (`AGENTS.md`).

**Order:** A → F → B1 → B2 (tonight), B3 → C (tomorrow, done before the freeze). Each card is one run. Run the next card only after the user merged the previous one.

---
## A — re-skin (`slice-a`) · no Figma needed
1. Link `assets/css/tokens.css` **before** `style.css` in `includes/header.php` and `admin/includes/admin_header.php`.
2. Remove the Google Fonts links in both files. Add `@font-face` for Fredoka and Nunito from the files in `assets/fonts/` (rules: `docs/DESIGN.md` §3). No font files found → stop this step, list under Questions.
3. In `style.css` `:root`, turn each old variable into an alias of a new token **by role** (walnut → brand/header, cream → page background, ink → text…). `--font-body` → `var(--font-family-body)`, `--font-display` → `var(--font-family-heading)`. Put the mapping table in the report. No clear role → keep the old value, list under Questions.
4. Test every public and admin page at 390 px: normal, empty, keyboard focus, no 404 for fonts.

## F — per-field errors (`slice-f`) · no Figma needed
Add the invalid state to every form (contact, sign in, sign up, checkout, admin product form): red border (`--color-feedback-error`), message under the field, `aria-invalid="true"`, `aria-describedby`, keep the top banner. Only top-of-file input handling may change, to show errors per field; no logic changes. Test: submit empty, submit invalid, submit valid.

## B1 — home, shop, product (`slice-b1`)
Frames: `Mobile/Public` → `index.php`, `Mobile/Collection` → `shop.php`, `Mobile/Product Profile` → `product.php` (node ids: `docs/DESIGN.md` §9). Read **one frame at a time** via MCP. Apply the `AGENTS.md` "Decisions" bulk rule. Restyle desktop (700/1000 px) from the same components. Log differences per page in the report (max 6 lines each).

## B2 — sign in/up, basket, checkout (`slice-b2`)
Frames: `Mobile/Sign In` → `login.php`, `Mobile/Sign Up` → `register.php`, `Mobile/Basket` → `cart.php`, `Mobile/Checkout` → `checkout.php`. Wording: "Basket", "Update Basket", "Order". Same method as B1. Keep CSRF inputs and `name=` attributes.

## B3 — admin (`slice-b3`)
Frames: `Mobile/Admin/Dashboard` → `admin/index.php`, `Mobile/Add/Product` → `admin/product_form.php`. Keep the "Out of stock" dashboard card. Lock icons only on the 3 disabled tabs. No admin orders screen (backend member's call).

## C — pages without a frame (`slice-c`)
Contact, about, account, order detail, admin products and users lists, delete confirm, mechanics, terms. Restyle from the nearest Figma component and tokens (no MCP needed). Contact must show the per-field error clearly (demo case).

## EXPORT (optional, Codex, `design-images`)
Only if time remains and the user asks. Export decided background images to `assets/img/` as WebP/JPG (max ~150 KB). Product placeholders are never exported.

## AUDIT (optional, not after the freeze)
Branch `codebase-audit`. Read-only report `docs/CODEBASE_AUDIT.md`: redundant files/functions, README claims that disagree with the code. No deletions.
