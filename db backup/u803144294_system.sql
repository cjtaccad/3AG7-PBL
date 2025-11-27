-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 27, 2025 at 07:08 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u803144294_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `adminnotes`
--

CREATE TABLE `adminnotes` (
  `note_id` int(11) NOT NULL,
  `admin_user_id` int(11) NOT NULL,
  `note_text` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adminnotes`
--

INSERT INTO `adminnotes` (`note_id`, `admin_user_id`, `note_text`, `updated_at`) VALUES
(1, 1, '<br>', '2025-11-26 00:15:21');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`user_id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `announcementfiles`
--

CREATE TABLE `announcementfiles` (
  `file_id` int(11) NOT NULL,
  `announcement_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcementfiles`
--

INSERT INTO `announcementfiles` (`file_id`, `announcement_id`, `file_name`, `file_path`, `uploaded_at`) VALUES
(1, 9, '2x2 picture discount.jpg', 'D:\\XAMPP\\htdocs\\evoting_system/uploads/announcements/ann_690c108694ecc.jpg', '2025-11-06 11:05:54'),
(2, 10, '2x2 picture discount.jpg', 'D:\\XAMPP\\htdocs\\evoting_system/uploads/announcements/ann_690c10a63e60a.jpg', '2025-11-06 11:06:27'),
(3, 11, '2x2 picture discount.jpg', 'D:\\XAMPP\\htdocs\\evoting_system/uploads/announcements/ann_690c10d9c5124.jpg', '2025-11-06 11:07:19'),
(4, 11, '2x2 picture discount.jpg', 'D:\\XAMPP\\htdocs\\evoting_system/uploads/announcements/ann_690c10d9c525b.jpg', '2025-11-06 11:07:19'),
(5, 12, '2x2 picture discount.jpg', 'D:\\XAMPP\\htdocs\\evoting_system/uploads/announcements/ann_690c11066063e.jpg', '2025-11-06 11:08:02'),
(6, 12, 'fix tomorrow.txt', 'D:\\XAMPP\\htdocs\\evoting_system/uploads/announcements/ann_690c11066076d.txt', '2025-11-06 11:08:02'),
(7, 26, 'taccad_qrcode.jpg', 'D:\\XAMPP\\htdocs\\evoting_system/uploads/announcements/ann_690c20ecec0bc.jpg', '2025-11-06 12:15:54'),
(8, 26, 'fix tomorrow.txt', 'D:\\XAMPP\\htdocs\\evoting_system/uploads/announcements/ann_690c20ecec243.txt', '2025-11-06 12:15:54'),
(9, 27, 'Screenshot 2024-12-10 174713.png', 'D:\\XAMPP\\htdocs\\evoting_system/uploads/announcements/ann_691a7e42c7f2a.png', '2025-11-17 09:45:53'),
(10, 28, 'Screenshot 2024-12-10 174713.png', 'D:\\XAMPP\\htdocs\\evoting_system/uploads/announcements/ann_691a7e51ebb62.png', '2025-11-17 09:46:08'),
(11, 29, 'Screenshot 2024-12-10 174713.png', 'D:\\XAMPP\\htdocs\\evoting_system/uploads/announcements/ann_691a7e6100a61.png', '2025-11-17 09:46:22'),
(12, 30, 'Screenshot 2024-12-10 174713.png', 'D:\\XAMPP\\htdocs\\evoting_system/uploads/announcements/ann_691a7e6ecc386.png', '2025-11-17 09:46:38'),
(13, 31, 'Screenshot 2024-12-10 174713.png', 'D:\\XAMPP\\htdocs\\evoting_system/uploads/announcements/ann_691a7e7e530ad.png', '2025-11-17 09:46:52'),
(14, 32, 'Screenshot 2024-12-10 174713.png', 'D:\\XAMPP\\htdocs\\evoting_system/uploads/announcements/ann_691a7e8ce9149.png', '2025-11-17 09:47:08'),
(15, 33, 'Screenshot 2024-12-10 174713.png', 'D:\\XAMPP\\htdocs\\evoting_system/uploads/announcements/ann_691a7e9cb733a.png', '2025-11-17 09:47:22'),
(16, 36, 'taccad_qrcode.jpg', '/home/u803144294/domains/umakevoting.online/public_html/uploads/announcements/ann_6922a8773b4d7.jpg', '2025-11-23 06:24:03'),
(17, 37, 'taccad_qrcode.jpg', '/home/u803144294/domains/umakevoting.online/public_html/uploads/announcements/ann_69257bc6c4d31.jpg', '2025-11-25 09:50:10'),
(18, 39, 'TACCAD__CHRISTIAN_JAKE_B_(3).png', '/home/u803144294/domains/umakevoting.online/public_html/uploads/announcements/ann_6927d0766fbd8.png', '2025-11-27 04:16:03');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `title`, `message`, `created_at`, `created_by`) VALUES
(1, 'test1', '<a href=\"https://www.youtube.com/\" target=\"_blank\" rel=\"noopener\">fafafafafa</a>', '2025-11-06 10:33:59', 1),
(2, 'test2', '<a href=\"fafafafafa\" target=\"_blank\" rel=\"noopener\">fafafafafa</a>', '2025-11-06 10:34:41', 1),
(3, 'test3', '<a href=\"http://localhost/evoting_system/login.php\" target=\"_blank\" rel=\"noopener\">fafafafafa</a>', '2025-11-06 10:37:22', 1),
(4, 'test4', '<a href=\"https://www.youtube.com/\" target=\"_blank\" rel=\"noopener\">lol</a>', '2025-11-06 10:39:41', 1),
(5, 'test5', '<a href=\"https://www.youtube.com/\" target=\"_blank\" rel=\"noopener\">fafafafafa</a>', '2025-11-06 10:40:26', 1),
(6, 'test1', 'hello', '2025-11-06 11:00:42', 1),
(7, 'test1', '<a href=\"https://www.youtube.com/\" target=\"_blank\" rel=\"noopener\">ok</a>', '2025-11-06 11:02:06', 1),
(8, 'test1', '<a href=\"https://www.roblox.com/home\" target=\"_blank\" rel=\"noopener\">lel</a>', '2025-11-06 11:04:08', 1),
(9, 'test1', 'lol', '2025-11-06 11:05:54', 1),
(10, 'test1', 'lol', '2025-11-06 11:06:27', 1),
(11, 'test1', 'lol', '2025-11-06 11:07:19', 1),
(12, 'test1', 'lol', '2025-11-06 11:08:02', 1),
(13, 'test1', '<a href=\"https://www.youtube.com/\" target=\"_blank\" rel=\"noopener\">lol</a>', '2025-11-06 11:16:31', 1),
(14, 'test1', '<a href=\"https://www.youtube.com/\" target=\"_blank\" rel=\"noopener\">https://www.youtube.com/</a>', '2025-11-06 11:18:25', 1),
(15, 'test1', '<a href=\"https://fafafafafa\" target=\"_blank\" rel=\"noopener\">work</a>', '2025-11-06 11:19:19', 1),
(16, 'test1', '<a href=\"http://localhost/evoting_system/login.php\" target=\"_blank\" rel=\"noopener\">go</a>', '2025-11-06 11:21:09', 1),
(17, 'test1', '<a href=\"https://www.youtube.com/\" target=\"_blank\" rel=\"noopener\">lol</a>', '2025-11-06 11:36:07', 1),
(18, 'test1', 'lol', '2025-11-06 11:37:27', 1),
(19, 'test1', '<a href=\"https://www.youtube.com/\" target=\"_blank\" rel=\"noopener\">lol</a>', '2025-11-06 11:43:20', 1),
(20, 'test1', '<a href=\"https://fffffffff\" target=\"_blank\" rel=\"noopener\">fff</a>', '2025-11-06 11:56:08', 1),
(21, 'test1', '<a href=\"https://login\" target=\"_blank\" rel=\"noopener\">click here</a>', '2025-11-06 12:02:13', 1),
(22, 'test1', '<a href=\"https://fak\" target=\"_blank\" rel=\"noopener\">lol</a>', '2025-11-06 12:11:47', 1),
(23, 'test1', '<a href=\"https://www.youtube.com/\" target=\"_blank\" rel=\"noopener\">i hate youuu</a>', '2025-11-06 12:12:36', 1),
(24, 'test1', '<a href=\"https://yeah\" target=\"_blank\" rel=\"noopener\">lol</a>', '2025-11-06 12:14:03', 1),
(25, 'test1', '<a href=\"https://www.youtube.com/\" target=\"_blank\" rel=\"noopener\">hada</a>', '2025-11-06 12:14:34', 1),
(26, 'test1', 'lol', '2025-11-06 12:15:54', 1),
(27, 'test1', '<b>dadad</b>', '2025-11-17 09:45:53', 1),
(28, 'test1', '<b>dadad</b>', '2025-11-17 09:46:08', 1),
(29, 'test1', '<b>dadad</b>', '2025-11-17 09:46:22', 1),
(30, 'test1', '<b>dadad</b>', '2025-11-17 09:46:38', 1),
(31, 'test1', '<b>dadad</b>', '2025-11-17 09:46:52', 1),
(32, 'test1', '<b>dadad</b>', '2025-11-17 09:47:08', 1),
(33, 'test1', '<b>dadad</b>', '2025-11-17 09:47:22', 1),
(34, 'test1', 'ada', '2025-11-17 10:34:47', 1),
(35, 'test1', 'aba', '2025-11-18 00:57:36', 1),
(36, 'schedule', '1321312', '2025-11-23 06:24:03', 1),
(37, 'test1', '21313<b>123123</b>', '2025-11-25 09:50:10', 1),
(38, 'test1', '<a href=\"https://umakevoting.online\" target=\"_blank\" rel=\"noopener\">click here</a>', '2025-11-25 09:51:02', 1),
(39, 'test1', '1212 123<i>123</i>23<b>1</b>31 231123', '2025-11-27 04:16:03', 1);

-- --------------------------------------------------------

--
-- Table structure for table `auditlogs`
--

CREATE TABLE `auditlogs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(200) NOT NULL,
  `log_timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auditlogs`
--

INSERT INTO `auditlogs` (`log_id`, `user_id`, `action`, `log_timestamp`) VALUES
(1, 1051, 'Logged in', '2025-11-08 12:57:45'),
(2, 1, 'Logged in', '2025-11-08 13:09:03'),
(3, 1, 'Logged in', '2025-11-08 13:11:07'),
(4, 1, 'Logged in', '2025-11-08 13:13:53'),
(5, 1, 'Deleted candidate 1001', '2025-11-08 13:21:34'),
(6, 1051, 'Logged in', '2025-11-08 13:26:09'),
(7, 1051, 'Logged in', '2025-11-08 13:28:23'),
(8, 1051, 'Logged in', '2025-11-08 13:28:43'),
(9, 1, 'Logged in', '2025-11-08 13:30:00'),
(10, 1051, 'Logged in', '2025-11-08 13:35:56'),
(11, 1, 'Logged in', '2025-11-08 13:45:42'),
(12, 1051, 'Logged in', '2025-11-08 13:48:03'),
(13, 1051, 'Logged in', '2025-11-08 17:44:54'),
(14, 1051, 'Logged in', '2025-11-08 18:04:05'),
(15, 1051, 'Logged in', '2025-11-09 14:02:06'),
(16, 1051, 'Logged in', '2025-11-11 08:32:14'),
(17, 1051, 'Logged in', '2025-11-15 15:51:58'),
(18, 1051, 'Logged in', '2025-11-15 15:53:59'),
(19, 1051, 'Logged in', '2025-11-15 16:35:46'),
(20, 1051, 'Logged in', '2025-11-16 09:36:16'),
(21, 1051, 'Logged in', '2025-11-17 09:32:04'),
(22, 1, 'Logged in', '2025-11-17 09:32:44'),
(23, 1, 'Logged in', '2025-11-17 09:33:53'),
(24, 1, 'Logged in', '2025-11-17 09:35:07'),
(25, 1, 'Logged in', '2025-11-17 09:40:15'),
(26, 1, 'Logged in', '2025-11-18 02:41:47'),
(27, 1, 'Added schedule: VIEW_CANDIDATES from November 18, 2025 to November 30, 2025', '2025-11-18 03:01:06'),
(28, 1, 'Exported ballot list PDF - File: ballots_1_2025-11-18_03-06-02.pdf', '2025-11-18 03:06:02'),
(29, 1, 'Exported PDF results - File: results_1_2025-11-18_03-11-51.pdf', '2025-11-18 03:11:52'),
(30, 1, 'Exported PDF results - File: results_1_2025-11-18_03-14-56.pdf', '2025-11-18 03:14:56'),
(31, 1, 'Saved admin note', '2025-11-18 03:22:28'),
(32, 1, 'Saved admin note', '2025-11-18 03:22:30'),
(33, 1, 'Saved admin note', '2025-11-18 03:22:48'),
(34, 1, 'Updated candidate id 17', '2025-11-18 03:24:37'),
(35, 1051, 'Logged in', '2025-11-18 03:24:57'),
(36, 1051, 'Logged in', '2025-11-18 03:26:07'),
(37, 1, 'Logged in', '2025-11-18 03:28:28'),
(38, 1, 'Logged in', '2025-11-18 04:28:37'),
(39, 1, 'Logged in', '2025-11-18 14:32:33'),
(40, 1016, 'Logged in', '2025-11-18 14:44:01'),
(41, 1016, 'Logged in', '2025-11-18 14:44:25'),
(42, 1032, 'Logged in', '2025-11-19 01:59:14'),
(43, 1011, 'Logged in', '2025-11-19 02:00:11'),
(44, 1011, 'Logged in', '2025-11-19 02:01:09'),
(45, 1011, 'Logged in', '2025-11-19 02:01:43'),
(46, 1011, 'Logged in', '2025-11-19 02:02:23'),
(47, 1016, 'Logged in', '2025-11-19 02:04:18'),
(48, 1016, 'Logged in', '2025-11-19 02:06:29'),
(49, 1051, 'Logged in', '2025-11-19 02:06:47'),
(50, 1051, 'Logged in', '2025-11-19 02:07:22'),
(51, 1016, 'Logged in', '2025-11-19 02:07:54'),
(52, 1049, 'Logged in', '2025-11-19 02:16:06'),
(53, 1051, 'Logged in', '2025-11-19 02:16:33'),
(54, 1014, 'Logged in', '2025-11-19 02:17:13'),
(55, 1028, 'Logged in', '2025-11-19 02:19:24'),
(56, 1019, 'Logged in', '2025-11-19 02:20:36'),
(57, 1019, 'Logged in', '2025-11-19 02:21:18'),
(58, 1047, 'Logged in', '2025-11-19 02:22:13'),
(59, 1023, 'Logged in', '2025-11-19 02:22:45'),
(60, 1042, 'Logged in', '2025-11-19 02:23:24'),
(61, 1022, 'Logged in', '2025-11-19 02:23:54'),
(62, 1034, 'Logged in', '2025-11-19 02:24:29'),
(63, 1018, 'Logged in', '2025-11-19 02:25:17'),
(64, 1005, 'Logged in', '2025-11-19 02:25:48'),
(65, 1026, 'Logged in', '2025-11-19 02:26:21'),
(66, 1020, 'Logged in', '2025-11-19 02:27:05'),
(67, 1001, 'Logged in', '2025-11-19 02:27:35'),
(68, 1001, 'Logged in', '2025-11-19 02:27:54'),
(69, 1016, 'Logged in', '2025-11-19 02:28:41'),
(70, 1, 'Logged in', '2025-11-19 02:35:36'),
(71, 1016, 'Logged in', '2025-11-19 02:36:03'),
(72, 1, 'Exported ballot list PDF - File: ballots_1_2025-11-19_02-36-15.pdf', '2025-11-19 02:36:15'),
(73, 1, 'Exported ballot list PDF - File: ballots_1_2025-11-19_02-36-29.pdf', '2025-11-19 02:36:29'),
(74, 1051, 'Logged in', '2025-11-19 02:45:23'),
(75, 1016, 'Logged in', '2025-11-19 02:45:46'),
(76, 1051, 'Logged in', '2025-11-19 02:46:07'),
(77, 1016, 'Logged in', '2025-11-19 02:46:27'),
(78, 1, 'Logged in', '2025-11-19 02:47:28'),
(79, 1, 'Exported PDF results - File: results_1_2025-11-19_03-03-08.pdf', '2025-11-19 03:03:08'),
(80, 1, 'Exported ballot list PDF - File: ballots_1_2025-11-19_03-03-31.pdf', '2025-11-19 03:03:31'),
(81, 1, 'Exported ballot list PDF - File: ballots_1_2025-11-19_03-05-10.pdf', '2025-11-19 03:05:10'),
(82, 1, 'Exported ballot list PDF - File: ballots_1_2025-11-19_03-05-34.pdf', '2025-11-19 03:05:34'),
(83, 1, 'Logged in', '2025-11-19 03:09:21'),
(84, 1051, 'Logged in', '2025-11-19 03:09:52'),
(85, 1016, 'Logged in', '2025-11-19 03:15:23'),
(86, 1, 'Logged in', '2025-11-19 03:16:29'),
(87, 1, 'Logged in', '2025-11-19 03:31:37'),
(88, 1051, 'Logged in', '2025-11-19 03:38:32'),
(89, 1051, 'Logged in', '2025-11-19 03:41:26'),
(90, 1051, 'Logged in', '2025-11-19 03:46:13'),
(91, 1051, 'Logged in', '2025-11-19 03:47:29'),
(92, 1051, 'Logged in', '2025-11-19 03:48:19'),
(93, 1051, 'Logged in', '2025-11-19 03:48:41'),
(94, 1051, 'Logged in', '2025-11-19 03:49:22'),
(95, 1051, 'Logged in', '2025-11-19 03:54:03'),
(96, 1051, 'Logged in', '2025-11-19 03:54:21'),
(97, 1051, 'Logged in', '2025-11-19 03:56:08'),
(98, 1051, 'Logged in', '2025-11-19 03:59:19'),
(99, 1051, 'Logged in', '2025-11-19 03:59:34'),
(100, 1051, 'Logged in', '2025-11-19 04:01:32'),
(101, 1051, 'Logged in', '2025-11-19 04:02:00'),
(102, 1051, 'Logged in', '2025-11-19 04:03:00'),
(103, 1051, 'Logged in', '2025-11-19 04:03:21'),
(104, 1051, 'Logged in', '2025-11-19 04:03:37'),
(105, 1051, 'Logged in', '2025-11-19 04:06:39'),
(106, 1051, 'Logged in', '2025-11-19 04:07:47'),
(107, 1051, 'Logged in', '2025-11-19 04:08:44'),
(108, 1051, 'Logged in', '2025-11-19 04:09:23'),
(109, 1051, 'Logged in', '2025-11-19 04:09:46'),
(110, 1051, 'Logged in', '2025-11-19 04:11:24'),
(111, 1051, 'Logged in', '2025-11-19 04:13:00'),
(112, 1051, 'Logged in', '2025-11-19 04:13:17'),
(113, 1051, 'Logged in', '2025-11-19 04:14:54'),
(114, 1051, 'Logged in', '2025-11-19 04:15:07'),
(115, 1051, 'Logged in', '2025-11-19 04:16:17'),
(116, 1051, 'Logged in', '2025-11-19 04:16:42'),
(117, 1051, 'Logged in', '2025-11-19 04:19:05'),
(118, 1051, 'Logged in', '2025-11-19 04:19:18'),
(119, 1051, 'Logged in', '2025-11-19 04:22:01'),
(120, 1051, 'Logged in', '2025-11-19 04:22:19'),
(121, 1051, 'Logged in', '2025-11-19 04:25:25'),
(122, 1051, 'Logged in', '2025-11-19 04:25:39'),
(123, 1051, 'Logged in', '2025-11-19 04:27:07'),
(124, 1051, 'Logged in', '2025-11-19 04:27:24'),
(125, 1051, 'Logged in', '2025-11-19 04:28:25'),
(126, 1051, 'Logged in', '2025-11-19 04:28:38'),
(127, 1051, 'Logged in', '2025-11-19 04:28:53'),
(128, 1051, 'Logged in', '2025-11-19 04:30:04'),
(129, 1051, 'Logged in', '2025-11-19 04:30:22'),
(130, 1051, 'Logged in', '2025-11-19 04:31:44'),
(131, 1051, 'Logged in', '2025-11-19 04:31:58'),
(132, 1051, 'Logged in', '2025-11-19 04:32:38'),
(133, 1051, 'Logged in', '2025-11-19 04:33:03'),
(134, 1051, 'Logged in', '2025-11-19 04:33:11'),
(135, 1051, 'Logged in', '2025-11-19 04:34:51'),
(136, 1051, 'Logged in', '2025-11-19 04:35:52'),
(137, 1051, 'Logged in', '2025-11-19 04:38:08'),
(138, 1051, 'Logged in', '2025-11-19 04:38:23'),
(139, 1051, 'Logged in', '2025-11-19 04:38:41'),
(140, 1051, 'Logged in', '2025-11-19 04:38:50'),
(141, 1, 'Logged in', '2025-11-19 04:41:43'),
(142, 1051, 'Logged in', '2025-11-19 04:47:05'),
(143, 1, 'Logged in', '2025-11-19 04:51:46'),
(144, 1, 'Exported PDF results - File: results_1_2025-11-19_04-52-12.pdf', '2025-11-19 04:52:12'),
(145, 1, 'Exported ballot list PDF - File: ballots_1_2025-11-19_04-52-16.pdf', '2025-11-19 04:52:16'),
(146, 1051, 'Logged in', '2025-11-19 04:59:52'),
(147, 1051, 'Logged in', '2025-11-19 06:21:03'),
(148, 1, 'Logged in', '2025-11-19 06:22:19'),
(149, 1, 'Added schedule: VOTING from November 19, 2025 to November 27, 2025', '2025-11-19 06:23:06'),
(150, 1051, 'Logged in', '2025-11-19 06:28:03'),
(151, 1, 'Logged in', '2025-11-19 06:46:18'),
(152, 1, 'Logged in', '2025-11-19 06:49:43'),
(153, 1051, 'Logged in', '2025-11-19 06:51:27'),
(154, 1051, 'Logged in', '2025-11-19 06:51:36'),
(155, 1, 'Logged in', '2025-11-19 06:52:44'),
(156, 1, 'Deleted voting schedule', '2025-11-19 06:56:27'),
(157, 1, 'Added schedule: VIEW_CANDIDATES from November 19, 2025 to November 27, 2025', '2025-11-19 06:56:37'),
(158, 1051, 'Logged in', '2025-11-19 06:56:50'),
(159, 1051, 'Logged in', '2025-11-19 07:01:09'),
(160, 1, 'Logged in', '2025-11-19 07:02:00'),
(161, 1, 'Deleted candidate viewing schedule', '2025-11-19 07:02:09'),
(162, 1, 'Added schedule: VOTING from November 19, 2025 to November 27, 2025', '2025-11-19 07:02:16'),
(163, 1051, 'Logged in', '2025-11-19 07:02:23'),
(164, 1, 'Logged in', '2025-11-19 07:02:46'),
(165, 1, 'Deleted voting schedule', '2025-11-19 07:03:01'),
(166, 1, 'Added schedule: VIEW_CANDIDATES from November 19, 2025 to November 27, 2025', '2025-11-19 07:03:08'),
(167, 1051, 'Logged in', '2025-11-19 07:03:18'),
(168, 1, 'Logged in', '2025-11-19 07:21:30'),
(169, 1, 'Deleted candidate viewing schedule', '2025-11-19 07:21:46'),
(170, 1, 'Added schedule: VOTING from November 19, 2025 to November 27, 2025', '2025-11-19 07:21:52'),
(171, 1051, 'Logged in', '2025-11-19 07:22:00'),
(172, 1, 'Logged in', '2025-11-19 07:27:07'),
(173, 1, 'Deleted voting schedule', '2025-11-19 07:35:32'),
(174, 1, 'Added schedule: VIEW_CANDIDATES from November 19, 2025 to November 27, 2025', '2025-11-19 07:35:39'),
(175, 1051, 'Logged in', '2025-11-19 07:35:49'),
(176, 1, 'Logged in', '2025-11-19 07:38:56'),
(177, 1051, 'Logged in', '2025-11-19 07:40:11'),
(178, 1, 'Deleted candidate viewing schedule', '2025-11-19 07:47:22'),
(179, 1, 'Added schedule: VIEW_CANDIDATES from November 19, 2025 to November 27, 2025', '2025-11-19 07:47:35'),
(180, 1, 'Deleted candidate viewing schedule', '2025-11-19 07:49:03'),
(181, 1, 'Added schedule: VIEW_CANDIDATES from November 19, 2025 to November 27, 2025', '2025-11-19 07:49:11'),
(182, 1, 'Deleted candidate viewing schedule', '2025-11-19 07:53:25'),
(183, 1, 'Added schedule: CANDIDATE_CHECKING from November 19, 2025 to November 27, 2025', '2025-11-19 07:53:33'),
(184, 1001, 'Logged in', '2025-11-19 07:55:15'),
(185, 1001, 'Logged in', '2025-11-19 07:56:14'),
(186, 1, 'Logged in', '2025-11-19 07:56:39'),
(187, 1, 'Deleted candidate viewing schedule', '2025-11-19 07:56:49'),
(188, 1, 'Added schedule: VIEW_CANDIDATES from November 19, 2025 to November 27, 2025', '2025-11-19 07:56:55'),
(189, 1051, 'Logged in', '2025-11-19 07:57:02'),
(190, 1, 'Logged in', '2025-11-19 07:57:29'),
(191, 1, 'Deleted candidate viewing schedule', '2025-11-19 07:57:39'),
(192, 1, 'Added schedule: CANDIDATE_CHECKING from November 19, 2025 to November 27, 2025', '2025-11-19 08:00:20'),
(193, 1, 'Deleted candidate viewing schedule', '2025-11-19 08:00:45'),
(194, 1, 'Added schedule: VIEW_CANDIDATES from November 19, 2025 to November 27, 2025', '2025-11-19 08:00:55'),
(195, 1, 'Deleted candidate viewing schedule', '2025-11-19 08:00:57'),
(196, 1, 'Added schedule: CANDIDATE_CHECKING from November 19, 2025 to November 27, 2025', '2025-11-19 08:01:02'),
(197, 1, 'Deleted candidate viewing schedule', '2025-11-19 08:01:12'),
(198, 1, 'Added schedule: VOTING from November 19, 2025 to November 27, 2025', '2025-11-19 08:01:18'),
(199, 1051, 'Logged in', '2025-11-19 08:01:24'),
(200, 1, 'Logged in', '2025-11-19 08:02:14'),
(201, 1001, 'Logged in', '2025-11-19 08:03:24'),
(202, 1, 'Logged in', '2025-11-19 08:05:18'),
(203, 1051, 'Logged in', '2025-11-19 08:07:28'),
(204, 1001, 'Logged in', '2025-11-19 08:09:06'),
(205, 1051, 'Logged in', '2025-11-19 08:12:50'),
(206, 1023, 'Logged in', '2025-11-19 10:41:25'),
(207, 1, 'Logged in', '2025-11-19 11:26:43'),
(208, 1, 'Exported PDF results - File: results_1_2025-11-19_11-27-32.pdf', '2025-11-19 11:27:32'),
(209, 1, 'Exported ballot list PDF - File: ballots_1_2025-11-19_11-28-09.pdf', '2025-11-19 11:28:09'),
(214, 1, 'Logged in', '2025-11-19 14:00:35'),
(215, 1, 'Exported PDF results - File: results_1_2025-11-19_14-02-22.pdf', '2025-11-19 14:02:22'),
(216, 1, 'Updated candidate id 18', '2025-11-19 14:10:15'),
(217, 1, 'Logged in', '2025-11-20 01:27:26'),
(218, 1, 'Added schedule: VIEW_CANDIDATES from November 20, 2025 to November 25, 2025', '2025-11-20 01:27:56'),
(219, 1, 'Added schedule: VOTING from November 20, 2025 to November 25, 2025', '2025-11-20 01:28:08'),
(220, 1005, 'Logged in', '2025-11-20 01:28:42'),
(221, 1, 'Logged in', '2025-11-20 01:29:41'),
(222, 1, 'Updated candidate id 18', '2025-11-20 01:31:57'),
(223, 1, 'Updated candidate id 19', '2025-11-20 01:33:03'),
(224, 1, 'Updated candidate id 21', '2025-11-20 01:33:43'),
(225, 1, 'Updated candidate id 20', '2025-11-20 01:34:35'),
(226, 1, 'Updated candidate id 22', '2025-11-20 01:35:07'),
(227, 1, 'Updated candidate id 23', '2025-11-20 01:35:38'),
(228, 1, 'Updated candidate id 25', '2025-11-20 01:36:35'),
(229, 1, 'Updated candidate id 24', '2025-11-20 01:37:05'),
(230, 1, 'Updated candidate id 28', '2025-11-20 01:38:02'),
(231, 1, 'Updated candidate id 29', '2025-11-20 01:38:49'),
(232, 1, 'Updated candidate id 26', '2025-11-20 01:40:09'),
(233, 1, 'Updated candidate id 31', '2025-11-20 01:41:10'),
(234, 1, 'Updated candidate id 27', '2025-11-20 01:41:44'),
(235, 1, 'Updated candidate id 30', '2025-11-20 01:42:12'),
(236, 1, 'Updated candidate id 33', '2025-11-20 01:42:42'),
(237, 1001, 'Logged in', '2025-11-20 01:45:15'),
(238, 1, 'Logged in', '2025-11-20 01:47:46'),
(239, 1005, 'Logged in', '2025-11-20 01:48:38'),
(240, 1, 'Updated candidate id 21', '2025-11-20 01:49:05'),
(241, 1, 'Updated candidate id 23', '2025-11-20 01:49:21'),
(242, 1, 'Updated candidate id 20', '2025-11-20 01:49:54'),
(243, 1005, 'Logged in', '2025-11-20 01:50:07'),
(244, 1, 'Updated candidate id 21', '2025-11-20 01:51:05'),
(245, 1, 'Updated candidate id 22', '2025-11-20 01:52:10'),
(246, 1, 'Updated candidate id 26', '2025-11-20 01:52:46'),
(247, 1, 'Updated candidate id 30', '2025-11-20 01:53:32'),
(248, 1, 'Updated candidate id 33', '2025-11-20 01:53:56'),
(249, 1, 'Updated candidate id 32', '2025-11-20 01:54:10'),
(250, 1, 'Deleted voting schedule', '2025-11-20 01:55:22'),
(251, 1, 'Added schedule: VOTING from November 25, 2025 to November 26, 2025', '2025-11-20 01:55:36'),
(252, 1, 'Deleted voting schedule', '2025-11-20 01:55:42'),
(253, 1, 'Deleted candidate viewing schedule', '2025-11-20 01:55:45'),
(254, 1, 'Added schedule: VOTING from November 20, 2025 to November 25, 2025', '2025-11-20 01:55:54'),
(255, 1005, 'Logged in', '2025-11-20 01:56:15'),
(256, 1, 'Logged in', '2025-11-20 01:57:01'),
(257, 1, 'Deleted voting schedule', '2025-11-20 01:57:28'),
(258, 1, 'Added schedule: VIEW_CANDIDATES from November 20, 2025 to November 25, 2025', '2025-11-20 01:57:38'),
(259, 1, 'Added schedule: VOTING from November 26, 2025 to November 27, 2025', '2025-11-20 01:57:49'),
(260, 1005, 'Logged in', '2025-11-20 01:58:22'),
(261, 1, 'Logged in', '2025-11-20 02:00:08'),
(262, 1, 'Updated candidate id 18', '2025-11-20 02:00:54'),
(263, 1, 'Updated candidate id 19', '2025-11-20 02:01:07'),
(264, 1, 'Updated candidate id 21', '2025-11-20 02:01:17'),
(265, 1, 'Updated candidate id 21', '2025-11-20 02:01:30'),
(266, 1, 'Updated candidate id 18', '2025-11-20 02:01:38'),
(267, 1, 'Updated candidate id 19', '2025-11-20 02:01:52'),
(268, 1, 'Updated candidate id 21', '2025-11-20 02:01:59'),
(269, 1, 'Updated candidate id 20', '2025-11-20 02:02:15'),
(270, 1, 'Updated candidate id 22', '2025-11-20 02:02:30'),
(271, 1, 'Updated candidate id 23', '2025-11-20 02:02:44'),
(272, 1, 'Updated candidate id 25', '2025-11-20 02:02:55'),
(273, 1, 'Updated candidate id 25', '2025-11-20 02:03:06'),
(274, 1, 'Updated candidate id 24', '2025-11-20 02:03:21'),
(275, 1, 'Updated candidate id 28', '2025-11-20 02:03:35'),
(276, 1, 'Updated candidate id 24', '2025-11-20 02:03:43'),
(277, 1, 'Updated candidate id 29', '2025-11-20 02:03:58'),
(278, 1, 'Updated candidate id 28', '2025-11-20 02:04:05'),
(279, 1, 'Updated candidate id 29', '2025-11-20 02:04:10'),
(280, 1, 'Updated candidate id 31', '2025-11-20 02:04:31'),
(281, 1, 'Updated candidate id 30', '2025-11-20 02:04:51'),
(282, 1, 'Updated candidate id 33', '2025-11-20 02:05:05'),
(283, 1, 'Updated candidate id 32', '2025-11-20 02:05:17'),
(284, 1, 'Deleted candidate viewing schedule', '2025-11-20 02:05:23'),
(285, 1, 'Deleted voting schedule', '2025-11-20 02:05:25'),
(286, 1, 'Added schedule: VOTING from November 20, 2025 to November 25, 2025', '2025-11-20 02:05:36'),
(287, 1, 'Logged in', '2025-11-20 02:32:14'),
(288, 1051, 'Logged in', '2025-11-20 02:33:32'),
(289, 1001, 'Logged in', '2025-11-20 03:01:48'),
(290, 1049, 'Logged in', '2025-11-20 03:05:21'),
(291, 1032, 'Logged in', '2025-11-20 03:05:31'),
(292, 1018, 'Logged in', '2025-11-20 03:05:49'),
(293, 1011, 'Logged in', '2025-11-20 03:18:07'),
(294, 1011, 'Logged in', '2025-11-20 03:23:01'),
(295, 1042, 'Logged in', '2025-11-20 03:23:19'),
(296, 1016, 'Logged in', '2025-11-20 03:35:04'),
(297, 1047, 'Logged in', '2025-11-20 03:59:24'),
(298, 1047, 'Logged in', '2025-11-20 04:01:51'),
(299, 1028, 'Logged in', '2025-11-20 08:40:46'),
(300, 1028, 'Logged in', '2025-11-20 08:41:43'),
(301, 1028, 'Logged in', '2025-11-20 08:47:49'),
(302, 1, 'Logged in', '2025-11-20 10:27:49'),
(303, 1001, 'Logged in', '2025-11-20 10:34:24'),
(304, 1001, 'Logged in', '2025-11-20 10:35:29'),
(305, 1, 'Logged in', '2025-11-20 11:18:51'),
(306, 1, 'Deleted candidate 1051', '2025-11-20 12:11:27'),
(307, 1, 'Deleted candidate 1051', '2025-11-20 12:25:58'),
(308, 1, 'Exported ballot list PDF - File: ballots_1_2025-11-20_12-37-38.pdf', '2025-11-20 12:37:38'),
(309, 1, 'Exported PDF results - File: results_1_2025-11-20_12-37-41.pdf', '2025-11-20 12:37:41'),
(310, 1, 'Exported PDF results - File: results_1_2025-11-20_12-37-50.pdf', '2025-11-20 12:37:50'),
(311, 1, 'Exported ballot list PDF - File: ballots_1_2025-11-20_12-38-03.pdf', '2025-11-20 12:38:03'),
(312, 1, 'SYSTEM_RESET', '2025-11-20 12:38:52'),
(313, 1, 'Added schedule: VOTING from November 20, 2025 to November 27, 2025', '2025-11-20 12:40:43'),
(314, 1022, 'Logged in', '2025-11-20 13:57:12'),
(315, 1020, 'Logged in', '2025-11-21 00:38:56'),
(316, 1, 'Logged in', '2025-11-21 09:44:29'),
(317, 1, 'Saved admin note', '2025-11-21 09:47:35'),
(318, 1, 'Exported ballot list PDF - File: ballots_1_2025-11-21_09-54-42.pdf', '2025-11-21 09:54:43'),
(319, 1001, 'Logged in', '2025-11-22 11:24:09'),
(320, 1020, 'Logged in', '2025-11-22 11:37:29'),
(321, 1020, 'Logged in', '2025-11-22 11:39:10'),
(322, 1, 'Logged in', '2025-11-22 12:07:54'),
(323, 1, 'Logged in', '2025-11-22 12:09:20'),
(326, 1, 'Logged in', '2025-11-23 05:11:39'),
(327, 1, 'Saved admin note', '2025-11-23 05:19:03'),
(328, 1, 'Saved admin note', '2025-11-23 05:19:10'),
(329, 1, 'Saved admin note', '2025-11-23 05:19:22'),
(330, 1, 'Saved admin note', '2025-11-23 05:19:25'),
(331, 1, 'Saved admin note', '2025-11-23 05:19:27'),
(332, 1, 'Saved admin note', '2025-11-23 05:19:28'),
(333, 1, 'Saved admin note', '2025-11-23 05:19:30'),
(334, 1, 'Saved admin note', '2025-11-23 05:19:38'),
(335, 1026, 'Logged in', '2025-11-23 05:26:30'),
(336, 1, 'Logged in', '2025-11-23 05:27:37'),
(337, 1, 'Logged in', '2025-11-23 05:27:48'),
(338, 1026, 'Logged in', '2025-11-23 05:30:51'),
(339, 1, 'Logged in', '2025-11-23 05:31:00'),
(340, 1, 'Logged in', '2025-11-23 05:34:59'),
(341, 1026, 'Logged in', '2025-11-23 05:46:44'),
(342, 1, 'Logged in', '2025-11-23 06:07:29'),
(343, 1, 'Saved admin note', '2025-11-23 06:08:49'),
(344, 1, 'Saved admin note', '2025-11-23 06:08:53'),
(345, 1, 'Saved admin note', '2025-11-23 06:08:56'),
(346, 1, 'Saved admin note', '2025-11-23 06:08:57'),
(347, 1, 'Saved admin note', '2025-11-23 06:09:02'),
(348, 1, 'Updated candidate id 34', '2025-11-23 06:17:19'),
(349, 1, 'Deleted candidate 1034', '2025-11-23 06:17:44'),
(350, 1, 'Exported PDF results - File: results_1_2025-11-23_06-21-26.pdf', '2025-11-23 06:21:26'),
(351, 1, 'Deleted voting schedule', '2025-11-23 06:27:57'),
(352, 1, 'Added schedule: VIEW_CANDIDATES from November 23, 2025 to November 30, 2025', '2025-11-23 06:28:42'),
(353, 1, 'Exported ballot list PDF - File: ballots_1_2025-11-23_06-31-49.pdf', '2025-11-23 06:31:49'),
(354, 1, 'Logged in', '2025-11-23 06:39:51'),
(355, 1, 'Logged in', '2025-11-23 07:10:29'),
(356, 1, 'Logged in', '2025-11-23 07:48:28'),
(357, 1051, 'Logged in', '2025-11-23 07:49:00'),
(358, 1, 'Logged in', '2025-11-23 07:50:08'),
(359, 1, 'Logged in', '2025-11-23 07:51:44'),
(360, 1, 'Logged in', '2025-11-23 07:56:48'),
(361, 1, 'Logged in', '2025-11-23 08:00:13'),
(362, 1, 'Logged in', '2025-11-23 08:01:54'),
(363, 1, 'Logged in', '2025-11-23 08:30:14'),
(364, 1, 'Logged in', '2025-11-23 08:46:35'),
(365, 1, 'Logged in', '2025-11-23 08:47:26'),
(366, 1, 'Logged in', '2025-11-23 08:50:09'),
(367, 1051, 'Logged in', '2025-11-23 09:08:31'),
(368, 1, 'Logged in', '2025-11-23 09:13:06'),
(370, 1, 'Logged in', '2025-11-23 09:16:12'),
(371, 1051, 'Logged in', '2025-11-23 09:27:22'),
(372, 1, 'Logged in', '2025-11-23 09:27:53'),
(373, 1, 'Logged in', '2025-11-23 09:30:08'),
(374, 1, 'Logged in', '2025-11-23 09:30:44'),
(375, 1, 'Logged in', '2025-11-23 09:41:20'),
(376, 1, 'Updated candidate id 36', '2025-11-23 09:42:55'),
(377, 1, 'Logged in', '2025-11-23 10:37:22'),
(378, 1, 'Logged in', '2025-11-23 13:11:39'),
(379, 1, 'Logged in', '2025-11-23 14:08:06'),
(380, 1026, 'Logged in', '2025-11-24 01:20:00'),
(381, 1, 'Logged in', '2025-11-24 01:23:56'),
(382, 1, 'Logged in', '2025-11-24 06:25:52'),
(383, 1, 'Exported PDF results - File: results_1_2025-11-24_06-35-32.pdf', '2025-11-24 06:35:32'),
(384, 1, 'Added schedule: VOTING from December 01, 2025 to December 06, 2025', '2025-11-24 06:38:50'),
(385, 1, 'Exported ballot list PDF - File: ballots_1_2025-11-24_06-41-45.pdf', '2025-11-24 06:41:45'),
(386, 1, 'Logged in', '2025-11-24 06:51:25'),
(387, 1, 'Logged in', '2025-11-25 09:46:16'),
(388, 1, 'Logged in', '2025-11-25 09:48:28'),
(389, 1, 'Deleted candidate 1034', '2025-11-25 09:48:56'),
(390, 1, 'Added schedule: CANDIDATE_CHECKING from November 30, 2025 to November 30, 2025', '2025-11-25 09:49:19'),
(391, 1, 'Deleted candidate viewing schedule', '2025-11-25 09:49:24'),
(392, 1, 'Exported ballot list PDF - File: ballots_1_2025-11-25_09-49-28.pdf', '2025-11-25 09:49:28'),
(393, 1, 'Exported PDF results - File: results_1_2025-11-25_09-49-32.pdf', '2025-11-25 09:49:32'),
(394, 1, 'Logged in', '2025-11-25 14:00:07'),
(395, 1, 'Logged in', '2025-11-25 14:34:29'),
(396, 1, 'Logged in', '2025-11-25 14:38:09'),
(397, 1, 'Logged in', '2025-11-26 00:05:00'),
(398, 1, 'Updated candidate id 18', '2025-11-26 00:08:16'),
(399, 1, 'Saved admin note', '2025-11-26 00:15:21'),
(400, 1, 'Logged in', '2025-11-26 01:12:36'),
(401, 1, 'Logged in', '2025-11-26 01:21:25'),
(402, 1, 'Logged in', '2025-11-26 04:55:07'),
(403, 1001, 'Logged in', '2025-11-26 04:58:46'),
(404, 1005, 'Logged in', '2025-11-26 05:00:23'),
(405, 1, 'Logged in', '2025-11-26 12:45:47'),
(406, 1, 'Logged in', '2025-11-26 12:46:24'),
(407, 1, 'Updated candidate id 18', '2025-11-26 12:49:17'),
(408, 1019, 'Logged in', '2025-11-26 12:58:16'),
(409, 1, 'Logged in', '2025-11-26 13:00:09'),
(410, 1, 'Deleted voting schedule', '2025-11-26 13:00:58'),
(411, 1, 'Deleted candidate viewing schedule', '2025-11-26 13:01:01'),
(412, 1, 'Added schedule: VOTING from November 26, 2025 to December 01, 2025', '2025-11-26 13:01:13'),
(414, 1, 'Logged in', '2025-11-26 13:06:43'),
(415, 1, 'Logged in', '2025-11-26 13:08:48'),
(416, 1, 'Added schedule: VOTING from November 26, 2025 to November 27, 2025', '2025-11-26 13:09:14'),
(417, 1051, 'Logged in', '2025-11-26 13:09:36'),
(418, 1, 'Updated candidate id 18', '2025-11-26 13:10:34'),
(419, 1001, 'Logged in', '2025-11-26 14:07:08'),
(420, 1, 'Logged in', '2025-11-26 14:24:22'),
(421, 1, 'Logged in', '2025-11-27 03:33:31'),
(422, 1051, 'Logged in', '2025-11-27 03:37:44'),
(423, 1, 'Deleted candidate 1040', '2025-11-27 03:38:08'),
(424, 1, 'Logged in', '2025-11-27 04:15:09');

-- --------------------------------------------------------

--
-- Table structure for table `ballots`
--

CREATE TABLE `ballots` (
  `ballot_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ballots`
--

INSERT INTO `ballots` (`ballot_id`, `user_id`) VALUES
(6, 1001),
(12, 1011),
(14, 1016),
(9, 1018),
(17, 1022),
(18, 1026),
(16, 1028),
(7, 1032),
(11, 1042),
(15, 1047),
(8, 1049);

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `candidate_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `platforms` text DEFAULT NULL,
  `credentials` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`candidate_id`, `user_id`, `position_id`, `platforms`, `credentials`, `profile_picture`) VALUES
