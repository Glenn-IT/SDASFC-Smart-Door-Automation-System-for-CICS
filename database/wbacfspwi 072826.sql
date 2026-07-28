-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 28, 2026 at 03:46 PM
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
-- Database: `wbacfspwi`
--

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
  `type` enum('low_moisture','low_battery','pump_fail','schedule_conflict') NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alerts`
--

INSERT INTO `alerts` (`id`, `type`, `message`, `is_read`, `created_at`) VALUES
(1, 'low_moisture', 'Soil moisture low: 15.5%', 1, '2026-07-06 15:48:54'),
(2, 'low_battery', 'Battery voltage low: 11V', 1, '2026-07-06 15:48:54'),
(3, 'low_moisture', 'Soil moisture low: 12%', 1, '2026-07-06 19:10:01'),
(4, 'low_battery', 'Battery voltage low: 10.9V', 1, '2026-07-06 19:10:01');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'login', 'User logged in', '127.0.0.1', '2026-07-06 15:42:57'),
(2, 1, 'schedule_create', 'Created schedule #1 (Morning Watering)', '127.0.0.1', '2026-07-06 15:42:58'),
(3, 1, 'schedule_toggle', 'Schedule #1 set to inactive', '127.0.0.1', '2026-07-06 15:43:10'),
(4, 1, 'profile_update', 'Updated name/email', '127.0.0.1', '2026-07-06 15:43:10'),
(5, 1, 'login', 'User logged in', '127.0.0.1', '2026-07-06 15:49:34'),
(6, 1, 'login', 'User logged in', '127.0.0.1', '2026-07-06 19:09:00'),
(7, 1, 'alert_mark_read', 'Marked alert #3 as read', '127.0.0.1', '2026-07-06 19:10:09'),
(8, 1, 'manual_override', 'Pump forced on for 30min (Testing dry spell)', '127.0.0.1', '2026-07-06 19:10:54'),
(9, 1, 'user_create', 'Created user #2 (viewer@wbacfspwi.local, viewer)', '127.0.0.1', '2026-07-06 19:11:23'),
(10, NULL, 'login', 'User logged in', '127.0.0.1', '2026-07-06 19:39:12'),
(11, 1, 'user_delete', 'Deleted user #2 (viewer@wbacfspwi.local)', '127.0.0.1', '2026-07-06 19:51:24'),
(12, 1, 'login', 'User logged in', '127.0.0.1', '2026-07-06 22:26:58'),
(13, 1, 'login', 'User logged in', '127.0.0.1', '2026-07-06 22:50:03'),
(14, 1, 'login', 'User logged in', '127.0.0.1', '2026-07-06 23:12:21'),
(15, 1, 'security_question_update', 'Updated security question', '127.0.0.1', '2026-07-06 23:18:12'),
(16, 1, 'password_reset', 'Password reset via forgot-password flow', '127.0.0.1', '2026-07-06 23:38:20'),
(17, 1, 'login', 'User logged in', '127.0.0.1', '2026-07-06 23:38:21'),
(18, 1, 'password_change', 'Changed own password', '127.0.0.1', '2026-07-06 23:43:49'),
(19, 1, 'login', 'User logged in', '127.0.0.1', '2026-07-06 23:47:56'),
(20, 1, 'security_question_update', 'Updated security question', '127.0.0.1', '2026-07-06 23:48:19'),
(21, 1, 'password_reset', 'Password reset via forgot-password flow', '127.0.0.1', '2026-07-06 23:48:39'),
(22, 1, 'login', 'User logged in', '127.0.0.1', '2026-07-06 23:48:44'),
(23, 1, 'password_reset', 'Password reset via forgot-password flow', '127.0.0.1', '2026-07-07 00:03:53'),
(24, 1, 'login', 'User logged in', '127.0.0.1', '2026-07-07 00:03:53'),
(25, 1, 'login', 'User logged in', '127.0.0.1', '2026-07-07 00:07:16'),
(26, 1, 'security_question_update', 'Updated security question', '127.0.0.1', '2026-07-07 00:07:27'),
(27, 1, 'password_reset', 'Password reset via forgot-password flow', '127.0.0.1', '2026-07-07 00:07:47'),
(28, 1, 'login', 'User logged in', '::1', '2026-07-09 00:01:23'),
(29, 1, 'security_question_update', 'Updated security question', '::1', '2026-07-09 00:01:43'),
(30, 1, 'password_reset', 'Password reset via forgot-password flow', '::1', '2026-07-09 00:02:01'),
(31, 1, 'login', 'User logged in', '::1', '2026-07-09 00:02:11'),
(32, 1, 'login', 'User logged in', '::1', '2026-07-09 00:02:27'),
(33, NULL, 'login_lockout', 'Login locked for wrong@example.com after 3 failed attempts', '::1', '2026-07-09 00:10:00'),
(34, NULL, 'login_lockout', 'Login locked for wrong@example.com after 3 failed attempts', '::1', '2026-07-09 00:11:39'),
(35, NULL, 'login_lockout', 'Login locked for admin@wbacfspwi.local after 3 failed attempts', '::1', '2026-07-09 00:12:17'),
(36, 1, 'login', 'User logged in', '::1', '2026-07-09 00:57:29'),
(37, 1, 'login', 'User logged in', '::1', '2026-07-09 01:31:31'),
(38, NULL, 'login_lockout', 'Login locked for adw@gmail.com after 3 failed attempts', '::1', '2026-07-13 17:52:54'),
(39, NULL, 'login_lockout', 'Login locked for asd@aw.gmail.com after 3 failed attempts', '::1', '2026-07-13 18:25:42'),
(40, 1, 'login', 'User logged in', '::1', '2026-07-14 16:46:56'),
(41, 1, 'login', 'User logged in', '::1', '2026-07-15 21:29:09'),
(42, 1, 'login', 'User logged in', '::1', '2026-07-16 01:37:50'),
(43, 1, 'login', 'User logged in', '::1', '2026-07-16 01:38:17'),
(44, 1, 'login', 'User logged in', '::1', '2026-07-21 22:24:19'),
(45, 1, 'schedule_create', 'Created schedule #2 (Lunch Watering)', '::1', '2026-07-21 22:26:49'),
(46, 1, 'schedule_toggle', 'Schedule #1 set to active', '::1', '2026-07-21 22:26:54'),
(47, 1, 'user_create', 'Created user #3 (admin@gmail.com, admin)', '::1', '2026-07-21 22:27:36'),
(48, 3, 'login', 'User logged in', '::1', '2026-07-21 22:27:50'),
(49, 3, 'security_question_update', 'Updated security question', '::1', '2026-07-21 22:28:13'),
(50, 1, 'login', 'User logged in', '::1', '2026-07-21 22:28:41'),
(51, 1, 'user_role_change', 'Set user #3 role to viewer', '::1', '2026-07-21 22:28:46'),
(52, 1, 'login', 'User logged in', '::1', '2026-07-21 22:29:02'),
(53, 3, 'login', 'User logged in', '::1', '2026-07-21 22:29:09'),
(54, 1, 'login', 'User logged in', '::1', '2026-07-21 22:29:23'),
(55, 3, 'login', 'User logged in', '::1', '2026-07-21 22:30:31'),
(56, 1, 'login', 'User logged in', '::1', '2026-07-21 22:37:16'),
(57, 1, 'login', 'User logged in', '::1', '2026-07-22 16:58:47'),
(58, 1, 'login', 'User logged in', '::1', '2026-07-22 17:19:00'),
(59, 1, 'login', 'User logged in', '::1', '2026-07-22 17:37:15'),
(60, 1, 'login', 'User logged in', '::1', '2026-07-22 17:49:32'),
(61, 1, 'login', 'User logged in', '::1', '2026-07-22 20:08:49'),
(62, 1, 'login', 'User logged in', '::1', '2026-07-23 20:08:21'),
(63, 1, 'login', 'User logged in', '::1', '2026-07-25 02:15:23'),
(64, 1, 'login', 'User logged in', '::1', '2026-07-27 21:55:25'),
(65, 1, 'login', 'User logged in', '::1', '2026-07-28 20:47:04'),
(66, 1, 'alert_mark_read', 'Marked alert #4 as read', '::1', '2026-07-28 21:01:32'),
(67, 1, 'alert_mark_all_read', 'Marked all alerts as read', '::1', '2026-07-28 21:01:33');

-- --------------------------------------------------------

--
-- Table structure for table `irrigation_events`
--

CREATE TABLE `irrigation_events` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `trigger_type` enum('scheduled','manual') NOT NULL,
  `started_at` datetime NOT NULL,
  `ended_at` datetime DEFAULT NULL,
  `status` enum('running','completed','aborted') NOT NULL DEFAULT 'running'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `irrigation_events`
--

INSERT INTO `irrigation_events` (`id`, `schedule_id`, `trigger_type`, `started_at`, `ended_at`, `status`) VALUES
(1, NULL, 'manual', '2026-07-06 15:48:54', '2026-07-06 22:32:32', 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `overrides`
--

CREATE TABLE `overrides` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` enum('on','off') NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `auto_revert_minutes` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `overrides`
--

