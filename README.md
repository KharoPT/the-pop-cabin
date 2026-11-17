# The Pop Cabin

A Funko Pop collectibles shop website for the client **The Pop Cabin**, built in PHP (OOP) with a MySQL/MariaDB database. Customers browse the collection, reserve ("Dibs") pops through a cart, and send inquiries. An admin manages products and views users from a mobile-first admin panel.

This is a school project (Universidad de Dagupan, School of Information Technology Education) that doubles as a real client deliverable. It is graded as the **OOP final project** and shares its database with the **Information Management and Database final project**.

> **Who this README is for:** teammates, the frontend designer, AI coding agents, and the panel/instructor. Every statement below was checked against the code in this folder. Where something is a guess, it says so.

### To collaborators

Use the correct SITE_URL always, check nyo rin lagi ung name ng folder and compare to site url value in config/config.php.

Don't forget to import the sql file in sql folder, and when working with databases dont forget to export and replace the sql file in the sql folder.
- Alex

### Background

The Pop Cabin is a funko pop online selling business. It is run through Facebook via posts, where deals are made via private messages and comments on posts.

This web system aims to create a promotional website for The Pop Cabin to eliminate the limitation on customers of difficulty in viewing products in a facebook page.

### Team

Abalos, Caburnay, De Guzman, Estrada

---

## 1. At a glance

| Item | Value |
| --- | --- |
| Language | PHP 8+ (uses `match`, `str_contains`, union return types, typed properties) |
| Database | MySQL / MariaDB, database name `popcabdb`, accessed with PDO |
| Server | Apache via XAMPP (or the PHP built-in server for quick tests) |
| Frontend | Server-rendered HTML + one stylesheet (`assets/css/style.css`) + one small script (`assets/js/main.js`) |
| Fonts | Today: Cabin + Zilla Slab from Google Fonts (linked in two files). Planned: Fredoka + Nunito, self-hosted in `assets/fonts/` (folder not created yet) |
| Frameworks | None (no Composer, no Bootstrap, no Tailwind) |
| Size | about 4,450 lines of PHP/CSS/JS (78 files including 30 product photos and the SQL dump) |
| Design target | Mobile-first (wireframes are 390 px wide); wider layouts start at 700 px and 1000 px |

---

## 2. Quick start (XAMPP on Windows/Mac)

1. **Install XAMPP**, start **Apache** and **MySQL**.
2. **Copy the project folder** into `xampp/htdocs/` and keep the name `the-pop-cabin`. The folder name is part of the URL.
3. **Check `config/config.php`:** `SITE_URL` must equal `http://localhost/<folder name>` (default now `http://localhost/the-pop-cabin`). `DB_USER` / `DB_PASS` default to XAMPP's `root` and an empty password.
4. **Import `sql/popcabdb.sql`** (phpMyAdmin, Import tab). It is the full schema plus data: all tables, 30 products, franchises, categories and one admin account. There are no migration files any more. Known problem: the dump contains `CREATE DATABASE popcabdb;` twice, which can stop the import (see 6.3).
5. **Open** `http://localhost/the-pop-cabin/index.php`. Admin login: the `admin` user from the dump (ask the backend member for the password; never write it in a file).
6. **Writable folders:** `uploads/products/` (exists, 30 photos). `storage/inquiries/` is created automatically by the first contact-form attachment.
7. After any database change, **export and replace `sql/popcabdb.sql`** (team rule, see "To collaborators").

**Quick alternative:** `php -S localhost:8000` in the project folder, with `SITE_URL` = `http://localhost:8000` (testing only; `.htaccess` rules are ignored).

---

## 3. Folder map

