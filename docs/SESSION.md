# SESSION.md — Pop Cabin status (Tue Nov 17, 2026)

Handoff so a new chat or agent can resume. Tags: **Decided** (user said so), **Adopted** (AI suggestion the user told us to move forward with), **Open** (not decided; recommendation given). Codebase facts: `README.md`. Rules for agents: `AGENTS.md`.

## Deadlines
- **OOP panel: Wednesday Nov 18** (marketing front page 40, paper 30, live demo + Q&A 30). Panel time: _fill in_.
- Database and DSA presentations: next week. The DB project uses this same Pop Cabin database; DSA is another group's project.

## Goal (user, Nov 17)
Adapt the Figma hi-fi onto the existing codebase **without changing major operations**: only minor visual cues and text, **per the user's decision**. Figma and the front end will mostly not match, so the agent must ask for a decision on every difference. **Figma audit and `tokens.css` are not started.**

## Decided (user)
- Palette: walnut `#5A3A29`, honey `#D9A441`, deep teal `#1F5560`, text `#241812`, background `#F3E8D3`. Fonts: Fredoka + Nunito. Main combo: honey with dark brown text. Success/error colors are in Figma.
- The current codebase is the source of truth for features and pages. Reservations dropped (leader). Cart, checkout, orders stay.
- Stack: HTML, CSS, PHP, no frameworks; small vanilla JS fine.
- Customer-facing cart is **Basket** (UI text only; `cart.php` and `Cart` keep names). Wording: "Order" and "Basket"; "Dibs!" stays.
- Hover/pressed states are not designed; the AI creates them, and they must be **dynamic** (light and dark buttons).
- **Compare → Decide → Build.** Every row needs the user's explicit decision (C / F / S); a blank is not approval.
- Figma images: some are placeholders for the real product photos (the code keeps the real photos), others are backgrounds (each needs a decision).
- Tools: Codex (Figma) audits and compares; DeepSeek builds; Claude chat plans and reviews; reports come back as md.
- **No code freeze at midnight** (user: it has to be finished). New freeze time: see Open 1.
- **Git:** branch per slice. The AI runs the whole git flow, including a fetch check for teammates' pushes before every run (`AGENTS.md` "Git").
- **`README.md` = the full codebase guide and replaces the repo's old README** (teammates' note kept inside it). It carries no status; status and "last verified" live in `AGENTS.md`/`docs/SESSION.md`.
- **Repo root:** `README.md` and `AGENTS.md` only. **`docs/`:** `SESSION.md`, `DESIGN.md`, `UNBOUND.md`, agent outputs (`COMPARISON.md`, `REPORT.md`, `CODEBASE_AUDIT.md`) and `figma/` PNGs. Tokens go in `assets/css/tokens.css`.

## Adopted
1. Hover/pressed/focus/disabled are built in code from `docs/DESIGN.md` section 6.
2. Codebase audit is report-only first; deletions only after the user approves each item.

## Open (recommendation, not decided)
1. **Freeze time.** Recommended: ≥3 hours before the panel. Merge each slice the moment it passes the demo checklist, so `main` is always presentable; anything unfinished stays on its branch. Slice order A → B → C keeps that true.
2. **Bulk decision line** at the top of `docs/COMPARISON.md` (e.g. `Bulk: accept all recommendations except rows 4, 9`).
3. **Who merges:** the AI pushes its branch; the user clicks "Squash and merge" on the GitHub pull request.
4. **Background images** from Figma go to `assets/img/` (optimized, referenced from CSS), not `uploads/` or `config/content.php`.
5. **Admin orders screen** does not exist in the code: intended?
6. **First merge:** Run 1 outputs and these md files go in through branch `design-setup` (docs + an unlinked `tokens.css`, cannot break the site), then tag `handoff-1`.

## Codebase state (commit 837c734, Nov 17; one branch `main`, teammates push to it)
- OOP gap closed by backend: abstract `BaseModel`, `describe()` overrides, `admin/oop_demo.php`. Not done: interface, namespaces (optional).
- `sql/popcabdb.sql` is the full dump (admin, 30 products, 11 franchises). Problems: `CREATE DATABASE` twice; missing foreign keys on `reviews`, `inquiries`, `product_collections`, `carts.user_id`.
- Still open: `display_errors` on, order prefix `RR-`, Google Fonts in 2 files, UI says "Cart".
- Admin has 3 pages (Dashboard, Products, Users); no orders or inquiries screen. Stray `admin/text.md`.
- No `docs/`, `assets/fonts/`, `tokens.css` yet. Detail: `README.md`.

## Hi-fi progress and what to design next
- **Done (Batch 1):** home (differs from the code in places), sign in, sign up, admin dashboard, admin add product.
- **Next:** (1) shop; (2) product detail; (3) contact form incl. invalid + success; (4) one states sheet; (5) basket + checkout; (6) admin products list + delete confirm/blocked; (7) admin users list; (8) account; (9) add user: skip or last (no such screen in the code).
- Design **mobile (390 px) and desktop (about 1280 px)** only; the agent interpolates 700 px. Undesigned pages are adapted from the nearest Figma component and flagged in `docs/COMPARISON.md`.

## Wednesday demo checklist
`display_errors` 0 · `SITE_URL` matches the folder name · clean import of `popcabdb.sql` works on a second laptop (fix the duplicate `CREATE DATABASE`) · admin and customer accounts exist · products show with photos · one invalid-input case ready (contact form) · phone-size screenshot ready · every member can explain their part · demo laptop runs a fresh clone of `main`.

## Database work for next week
Rename `categories`/`collections` (migration + class updates); add missing foreign keys; guests as NULL not `0`; be ready to explain the `order_items` price snapshot for 3NF.

## Next steps
1. User: put `README.md` and `AGENTS.md` in the repo root and the other three in `docs/`. Codex Run 1 (prompt in chat) does the branch, commit, audit, `tokens.css`.
2. User: review `docs/REPORT.md` in Claude chat, merge `design-setup`, tag `handoff-1`.
3. DeepSeek: Slice A (re-skin). Then Run 2 (`docs/COMPARISON.md`) once Batch 1 frames are final.
4. Backend member: fix the dump, `display_errors`, `RR-` prefix.
