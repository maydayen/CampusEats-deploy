-- CampusEats MySQL database
-- Database name: campuseats_db
-- Demo account password for all seeded users: 123456
-- Passwords are stored as bcrypt hashes. In PHP/Slim login, use password_verify($password, $user['password_hash']).

DROP DATABASE IF EXISTS `campuseats_db`;
CREATE DATABASE `campuseats_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `campuseats_db`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `promotions`;
DROP TABLE IF EXISTS `menu_item_options`;
DROP TABLE IF EXISTS `menu_items`;
DROP TABLE IF EXISTS `vendor_users`;
DROP TABLE IF EXISTS `vendors`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `users` (
    `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('customer', 'vendor', 'admin') NOT NULL DEFAULT 'customer',
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vendors` (
    `vendor_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(120) NOT NULL,
    `location` VARCHAR(120) NOT NULL,
    `opening_hours` VARCHAR(80) NOT NULL,
    `rating` DECIMAL(2,1) NOT NULL DEFAULT 0.0,
    `status` ENUM('pending', 'approved', 'rejected', 'deactivated') NOT NULL DEFAULT 'pending',
    `image_url` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`vendor_id`),
    KEY `idx_vendors_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vendor_users` (
    `vendor_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`vendor_id`, `user_id`),
    CONSTRAINT `fk_vendor_users_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`vendor_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_vendor_users_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `menu_items` (
    `menu_item_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `vendor_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(160) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10,2) NOT NULL,
    `category` VARCHAR(80) NOT NULL,
    `image_url` VARCHAR(255) DEFAULT NULL,
    `in_stock` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`menu_item_id`),
    KEY `idx_menu_vendor` (`vendor_id`),
    KEY `idx_menu_category` (`category`),
    CONSTRAINT `fk_menu_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`vendor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE `menu_item_options` (
    `option_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `menu_item_id` INT UNSIGNED NOT NULL,
    `option_name` VARCHAR(80) NOT NULL,
    PRIMARY KEY (`option_id`),
    KEY `idx_options_menu_item` (`menu_item_id`),
    CONSTRAINT `fk_options_menu_item` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`menu_item_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `promotions` (
    `promotion_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `vendor_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT,
    `discount_text` VARCHAR(50) NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`promotion_id`),
    KEY `idx_promotions_vendor` (`vendor_id`),
    CONSTRAINT `fk_promotions_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`vendor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
    `order_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `vendor_id` INT UNSIGNED NOT NULL,
    `status` ENUM('placed', 'preparing', 'ready', 'collected', 'cancelled') NOT NULL DEFAULT 'placed',
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `service_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `tax_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `pickup_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`order_id`),
    KEY `idx_orders_user` (`user_id`),
    KEY `idx_orders_vendor` (`vendor_id`),
    KEY `idx_orders_status` (`status`),
    CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_orders_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`vendor_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
    `order_item_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` INT UNSIGNED NOT NULL,
    `menu_item_id` INT UNSIGNED NOT NULL,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (`order_item_id`),
    KEY `idx_order_items_order` (`order_id`),
    KEY `idx_order_items_menu` (`menu_item_id`),
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_items_menu` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`menu_item_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reviews` (
    `review_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `vendor_id` INT UNSIGNED NOT NULL,
    `rating` TINYINT UNSIGNED NOT NULL,
    `comment` TEXT,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`review_id`),
    UNIQUE KEY `uq_reviews_order_user` (`order_id`, `user_id`),
    KEY `idx_reviews_vendor` (`vendor_id`),
    CONSTRAINT `fk_reviews_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_reviews_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`vendor_id`) ON DELETE RESTRICT,
    CONSTRAINT `chk_reviews_rating` CHECK (`rating` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
    `notification_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `order_id` INT UNSIGNED DEFAULT NULL,
    `promotion_id` INT UNSIGNED DEFAULT NULL,
    `title` VARCHAR(120) NOT NULL,
    `message` TEXT NOT NULL,
    `type` VARCHAR(50) NOT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`notification_id`),
    KEY `idx_notifications_user` (`user_id`),
    KEY `idx_notifications_order` (`order_id`),
    KEY `idx_notifications_promotion` (`promotion_id`),
    CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_notifications_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE SET NULL,
    CONSTRAINT `fk_notifications_promotion` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`promotion_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`user_id`, `name`, `email`, `password_hash`, `role`, `status`, `created_at`) VALUES
    (1, 'Ali Student', 'ali@student.utm.my', '$2y$12$PW7fuytoSc6xjZx5A3Y89uQnQxsksuwl8kMQNTT0qt1lpmtb.ibwO', 'customer', 'active', '2026-04-01 09:00:00'),
    (2, 'Daily Grind Owner', 'dailygrind@utm.my', '$2y$12$PW7fuytoSc6xjZx5A3Y89uQnQxsksuwl8kMQNTT0qt1lpmtb.ibwO', 'vendor', 'active', '2026-04-01 09:10:00'),
    (3, 'Admin User', 'admin@campuseats.my', '$2y$12$PW7fuytoSc6xjZx5A3Y89uQnQxsksuwl8kMQNTT0qt1lpmtb.ibwO', 'admin', 'active', '2026-04-01 09:20:00');

INSERT INTO `vendors` (`vendor_id`, `name`, `location`, `opening_hours`, `rating`, `status`, `image_url`) VALUES
    (1, 'Lemon Tree', 'Arked Meranti', '10:00 AM - 10:00 PM', 4.5, 'approved', '/images/lemon-tree.jpg'),
    (2, 'Nasi Kandar Osman', 'N24', '10:00 AM - 12:00 AM', 4.4, 'approved', '/images/osman.webp'),
    (3, 'Chill Cafe', 'Student Mall', '8:00 AM - 6:00 PM', 4.3, 'approved', '/images/kacani.jpg');

-- Link the vendor demo account to Lemon Tree because the current Vendor Dashboard filters vendor_id = 1.
INSERT INTO `vendor_users` (`vendor_id`, `user_id`) VALUES
    (1, 2);

INSERT INTO `menu_items` (`menu_item_id`, `vendor_id`, `name`, `description`, `price`, `category`, `image_url`, `in_stock`) VALUES
    (1, 1, 'Garlic Rice with Crispy Breaded Cheesy Chicken Chop', 'Garlic rice served with crispy breaded cheesy chicken chop.', 13.4, 'Western', '/images/menu/Lemon Tree/Garlic Rice With Crispy Breaded Cheesy Chicken Chop.webp', 1),
    (2, 1, 'Spaghetti Aglio-Olio with Crispy Breaded Cheesy Chicken Chop', 'Spaghetti aglio-olio served with crispy breaded cheesy chicken chop.', 13.4, 'Western', '/images/menu/Lemon Tree/Spaghetti Aglio-Olio With Crispy Breaded Cheesy Chicken Chop.webp', 1),
    (3, 1, 'Cheesy Crispy Chicken Burger', 'Crispy chicken burger served with cheese and sauce.', 15.9, 'Burger', '/images/menu/Lemon Tree/Cheesy Crispy Chicken Burger.webp', 1),
    (4, 1, 'Aglio-olio', 'Classic aglio-olio pasta with garlic, herbs and chili flakes.', 11.9, 'Pasta', '/images/menu/Lemon Tree/Aglio-olio.webp', 1),
    (5, 1, 'Chicken Meatloaf Carbonara', 'Creamy carbonara pasta served with chicken meatloaf.', 21.9, 'Pasta', '/images/menu/Lemon Tree/Chicken Meatloaf Carbonara.webp', 1),
    (6, 1, 'Seafood Marinara', 'Seafood pasta served with marinara sauce.', 32.0, 'Pasta', '/images/menu/Lemon Tree/Seafood Marinara.webp', 1),
    (7, 1, 'Iced Lemonade', 'Cold refreshing lemonade.', 6.0, 'Drinks', '/images/menu/Lemon Tree/Iced Lemonade.webp', 1),
    (8, 1, 'Sparkling Lemon Tea', 'Sparkling lemon tea served cold.', 10.0, 'Drinks', '/images/menu/Lemon Tree/Sparkling Lemon Tea.webp', 1),
    (9, 2, 'Roti Canai', 'Classic mamak-style roti canai served with curry.', 1.5, 'Mamak', '/images/menu/Nasi Kandar Osman/Roti Canai.webp', 1),
    (10, 2, 'Roti Telur', 'Roti canai with egg, served with curry.', 3.0, 'Mamak', '/images/menu/Nasi Kandar Osman/Roti Telur.webp', 1),
    (11, 2, 'Roti Tisu', 'Thin and crispy sweet roti tisu.', 4.5, 'Mamak', '/images/menu/Nasi Kandar Osman/Roti Tisu.jpg', 1),
    (12, 2, 'Nasi Kandar Student Set', 'Student nasi kandar set with rice, curry, vegetables and chicken.', 7.5, 'Rice', '/images/menu/Nasi Kandar Osman/Nasi Kandar Student Set.jpg', 1),
    (13, 2, 'Nasi Kandar Set A', 'Nasi kandar with mixed curry, vegetables and fried chicken.', 9.5, 'Rice', '/images/menu/Nasi Kandar Osman/Nasi Kandar Set A.jpg', 1),
    (14, 2, 'Nasi Kandar Set B', 'Nasi kandar with mixed curry, vegetables and meat dish.', 11.0, 'Rice', '/images/menu/Nasi Kandar Osman/Nasi Kandar Set B.jpg', 1),
    (15, 2, 'Maggi Goreng', 'Mamak-style fried instant noodles with egg and vegetables.', 6.5, 'Noodles', '/images/menu/Nasi Kandar Osman/Maggi Goreng.webp', 1),
    (16, 2, 'Teh Ais', 'Classic iced milk tea.', 2.5, 'Drinks', '/images/menu/Nasi Kandar Osman/Teh Ais.jpg', 1),
    (17, 3, 'Chicken Croissant Sandwich', 'Croissant sandwich filled with chicken slices, lettuce and mayo.', 9.9, 'Sandwich', '/images/menu/Chill Cafe/Chicken Croissant Sandwich.jpg', 1),
    (18, 3, 'Creamy Mushroom Pasta', 'Creamy mushroom pasta with herbs.', 12.9, 'Pasta', '/images/menu/Chill Cafe/Creamy Mushroom Pasta.jpg', 1),
    (19, 3, 'Grilled Chicken Wrap', 'Soft wrap filled with grilled chicken, vegetables and sauce.', 10.9, 'Wrap', '/images/menu/Chill Cafe/Grilled Chicken Wrap.jpg', 1),
    (20, 3, 'Chocolate Muffin', 'Soft chocolate muffin suitable for a quick snack.', 4.5, 'Snacks', '/images/menu/Chill Cafe/Chocolate Muffin.jpg', 1),
    (21, 3, 'Butter Croissant', 'Freshly baked butter croissant.', 5.5, 'Pastry', '/images/menu/Chill Cafe/Butter Croissant.jpg', 1),
    (22, 3, 'Iced Americano', 'Cold black coffee.', 6.0, 'Drinks', '/images/menu/Chill Cafe/Iced Americano.jpg', 1),
    (23, 3, 'Caramel Latte', 'Coffee latte with caramel syrup.', 8.5, 'Drinks', '/images/menu/Chill Cafe/Caramel Latte.jpg', 1),
    (24, 3, 'Matcha Latte', 'Iced matcha latte with milk.', 8.0, 'Drinks', '/images/menu/Chill Cafe/Matcha Latte.jpg', 1);

INSERT INTO `menu_item_options` (`option_id`, `menu_item_id`, `option_name`) VALUES
    (1, 1, 'Teriyaki'),
    (2, 1, 'Cheesy'),
    (3, 1, 'BBQ'),
    (4, 2, 'Teriyaki'),
    (5, 2, 'Cheesy'),
    (6, 2, 'BBQ'),
    (7, 3, 'Teriyaki'),
    (8, 3, 'Cheesy'),
    (9, 3, 'Korean');

INSERT INTO `promotions` (`promotion_id`, `vendor_id`, `title`, `description`, `discount_text`, `start_date`, `end_date`, `is_active`, `created_at`) VALUES
    (1, 2, 'Friday Pizza Party', 'Buy any large pizza and get one free.', 'BOGO FREE', '2026-04-01', '2026-04-30', 1, '2026-04-01 10:00:00'),
    (2, 1, 'Morning Coffee Deal', 'Get 20% off selected coffee before 10 AM.', '20% OFF', '2026-04-01', '2026-04-20', 1, '2026-04-01 10:10:00');

INSERT INTO `orders` (`order_id`, `user_id`, `vendor_id`, `status`, `subtotal`, `service_fee`, `tax_amount`, `total`, `pickup_at`, `created_at`) VALUES
    (1, 1, 1, 'collected', 13.45, 0.5, 0.0, 13.95, '2026-04-08 12:30:00', '2026-04-08 12:00:00'),
    (2, 1, 2, 'preparing', 21.5, 0.5, 1.85, 23.85, '2026-04-08 13:00:00', '2026-04-08 12:20:00'),
    (3, 1, 3, 'collected', 9.9, 0.5, 1.85, 12.25, '2026-04-08 13:00:00', '2026-04-08 12:20:00');

INSERT INTO `order_items` (`order_item_id`, `order_id`, `menu_item_id`, `quantity`, `unit_price`) VALUES
    (1, 1, 1, 1, 4.5),
    (2, 1, 3, 1, 8.95),
    (3, 2, 4, 1, 12.5),
    (4, 2, 5, 1, 9.0),
    (17, 3, 5, 1, 9.9);

INSERT INTO `reviews` (`review_id`, `order_id`, `user_id`, `vendor_id`, `rating`, `comment`, `created_at`) VALUES
    (1, 1, 1, 1, 5, 'Food was ready on time and tasted good.', '2026-04-08 13:00:00');

INSERT INTO `notifications` (`notification_id`, `user_id`, `order_id`, `promotion_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
    (1, 1, 1, NULL, 'Order Ready', 'Your order from The Daily Grind is ready for pickup.', 'order_ready', 0, '2026-04-08 12:25:00'),
    (2, 2, 2, NULL, 'New Order Received', 'A new order has been placed by Ali Student.', 'order_status', 0, '2026-04-08 12:20:00'),
    (3, 1, NULL, 1, 'New Promotion', 'Friday Pizza Party is now available at The Commons Hall.', 'promotion', 1, '2026-04-08 11:00:00');