(18, 1022, 2, 'Strengthen transparency by publishing monthly council updates.\r\n Improve campus facilities through continuous coordination with admin offices.\r\n Promote inclusive student participation in university events.', 'Former Class Representative \r\n Volunteer, UMak Help Desk', 'uploads/candidates/cand_691e6f8dad659.png'),
(19, 1001, 1, 'Enhance communication channels between students and the council.\r\nIntroduce digital feedback forms for faster issue resolution.\r\nSupport academic assistance programs like peer tutoring.', 'Vice President, Student Organization \r\n Dean’s Lister (2 semesters)', 'uploads/candidates/cand_691e6fcf1895b.png'),
(20, 1006, 2, 'Expand mental health awareness campaigns.\r\nPartner with external organizations for student seminars and workshops.\r\nIncrease availability of safe spaces for student concerns.', 'Psychology Society Officer \r\nEvent Organizer, UMak Wellness Week', 'uploads/candidates/cand_691e73c2e1b2e.png'),
(21, 1002, 2, 'Improve campus cleanliness and waste management practices.\r\nImplement eco-friendly initiatives and green challenges.\r\nStrengthen collaboration with environmental student groups.', 'Member, Eco-Warriors Club \r\nVolunteer, Clean-Up Drive 2024', 'uploads/candidates/cand_691e74092c9fb.png'),
(22, 1010, 3, 'Provide more scholarship info sessions and financial aid guidance.\r\nEstablish a support desk for student academic concerns.\r\nAdvocate for fair and updated student policies.', 'Former Student Assistant \r\nScholar, City of Makati Grant', 'uploads/candidates/cand_691e744aba65c.png'),
(23, 1009, 3, 'Upgrade the student helpdesk to handle concerns quickly.\r\nImprove online systems to reduce long queues in offices.\r\nIntroduce digital kiosks for information dissemination.', 'IT Society Member \r\nAssistant Developer, Campus Projects', 'uploads/candidates/cand_691e73a173e5b.png'),
(24, 1021, 4, 'Boost participation in sports and wellness activities.\r\nSupport athletes with proper recognition and incentives.\r\nOrganize inter-course sports festivals.', 'Varsity Athlete \r\nSports Committee Volunteer', 'uploads/candidates/cand_691e70c1a4c04.png'),
(25, 1025, 4, 'Strengthen cultural and arts programs.\r\nProvide more opportunities for talented students to showcase skills.\r\nSupport production-based student organizations.', 'Member, UMak Performing Arts \r\nScriptwriter, Campus Filmfest', 'uploads/candidates/cand_691e70a3d94b7.png'),
(26, 1005, 6, '- Advocate for safer campus spaces and stricter implementation of policies.\r\n- Work with security teams to improve safety reports.\r\n- Install more lighting and emergency help points.', '• NSTP Corps Leader \r\n• Trained in Basic Safety Response', 'uploads/candidates/cand_691e746e270e6.png'),
(27, 1013, 6, '- Promote entrepreneurship and business-related activities.\r\n- Organize seminars on financial literacy.\r\n- Support student-owned small businesses.', '• Junior Marketing Association Officer \r\n• Business Plan Competition Finalist', 'uploads/candidates/cand_691e71d89c2ff.png'),
(28, 1029, 5, 'Improve coordination between student organizations.\r\nCreate a centralized events calendar.\r\nSupport organization development training.', 'Former Organization Secretary \r\nProject Head, Orientation Events', 'uploads/candidates/cand_691e70fa7b525.png'),
(29, 1027, 5, 'Push for better academic resources in libraries and labs.\r\nRequest updated equipment for specialized courses.\r\nWork closely with college departments for student needs.', 'Library Assistant Volunteer \r\nAcademic Achiever Awardee', 'uploads/candidates/cand_691e712953226.png'),
(30, 1012, 7, 'Strengthen community extension programs.\r\n Encourage students to join outreach and charity activities.\r\n Build partnerships with local NGOs.', 'Outreach Program Coordinator \r\nVolunteer, Feeding Programs', 'uploads/candidates/cand_691e749c8cbac.png'),
(31, 1015, 7, 'Make student council decisions more inclusive through surveys.\r\nProvide more open forums for student concerns.\r\nConduct town-hall style dialogues with administrators.', 'Public Speaking Club Member \r\nFacilitator, Leadership Bootcamp', 'uploads/candidates/cand_691e71b653007.png'),
(32, 1026, 8, 'Improve dormitory and off-campus student support.\r\n Create a guidebook for new students (freshman handbook).\r\n Support commuter-friendly initiatives.', 'Peer Mentor \r\n UMak Freshie Guide Volunteer', 'uploads/candidates/cand_691e74c2547a2.png'),
(33, 1024, 8, 'Promote fairness and equal opportunities in all student activities.\r\n Establish clearer evaluation criteria for campus competitions.\r\n Support diversity and inclusion initiatives.', 'Student Mediator Volunteer \r\n Training in Conflict Resolution', 'uploads/candidates/cand_691e74b4a715e.png'),
(37, 1003, 3, 'rthgr5t', 'rtgrtg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `colleges`
--

CREATE TABLE `colleges` (
  `college_id` int(11) NOT NULL,
  `college_name` varchar(100) NOT NULL,
  `logo_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `colleges`
--

INSERT INTO `colleges` (`college_id`, `college_name`, `logo_path`) VALUES
(1, 'Institute of Arts and Design', NULL),
(2, 'College of Computing and Information Sciences', NULL),
(3, 'College of Business and Financial Sciences', NULL),
(4, 'Institute of Accountancy', NULL),
(5, 'College of Human Kinetics', NULL),
(6, 'College of Governance and Public Policy', NULL),
(7, 'Institute of Nursing', NULL),
(8, 'College of Tourism and Hospitality Management', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(150) NOT NULL,
  `college_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_name`, `college_id`) VALUES
(1, 'Bachelor in Multimedia Arts', 1),
(2, 'Associate in Customer Service Communication', 1),
(3, 'Bachelor of Science in Computer Science (Application Development Elective Track)', 2),
(4, 'Bachelor of Science in Information Technology (Information and Network Security Elective Track)', 2),
(5, 'Diploma in Application Development', 2),
(6, 'Diploma in Computer Network Administration', 2),
(7, 'Bachelor of Science in Entrepreneurial Management', 3),
(8, 'Bachelor of Science in Business Administration Major in Marketing Management', 3),
(9, 'Bachelor of Science in Office Administration', 3),
(10, 'Bachelor of Science in Financial Management', 3),
(11, 'Associate in Building and Property Management', 3),
(12, 'Associate in Supply Management', 3),
(13, 'Bachelor of Science in Accountancy', 4),
(14, 'Bachelor of Science in Management Accounting', 4),
(15, 'Bachelor of Science in Exercise and Sports Science major in Fitness and Sports Management', 5),
(16, 'Bachelor of Arts in Political Science major in Paralegal Studies', 6),
(17, 'Bachelor of Arts in Political Science major in Policy Management', 6),
(18, 'Bachelor of Arts in Political Science major in Local Government Administration', 6),
(19, 'Master of Arts in Nursing', 7),
(20, 'Bachelor of Science in Nursing', 7),
(21, 'Bachelor of Science in Hospitality Management', 8),
(22, 'Bachelor of Science in Tourism Management', 8),
(23, 'Associate in Hospitality Management', 8);

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `position_id` int(11) NOT NULL,
  `position_name` varchar(100) NOT NULL,
  `position_order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`position_id`, `position_name`, `position_order`) VALUES
(1, 'Chairperson', 1),
(2, 'Vice Chairperson', 2),
(3, 'Secretary', 3),
(4, 'Treasurer', 4),
(5, 'Auditor', 5),
(6, '2nd Year Representative', 6),
(7, '3rd Year Representative', 7),
(8, '4th Year Representative', 8);

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `schedule_id` int(11) NOT NULL,
  `phase` enum('VIEW_CANDIDATES','VOTING','CANDIDATE_CHECKING') NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`schedule_id`, `phase`, `start_datetime`, `end_datetime`) VALUES
