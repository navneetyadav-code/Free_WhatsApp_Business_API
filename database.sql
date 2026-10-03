-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 14, 2026 at 10:48 PM
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
-- Database: `whatsapp_api`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL DEFAULT 'Default key',
  `key_hash` char(64) NOT NULL,
  `key_prefix` varchar(32) NOT NULL,
  `secret_hash` varchar(255) NOT NULL,
  `status` enum('active','disabled') NOT NULL DEFAULT 'active',
  `ip_allowlist` text DEFAULT NULL,
  `rate_per_minute` int(10) UNSIGNED NOT NULL DEFAULT 20,
  `rate_per_hour` int(10) UNSIGNED NOT NULL DEFAULT 300,
  `rate_per_day` int(10) UNSIGNED NOT NULL DEFAULT 1000,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `api_keys`
--

INSERT INTO `api_keys` (`id`, `user_id`, `name`, `key_hash`, `key_prefix`, `secret_hash`, `status`, `ip_allowlist`, `rate_per_minute`, `rate_per_hour`, `rate_per_day`, `last_used_at`, `created_at`, `updated_at`) VALUES
(2, 2, 'Default key', '29a451f98b0137588189ceb0dc07db482432f2d7f34ce3e57afe980cdceb2460', 'wa_live_eb31a502ec0d77', '$2y$10$u6KR8pm6Cdu61xgzm.6tYOYcNlFWqL5LRpX7wdIKVkIAhKi6DvOAO', 'active', NULL, 20, 300, 1000, NULL, '2026-06-02 10:09:26', '2026-06-02 10:09:26'),
(5, 1, 'Production key', '5ddc2de3cab334d1f3687ed9ab40cee4516e2fe2a948541c8b3a62bb08762ead', 'wa_live_1822799378f18a', '$2y$10$70kjvKmJFSOXSYaN6Sw5Xu/fcguA30Bwqb9l5WGtdEcCh.5Vyby32', 'active', NULL, 20, 300, 1000, NULL, '2026-06-02 10:22:12', '2026-06-02 10:22:12'),
(6, 1, 'chut me ;amd', '4320f9e96049b5d94d1a7b0628c6e2357cc45dfbf59e55312de3c92116bfa7d1', 'wa_live_090f4e71286322', '$2y$10$GB3IFGOwCqHCuo/a5Gkzueip8eTAosKy6mhhvQKeNvyIJiZyAvjp2', 'active', NULL, 20, 300, 1000, '2026-06-02 11:26:49', '2026-06-02 11:09:24', '2026-06-02 11:26:49'),
(9, 5, 'Generated key', 'b6ef0c229405c44b518c77946bd96c08d4f25d442ad0ab25e87e13763abb01ea', 'wa_live_6659f06df26c18', '$2y$10$i8.ev7qbi98E8ExCeT6Ex.zEzVCc9rKkPIUl73EY14oSaNsH4MUc6', 'active', NULL, 20, 300, 1000, NULL, '2026-07-13 14:12:52', '2026-07-13 14:12:52'),
(11, 5, 'Nabn', 'f196711ec9a0dca39926c266beeaac1c22fb5a3c2b0df1120f330ed80b2517dd', 'wa_live_b30cdf32b3cb12', '$2y$10$NnwQF9vlgpTl.PVk9LSpYeWegQ66UQKYIla4rJx2Iab4ep0FqM7fm', 'active', NULL, 20, 300, 1000, NULL, '2026-07-16 16:11:42', '2026-07-16 16:11:42'),
(13, 6, 'Production key', 'c6db3fe06c7e5962b35e46131c40f948c7e9c3e5a44be92f700f76174e6e7620', 'wa_live_21d4c217239c88', '$2y$10$yeAxOFuPZDdvNflCBS54a.Nle8b8HmgUPXWQU1/uqHQ9FxM.lwZTO', 'active', NULL, 20, 300, 1000, NULL, '2026-08-06 13:24:10', '2026-08-06 13:24:10');

-- --------------------------------------------------------

--
-- Table structure for table `api_request_logs`
--

CREATE TABLE `api_request_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `api_key_id` bigint(20) UNSIGNED DEFAULT NULL,
  `endpoint` varchar(120) NOT NULL,
  `ip_address` varchar(64) NOT NULL,
  `status_code` int(10) UNSIGNED NOT NULL,
  `request_id` varchar(64) NOT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `api_request_logs`
--

INSERT INTO `api_request_logs` (`id`, `user_id`, `api_key_id`, `endpoint`, `ip_address`, `status_code`, `request_id`, `error_message`, `created_at`) VALUES
(1, 1, NULL, 'send', '::1', 202, '28dc986f3a7b9222c4ad26a4', NULL, '2026-06-02 10:19:09'),
(2, 1, 6, 'send', '::1', 202, '59f179cfbb3fc4d4fd5ca8c0', NULL, '2026-06-02 11:20:24'),
(3, 1, 6, 'send', '::1', 202, '694f408b036d689a3a3fd3f6', NULL, '2026-06-02 11:26:19'),
(4, 1, 6, 'send', '::1', 202, '1515a4735ea582a28db24885', NULL, '2026-06-02 11:26:49');

-- --------------------------------------------------------

--
-- Table structure for table `birthday_tasks`
--

CREATE TABLE `birthday_tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `recipient_source` enum('contact','number') NOT NULL DEFAULT 'contact',
  `contact_id` bigint(20) UNSIGNED DEFAULT NULL,
  `recipient_name` varchar(160) NOT NULL,
  `recipient_phone` varchar(80) NOT NULL,
  `timezone` varchar(80) NOT NULL DEFAULT 'Asia/Kolkata',
  `birthday_month` tinyint(3) UNSIGNED NOT NULL,
  `birthday_day` tinyint(3) UNSIGNED NOT NULL,
  `send_time` time NOT NULL DEFAULT '00:00:00',
  `final_message_template` text NOT NULL,
  `emoji_pool` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`emoji_pool`)),
  `countdown_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `countdown_days_start` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `day_message_template` text DEFAULT NULL,
  `countdown_hours_start` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `hour_message_template` text DEFAULT NULL,
  `countdown_minutes_start` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `minute_interval` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `minute_message_template` text DEFAULT NULL,
  `status` enum('active','disabled') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `birthday_tasks`
--

INSERT INTO `birthday_tasks` (`id`, `user_id`, `name`, `recipient_source`, `contact_id`, `recipient_name`, `recipient_phone`, `timezone`, `birthday_month`, `birthday_day`, `send_time`, `final_message_template`, `emoji_pool`, `countdown_enabled`, `countdown_days_start`, `day_message_template`, `countdown_hours_start`, `hour_message_template`, `countdown_minutes_start`, `minute_interval`, `minute_message_template`, `status`, `created_at`, `updated_at`) VALUES
(2, 5, 'Kajal Kumari Birthday', 'number', NULL, 'Kajal didi', '9142058716', 'Asia/Kolkata', 7, 22, '00:00:00', 'Happy Birthday, my dearest sister {name}! 🎂🎉🥳 May your special day be filled with love, laughter, blessings, and beautiful memories. May Maa Durga and Ganpati Bappa always protect you, guide you, and fill your life with happiness, health, success, and peace. You are not just my sister, you are my pride, my strength, and one of the most precious gifts in my life. Stay smiling, keep shining, and may all your dreams come true this year. Wishing you a joyful birthday and a wonderful year ahead! {emoji}', '[\"🎂\",\"🎉\",\"🥳\",\"💖\",\"✨\",\"🌸\",\"🌺\",\"💐\",\"🙏\",\"👑\",\"🌟\",\"💫\",\"😊\",\"❤️\"]', 1, 7, 'Bas {days_left} hi din bachha hai didi apke birthday me Happy Birthday My dear sister in Advance! 🎉💖', 24, 'Ab sirf ek Ghnta aur 😁😅, Happy Birthday Didi in advance {emoji}', 60, 1, 'Only {minutes_left}  min left 😁 Advance me birthday my sister ❤️ {emoji}', 'active', '2026-07-14 14:45:11', '2026-07-21 17:23:11');

-- --------------------------------------------------------

--
-- Table structure for table `birthday_task_logs`
--

CREATE TABLE `birthday_task_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `birthday_task_id` bigint(20) UNSIGNED NOT NULL,
  `occurrence_year` int(10) UNSIGNED NOT NULL,
  `event_key` varchar(80) NOT NULL,
  `scheduled_for` datetime NOT NULL,
  `queued_message_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `birthday_task_logs`
--

INSERT INTO `birthday_task_logs` (`id`, `birthday_task_id`, `occurrence_year`, `event_key`, `scheduled_for`, `queued_message_id`, `created_at`) VALUES
(169, 2, 2026, 'hour-3', '2026-07-21 15:30:00', 49, '2026-07-21 15:30:00'),
(189, 2, 2026, 'hour-2', '2026-07-21 16:30:00', 50, '2026-07-21 16:30:02'),
(209, 2, 2026, 'hour-1', '2026-07-21 17:30:00', 51, '2026-07-21 17:30:01'),
(210, 2, 2026, 'minute-60', '2026-07-21 17:30:00', 52, '2026-07-21 17:30:01'),
(247, 2, 2026, 'minute-59', '2026-07-21 17:31:00', 53, '2026-07-21 17:31:01'),
(267, 2, 2026, 'minute-58', '2026-07-21 17:32:00', 54, '2026-07-21 17:32:01'),
(287, 2, 2026, 'minute-57', '2026-07-21 17:33:00', 55, '2026-07-21 17:33:01'),
(309, 2, 2026, 'minute-56', '2026-07-21 17:34:00', 56, '2026-07-21 17:34:02'),
(329, 2, 2026, 'minute-55', '2026-07-21 17:35:00', 57, '2026-07-21 17:35:02'),
(349, 2, 2026, 'minute-54', '2026-07-21 17:36:00', 58, '2026-07-21 17:36:02'),
(369, 2, 2026, 'minute-53', '2026-07-21 17:37:00', 59, '2026-07-21 17:37:02'),
(390, 2, 2026, 'minute-52', '2026-07-21 17:38:00', 60, '2026-07-21 17:38:02'),
(410, 2, 2026, 'minute-51', '2026-07-21 17:39:00', 61, '2026-07-21 17:39:02'),
(430, 2, 2026, 'minute-50', '2026-07-21 17:40:00', 62, '2026-07-21 17:40:02'),
(449, 2, 2026, 'minute-49', '2026-07-21 17:41:00', 63, '2026-07-21 17:41:00'),
(469, 2, 2026, 'minute-48', '2026-07-21 17:42:00', 64, '2026-07-21 17:42:00'),
(489, 2, 2026, 'minute-47', '2026-07-21 17:43:00', 65, '2026-07-21 17:43:00'),
(509, 2, 2026, 'minute-46', '2026-07-21 17:44:00', 66, '2026-07-21 17:44:00'),
(529, 2, 2026, 'minute-45', '2026-07-21 17:45:00', 67, '2026-07-21 17:45:00'),
(549, 2, 2026, 'minute-44', '2026-07-21 17:46:00', 68, '2026-07-21 17:46:00'),
(569, 2, 2026, 'minute-43', '2026-07-21 17:47:00', 69, '2026-07-21 17:47:00'),
(589, 2, 2026, 'minute-42', '2026-07-21 17:48:00', 70, '2026-07-21 17:48:01'),
(609, 2, 2026, 'minute-41', '2026-07-21 17:49:00', 71, '2026-07-21 17:49:01'),
(629, 2, 2026, 'minute-40', '2026-07-21 17:50:00', 72, '2026-07-21 17:50:01'),
(649, 2, 2026, 'minute-39', '2026-07-21 17:51:00', 73, '2026-07-21 17:51:01'),
(669, 2, 2026, 'minute-38', '2026-07-21 17:52:00', 74, '2026-07-21 17:52:01'),
(689, 2, 2026, 'minute-37', '2026-07-21 17:53:00', 75, '2026-07-21 17:53:01'),
(709, 2, 2026, 'minute-36', '2026-07-21 17:54:00', 76, '2026-07-21 17:54:02'),
(729, 2, 2026, 'minute-35', '2026-07-21 17:55:00', 77, '2026-07-21 17:55:02'),
(749, 2, 2026, 'minute-34', '2026-07-21 17:56:00', 78, '2026-07-21 17:56:02'),
(769, 2, 2026, 'minute-33', '2026-07-21 17:57:00', 79, '2026-07-21 17:57:02'),
(790, 2, 2026, 'minute-32', '2026-07-21 17:58:00', 80, '2026-07-21 17:58:02'),
(811, 2, 2026, 'minute-31', '2026-07-21 17:59:00', 81, '2026-07-21 17:59:02'),
(830, 2, 2026, 'minute-30', '2026-07-21 18:00:00', 82, '2026-07-21 18:00:00'),
(850, 2, 2026, 'minute-29', '2026-07-21 18:01:00', 83, '2026-07-21 18:01:00'),
(870, 2, 2026, 'minute-28', '2026-07-21 18:02:00', 84, '2026-07-21 18:02:00'),
(892, 2, 2026, 'minute-27', '2026-07-21 18:03:00', 85, '2026-07-21 18:03:00'),
(912, 2, 2026, 'minute-26', '2026-07-21 18:04:00', 86, '2026-07-21 18:04:00'),
(934, 2, 2026, 'minute-25', '2026-07-21 18:05:00', 87, '2026-07-21 18:05:00'),
(956, 2, 2026, 'minute-24', '2026-07-21 18:06:00', 88, '2026-07-21 18:06:01'),
(976, 2, 2026, 'minute-23', '2026-07-21 18:07:00', 89, '2026-07-21 18:07:01'),
(996, 2, 2026, 'minute-22', '2026-07-21 18:08:00', 90, '2026-07-21 18:08:01'),
(1016, 2, 2026, 'minute-21', '2026-07-21 18:09:00', 91, '2026-07-21 18:09:01'),
(1036, 2, 2026, 'minute-20', '2026-07-21 18:10:00', 92, '2026-07-21 18:10:01'),
(1056, 2, 2026, 'minute-19', '2026-07-21 18:11:00', 93, '2026-07-21 18:11:01'),
(1076, 2, 2026, 'minute-18', '2026-07-21 18:12:00', 94, '2026-07-21 18:12:01'),
(1096, 2, 2026, 'minute-17', '2026-07-21 18:13:00', 95, '2026-07-21 18:13:01'),
(1116, 2, 2026, 'minute-16', '2026-07-21 18:14:00', 96, '2026-07-21 18:14:01'),
(1138, 2, 2026, 'minute-15', '2026-07-21 18:15:00', 97, '2026-07-21 18:15:01'),
(1160, 2, 2026, 'minute-14', '2026-07-21 18:16:00', 98, '2026-07-21 18:16:01'),
(1182, 2, 2026, 'minute-13', '2026-07-21 18:17:00', 99, '2026-07-21 18:17:01'),
(1204, 2, 2026, 'minute-12', '2026-07-21 18:18:00', 100, '2026-07-21 18:18:02'),
(1224, 2, 2026, 'minute-11', '2026-07-21 18:19:00', 101, '2026-07-21 18:19:02'),
(1245, 2, 2026, 'minute-10', '2026-07-21 18:20:00', 102, '2026-07-21 18:20:02'),
(1266, 2, 2026, 'minute-9', '2026-07-21 18:21:00', 103, '2026-07-21 18:21:02'),
(1287, 2, 2026, 'minute-8', '2026-07-21 18:22:00', 104, '2026-07-21 18:22:02'),
(1308, 2, 2026, 'minute-7', '2026-07-21 18:23:00', 105, '2026-07-21 18:23:02'),
(1329, 2, 2026, 'minute-6', '2026-07-21 18:24:00', 106, '2026-07-21 18:24:02'),
(1349, 2, 2026, 'minute-5', '2026-07-21 18:25:00', 107, '2026-07-21 18:25:03'),
(1368, 2, 2026, 'minute-4', '2026-07-21 18:26:00', 108, '2026-07-21 18:26:00'),
(1388, 2, 2026, 'minute-3', '2026-07-21 18:27:00', 109, '2026-07-21 18:27:00'),
(1408, 2, 2026, 'minute-2', '2026-07-21 18:28:00', 110, '2026-07-21 18:28:00'),
(1430, 2, 2026, 'minute-1', '2026-07-21 18:29:00', 111, '2026-07-21 18:29:00');

-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

CREATE TABLE `campaigns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `message_template` text NOT NULL,
  `target_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `queued_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('queued','completed','cancelled') NOT NULL DEFAULT 'queued',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `campaigns`
