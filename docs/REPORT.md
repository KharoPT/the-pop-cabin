# Card B1 report — home, shop, product + card H header/footer (`slice-b1` redo)

- **Done:** Reran B1 on the existing branch. Rebuilt home to `Mobile/Public`, rechecked shop/product, and added the missing card H header/footer (H was not on `main`; it was built on `slice-h` and merged here).
- **Files changed:** `includes/header.php`, `includes/footer.php`, `index.php`, `shop.php` / `product.php` (first-run restyle), `assets/css/style.css`, `assets/js/main.js`, 18 files in `assets/img/`.
- **Git preflight:** Continued on `slice-b1` (no force-push). `main` unchanged at `a5b8c6f`; final fetch clean.
- **Figma read (MCP):** `Mobile/Public` 243:5030 design context (header, hero, overview, discover, devs, footer) + `Mobile/Collection` 365:6802 and `Mobile/Product Profile` 370:7680 screenshots; PNGs cross-checked.
- **Exported (`assets/img`):** `bg-hero.jpg`, `bg-devs.jpg`, `object-logo.png` (transparent), `hero-notch-left/right.svg`, `footer-notch.svg`, `logo-mark.svg`, `icon-burger/search/close.svg`, four `team-*.png`, social SVGs. 26–143 KB; product photos were not exported.
- **Home:** dark hero with real background, notch cutouts and a cream tab around the teal pill; overview added with measured 32/24/14 type and the exported sign illustration; Discover Pops 2-up 190×240 cards with arrows and honey View All; Devs band uses `bg-devs.jpg`, 172×135 glass cards, real names/roles/quotes/portraits; “Ready to order”, extra reviews and featured carousel removed (not in Figma).
- **Home differences left:** stats use real counts (30 / 0), not Figma’s 150+/30+ samples; title is solid cream (Figma has a cream→muted gradient); overview illustration keeps its baked cream background.
- **Shop:** rechecked at 390; 2-up cream cards, honey Filter & Sort, stock line, pager. Still no Add to Basket / Place Order on cards (needs the cart flow; behaviour kept).
- **Product:** rechecked at 390; cream product card, mechanic pills, honey Write a Review, dark review cards, related grid.
- **Product differences left:** no Place Order button and no lock icons on the 3 disabled tabs (markup/behaviour, not visual).
- **Header/footer:** glass 48 px nav with exported burger/mark/search and honey Sign In; footer uses `footer-notch.svg`, real wordmark, 22 px headings.
- **Tokens/unbound added:** none (exported images only; spacing uses existing tokens/measured values).
- **JS added:** one short block in `main.js` for the category carousel arrows (scrolls the row; row still scrolls with JS off).
- **Tested:** 390 px home/shop/product screenshots; home also at 1000 px; all public pages 200 with no PHP warnings; no PHP BOM.
- **LAYOUT NOT VERIFIED:** nav open state (no frame).
- **ASSUMED:** card H was not merged, so it was built and merged here; overview copy is new mock text with Figma’s sentence count; removed home sections the frame does not show.
- **Questions:** 1) Add shop/product buy buttons in B2 or keep out? 2) Keep the baked cream rectangle behind the overview illustration, or cut it out?
- **Backend needs:** seed the real team rows (names/roles/photos) in `config/content.php` / DB; the fallback array in `index.php` is used until then.
