-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 02, 2025 at 04:21 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventory_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `borrow_requests`
--

CREATE TABLE `borrow_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `ched_request` varchar(255) DEFAULT NULL,
  `quantity_borrowed` int(11) DEFAULT 0,
  `quantity_returned` int(11) DEFAULT 0,
  `status` enum('Available','Not Available','For Delivery') DEFAULT 'Available',
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `available_quantity` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `borrowed_quantity` int(11) DEFAULT 0,
  `borrowed` int(11) DEFAULT 0,
  `returned` int(11) DEFAULT 0,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `category`, `quantity`, `available_quantity`, `description`, `department`, `created_at`, `borrowed_quantity`, `borrowed`, `returned`, `remarks`) VALUES
(9, 'Bell boys cart', 'Tools', 2, 2, NULL, NULL, '2025-05-02 01:56:07', 0, 0, 0, NULL),
(10, 'Credit Card Voucher', 'Tools', 1, 1, NULL, NULL, '2025-05-02 01:56:26', 0, 0, 0, NULL),
(11, 'Calculator', 'Equipment', 5, 5, NULL, NULL, '2025-05-02 01:56:52', 0, 0, 0, NULL),
(12, 'Cash Box Drawer', 'Equipment', 1, 1, NULL, NULL, '2025-05-02 01:57:18', 0, 0, 0, NULL),
(13, 'Cash Register', 'Equipment', 1, 1, NULL, NULL, '2025-05-02 01:57:36', 0, 0, 0, NULL),
(14, 'Clocks at least 4 various time zone', 'Equipment', 4, 4, NULL, NULL, '2025-05-02 01:57:53', 0, 0, 0, NULL),
(15, 'Computer (with reservation System) PMS', 'Equipment', 2, 2, NULL, NULL, '2025-05-02 01:58:08', 0, 0, 0, NULL),
(16, 'Credit card Imprinter', 'Equipment', 1, 1, NULL, NULL, '2025-05-02 01:58:22', 0, 0, 0, NULL),
(17, 'Etpos – Electronic funds Transfer at point of Sale', 'Equipment', 1, 1, NULL, NULL, '2025-05-02 01:58:35', 0, 0, 0, NULL),
(18, 'Fake bills Detector', 'Equipment', 1, 1, NULL, NULL, '2025-05-02 01:58:46', 0, 0, 0, NULL),
(19, 'Fax Machine', 'Equipment', 1, 1, NULL, NULL, '2025-05-02 01:59:00', 0, 0, 0, NULL),
(20, 'Front Office Desk', 'Equipment', 1, 1, NULL, NULL, '2025-05-02 01:59:14', 0, 0, 0, NULL),
(21, 'Guest folio Rack', 'Equipment', 1, 1, NULL, NULL, '2025-05-02 01:59:26', 0, 0, 0, NULL),
(22, 'Hypercom', 'Equipment', 1, 1, NULL, NULL, '2025-05-02 01:59:34', 0, 0, 0, NULL),
(23, 'Key Card Marker', 'Equipment', 1, 1, NULL, NULL, '2025-05-02 01:59:43', 0, 0, 0, NULL),
(24, 'Key Card Verifier', 'Equipment', 1, 1, NULL, NULL, '2025-05-02 01:59:57', 0, 0, 0, NULL),
(25, 'Key rack/ keycard holders', 'Equipment', 1, 1, NULL, NULL, '2025-05-02 02:01:43', 0, 0, 0, NULL),
(26, 'Lapel Microphone', 'Equipment', 4, 4, NULL, NULL, '2025-05-02 02:02:08', 0, 0, 0, NULL),
(27, 'Manual Credit Card Machine', 'Equipment', 1, 1, NULL, NULL, '2025-05-02 02:02:30', 0, 0, 0, NULL),
(28, 'Safety Deposit Box drop vault', 'Equipment', 1, 1, NULL, NULL, '2025-05-02 02:02:43', 0, 0, 0, NULL),
(29, 'Telephone system', 'Equipment', 2, 2, NULL, NULL, '2025-05-02 02:02:55', 0, 0, 0, NULL),
(30, 'Typewriter', 'Equipment', 1, 1, NULL, NULL, '2025-05-02 02:03:20', 0, 0, 0, NULL),
(31, 'Logbook', 'Materials', 1, 1, NULL, NULL, '2025-05-02 02:03:37', 0, 0, 0, NULL),
(32, 'Room keys', 'Materials', 10, 10, NULL, NULL, '2025-05-02 02:04:03', 0, 0, 0, NULL),
(33, 'Ving Vard', 'Materials', 2, 2, NULL, NULL, '2025-05-02 02:04:18', 0, 0, 0, NULL),
(34, 'White Board/ cork board', 'Materials', 1, 1, NULL, NULL, '2025-05-02 02:04:29', 0, 0, 0, NULL),
(35, 'Empty Envelopes', 'Materials', 5, 5, NULL, NULL, '2025-05-02 02:04:41', 0, 0, 0, NULL),
(36, 'Luggage Tag', 'Materials', 5, 5, NULL, NULL, '2025-05-02 02:04:53', 0, 0, 0, NULL),
(37, 'Registration form', 'Training Resources', 25, 25, NULL, NULL, '2025-05-02 02:05:20', 0, 0, 0, NULL),
(38, 'Cancellation booking forms', 'Training Resources', 25, 25, NULL, NULL, '2025-05-02 02:05:50', 0, 0, 0, NULL),
(39, 'No show forms', 'Training Resources', 25, 25, NULL, NULL, '2025-05-02 02:06:00', 0, 0, 0, NULL),
(40, 'General Folio', 'Training Resources', 25, 25, NULL, NULL, '2025-05-02 02:06:11', 0, 0, 0, NULL),
(41, 'Credit Card Voucher', 'Training Resources', 25, 25, NULL, NULL, '2025-05-02 02:06:22', 0, 0, 0, NULL),
(42, 'Different Forms, booking internal services', 'Training Resources', 25, 25, NULL, NULL, '2025-05-02 02:06:32', 0, 0, 0, NULL),
(43, 'Mockup room Desk Counter', 'Facility', 1, 1, NULL, NULL, '2025-05-02 02:06:50', 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `borrowed_quantity` int(11) NOT NULL,
  `returned_quantity` int(11) DEFAULT 0,
  `status` enum('Borrowed','Returned') NOT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_type` enum('admin','user') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `department`, `role`, `created_at`, `user_type`) VALUES
(2, 'admin', 'admin', 'admin@gmail.com', '$2y$10$heXRymKFqL6./83WbFty8.07rSLitgZd35kCYv8BLnPF63RICRePm', NULL, 'user', '2025-04-28 03:10:48', 'admin'),
(3, 'test', 'test', 'test@gmail.com', '$2y$10$u3Jh31grXDnbtkzYHTXEX.X6s6gzWShY9ydvCPLuA3A9lVkRnbF3q', NULL, 'user', '2025-04-28 03:19:26', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_item_id` (`item_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD CONSTRAINT `borrow_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `borrow_requests_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_item_id` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
