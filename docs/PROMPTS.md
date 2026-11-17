# PROMPTS.md — task cards (agents work from these; the chat only names the card)

**Launcher (change only the card name; the user adds the "Claude down" line if needed):**
`Read AGENTS.md, then docs/SESSION.md, then run task card "<CARD>" in docs/PROMPTS.md. Panel: Wed Nov 18, 1-5 pm (order unknown), code freeze 10:00 AM. Claude is available.`

**Every card:** Git preflight (`AGENTS.md` "Git") → branch from up-to-date `main` → build, one commit per finished page/step → write `docs/REPORT.md` (max 30 lines, questions go there) → add one Run log line in `docs/SESSION.md` → push the branch → **stop**. Never touch `main`. Cards that read a frame run the Figma MCP check first (`AGENTS.md`).

**Order:** A, F (merged) → **H → B1 (redo)** → B2 → B3 → C. Done before the 10:00 freeze. Each card is one run. Run the next card only after the user merged the previous one.

---
## A — re-skin (`slice-a`) · no Figma needed
1. Link `assets/css/tokens.css` **before** `style.css` in `includes/header.php` and `admin/includes/admin_header.php`.
2. Remove the Google Fonts links in both files. Add `@font-face` for Fredoka and Nunito from the files in `assets/fonts/` (rules: `docs/DESIGN.md` §3). No font files found → stop this step, list under Questions.
3. In `style.css` `:root`, turn each old variable into an alias of a new token **by role** (walnut → brand/header, cream → page background, ink → text…). `--font-body` → `var(--font-family-body)`, `--font-display` → `var(--font-family-heading)`. Put the mapping table in the report. No clear role → keep the old value, list under Questions.
4. Test every public and admin page at 390 px: normal, empty, keyboard focus, no 404 for fonts.

## F — per-field errors (`slice-f`) · no Figma needed
Add the invalid state to every form (contact, sign in, sign up, checkout, admin product form): red border (`--color-feedback-error`), message under the field, `aria-invalid="true"`, `aria-describedby`, keep the top banner. Only top-of-file input handling may change, to show errors per field; no logic changes. Test: submit empty, submit invalid, submit valid.

## H — shared header, nav, footer (`slice-h`) · runs BEFORE B1
Files: `includes/header.php`, `includes/footer.php`, `admin/includes/admin_header.php` (and its footer). Read the header and footer of `Mobile/Public` (243:5030) via MCP (design context, not only screenshots); export logo/mascot/background assets per `AGENTS.md` rule 7. Keep every link, the Basket count, the login/account/admin states and the search. Menu open state has no frame: build it CSS-first from the header components and tokens (JS only if CSS cannot, must work with JS off) and write `LAYOUT NOT VERIFIED: nav open state`. Desktop (700/1000 px): same components, nav in a row. Test logged out, customer and admin, at 390 and 1000 px, keyboard focus.

## B1 — home, shop, product (`slice-b1`) · REDO of a rejected run
Continue on the existing `slice-b1` branch (new commits; do not force-push). Frames: `Mobile/Public` → `index.php`, `Mobile/Collection` → `shop.php`, `Mobile/Product Profile` → `product.php` (node ids: `docs/DESIGN.md` §9). Header/footer are card H (already merged): do not touch them. Read **one frame at a time** and use `get_design_context` values for every spacing, size and radius (`AGENTS.md` rule 9). The first run was rejected for these faults; fix each:
1. Hero and "Devs" backgrounds, the Overview illustration, and every decorative/complex vector shape were replaced by gradients or plain boxes. Export them to `assets/img/` and use them.
2. Spacing is off. Re-measure every section.
3. Meet the Devs cards were simplified. Rebuild exactly: card shape, glass/border, portrait, name, role. Use the **real Figma names and portrait PNGs** (rule 10).
4. Remove the "Ready to order your pop" section (not in Figma).
5. Add the Overview section back: Lorem ipsum in Figma → write mock Pop Cabin copy with the same word/sentence count and text style; keep its illustration.
6. Wooden signs/headings replaced by plain text: rebuild the Figma look.
Re-check shop and product against their frames with the same method. Log differences per page (max 6 lines each).

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