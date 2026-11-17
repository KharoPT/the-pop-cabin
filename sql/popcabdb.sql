-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2026 at 12:14 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.5.12

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
(5, 0, '80f7464ae2ec1922a22b893942668905', '2026-11-16 09:48:41'),
(8, 0, '44b54f1de8c41ba4fe244666194559a8', '2026-11-17 22:53:45'),
(11, 2, '171aeee427ca9c1c4e626f25b79d1ef4', '2026-11-17 23:11:55');

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
(2, 5, 'Slifer The Sky Dragon', 'slifer-the-sky-dragon', 'Summon an Egyptian God Card to your display with this oversized 6-inch Yu-Gi-Oh! Slifer the Sky Dragon #756 Funko Pop!. Sculpted with its crimson coils, massive wings, and signature double jaws, this super-sized vinyl figure is an imposing centerpiece for any Duelist\'s shelf.', 4499.00, 1, NULL, 0, 0, 'uploads/products/pop-ec738c1480585b2d.jpg', '2026-11-16 18:00:44', '2026-11-17 23:09:59', 'Regular'),
(3, 6, 'Himmel', 'himmel', 'Complete your Hero Party display with this Frieren: Beyond Journey\'s End Himmel Funko Pop!. Sculpted in his white hero cape with his sword at his side, this stylized vinyl figure pairs right alongside Frieren and Fern.', 499.00, 1, NULL, 0, 0, 'uploads/products/pop-c9a2dfec3abf90a3.jpg', '2026-11-16 18:03:14', '2026-11-17 23:09:52', 'Regular'),
(4, 7, 'Spiderman(Homemade Suit)', 'spiderman-homemade-suit', 'Swing into your Marvel lineup with this Spider-Man: Homecoming Spider-Man (Homemade Suit) Funko Pop!. Featuring Peter Parker in his red-and-blue hoodie suit with homemade web-shooters, this stylized vinyl bobblehead is a fun addition to any Spider-Verse shelf.', 300.00, 2, NULL, 0, 0, 'uploads/products/pop-ae3f8284adfc6444.jpg', '2026-11-16 18:06:22', '2026-11-17 23:09:46', 'Regular'),
(5, 8, 'Alex In Diamond Armor', 'alex-in-diamond-armor', 'Gear up your gaming shelf with this Minecraft Alex in Diamond Armor Funko Pop!. Sculpted in classic blocky voxel style with blue diamond armor and matching sword, this vinyl figure comes in a window display box ready for any Minecraft fan.', 300.00, 1, NULL, 0, 0, 'uploads/products/pop-5c5a854bf008a50e.jpg', '2026-11-16 18:23:13', '2026-11-17 23:09:40', 'Regular'),
(6, 9, 'Braum', 'braum', 'Stand behind the Heart of the Freljord with this League of Legends Braum #04 Funko Pop!. Sculpted with his signature mustache, ram-horned shield, and Poro companion, this vinyl figure is a standout piece for any League of Legends lineup.', 1299.00, 1, NULL, 0, 0, 'uploads/products/pop-fa7d85771124d23c.jpg', '2026-11-17 01:02:31', '2026-11-17 23:08:52', 'Regular'),
(7, 10, 'Dark Voyager', 'dark-voyager', 'Drop into your gaming display with this Fortnite Dark Voyager #435 Funko Pop!. Dressed in his sleek black-and-orange astronaut spacesuit, this stylized vinyl figure comes in a window display box ready for your collection.', 199.00, 2, NULL, 0, 0, 'uploads/products/pop-049867db3d06ca38.jpg', '2026-11-17 01:03:36', '2026-11-17 23:08:45', 'Regular'),
(8, 11, 'Deku', 'deku', 'Power up your anime collection with this My Hero Academia Deku #247 Funko Pop!. Featuring Izuku Midoriya in his green hero costume and mask, this stylized vinyl figure is a staple for any My Hero Academia shelf.', 349.00, 2, NULL, 0, 0, 'uploads/products/pop-25588e8572767e19.jpg', '2026-11-17 01:05:15', '2026-11-17 23:08:36', 'Regular'),
(9, 12, 'Dobby', 'dobby', 'Bring the beloved house-elf to your Wizarding World lineup with this Harry Potter Dobby #17 Funko Pop!. Sculpted in his classic pillowcase tunic with big expressive eyes, this vinyl figure is a charming pickup for any Harry Potter fan.', 799.00, 2, NULL, 0, 0, 'uploads/products/pop-39b77cd24f7a823d.jpg', '2026-11-17 01:06:34', '2026-11-17 23:08:26', 'Regular'),
(10, 7, 'Doctor Strange', 'doctor-strange', 'Add the Master of the Mystic Arts to your Marvel shelf with this Doctor Strange in the Multiverse of Madness Doctor Strange #1000 Funko Pop!. Featuring his Cloak of Levitation and mystical spell runes, this vinyl bobblehead comes in window-box packaging ready for display.', 499.00, 2, NULL, 0, 0, 'uploads/products/pop-f0e10a22d361b0cd.jpg', '2026-11-17 01:07:46', '2026-11-17 23:08:17', 'Regular'),
(11, 6, 'Fern', 'fern', 'Expand your anime display with this Frieren: Beyond Journey\'s End Fern #1987 Funko Pop!. Sculpted in her dark mage robe and holding her staff, this stylized vinyl figure pairs nicely with Frieren on any shelf.', 699.00, 2, NULL, 0, 0, 'uploads/products/pop-f1ad5a669cf075d5.jpg', '2026-11-17 01:08:20', '2026-11-17 23:08:06', 'Regular'),
(12, 12, 'Fleur Delacour', 'fleur-delacour', 'Celebrate the Yule Ball in your Wizarding World display with this Harry Potter Fleur Delacour #88 Funko Pop!. Sculpted in her elegant silver-gray Beauxbatons gown, this vinyl figure comes in a window display box ready for any Harry Potter collection.', 1799.00, 1, NULL, 0, 0, 'uploads/products/pop-d89e6846965f2952.jpg', '2026-11-17 01:10:38', '2026-11-17 23:07:36', 'Regular'),
(13, 6, 'Frieren', 'frieren', 'Bring the elven mage to your display with this Frieren: Beyond Journey\'s End Frieren #1986 Funko Pop!. Featuring her white twin-tails, mage outfit, and staff, this stylized vinyl figure comes in window-box packaging ready for any anime shelf.', 699.00, 2, NULL, 0, 0, 'uploads/products/pop-1cbf28885d366fe7.jpg', '2026-11-17 01:11:40', '2026-11-17 23:07:30', 'Regular'),
(14, 14, 'Hancock', 'hancock', 'Add Goodneighbor\'s charismatic ghoul mayor to your gaming shelf with this Fallout 4 Hancock Funko Pop!. Dressed in his signature red tricorn hat and colonial coat, this stylized vinyl figure comes in a window display box ready for any Wasteland collection.', 999.00, 2, NULL, 0, 0, 'uploads/products/pop-a3bd21f5db2c4b2c.jpg', '2026-11-17 01:13:19', '2026-11-17 23:07:24', 'Regular'),
(15, 12, 'Harry Potter', 'harry-potter', 'Start or expand your Wizarding World lineup with the classic Harry Potter #01 Funko Pop!. Featuring Harry in his Gryffindor robes with his round glasses, lightning scar, and wand, this stylized vinyl figure is an essential staple for any Harry Potter collection.', 749.00, 1, NULL, 0, 0, 'uploads/products/pop-be80a1a038599e58.jpg', '2026-11-17 01:14:27', '2026-11-17 23:07:13', 'Regular'),
(16, 11, 'Hawks', 'hawks', 'Bring the high-flying Pro Hero to your anime shelf with this My Hero Academia Hawks Funko Pop!. Sculpted with his signature crimson wings, visor, and hero jacket, this stylized vinyl figure comes in window-box packaging ready for display.', 449.00, 1, NULL, 0, 0, 'uploads/products/pop-824e96269649a405.jpg', '2026-11-17 01:15:35', '2026-11-17 23:07:07', 'Regular'),
(17, 9, 'Jinx (Arcane)', 'jinx-arcane', 'Bring Zaun-level chaos to your display with this Arcane: League of Legends Jinx #1602 Funko Pop!. Featuring her signature long blue braids and wild expression from the hit animated series, this vinyl figure pairs perfectly with Vi on any shelf.   \r\nimage_0e05c7.png\r\n+ 1', 599.00, 2, NULL, 0, 0, 'uploads/products/pop-678a542913f285f2.jpg', '2026-11-17 01:16:41', '2026-11-17 23:06:40', 'Regular'),
(18, 12, 'Minerva McGonagall', 'minerva-mcgonagall', 'Celebrate Hogwarts\' beloved Transfiguration professor in her Animagus form with this Harry Potter Minerva McGonagall (Cat) Funko Pop!. Sculpted with her signature tabby markings and square spectacle markings around her eyes, this vinyl figure is a charming pickup for any Wizarding World fan.', 848.00, 2, NULL, 0, 0, 'uploads/products/pop-205cf23db1c6f0c8.jpg', '2026-11-17 01:18:37', '2026-11-17 23:06:31', 'Regular'),
(19, 13, 'Osiris', 'osiris', 'Bring the legendary Warlock exile to your gaming shelf with this Destiny Osiris #339 Funko Pop!. Sculpted in his iconic golden feathered armor and Sunbracers, this vinyl figure is an essential centerpiece for any Guardian\'s collection.', 899.00, 3, NULL, 0, 0, 'uploads/products/pop-7be37422664856c1.jpg', '2026-11-17 01:19:22', '2026-11-17 23:06:26', 'Regular'),
(20, 10, 'Red-Nosed Raider', 'red-nosed-raider', 'Drop into the holiday spirit with this Fortnite Red-Nosed Raider #437 Funko Pop!. Featuring her festive reindeer antlers and tactical winter outfit, this stylized vinyl figure comes in a window display box ready for your gaming lineup.', 399.00, 4, NULL, 0, 0, 'uploads/products/pop-f4a8c1fa4266860e.jpg', '2026-11-17 01:20:00', '2026-11-17 23:06:17', 'Regular'),
(21, 12, 'Rita Skeeter', 'rita-skeeter', 'Get the exclusive scoop for your Wizarding World shelf with this 2019 Summer Convention exclusive Rita Skeeter #101 Funko Pop!. Complete with her signature green outfit, jeweled spectacles, and Quick-Quotes Quill, this vinyl figure is a standout addition to any Harry Potter collection', 1199.00, 3, NULL, 0, 0, 'uploads/products/pop-2d2a4f90c8e6631a.jpg', '2026-11-17 01:20:49', '2026-11-17 23:06:10', 'Regular'),
(22, 15, 'Super Saiyan Caulifla', 'super-saiyan-caulifla', 'Add Universe 6\'s fiercest Saiyan to your shelf with this Dragon Ball Super Super Saiyan Caulifla #826 Funko Pop!. Featuring her golden Super Saiyan hair and classic outfit, this vinyl figure comes in window-box packaging ready for display.', 299.00, 4, NULL, 0, 0, 'uploads/products/pop-0bdf08d29ab8a2c5.jpg', '2026-11-17 01:22:35', '2026-11-17 23:03:08', 'Regular'),
(23, 15, 'Super Saiyan Kefla', 'super-saiyan-kefla', 'Power up your anime display with this Dragon Ball Super Super Saiyan Kefla Funko Pop!. Sculpted with her spiky green hair and Potara earrings, this Universe 6 fusion warrior is ready to stand out in any Dragon Ball collection.', 349.00, 2, NULL, 0, 0, 'uploads/products/pop-548803c6ff4dc1b1.jpg', '2026-11-17 01:23:25', '2026-11-17 23:02:59', 'Regular'),
(24, 13, 'Sweeper Bot', 'sweeper-bot', 'Tidy up your gaming shelf with this San Diego Comic-Con exclusive Sweeper Bot #342 Funko Pop! from Destiny. Complete with its signature broom and Summer Convention Exclusive sticker, this vinyl figure is a fun pickup for any Guardian.', 499.00, 2, NULL, 0, 0, 'uploads/products/pop-936f0c052249009c.jpg', '2026-11-17 01:24:17', '2026-11-17 23:02:52', 'Regular'),
(25, 7, 'T\'Challa', 't-challa', 'Celebrate the alternate universe of Marvel\'s What If...? with this Star-Lord T\'Challa Funko Pop!. Featuring T\'Challa in his Ravager gear, this stylized vinyl bobblehead comes in a window display box ready for your Marvel lineup.', 300.00, 4, NULL, 0, 0, 'uploads/products/pop-2faf773fa6b6df0d.jpg', '2026-11-17 01:25:25', '2026-11-17 23:02:45', 'Regular'),
(26, 7, 'Thanos', 'thanos', 'Bring the Mad Titan to your Marvel shelf with this Avengers: Endgame Thanos #453 Funko Pop!. Sculpted in his battle armor with the Infinity Gauntlet, this stylized vinyl bobblehead is a must-have centerpiece for any Marvel collection.', 300.00, 9, NULL, 0, 0, 'uploads/products/pop-2c12eccc55f5d1c3.jpg', '2026-11-17 01:26:28', '2026-11-17 23:02:36', 'Regular'),
(27, 7, 'Thor (Ragnarok)', 'thor-ragnarok', 'Suit up for the final battle with this Avengers: Endgame Thor Funko Pop!. Wielding Stormbreaker in his white-and-black team suit, the God of Thunder is ready to join your Marvel lineup.', 300.00, 5, NULL, 0, 0, 'uploads/products/pop-38995e36ce6bf9ef.jpg', '2026-11-17 01:27:51', '2026-11-17 23:01:45', 'Regular'),
(28, 11, 'Todoroki', 'todoroki', 'Bring fire and ice to your collection with this Special Edition Shoto Todoroki Funko Pop!. Featuring his signature split red-and-white hair and active flames, this U.A. High hero-in-training is ready to stand out on any shelf.', 399.00, 4, NULL, 0, 0, 'uploads/products/pop-1da066a839035752.jpg', '2026-11-17 01:28:40', '2026-11-17 23:00:40', 'Regular'),
(29, 11, 'Tomura Shigaraki', 'tomura-shigaraki', 'Bring the leader of the League of Villains to your shelf with this Galactic Toys Exclusive Tomura Shigaraki Funko Pop!. Sculpted with his signature Pale Blue hair and eerie disembodied hands, he\'s an essential centerpiece for any My Hero Academia villain display.', 349.00, 5, NULL, 0, 0, 'uploads/products/pop-c967fe87c7303c56.jpg', '2026-11-17 01:29:59', '2026-11-17 22:59:35', 'Regular'),
(30, 9, 'Vi', 'vi', 'Bring home the fierce brawler of Zaun with this Vi Funko Pop! featuring her iconic pink hair, facial tattoo, and heavy gauntlets. A must-have collectible for any Arcane or League of Legends fan!', 499.00, 3, NULL, 0, 0, 'uploads/products/pop-7b70945921fdfafe.jpg', '2026-11-17 01:30:56', '2026-11-17 22:58:46', 'Regular'),
(31, 9, 'Vi (Arcane)', 'vi-arcane', 'Bring home the fierce brawler of Zaun with this Vi Funko Pop! featuring her iconic pink hair, facial tattoo, and heavy gauntlets. A must-have collectible for any Arcane or League of Legends fan!', 3499.00, 1, NULL, 0, 0, 'uploads/products/pop-4baf8eb7777008aa.jpg', '2026-11-17 01:31:47', '2026-11-17 22:58:40', 'Regular');

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

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `product_id`, `user_id`, `rating`, `comment`, `is_anonymous`, `created_at`) VALUES
(1, 13, 2, 4, 'Frieren is one of my favorite Funko Pops! I\'m glad this product was sold at a fair price.', 0, '2026-11-17 23:13:49');

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
(1, 'admin', 'admin@gmail.com', '$2y$12$ZhW5uyDqbvpYwvtE1OqL7eGLucYlS7jt1pFDjvBL7.vQxRhZXzVl2', 'Admin 1', '', '', '', '', '', 'admin'),
(2, 'customer', 'customer@gmail.com', '$2y$12$7AtORibJkJXxkSoj5E5c1u3smlRSlNZspuipfO.dKWbFSL4SxEeh2', 'Customer 1', '', '', '', '', '', 'customer');

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
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