--

INSERT INTO `campaigns` (`id`, `user_id`, `name`, `message_template`, `target_count`, `queued_count`, `status`, `created_at`, `updated_at`) VALUES
(1, 5, 'Opt-in announcement', 'hii {nmamee{ ss', 1, 1, 'completed', '2026-07-13 14:29:42', '2026-07-13 15:22:01'),
(2, 5, 'Opt-in announcement', 'ssss', 2, 2, 'completed', '2026-07-13 14:30:46', '2026-07-13 15:22:01'),
(3, 5, 'Opt-in announcement (Resend)', 'ssss', 2, 2, 'completed', '2026-07-13 15:25:00', '2026-07-13 15:25:08'),
(4, 5, 'Opt-in announcement (Resend) (Resend)', 'ssss', 2, 2, 'completed', '2026-07-14 02:57:15', '2026-07-14 03:14:46'),
(5, 5, 'Opt-in announcement (Resend)', 'hii {nmamee{ ss', 1, 1, 'completed', '2026-07-14 04:03:04', '2026-07-14 04:04:49'),
(6, 5, 'Opt-in announcement (Resend)', 'ssss', 2, 2, 'completed', '2026-07-14 04:03:07', '2026-07-14 04:04:21'),
(7, 5, 'Opt-in announcement (Resend) (Resend) (Resend)', 'ssss', 2, 2, 'completed', '2026-07-14 04:03:10', '2026-07-14 04:04:25'),
(8, 5, 'Opt-in announcement (Resend) (Resend) (Resend) (Resend)', 'ssss', 2, 2, 'completed', '2026-07-14 04:03:18', '2026-07-14 04:04:37'),
(9, 5, 'Opt-in announcement', 'Hi if you think you are bad then i am your dad', 3, 3, 'completed', '2026-07-16 16:12:01', '2026-07-16 16:12:20');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `phone` varchar(80) NOT NULL,
  `normalized_phone` varchar(80) DEFAULT NULL,
  `opt_in` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `user_id`, `name`, `phone`, `normalized_phone`, `opt_in`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'navneet yadav', '9507286092', NULL, 1, NULL, '2026-06-02 10:14:36', '2026-06-02 10:14:36'),
(2, 5, 'nav', '6202404133', NULL, 1, NULL, '2026-07-13 14:29:18', '2026-07-13 14:29:18'),
(3, 5, 'xyz@mgmgmgm.com', '9507286092', NULL, 1, NULL, '2026-07-13 14:30:32', '2026-07-13 14:30:32'),
(4, 5, 'Rishab baby', '9430815180', NULL, 1, NULL, '2026-07-16 16:03:54', '2026-07-16 16:03:54');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(190) NOT NULL,
  `ip_address` varchar(64) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_logs`
--

CREATE TABLE `message_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `recipient` varchar(80) NOT NULL,
  `message_preview` varchar(255) NOT NULL,
  `status` enum('queued','sent','failed') NOT NULL DEFAULT 'queued',
  `provider_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider_response`)),
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `message_logs`
--

