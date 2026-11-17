# Card B1 report — home, shop, product (`slice-b1`)

- **Done:** Re-skinned home, shop and product to the hi-fi frames at 390 px; desktop widths follow the same components. Data and behaviour untouched.
- **Files changed:** `index.php`, `shop.php`, `product.php`, `includes/product_card.php`, `assets/css/style.css`.
- **Git preflight:** `slice-b1` cut from `origin/main` `a5b8c6f` (slice-f merged). Final fetch: no new commits.
- **Figma read (MCP):** `Mobile/Public` 243:5030 (design context + screenshot), `Mobile/Collection` 365:6802 and `Mobile/Product Profile` 370:7680 (screenshots); cross-checked the three PNGs.
- **Home:** log-wall hero → dark CSS gradient; cream/honey title, real stat cards, teal Shop Collection pill; wooden signs → plain Fredoka; Discover cards 290 px / r16 with glass label; honey View All; team = dark forest band with 2×2 glass cards; reviews/CTA restyled.
- **Home kept/dropped:** no Overview section (its Figma copy is Lorem placeholder); no hero photo (backgrounds stay CSS); shared header/footer still the old wood chrome (not in this card).
- **Shop:** 2-up cream cards r16, image r12, dark Fredoka name, category/stock lines, plain price; honey Filter & Sort pill; "N Pops"; pager pills. Added `Stock: N` from real data.
- **Shop not done:** Figma's Add to Basket / Place Order buttons stay off the cards (they need the cart flow; behaviour kept).
- **Product:** cream product card, 32 px title, stock line, 28 px price, 4 mechanic pills (active honey), Dibs! walnut pill, Details 22 px, Reviews & Comments + honey Write a Review, dark review cards, related grid, honey Back to Collection.
- **Product not done:** Place Order button and lock icons on the 3 disabled tabs (behaviour/markup, not visual); review empty state unchanged.
- **Tokens/unbound added:** none; reused `--color-highlight-default`, `--color-action-primary`, `--color-bg-inverse`, `--color-feedback-error` etc.
- **JS added:** none.
- **Tested:** 390 px screenshots of all three pages (normal, shop empty search, product no-reviews); all 200, no PHP warnings. An editor step added a UTF-8 BOM to shop.php/product.php — stripped and re-tested (`5bf1d31`).
- **LAYOUT NOT VERIFIED:** none.
- **Questions:** 1) Home Overview: build it once real copy exists, or leave it out? 2) Product Place Order + lock icons: add in B2 or keep out? 3) Restyle the shared header/footer in B2/B3, or now?
- **Backend needs:** none.
