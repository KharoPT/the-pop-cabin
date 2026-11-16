-- popcabdb: additive changes needed by the new front-end pages.
-- Run ONCE, after popcabdb_migration.sql:  phpMyAdmin -> popcabdb -> SQL tab -> paste -> Go.
-- Written for MariaDB (the database that ships with XAMPP). It only ADDS things and
-- never touches existing rows, so it is safe on a database that already has data.

-- 1. Product details shown on the product page and edited in the admin form.
ALTER TABLE `products`
  ADD COLUMN IF NOT EXISTS `description` TEXT NULL,
  ADD COLUMN IF NOT EXISTS `edition` VARCHAR(50) NOT NULL DEFAULT 'Regular';

-- 2. The admin dashboard shows the date of each reservation. Existing orders get the
--    moment this script runs as their date (there is no earlier date to recover).
ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- 3. "Categories" (Movies, Anime, Exclusives, Games). A product can be in several.
--    The existing `categories` table stays as the Franchise list (Marvel, DC, ...).
CREATE TABLE IF NOT EXISTS `collections` (
  `collection_id`   INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `collection_name` VARCHAR(100) NOT NULL,
  `slug`            VARCHAR(100) NOT NULL,
  UNIQUE KEY `uq_collection_slug` (`slug`)
);
INSERT IGNORE INTO `collections` (`collection_name`, `slug`) VALUES
  ('Movies', 'movies'), ('Anime', 'anime'), ('Exclusives', 'exclusives'), ('Games', 'games');

CREATE TABLE IF NOT EXISTS `product_collections` (
  `product_id`    INT NOT NULL,
  `collection_id` INT NOT NULL,
  PRIMARY KEY (`product_id`, `collection_id`),
  KEY `idx_pc_collection` (`collection_id`)
);

-- 4. Buyer reviews (product page + optional "Words from buyers" section on the home page).
CREATE TABLE IF NOT EXISTS `reviews` (
  `review_id`    INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `product_id`   INT NOT NULL,
  `user_id`      INT NOT NULL,
  `rating`       TINYINT NOT NULL,
  `comment`      TEXT NOT NULL,
  `is_anonymous` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_review_once` (`product_id`, `user_id`),
  KEY `idx_review_created` (`created_at`)
);

-- 5. Messages sent from the Contact Us page.
CREATE TABLE IF NOT EXISTS `inquiries` (
  `inquiry_id`      INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id`         INT NULL,
  `first_name`      VARCHAR(100) NOT NULL,
  `last_name`       VARCHAR(100) NOT NULL,
  `email`           VARCHAR(255) NOT NULL,
  `message`         TEXT NOT NULL,
  `attachment_path` VARCHAR(255) NULL,
  `attachment_name` VARCHAR(255) NULL,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