INSERT INTO `message_logs` (`id`, `user_id`, `recipient`, `message_preview`, `status`, `provider_response`, `error_message`, `created_at`) VALUES
(1, 1, '9507286092', 'jajs', 'failed', '{\"ok\":false,\"error\":\"WhatsApp device is not connected.\",\"session\":{\"userId\":\"1\",\"state\":\"idle\",\"qr\":null,\"hasQr\":false,\"phone\":null,\"pushName\":null,\"error\":null},\"status_code\":409}', 'WhatsApp device is not connected.', '2026-06-02 07:16:39'),
(2, 1, '9507286092', 'jajs', 'sent', '{\"ok\":true,\"messageId\":\"3EB06487BA53470AB8E37B\",\"status_code\":200}', NULL, '2026-06-02 07:20:41'),
(3, 1, '9507286092', 'navneet yadav', 'sent', '{\"ok\":true,\"messageId\":\"3EB0A7BDC811CCCA005ED5\",\"status_code\":200}', NULL, '2026-06-02 07:21:09'),
(4, 1, '919507286092', 'navneet yadav', 'sent', '{\"ok\":true,\"messageId\":\"3EB0ABC50BDCC610210BA4\",\"status_code\":200}', NULL, '2026-06-02 07:23:40'),
(5, 1, '919507286092', 'hellow', 'failed', '{\"ok\":false,\"normalizedTo\":\"919507286092\",\"status_code\":500}', NULL, '2026-06-02 07:30:15'),
(6, 1, '919507286092', 'hellow', 'sent', '{\"ok\":true,\"messageId\":\"3EB0EDE299B98F2B055FBB\",\"normalizedTo\":\"919507286092\",\"status_code\":200}', NULL, '2026-06-02 09:53:59'),
(7, 1, '919507286092', 'chut me land', 'sent', '{\"ok\":true,\"messageId\":\"3EB0AD89A0682F3140DA80\",\"normalizedTo\":\"919507286092\",\"status_code\":200}', NULL, '2026-06-02 10:09:23'),
(8, 1, '919507286092', 'chut me land t', 'sent', '{\"ok\":true,\"messageId\":\"3EB01C35FE87B5171FFF14\",\"normalizedTo\":\"919507286092\",\"status_code\":200}', NULL, '2026-06-02 10:09:35'),
(9, 1, '919507286092', 'chut me land t', 'sent', '{\"ok\":true,\"messageId\":\"3EB0004EE7E870E5786F94\",\"normalizedTo\":\"919507286092\",\"status_code\":200}', NULL, '2026-06-02 10:09:42'),
(10, 1, '919507286092', 'chut me land t', 'sent', '{\"ok\":true,\"messageId\":\"3EB0FA59126F892EDC1337\",\"normalizedTo\":\"919507286092\",\"status_code\":200}', NULL, '2026-06-02 10:09:55'),
(11, 1, '919507286092', 'ek ke gand me kidaa ghr me land bur me chudaie ka gand me pelaie ka gand', 'sent', '{\"ok\":true,\"messageId\":\"3EB03846EFFCABC56880D4\",\"normalizedTo\":\"919507286092\",\"status_code\":200}', NULL, '2026-06-02 10:10:56'),
(12, 1, '919507286092', 'ek ke gand me kidaa ghr me land bur me chudaie ka gand me pelaie ka gand', 'sent', '{\"ok\":true,\"messageId\":\"3EB024B064530E8EAAB3E0\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-06-02 10:18:21'),
(13, 1, '123', 'verification only', 'failed', NULL, 'Invalid recipient. Use an international number or a 10-digit Indian mobile number.', '2026-06-02 10:20:04'),
(14, 1, '919507286092', 'hahaha', 'sent', '{\"ok\":true,\"messageId\":\"3EB0F616B19F6DE6D02FDB\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-06-02 10:22:29'),
(15, 1, '919507286092', '555', 'sent', '{\"ok\":true,\"messageId\":\"3EB0CDC4981336940EF10B\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-06-02 11:09:00'),
(16, 1, '919507286092', 'Hello from WhatsApp API Hub test page.', 'sent', '{\"ok\":true,\"messageId\":\"3EB0B25A8009B2038DA2A4\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-06-02 11:20:28'),
(17, 1, '919507286092', 'Hello from WhatsApp API Hub test page.', 'sent', '{\"ok\":true,\"messageId\":\"3EB0FB6330A2C11DDA721E\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-06-02 11:26:23'),
(18, 1, '919507286092', 'Hello from WhatsApp API Hub test page.', 'sent', '{\"ok\":true,\"messageId\":\"3EB08628D0B8C66FE36FA0\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-06-02 11:26:52'),
(19, 5, '916202404133', 'hii {nmamee{ ss', 'sent', '{\"ok\":true,\"messageId\":\"3EB09FEB35843F53672A8E\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-13 14:29:52'),
(20, 5, '916202404133', 'ssss', 'sent', '{\"ok\":true,\"messageId\":\"3EB023930E32E08477A558\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-13 14:30:51'),
(21, 5, '919507286092', 'ssss', 'sent', '{\"ok\":true,\"messageId\":\"3EB0EA3ABF3839FBFA2A84\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 14:30:59'),
(22, 5, '9507286092', 'hello cheak this', 'failed', NULL, 'WhatsApp device is not connected.', '2026-07-13 15:06:45'),
(23, 5, '919507286092', 'hello cheak this', 'sent', '{\"ok\":true,\"messageId\":\"3EB03CA8B379E8F5D40ED9\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 15:07:47'),
(24, 5, '916202404133', 'dd', 'sent', '{\"ok\":true,\"messageId\":\"3EB07D04A896DF86AC8532\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-13 15:08:41'),
(25, 5, '919507286092', 'dd', 'sent', '{\"ok\":true,\"messageId\":\"3EB08406C26889A4544EDD\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 15:08:47'),
(26, 5, '916202404133', 'xycc', 'sent', '{\"ok\":true,\"messageId\":\"3EB03D738BA93868EDAC9E\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-13 15:23:26'),
(27, 5, '919507286092', 'asaa', 'sent', '{\"ok\":true,\"messageId\":\"3EB0206ABC1C5B5F678F40\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 15:24:20'),
(28, 5, '919507286092', 'saa', 'sent', '{\"ok\":true,\"messageId\":\"3EB0A247E3BB41EEAD1FD0\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 15:24:44'),
(29, 5, '916202404133', 'ssss', 'sent', '{\"ok\":true,\"messageId\":\"3EB02EFC3571D0E6F0B70E\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-13 15:25:04'),
(30, 5, '919507286092', 'ssss', 'sent', '{\"ok\":true,\"messageId\":\"3EB0201B4659E0EE515219\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 15:25:07'),
(31, 5, '919507286092', 'just 3🎂🥳💖🫶🌸✨🎁💕Navneet Yadav aaa', 'sent', '{\"ok\":true,\"messageId\":\"3EB02A1DE83D84013E47EF\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:45:23'),
(32, 5, '919507286092', 'just 1🎂🥳💖🫶🌸✨🎁💕Navneet Yadav aaa', 'sent', '{\"ok\":true,\"messageId\":\"3EB0770BC65DD97AB10E90\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:46:01'),
(33, 5, '919507286092', 'Only 60 to your birthday baby🎂🥳💖🫶🌸✨🎁💕🎂🥳💖🫶🌸✨🎁💕', 'sent', '{\"ok\":true,\"messageId\":\"3EB0EB79FE6D0D73C6D946\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:46:04'),
(34, 5, '919507286092', 'Only 59 to your birthday baby🎂🥳💖🫶🌸✨🎁💕🎂🥳💖🫶🌸✨🎁💕', 'sent', '{\"ok\":true,\"messageId\":\"3EB07BFE8B250F07961A4E\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:46:07'),
(35, 5, '919507286092', 'Only 58 to your birthday baby🎂🥳💖🫶🌸✨🎁💕🎂🥳💖🫶🌸✨🎁💕', 'sent', '{\"ok\":true,\"messageId\":\"3EB0D500F6D51ABC03D4DE\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:47:04'),
(36, 5, '919507286092', 'Only 57 to your birthday baby🎂🥳💖🫶🌸✨🎁💕🎂🥳💖🫶🌸✨🎁💕', 'sent', '{\"ok\":true,\"messageId\":\"3EB00AC6B07821A5E78124\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:48:04'),
(37, 5, '919507286092', 'Only 56 to your birthday baby🎂🥳💖🫶🌸✨🎁💕🎂🥳💖🫶🌸✨🎁💕', 'sent', '{\"ok\":true,\"messageId\":\"3EB0971BBD03004BD4D61B\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:49:05'),
(38, 5, '919507286092', 'Only 55 to your birthday baby🎂🥳💖🫶🌸✨🎁💕🎂🥳💖🫶🌸✨🎁💕', 'sent', '{\"ok\":true,\"messageId\":\"3EB0CCE40B86AD98127555\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:50:05'),
(39, 5, '919507286092', 'Only 54 to your birthday baby🎂🥳💖🫶🌸✨🎁💕🎂🥳💖🫶🌸✨🎁💕', 'sent', '{\"ok\":true,\"messageId\":\"3EB072966E5DF4F584FE9E\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:51:06'),
(40, 5, '919507286092', 'Only 53 to your birthday baby🎂🥳💖🫶🌸✨🎁💕🎂🥳💖🫶🌸✨🎁💕', 'sent', '{\"ok\":true,\"messageId\":\"3EB052A6478BB71248E883\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:52:05'),
(41, 5, '919507286092', 'Only 52 to your birthday baby🎂🥳💖🫶🌸✨🎁💕🎂🥳💖🫶🌸✨🎁💕', 'sent', '{\"ok\":true,\"messageId\":\"3EB0011DFB2B1A63BAAFA2\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:53:05'),
(42, 5, '916202404133', 'ssss', 'sent', '{\"ok\":true,\"messageId\":\"3EB02801A41834F6DFC5F1\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-14 03:06:45'),
(43, 5, '919507286092', 'ssss', 'sent', '{\"ok\":true,\"messageId\":\"3EB0A9BF910E5F0B8CAA59\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-14 03:06:51'),
(44, 5, '6202404133', 'hii {nmamee{ ss', 'failed', NULL, 'WhatsApp device is not connected.', '2026-07-14 04:04:06'),
(45, 5, '916202404133', 'ssss', 'sent', '{\"ok\":true,\"messageId\":\"3EB0350D97921D6EF48E04\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-14 04:04:11'),
(46, 5, '919507286092', 'ssss', 'sent', '{\"ok\":true,\"messageId\":\"3EB036423439677810B717\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-14 04:04:17'),
(47, 5, '916202404133', 'ssss', 'sent', '{\"ok\":true,\"messageId\":\"3EB03DE2696D58D015F8FA\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-14 04:04:22'),
(48, 5, '919507286092', 'ssss', 'sent', '{\"ok\":true,\"messageId\":\"3EB04A95205E2780876DFA\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-14 04:04:25'),
(49, 5, '916202404133', 'ssss', 'sent', '{\"ok\":true,\"messageId\":\"3EB0CCCFE35A65B0E03DBF\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-14 04:04:28'),
(50, 5, '919507286092', 'ssss', 'sent', '{\"ok\":true,\"messageId\":\"3EB0BEEEB29B5DBAE2CB2E\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-14 04:04:34'),
(51, 5, '916202404133', 'hii {nmamee{ ss', 'sent', '{\"ok\":true,\"messageId\":\"3EB0FA08E647DEB46F0705\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-14 04:04:46'),
(52, 5, '919507286092', 'Nice', 'sent', '{\"ok\":true,\"messageId\":\"3EB098CB3E7CF180A179F6\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-14 14:48:56'),
(53, 5, '916202404133', 'hhhhhh', 'sent', '{\"ok\":true,\"messageId\":\"3EB0C3AD0DCF2E7AA4A9C4\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-14 16:30:03'),
(54, 5, '919507286092', 'zxxx', 'sent', '{\"ok\":true,\"messageId\":\"3EB085C325C442ABFB19BD\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-15 16:10:26'),
(55, 5, '919430815180', 'Hii', 'sent', '{\"ok\":true,\"messageId\":\"3EB0640A644FD76D0833BC\",\"normalizedTo\":\"919430815180\"}', NULL, '2026-07-16 16:00:26'),
(56, 5, '919430815180', 'Sexy hai baby 😛😛😛🍼🍼🍼🍼🍼🍼🍼', 'sent', '{\"ok\":true,\"messageId\":\"3EB014E3E1A6041B3EE9ED\",\"normalizedTo\":\"919430815180\"}', NULL, '2026-07-16 16:09:24'),
(57, 5, '919430815180', 'Hello dogla', 'sent', '{\"ok\":true,\"messageId\":\"3EB00067AE2B3133C786FD\",\"normalizedTo\":\"919430815180\"}', NULL, '2026-07-16 16:09:36'),
(58, 5, '916202404133', 'Hi if you think you are bad then i am your dad', 'sent', '{\"ok\":true,\"messageId\":\"3EB0464D9504B567B302A1\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-16 16:12:07'),
(59, 5, '919507286092', 'Hi if you think you are bad then i am your dad', 'sent', '{\"ok\":true,\"messageId\":\"3EB0DA0346A89D3DBE6B11\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-16 16:12:13'),
(60, 5, '919430815180', 'Hi if you think you are bad then i am your dad', 'sent', '{\"ok\":true,\"messageId\":\"3EB0D0425B5F9764E98A9F\",\"normalizedTo\":\"919430815180\"}', NULL, '2026-07-16 16:12:19'),
(61, 5, '919142058716', 'Bas 3333 💖 🎉 🥳 🎂 ✨', 'sent', '{\"ok\":true,\"messageId\":\"3EB06D1A578A0F8E9C7A5A\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 15:30:22'),
(62, 5, '919142058716', 'Bas 2222 💫 🎂 💖', 'sent', '{\"ok\":true,\"messageId\":\"3EB035B8C39BE2D96E35A9\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 16:30:06'),
(63, 5, '919142058716', 'Ab sirf ek Ghnta aur 😁😅, Happy Birthday Didi in advance 💖 🙏 🌸', 'sent', '{\"ok\":true,\"messageId\":\"3EB0A849269A492834F174\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:30:06'),
(64, 5, '919142058716', 'Only 60  min left 😁 Advance me birthday my sister ❤️ 🥳 👑 🌺 🥳 😊 💐', 'sent', '{\"ok\":true,\"messageId\":\"3EB070260056EE97CAF432\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:30:10'),
(65, 5, '919142058716', 'Only 59  min left 😁 Advance me birthday my sister ❤️ 🎂 🎂 🎂 🎉 🎂', 'sent', '{\"ok\":true,\"messageId\":\"3EB02205C911B2D4881E75\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:31:04'),
(66, 5, '919142058716', 'Only 58  min left 😁 Advance me birthday my sister ❤️ ❤️ 💖 🥳 💐 💐 ❤️', 'sent', '{\"ok\":true,\"messageId\":\"3EB013C8169C706F3880B3\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:32:04'),
(67, 5, '919142058716', 'Only 57  min left 😁 Advance me birthday my sister ❤️ 🥳 🎉 🥳 🥳 🎉 🎂', 'sent', '{\"ok\":true,\"messageId\":\"3EB04C1AD20B429988A0D8\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:33:04'),
(68, 5, '919142058716', 'Only 56  min left 😁 Advance me birthday my sister ❤️ 🙏 👑 ❤️ 💖 🙏', 'sent', '{\"ok\":true,\"messageId\":\"3EB00FB85A3CA45A766773\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:34:05'),
(69, 5, '919142058716', 'Only 55  min left 😁 Advance me birthday my sister ❤️ 🌺 🌟 🌸 🌟 🎉 🙏', 'sent', '{\"ok\":true,\"messageId\":\"3EB08C8E2B75C0959AE95D\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:35:05'),
(70, 5, '919142058716', 'Only 54  min left 😁 Advance me birthday my sister ❤️ ✨ ❤️ 🥳', 'sent', '{\"ok\":true,\"messageId\":\"3EB0E864EF6705B71AFFED\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:36:06'),
(71, 5, '919142058716', 'Only 53  min left 😁 Advance me birthday my sister ❤️ 🌟 🎉 🥳 🎂 🌟 🎂', 'sent', '{\"ok\":true,\"messageId\":\"3EB09689FA41F59F77948F\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:37:05'),
(72, 5, '919142058716', 'Only 52  min left 😁 Advance me birthday my sister ❤️ 💐 🎉 🎂 🙏 🥳', 'sent', '{\"ok\":true,\"messageId\":\"3EB08310A7A5510CBB9F76\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:38:05'),
(73, 5, '919142058716', 'Only 51  min left 😁 Advance me birthday my sister ❤️ ❤️ 👑 🎉', 'sent', '{\"ok\":true,\"messageId\":\"3EB0DB42C08AADB5334BAC\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:39:05'),
(74, 5, '919142058716', 'Only 50  min left 😁 Advance me birthday my sister ❤️ 🎂 🎉 🌸', 'sent', '{\"ok\":true,\"messageId\":\"3EB0222CD06182846FB087\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:40:05'),
(75, 5, '919142058716', 'Only 49  min left 😁 Advance me birthday my sister ❤️ 🌟 💖 🙏 ❤️', 'sent', '{\"ok\":true,\"messageId\":\"3EB073B640AE75F76D728E\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:41:03'),
(76, 5, '919142058716', 'Only 48  min left 😁 Advance me birthday my sister ❤️ 🌟 🌸 🎉', 'sent', '{\"ok\":true,\"messageId\":\"3EB0F89BEC71D116CA3768\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:42:04'),
(77, 5, '919142058716', 'Only 47  min left 😁 Advance me birthday my sister ❤️ ✨ 🎉 🎉 🌺 ✨', 'sent', '{\"ok\":true,\"messageId\":\"3EB086E66B959D6E1E6286\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:43:03'),
(78, 5, '919142058716', 'Only 46  min left 😁 Advance me birthday my sister ❤️ 🌸 💐 🌺 🙏', 'sent', '{\"ok\":true,\"messageId\":\"3EB0460AC738EBC10E27A1\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:44:03'),
(79, 5, '919142058716', 'Only 45  min left 😁 Advance me birthday my sister ❤️ 💐 🎉 🎉 😊', 'sent', '{\"ok\":true,\"messageId\":\"3EB04696F274EB13AE6D53\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:45:03'),
(80, 5, '919142058716', 'Only 44  min left 😁 Advance me birthday my sister ❤️ 💐 👑 🎉 ✨', 'sent', '{\"ok\":true,\"messageId\":\"3EB0D321977470988515B0\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:46:03'),
(81, 5, '919142058716', 'Only 43  min left 😁 Advance me birthday my sister ❤️ 😊 🌺 💖 ✨ 💖 🎉', 'sent', '{\"ok\":true,\"messageId\":\"3EB0FF2BB1E4FCC0B0FFE2\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:47:04'),
(82, 5, '919142058716', 'Only 42  min left 😁 Advance me birthday my sister ❤️ 🥳 🥳 🎉 🌸', 'sent', '{\"ok\":true,\"messageId\":\"3EB0E52E1D1A33C0A6C51C\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:48:06'),
(83, 5, '919142058716', 'Only 41  min left 😁 Advance me birthday my sister ❤️ ❤️ 💫 🥳 🎉', 'sent', '{\"ok\":true,\"messageId\":\"3EB058B87E9CBDBBC075CC\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:49:04'),
(84, 5, '919142058716', 'Only 40  min left 😁 Advance me birthday my sister ❤️ 💐 💐 🎂 🙏', 'sent', '{\"ok\":true,\"messageId\":\"3EB0162463982CCC48910F\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:50:04'),
(85, 5, '919142058716', 'Only 39  min left 😁 Advance me birthday my sister ❤️ 🌟 🎂 😊 👑', 'sent', '{\"ok\":true,\"messageId\":\"3EB0FD3ECCEB5D1BDC5A77\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:51:04'),
(86, 5, '919142058716', 'Only 38  min left 😁 Advance me birthday my sister ❤️ 💖 🎉 🎉 👑 🌟 🎉', 'sent', '{\"ok\":true,\"messageId\":\"3EB061A72E6BB7E4DD2F8C\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:52:04'),
(87, 5, '919142058716', 'Only 37  min left 😁 Advance me birthday my sister ❤️ ❤️ 🌸 🙏 🌺 💫', 'sent', '{\"ok\":true,\"messageId\":\"3EB0574762A00A8DF7029B\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:53:04'),
(88, 5, '919142058716', 'Only 36  min left 😁 Advance me birthday my sister ❤️ ✨ 😊 💫 💐', 'sent', '{\"ok\":true,\"messageId\":\"3EB0B36669FACBCAC372D5\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:54:06'),
(89, 5, '919142058716', 'Only 35  min left 😁 Advance me birthday my sister ❤️ 🎉 😊 💐 💫 💐 🌺', 'sent', '{\"ok\":true,\"messageId\":\"3EB0A63991BD86DBFAF63B\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:55:05'),
(90, 5, '919142058716', 'Only 34  min left 😁 Advance me birthday my sister ❤️ 🌟 💫 🙏 🎂 👑', 'sent', '{\"ok\":true,\"messageId\":\"3EB09520EF6E3A90D401D7\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:56:05'),
(91, 5, '919142058716', 'Only 33  min left 😁 Advance me birthday my sister ❤️ 💫 🌟 🌺 🌸', 'sent', '{\"ok\":true,\"messageId\":\"3EB09D7861AA8F087525B1\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:57:05'),
(92, 5, '919142058716', 'Only 32  min left 😁 Advance me birthday my sister ❤️ 💐 🎂 ✨', 'sent', '{\"ok\":true,\"messageId\":\"3EB070EDD6214C381EB190\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:58:05'),
(93, 5, '919142058716', 'Only 31  min left 😁 Advance me birthday my sister ❤️ ❤️ 👑 🌟 🌺 👑', 'sent', '{\"ok\":true,\"messageId\":\"3EB0AD19A2708145C0B184\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:59:06'),
(94, 5, '919142058716', 'Only 30  min left 😁 Advance me birthday my sister ❤️ 🥳 🎂 💫', 'sent', '{\"ok\":true,\"messageId\":\"3EB02F3B55CFD18E64C4F7\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:00:04'),
(95, 5, '919142058716', 'Only 29  min left 😁 Advance me birthday my sister ❤️ 💖 ❤️ 👑 💐 🌺', 'sent', '{\"ok\":true,\"messageId\":\"3EB0461212D32184D919FB\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:01:03'),
(96, 5, '919142058716', 'Only 28  min left 😁 Advance me birthday my sister ❤️ 🌟 🌺 🙏 🌸 🌟', 'sent', '{\"ok\":true,\"messageId\":\"3EB08F1A3AF4ACE7AFEC02\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:02:03'),
(97, 5, '919142058716', 'Only 27  min left 😁 Advance me birthday my sister ❤️ 💐 🎉 ❤️', 'sent', '{\"ok\":true,\"messageId\":\"3EB0D79FDC28DD2D893AE2\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:03:03'),
(98, 5, '919142058716', 'Only 26  min left 😁 Advance me birthday my sister ❤️ 💫 🙏 😊 ✨ 🌺', 'sent', '{\"ok\":true,\"messageId\":\"3EB07D1E7451FE4A31C070\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:04:03'),
(99, 5, '919142058716', 'Only 25  min left 😁 Advance me birthday my sister ❤️ 😊 🥳 💖 🎂', 'sent', '{\"ok\":true,\"messageId\":\"3EB065B63C975BBCD6297C\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:05:03'),
(100, 5, '919142058716', 'Only 24  min left 😁 Advance me birthday my sister ❤️ 🎉 🌟 ✨ ❤️', 'sent', '{\"ok\":true,\"messageId\":\"3EB0E7C244D53DF4D08A59\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:06:05'),
(101, 5, '919142058716', 'Only 23  min left 😁 Advance me birthday my sister ❤️ ❤️ 🌟 😊 💖 🙏', 'sent', '{\"ok\":true,\"messageId\":\"3EB0215701B716623F8740\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:07:04'),
(102, 5, '919142058716', 'Only 22  min left 😁 Advance me birthday my sister ❤️ 😊 😊 ❤️', 'sent', '{\"ok\":true,\"messageId\":\"3EB0DAF86858A1708E40C6\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:08:04'),
(103, 5, '919142058716', 'Only 21  min left 😁 Advance me birthday my sister ❤️ 🎂 💫 🙏 🥳 🎂', 'sent', '{\"ok\":true,\"messageId\":\"3EB047A9BACAB7D65FA5AB\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:09:04'),
(104, 5, '919142058716', 'Only 20  min left 😁 Advance me birthday my sister ❤️ 🎉 ❤️ ❤️ 🎂', 'sent', '{\"ok\":true,\"messageId\":\"3EB027BB5F1109125D4C9C\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:10:04'),
(105, 5, '919142058716', 'Only 19  min left 😁 Advance me birthday my sister ❤️ 🙏 🌺 💐 👑 🎂', 'sent', '{\"ok\":true,\"messageId\":\"3EB0FEAD32C85E68C34A7D\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:11:04'),
(106, 5, '919142058716', 'Only 18  min left 😁 Advance me birthday my sister ❤️ 💐 🌺 👑', 'sent', '{\"ok\":true,\"messageId\":\"3EB068E2773453124E2144\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:12:06'),
(107, 5, '919142058716', 'Only 17  min left 😁 Advance me birthday my sister ❤️ 🎂 🎂 💫 ❤️', 'sent', '{\"ok\":true,\"messageId\":\"3EB090D4FFEC5B08FBACC6\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:13:04'),
(108, 5, '919142058716', 'Only 16  min left 😁 Advance me birthday my sister ❤️ 🙏 💖 ❤️', 'sent', '{\"ok\":true,\"messageId\":\"3EB0C97CAFFA2FB86AA115\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:14:04'),
(109, 5, '919142058716', 'Only 15  min left 😁 Advance me birthday my sister ❤️ 👑 🙏 🎉', 'sent', '{\"ok\":true,\"messageId\":\"3EB075C5D806C4349152CB\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:15:04'),
(110, 5, '919142058716', 'Only 14  min left 😁 Advance me birthday my sister ❤️ 🌺 🙏 🎉 ✨ 💐 🌟', 'sent', '{\"ok\":true,\"messageId\":\"3EB0E1CCAEF4A1986AFB8A\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:16:04'),
(111, 5, '919142058716', 'Only 13  min left 😁 Advance me birthday my sister ❤️ 🌺 🌺 👑 💐 🎉', 'sent', '{\"ok\":true,\"messageId\":\"3EB044084547622397E942\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:17:04'),
(112, 5, '919142058716', 'Only 12  min left 😁 Advance me birthday my sister ❤️ 💐 ❤️ 😊 💖', 'sent', '{\"ok\":true,\"messageId\":\"3EB097A52734B6B096DFC4\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:18:06'),
(113, 5, '919142058716', 'Only 11  min left 😁 Advance me birthday my sister ❤️ 🙏 ❤️ 🎂', 'sent', '{\"ok\":true,\"messageId\":\"3EB07A0F3073F7A71A5CFA\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:19:05'),
(114, 5, '919142058716', 'Only 10  min left 😁 Advance me birthday my sister ❤️ 💖 🎂 👑 👑 🥳 🥳', 'sent', '{\"ok\":true,\"messageId\":\"3EB080C1F955A67BE3FFC5\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:20:05'),
(115, 5, '919142058716', 'Only 9  min left 😁 Advance me birthday my sister ❤️ 🙏 🙏 ❤️ 🙏', 'sent', '{\"ok\":true,\"messageId\":\"3EB0F61129B7F377DC4ED4\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:21:05'),
(116, 5, '919142058716', 'Only 8  min left 😁 Advance me birthday my sister ❤️ 🙏 ✨ 💐 ❤️ 💐', 'sent', '{\"ok\":true,\"messageId\":\"3EB080A7588B33FEBB5EBF\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:22:05'),
(117, 5, '919142058716', 'Only 7  min left 😁 Advance me birthday my sister ❤️ 👑 🥳 🌸 👑 🌟', 'sent', '{\"ok\":true,\"messageId\":\"3EB0250A42018413CB06D2\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:23:05'),
(118, 5, '919142058716', 'Only 6  min left 😁 Advance me birthday my sister ❤️ 🌺 ❤️ ❤️ ✨ 🌟 🎂', 'sent', '{\"ok\":true,\"messageId\":\"3EB056DBA6CD3F314CA5BE\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:24:07'),
(119, 5, '919142058716', 'Only 5  min left 😁 Advance me birthday my sister ❤️ 🌸 🎉 🌟 🎉 ❤️', 'sent', '{\"ok\":true,\"messageId\":\"3EB0FB25CEE4D1CDFA86B6\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:25:06'),
(120, 5, '919142058716', 'Only 4  min left 😁 Advance me birthday my sister ❤️ ❤️ 🙏 🌸 🥳 👑', 'sent', '{\"ok\":true,\"messageId\":\"3EB0F819DC8E47F3ACCFAF\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:26:03'),
(121, 5, '919142058716', 'Only 3  min left 😁 Advance me birthday my sister ❤️ 🌺 ✨ ❤️', 'sent', '{\"ok\":true,\"messageId\":\"3EB02AFF879B790C1A3581\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:27:03'),
(122, 5, '919142058716', 'Only 2  min left 😁 Advance me birthday my sister ❤️ 💫 💖 ❤️', 'sent', '{\"ok\":true,\"messageId\":\"3EB08442AE90C5A50ACD06\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:28:03'),
(123, 5, '919142058716', 'Only 1  min left 😁 Advance me birthday my sister ❤️ 🌟 🥳 ✨ 🌟 ❤️', 'sent', '{\"ok\":true,\"messageId\":\"3EB0D5C90C22E4F078BD93\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:29:03');

