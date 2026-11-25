-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 24, 2026 at 11:02 AM
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
-- Database: `kidswallet`
--

-- --------------------------------------------------------

--
-- Table structure for table `approval_requests`
--

CREATE TABLE `approval_requests` (
  `request_id` bigint(20) NOT NULL,
  `child_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `status` enum('PENDING','APPROVED','REJECTED','EXPIRED') NOT NULL DEFAULT 'PENDING',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `responded_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approval_requests`
--

INSERT INTO `approval_requests` (`request_id`, `child_id`, `parent_id`, `status`, `requested_at`, `expires_at`, `responded_at`) VALUES
(1, 1, 1, 'APPROVED', '2026-11-19 02:46:25', '2026-11-19 11:46:25', '2026-11-19 11:13:14'),
(2, 1, 1, 'APPROVED', '2026-11-19 03:14:42', '2026-11-19 12:14:42', '2026-11-19 11:14:45'),
(3, 1, 1, 'APPROVED', '2026-11-19 03:20:28', '2026-11-19 12:20:28', '2026-11-19 11:20:33'),
(4, 1, 1, 'APPROVED', '2026-11-19 12:01:40', '2026-11-19 21:01:40', '2026-11-19 20:01:45'),
(5, 1, 1, 'APPROVED', '2026-11-20 07:17:59', '2026-11-20 16:17:59', '2026-11-20 15:18:10'),
(6, 1, 1, 'REJECTED', '2026-11-20 07:18:56', '2026-11-20 16:18:56', '2026-11-20 15:19:04'),
(7, 1, 1, 'APPROVED', '2026-11-20 07:20:12', '2026-11-20 16:20:12', '2026-11-20 15:20:30'),
(8, 1, 1, 'APPROVED', '2026-11-23 02:08:00', '2026-11-23 11:08:00', '2026-11-23 10:08:22'),
(9, 1, 1, 'APPROVED', '2026-11-24 09:50:19', '2026-11-24 18:50:19', '2026-11-24 17:50:26');

-- --------------------------------------------------------

--
-- Table structure for table `children`
--

CREATE TABLE `children` (
  `child_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `child_name` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `pin_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `children`
--

INSERT INTO `children` (`child_id`, `parent_id`, `child_name`, `username`, `pin_hash`, `created_at`) VALUES
(1, 1, 'Alice', 'alicerosebloom', '$2y$10$mGCajUkehcAqb8jFGf0clemxu94W4R6OamJl2/Bwm8gtc0TXYu0xy', '2026-11-19 02:42:47');

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `parent_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`parent_id`, `email`, `name`, `phone`, `password_hash`, `created_at`) VALUES
(1, 'march@gmail.com', 'March', '01234567', '$2y$10$VCOQfL9niQCW0ULELKryre55K0dfMeuBaLMOMoyeR8vaAer6lR0rS', '2026-11-18 14:28:04');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `wallet_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `transaction_type` enum('TOP_UP','PAYMENT') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `wallet_id`, `child_id`, `parent_id`, `transaction_type`, `amount`, `description`, `created_at`) VALUES
(1, 1, 1, 1, 'TOP_UP', 50.00, 'November allowance', '2026-11-19 13:04:25'),
(2, 1, 1, 1, 'PAYMENT', 20.00, '7-11', '2026-11-19 13:34:37');

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `wallet_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`wallet_id`, `child_id`, `balance`, `updated_at`) VALUES
(1, 1, 50.00, '2026-11-19 12:34:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `approval_requests`
--
ALTER TABLE `approval_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `child_id` (`child_id`,`parent_id`);

--
-- Indexes for table `children`
--
ALTER TABLE `children`
  ADD PRIMARY KEY (`child_id`),
  ADD UNIQUE KEY `unique_child_parent` (`child_id`,`parent_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`parent_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `wallet_id` (`wallet_id`),
  ADD KEY `child_id` (`child_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`wallet_id`),
  ADD KEY `child_id` (`child_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `approval_requests`
--
ALTER TABLE `approval_requests`
  MODIFY `request_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `children`
--
ALTER TABLE `children`
  MODIFY `child_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `parent_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `wallet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `approval_requests`
--
ALTER TABLE `approval_requests`
  ADD CONSTRAINT `approval_requests_ibfk_1` FOREIGN KEY (`child_id`,`parent_id`) REFERENCES `children` (`child_id`, `parent_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `children`
--
ALTER TABLE `children`
  ADD CONSTRAINT `children_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`parent_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`wallet_id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`child_id`) REFERENCES `children` (`child_id`),
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`parent_id`);

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`child_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