(28, 'VOTING', '2025-11-26 08:00:00', '2025-11-27 17:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `year_level` int(11) NOT NULL,
  `is_enrolled` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`user_id`, `course_id`, `year_level`, `is_enrolled`) VALUES
(1001, 3, 3, 1),
(1002, 7, 2, 1),
(1003, 4, 2, 1),
(1004, 1, 4, 1),
(1005, 3, 1, 1),
(1006, 8, 2, 1),
(1007, 3, 4, 1),
(1008, 21, 1, 1),
(1009, 13, 3, 1),
(1010, 20, 2, 1),
(1011, 4, 3, 1),
(1012, 5, 2, 1),
(1013, 9, 1, 1),
(1014, 10, 4, 1),
(1015, 6, 2, 1),
(1016, 3, 5, 1),
(1017, 4, 3, 1),
(1018, 11, 2, 1),
(1019, 12, 1, 1),
(1020, 14, 4, 0),
(1021, 3, 2, 1),
(1022, 7, 3, 1),
(1023, 8, 2, 1),
(1024, 1, 3, 1),
(1025, 15, 2, 1),
(1026, 16, 3, 1),
(1027, 17, 1, 1),
(1028, 18, 4, 1),
(1029, 21, 3, 1),
(1030, 22, 2, 1),
(1031, 4, 1, 1),
(1032, 13, 2, 1),
(1033, 3, 4, 1),
(1034, 5, 1, 1),
(1035, 9, 4, 0),
(1036, 6, 2, 1),
(1037, 10, 3, 1),
(1038, 19, 1, 1),
(1039, 2, 1, 1),
(1040, 3, 2, 0),
(1041, 11, 2, 1),
(1042, 8, 4, 1),
(1043, 14, 3, 1),
(1044, 7, 1, 1),
(1045, 13, 5, 0),
(1046, 4, 2, 1),
(1047, 21, 3, 1),
(1048, 22, 1, 1),
(1049, 12, 1, 1),
(1050, 20, 5, 0),
(1051, 4, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `qr_code` text DEFAULT NULL,
  `role` enum('STUDENT','ADMIN') NOT NULL,
  `full_name` varchar(155) GENERATED ALWAYS AS (concat_ws(' ',`first_name`,`middle_name`,`last_name`)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `first_name`, `middle_name`, `last_name`, `password_hash`, `qr_code`, `role`) VALUES
(1, 'universityevotesystem@umak.edu.ph', 'Carlos', 'M.', 'Admin', 'e86f78a8a3caf0b60d8e74e5942aa6d86dc150cd3c03338aef25b7d2d7e3acc7', 'QR|1|ADMIN', 'ADMIN'),
(1001, 'juan.delacruz@umak.edu.ph', 'Juan', 'S', 'Delacruz', 'ebea740f538789186ea3130360354538ac629a749c662bc448a648543ff4dd4b', 'QR|1001|ENROLLED', 'STUDENT'),
(1002, 'maria.reyes@umak.edu.ph', 'Maria', 'C', 'Reyes', 'e3fb7650371ed915f63899035eb25f5f111032be1fbefb9a5f7f3659f7ac05b5', 'QR|1002|ENROLLED', 'STUDENT'),
(1003, 'juanito.santos@umak.edu.ph', 'Juanito', 'R', 'Santos', '4a35dce5a0e14f960d84ea82beda7aef88f94ca5c76ead459b51ac5aaab65541', 'QR|1003|ENROLLED', 'STUDENT'),
(1004, 'angelica.delao@umak.edu.ph', 'Angelica', 'M', 'Delao', '965531fdabdcc94d9533b13759055b18cc7c2bd6dc274174bb8d8714719e5bb8', 'QR|1004|ENROLLED', 'STUDENT'),
(1005, 'mark.rosales@umak.edu.ph', 'Mark', 'A', 'Rosales', '6fa0372ca9510a169305ddc9bd4443e4ee90d20381fa61d0c0642fde8e98150e', 'QR|1005|ENROLLED', 'STUDENT'),
(1006, 'nicole.mendoza@umak.edu.ph', 'Nicole', 'L', 'Mendoza', '131d3e2b37e1ef08b8d4eb1b60d614864c7c108973cdc0e57960883f4eedf429', 'QR|1006|ENROLLED', 'STUDENT'),
(1007, 'daniel.garcia@umak.edu.ph', 'Daniel', 'P', 'Garcia', '42db4895a825c9c4369b90e7855cf7dfe39ababfbb9a7ce4abe0038665728b1a', 'QR|1007|ENROLLED', 'STUDENT'),
(1008, 'karen.bautista@umak.edu.ph', 'Karen', 'R', 'Bautista', '3ae6137b728fcaf7bcde964984ba9dee8d2e9bbbe108e0490e9a1343e0e20a5b', 'QR|1008|ENROLLED', 'STUDENT'),
(1009, 'raph.delosreyes@umak.edu.ph', 'Raphael', 'V', 'Delosreyes', '684bdd34ef28d4d241b4f7867a4cc2d3b51f2c0b76de3d9d5eb92db4fcb7182a', 'QR|1009|ENROLLED', 'STUDENT'),
(1010, 'erika.soriano@umak.edu.ph', 'Erika', 'B', 'Soriano', '019adacf559347c105e054af637f810197bb881c28cf40c77efb6617cc02b4dc', 'QR|1010|ENROLLED', 'STUDENT'),
(1011, 'michael.tan@umak.edu.ph', 'Michael', 'C', 'Tan', '01d3494c993be2e606921c91829902de642fc788c1ec3f3112bc2958f693f8f5', 'QR|1011|ENROLLED', 'STUDENT'),
(1012, 'jasmine.perez@umak.edu.ph', 'Jasmine', 'L', 'Perez', 'be2820bbd275a723d0534073a3ff49ec767ed13eee561745aae3218e25d41cc9', 'QR|1012|ENROLLED', 'STUDENT'),
(1013, 'rico.bautista@umak.edu.ph', 'Rico', 'D', 'Bautista', 'ddaee17e39949ed822296d508797b69dffdd835396038d5e5cf6f5fd30d7718d', 'QR|1013|ENROLLED', 'STUDENT'),
(1014, 'alvin.cortes@umak.edu.ph', 'Alvin', 'J', 'Cortes', '0bcb98034be690c302e0a38bf01051f48cf7f714f1e20c65b8bd7744d95f9539', 'QR|1014|ENROLLED', 'STUDENT'),
(1015, 'chelle.santiago@umak.edu.ph', 'Chelle', 'E', 'Santiago', '0eee0178195a896147f669c6a2388dddfd12432aed640700a318171b8f20eeac', 'QR|1015|ENROLLED', 'STUDENT'),
(1016, 'patrick.ong@umak.edu.ph', 'Patrick', 'M', 'Ong', 'f22a1c5e43ef78e2681c1178cca004b684a0bfcceb88c0f885710221463b817a', 'QR|1016|ENROLLED', 'STUDENT'),
(1017, 'nicco.gonzales@umak.edu.ph', 'Nicco', 'A', 'Gonzales', '611dbc190dc0beb008e2a21f55ffc025e24bc6dcd8484effee5c5c2e0bd93e76', 'QR|1017|ENROLLED', 'STUDENT'),
(1018, 'leah.villanueva@umak.edu.ph', 'Leah', 'F', 'Villanueva', '7cc9191fc19c0c22a58dfeb5ad957838bc9c9aa43f8eb7936f6bb2d27c190bb1', 'QR|1018|ENROLLED', 'STUDENT'),
(1019, 'kevin.martinez@umak.edu.ph', 'Kevin', 'S', 'Martinez', 'aaf7f7be5ebb01e03f06250ba008953ef90fde16218ad22753fa9b203fcc399c', 'QR|1019|ENROLLED', 'STUDENT'),
(1020, 'anne.morales@umak.edu.ph', 'Anne', 'R', 'Morales', '2c0015835a31e6fac790eb5584dc52caebf05f3367e9859fcdcdc5fd5a84dcf4', 'QR|1020|UNENROLLED', 'STUDENT'),
(1021, 'rico.santos@umak.edu.ph', 'Rico', 'L', 'Santos', 'c07a108273d3141f0aa3f5476075813c16f7a71f31819211ca2483bb46c67855', 'QR|1021|ENROLLED', 'STUDENT'),
(1022, 'geraldine.ramos@umak.edu.ph', 'Geraldine', 'T', 'Ramos', '4798798bf3d3e54132beb6df2cac3ebf978e954a9fdae3be7f93af38ad32bbd7', 'QR|1022|ENROLLED', 'STUDENT'),
(1023, 'joel.alfaro@umak.edu.ph', 'Joel', 'P', 'Alfaro', 'a8fbf86ffd42942f7da895698014c30808feb2833b318b127e44540424354845', 'QR|1023|ENROLLED', 'STUDENT'),
(1024, 'michelle.cabral@umak.edu.ph', 'Michelle', 'A', 'Cabral', 'cbaf24504bd70f394a3430c08339da368f4db6156d96b1b861bc3d3dfdd997c4', 'QR|1024|ENROLLED', 'STUDENT'),
(1025, 'bernard.delosangeles@umak.edu.ph', 'Bernard', 'R', 'DelosAngeles', 'e259f80c578753f3a65a0245bf122604927ea79418810989c19e46af931ddde3', 'QR|1025|ENROLLED', 'STUDENT'),
(1026, 'paulino.dizon@umak.edu.ph', 'Paulino', 'M', 'Dizon', 'f9223e38562a30c9e7bd7e167af2c7b428cb613aa382e345b477be0a2720f87d', 'QR|1026|ENROLLED', 'STUDENT'),
(1027, 'kimberly.luna@umak.edu.ph', 'Kimberly', 'Y', 'Luna', 'b6daa39d219d64b7fb068b26e840ba65fb17e3d97f8081d01c457efadbf9e8c8', 'QR|1027|ENROLLED', 'STUDENT'),
(1028, 'josefina.caballero@umak.edu.ph', 'Josefina', 'N', 'Caballero', '784624311c6984067bed2304ce365dfb2b8b1f7a5ded24a91049541652633f27', 'QR|1028|ENROLLED', 'STUDENT'),
(1029, 'alfredo.guevarra@umak.edu.ph', 'Alfredo', 'C', 'Guevarra', '714712ff48f9bf6a1cfe665764e2b3f731e8db779cd07939b835709c0a3422f9', 'QR|1029|ENROLLED', 'STUDENT'),
(1030, 'charmaine.maliksi@umak.edu.ph', 'Charmaine', 'V', 'Maliksi', 'f1f7ef43e7997721cb0ff74bc4c5d987c70f34f72741841ca9009bcccb0d7514', 'QR|1030|ENROLLED', 'STUDENT'),
(1031, 'marvin.ong@umak.edu.ph', 'Marvin', 'D', 'Ong', '73c42191d688ae362134db5cd358ea752445696dbd23fec1bfffb4ea700e9fc8', 'QR|1031|ENROLLED', 'STUDENT'),
(1032, 'shiela.diaz@umak.edu.ph', 'Shiela', 'B', 'Diaz', '85c7bbea10939baed240a0f1ad95d1224ec04162305d7005a1245b097bacfa46', 'QR|1032|ENROLLED', 'STUDENT'),
(1033, 'raymond.estrada@umak.edu.ph', 'Raymond', 'L', 'Estrada', 'a88a29da654d20daa542445a34adf0328aef0bff07caad408133ef2a47c69284', 'QR|1033|ENROLLED', 'STUDENT'),
(1034, 'carlo.paimanes@umak.edu.ph', 'Carlo', 'H', 'Paimanes', '284b08e412f27751ded460d917b2413c318fcf091969dbbfec28cb16eb9f3866', 'QR|1034|ENROLLED', 'STUDENT'),
(1035, 'isabelle.cabaluna@umak.edu.ph', 'Isabelle', 'M', 'Cabaluna', '8d36cc988a05b250572308b997149b2d5ca2bbda81e975d7dfcb2dc5e277f2ab', 'QR|1035|UNENROLLED', 'STUDENT'),
(1036, 'manny.torres@umak.edu.ph', 'Manny', 'O', 'Torres', '12c358aa0f5f7ca09d69a17641796296585ec5e788b5b38ce960551dee1b930e', 'QR|1036|ENROLLED', 'STUDENT'),
(1037, 'joanna.beltran@umak.edu.ph', 'Joanna', 'R', 'Beltran', '5882df7d9ee9900a52d07da3d837b52af339c7644b1ccdc41599acf944059d79', 'QR|1037|ENROLLED', 'STUDENT'),
(1038, 'erwin.pantoja@umak.edu.ph', 'Erwin', 'A', 'Pantoja', '6ad39b2a6e61fda081cde3242fb38c3ddaf67d21f61e715ec4408cc46f2da0c5', 'QR|1038|ENROLLED', 'STUDENT'),
(1039, 'rhian.lao@umak.edu.ph', 'Rhian', 'S', 'Lao', '871349bf553792deed394d91b45d600bbfc80c66d3acee1c515b1b9ac2ebba3d', 'QR|1039|ENROLLED', 'STUDENT'),
(1040, 'dave.cervantes@umak.edu.ph', 'Dave', 'P', 'Cervantes', '6b636653449bb1f8fbc985e5b64cdf592403891e06105a7077523b52f75a6806', 'QR|1040|DROPPED', 'STUDENT'),
(1041, 'mike.baldoza@umak.edu.ph', 'Mike', 'J', 'Baldoza', 'f6f44c75dccd026819bed8a072a7e86647bcf5b1b0495249ded37748d9e08e47', 'QR|1041|ENROLLED', 'STUDENT'),
(1042, 'raissa.padilla@umak.edu.ph', 'Raissa', 'L', 'Padilla', '29e69e68c067b843f4f4b66517adc6ee709bca202cf599236871fe9624975e7b', 'QR|1042|ENROLLED', 'STUDENT'),
(1043, 'adrian.abalos@umak.edu.ph', 'Adrian', 'V', 'Abalos', '02e64104a8c02a0256256b8777c658260e7850af286b4023c37e1e2d9b5cd09b', 'QR|1043|ENROLLED', 'STUDENT'),
(1044, 'samantha.tuazon@umak.edu.ph', 'Samantha', 'G', 'Tuazon', '4076b47737081c9a529a84ceb0b8a6a8f87e47db66e542ce0cd6b0011bbd7780', 'QR|1044|ENROLLED', 'STUDENT'),
(1045, 'francis.soriano@umak.edu.ph', 'Francis', 'K', 'Soriano', '25919d2dada35f9c5a7adc8af66043827e4f76fd84e32e9be9609d6c3ac8a893', 'QR|1045|GRADUATED', 'STUDENT'),
(1046, 'bryan.escobar@umak.edu.ph', 'Bryan', 'M', 'Escobar', 'dbe7466a1d4f673520393d8cda9cee50f49d8f74be915820f6c7b3e633fc8e7c', 'QR|1046|ENROLLED', 'STUDENT'),
(1047, 'janine.lim@umak.edu.ph', 'Janine', 'A', 'Lim', '0481e2582fb4151ca0887b75d24fcd31cf64b84f4d85a13d6f90f746a272c74c', 'QR|1047|ENROLLED', 'STUDENT'),
(1048, 'quentin.sison@umak.edu.ph', 'Quentin', 'E', 'Sison', '1134a4edf2e47185cbc310e18f25c9e7b14368da2dff9dd8c8cd178d990619e9', 'QR|1048|ENROLLED', 'STUDENT'),
(1049, 'theresa.magsino@umak.edu.ph', 'Theresa', 'N', 'Magsino', 'a7d4706d8339a338fa63be6b24bda56a1ec9c339c0642ba96acfc899bcd74f1f', 'QR|1049|ENROLLED', 'STUDENT'),
(1050, 'eduardo.bueno@umak.edu.ph', 'Eduardo', 'R', 'Bueno', '2ad97222e4fbae3e0dcc4d57ee821077effdbf925b9365cccd4d6e67d9308673', 'QR|1050|GRADUATED', 'STUDENT'),
(1051, 'ctaccad.a12345224@umak.edu.ph', 'Christian Jake', 'B', 'Taccad', '076b3ebdca20a200a2a05954508adb263f9e716b40118c48a2c3ff6100101f7b', 'QR|1051|ENROLLED', 'STUDENT');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `vote_id` int(11) NOT NULL,
  `ballot_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `vote_timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`vote_id`, `ballot_id`, `candidate_id`, `vote_timestamp`) VALUES