-- --------------------------------------------------------

--
-- Table structure for table `message_queue`
--

CREATE TABLE `message_queue` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `api_key_id` bigint(20) UNSIGNED DEFAULT NULL,
  `recipient` varchar(80) NOT NULL,
  `normalized_recipient` varchar(80) DEFAULT NULL,
  `message_type` enum('text','image','document','video') NOT NULL DEFAULT 'text',
  `body` text NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `status` enum('queued','processing','sent','failed','cancelled') NOT NULL DEFAULT 'queued',
  `attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `max_attempts` int(10) UNSIGNED NOT NULL DEFAULT 3,
  `available_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `locked_at` timestamp NULL DEFAULT NULL,
  `locked_by` varchar(80) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `provider_message_id` varchar(120) DEFAULT NULL,
  `provider_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider_response`)),
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `message_queue`
--

INSERT INTO `message_queue` (`id`, `user_id`, `api_key_id`, `recipient`, `normalized_recipient`, `message_type`, `body`, `payload`, `status`, `attempts`, `max_attempts`, `available_at`, `locked_at`, `locked_by`, `sent_at`, `provider_message_id`, `provider_response`, `error_message`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, '9507286092', '919507286092', 'text', 'ek ke gand me kidaa ghr me land bur me chudaie ka gand me pelaie ka gand', '{\"source\":\"dashboard\",\"type\":\"text\"}', 'sent', 1, 3, '2026-06-02 10:14:02', NULL, NULL, '2026-06-02 10:18:21', '3EB024B064530E8EAAB3E0', '{\"ok\":true,\"messageId\":\"3EB024B064530E8EAAB3E0\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-06-02 10:14:02', '2026-06-02 10:18:21'),
(2, 1, NULL, '123', '123', 'text', 'verification only', '{\"source\":\"api\",\"type\":\"text\"}', 'failed', 3, 3, '2026-06-02 10:20:49', NULL, NULL, NULL, NULL, NULL, 'Invalid recipient. Use an international number or a 10-digit Indian mobile number.', '2026-06-02 10:19:09', '2026-06-02 10:20:04'),
(3, 1, NULL, '9507286092\'', '919507286092', 'text', 'hahaha', '{\"source\":\"dashboard\",\"type\":\"text\"}', 'sent', 1, 3, '2026-06-02 10:22:23', NULL, NULL, '2026-06-02 10:22:29', '3EB0F616B19F6DE6D02FDB', '{\"ok\":true,\"messageId\":\"3EB0F616B19F6DE6D02FDB\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-06-02 10:22:23', '2026-06-02 10:22:29'),
(4, 1, NULL, '9507286092', '919507286092', 'text', '555', '{\"source\":\"dashboard\",\"type\":\"text\"}', 'sent', 1, 3, '2026-06-02 11:08:54', NULL, NULL, '2026-06-02 11:09:00', '3EB0CDC4981336940EF10B', '{\"ok\":true,\"messageId\":\"3EB0CDC4981336940EF10B\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-06-02 11:08:54', '2026-06-02 11:09:00'),
(5, 1, 6, '9507286092', '919507286092', 'text', 'Hello from WhatsApp API Hub test page.', '{\"source\":\"api\",\"type\":\"text\"}', 'sent', 1, 3, '2026-06-02 11:20:24', NULL, NULL, '2026-06-02 11:20:28', '3EB0B25A8009B2038DA2A4', '{\"ok\":true,\"messageId\":\"3EB0B25A8009B2038DA2A4\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-06-02 11:20:24', '2026-06-02 11:20:28'),
(6, 1, 6, '9507286092', '919507286092', 'text', 'Hello from WhatsApp API Hub test page.', '{\"source\":\"api\",\"type\":\"text\"}', 'sent', 1, 3, '2026-06-02 11:26:19', NULL, NULL, '2026-06-02 11:26:23', '3EB0FB6330A2C11DDA721E', '{\"ok\":true,\"messageId\":\"3EB0FB6330A2C11DDA721E\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-06-02 11:26:19', '2026-06-02 11:26:23'),
(7, 1, 6, '9507286092', '919507286092', 'text', 'Hello from WhatsApp API Hub test page.', '{\"source\":\"api\",\"type\":\"text\"}', 'sent', 1, 3, '2026-06-02 11:26:49', NULL, NULL, '2026-06-02 11:26:52', '3EB08628D0B8C66FE36FA0', '{\"ok\":true,\"messageId\":\"3EB08628D0B8C66FE36FA0\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-06-02 11:26:49', '2026-06-02 11:26:52'),
(8, 5, NULL, '6202404133', '916202404133', 'text', 'hii {nmamee{ ss', '{\"source\":\"campaign\",\"campaign_id\":1,\"contact_id\":2,\"type\":\"text\"}', 'sent', 1, 3, '2026-07-13 14:29:42', NULL, NULL, '2026-07-13 14:29:52', '3EB09FEB35843F53672A8E', '{\"ok\":true,\"messageId\":\"3EB09FEB35843F53672A8E\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-13 14:29:42', '2026-07-13 14:29:52'),
(9, 5, NULL, '6202404133', '916202404133', 'text', 'ssss', '{\"source\":\"campaign\",\"campaign_id\":2,\"contact_id\":2,\"type\":\"text\"}', 'sent', 1, 3, '2026-07-13 14:30:46', NULL, NULL, '2026-07-13 14:30:51', '3EB023930E32E08477A558', '{\"ok\":true,\"messageId\":\"3EB023930E32E08477A558\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-13 14:30:46', '2026-07-13 14:30:51'),
(10, 5, NULL, '9507286092', '919507286092', 'text', 'ssss', '{\"source\":\"campaign\",\"campaign_id\":2,\"contact_id\":3,\"type\":\"text\"}', 'sent', 1, 3, '2026-07-13 14:30:46', NULL, NULL, '2026-07-13 14:30:59', '3EB0EA3ABF3839FBFA2A84', '{\"ok\":true,\"messageId\":\"3EB0EA3ABF3839FBFA2A84\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 14:30:46', '2026-07-13 14:30:59'),
(11, 5, NULL, '9507286092', '919507286092', 'document', 'hello cheak this', '{\"source\":\"manual\",\"type\":\"document\",\"attachment\":{\"type\":\"document\",\"mime\":\"application/pdf\",\"name\":\"BOBCDM_2026_20382.pdf\",\"path\":\"D:\\\\Projects\\\\whatsapp-api\\\\public\\\\uploads\\\\attachments\\\\user_5\\\\20260713170546-468be0b042a0-BOBCDM_2026_20382.pdf\",\"size\":111663}}', 'sent', 1, 3, '2026-07-13 15:07:10', NULL, NULL, '2026-07-13 15:07:47', '3EB03CA8B379E8F5D40ED9', '{\"ok\":true,\"messageId\":\"3EB03CA8B379E8F5D40ED9\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 15:05:46', '2026-07-13 15:07:47'),
(12, 5, NULL, '6202404133', '916202404133', 'text', 'dd', '{\"source\":\"manual\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-13 15:08:33', NULL, NULL, '2026-07-13 15:08:41', '3EB07D04A896DF86AC8532', '{\"ok\":true,\"messageId\":\"3EB07D04A896DF86AC8532\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-13 15:08:33', '2026-07-13 15:08:41'),
(13, 5, NULL, '9507286092', '919507286092', 'text', 'dd', '{\"source\":\"manual\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-13 15:08:33', NULL, NULL, '2026-07-13 15:08:47', '3EB08406C26889A4544EDD', '{\"ok\":true,\"messageId\":\"3EB08406C26889A4544EDD\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 15:08:33', '2026-07-13 15:08:47'),
(14, 5, NULL, '6202404133', NULL, 'text', 'xxx', '{\"source\":\"manual\",\"type\":\"text\"}', 'cancelled', 1, 3, '2026-07-13 15:23:31', NULL, NULL, NULL, NULL, NULL, 'WhatsApp device is not connected.', '2026-07-13 15:23:10', '2026-07-13 15:23:25'),
(15, 5, NULL, '6202404133', '916202404133', 'text', 'xycc', '{\"source\":\"manual\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-13 15:23:21', NULL, NULL, '2026-07-13 15:23:26', '3EB03D738BA93868EDAC9E', '{\"ok\":true,\"messageId\":\"3EB03D738BA93868EDAC9E\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-13 15:23:21', '2026-07-13 15:23:26'),
(16, 5, NULL, '9507286092', '919507286092', 'text', 'asaa', '{\"source\":\"manual\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-13 15:24:15', NULL, NULL, '2026-07-13 15:24:20', '3EB0206ABC1C5B5F678F40', '{\"ok\":true,\"messageId\":\"3EB0206ABC1C5B5F678F40\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 15:24:15', '2026-07-13 15:24:20'),
(17, 5, NULL, '9507286092', '919507286092', 'document', 'saa', '{\"source\":\"manual\",\"type\":\"document\",\"attachment\":{\"type\":\"document\",\"mime\":\"application/pdf\",\"name\":\"BOBCDM_2026_20382.pdf\",\"path\":\"D:\\\\Projects\\\\whatsapp-api\\\\public\\\\uploads\\\\attachments\\\\user_5\\\\20260713172440-4aab815716e7-BOBCDM_2026_20382.pdf\",\"size\":111663}}', 'sent', 1, 3, '2026-07-13 15:24:40', NULL, NULL, '2026-07-13 15:24:44', '3EB0A247E3BB41EEAD1FD0', '{\"ok\":true,\"messageId\":\"3EB0A247E3BB41EEAD1FD0\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 15:24:40', '2026-07-13 15:24:44'),
(18, 5, NULL, '6202404133', '916202404133', 'text', 'ssss', '{\"source\":\"campaign\",\"campaign_id\":3,\"contact_id\":2,\"type\":\"text\"}', 'sent', 1, 3, '2026-07-13 15:25:00', NULL, NULL, '2026-07-13 15:25:04', '3EB02EFC3571D0E6F0B70E', '{\"ok\":true,\"messageId\":\"3EB02EFC3571D0E6F0B70E\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-13 15:25:00', '2026-07-13 15:25:04'),
(19, 5, NULL, '9507286092', '919507286092', 'text', 'ssss', '{\"source\":\"campaign\",\"campaign_id\":3,\"contact_id\":3,\"type\":\"text\"}', 'sent', 1, 3, '2026-07-13 15:25:00', NULL, NULL, '2026-07-13 15:25:07', '3EB0201B4659E0EE515219', '{\"ok\":true,\"messageId\":\"3EB0201B4659E0EE515219\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 15:25:00', '2026-07-13 15:25:07'),
(20, 5, NULL, '9507286092', '919507286092', 'text', 'just 3🎂🥳💖🫶🌸✨🎁💕Navneet Yadav aaa', '{\"source\":\"birthday-task\",\"task_id\":1,\"occurrence_year\":2026,\"event_key\":\"hour-3\",\"type\":\"text\"}', 'sent', 2, 3, '2026-07-13 16:45:19', NULL, NULL, '2026-07-13 16:45:23', '3EB02A1DE83D84013E47EF', '{\"ok\":true,\"messageId\":\"3EB02A1DE83D84013E47EF\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:45:01', '2026-07-13 16:45:23'),
(21, 5, NULL, '9507286092', '919507286092', 'text', 'just 1🎂🥳💖🫶🌸✨🎁💕Navneet Yadav aaa', '{\"source\":\"birthday-task\",\"task_id\":1,\"occurrence_year\":2026,\"event_key\":\"hour-1\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-13 16:45:58', NULL, NULL, '2026-07-13 16:46:01', '3EB0770BC65DD97AB10E90', '{\"ok\":true,\"messageId\":\"3EB0770BC65DD97AB10E90\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:45:58', '2026-07-13 16:46:01'),
(22, 5, NULL, '9507286092', '919507286092', 'text', 'Only 60 to your birthday baby🎂🥳💖🫶🌸✨🎁💕🎂🥳💖🫶🌸✨🎁💕', '{\"source\":\"birthday-task\",\"task_id\":1,\"occurrence_year\":2026,\"event_key\":\"minute-60\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-13 16:45:58', NULL, NULL, '2026-07-13 16:46:04', '3EB0EB79FE6D0D73C6D946', '{\"ok\":true,\"messageId\":\"3EB0EB79FE6D0D73C6D946\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:45:58', '2026-07-13 16:46:04'),
(23, 5, NULL, '9507286092', '919507286092', 'text', 'Only 59 to your birthday baby🎂🥳💖🫶🌸✨🎁💕🎂🥳💖🫶🌸✨🎁💕', '{\"source\":\"birthday-task\",\"task_id\":1,\"occurrence_year\":2026,\"event_key\":\"minute-59\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-13 16:46:01', NULL, NULL, '2026-07-13 16:46:07', '3EB07BFE8B250F07961A4E', '{\"ok\":true,\"messageId\":\"3EB07BFE8B250F07961A4E\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:46:01', '2026-07-13 16:46:07'),
(24, 5, NULL, '9507286092', '919507286092', 'text', 'Only 58 to your birthday baby🎂🥳💖🫶🌸✨🎁💕🎂🥳💖🫶🌸✨🎁💕', '{\"source\":\"birthday-task\",\"task_id\":1,\"occurrence_year\":2026,\"event_key\":\"minute-58\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-13 16:47:01', NULL, NULL, '2026-07-13 16:47:04', '3EB0D500F6D51ABC03D4DE', '{\"ok\":true,\"messageId\":\"3EB0D500F6D51ABC03D4DE\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:47:01', '2026-07-13 16:47:04'),
(25, 5, NULL, '9507286092', '919507286092', 'text', 'Only 57 to your birthday baby🎂🥳💖🫶🌸✨🎁💕🎂🥳💖🫶🌸✨🎁💕', '{\"source\":\"birthday-task\",\"task_id\":1,\"occurrence_year\":2026,\"event_key\":\"minute-57\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-13 16:48:01', NULL, NULL, '2026-07-13 16:48:04', '3EB00AC6B07821A5E78124', '{\"ok\":true,\"messageId\":\"3EB00AC6B07821A5E78124\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:48:01', '2026-07-13 16:48:04'),
(26, 5, NULL, '9507286092', '919507286092', 'text', 'Only 56 to your birthday baby🎂🥳💖🫶🌸✨🎁💕🎂🥳💖🫶🌸✨🎁💕', '{\"source\":\"birthday-task\",\"task_id\":1,\"occurrence_year\":2026,\"event_key\":\"minute-56\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-13 16:49:01', NULL, NULL, '2026-07-13 16:49:05', '3EB0971BBD03004BD4D61B', '{\"ok\":true,\"messageId\":\"3EB0971BBD03004BD4D61B\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:49:01', '2026-07-13 16:49:05'),
(27, 5, NULL, '9507286092', '919507286092', 'text', 'Only 55 to your birthday baby🎂🥳💖🫶🌸✨🎁💕🎂🥳💖🫶🌸✨🎁💕', '{\"source\":\"birthday-task\",\"task_id\":1,\"occurrence_year\":2026,\"event_key\":\"minute-55\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-13 16:50:02', NULL, NULL, '2026-07-13 16:50:05', '3EB0CCE40B86AD98127555', '{\"ok\":true,\"messageId\":\"3EB0CCE40B86AD98127555\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:50:02', '2026-07-13 16:50:05'),
(28, 5, NULL, '9507286092', '919507286092', 'text', 'Only 54 to your birthday baby🎂🥳💖🫶🌸✨🎁💕🎂🥳💖🫶🌸✨🎁💕', '{\"source\":\"birthday-task\",\"task_id\":1,\"occurrence_year\":2026,\"event_key\":\"minute-54\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-13 16:51:02', NULL, NULL, '2026-07-13 16:51:06', '3EB072966E5DF4F584FE9E', '{\"ok\":true,\"messageId\":\"3EB072966E5DF4F584FE9E\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:51:02', '2026-07-13 16:51:06'),
(29, 5, NULL, '9507286092', '919507286092', 'text', 'Only 53 to your birthday baby🎂🥳💖🫶🌸✨🎁💕🎂🥳💖🫶🌸✨🎁💕', '{\"source\":\"birthday-task\",\"task_id\":1,\"occurrence_year\":2026,\"event_key\":\"minute-53\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-13 16:52:02', NULL, NULL, '2026-07-13 16:52:05', '3EB052A6478BB71248E883', '{\"ok\":true,\"messageId\":\"3EB052A6478BB71248E883\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:52:02', '2026-07-13 16:52:05'),
(30, 5, NULL, '9507286092', '919507286092', 'text', 'Only 52 to your birthday baby🎂🥳💖🫶🌸✨🎁💕🎂🥳💖🫶🌸✨🎁💕', '{\"source\":\"birthday-task\",\"task_id\":1,\"occurrence_year\":2026,\"event_key\":\"minute-52\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-13 16:53:02', NULL, NULL, '2026-07-13 16:53:05', '3EB0011DFB2B1A63BAAFA2', '{\"ok\":true,\"messageId\":\"3EB0011DFB2B1A63BAAFA2\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-13 16:53:02', '2026-07-13 16:53:05'),
(31, 5, NULL, '6202404133', '916202404133', 'text', 'ssss', '{\"source\":\"campaign\",\"campaign_id\":4,\"contact_id\":2,\"type\":\"text\"}', 'sent', 1, 3, '2026-07-14 02:57:15', NULL, NULL, '2026-07-14 03:06:45', '3EB02801A41834F6DFC5F1', '{\"ok\":true,\"messageId\":\"3EB02801A41834F6DFC5F1\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-14 02:57:15', '2026-07-14 03:06:45'),
(32, 5, NULL, '9507286092', '919507286092', 'text', 'ssss', '{\"source\":\"campaign\",\"campaign_id\":4,\"contact_id\":3,\"type\":\"text\"}', 'sent', 1, 3, '2026-07-14 02:57:15', NULL, NULL, '2026-07-14 03:06:51', '3EB0A9BF910E5F0B8CAA59', '{\"ok\":true,\"messageId\":\"3EB0A9BF910E5F0B8CAA59\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-14 02:57:15', '2026-07-14 03:06:51'),
(33, 5, NULL, '6202404133', '916202404133', 'text', 'hii {nmamee{ ss', '{\"source\":\"campaign\",\"campaign_id\":5,\"contact_id\":2,\"type\":\"text\"}', 'sent', 1, 3, '2026-07-14 04:04:40', NULL, NULL, '2026-07-14 04:04:46', '3EB0FA08E647DEB46F0705', '{\"ok\":true,\"messageId\":\"3EB0FA08E647DEB46F0705\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-14 04:03:04', '2026-07-14 04:04:46'),
(34, 5, NULL, '6202404133', '916202404133', 'text', 'ssss', '{\"source\":\"campaign\",\"campaign_id\":6,\"contact_id\":2,\"type\":\"text\"}', 'sent', 1, 3, '2026-07-14 04:03:07', NULL, NULL, '2026-07-14 04:04:11', '3EB0350D97921D6EF48E04', '{\"ok\":true,\"messageId\":\"3EB0350D97921D6EF48E04\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-14 04:03:07', '2026-07-14 04:04:11'),
(35, 5, NULL, '9507286092', '919507286092', 'text', 'ssss', '{\"source\":\"campaign\",\"campaign_id\":6,\"contact_id\":3,\"type\":\"text\"}', 'sent', 1, 3, '2026-07-14 04:03:07', NULL, NULL, '2026-07-14 04:04:17', '3EB036423439677810B717', '{\"ok\":true,\"messageId\":\"3EB036423439677810B717\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-14 04:03:07', '2026-07-14 04:04:17'),
(36, 5, NULL, '6202404133', '916202404133', 'text', 'ssss', '{\"source\":\"campaign\",\"campaign_id\":7,\"contact_id\":2,\"type\":\"text\"}', 'sent', 1, 3, '2026-07-14 04:03:10', NULL, NULL, '2026-07-14 04:04:22', '3EB03DE2696D58D015F8FA', '{\"ok\":true,\"messageId\":\"3EB03DE2696D58D015F8FA\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-14 04:03:10', '2026-07-14 04:04:22'),
(37, 5, NULL, '9507286092', '919507286092', 'text', 'ssss', '{\"source\":\"campaign\",\"campaign_id\":7,\"contact_id\":3,\"type\":\"text\"}', 'sent', 1, 3, '2026-07-14 04:03:10', NULL, NULL, '2026-07-14 04:04:25', '3EB04A95205E2780876DFA', '{\"ok\":true,\"messageId\":\"3EB04A95205E2780876DFA\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-14 04:03:10', '2026-07-14 04:04:25'),
(38, 5, NULL, '6202404133', '916202404133', 'text', 'ssss', '{\"source\":\"campaign\",\"campaign_id\":8,\"contact_id\":2,\"type\":\"text\"}', 'sent', 1, 3, '2026-07-14 04:03:18', NULL, NULL, '2026-07-14 04:04:28', '3EB0CCCFE35A65B0E03DBF', '{\"ok\":true,\"messageId\":\"3EB0CCCFE35A65B0E03DBF\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-14 04:03:18', '2026-07-14 04:04:28'),
(39, 5, NULL, '9507286092', '919507286092', 'text', 'ssss', '{\"source\":\"campaign\",\"campaign_id\":8,\"contact_id\":3,\"type\":\"text\"}', 'sent', 1, 3, '2026-07-14 04:03:18', NULL, NULL, '2026-07-14 04:04:34', '3EB0BEEEB29B5DBAE2CB2E', '{\"ok\":true,\"messageId\":\"3EB0BEEEB29B5DBAE2CB2E\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-14 04:03:18', '2026-07-14 04:04:34'),
(40, 5, NULL, '9507286092', '919507286092', 'image', 'Nice', '{\"source\":\"manual\",\"type\":\"image\",\"attachment\":{\"type\":\"image\",\"mime\":\"image/png\",\"name\":\"player1.png\",\"path\":\"D:\\\\Projects\\\\whatsapp-api\\\\public\\\\uploads\\\\attachments\\\\user_5\\\\20260714164849-d0baa4271e14-player1.png\",\"size\":53937}}', 'sent', 1, 3, '2026-07-14 14:48:49', NULL, NULL, '2026-07-14 14:48:56', '3EB098CB3E7CF180A179F6', '{\"ok\":true,\"messageId\":\"3EB098CB3E7CF180A179F6\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-14 14:48:49', '2026-07-14 14:48:56'),
(41, 5, NULL, '6202404133', '916202404133', 'text', 'hhhhhh', '{\"source\":\"manual\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-14 16:27:40', NULL, NULL, '2026-07-14 16:30:03', '3EB0C3AD0DCF2E7AA4A9C4', '{\"ok\":true,\"messageId\":\"3EB0C3AD0DCF2E7AA4A9C4\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-14 16:27:40', '2026-07-14 16:30:03'),
(42, 5, NULL, '9507286092', '919507286092', 'document', 'zxxx', '{\"source\":\"manual\",\"type\":\"document\",\"attachment\":{\"type\":\"document\",\"mime\":\"application/pdf\",\"name\":\"ANKUSH-LOAN.pdf\",\"path\":\"D:\\\\Projects\\\\whatsapp-api\\\\public\\\\uploads\\\\attachments\\\\user_5\\\\20260715181017-7f5b1e99d09a-ANKUSH-LOAN.pdf\",\"size\":75385}}', 'sent', 1, 3, '2026-07-15 16:10:17', NULL, NULL, '2026-07-15 16:10:26', '3EB085C325C442ABFB19BD', '{\"ok\":true,\"messageId\":\"3EB085C325C442ABFB19BD\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-15 16:10:17', '2026-07-15 16:10:26'),
(43, 5, NULL, '9430815180', '919430815180', 'text', 'Hii', '{\"source\":\"manual\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-16 16:00:18', NULL, NULL, '2026-07-16 16:00:26', '3EB0640A644FD76D0833BC', '{\"ok\":true,\"messageId\":\"3EB0640A644FD76D0833BC\",\"normalizedTo\":\"919430815180\"}', NULL, '2026-07-16 16:00:18', '2026-07-16 16:00:26'),
(44, 5, NULL, '9430815180', '919430815180', 'image', 'Sexy hai baby 😛😛😛🍼🍼🍼🍼🍼🍼🍼', '{\"source\":\"manual\",\"type\":\"image\",\"attachment\":{\"type\":\"image\",\"mime\":\"image/jpeg\",\"name\":\"IMG20260712145904.jpg\",\"path\":\"D:\\\\Projects\\\\whatsapp-api\\\\public\\\\uploads\\\\attachments\\\\user_5\\\\20260716180721-13010af64c3b-IMG20260712145904.jpg\",\"size\":4982241}}', 'sent', 1, 3, '2026-07-16 16:07:21', NULL, NULL, '2026-07-16 16:09:24', '3EB014E3E1A6041B3EE9ED', '{\"ok\":true,\"messageId\":\"3EB014E3E1A6041B3EE9ED\",\"normalizedTo\":\"919430815180\"}', NULL, '2026-07-16 16:07:21', '2026-07-16 16:09:24'),
(45, 5, NULL, '9430815180', '919430815180', 'text', 'Hello dogla', '{\"source\":\"manual\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-16 16:09:32', NULL, NULL, '2026-07-16 16:09:36', '3EB00067AE2B3133C786FD', '{\"ok\":true,\"messageId\":\"3EB00067AE2B3133C786FD\",\"normalizedTo\":\"919430815180\"}', NULL, '2026-07-16 16:09:32', '2026-07-16 16:09:36'),
(46, 5, NULL, '6202404133', '916202404133', 'text', 'Hi if you think you are bad then i am your dad', '{\"source\":\"campaign\",\"campaign_id\":9,\"contact_id\":2,\"type\":\"text\"}', 'sent', 1, 3, '2026-07-16 16:12:01', NULL, NULL, '2026-07-16 16:12:07', '3EB0464D9504B567B302A1', '{\"ok\":true,\"messageId\":\"3EB0464D9504B567B302A1\",\"normalizedTo\":\"916202404133\"}', NULL, '2026-07-16 16:12:01', '2026-07-16 16:12:07'),
(47, 5, NULL, '9507286092', '919507286092', 'text', 'Hi if you think you are bad then i am your dad', '{\"source\":\"campaign\",\"campaign_id\":9,\"contact_id\":3,\"type\":\"text\"}', 'sent', 1, 3, '2026-07-16 16:12:01', NULL, NULL, '2026-07-16 16:12:13', '3EB0DA0346A89D3DBE6B11', '{\"ok\":true,\"messageId\":\"3EB0DA0346A89D3DBE6B11\",\"normalizedTo\":\"919507286092\"}', NULL, '2026-07-16 16:12:01', '2026-07-16 16:12:13'),
(48, 5, NULL, '9430815180', '919430815180', 'text', 'Hi if you think you are bad then i am your dad', '{\"source\":\"campaign\",\"campaign_id\":9,\"contact_id\":4,\"type\":\"text\"}', 'sent', 1, 3, '2026-07-16 16:12:01', NULL, NULL, '2026-07-16 16:12:19', '3EB0D0425B5F9764E98A9F', '{\"ok\":true,\"messageId\":\"3EB0D0425B5F9764E98A9F\",\"normalizedTo\":\"919430815180\"}', NULL, '2026-07-16 16:12:01', '2026-07-16 16:12:19'),
(49, 5, NULL, '9142058716', '919142058716', 'text', 'Bas 3333 💖 🎉 🥳 🎂 ✨', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"hour-3\",\"type\":\"text\"}', 'sent', 2, 3, '2026-07-21 15:30:18', NULL, NULL, '2026-07-21 15:30:22', '3EB06D1A578A0F8E9C7A5A', '{\"ok\":true,\"messageId\":\"3EB06D1A578A0F8E9C7A5A\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 15:30:00', '2026-07-21 15:30:22'),
(50, 5, NULL, '9142058716', '919142058716', 'text', 'Bas 2222 💫 🎂 💖', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"hour-2\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 16:30:02', NULL, NULL, '2026-07-21 16:30:06', '3EB035B8C39BE2D96E35A9', '{\"ok\":true,\"messageId\":\"3EB035B8C39BE2D96E35A9\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 16:30:02', '2026-07-21 16:30:06'),
(51, 5, NULL, '9142058716', '919142058716', 'text', 'Ab sirf ek Ghnta aur 😁😅, Happy Birthday Didi in advance 💖 🙏 🌸', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"hour-1\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:30:01', NULL, NULL, '2026-07-21 17:30:06', '3EB0A849269A492834F174', '{\"ok\":true,\"messageId\":\"3EB0A849269A492834F174\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:30:01', '2026-07-21 17:30:06'),
(52, 5, NULL, '9142058716', '919142058716', 'text', 'Only 60  min left 😁 Advance me birthday my sister ❤️ 🥳 👑 🌺 🥳 😊 💐', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-60\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:30:01', NULL, NULL, '2026-07-21 17:30:10', '3EB070260056EE97CAF432', '{\"ok\":true,\"messageId\":\"3EB070260056EE97CAF432\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:30:01', '2026-07-21 17:30:10'),
(53, 5, NULL, '9142058716', '919142058716', 'text', 'Only 59  min left 😁 Advance me birthday my sister ❤️ 🎂 🎂 🎂 🎉 🎂', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-59\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:31:01', NULL, NULL, '2026-07-21 17:31:04', '3EB02205C911B2D4881E75', '{\"ok\":true,\"messageId\":\"3EB02205C911B2D4881E75\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:31:01', '2026-07-21 17:31:04'),
(54, 5, NULL, '9142058716', '919142058716', 'text', 'Only 58  min left 😁 Advance me birthday my sister ❤️ ❤️ 💖 🥳 💐 💐 ❤️', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-58\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:32:01', NULL, NULL, '2026-07-21 17:32:04', '3EB013C8169C706F3880B3', '{\"ok\":true,\"messageId\":\"3EB013C8169C706F3880B3\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:32:01', '2026-07-21 17:32:04'),
(55, 5, NULL, '9142058716', '919142058716', 'text', 'Only 57  min left 😁 Advance me birthday my sister ❤️ 🥳 🎉 🥳 🥳 🎉 🎂', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-57\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:33:01', NULL, NULL, '2026-07-21 17:33:04', '3EB04C1AD20B429988A0D8', '{\"ok\":true,\"messageId\":\"3EB04C1AD20B429988A0D8\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:33:01', '2026-07-21 17:33:04'),
(56, 5, NULL, '9142058716', '919142058716', 'text', 'Only 56  min left 😁 Advance me birthday my sister ❤️ 🙏 👑 ❤️ 💖 🙏', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-56\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:34:02', NULL, NULL, '2026-07-21 17:34:05', '3EB00FB85A3CA45A766773', '{\"ok\":true,\"messageId\":\"3EB00FB85A3CA45A766773\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:34:02', '2026-07-21 17:34:05'),
(57, 5, NULL, '9142058716', '919142058716', 'text', 'Only 55  min left 😁 Advance me birthday my sister ❤️ 🌺 🌟 🌸 🌟 🎉 🙏', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-55\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:35:02', NULL, NULL, '2026-07-21 17:35:05', '3EB08C8E2B75C0959AE95D', '{\"ok\":true,\"messageId\":\"3EB08C8E2B75C0959AE95D\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:35:02', '2026-07-21 17:35:05'),
(58, 5, NULL, '9142058716', '919142058716', 'text', 'Only 54  min left 😁 Advance me birthday my sister ❤️ ✨ ❤️ 🥳', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-54\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:36:02', NULL, NULL, '2026-07-21 17:36:06', '3EB0E864EF6705B71AFFED', '{\"ok\":true,\"messageId\":\"3EB0E864EF6705B71AFFED\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:36:02', '2026-07-21 17:36:06'),
(59, 5, NULL, '9142058716', '919142058716', 'text', 'Only 53  min left 😁 Advance me birthday my sister ❤️ 🌟 🎉 🥳 🎂 🌟 🎂', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-53\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:37:02', NULL, NULL, '2026-07-21 17:37:05', '3EB09689FA41F59F77948F', '{\"ok\":true,\"messageId\":\"3EB09689FA41F59F77948F\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:37:02', '2026-07-21 17:37:05'),
(60, 5, NULL, '9142058716', '919142058716', 'text', 'Only 52  min left 😁 Advance me birthday my sister ❤️ 💐 🎉 🎂 🙏 🥳', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-52\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:38:02', NULL, NULL, '2026-07-21 17:38:05', '3EB08310A7A5510CBB9F76', '{\"ok\":true,\"messageId\":\"3EB08310A7A5510CBB9F76\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:38:02', '2026-07-21 17:38:05'),
(61, 5, NULL, '9142058716', '919142058716', 'text', 'Only 51  min left 😁 Advance me birthday my sister ❤️ ❤️ 👑 🎉', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-51\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:39:02', NULL, NULL, '2026-07-21 17:39:05', '3EB0DB42C08AADB5334BAC', '{\"ok\":true,\"messageId\":\"3EB0DB42C08AADB5334BAC\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:39:02', '2026-07-21 17:39:05'),
(62, 5, NULL, '9142058716', '919142058716', 'text', 'Only 50  min left 😁 Advance me birthday my sister ❤️ 🎂 🎉 🌸', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-50\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:40:02', NULL, NULL, '2026-07-21 17:40:05', '3EB0222CD06182846FB087', '{\"ok\":true,\"messageId\":\"3EB0222CD06182846FB087\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:40:02', '2026-07-21 17:40:05'),
(63, 5, NULL, '9142058716', '919142058716', 'text', 'Only 49  min left 😁 Advance me birthday my sister ❤️ 🌟 💖 🙏 ❤️', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-49\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:41:00', NULL, NULL, '2026-07-21 17:41:03', '3EB073B640AE75F76D728E', '{\"ok\":true,\"messageId\":\"3EB073B640AE75F76D728E\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:41:00', '2026-07-21 17:41:03'),
(64, 5, NULL, '9142058716', '919142058716', 'text', 'Only 48  min left 😁 Advance me birthday my sister ❤️ 🌟 🌸 🎉', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-48\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:42:00', NULL, NULL, '2026-07-21 17:42:04', '3EB0F89BEC71D116CA3768', '{\"ok\":true,\"messageId\":\"3EB0F89BEC71D116CA3768\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:42:00', '2026-07-21 17:42:04'),
(65, 5, NULL, '9142058716', '919142058716', 'text', 'Only 47  min left 😁 Advance me birthday my sister ❤️ ✨ 🎉 🎉 🌺 ✨', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-47\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:43:00', NULL, NULL, '2026-07-21 17:43:03', '3EB086E66B959D6E1E6286', '{\"ok\":true,\"messageId\":\"3EB086E66B959D6E1E6286\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:43:00', '2026-07-21 17:43:03'),
(66, 5, NULL, '9142058716', '919142058716', 'text', 'Only 46  min left 😁 Advance me birthday my sister ❤️ 🌸 💐 🌺 🙏', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-46\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:44:00', NULL, NULL, '2026-07-21 17:44:03', '3EB0460AC738EBC10E27A1', '{\"ok\":true,\"messageId\":\"3EB0460AC738EBC10E27A1\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:44:00', '2026-07-21 17:44:03'),
(67, 5, NULL, '9142058716', '919142058716', 'text', 'Only 45  min left 😁 Advance me birthday my sister ❤️ 💐 🎉 🎉 😊', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-45\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:45:00', NULL, NULL, '2026-07-21 17:45:03', '3EB04696F274EB13AE6D53', '{\"ok\":true,\"messageId\":\"3EB04696F274EB13AE6D53\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:45:00', '2026-07-21 17:45:03'),
(68, 5, NULL, '9142058716', '919142058716', 'text', 'Only 44  min left 😁 Advance me birthday my sister ❤️ 💐 👑 🎉 ✨', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-44\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:46:00', NULL, NULL, '2026-07-21 17:46:03', '3EB0D321977470988515B0', '{\"ok\":true,\"messageId\":\"3EB0D321977470988515B0\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:46:00', '2026-07-21 17:46:03'),
(69, 5, NULL, '9142058716', '919142058716', 'text', 'Only 43  min left 😁 Advance me birthday my sister ❤️ 😊 🌺 💖 ✨ 💖 🎉', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-43\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:47:00', NULL, NULL, '2026-07-21 17:47:04', '3EB0FF2BB1E4FCC0B0FFE2', '{\"ok\":true,\"messageId\":\"3EB0FF2BB1E4FCC0B0FFE2\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:47:00', '2026-07-21 17:47:04'),
(70, 5, NULL, '9142058716', '919142058716', 'text', 'Only 42  min left 😁 Advance me birthday my sister ❤️ 🥳 🥳 🎉 🌸', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-42\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:48:01', NULL, NULL, '2026-07-21 17:48:06', '3EB0E52E1D1A33C0A6C51C', '{\"ok\":true,\"messageId\":\"3EB0E52E1D1A33C0A6C51C\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:48:01', '2026-07-21 17:48:06'),
(71, 5, NULL, '9142058716', '919142058716', 'text', 'Only 41  min left 😁 Advance me birthday my sister ❤️ ❤️ 💫 🥳 🎉', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-41\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:49:01', NULL, NULL, '2026-07-21 17:49:04', '3EB058B87E9CBDBBC075CC', '{\"ok\":true,\"messageId\":\"3EB058B87E9CBDBBC075CC\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:49:01', '2026-07-21 17:49:04'),
(72, 5, NULL, '9142058716', '919142058716', 'text', 'Only 40  min left 😁 Advance me birthday my sister ❤️ 💐 💐 🎂 🙏', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-40\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:50:01', NULL, NULL, '2026-07-21 17:50:04', '3EB0162463982CCC48910F', '{\"ok\":true,\"messageId\":\"3EB0162463982CCC48910F\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:50:01', '2026-07-21 17:50:04'),
(73, 5, NULL, '9142058716', '919142058716', 'text', 'Only 39  min left 😁 Advance me birthday my sister ❤️ 🌟 🎂 😊 👑', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-39\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:51:01', NULL, NULL, '2026-07-21 17:51:04', '3EB0FD3ECCEB5D1BDC5A77', '{\"ok\":true,\"messageId\":\"3EB0FD3ECCEB5D1BDC5A77\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:51:01', '2026-07-21 17:51:04'),
(74, 5, NULL, '9142058716', '919142058716', 'text', 'Only 38  min left 😁 Advance me birthday my sister ❤️ 💖 🎉 🎉 👑 🌟 🎉', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-38\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:52:01', NULL, NULL, '2026-07-21 17:52:04', '3EB061A72E6BB7E4DD2F8C', '{\"ok\":true,\"messageId\":\"3EB061A72E6BB7E4DD2F8C\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:52:01', '2026-07-21 17:52:04'),
(75, 5, NULL, '9142058716', '919142058716', 'text', 'Only 37  min left 😁 Advance me birthday my sister ❤️ ❤️ 🌸 🙏 🌺 💫', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-37\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:53:01', NULL, NULL, '2026-07-21 17:53:04', '3EB0574762A00A8DF7029B', '{\"ok\":true,\"messageId\":\"3EB0574762A00A8DF7029B\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:53:01', '2026-07-21 17:53:04'),
(76, 5, NULL, '9142058716', '919142058716', 'text', 'Only 36  min left 😁 Advance me birthday my sister ❤️ ✨ 😊 💫 💐', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-36\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:54:02', NULL, NULL, '2026-07-21 17:54:06', '3EB0B36669FACBCAC372D5', '{\"ok\":true,\"messageId\":\"3EB0B36669FACBCAC372D5\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:54:02', '2026-07-21 17:54:06'),
(77, 5, NULL, '9142058716', '919142058716', 'text', 'Only 35  min left 😁 Advance me birthday my sister ❤️ 🎉 😊 💐 💫 💐 🌺', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-35\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:55:02', NULL, NULL, '2026-07-21 17:55:05', '3EB0A63991BD86DBFAF63B', '{\"ok\":true,\"messageId\":\"3EB0A63991BD86DBFAF63B\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:55:02', '2026-07-21 17:55:05'),
(78, 5, NULL, '9142058716', '919142058716', 'text', 'Only 34  min left 😁 Advance me birthday my sister ❤️ 🌟 💫 🙏 🎂 👑', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-34\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:56:02', NULL, NULL, '2026-07-21 17:56:05', '3EB09520EF6E3A90D401D7', '{\"ok\":true,\"messageId\":\"3EB09520EF6E3A90D401D7\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:56:02', '2026-07-21 17:56:05'),
(79, 5, NULL, '9142058716', '919142058716', 'text', 'Only 33  min left 😁 Advance me birthday my sister ❤️ 💫 🌟 🌺 🌸', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-33\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:57:02', NULL, NULL, '2026-07-21 17:57:05', '3EB09D7861AA8F087525B1', '{\"ok\":true,\"messageId\":\"3EB09D7861AA8F087525B1\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:57:02', '2026-07-21 17:57:05'),
(80, 5, NULL, '9142058716', '919142058716', 'text', 'Only 32  min left 😁 Advance me birthday my sister ❤️ 💐 🎂 ✨', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-32\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:58:02', NULL, NULL, '2026-07-21 17:58:05', '3EB070EDD6214C381EB190', '{\"ok\":true,\"messageId\":\"3EB070EDD6214C381EB190\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:58:02', '2026-07-21 17:58:05'),
(81, 5, NULL, '9142058716', '919142058716', 'text', 'Only 31  min left 😁 Advance me birthday my sister ❤️ ❤️ 👑 🌟 🌺 👑', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-31\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 17:59:02', NULL, NULL, '2026-07-21 17:59:06', '3EB0AD19A2708145C0B184', '{\"ok\":true,\"messageId\":\"3EB0AD19A2708145C0B184\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 17:59:02', '2026-07-21 17:59:06'),
(82, 5, NULL, '9142058716', '919142058716', 'text', 'Only 30  min left 😁 Advance me birthday my sister ❤️ 🥳 🎂 💫', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-30\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:00:00', NULL, NULL, '2026-07-21 18:00:04', '3EB02F3B55CFD18E64C4F7', '{\"ok\":true,\"messageId\":\"3EB02F3B55CFD18E64C4F7\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:00:00', '2026-07-21 18:00:04'),
(83, 5, NULL, '9142058716', '919142058716', 'text', 'Only 29  min left 😁 Advance me birthday my sister ❤️ 💖 ❤️ 👑 💐 🌺', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-29\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:01:00', NULL, NULL, '2026-07-21 18:01:03', '3EB0461212D32184D919FB', '{\"ok\":true,\"messageId\":\"3EB0461212D32184D919FB\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:01:00', '2026-07-21 18:01:03'),
(84, 5, NULL, '9142058716', '919142058716', 'text', 'Only 28  min left 😁 Advance me birthday my sister ❤️ 🌟 🌺 🙏 🌸 🌟', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-28\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:02:00', NULL, NULL, '2026-07-21 18:02:03', '3EB08F1A3AF4ACE7AFEC02', '{\"ok\":true,\"messageId\":\"3EB08F1A3AF4ACE7AFEC02\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:02:00', '2026-07-21 18:02:03'),
(85, 5, NULL, '9142058716', '919142058716', 'text', 'Only 27  min left 😁 Advance me birthday my sister ❤️ 💐 🎉 ❤️', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-27\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:03:00', NULL, NULL, '2026-07-21 18:03:03', '3EB0D79FDC28DD2D893AE2', '{\"ok\":true,\"messageId\":\"3EB0D79FDC28DD2D893AE2\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:03:00', '2026-07-21 18:03:03'),
(86, 5, NULL, '9142058716', '919142058716', 'text', 'Only 26  min left 😁 Advance me birthday my sister ❤️ 💫 🙏 😊 ✨ 🌺', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-26\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:04:00', NULL, NULL, '2026-07-21 18:04:03', '3EB07D1E7451FE4A31C070', '{\"ok\":true,\"messageId\":\"3EB07D1E7451FE4A31C070\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:04:00', '2026-07-21 18:04:03'),
(87, 5, NULL, '9142058716', '919142058716', 'text', 'Only 25  min left 😁 Advance me birthday my sister ❤️ 😊 🥳 💖 🎂', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-25\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:05:00', NULL, NULL, '2026-07-21 18:05:03', '3EB065B63C975BBCD6297C', '{\"ok\":true,\"messageId\":\"3EB065B63C975BBCD6297C\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:05:00', '2026-07-21 18:05:03'),
(88, 5, NULL, '9142058716', '919142058716', 'text', 'Only 24  min left 😁 Advance me birthday my sister ❤️ 🎉 🌟 ✨ ❤️', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-24\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:06:01', NULL, NULL, '2026-07-21 18:06:05', '3EB0E7C244D53DF4D08A59', '{\"ok\":true,\"messageId\":\"3EB0E7C244D53DF4D08A59\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:06:01', '2026-07-21 18:06:05'),
(89, 5, NULL, '9142058716', '919142058716', 'text', 'Only 23  min left 😁 Advance me birthday my sister ❤️ ❤️ 🌟 😊 💖 🙏', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-23\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:07:01', NULL, NULL, '2026-07-21 18:07:04', '3EB0215701B716623F8740', '{\"ok\":true,\"messageId\":\"3EB0215701B716623F8740\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:07:01', '2026-07-21 18:07:04'),
(90, 5, NULL, '9142058716', '919142058716', 'text', 'Only 22  min left 😁 Advance me birthday my sister ❤️ 😊 😊 ❤️', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-22\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:08:01', NULL, NULL, '2026-07-21 18:08:04', '3EB0DAF86858A1708E40C6', '{\"ok\":true,\"messageId\":\"3EB0DAF86858A1708E40C6\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:08:01', '2026-07-21 18:08:04'),
(91, 5, NULL, '9142058716', '919142058716', 'text', 'Only 21  min left 😁 Advance me birthday my sister ❤️ 🎂 💫 🙏 🥳 🎂', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-21\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:09:01', NULL, NULL, '2026-07-21 18:09:04', '3EB047A9BACAB7D65FA5AB', '{\"ok\":true,\"messageId\":\"3EB047A9BACAB7D65FA5AB\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:09:01', '2026-07-21 18:09:04'),
(92, 5, NULL, '9142058716', '919142058716', 'text', 'Only 20  min left 😁 Advance me birthday my sister ❤️ 🎉 ❤️ ❤️ 🎂', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-20\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:10:01', NULL, NULL, '2026-07-21 18:10:04', '3EB027BB5F1109125D4C9C', '{\"ok\":true,\"messageId\":\"3EB027BB5F1109125D4C9C\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:10:01', '2026-07-21 18:10:04'),
(93, 5, NULL, '9142058716', '919142058716', 'text', 'Only 19  min left 😁 Advance me birthday my sister ❤️ 🙏 🌺 💐 👑 🎂', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-19\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:11:01', NULL, NULL, '2026-07-21 18:11:04', '3EB0FEAD32C85E68C34A7D', '{\"ok\":true,\"messageId\":\"3EB0FEAD32C85E68C34A7D\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:11:01', '2026-07-21 18:11:04'),
(94, 5, NULL, '9142058716', '919142058716', 'text', 'Only 18  min left 😁 Advance me birthday my sister ❤️ 💐 🌺 👑', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-18\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:12:01', NULL, NULL, '2026-07-21 18:12:06', '3EB068E2773453124E2144', '{\"ok\":true,\"messageId\":\"3EB068E2773453124E2144\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:12:01', '2026-07-21 18:12:06'),
(95, 5, NULL, '9142058716', '919142058716', 'text', 'Only 17  min left 😁 Advance me birthday my sister ❤️ 🎂 🎂 💫 ❤️', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-17\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:13:01', NULL, NULL, '2026-07-21 18:13:04', '3EB090D4FFEC5B08FBACC6', '{\"ok\":true,\"messageId\":\"3EB090D4FFEC5B08FBACC6\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:13:01', '2026-07-21 18:13:04'),
(96, 5, NULL, '9142058716', '919142058716', 'text', 'Only 16  min left 😁 Advance me birthday my sister ❤️ 🙏 💖 ❤️', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-16\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:14:01', NULL, NULL, '2026-07-21 18:14:04', '3EB0C97CAFFA2FB86AA115', '{\"ok\":true,\"messageId\":\"3EB0C97CAFFA2FB86AA115\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:14:01', '2026-07-21 18:14:04'),
(97, 5, NULL, '9142058716', '919142058716', 'text', 'Only 15  min left 😁 Advance me birthday my sister ❤️ 👑 🙏 🎉', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-15\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:15:01', NULL, NULL, '2026-07-21 18:15:04', '3EB075C5D806C4349152CB', '{\"ok\":true,\"messageId\":\"3EB075C5D806C4349152CB\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:15:01', '2026-07-21 18:15:04'),
(98, 5, NULL, '9142058716', '919142058716', 'text', 'Only 14  min left 😁 Advance me birthday my sister ❤️ 🌺 🙏 🎉 ✨ 💐 🌟', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-14\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:16:01', NULL, NULL, '2026-07-21 18:16:04', '3EB0E1CCAEF4A1986AFB8A', '{\"ok\":true,\"messageId\":\"3EB0E1CCAEF4A1986AFB8A\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:16:01', '2026-07-21 18:16:04'),
(99, 5, NULL, '9142058716', '919142058716', 'text', 'Only 13  min left 😁 Advance me birthday my sister ❤️ 🌺 🌺 👑 💐 🎉', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-13\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:17:01', NULL, NULL, '2026-07-21 18:17:04', '3EB044084547622397E942', '{\"ok\":true,\"messageId\":\"3EB044084547622397E942\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:17:01', '2026-07-21 18:17:04'),
(100, 5, NULL, '9142058716', '919142058716', 'text', 'Only 12  min left 😁 Advance me birthday my sister ❤️ 💐 ❤️ 😊 💖', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-12\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:18:02', NULL, NULL, '2026-07-21 18:18:06', '3EB097A52734B6B096DFC4', '{\"ok\":true,\"messageId\":\"3EB097A52734B6B096DFC4\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:18:02', '2026-07-21 18:18:06'),
(101, 5, NULL, '9142058716', '919142058716', 'text', 'Only 11  min left 😁 Advance me birthday my sister ❤️ 🙏 ❤️ 🎂', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-11\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:19:02', NULL, NULL, '2026-07-21 18:19:05', '3EB07A0F3073F7A71A5CFA', '{\"ok\":true,\"messageId\":\"3EB07A0F3073F7A71A5CFA\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:19:02', '2026-07-21 18:19:05'),
(102, 5, NULL, '9142058716', '919142058716', 'text', 'Only 10  min left 😁 Advance me birthday my sister ❤️ 💖 🎂 👑 👑 🥳 🥳', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-10\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:20:02', NULL, NULL, '2026-07-21 18:20:05', '3EB080C1F955A67BE3FFC5', '{\"ok\":true,\"messageId\":\"3EB080C1F955A67BE3FFC5\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:20:02', '2026-07-21 18:20:05'),
(103, 5, NULL, '9142058716', '919142058716', 'text', 'Only 9  min left 😁 Advance me birthday my sister ❤️ 🙏 🙏 ❤️ 🙏', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-9\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:21:02', NULL, NULL, '2026-07-21 18:21:05', '3EB0F61129B7F377DC4ED4', '{\"ok\":true,\"messageId\":\"3EB0F61129B7F377DC4ED4\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:21:02', '2026-07-21 18:21:05'),
(104, 5, NULL, '9142058716', '919142058716', 'text', 'Only 8  min left 😁 Advance me birthday my sister ❤️ 🙏 ✨ 💐 ❤️ 💐', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-8\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:22:02', NULL, NULL, '2026-07-21 18:22:05', '3EB080A7588B33FEBB5EBF', '{\"ok\":true,\"messageId\":\"3EB080A7588B33FEBB5EBF\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:22:02', '2026-07-21 18:22:05'),
(105, 5, NULL, '9142058716', '919142058716', 'text', 'Only 7  min left 😁 Advance me birthday my sister ❤️ 👑 🥳 🌸 👑 🌟', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-7\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:23:02', NULL, NULL, '2026-07-21 18:23:05', '3EB0250A42018413CB06D2', '{\"ok\":true,\"messageId\":\"3EB0250A42018413CB06D2\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:23:02', '2026-07-21 18:23:05'),
(106, 5, NULL, '9142058716', '919142058716', 'text', 'Only 6  min left 😁 Advance me birthday my sister ❤️ 🌺 ❤️ ❤️ ✨ 🌟 🎂', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-6\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:24:02', NULL, NULL, '2026-07-21 18:24:07', '3EB056DBA6CD3F314CA5BE', '{\"ok\":true,\"messageId\":\"3EB056DBA6CD3F314CA5BE\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:24:02', '2026-07-21 18:24:07'),
(107, 5, NULL, '9142058716', '919142058716', 'text', 'Only 5  min left 😁 Advance me birthday my sister ❤️ 🌸 🎉 🌟 🎉 ❤️', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-5\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:25:03', NULL, NULL, '2026-07-21 18:25:06', '3EB0FB25CEE4D1CDFA86B6', '{\"ok\":true,\"messageId\":\"3EB0FB25CEE4D1CDFA86B6\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:25:03', '2026-07-21 18:25:06'),
(108, 5, NULL, '9142058716', '919142058716', 'text', 'Only 4  min left 😁 Advance me birthday my sister ❤️ ❤️ 🙏 🌸 🥳 👑', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-4\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:26:00', NULL, NULL, '2026-07-21 18:26:03', '3EB0F819DC8E47F3ACCFAF', '{\"ok\":true,\"messageId\":\"3EB0F819DC8E47F3ACCFAF\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:26:00', '2026-07-21 18:26:03'),
(109, 5, NULL, '9142058716', '919142058716', 'text', 'Only 3  min left 😁 Advance me birthday my sister ❤️ 🌺 ✨ ❤️', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-3\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:27:00', NULL, NULL, '2026-07-21 18:27:03', '3EB02AFF879B790C1A3581', '{\"ok\":true,\"messageId\":\"3EB02AFF879B790C1A3581\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:27:00', '2026-07-21 18:27:03'),
(110, 5, NULL, '9142058716', '919142058716', 'text', 'Only 2  min left 😁 Advance me birthday my sister ❤️ 💫 💖 ❤️', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-2\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:28:00', NULL, NULL, '2026-07-21 18:28:03', '3EB08442AE90C5A50ACD06', '{\"ok\":true,\"messageId\":\"3EB08442AE90C5A50ACD06\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:28:00', '2026-07-21 18:28:03');
INSERT INTO `message_queue` (`id`, `user_id`, `api_key_id`, `recipient`, `normalized_recipient`, `message_type`, `body`, `payload`, `status`, `attempts`, `max_attempts`, `available_at`, `locked_at`, `locked_by`, `sent_at`, `provider_message_id`, `provider_response`, `error_message`, `created_at`, `updated_at`) VALUES
(111, 5, NULL, '9142058716', '919142058716', 'text', 'Only 1  min left 😁 Advance me birthday my sister ❤️ 🌟 🥳 ✨ 🌟 ❤️', '{\"source\":\"birthday-task\",\"task_id\":2,\"occurrence_year\":2026,\"event_key\":\"minute-1\",\"type\":\"text\"}', 'sent', 1, 3, '2026-07-21 18:29:00', NULL, NULL, '2026-07-21 18:29:03', '3EB0D5C90C22E4F078BD93', '{\"ok\":true,\"messageId\":\"3EB0D5C90C22E4F078BD93\",\"normalizedTo\":\"919142058716\"}', NULL, '2026-07-21 18:29:00', '2026-07-21 18:29:03'),
(112, 1, NULL, '9507286092', NULL, 'text', 'hello', '{\"source\":\"manual\",\"type\":\"text\"}', 'queued', 0, 3, '2026-09-14 20:45:10', NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-14 20:45:10', '2026-09-14 20:45:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `api_key_hash` char(64) DEFAULT NULL,
  `api_key_prefix` varchar(32) DEFAULT NULL,
  `api_secret_hash` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `api_key_hash`, `api_key_prefix`, `api_secret_hash`, `created_at`, `updated_at`) VALUES
(1, 'Navneet Kumar Yadav', 'navneetyadav982008@gmail.com', '$2y$10$0f54t5I9xHBfsjIEghmb/Oo81PHo0iXrYH5EMRkjeZOmTde4duDEG', '58901c3bfb7c80d3923f6a2f9705de7a80b751a4e63513ea060eb2768f91163d', 'wa_live_f89b0312c06de1', '$2y$10$9m3OyoMuxsOOnWLNgx9aMuYPtUOppNmHHNSI.G9IO9BIsBT3W1xGK', '2026-06-02 07:16:18', '2026-06-02 07:27:21'),
(2, 'Navneet Yadav', 'admin@admin.com', '$2y$10$5rmTbGFTeRK.GzMtfWy.Zunwz0ytkvEy0Tk2mu0..8OoX6y525nYe', '29a451f98b0137588189ceb0dc07db482432f2d7f34ce3e57afe980cdceb2460', 'wa_live_eb31a502ec0d77', '$2y$10$u6KR8pm6Cdu61xgzm.6tYOYcNlFWqL5LRpX7wdIKVkIAhKi6DvOAO', '2026-06-02 10:08:49', '2026-06-02 10:08:49'),
(5, 'Navneet Kumar Yadav', 'asdf@gmail.com', '$2y$10$wvzIrWFqqSrsT9.pW8.IpeParBDbiYgRnFW5/nbWQ6ogo60.BXszS', NULL, NULL, NULL, '2026-07-13 14:12:52', '2026-07-13 14:12:52'),
(6, 'hahah', 'asd@gmail.com', '$2y$10$y3c.JXrgxrUigq73KHJME.2HyLvG2YqxaSIypACtunOA/KGEhpv.u', NULL, NULL, NULL, '2026-08-06 13:22:04', '2026-08-06 13:22:04');

-- --------------------------------------------------------

--
-- Table structure for table `webhooks`
--

CREATE TABLE `webhooks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `target_url` varchar(500) DEFAULT NULL,
  `secret` varchar(120) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `webhooks`
--

INSERT INTO `webhooks` (`id`, `user_id`, `target_url`, `secret`, `enabled`, `created_at`, `updated_at`) VALUES
(1, 2, NULL, NULL, 0, '2026-06-02 10:09:26', '2026-06-02 10:09:26'),
(2, 1, NULL, NULL, 0, '2026-06-02 10:09:26', '2026-06-02 10:09:26'),
(6, 5, NULL, NULL, 0, '2026-07-13 14:12:52', '2026-07-13 14:12:52'),
(7, 6, NULL, NULL, 0, '2026-08-06 13:22:04', '2026-08-06 13:22:04');

-- --------------------------------------------------------

--
-- Table structure for table `webhook_deliveries`
--

CREATE TABLE `webhook_deliveries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `webhook_id` bigint(20) UNSIGNED DEFAULT NULL,
  `message_id` bigint(20) UNSIGNED DEFAULT NULL,
  `event` varchar(80) NOT NULL,
  `target_url` varchar(500) NOT NULL,
  `status_code` int(10) UNSIGNED DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `attempt` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_sessions`
--

CREATE TABLE `whatsapp_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('idle','connecting','qr','connected','disconnected','error') NOT NULL DEFAULT 'idle',
  `phone` varchar(80) DEFAULT NULL,
  `push_name` varchar(120) DEFAULT NULL,
  `qr_updated_at` timestamp NULL DEFAULT NULL,
  `connected_at` timestamp NULL DEFAULT NULL,
  `disconnected_at` timestamp NULL DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `whatsapp_sessions`
--

INSERT INTO `whatsapp_sessions` (`id`, `user_id`, `status`, `phone`, `push_name`, `qr_updated_at`, `connected_at`, `disconnected_at`, `last_error`, `created_at`, `updated_at`) VALUES
(1, 1, 'disconnected', '917491954139:25@s.whatsapp.net', 'Assist Group', NULL, '2026-06-02 11:50:12', '2026-06-02 17:16:44', 'WebSocket Error (getaddrinfo ENOTFOUND web.whatsapp.com)', '2026-06-02 07:16:18', '2026-06-02 17:16:44'),
(2, 2, 'idle', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-02 10:08:49', '2026-06-02 10:08:49'),
(5, 5, 'idle', '917491954139:29@s.whatsapp.net', 'Assist Group', NULL, '2026-07-21 17:14:30', '2026-07-21 19:27:25', 'Stream Errored (conflict)', '2026-07-13 14:12:52', '2026-07-21 19:27:25'),
(6, 6, 'connected', '919507286092:57@s.whatsapp.net', NULL, NULL, '2026-08-06 13:23:23', NULL, NULL, '2026-08-06 13:22:04', '2026-08-06 13:23:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_hash` (`key_hash`),
  ADD KEY `idx_api_keys_user_status` (`user_id`,`status`);

--
-- Indexes for table `api_request_logs`
--
ALTER TABLE `api_request_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_api_request_logs_user_created` (`user_id`,`created_at`),
  ADD KEY `idx_api_request_logs_key_created` (`api_key_id`,`created_at`);

--
-- Indexes for table `birthday_tasks`
--
ALTER TABLE `birthday_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_birthday_tasks_contact` (`contact_id`),
  ADD KEY `idx_birthday_tasks_user_status` (`user_id`,`status`);

--
-- Indexes for table `birthday_task_logs`
--
ALTER TABLE `birthday_task_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_birthday_task_event` (`birthday_task_id`,`occurrence_year`,`event_key`),
  ADD KEY `idx_birthday_task_logs_schedule` (`scheduled_for`);

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_campaigns_user_created` (`user_id`,`created_at`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_contacts_user_phone` (`user_id`,`phone`),
  ADD KEY `idx_contacts_user` (`user_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_attempts_email_ip_time` (`email`,`ip_address`,`attempted_at`);

--
-- Indexes for table `message_logs`
--
ALTER TABLE `message_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message_logs_user_created` (`user_id`,`created_at`);

--
-- Indexes for table `message_queue`
--
ALTER TABLE `message_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message_queue_worker` (`status`,`available_at`,`locked_at`),
  ADD KEY `idx_message_queue_user_created` (`user_id`,`created_at`),
  ADD KEY `idx_message_queue_api_key_created` (`api_key_id`,`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `webhooks`
--
ALTER TABLE `webhooks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `webhook_deliveries`
--
ALTER TABLE `webhook_deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_webhook_deliveries_webhook` (`webhook_id`),
  ADD KEY `idx_webhook_deliveries_user_created` (`user_id`,`created_at`),
  ADD KEY `idx_webhook_deliveries_message` (`message_id`);

--
-- Indexes for table `whatsapp_sessions`
--
ALTER TABLE `whatsapp_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `api_request_logs`
--
ALTER TABLE `api_request_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `birthday_tasks`
--
ALTER TABLE `birthday_tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `birthday_task_logs`
--
ALTER TABLE `birthday_task_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1449;

--
-- AUTO_INCREMENT for table `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `message_logs`
--
ALTER TABLE `message_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `message_queue`
--
ALTER TABLE `message_queue`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `webhooks`
--
ALTER TABLE `webhooks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `webhook_deliveries`
--
ALTER TABLE `webhook_deliveries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `whatsapp_sessions`
--
ALTER TABLE `whatsapp_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD CONSTRAINT `fk_api_keys_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `api_request_logs`
--
ALTER TABLE `api_request_logs`
  ADD CONSTRAINT `fk_api_request_logs_api_key` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_api_request_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `birthday_tasks`
--
ALTER TABLE `birthday_tasks`
  ADD CONSTRAINT `fk_birthday_tasks_contact` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_birthday_tasks_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `birthday_task_logs`
--
ALTER TABLE `birthday_task_logs`
  ADD CONSTRAINT `fk_birthday_task_logs_task` FOREIGN KEY (`birthday_task_id`) REFERENCES `birthday_tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD CONSTRAINT `fk_campaigns_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `fk_contacts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_logs`
--
ALTER TABLE `message_logs`
  ADD CONSTRAINT `fk_message_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_queue`
--
ALTER TABLE `message_queue`
  ADD CONSTRAINT `fk_message_queue_api_key` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_message_queue_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `webhooks`
--
ALTER TABLE `webhooks`
  ADD CONSTRAINT `fk_webhooks_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `webhook_deliveries`
--
ALTER TABLE `webhook_deliveries`
  ADD CONSTRAINT `fk_webhook_deliveries_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_webhook_deliveries_webhook` FOREIGN KEY (`webhook_id`) REFERENCES `webhooks` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `whatsapp_sessions`
--
ALTER TABLE `whatsapp_sessions`
  ADD CONSTRAINT `fk_whatsapp_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
CREATE TABLE IF NOT EXISTS chatbot_rules (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL, keyword VARCHAR(255) NOT NULL, match_type ENUM('exact', 'contains', 'starts_with', 'catch_all') DEFAULT 'exact', reply_text TEXT NOT NULL, status ENUM('active', 'disabled') DEFAULT 'active', created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE);