INSERT INTO `overrides` (`id`, `user_id`, `action`, `reason`, `auto_revert_minutes`, `created_at`) VALUES
(1, 1, 'on', 'Testing dry spell', 30, '2026-07-06 19:10:54');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `label` varchar(100) NOT NULL,
  `start_time` time NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `days_of_week` set('mon','tue','wed','thu','fri','sat','sun') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `label`, `start_time`, `duration_minutes`, `days_of_week`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Morning Watering', '06:00:00', 15, 'mon,wed,fri', 1, 1, '2026-07-06 15:42:58', '2026-07-21 22:26:54'),
(2, 'Lunch Watering', '22:26:00', 15, 'mon,tue', 1, 1, '2026-07-21 22:26:49', '2026-07-21 22:26:49');

-- --------------------------------------------------------

--
-- Table structure for table `sensor_readings`
--

CREATE TABLE `sensor_readings` (
  `id` int(11) NOT NULL,
  `soil_moisture` decimal(5,2) DEFAULT NULL,
  `water_level` decimal(5,2) DEFAULT NULL,
  `battery_voltage` decimal(5,2) DEFAULT NULL,
  `solar_output` decimal(6,2) DEFAULT NULL,
  `pump_state` enum('on','off') NOT NULL DEFAULT 'off',
  `recorded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sensor_readings`
--

INSERT INTO `sensor_readings` (`id`, `soil_moisture`, `water_level`, `battery_voltage`, `solar_output`, `pump_state`, `recorded_at`) VALUES
(1, 15.50, NULL, 11.00, 22.30, 'on', '2026-07-06 15:48:54'),
(2, 12.00, NULL, 10.90, NULL, 'on', '2026-07-06 19:10:01'),
(3, 40.00, NULL, 12.50, NULL, 'off', '2026-07-06 22:32:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','viewer') NOT NULL DEFAULT 'viewer',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `security_question` varchar(50) DEFAULT NULL,
  `security_answer_hash` varchar(255) DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `is_active`, `security_question`, `security_answer_hash`, `last_login_at`, `created_at`) VALUES
