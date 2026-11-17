# Card F report — per-field errors (`slice-f`)

- **Done:** Contact, sign in, sign up, checkout and the admin product form now show the invalid state per field: red border (`--color-feedback-error`), message under the field, `aria-invalid="true"`, `aria-describedby`. The existing top banner is kept.
- **Files changed:** `contact.php`, `login.php`, `register.php`, `checkout.php`, `admin/product_form.php`, `assets/css/style.css`, `assets/js/main.js`.
- **Git preflight:** `slice-f` cut from `origin/main` `cfd798c` (slice-a merged); final fetch showed no new commits. Card A decisions committed first as `683b5d6`.
- **Contact:** message, first name, last name, email mapped; an attachment error shows under the dropzone; banner + inline both show (see Questions).
- **Sign in:** identifier and password mapped; wrong credentials stay on the banner only (no single field is at fault). `novalidate` added so server errors render.
- **Sign up:** first/last name, email, password, confirm, terms mapped; duplicate-email error goes under email; `novalidate` added.
- **Admin product form:** name, franchise, price, description and photo upload mapped; admin banner kept; `novalidate` added.
- **Checkout:** server validation lives in `actions/place_order.php` (not editable), so the per-field state is JS in `main.js` (checks required fields on submit, sets aria/messages, clears on input); `novalidate` added. With JS off the server banner still catches empty fields.
- **Tokens/unbound added:** none; error text/border use `--color-feedback-error` (5.4:1, AA).
- **Moss → success text:** no `--moss`/success **text** uses exist in the touched code (only borders/fills), so nothing to fix here.
- **JS added:** one commented block (~45 lines) for checkout per-field errors; `node --check` passes.
- **Tested:** empty + invalid + valid POSTs for contact, login, register and product form; checkout invalid in headless Chrome at 390 px. Touched pages 200, no PHP warnings. Test rows cleaned up (1 inquiry, the test user + cart, the seeded cart item); product 2 update was a no-op.
- **Not done:** product "Add" with no photo still fails — pre-existing `products.image_url NOT NULL` while the form allows no photo; outside card F.
- **LAYOUT NOT VERIFIED:** none (no Figma frame for F).
- **Questions:** 1) Banner + inline repeat the same text — keep both, or make the banner a one-line summary? 2) Product add needs a photo (or schema default): backend owner? 3) Should wrong-credential login mark the password field, or stay banner-only?
- **Backend needs:** none in this card.
