-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 19, 2026 at 05:54 AM
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
-- Database: `sdasfc`
--

-- --------------------------------------------------------

--
-- Table structure for table `access_logs`
--

CREATE TABLE `access_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rfid_uid` varchar(50) NOT NULL,
  `scanned_at` datetime NOT NULL DEFAULT current_timestamp(),
  `result` enum('granted','denied') NOT NULL,
  `reason` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `access_logs`
--

INSERT INTO `access_logs` (`id`, `user_id`, `rfid_uid`, `scanned_at`, `result`, `reason`) VALUES
(146, 20, '93 39 6E 1B', '2026-09-09 18:34:50', 'granted', 'ok'),
(147, NULL, '03 98 4D 25', '2026-09-09 18:35:00', 'denied', 'unknown_uid'),
(148, NULL, '03 98 4D 25', '2026-09-09 18:35:04', 'denied', 'unknown_uid'),
(149, NULL, '08 29 11 02', '2026-09-09 18:35:13', 'denied', 'unknown_uid'),
(150, NULL, '08 3B 73 5D', '2026-09-09 18:35:15', 'denied', 'unknown_uid'),
(151, NULL, '08 3F F2 84', '2026-09-09 18:35:18', 'denied', 'unknown_uid'),
(152, NULL, '08 73 C5 B9', '2026-09-09 18:35:22', 'denied', 'unknown_uid'),
(153, 20, '93 39 6E 1B', '2026-09-09 18:35:36', 'granted', 'ok'),
(154, 20, '93 39 6E 1B', '2026-09-09 19:15:11', 'granted', 'ok'),
(155, 20, '93 39 6E 1B', '2026-09-09 19:15:17', 'granted', 'ok'),
(156, 20, '93 39 6E 1B', '2026-09-09 19:16:03', 'granted', 'ok'),
(157, NULL, '03 98 4D 25', '2026-09-09 20:22:26', 'denied', 'unknown_uid'),
(158, NULL, '03 98 4D 25', '2026-09-09 20:22:28', 'denied', 'unknown_uid'),
(159, NULL, '03 98 4D 25', '2026-09-09 20:22:32', 'denied', 'unknown_uid'),
(160, NULL, '03 98 4D 25', '2026-09-09 20:22:37', 'denied', 'unknown_uid'),
(161, NULL, '03 98 4D 25', '2026-09-09 20:23:26', 'granted', 'ok'),
(162, NULL, 'C6 85 C6 01', '2026-09-09 20:23:46', 'denied', 'unknown_uid'),
(163, NULL, '0A 75 B4 02', '2026-09-09 20:23:50', 'denied', 'unknown_uid'),
(164, NULL, '0A 75 B4 02', '2026-09-09 20:23:57', 'denied', 'unknown_uid'),
(165, NULL, '0A 75 B4 02', '2026-09-09 20:24:01', 'denied', 'unknown_uid'),
(166, NULL, 'C6 85 C6 01', '2026-09-09 20:24:06', 'denied', 'unknown_uid'),
(167, 20, '93 39 6E 1B', '2026-09-09 20:24:22', 'granted', 'ok'),
(168, 20, '93 39 6E 1B', '2026-09-09 20:24:48', 'granted', 'ok'),
(169, NULL, '03 98 4D 25', '2026-09-09 20:24:50', 'granted', 'ok'),
(170, NULL, '03 98 4D 25', '2026-09-09 20:26:21', 'granted', 'ok'),
(171, 20, '93 39 6E 1B', '2026-09-09 20:39:44', 'granted', 'ok'),
(172, NULL, '03 98 4D 25', '2026-09-09 20:39:48', 'granted', 'ok'),
(173, 20, '93 39 6E 1B', '2026-09-09 20:40:02', 'granted', 'ok'),
(174, NULL, '0A 75 B4 02', '2026-09-09 20:40:06', 'denied', 'unknown_uid'),
(175, NULL, '0A 75 B4 02', '2026-09-09 20:40:10', 'denied', 'unknown_uid'),
(176, 20, '93 39 6E 1B', '2026-09-09 20:40:53', 'granted', 'ok'),
(177, NULL, '03 98 4D 25', '2026-09-09 20:40:59', 'granted', 'ok'),
(178, NULL, '0A 75 B4 02', '2026-09-13 13:06:39', 'denied', 'unknown_uid'),
(179, NULL, '03 98 4D 25', '2026-09-13 13:06:43', 'granted', 'ok'),
(180, NULL, '0A 75 B4 02', '2026-09-13 13:06:53', 'denied', 'unknown_uid'),
(181, 20, '93 39 6E 1B', '2026-09-13 13:07:02', 'granted', 'ok'),
(182, 20, '93 39 6E 1B', '2026-09-13 13:07:14', 'granted', 'ok'),
(183, NULL, '03 98 4D 25', '2026-09-13 13:07:16', 'granted', 'ok'),
(184, NULL, '0A 75 B4 02', '2026-09-13 13:07:28', 'denied', 'unknown_uid'),
(185, 20, '93 39 6E 1B', '2026-09-13 13:10:05', 'granted', 'ok'),
(186, NULL, '03 98 4D 25', '2026-09-13 13:10:09', 'granted', 'ok'),
(187, NULL, '0A 75 B4 02', '2026-09-13 13:10:17', 'denied', 'unknown_uid'),
(188, NULL, '03 98 4D 25', '2026-09-13 13:33:59', 'granted', 'ok'),
(189, NULL, '03 98 4D 25', '2026-09-13 13:34:49', 'granted', 'ok'),
(190, 20, '93 39 6E 1B', '2026-09-13 13:35:04', 'granted', 'ok'),
(191, NULL, '0A 75 B4 02', '2026-09-13 13:35:05', 'denied', 'unknown_uid'),
(192, NULL, '0A 75 B4 02', '2026-09-13 13:35:09', 'denied', 'unknown_uid'),
(193, 20, '93 39 6E 1B', '2026-09-13 14:16:54', 'granted', 'ok'),
(194, NULL, '03 98 4D 25', '2026-09-13 14:18:14', 'denied', 'unknown_uid'),
(195, NULL, '0A 75 B4 02', '2026-09-13 14:18:18', 'denied', 'unknown_uid'),
(196, NULL, '0A 75 B4 02', '2026-09-13 14:18:57', 'denied', 'unknown_uid'),
(197, 22, '0A 75 B4 02', '2026-09-13 14:19:14', 'granted', 'ok'),
(198, NULL, '03 98 4D 25', '2026-09-13 14:19:26', 'denied', 'unknown_uid'),
(199, 22, '0A 75 B4 02', '2026-09-13 14:19:39', 'denied', 'inactive_user');

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `admin_id`, `action`, `description`, `created_at`) VALUES
(37, 1, 'login', 'Admin System Administrator logged in', '2026-09-09 19:59:15'),
(38, 1, 'user_created', 'Created user Sample 1 (ID 123qwe312)', '2026-09-09 20:23:19'),
(39, 1, 'logout', 'Admin System Administrator logged out', '2026-09-09 20:36:46'),
(40, 1, 'login', 'Admin System Administrator logged in', '2026-09-09 20:39:17'),
(41, 1, 'login', 'Admin System Administrator logged in', '2026-09-13 13:04:05'),
(42, 1, 'logout', 'Admin System Administrator logged out', '2026-09-13 14:16:35'),
(43, 1, 'login', 'Admin System Administrator logged in', '2026-09-13 14:16:38'),
(44, 1, 'user_created', 'Created user sample 1 (ID 1231312)', '2026-09-13 14:19:11'),
(45, 1, 'user_status_toggled', 'Set user sample 1 to inactive', '2026-09-13 14:19:32');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer_hash` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `full_name`, `security_question`, `security_answer_hash`, `created_at`) VALUES
(1, 'admin', '$2y$10$SUj/u8bCONFGKfFXc8mbC.yONC0Tz8oeeP2xBLB2oRXVIqxsCWFo6', 'System Administrator', 'What is your mother\'s maiden name?', '$2y$10$NL6FspXPq/8ZCPBnfs5mH.TnZAEWbDs6sa7mJZIeirafIZz5SSc3W', '2026-07-06 10:20:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `id_number` varchar(50) NOT NULL,
  `rfid_uid` varchar(50) NOT NULL,
  `role` enum('student','faculty','staff') NOT NULL DEFAULT 'student',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `id_number`, `rfid_uid`, `role`, `status`, `created_at`) VALUES
(20, 'Master Emergency Key (Admin)', 'MASTER-001', '93 39 6E 1B', 'faculty', 'active', '2026-09-09 18:27:07'),
(22, 'sample 1', '1231312', '0A 75 B4 02', 'faculty', 'inactive', '2026-09-13 14:19:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access_logs`
--
ALTER TABLE `access_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_logs_scanned_at` (`scanned_at`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `idx_activity_created_at` (`created_at`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_number` (`id_number`),
  ADD UNIQUE KEY `rfid_uid` (`rfid_uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access_logs`
--
ALTER TABLE `access_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=200;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `access_logs`
--
ALTER TABLE `access_logs`
  ADD CONSTRAINT `access_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