```
the-pop-cabin/
├── index.php  shop.php  product.php         Public storefront pages
├── cart.php  checkout.php                   Cart and checkout (UI word: Basket)
├── login.php  register.php  logout.php      Authentication
├── account.php  order_detail.php            Customer area
├── contact.php  about.php                   Information pages
├── mechanics.php  terms.php                 Shells ("not published yet")
├── README.md  AGENTS.md                      This guide + rules for AI agents
│
├── actions/   add_to_cart  update_cart  remove_from_cart  place_order  submit_review   (.php)
├── admin/     index  products  product_form  product_delete  users  (.php)
│              oop_demo.php (OOP demo, not in the menu)   text.md (stray note)
│              includes/ admin_header.php  admin_footer.php
├── classes/   BaseModel.php (abstract parent) + Product Cart Order User Review Inquiry (.php), .htaccess
├── config/    config.php  database.php  content.php
├── includes/  header.php  footer.php  product_card.php  functions.php
├── assets/    css/style.css (+ tokens.css, to be created)   js/main.js   img/logo.svg   (no fonts/ folder yet)
├── docs/                                    SESSION.md  DESIGN.md  UNBOUND.md, agent outputs (COMPARISON.md, REPORT.md), figma/ PNG exports
├── sql/       popcabdb.sql  (full schema + data; the only SQL file)
├── uploads/products/                        30 product photos (tracked in git)
└── storage/inquiries/                       created on first attachment; not in the repo
```

---

## 4. How a page request works (read this once)

Every page follows the same recipe. Understanding it makes the whole project easy to read.

```
Browser asks for shop.php
   │
   ├─ 1. require config/config.php      → constants, session starts, database + helpers load
   ├─ 2. require classes/Product.php    → pull in the class this page needs
   ├─ 3. Read and clean the input       → $_GET / $_POST, trimmed, checked, typed
   ├─ 4. Ask the class for data         → $productModel->getAll($filters, ...)
   ├─ 5. Set $pageTitle, $bodyClass     → variables the header reads
   ├─ 6. require includes/header.php    → prints <head>, nav, flash messages, opens <main>
   ├─ 7. Print the page HTML            → PHP loops and ifs mixed with HTML
   └─ 8. require includes/footer.php    → closes </main>, prints footer, loads main.js
```

**Pages that save data** (cart, login, register, contact, admin forms) use the *Post → Redirect → Get* pattern: handle the form, then `redirect()` so refreshing the page never re-submits it. The result is shown through a **flash message** (see glossary).

---

## 5. Pages, who can see them, and what they do

### 5.1 Public and customer pages

| Page | File | Access | Main class | What it does |
| --- | --- | --- | --- | --- |
| Home | `index.php` | Everyone | `Product`, `Review` | Hero, "Exclusive Pops" carousel (products flagged *Show on home page*), category and franchise tile rows, "Words from buyers" (hidden until reviews exist), team section, call to action |
| Shop | `shop.php` | Everyone | `Product` | Product grid (16 per page), filter by franchise / category / price, sort, search, pager |
| Product | `product.php?slug=` | Everyone | `Product`, `Review` | Photo with zoom, price, rating, "Dibs!" button, details, reviews, "you may also like" |
| Cart | `cart.php` | Everyone (guest or user) | `Cart` | Change quantities, remove items, shipping summary |
| Checkout | `checkout.php` | Logged in | `Cart`, `User` | Shipping form prefilled from the profile |
| Place order | `actions/place_order.php` | Logged in | `Order` | Creates the order inside a database transaction |
| Order detail | `order_detail.php?order=` | Owner or admin | `Order` | Items, totals, shipping address, status |
| Account | `account.php` | Logged in | `User`, `Order` | Edit profile, order history |
| Login / Sign up | `login.php`, `register.php` | Guests | `User` | Auth forms, "remember me" (30 days), auto-login after sign-up |
| Contact | `contact.php` | Everyone | `Inquiry` | Message form with optional file upload (PNG, JPG, JPEG, DOCX, TXT, MD up to 10 MB) |
| About | `about.php` | Everyone | none | Text, photo gallery, map, address (all from `config/content.php`) |
| Mechanics, Terms | `mechanics.php`, `terms.php` | Everyone | none | Empty shells saying "This page has not been published yet." |

### 5.2 Admin pages (role must be `admin`)

