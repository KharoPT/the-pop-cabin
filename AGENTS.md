# AGENTS.md — The Pop Cabin (frontend / design-to-code)

Updated Nov 17, 2026. Read this first. `docs/SESSION.md` = status. `docs/DESIGN.md` = only when touching styles. `README.md` = codebase map, read only the section you need. Keep replies short.

**Codebase last verified:** Nov 17, commit `837c734` (README sections 1, 2, 3, 5.2, 6.1, 6.3, 7, 7.1, 9.2, 9.5, 10 were rewritten from the code). Re-verify the README after backend pushes.

## Truth order
1. **Figma hi-fi**, exported as text (`docs/DESIGN.md` sections 7-9, `assets/css/tokens.css`, PNGs in `docs/figma/`), for the screens that exist there. Codex reads Figma through the remote Figma MCP (link in `docs/DESIGN.md` section 1). DeepSeek has only the local desktop MCP (`http://127.0.0.1:3845`, needs the Figma desktop app open, fewer tools), so it builds from the text exports and does not rely on live Figma.
2. **The current codebase** for features, pages, and behavior. Reservations are dropped (leader's decision): cart, checkout, and orders stay.
3. Where the two differ, the **user's decision in `docs/COMPARISON.md`** wins (see below).

## Who does what
- **Every prompt starts with "read AGENTS.md first"** (not every agent loads it by itself).
- **Codex (free, remote Figma MCP):** reads Figma and writes everything Figma-related as text (Run 1, Run 2). Edits no PHP.
- **DeepSeek:** builds from the text exports (`tokens.css`, `docs/DESIGN.md`, `docs/COMPARISON.md`, PNGs in `docs/figma/` if it can see images). It does not re-audit Figma. **Fallback:** if a row lacks detail, it may query the local Figma MCP for that one frame only and must record what it read in `docs/REPORT.md` under "Figma details used". The local MCP needs the Figma desktop app open and has fewer tools, so it is never the main source.
- **Claude chat:** plans and reviews reports. Does not edit the repo.
- **The user:** decides rows and clicks Merge on GitHub.

## Scope
- **Frontend only:** page templates, `includes/`, `admin/includes/`, `assets/css/`, `assets/js/`, `assets/fonts/`.
- **Do not edit** `classes/`, `config/`, `actions/`, `sql/`, or any table/column name. Put backend needs in your report.
- Never remove or rename a page, a form field `name=`, or a `csrf_token` input.
- **Stack:** HTML, CSS, PHP templates, small vanilla JS. No frameworks, libraries, or CDN scripts.
- **JS rules:** CSS first; JS only where CSS cannot do it. The page must still work with JS off. Keep it in `assets/js/main.js`, short and commented, and list every addition in the report.
- **Wording:** UI text says "Order" (not "Reservation") and "Basket" (not "Cart"). The "Dibs!" button label stays. Text only: never rename files, classes, or `name=` attributes. Today the UI still says "Cart" in `includes/header.php` and `cart.php` (title, h1, "Update Cart"); messages that live in `actions/` or `classes/` are out of scope: list them under Backend needs.

## Compare → Decide → Build (run per batch of finished Figma frames)
**1. Compare** (Codex, read-only, Figma + repo open together). Write `docs/COMPARISON.md`: one row per Figma frame or element, plus one row per codebase page that has no frame.

| # | Figma frame / element | Codebase file / element | Differences | Type | Recommended side | Why | Decision |
| --- | --- | --- | --- | --- | --- | --- | --- |

- *Differences:* layout, components, copy, form fields, states, behavior (be specific, one line each).
- *Type:* visual / behavior / image / Figma-only (needs backend) / code-only (no frame).
- *Recommended side:* **C** = change the code to match Figma, **F** = change Figma to match the code (the user edits Figma), **S** = skip.
- Default recommendations: visuals (layout, color, type, spacing, component look) → **C**. Behavior and data (fields the database requires, validation, features that exist) → keep the code's behavior, restyle it → **C**. Figma-only features that need backend → **S**, and list under Backend needs. Code-only pages → **C** using the nearest Figma component, flagged. Use **F** only when Figma omits something the code must have.
- Codex also produces `tokens.css`, `docs/DESIGN.md` sections 7-9, and PNG exports. No PHP edits.

**1a. Order of runs.**
- **Run 1 (audit; branch `design-setup`; edits no PHP or existing CSS; needs no codebase changes).** Deliverables: (1) variable collections, modes, styles, components with counts → `docs/DESIGN.md` section 7; (2) `assets/css/tokens.css` per `docs/DESIGN.md` section 4 (names = Figma variable names; only variables that exist; **not linked from any PHP yet**); (3) `docs/DESIGN.md` section 8 (principles not followed); (4) section 9 screen map (frame → PHP file); (5) PNG exports to `docs/figma/<Screen>-<Mobile|Desktop>.png`; (6) section 10 image inventory (product placeholder / background / other); (7) `docs/UNBOUND.md` rows; (8) `docs/REPORT.md` (max 40 lines). If a Figma tool fails, name it and stop; do not guess.
- **Run 2 (comparison, after Run 1 is merged and `handoff-1` is tagged):** `docs/COMPARISON.md` against `handoff-1`; only templates changed after the tag are re-checked.

**2. Decide** (user). Figma and the code will mostly not match, so **every row needs the user's explicit decision (C / F / S). A blank Decision is NOT approval**: the agent builds nothing for it and lists it under "Undecided". The Recommended column is only a suggestion. To save time the user may write one line at the top of `docs/COMPARISON.md` (e.g. `Bulk: accept all recommendations except rows 4, 9, 12`); that line counts as the explicit decision. Rows the agent cannot recommend go under "Questions". The user may upload `docs/COMPARISON.md` to Claude chat for help.

**What C / F / S mean** (one letter per row): **C** = change the **C**ode to match Figma. **F** = change **F**igma to match the code (the user edits Figma, the code stays). **S** = **S**kip: leave both as they are (the feature is not built). Example: Figma Contact has a phone field, the code form has none → Type *Figma-only (needs backend)*, recommended **S**, listed under Backend needs.

**Each Differences cell must state the exact Figma values** (token names, px sizes, copy text), because DeepSeek may not see images or live Figma.

**3. Build** (DeepSeek). Follow the Decision column exactly; skip Undecided rows. No new visual patterns, colors, or fonts beyond the Figma components and tokens. Rows decided **F** wait until the user updates Figma; build them last.

## Images (each one is a row, Type = image)
- **Product photos in Figma are placeholders** for the real photos. Do not export or hard-code them: the code shows the real photos from `uploads/products/` (30 today, managed in the admin). Adapt only the frame (size, ratio, radius, shadow).
- **Decorative / background images in Figma** (hero, sections, CTA, textures) do not exist in the code, which uses CSS gradients and inline SVG patterns. Recommended: export optimized (WebP or JPG, compressed) to `assets/img/`, one file per image named by use (`bg-hero.webp`), referenced from CSS, with a fallback background color so text stays readable if the image fails. Not in `uploads/` (admin photos) and not in `config/content.php` (editable content) unless the user wants it editable.
- Slots that are empty today (tile images, About photos, team photos, CTA image) show the `.img-ph` placeholder; their Figma versions are rows too.

## Hard rules
1. **Bound** value (Figma variable/style) → matching token. **Unbound** → exact raw value + one row in `docs/UNBOUND.md`. Never round to the nearest token.
2. **Hover, pressed, focus, disabled:** where Figma shows a state, Figma wins. Otherwise derive it with the **dynamic** rules in `docs/DESIGN.md` section 6 (one shared rule set; each button variant only sets variables, so light and dark buttons both work).
3. Safe PHP: escape output with `e()`, no SQL in pages, every POST form keeps its CSRF token.
4. Every screen covers **default, empty, invalid, success** (plus blocked/loading per `README.md` 9.5). Invalid = per-field error: red border, message under the field, `aria-invalid="true"`, `aria-describedby`.
5. Mobile-first at 390 px, then 700 px and 1000 px.
6. Unsure? Put it under "Questions" in the report. Do not guess.

## Slices (the OOP panel grades the front page, 40 pts)
- **A, re-skin:** `tokens.css` + self-hosted fonts + old `:root` names aliased to new tokens. Replace the Google Fonts links in BOTH `includes/header.php` and `admin/includes/admin_header.php`. Needs no decisions; start as soon as tokens exist.
- **B, designed screens:** rows with a Figma frame, per the Decision column.
- **C, code-only pages:** cart, checkout, account, order detail, admin products/users lists, `mechanics.php`/`terms.php` shells, per their rows. (There is no admin orders screen in the code today.)

## Loop per slice
1. User commits clean `main`, then `git switch -c slice-a` (b, c).
2. Build; test each touched page: normal data, empty, invalid, keyboard focus.
3. Overwrite `docs/REPORT.md` (max 40 lines): **Done / Files changed / Comparison outcome (row #, built as decided, remaining differences) / Tokens used / Unbound / Derived states / JS added / Not done / Questions / Backend needs**.
4. Stop. The user reviews, runs the demo checklist (`docs/SESSION.md`), merges.

## Codebase audit (separate run, own branch, optional)
Read-only `docs/CODEBASE_AUDIT.md`: redundant files/functions and places where README/docs disagree with the code. **No deletions** until the user approves each item. Not after the code freeze (time in `docs/SESSION.md`).

## Git (the agent runs all of it; the user only clicks Merge)
**Preflight, before touching any file (every run):**
1. `git status`: uncommitted changes that are not yours → stop and report.
2. `git fetch origin`, then `git log --oneline HEAD..origin/main`. State in `docs/REPORT.md` whether teammates pushed (commits and files touched).
3. If `main` moved: `git switch main`, `git pull`, `git switch <branch>`, `git merge main`. Conflict in a frontend file you own (`assets/`, templates you are editing): keep both sides' intent and list it in the report. Conflict anywhere else (`classes/`, `config/`, `actions/`, `sql/`): **stop and report**, change nothing.
4. Use the branch for this run: `design-setup` (Run 1), `slice-a`, `slice-b`, `slice-c`. Never commit to `main`.

**Before finishing:** fetch again; if `main` moved, merge it again and re-test the touched pages. Commit with clear messages, then `git push -u origin <branch>`. Never force-push; never push or merge to `main`. The user merges through a GitHub pull request (squash).

## Context hygiene (free-tier limits)
Cite path + line range; never paste whole files. One slice per run. If the chat gets long, rewrite `docs/SESSION.md`.
