# SESSION.md — Pop Cabin status (Tue Nov 17, 2026, after Run 1; build starts now)

**Handoff for agents AND Claude chats.** New Claude chat: upload only this file + `docs/REPORT.md` and say "resume". Agents: read `AGENTS.md`, then run the card named in `docs/PROMPTS.md`. Codebase facts: `README.md`. Tags: **Decided** (user), **Delegated** (user told Claude to decide; user does not get asked).

## Deadlines
- **OOP panel: Wed Nov 18** (front page 40, paper 30, live demo + Q&A 30). Panel time: Wed Nov 18, 1–5 pm (order unknown) → **code freeze = 10:00 AM**. `main` must always be demoable; a slice that is not merge-ready at freeze is NOT merged.
- Tonight (Tue) + tomorrow morning = design-to-code. DB and DSA presentations: next week (DB uses this same database).

## Goal
Adapt the Figma hi-fi onto the existing codebase **without changing major operations**: visual cues and text only.

## Decided (user)
- Palette: walnut `#5A3A29`, honey `#D9A441`, teal `#1F5560`, ink `#241812`, cream `#F3E8D3`. Fonts: Fredoka + Nunito (self-hosted in `assets/fonts/`).
- Codebase = source of truth for features/pages; reservations dropped; cart, checkout, orders stay. Stack: HTML, CSS, PHP, small vanilla JS.
- Wording: "Basket", "Order", "Dibs!". Text only; file/class names unchanged.
- Figma audit scope: High-Fi page only. Product images in Figma are placeholders.
- Builder order: **DeepSeek reads Figma (local MCP, tested first; if down it stops and the user fixes it) and builds; Codex substitutes when DeepSeek runs out.** Claude = orchestrator. **No more questions to the user: agents ask Claude via the report.** If Claude is down, the agent decides by the bulk rules and logs `ASSUMED:`.
- Fonts: the user adds `.ttf` files to `assets/fonts/` (agents use whatever files are there).
- Git: branch per card; agent runs it all; user clicks Merge; commit every step, push every run.

## Delegated (Claude decided, Nov 17; full rules in `AGENTS.md` "Decisions" and `DESIGN.md` §11)
- Bulk rule replaces COMPARISON.md: visuals → match Figma; behavior/data/copy → keep code; Figma sample copy ignored.
- Hover/pressed = Figma overlay tokens; disabled 0.4; breakpoints 700/1000; Inter → Nunito; desktop = restyled current layout.
- Backgrounds/images stay CSS tonight; export is optional (EXPORT card, Codex, only if time).
- Admin orders screen: not built (frontend-only); backend member's call. Do not use `--space-32` / `--space-48`; use the AA-safe text tokens.
- Cards are built in this order: **A (re-skin) → F (per-field errors) → B1 (home, shop, product) → B2 (sign in/up, basket, checkout) → B3 (admin) → C (rest)**. `design-frames` is skipped unless reading a frame through the MCP proves too heavy for DeepSeek.

## Done
- Codebase re-read (commit 837c734); `BaseModel` added (OOP complete). Detail: `README.md`.
- Codex Run 1: `tokens.css` (252 variables, cleaned + 2 AA-safe tokens added by Claude), 9 PNGs, `DESIGN.md` §7-11, `UNBOUND.md` (161 rows).
- Hi-fi frames (mobile 390 only): home, sign in, sign up, admin dashboard, admin add product, product profile, basket, checkout, collection (= shop). None for desktop, states, contact, account, admin lists.

## Run log (agents append ONE line per run: card · branch · done/partial · date)
- Run 1 · design-setup · done · Nov 17
- Run 2 · slice-a · done · Nov 17

## Next (Claude chat)
1. `docs/PROMPTS.md` is STALE (old RUN-2 comparison, single SLICE-B): rewrite it with cards A, F, B1, B2, B3, C + the launcher. 2. `README.md` and `docs/REPORT.md` need no change (agents overwrite REPORT). 3. Fill in the panel time above.

## Backend/other owners (not in the design runs, but worth more points than polish)
`display_errors` = 0 · fix `CREATE DATABASE` twice in `sql/popcabdb.sql` · `RR-` order prefix · admin orders/inquiries screens (optional) · the paper (30 pts) · every member can explain their part.

## Wednesday demo checklist
`display_errors` 0 · `SITE_URL` matches folder name · clean import of `popcabdb.sql` on a second laptop · admin + customer accounts · products with photos · one invalid-input case (contact form, per-field error visible) · phone-size screenshot · demo laptop runs a fresh clone of `main`.

## Database work for next week
Rename `categories`/`collections`; add missing foreign keys; guests as NULL not `0`; explain the `order_items` price snapshot for 3NF.