| Page | File | CRUD role | What it does |
| --- | --- | --- | --- |
| Dashboard | `admin/index.php` | Read | Three stat cards (Products, Users, Out of stock) and two buttons (Add product, All products). No orders shown |
| Products | `admin/products.php` | **R**ead | List with search, franchise filter, stock filter, pager (20 per page), Edit and Delete buttons |
| Product form | `admin/product_form.php` | **C**reate / **U**pdate | Photo upload, name, franchise, edition, price, stock, categories (many-to-many), description, "Show on home page" (edit only) |
| Delete product | `admin/product_delete.php` | **D**elete | Confirmation screen; blocked ("still referenced by existing records", hint: set stock to 0) when the database refuses the delete |
| Users | `admin/users.php` | Read | List only; no edit, delete, or role change screens |
| OOP demo | `admin/oop_demo.php` | Read | Lists User, Product, Review, Inquiry with parent class and `describe()` text; not linked in the menu |

> **No admin orders screen.** Customers can still place orders (checkout, order detail, account history work), but no admin page lists or updates them: `Order::updateStatus()`, `getAllWithCounts()` and `getPending()` are unused. Confirm whether this is intended.
>
> **Naming (Nov 16):** reservations are dropped (leader's decision). The record is an **order**; UI text says "Order" and "Basket". The "Dibs!" label stays. Code comments in `Order.php` and `main.js` still say "reservation".

---

## 6. Database

### 6.1 Tables (what the code expects)

> The schema now lives in `sql/popcabdb.sql` (phpMyAdmin export, Nov 16 18:32). The table below was written before that and was not re-verified column by column: confirm types against the dump and the ERD before documenting.

| Table | Purpose | Columns the code uses |
| --- | --- | --- |
| `users` | Accounts | `user_id` PK, `username`, `email`, `password_hash`, `full_name`, `phone`, `address`, `city`, `postal_code`, `country` (these five are NOT NULL), `user_role` ENUM('customer','admin') |
| `categories` | **Franchises** (Marvel, DC...). Named "categories" for historical reasons | `category_id` PK, `category_name`, `slug` |
| `products` | The pops | `product_id` PK, `category_id` FK, `name`, `slug`, `price`, `stock_quantity`, `condition_grade` (kept NULL), `is_authenticated` (kept 0), `is_featured`, `image_url`, `created_at`, `description`, `edition` |
| `collections` | **Category pills** (Movies, Anime, Games) | `collection_id` PK, `collection_name`, `slug` UNIQUE |
| `product_collections` | Junction: a product can be in several collections | `product_id` + `collection_id` (composite PK) |
| `reviews` | Buyer reviews | `review_id` PK, `product_id`, `user_id`, `rating` (1-5), `comment`, `is_anonymous`, `created_at`; UNIQUE (`product_id`, `user_id`) = one review per buyer per pop |
| `inquiries` | Contact form messages | `inquiry_id` PK, `user_id` (nullable), `first_name`, `last_name`, `email`, `message`, `attachment_path`, `attachment_name`, `created_at` |
| `carts` | One cart per browser or user | `cart_id` PK, `user_id` (0 = guest), `session_id` |
| `cart_items` | Lines in a cart | `cart_item_id` PK, `cart_id`, `product_id`, `quantity`, `added_at` |
| `orders` | Placed orders | `order_id` PK, `order_number`, `user_id`, `order_status`, `subtotal`, `shipping_fee`, `total_amount`, `shipping_name/address/city/postal_code/country`, `created_at` |
| `order_items` | Lines in an order (a price snapshot) | `order_id`, `product_id` FK, `product_name`, `price`, `quantity` |

### 6.2 Relationships

```
categories (franchise) 1 ───< products >───< collections       (via product_collections, M:N)
users      1 ───< orders 1 ───< order_items >─── 1 products
users      1 ───< reviews >─── 1 products                      (UNIQUE: one review per user per product)
users      1 ───< inquiries                                    (user_id optional)
carts      1 ───< cart_items >─── 1 products
users      1 ─── 0..1 carts   (guest carts use user_id = 0 plus a session token)
```

Two design decisions worth knowing:

- **A product has exactly one franchise** (`products.category_id`) but **many categories** (junction table). The code comments call this out because the wireframes label the pills "Categories".
- **`order_items` keeps its own copy of the product name and price**, so an old order stays correct even if the product later changes. Because `order_items` has a foreign key to `products`, a product that appears in any order **cannot be deleted** (the admin delete screen explains this).


### 6.3 Dump findings (Nov 17)

- **Data:** 30 products (each with a photo in `uploads/products/`), 11 franchises in `categories` (ids 5-15), 3 collections (Movies, Anime, Games), 30 product-collection links, 1 user (the admin), 4 carts. No orders, reviews, or inquiries yet.
- **Duplicate `CREATE DATABASE popcabdb;`** (lines 23 and 27, no `IF NOT EXISTS`): the import can fail at the second one or when the database already exists. Fix: keep a single `CREATE DATABASE IF NOT EXISTS popcabdb;`.
- **Foreign keys present:** `cart_items` (2), `orders` (1), `order_items` (2), `products` (1). **Missing:** `reviews`, `inquiries`, `product_collections`, `carts.user_id` (guests stored as 0).
- **Naming:** `categories` holds franchises and `collections` holds categories (rename planned after Wednesday).
- `order_items.product_name` / `price` are a deliberate price snapshot: be ready to explain it for 3NF.

---

## 7. The OOP layer (`classes/`)

Each class wraps one area of the database. Pages never write SQL directly; they call these methods. All six model classes extend the abstract `BaseModel`.

| Class | Responsibility | Notable public methods |
| --- | --- | --- |
| `Database` (`config/database.php`) | One shared PDO connection (**Singleton**: constructor is private) | `getInstance()`, `getConnection()` |
| `BaseModel` (`classes/BaseModel.php`) | **Abstract parent** of every model: holds the protected PDO connection, forces each child to name its table, shares two methods | abstract `table()`, `countAll()`, `describe()` |
| `Product` | Listing, searching, filtering, sorting, pagination, featured and related items, create / update / delete, stock | `getAll()`, `countAll()`, `getBySlug()`, `getById()`, `getFeatured()`, `getRelated()`, `create()`, `update()`, `delete()` (transaction), `decrementStock()`, `setCollections()`, `uniqueSlug()` |
| `Cart` | Guest and logged-in carts stored in the database; merges the guest cart into the user's cart at login | `addItem()`, `updateItem()`, `removeItem()`, `getItems()`, `getTotal()`, `getItemCount()`, `clear()` |
| `Order` | Creates an order and its lines in **one transaction**, lowers stock, rolls everything back if any item is short | `create()`, `getByOrderNumber()`, `getItemsByOrderId()`, `getByUserId()`, `getAllWithCounts()`, `getPending()`, `countPending()`, `updateStatus()` |
| `User` | Register, login, profile, username generation | `register()`, `login()`, `getById()`, `updateProfile()`, `generateUsername()`, `emailExists()`, `getAll()` |
| `Review` | Reviews with anonymous display names and rating summary | `getByProduct()`, `summary()`, `getLatest()`, `create()`, `userHasReviewed()` |
| `Inquiry` | Contact messages and safe attachment storage | `create()`, static `saveAttachment()`, constant `ALLOWED_EXTENSIONS` |

### 7.1 OOP concepts: honest status (for the rubric)

The OOP rubric awards **7 pts (code) + 7 pts (documentation) + 6 pts (presentation)**. Since the Nov 17 commit "Biggest Changes" the code has a parent class, so inheritance, abstraction and polymorphism now exist.

| Concept | Status | Evidence |
| --- | --- | --- |
| Encapsulation | **Present** | `protected PDO $db` in `BaseModel` (only model classes reach the connection); `Cart` hides `$cartId` behind `getCartId()` |
| Inheritance | **Present** | `User`, `Product`, `Cart`, `Order`, `Review`, `Inquiry` all `extends BaseModel` and inherit `$db`, the constructor, `countAll()`, `describe()` |
| Abstraction | **Present** | `abstract class BaseModel`; `abstract protected function table()` forces each child to name its table |
| Polymorphism | **Present** | `describe()` is overridden by `Order`, `Product`, `User`; `Cart`, `Review`, `Inquiry` inherit the default. `Product::countAll(array $filters = [])` overrides the parent with an optional filter. `admin/oop_demo.php` calls `$model->describe()` on four models |
| Singleton | **Present** | `Database::getInstance()` |
| Exceptions / transactions | **Present** | `Order::create()` throws `RuntimeException` and rolls back; `Product::delete()` rolls back on failure |
| Interface / namespaces / autoload | **Not yet** | Classes are loaded with `require_once`; optional extras |

Defense tip: be ready to say where `describe()` and `countAll()` are used on the real site (the dashboard uses `countAll()`), not only in the demo page.

---

## 8. Security features already in place

| Feature | Where | Why it matters |
| --- | --- | --- |
| **Prepared statements** (PDO) | All `classes/*.php` | Blocks SQL injection. `LIMIT` values are cast to integers first |
| **Output escaping** `e()` | Every page | Blocks cross-site scripting (XSS) |
| **CSRF tokens** | Every POST form, checked by `verifyCsrfToken()` | Stops other sites from submitting forms for a logged-in user |
| **Password hashing** | `User::register/login` | `password_hash` / `password_verify`; passwords limited to 8-72 characters (bcrypt ignores the rest) |
| **Session hardening** | `config.php`, `login.php` | `httponly`, strict mode, `session_regenerate_id` at login/logout |
| **Role gate** | `requireAdmin()`, `requireLogin()` | Admin pages are unreachable for customers and guests |
| **Safe redirects** | `safeNextPath()` | `?next=` only accepts paths inside this site |
| **Upload checks** | `saveProductImage()`, `Inquiry::saveAttachment()` | Checks real file type (not just the name), size limits, random file names; `.htaccess` blocks scripts in `uploads/` and all web access to `storage/` |
| **Stock safety** | `Cart`, `add_to_cart.php`, `Order::create()` | The cart can never hold more than the stock; the order is re-checked at purchase time |

**Before the final demo:** in `config/config.php`, set `display_errors` to `0` (the file says so itself). Error text on screen is hard to explain in front of a panel.

---

## 9. Frontend guide (for the designer and design-to-code work)

### 9.1 Stylesheet structure (`assets/css/style.css`, 763 lines)

One file, mobile-first, in this order:

1. `:root` design tokens (colors, fonts, wrap width, radius, wood textures)
2. Reset and base elements
3. Buttons, "wooden signs" (the dark label bars), tags, chips, image placeholder
4. Header, flash messages and toast, footer
5. Home page sections
6. Shop, product, contact, about, auth pages (the newer, wireframe-based pages)
7. Form fields (shared)
8. **"Pages kept from the original build":** cart, checkout, account, order detail, status pills
9. Admin
10. Wider screens: `min-width: 700px` and `1000px`, one `max-width: 520px` tweak, and a `prefers-reduced-motion` rule

### 9.2 Tokens that exist today (`:root`)

| Group | Tokens |
| --- | --- |
| Palette | Old wireframe-pass tokens (`--birch`, `--walnut #3b2a1f`, `--ember`...), being replaced by the Figma palette. See `docs/DESIGN.md` section 3 and `tokens.css` |
| Fonts | `--font-body` and `--font-display`, still Cabin and Zilla Slab (to become Nunito and Fredoka) |
| Layout | `--wrap: 1100px`, `--radius: 6px` |
| Textures | `--grain`, `--planks`, `--plank-dark`, `--log-wall` |

**Not tokenized yet** (hard-coded values scattered in the CSS): status-pill colors (blue, purple, green, red tints), the danger red `#9b2f1d`, the placeholder gray `#8d7a68`, shadows, all spacing, font sizes, border widths, and z-index values. There is no spacing or type scale.

### 9.3 Two generations of markup (important)

The site was built in two passes, so two class vocabularies coexist:

| Generation | Pages | Typical classes |
| --- | --- | --- |
| **Newer** (follows the wireframes) | home, shop, product, contact, about, login, register, all admin pages | `.wrap`, `.sign`, `.btn-dark`, `.btn-outline`, `.pop-card`, `.field`, `.admin-*` |
| **Legacy** (original build, restyled lightly) | cart, checkout, account, order detail | `.container`, `.page-header`, `.section`, `.btn-primary`, `.btn-ghost`, `.cart-*`, `.form-row` |

`.btn-primary` and `.btn-ghost` are aliases of `.btn-dark` and `.btn-outline` in the CSS, and `.container` shares a rule with `.wrap`. The legacy pages are the first candidates for redesign.

### 9.4 JavaScript (`assets/js/main.js`, optional enhancement)

Every feature is progressive: pages still work without JavaScript.

- Hamburger menu (public) and admin menu, with Escape key and scrim
- Dismissible admin banners
- Carousels with dot pagers (home)
- Favorites hearts, stored in the browser's `localStorage` only (**not** in the database)
- "Dibs / Steal / Claim / Cabin Lock" tabs on the product page (only **Dibs** is live; the others are disabled)
- Image zoom lightbox, contact-form dropzone label, admin photo preview
- AJAX add-to-cart with a toast and live cart badge

### 9.5 Which UI states exist today

This table is the design checklist for the CRUD and operations pages. **Empty / invalid / success** are the states the designer asked about.

| Screen | Empty | Invalid / error | Success | Other states |
| --- | --- | --- | --- | --- |
| Admin products | "No products yet. Add your first one above." / "No products match your search." | n/a | Green banner "X was added / updated / deleted." | Stock pill: normal, low (2 or fewer), out (0) |
| Admin product form | n/a | Red banner list: name, franchise, price above 0, description length, photo type/size, "photo larger than server allows", expired session, "no franchises yet" | Redirect to list with banner | Add vs Edit titles; "Show on home page" switch only on Edit |
| Admin delete | n/a | Expired-session banner | Redirect to list with banner | **Confirm** state and **Blocked** state ("was not deleted: still referenced by existing records", hint: set stock to 0) |
| Admin users | "No users yet." | n/a | n/a | Read-only list, role pill |
| Admin dashboard | None (three stat cards always shown) | n/a | n/a | Products, Users, Out of stock |
| Shop | "No pops match your search or filters." + reset button | n/a | n/a | Out-of-stock ribbon, disabled prev/next, filter panel open/closed |
| Product | "No reviews yet" / "No details have been added" / no-photo placeholder | Review form reopens with a banner; **404 "Pop not found"** | "Thanks! Your review has been posted." Toast "added to your cart" | Out of stock (disabled button + note), "You reviewed this", login required |
| Cart | "Your cart is empty." + Start Shopping | "Only N available", "Sold out", quantities capped banner | "Item removed", "Cart updated" | Checkout button disabled while quantities are invalid; free-shipping hint |
| Checkout | Redirects to cart if empty | "Please complete all shipping fields", stock rollback message | Redirect to order detail | Requires login |
| Order detail | n/a | **404 "Order not found"** | "Thank you! Your order ... has been placed." | Five status pills |
| Account | "You haven't placed any orders yet." | "Full name cannot be empty." | "Your profile has been updated." | |
| Login / Sign up | n/a | Banner list (credentials, email format, password length/match, terms) | Welcome flash | |
| Contact | n/a | Banner list (missing fields, bad email, bad file type, file too large) | "Thank you! Your message has been sent." | Dropzone with file name |

**Gap to design for:** invalid input is only shown as a **banner at the top**. There is **no per-field error styling** (no red border, no message under the field, no `aria-invalid`), and `contact.php` uses `novalidate`, so the browser's own tooltips are off. Forms do keep what the user typed (sticky values), except passwords.

---

## 10. Known gaps and loose ends (re-checked Nov 17)

Nothing here is broken unless marked; these are things to decide or finish.

| # | Item | Why it matters |
| --- | --- | --- |
| 1 | **Import risk:** the dump has `CREATE DATABASE` twice; foreign keys missing on `reviews`, `inquiries`, `product_collections`, `carts.user_id` | Wednesday demo on a second laptop; DB project's logical/physical consistency |
| 2 | Orders are numbered `RR-...` (`Order.php` line 26), shown on customer screens; **no admin orders screen** | Leftover prefix; orders cannot be seen or updated by an admin |
| 3 | `display_errors` is on (`config/config.php`) | Turn off before the demo |
| 4 | Shipping: free over 100, flat fee 15 (in ₱) | Placeholder numbers; confirm with the client or remove |
| 5 | Inquiries are saved but **no admin screen reads them**; Admin Users is read-only; no review moderation | Contact feature is a dead end for the admin |
| 6 | Cart, checkout, account, order detail use the legacy look; UI still says "Cart" (`includes/header.php`, `cart.php`) | Inconsistent with the redesign; wording should be "Basket" |
| 7 | `mechanics.php`, `terms.php` are shells; "Forgot Password" and "Privacy Policy" inactive (`.is-pending`); "Steal / Claim / Cabin Lock" tabs disabled | Visible links or tabs that do nothing |
| 8 | Favorites live in `localStorage` only | Lost on another device or after clearing the browser |
| 9 | OOP: no interface or namespaces (optional); `describe()` is mostly shown in `admin/oop_demo.php` | See 7.1 |
| 10 | Fonts come from Google Fonts (needs internet, two files); no `assets/fonts/`, `tokens.css`, or `docs/` yet | Demo must not depend on Wi-Fi |
| 11 | Leftovers: `admin/text.md`, code comments saying "reservation" (`Order.php`, `main.js`); from the old list and not re-checked: `conditionOptions()` in `functions.php`, `Order::getAll()`, `condition_grade` / `is_authenticated` columns | Cleanup for the audit |
| 12 | Placeholders in `config/content.php`: 4 team cards ("Team member"), social URLs, About text/images/map, footer trademark line | Replace with real content |
| 13 | `categories` holds franchises and `collections` holds categories | Confusing names; rename planned after Wednesday |

---

## 11. Conventions for contributing

- **Never write SQL in a page.** Add a method to the matching class in `classes/`.
- **Always escape output** with `e()` and **always prepare statements**.
- **Every POST form** needs `<input type="hidden" name="csrf_token" ...>` and a `verifyCsrfToken()` check.
- **After saving, redirect** and show the result with `setFlash('success' | 'error', '...')`.
- **Site-editable text** goes in `config/content.php`, not in templates.
- **Colors and sizes** should come from `:root` variables, not new hex codes.
- **Commit small and often** with the command line; one change per commit; do not commit real passwords or uploaded customer files.

---

## 12. Glossary (plain-English)

| Term | Meaning |
| --- | --- |
| **OOP** | Object-Oriented Programming: organizing code into *classes* (blueprints) and *objects* (things made from them) |
| **CRUD** | **C**reate, **R**ead, **U**pdate, **D**elete: the four basic things you do with stored records |
| **PDO** | PHP's standard, safe way to talk to a database |
| **Prepared statement** | A SQL query where values are sent separately (`?`), so user input can never change the query itself |
| **Singleton** | A class designed so only one object of it ever exists (here: one database connection) |
| **Session** | Server-side memory about one visitor (who is logged in, their cart token), tied to a cookie |
| **CSRF** | A trick where another website makes your browser submit a form to this site; the hidden token stops it |
| **XSS** | Injecting script through text shown on a page; escaping output stops it |
| **Flash message** | A one-time message saved before a redirect and shown on the next page (success or error banner) |
| **Slug** | A URL-friendly name, like `cosmic-hulk` |
| **Transaction** | A group of database changes that all succeed or all get undone |
| **Junction table** | A middle table that links two tables in a many-to-many relationship (`product_collections`) |
| **Migration** | A SQL file that changes an existing database step by step |
| **Progressive enhancement** | The page works without JavaScript; JavaScript only makes it nicer |
| **Design tokens** | Named values (colors, spacing, fonts) defined once and reused everywhere |


