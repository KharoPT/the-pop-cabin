# AGENTS.md — The Pop Cabin (frontend / design-to-code)

Updated Nov 17, 2026. Read this first, then `docs/SESSION.md` (status), then your task card in `docs/PROMPTS.md`. `docs/DESIGN.md` only when touching styles. `README.md` = codebase map: read only the section you need. Keep replies short.

## Roles
- **Claude chat = orchestrator.** Plans, decides open questions, reviews `docs/REPORT.md`. Edits no repo files. **Agents never ask the user questions**: put them under "Questions" in the report (Claude answers).
- **DeepSeek = Figma reader AND builder**: reads its one frame through the local Figma MCP, then builds. **Codex = substitute** when DeepSeek runs out. Same rules for both.
- **Claude unavailable?** (the user says so in the launcher) Do not wait: decide with the bulk rules below and log each guess as `ASSUMED: <what>` in the report. Claude reviews it later.
- **The user** runs the launcher, uploads `docs/REPORT.md` to Claude, clicks Merge on GitHub.
- **The chat is not memory.** Every agreed rule or decision lives in the md files.

## Figma MCP check (first step of any card that reads a frame)
Read one small node from `http://127.0.0.1:3845`. If it fails: **STOP**, change nothing, write `MCP DOWN: <error>` (3 lines) in `docs/REPORT.md`, tell the user to open the Figma desktop app / enable the server, and wait. This is the only time an agent talks to the user. Never fall back to guessing layouts.

## Truth order
1. **Figma hi-fi** for the 9 screens that exist: `docs/FRAMES.md` (if present) > `docs/DESIGN.md` §7-9 > `assets/css/tokens.css` > PNGs in `docs/figma/` (if you can see images) > local Figma MCP (`http://127.0.0.1:3845`, desktop app must be open; one frame only; record what you read in the report).
2. **The codebase** for features, pages, data and behavior. Reservations are dropped; cart (UI word "Basket"), checkout, orders stay.
3. **Cannot see the frame?** Do NOT guess layout. Restyle with tokens, keep the existing structure, and write `LAYOUT NOT VERIFIED: <page>` in the report. Never invent values.

## Decisions (bulk rule, set by the orchestrator, replaces the row-by-row COMPARISON.md)
- **Visuals** (color, type, spacing, radius, shadow, component look, section order, copy of labels) → **change the code to match Figma.**
- **Behavior and data** (form fields, validation, features, real product data, real copy) → **keep the code**, restyle it. Figma sample copy (Lorem ipsum, repeated products, "1-16 out of 11 Pops", ₱449 × 2 = ₱800, repeated "Movies" chips) is placeholder: never copy it.
- **Figma-only features** needing backend (new fields, new pages) → skip; list under "Backend needs".
- **Pages without a frame** (contact, about, account, order detail, admin lists, delete confirm, mechanics, terms) and **desktop widths** → restyle from the nearest Figma component and tokens.
- Wording: "Basket" (not Cart), "Order" (not Reservation), "Update Basket", "Dibs!" keeps its exclamation mark. Lock icons only on the 3 disabled tabs. Keep the "Out of stock" dashboard card. Text only: never rename files, classes, or `name=` attributes. Wording inside `actions/` or `classes/` is out of scope: list it under Backend needs.
- **Per page, log differences you found and what you did** (max 6 lines per page) in the report. This replaces COMPARISON.md.

## Scope
- **Allowed:** page templates and their top-of-file input handling (only to add per-field errors), `includes/`, `admin/includes/`, `assets/css/`, `assets/js/`, `assets/fonts/`, `assets/img/`, `docs/`.
- **Never edit:** `classes/`, `config/`, `actions/`, `sql/`, table/column names. Never remove or rename a page, a form field `name=`, or a `csrf_token` input.
- **Stack:** HTML, CSS, PHP templates, small vanilla JS. No frameworks, libraries or CDN scripts. CSS first; JS only where CSS cannot; the page must work with JS off; keep JS in `assets/js/main.js`, short and commented, and list additions in the report.

## Hard rules
1. **Bound** Figma value → matching token. **Unbound** → exact raw value + one row in `docs/UNBOUND.md`. Where a raw spacing/padding/radius value exactly equals an existing token, use the token. Never round to the nearest token. Gradient/effect rows: simplify per component.
2. **Do not use** `--space-32` and `--space-48` (they break the 4 px scale). Use `--color-text-muted-aa` and `--color-feedback-success-text` for small TEXT (the base muted/success colors fail 4.5:1). Details: `docs/DESIGN.md` §11.
3. **States:** where Figma shows one, Figma wins; otherwise use the dynamic rules in `docs/DESIGN.md` §6 (hover 0.08 / pressed 0.12 overlay, disabled 0.4, `:focus-visible` 2px outline, transitions off under `prefers-reduced-motion`).
4. **Every screen covers default, empty, invalid, success.** Invalid = per-field error: red border, message under the field, `aria-invalid="true"`, `aria-describedby`; keep the existing top banner too. Every input has a `<label>`.
5. **Safe PHP:** escape output with `e()`, no SQL in pages, every POST form keeps its CSRF token.
6. **Mobile-first at 390 px**, then 700 px and 1000 px (the code's breakpoints; Figma's 390/768/1280 are frame widths).
7. Images: product photos in Figma are placeholders (real ones come from `uploads/products/`); never export them. Backgrounds stay CSS tonight; `assets/img/` only via the EXPORT card.
8. Unsure? Put it under "Questions". Do not guess.

## Loop per card
1. **Git preflight** (below). Create the card's branch from up-to-date `main`.
2. Build. Test each touched page at 390 px: normal data, empty, invalid, keyboard focus. Commit after each finished page/step.
3. Overwrite `docs/REPORT.md` (**max 30 lines**): Done · Files changed · Differences found and done (per page) · Tokens/unbound added · JS added · Not done · LAYOUT NOT VERIFIED · Questions · Backend needs. Append ONE line to the "Run log" in `docs/SESSION.md`.
4. Push the branch. **Stop.** The user reviews, merges, and sends the report to Claude.
- **Running out of quota or time:** commit what is done as `WIP: <what>`, push, list the rest under "Not done". **Resuming** (new agent or new session): read `docs/REPORT.md` "Not done" and the Run log, continue on the same branch.

## Git (the agent runs all of it; the user only clicks Merge)
- Preflight: `git status` (changes only in `docs/`, `assets/css/tokens.css`, `assets/fonts/` are the user's handoff files: commit them first on the card branch as `chore: handoff context`; any other change → stop and report) → `git fetch origin` → `git log --oneline HEAD..origin/main` (note in the report if teammates pushed). If `main` moved: `git switch main && git pull && git switch <branch> && git merge main`. Conflict in a frontend file you own: keep both sides' intent and report it. Conflict in `classes/`, `config/`, `actions/`, `sql/`: **stop, change nothing, report.**
- Branches: `slice-a`, `slice-f`, `slice-b1`, `slice-b2`, `slice-b3`, `slice-c`, `design-frames`, `design-images`. Never commit to `main`. Never force-push. The PR is squashed on merge, so many small commits are fine.
- Before finishing: fetch again; if `main` moved, merge it and re-test touched pages; `git push -u origin <branch>`.

## Context hygiene (free-tier limits)
Cite path + line range; never paste whole files. One card per run. Do not re-read files you already read this run. If `docs/SESSION.md` gets long, compress it.