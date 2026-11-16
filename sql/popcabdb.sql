-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 16, 2026 at 06:32 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `popcabdb`
--
CREATE DATABASE popcabdb;

USE popcabdb;
-- --------------------------------------------------------
CREATE DATABASE popcabdb;
USE popcabdb;
--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`cart_id`, `user_id`, `session_id`, `created_at`) VALUES
(1, 0, '6d0ff865c0c1cbb4a497629b6426d0bd', '2026-10-31 05:27:23'),
(2, 0, '8fc8c49ea51a226f0699e4fd74b809c9', '2026-11-15 11:49:48'),
(3, 1, '42fd920a831749822e79084fe62cd0da', '2026-11-16 09:07:59'),
(5, 0, '80f7464ae2ec1922a22b893942668905', '2026-11-16 09:48:41');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `added_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `slug`, `description`) VALUES
(5, 'Yu-Gi-Oh!', 'yu-gi-oh', ''),
(6, 'Frieren', 'frieren', ''),
(7, 'Marvel', 'marvel', ''),
(8, 'Minecraft', 'minecraft', ''),
(9, 'League of Legends', 'league of legends', ''),
(10, 'Fortnite', 'fortnite', ''),
(11, 'My Hero Academia', 'my hero academia', ''),
(12, 'Harry Potter', 'harry potter', ''),
(13, 'Destiny', 'destiny', ''),
(14, 'Fallout 4', 'fallout 4', ''),
(15, 'Dragon Ball Super', 'dragon ball super', '');

-- --------------------------------------------------------

--
-- Table structure for table `collections`
--

CREATE TABLE `collections` (
  `collection_id` int(11) NOT NULL,
  `collection_name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `collections`
--

INSERT INTO `collections` (`collection_id`, `collection_name`, `slug`) VALUES
(1, 'Movies', 'movies'),
(2, 'Anime', 'anime'),
(4, 'Games', 'games');

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `inquiry_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `attachment_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_number` varchar(40) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_name` varchar(150) NOT NULL,
  `shipping_address` varchar(255) NOT NULL,
  `shipping_city` varchar(100) NOT NULL,
  `shipping_postal_code` varchar(20) NOT NULL,
  `shipping_country` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(180) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(180) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `condition_grade` enum('Mint','Near Mint','Excellent','Good','Fair') DEFAULT NULL,
  `is_authenticated` tinyint(1) NOT NULL,
  `is_featured` tinyint(1) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `edition` varchar(50) NOT NULL DEFAULT 'Regular'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `name`, `slug`, `description`, `price`, `stock_quantity`, `condition_grade`, `is_authenticated`, `is_featured`, `image_url`, `created_at`, `updated_at`, `edition`) VALUES