(9, 6, 18, '2025-11-20 03:05:37'),
(10, 6, 20, '2025-11-20 03:05:37'),
(11, 6, 23, '2025-11-20 03:05:37'),
(12, 6, 24, '2025-11-20 03:05:37'),
(13, 6, 29, '2025-11-20 03:05:37'),
(14, 6, 33, '2025-11-20 03:05:37'),
(15, 7, 20, '2025-11-20 03:06:24'),
(16, 7, 18, '2025-11-20 03:06:24'),
(17, 7, 22, '2025-11-20 03:06:24'),
(18, 7, 25, '2025-11-20 03:06:24'),
(19, 7, 29, '2025-11-20 03:06:24'),
(20, 7, 31, '2025-11-20 03:06:24'),
(21, 8, 20, '2025-11-20 03:06:45'),
(22, 8, 18, '2025-11-20 03:06:45'),
(23, 8, 23, '2025-11-20 03:06:45'),
(24, 8, 24, '2025-11-20 03:06:45'),
(25, 8, 28, '2025-11-20 03:06:45'),
(26, 8, 27, '2025-11-20 03:06:45'),
(27, 9, 21, '2025-11-20 03:08:00'),
(28, 9, 18, '2025-11-20 03:08:00'),
(29, 9, 23, '2025-11-20 03:08:00'),
(30, 9, 24, '2025-11-20 03:08:00'),
(31, 9, 29, '2025-11-20 03:08:00'),
(32, 9, 30, '2025-11-20 03:08:00'),
(33, 11, 19, '2025-11-20 03:24:33'),
(34, 11, 20, '2025-11-20 03:24:33'),
(35, 11, 23, '2025-11-20 03:24:33'),
(36, 11, 25, '2025-11-20 03:24:33'),
(37, 11, 28, '2025-11-20 03:24:33'),
(38, 12, 20, '2025-11-20 03:27:11'),
(39, 12, 18, '2025-11-20 03:27:11'),
(40, 12, 23, '2025-11-20 03:27:11'),
(41, 14, 18, '2025-11-20 03:37:34'),
(42, 14, 21, '2025-11-20 03:37:34'),
(43, 14, 23, '2025-11-20 03:37:34'),
(44, 14, 25, '2025-11-20 03:37:34'),
(45, 14, 28, '2025-11-20 03:37:34'),
(46, 15, 19, '2025-11-20 04:05:43'),
(47, 15, 20, '2025-11-20 04:05:43'),
(48, 15, 22, '2025-11-20 04:05:43'),
(49, 15, 24, '2025-11-20 04:05:43'),
(50, 15, 29, '2025-11-20 04:05:43'),
(51, 15, 33, '2025-11-20 04:05:43'),
(52, 16, 21, '2025-11-20 08:45:07'),
(53, 16, 23, '2025-11-20 08:45:07'),
(54, 16, 25, '2025-11-20 08:45:07'),
(55, 16, 29, '2025-11-20 08:45:07'),
(56, 16, 18, '2025-11-20 08:45:07'),
(57, 17, 18, '2025-11-20 14:02:53'),
(58, 17, 20, '2025-11-20 14:02:53'),
(59, 17, 22, '2025-11-20 14:02:53'),
(60, 17, 24, '2025-11-20 14:02:53'),
(61, 17, 29, '2025-11-20 14:02:53'),
(62, 17, 33, '2025-11-20 14:02:53'),
(63, 18, 18, '2025-11-23 05:51:48'),
(64, 18, 21, '2025-11-23 05:51:48'),
(65, 18, 23, '2025-11-23 05:51:48'),
(66, 18, 24, '2025-11-23 05:51:48'),
(67, 18, 32, '2025-11-23 05:51:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adminnotes`
--
ALTER TABLE `adminnotes`
  ADD PRIMARY KEY (`note_id`),
  ADD KEY `admin_user_id` (`admin_user_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `announcementfiles`
--
ALTER TABLE `announcementfiles`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `announcement_id` (`announcement_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `auditlogs`
--
ALTER TABLE `auditlogs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ballots`
--
ALTER TABLE `ballots`
  ADD PRIMARY KEY (`ballot_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`candidate_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `position_id` (`position_id`);

--
-- Indexes for table `colleges`
--
ALTER TABLE `colleges`
  ADD PRIMARY KEY (`college_id`),
  ADD UNIQUE KEY `college_name` (`college_name`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `course_name` (`course_name`),
  ADD KEY `college_id` (`college_id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`position_id`),
  ADD UNIQUE KEY `position_name` (`position_name`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`schedule_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`vote_id`),
  ADD UNIQUE KEY `uq_vote` (`ballot_id`,`candidate_id`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adminnotes`
--
ALTER TABLE `adminnotes`
  MODIFY `note_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `announcementfiles`
--
ALTER TABLE `announcementfiles`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `auditlogs`
--
ALTER TABLE `auditlogs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=425;

--
-- AUTO_INCREMENT for table `ballots`
--
ALTER TABLE `ballots`
  MODIFY `ballot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `candidate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `colleges`
--
ALTER TABLE `colleges`
  MODIFY `college_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1052;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `vote_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adminnotes`
--
ALTER TABLE `adminnotes`
  ADD CONSTRAINT `adminnotes_ibfk_1` FOREIGN KEY (`admin_user_id`) REFERENCES `admins` (`user_id`);

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `announcementfiles`
--
ALTER TABLE `announcementfiles`
  ADD CONSTRAINT `announcementfiles_ibfk_1` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`announcement_id`);

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`user_id`);

--
-- Constraints for table `auditlogs`
--
ALTER TABLE `auditlogs`
  ADD CONSTRAINT `auditlogs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `ballots`
--
ALTER TABLE `ballots`
  ADD CONSTRAINT `ballots_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `students` (`user_id`);

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `students` (`user_id`),
  ADD CONSTRAINT `candidates_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`);

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`college_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`);

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`ballot_id`) REFERENCES `ballots` (`ballot_id`),
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`candidate_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