-- Keep AUTO_INCREMENT values ahead of seeded IDs.
ALTER TABLE `users` AUTO_INCREMENT = 4;
ALTER TABLE `vendors` AUTO_INCREMENT = 4;
ALTER TABLE `menu_items` AUTO_INCREMENT = 25;
ALTER TABLE `menu_item_options` AUTO_INCREMENT = 10;
ALTER TABLE `promotions` AUTO_INCREMENT = 3;
ALTER TABLE `orders` AUTO_INCREMENT = 4;
ALTER TABLE `order_items` AUTO_INCREMENT = 18;
ALTER TABLE `reviews` AUTO_INCREMENT = 3;
ALTER TABLE `notifications` AUTO_INCREMENT = 4;

-- Quick checks for demo/report evidence.
SELECT 'users' AS table_name, COUNT(*) AS total_records FROM users
UNION ALL SELECT 'vendors', COUNT(*) FROM vendors
UNION ALL SELECT 'menu_items', COUNT(*) FROM menu_items
UNION ALL SELECT 'promotions', COUNT(*) FROM promotions
UNION ALL SELECT 'orders', COUNT(*) FROM orders
UNION ALL SELECT 'order_items', COUNT(*) FROM order_items
UNION ALL SELECT 'reviews', COUNT(*) FROM reviews
UNION ALL SELECT 'notifications', COUNT(*) FROM notifications;