(2, 5, 'Slifer The Sky Dragon', 'slifer-the-sky-dragon', 'Slifer The Sky Dragon from Yu-Gi-Oh! Is a popular character known by most fans due to its intimidating looks!', 4499.00, 1, NULL, 0, 0, 'uploads/products/pop-ec738c1480585b2d.jpg', '2026-11-16 18:00:44', '2026-11-16 10:00:44', 'Regular'),
(3, 6, 'Himmel', 'himmel', '', 499.00, 1, NULL, 0, 0, 'uploads/products/pop-c9a2dfec3abf90a3.jpg', '2026-11-16 18:03:14', '2026-11-16 10:03:14', 'Regular'),
(4, 7, 'Spiderman(Homemade Suit)', 'spiderman-homemade-suit', '\"With Great Power Comes Great Responsibility\". Spiderman is one of the most popular characters of the Marvel Franchise!', 300.00, 2, NULL, 0, 0, 'uploads/products/pop-ae3f8284adfc6444.jpg', '2026-11-16 18:06:22', '2026-11-16 10:06:22', 'Regular'),
(5, 8, 'Alex In Diamond Armor', 'alex-in-diamond-armor', 'Alex', 300.00, 1, NULL, 0, 0, 'uploads/products/pop-5c5a854bf008a50e.jpg', '2026-11-16 18:23:13', '2026-11-16 10:23:13', 'Regular'),
(6, 9, 'Braum', 'braum', '', 1299.00, 1, NULL, 0, 0, 'uploads/products/pop-fa7d85771124d23c.jpg', '2026-11-17 01:02:31', '2026-11-16 17:02:31', 'Regular'),
(7, 10, 'Dark Voyager', 'dark-voyager', '', 199.00, 2, NULL, 0, 0, 'uploads/products/pop-049867db3d06ca38.jpg', '2026-11-17 01:03:36', '2026-11-16 17:03:36', 'Regular'),
(8, 11, 'Deku', 'deku', '', 349.00, 2, NULL, 0, 0, 'uploads/products/pop-25588e8572767e19.jpg', '2026-11-17 01:05:15', '2026-11-16 17:05:15', 'Regular'),
(9, 12, 'Dobby', 'dobby', '', 799.00, 2, NULL, 0, 0, 'uploads/products/pop-39b77cd24f7a823d.jpg', '2026-11-17 01:06:34', '2026-11-16 17:06:34', 'Regular'),
(10, 7, 'Doctor Strange', 'doctor-strange', '', 499.00, 2, NULL, 0, 0, 'uploads/products/pop-f0e10a22d361b0cd.jpg', '2026-11-17 01:07:46', '2026-11-16 17:07:46', 'Regular'),
(11, 6, 'Fern', 'fern', '', 699.00, 2, NULL, 0, 0, 'uploads/products/pop-f1ad5a669cf075d5.jpg', '2026-11-17 01:08:20', '2026-11-16 17:08:20', 'Regular'),
(12, 12, 'Fleur Delacour', 'fleur-delacour', '', 1799.00, 1, NULL, 0, 0, 'uploads/products/pop-d89e6846965f2952.jpg', '2026-11-17 01:10:38', '2026-11-16 17:10:38', 'Regular'),
(13, 6, 'Frieren', 'frieren', '', 699.00, 2, NULL, 0, 0, 'uploads/products/pop-1cbf28885d366fe7.jpg', '2026-11-17 01:11:40', '2026-11-16 17:11:40', 'Regular'),
(14, 14, 'Hancock', 'hancock', '', 999.00, 2, NULL, 0, 0, 'uploads/products/pop-a3bd21f5db2c4b2c.jpg', '2026-11-17 01:13:19', '2026-11-16 17:13:19', 'Regular'),
(15, 12, 'Harry Potter', 'harry-potter', '', 749.00, 1, NULL, 0, 0, 'uploads/products/pop-be80a1a038599e58.jpg', '2026-11-17 01:14:27', '2026-11-16 17:14:27', 'Regular'),
(16, 11, 'Hawks', 'hawks', '', 449.00, 1, NULL, 0, 0, 'uploads/products/pop-824e96269649a405.jpg', '2026-11-17 01:15:35', '2026-11-16 17:15:35', 'Regular'),
(17, 9, 'Jinx (Arcane)', 'jinx-arcane', '', 599.00, 2, NULL, 0, 0, 'uploads/products/pop-678a542913f285f2.jpg', '2026-11-17 01:16:41', '2026-11-16 17:16:41', 'Regular'),
(18, 12, 'Minerva McGonagall', 'minerva-mcgonagall', '', 848.00, 2, NULL, 0, 0, 'uploads/products/pop-205cf23db1c6f0c8.jpg', '2026-11-17 01:18:37', '2026-11-16 17:18:37', 'Regular'),
(19, 13, 'Osiris', 'osiris', '', 899.00, 3, NULL, 0, 0, 'uploads/products/pop-7be37422664856c1.jpg', '2026-11-17 01:19:22', '2026-11-16 17:19:22', 'Regular'),
(20, 10, 'Red-Nosed Raider', 'red-nosed-raider', '', 399.00, 4, NULL, 0, 0, 'uploads/products/pop-f4a8c1fa4266860e.jpg', '2026-11-17 01:20:00', '2026-11-16 17:20:00', 'Regular'),
(21, 12, 'Rita Skeeter', 'rita-skeeter', '', 1199.00, 3, NULL, 0, 0, 'uploads/products/pop-2d2a4f90c8e6631a.jpg', '2026-11-17 01:20:49', '2026-11-16 17:20:49', 'Regular'),
(22, 15, 'Super Saiyan Caulifla', 'super-saiyan-caulifla', '', 299.00, 4, NULL, 0, 0, 'uploads/products/pop-0bdf08d29ab8a2c5.jpg', '2026-11-17 01:22:35', '2026-11-16 17:22:35', 'Regular'),
(23, 15, 'Super Saiyan Kefla', 'super-saiyan-kefla', '', 349.00, 2, NULL, 0, 0, 'uploads/products/pop-548803c6ff4dc1b1.jpg', '2026-11-17 01:23:25', '2026-11-16 17:23:25', 'Regular'),
(24, 13, 'Sweeper Bot', 'sweeper-bot', '', 499.00, 2, NULL, 0, 0, 'uploads/products/pop-936f0c052249009c.jpg', '2026-11-17 01:24:17', '2026-11-16 17:24:17', 'Regular'),
(25, 7, 'T\'Challa', 't-challa', '', 300.00, 4, NULL, 0, 0, 'uploads/products/pop-2faf773fa6b6df0d.jpg', '2026-11-17 01:25:25', '2026-11-16 17:25:25', 'Regular'),
(26, 7, 'Thanos', 'thanos', '', 300.00, 9, NULL, 0, 0, 'uploads/products/pop-2c12eccc55f5d1c3.jpg', '2026-11-17 01:26:28', '2026-11-16 17:26:28', 'Regular'),
(27, 7, 'Thor (Ragnarok)', 'thor-ragnarok', '', 300.00, 5, NULL, 0, 0, 'uploads/products/pop-38995e36ce6bf9ef.jpg', '2026-11-17 01:27:51', '2026-11-16 17:27:51', 'Regular'),
(28, 11, 'Todoroki', 'todoroki', '', 399.00, 4, NULL, 0, 0, 'uploads/products/pop-1da066a839035752.jpg', '2026-11-17 01:28:40', '2026-11-16 17:28:40', 'Regular'),
(29, 11, 'Tomura Shigaraki', 'tomura-shigaraki', '', 349.00, 5, NULL, 0, 0, 'uploads/products/pop-c967fe87c7303c56.jpg', '2026-11-17 01:29:59', '2026-11-16 17:29:59', 'Regular'),
(30, 9, 'Vi', 'vi', '', 499.00, 3, NULL, 0, 0, 'uploads/products/pop-7b70945921fdfafe.jpg', '2026-11-17 01:30:56', '2026-11-16 17:30:56', 'Regular'),
(31, 9, 'Vi (Arcane)', 'vi-arcane', '', 3499.00, 1, NULL, 0, 0, 'uploads/products/pop-4baf8eb7777008aa.jpg', '2026-11-17 01:31:47', '2026-11-16 17:31:47', 'Regular');