(1, 'Super Admin', 'admin@wbacfspwi.local', '$2y$10$TEKwnvxpaMtF1Hu/7kbaVu2z./ak1Zie2Ppkq9zK6vcfkHFCTaLsS', 'super_admin', 1, 'first_pet', '$2y$10$n40lYTsOwrVQTBC8NpjVFO9l6KL0QlQttxRIBQ4xjxqZ.DwGWs.aO', '2026-07-28 20:47:04', '2026-07-06 15:38:01'),
(3, 'Admin', 'admin@gmail.com', '$2y$10$qQ5ZDrvAh/VGDnOguzHKnOs6k2BO8zfjLdsxMPHBsXaPDpldcKy4W', 'viewer', 1, 'first_pet', '$2y$10$Apvou.IuW/49CZuaq9mtseizEJ.dblE0fv7d1wsFzm/4fh4Gwbq4W', '2026-07-21 22:30:31', '2026-07-21 22:27:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `irrigation_events`
--
ALTER TABLE `irrigation_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `overrides`
--
ALTER TABLE `overrides`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `sensor_readings`
--
ALTER TABLE `sensor_readings`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `irrigation_events`
--
ALTER TABLE `irrigation_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `overrides`
--
ALTER TABLE `overrides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sensor_readings`
--
ALTER TABLE `sensor_readings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `irrigation_events`
--
ALTER TABLE `irrigation_events`
  ADD CONSTRAINT `irrigation_events_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `overrides`
--
ALTER TABLE `overrides`
  ADD CONSTRAINT `overrides_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
