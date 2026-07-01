-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2026 at 10:35 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `toleo`
--

-- --------------------------------------------------------

--
-- Table structure for table `institutions`
--

CREATE TABLE `institutions` (
  `inst_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `type` varchar(50) NOT NULL,
  `location` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

CREATE TABLE `listings` (
  `listing_id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `crop_name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `approved_price` decimal(10,2) DEFAULT NULL,
  `admin_transport_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `admin_platform_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit` varchar(20) NOT NULL DEFAULT 'kg',
  `quantity` decimal(10,2) NOT NULL,
  `subcounty` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `delivery_available` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','sold_out') NOT NULL DEFAULT 'active',
  `approval_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `listings`
--

INSERT INTO `listings` (`listing_id`, `farmer_id`, `crop_name`, `category`, `price`, `approved_price`, `admin_transport_fee`, `admin_platform_fee`, `unit`, `quantity`, `subcounty`, `description`, `delivery_available`, `status`, `approval_status`, `approved_by`, `approved_at`, `admin_notes`, `created_at`) VALUES
(1, 7, 'Fresh Tomates', 'Vegetables', 100.00, 100.00, 0.00, 0.00, 'kg', 500.00, 'Kiambu', 'very ripe', 1, 'active', 'pending', NULL, NULL, NULL, '2026-03-24 14:20:34'),
(2, 10, 'Potatoes', 'Vegetables', 100.00, 110.00, 250.00, 50.00, 'kg', 5.00, 'Kikuyu', 'Ripe and fresh frm the farm', 1, 'active', 'approved', 5, '2026-04-30 14:31:30', '', '2026-04-14 12:15:25'),
(3, 10, 'Red Onions', 'Vegetables', 50.00, 60.00, 250.00, 50.00, 'kg', 230.00, 'Other (Kiambu)', '', 1, 'active', 'approved', 5, '2026-04-18 21:22:19', '', '2026-04-16 23:57:38'),
(4, 11, 'Green Bananas', 'Fruits', 50.00, 60.00, 250.00, 50.00, 'kg', 400.00, 'Thika West', 'Green bananas for cooking', 0, 'active', 'approved', 5, '2026-04-30 14:30:48', '', '2026-04-30 14:27:46');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `produce_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `farmer_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `admin_markup_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `transport_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `platform_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `source_location` varchar(100) DEFAULT NULL,
  `destination_location` varchar(100) DEFAULT NULL,
  `transport_option_id` int(11) DEFAULT NULL,
  `payment_option` enum('pay_now','pay_after_delivery') NOT NULL DEFAULT 'pay_now',
  `payment_status` enum('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `payment_reference` varchar(100) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `farmer_payout_status` enum('pending','disbursed') NOT NULL DEFAULT 'pending',
  `farmer_payout_reference` varchar(100) DEFAULT NULL,
  `farmer_paid_at` datetime DEFAULT NULL,
  `transport_payout_status` enum('pending','disbursed') NOT NULL DEFAULT 'pending',
  `transport_payout_reference` varchar(100) DEFAULT NULL,
  `transport_paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `listing_id`, `buyer_id`, `quantity`, `produce_total`, `farmer_total`, `admin_markup_total`, `transport_fee`, `platform_fee`, `total`, `source_location`, `destination_location`, `transport_option_id`, `payment_option`, `payment_status`, `payment_reference`, `paid_at`, `farmer_payout_status`, `farmer_payout_reference`, `farmer_paid_at`, `transport_payout_status`, `transport_payout_reference`, `transport_paid_at`, `created_at`) VALUES
(1, 1, 8, 5.00, 500.00, 500.00, 0.00, 0.00, 0.00, 500.00, 'Kiambu', 'Nairobi', NULL, 'pay_now', 'unpaid', NULL, NULL, 'pending', NULL, NULL, 'pending', NULL, NULL, '2026-03-24 15:15:50'),
(2, 1, 9, 2.00, 200.00, 200.00, 0.00, 0.00, 0.00, 200.00, 'Kiambu', 'Kikuyu', NULL, 'pay_now', 'unpaid', NULL, NULL, 'pending', NULL, NULL, 'pending', NULL, NULL, '2026-04-13 15:50:38'),
(3, 2, 9, 9.00, 900.00, 900.00, 0.00, 0.00, 0.00, 900.00, 'Kikuyu', 'Kikuyu', NULL, 'pay_now', 'unpaid', NULL, NULL, 'pending', NULL, NULL, 'pending', NULL, NULL, '2026-04-14 13:21:39'),
(4, 2, 9, 8.00, 800.00, 800.00, 0.00, 0.00, 0.00, 800.00, 'Kikuyu', 'Kikuyu', NULL, 'pay_now', 'unpaid', NULL, NULL, 'pending', NULL, NULL, 'pending', NULL, NULL, '2026-04-16 11:48:56'),
(5, 1, 9, 17.00, 1700.00, 1700.00, 0.00, 250.00, 50.00, 2000.00, 'Kiambu', 'Kikuyu', NULL, 'pay_now', 'paid', 'ORD_5_1776332103', '2026-04-16 12:35:46', 'pending', NULL, NULL, 'pending', NULL, NULL, '2026-04-16 12:35:03'),
(6, 2, 9, 13.00, 1300.00, 1300.00, 0.00, 250.00, 50.00, 1600.00, 'Kikuyu', 'Kikuyu', NULL, 'pay_after_delivery', 'paid', 'ORD_6_1776332224', '2026-04-16 12:38:00', 'pending', NULL, NULL, 'pending', NULL, NULL, '2026-04-16 12:36:41'),
(7, 1, 9, 7.00, 700.00, 700.00, 0.00, 250.00, 50.00, 1000.00, 'Kiambu', 'Kikuyu', 2, 'pay_now', 'paid', 'ORD_7_1776374787', '2026-04-17 00:26:58', 'disbursed', '700', '2026-04-18 21:25:29', 'disbursed', '250', '2026-04-18 21:26:25', '2026-04-17 00:20:23'),
(8, 3, 9, 20.00, 1200.00, 1000.00, 200.00, 250.00, 50.00, 1500.00, 'Other (Kiambu)', 'Kikuyu', NULL, 'pay_after_delivery', 'unpaid', NULL, NULL, 'pending', NULL, NULL, 'pending', NULL, NULL, '2026-04-18 21:43:52'),
(9, 4, 12, 2.00, 120.00, 100.00, 20.00, 250.00, 50.00, 420.00, 'Thika West', 'Nairobi', 2, 'pay_now', 'paid', 'ORD_9_1777548897', '2026-04-30 14:35:52', 'pending', NULL, NULL, 'pending', NULL, NULL, '2026-04-30 14:34:57'),
(10, 3, 12, 4.00, 240.00, 200.00, 40.00, 250.00, 50.00, 540.00, 'Other (Kiambu)', 'Nairobi', NULL, 'pay_after_delivery', 'unpaid', NULL, NULL, 'pending', NULL, NULL, 'pending', NULL, NULL, '2026-04-30 14:36:34'),
(11, 3, 12, 8.00, 480.00, 400.00, 80.00, 250.00, 50.00, 780.00, 'Other (Kiambu)', 'Nairobi', NULL, 'pay_now', 'paid', 'ORD_11_1777981469', '2026-05-05 14:49:32', 'pending', NULL, NULL, 'pending', NULL, NULL, '2026-05-05 14:28:19');

-- --------------------------------------------------------

--
-- Table structure for table `order_status`
--

CREATE TABLE `order_status` (
  `status_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` enum('pending','dispatched','delivered') NOT NULL DEFAULT 'pending',
  `updated_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_status`
--

INSERT INTO `order_status` (`status_id`, `order_id`, `status`, `updated_by`, `created_at`) VALUES
(1, 1, 'pending', 8, '2026-03-24 15:15:50'),
(2, 1, 'dispatched', 7, '2026-03-24 15:40:05'),
(3, 1, 'delivered', 7, '2026-03-24 15:40:12'),
(4, 2, 'pending', 9, '2026-04-13 15:50:38'),
(5, 3, 'pending', 9, '2026-04-14 13:21:39'),
(6, 4, 'pending', 9, '2026-04-16 11:48:56'),
(7, 5, 'pending', 9, '2026-04-16 12:35:03'),
(8, 6, 'pending', 9, '2026-04-16 12:36:41'),
(9, 6, 'delivered', 10, '2026-04-16 12:40:20'),
(10, 7, 'pending', 9, '2026-04-17 00:20:23'),
(11, 8, 'pending', 9, '2026-04-18 21:43:52'),
(12, 9, 'pending', 12, '2026-04-30 14:34:57'),
(13, 10, 'pending', 12, '2026-04-30 14:36:34'),
(14, 11, 'pending', 12, '2026-05-05 14:28:19');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `request_id` int(11) NOT NULL,
  `inst_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `status` varchar(30) DEFAULT 'Pending',
  `request_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_updates`
--

CREATE TABLE `stock_updates` (
  `stock_update_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `remaining_quantity` decimal(10,2) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_updates`
--

INSERT INTO `stock_updates` (`stock_update_id`, `listing_id`, `farmer_id`, `order_id`, `remaining_quantity`, `note`, `created_at`) VALUES
(1, 2, 10, 6, 5.00, '', '2026-04-28 10:07:15');

-- --------------------------------------------------------

--
-- Table structure for table `transport_options`
--

CREATE TABLE `transport_options` (
  `transport_option_id` int(11) NOT NULL,
  `option_name` varchar(100) NOT NULL,
  `transport_type` varchar(40) NOT NULL,
  `owner_name` varchar(100) NOT NULL,
  `owner_phone` varchar(30) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transport_options`
--

INSERT INTO `transport_options` (`transport_option_id`, `option_name`, `transport_type`, `owner_name`, `owner_phone`, `status`, `notes`, `created_at`) VALUES
(1, 'Kiambu Lorry Fleet', 'lorry', 'Main Lorry Team', '', 'active', 'Suitable for bulk farm produce and longer delivery trips.', '2026-04-17 22:30:00'),
(2, 'Kiambu Motorbike Courier', 'motorbike', 'Main Rider Team', '', 'active', 'Suitable for lighter and urgent deliveries.', '2026-04-17 22:30:00'),
(3, 'Extra county lorry delivery', 'lorry', 'Waweru Danson', '0788272592', 'active', 'suitable for bulky deliveries outside Kiambu county', '2026-04-18 21:20:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('farmer','buyer','admin') NOT NULL DEFAULT 'buyer',
  `county` varchar(80) NOT NULL DEFAULT 'Nairobi',
  `subcounty` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `phone`, `role`, `county`, `subcounty`, `password`) VALUES
(3, 'Ohm', 'lukam2837@gmail.com', '0733567890', 'buyer', 'Nairobi', NULL, '827ccb0eea8a706c4c34a16891f84e7b'),
(4, 'rita123', 'ritanjoki27@gmail.com', '0744567891', 'farmer', 'Nakuru', NULL, '7c9518e8ea0deea1ceaaae9b24c0623f'),
(5, 'riri24', 'riri24@gmail.com', '0755678902', 'admin', 'any', 'any', '$2y$10$EABcJ88A3mNRHOD7jmi9MuwQdyCKuSszBgXzqWX7x7.Q.e0NuGKsy'),
(6, 'shilalia234', 'shilalia2342@gmail.com', '0766789013', 'buyer', 'Nairobi', NULL, '2321994d85d661d792223f647000c65f'),
(7, 'riri2424', 'matwana@gmail.com', '0710705205', 'farmer', 'Kiambu', NULL, '8daab76db4b4c13ae4571c93d98ee4ad'),
(8, 'samuel123', 'samuel123@gmail.com', '0710705205', 'buyer', 'Nairobi', NULL, 'e6fb448feb2fa877aab63b3713027775'),
(9, 'Cynthia254', 'cynthia254@gmail.cm', '0792882725', 'buyer', 'Kikuyu', NULL, '4780c127fa1c163aa0185bf42f44db69'),
(10, 'MainaPatrick', 'patrickmaina254@gmail.com', '0710705206', 'farmer', 'Thika East', NULL, '0fa758773dae755f411c9fd81b17a69c'),
(11, 'leiman', 'leiman23@gmail.com', '0710704204', 'farmer', 'Thika West', NULL, '5a98ccb8eb8bc21cf4244bdedb93eb5a'),
(12, 'Salima', 'salima123@gmail.com', '0792882724', 'buyer', 'Nairobi', NULL, '$2y$10$XGzVe3W.lyz6jXJjRTj9QOx..Pkg6dLgJDEcbLGHLTVhD7rsegtUq');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `institutions`
--
ALTER TABLE `institutions`
  ADD PRIMARY KEY (`inst_id`);

--
-- Indexes for table `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`listing_id`),
  ADD KEY `farmer_id` (`farmer_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `listing_id` (`listing_id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indexes for table `order_status`
--
ALTER TABLE `order_status`
  ADD PRIMARY KEY (`status_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `inst_id` (`inst_id`);

--
-- Indexes for table `stock_updates`
--
ALTER TABLE `stock_updates`
  ADD PRIMARY KEY (`stock_update_id`);

--
-- Indexes for table `transport_options`
--
ALTER TABLE `transport_options`
  ADD PRIMARY KEY (`transport_option_id`);

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
-- AUTO_INCREMENT for table `institutions`
--
ALTER TABLE `institutions`
  MODIFY `inst_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `listings`
--
ALTER TABLE `listings`
  MODIFY `listing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_status`
--
ALTER TABLE `order_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_updates`
--
ALTER TABLE `stock_updates`
  MODIFY `stock_update_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transport_options`
--
ALTER TABLE `transport_options`
  MODIFY `transport_option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `listings`
--
ALTER TABLE `listings`
  ADD CONSTRAINT `listings_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listing_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_status`
--
ALTER TABLE `order_status`
  ADD CONSTRAINT `order_status_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_status_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`inst_id`) REFERENCES `institutions` (`inst_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