-- --------------------------------------------------------

--
-- Table structure for table `product_collections`
--

CREATE TABLE `product_collections` (
  `product_id` int(11) NOT NULL,
  `collection_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_collections`
--

INSERT INTO `product_collections` (`product_id`, `collection_id`) VALUES
(2, 2),
(3, 2),
(4, 1),
(5, 4),
(6, 4),
(7, 4),
(8, 2),
(9, 1),
(10, 1),
(11, 2),
(12, 1),
(13, 2),
(14, 4),
(15, 1),
(16, 2),
(17, 4),
(18, 1),
(19, 4),
(20, 4),
(21, 1),
(22, 2),
(23, 2),
(24, 4),
(25, 1),
(26, 1),
(27, 1),
(28, 2),
(29, 2),
(30, 4),
(31, 4);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text NOT NULL,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL,
  `user_role` enum('customer','admin') NOT NULL DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `full_name`, `phone`, `address`, `city`, `postal_code`, `country`, `user_role`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$12$ZhW5uyDqbvpYwvtE1OqL7eGLucYlS7jt1pFDjvBL7.vQxRhZXzVl2', 'Admin 1', '', '', '', '', '', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`cart_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `collections`
--
ALTER TABLE `collections`
  ADD PRIMARY KEY (`collection_id`),
  ADD UNIQUE KEY `uq_collection_slug` (`slug`);

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`inquiry_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_collections`
--
ALTER TABLE `product_collections`
  ADD PRIMARY KEY (`product_id`,`collection_id`),
  ADD KEY `idx_pc_collection` (`collection_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `uq_review_once` (`product_id`,`user_id`),
  ADD KEY `idx_review_created` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `collections`
--
ALTER TABLE `collections`
  MODIFY `collection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `inquiry_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`cart_id`),
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
