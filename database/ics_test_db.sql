-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 16, 2026 at 04:29 PM
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
-- Database: `ics_test_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `log_id` varchar(50) NOT NULL,
  `admin_id` varchar(50) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `affected_resource_type` varchar(100) DEFAULT NULL,
  `affected_resource_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `log_id`, `admin_id`, `action`, `details`, `ip_address`, `user_agent`, `affected_resource_type`, `affected_resource_id`, `created_at`) VALUES
(1, 'LOG2025111072172671', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-11-10 08:28:53'),
(2, 'LOG20251110C0A3477F', 'UNKNOWN', 'LOGIN_FAILED', '{\"username\":null,\"reason\":\"user_not_found\"}', '::1', 'PostmanRuntime/7.50.0', '', '', '2025-11-10 09:23:39'),
(3, 'LOG202511105A49D57D', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'PostmanRuntime/7.50.0', '', '', '2025-11-10 09:24:24'),
(4, 'LOG2025111042994FF2', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-11-10 15:43:32'),
(5, 'LOG2025111013674004', '3', 'APPOINTMENT_STATUS_UPDATE', '{\"appointment_id\":\"4\",\"new_status\":\"completed\"}', '::1', 'PostmanRuntime/7.50.0', 'appointment', '4', '2025-11-10 17:56:18'),
(6, 'LOG20251110834C1738', '3', 'APPOINTMENT_STATUS_UPDATE', '{\"appointment_id\":\"4\",\"new_status\":\"no-show\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'appointment', '4', '2025-11-10 18:16:07'),
(7, 'LOG20251110D8A466E0', '3', 'APPOINTMENT_STATUS_UPDATE', '{\"appointment_id\":\"4\",\"new_status\":\"completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'appointment', '4', '2025-11-10 18:16:39'),
(8, 'LOG20251110DF964A0D', '3', 'APPOINTMENT_STATUS_UPDATE', '{\"appointment_id\":\"4\",\"new_status\":\"scheduled\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'appointment', '4', '2025-11-10 18:22:09'),
(9, 'LOG202511105D5406E5', '3', 'APPOINTMENT_STATUS_UPDATE', '{\"appointment_id\":\"4\",\"new_status\":\"completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'appointment', '4', '2025-11-10 18:22:27'),
(10, 'LOG20251110C360D439', '3', 'APPOINTMENT_STATUS_UPDATE', '{\"appointment_id\":\"4\",\"new_status\":\"no-show\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'appointment', '4', '2025-11-10 18:23:56'),
(11, 'LOG20251110E7051B22', '3', 'APPOINTMENT_STATUS_UPDATE', '{\"appointment_id\":\"4\",\"new_status\":\"scheduled\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'appointment', '4', '2025-11-10 18:25:16'),
(12, 'LOG202511101E43F294', '3', 'APPOINTMENT_STATUS_UPDATE', '{\"appointment_id\":\"4\",\"new_status\":\"completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'appointment', '4', '2025-11-10 18:25:19'),
(13, 'LOG20251111FA7D31ED', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'PostmanRuntime/7.50.0', '', '', '2025-11-10 18:50:44'),
(14, 'LOG2025111219282D8D', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'PostmanRuntime/7.50.0', '', '', '2025-11-12 08:50:34'),
(15, 'LOG20251112869E358E', '3', 'CREATE_SERVICE', '{\"service_id\":5,\"title\":\"Passport Renewal\"}', '::1', 'PostmanRuntime/7.50.0', 'service', '5', '2025-11-12 09:55:54'),
(16, 'LOG20251112128B293A', '3', 'CREATE_SERVICE', '{\"service_id\":6,\"title\":\"Passport Renewal\"}', '::1', 'PostmanRuntime/7.50.0', 'service', '6', '2025-11-12 09:56:10'),
(17, 'LOG2025111209423E79', '3', 'CREATE_SERVICE', '{\"service_id\":7,\"title\":\"Passport Renewal\"}', '::1', 'PostmanRuntime/7.50.0', 'service', '7', '2025-11-12 09:56:49'),
(18, 'LOG202511121E02A824', '3', 'CREATE_SERVICE', '{\"service_id\":8,\"title\":\"Passport Renewal\"}', '::1', 'PostmanRuntime/7.50.0', 'service', '8', '2025-11-12 09:58:01'),
(19, 'LOG20251112F4D7F9C4', '3', 'CREATE_SERVICE', '{\"service_id\":9,\"title\":\"Passport Renewal\"}', '::1', 'PostmanRuntime/7.50.0', 'service', '9', '2025-11-12 09:58:36'),
(20, 'LOG2025111267376514', '3', 'CREATE_SERVICE', '{\"service_id\":10,\"title\":\"Passport Renewal\"}', '::1', 'PostmanRuntime/7.50.0', 'service', '10', '2025-11-12 10:00:26'),
(21, 'LOG202511123E434534', '3', 'UPDATE_SERVICE', '{\"service_id\":\"10\",\"updates\":[\"title\",\"fees\",\"updated_at\"]}', '::1', 'PostmanRuntime/7.50.0', 'service', '10', '2025-11-12 10:04:33'),
(22, 'LOG20251112BA4F94AC', '3', 'DEACTIVATE_SERVICE', '{\"service_id\":\"10\"}', '::1', 'PostmanRuntime/7.50.0', 'service', '10', '2025-11-12 10:05:50'),
(23, 'LOG20251112ABD6B547', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-11-12 10:31:46'),
(24, 'LOG202511128D4DE121', '3', 'DEACTIVATE_SERVICE', '{\"service_id\":\"2\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'service', '2', '2025-11-12 10:32:15'),
(25, 'LOG202511123CF881FA', '3', 'ACTIVATE_SERVICE', '{\"service_id\":\"2\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'service', '2', '2025-11-12 10:32:17'),
(26, 'LOG2025111253E7383C', '3', 'DEACTIVATE_SERVICE', '{\"service_id\":\"2\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'service', '2', '2025-11-12 10:32:21'),
(27, 'LOG20251112AA624443', '3', 'ACTIVATE_SERVICE', '{\"service_id\":\"2\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'service', '2', '2025-11-12 10:32:30'),
(28, 'LOG2025111267B86FB8', '3', 'ACTIVATE_SERVICE', '{\"service_id\":\"10\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'service', '10', '2025-11-12 10:32:31'),
(29, 'LOG20251113F595DCC9', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-11-13 12:27:55'),
(30, 'LOG20251122BB8C2C3A', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-11-22 02:46:09'),
(31, 'LOG202511223BAB0CDE', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'PostmanRuntime/7.49.1', '', '', '2025-11-22 04:46:19'),
(32, 'LOG20251126B9DEC22B', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-11-26 03:24:30'),
(33, 'LOG20251126AE6625CB', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'PostmanRuntime/7.49.1', '', '', '2025-11-26 05:16:22'),
(34, 'LOG202511262C1BC857', '3', 'DELETE_SERVICE', '{\"service_id\":\"10\",\"title\":\"Passport Renewal\"}', '::1', 'PostmanRuntime/7.49.1', 'service', '10', '2025-11-26 05:44:14'),
(35, 'LOG202511263957F0F4', '3', 'CREATE_SERVICE', '{\"service_id\":11,\"title\":\"Passport Renewal\",\"centers\":[1,2]}', '::1', 'PostmanRuntime/7.49.1', 'service', '11', '2025-11-26 05:46:38'),
(36, 'LOG202511262805EC10', '3', 'UPDATE_SERVICE', '{\"service_id\":\"2\",\"updates\":[\"category\",\"title\",\"description\",\"fees\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'service', '2', '2025-11-26 06:45:22'),
(37, 'LOG202511263B269F6C', '3', 'CREATE_SERVICE', '{\"service_id\":12,\"title\":\"new passport\",\"centers\":[1]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'service', '12', '2025-11-26 06:52:21'),
(38, 'LOG2025112641FA10CE', '3', 'UPDATE_SERVICE', '{\"service_id\":\"12\",\"updates\":[\"category\",\"title\",\"description\",\"fees\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'service', '12', '2025-11-26 06:57:55'),
(39, 'LOG20251126FCE5949E', '3', 'DEACTIVATE_COUNTER', '{\"counter_id\":\"3\"}', '::1', 'PostmanRuntime/7.49.1', 'counter', '3', '2025-11-26 07:00:54'),
(40, 'LOG202511260F2E4889', '3', 'UPDATE_COUNTER', '{\"counter_id\":\"3\",\"updated_fields\":[\"counter_name\",\"is_active\",\"updated_at\"]}', '::1', 'PostmanRuntime/7.49.1', 'counter', '3', '2025-11-26 07:25:15'),
(41, 'LOG20251126DFB00D48', '3', 'UPDATE_COUNTER', '{\"counter_id\":\"3\",\"updated_fields\":[\"counter_name\",\"is_active\",\"updated_at\"]}', '::1', 'PostmanRuntime/7.49.1', 'counter', '3', '2025-11-26 07:25:52'),
(42, 'LOG202511262E914A11', '3', 'UPDATE_COUNTER_FULL', '{\"counter_id\":\"3\",\"updated_fields\":[\"counter_name\",\"is_active\",\"service_handled\"]}', '::1', 'PostmanRuntime/7.49.1', 'counter', '3', '2025-11-26 07:27:35'),
(43, 'LOG20251126113DEA07', '3', 'DEACTIVATE_COUNTER', '{\"counter_id\":\"2\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'counter', '2', '2025-11-26 07:44:56'),
(44, 'LOG20251126FF66369B', '3', 'UPDATE_COUNTER_FULL', '{\"counter_id\":\"3\",\"updated_fields\":[\"counter_name\",\"is_active\",\"service_handled\"]}', '::1', 'PostmanRuntime/7.49.1', 'counter', '3', '2025-11-26 08:15:10'),
(45, 'LOG2025112635942A1B', '3', 'ACTIVATE_COUNTER', '{\"counter_id\":\"2\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'counter', '2', '2025-11-26 08:26:32'),
(46, 'LOG202511264BA36C3C', '3', 'ACTIVATE_COUNTER', '{\"counter_id\":\"3\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'counter', '3', '2025-11-26 08:26:32'),
(47, 'LOG20251126F7D2DA1C', '3', 'UPDATE_COUNTER_FULL', '{\"counter_id\":\"3\",\"updated_fields\":[\"counter_name\",\"is_active\",\"service_handled\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'counter', '3', '2025-11-26 08:27:13'),
(48, 'LOG20251126FC523CCE', '3', 'UPDATE_COUNTER_FULL', '{\"counter_id\":\"3\",\"updated_fields\":[\"counter_name\",\"is_active\",\"service_handled\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'counter', '3', '2025-11-26 08:27:27'),
(49, 'LOG20251208FE61360D', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-12-08 10:27:08'),
(50, 'LOG202512084271EAE9', '3', 'APPOINTMENT_STATUS_UPDATE', '{\"appointment_id\":\"5\",\"new_status\":\"no-show\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'appointment', '5', '2025-12-08 10:31:32'),
(51, 'LOG202512083DE2F567', '3', 'APPOINTMENT_STATUS_UPDATE', '{\"appointment_id\":\"5\",\"new_status\":\"scheduled\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'appointment', '5', '2025-12-08 12:28:33'),
(52, 'LOG20251219EC6FE575', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-19 03:00:41'),
(53, 'LOG20251219A61FA574', '3', 'UPDATE_SERVICE', '{\"service_id\":\"1\",\"updates\":[\"category\",\"title\",\"description\",\"fees\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'service', '1', '2025-12-19 03:48:33'),
(54, 'LOG202512190E0A9D92', '3', 'UPDATE_SERVICE', '{\"service_id\":\"2\",\"updates\":[\"category\",\"title\",\"description\",\"fees\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'service', '2', '2025-12-19 03:48:39'),
(55, 'LOG20251219025AB03B', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'PostmanRuntime/7.49.1', '', '', '2025-12-19 04:38:55'),
(56, 'LOG20251219C46410CE', '3', 'CREATE_SERVICE_DETAILS', '{\"service_id\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'service_details', '1', '2025-12-19 06:04:30'),
(57, 'LOG2025121915423AE9', '3', 'CREATE_SERVICE_DETAILS', '{\"service_id\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'service_details', '2', '2025-12-19 07:02:55'),
(58, 'LOG20251220599AC84B', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-20 02:27:02'),
(59, 'LOG20251220589028DE', '3', 'UPDATE_SERVICE_DETAILS', '{\"service_id\":\"1\",\"updates\":[\"overview\",\"visa_fees\",\"documents_required\",\"photo_specifications\",\"processing_time\",\"downloads_form\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'service_details', '1', '2025-12-20 03:40:17'),
(60, 'LOG202512203A2A2394', '3', 'UPDATE_SERVICE_DETAILS', '{\"service_id\":\"1\",\"updates\":[\"overview\",\"visa_fees\",\"documents_required\",\"photo_specifications\",\"processing_time\",\"downloads_form\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'service_details', '1', '2025-12-20 03:40:24'),
(61, 'LOG20251220A5E2E095', '3', 'UPDATE_SERVICE_DETAILS', '{\"service_id\":\"1\",\"updates\":[\"overview\",\"visa_fees\",\"documents_required\",\"photo_specifications\",\"processing_time\",\"downloads_form\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'service_details', '1', '2025-12-20 03:53:48'),
(62, 'LOG2025122051011E3B', '3', 'UPDATE_SERVICE_DETAILS', '{\"service_id\":\"2\",\"updates\":[\"overview\",\"visa_fees\",\"documents_required\",\"photo_specifications\",\"processing_time\",\"downloads_form\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'service_details', '2', '2025-12-20 03:53:53'),
(63, 'LOG202512215EAF03A9', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-21 01:22:15'),
(64, 'LOG202512214268977C', '3', 'UPDATE_SERVICE_DETAILS', '{\"service_id\":\"1\",\"updates\":[\"overview\",\"visa_fees\",\"documents_required\",\"photo_specifications\",\"processing_time\",\"downloads_form\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'service_details', '1', '2025-12-21 01:22:45'),
(65, 'LOG20251221B47A79BB', '3', 'UPDATE_SERVICE_DETAILS', '{\"service_id\":\"2\",\"updates\":[\"overview\",\"visa_fees\",\"documents_required\",\"photo_specifications\",\"processing_time\",\"downloads_form\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'service_details', '2', '2025-12-21 01:23:26'),
(66, 'LOG20251222FD8CD43A', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-22 03:18:41'),
(67, 'LOG20251224D803A422', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-24 05:17:05'),
(68, 'LOG20251224F884B3C5', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-24 05:20:41'),
(69, 'LOG20251224BEDBD80B', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-24 05:21:28'),
(70, 'LOG20251224209318DB', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-24 05:21:49'),
(71, 'LOG2025122476A44F38', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-24 05:22:10'),
(72, 'LOG20251224F41D730A', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-24 05:22:21'),
(73, 'LOG2025122452C30027', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-24 05:22:33'),
(74, 'LOG20260114A5959DA4', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 03:53:23'),
(75, 'LOG202601147725C582', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 03:59:20'),
(76, 'LOG2026011437FB2BBF', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 04:17:31'),
(77, 'LOG20260114131005EA', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 04:49:13'),
(78, 'LOG202601145F91BF1C', 'UNKNOWN', 'LOGIN_FAILED', '{\"email\":\"adminujwal\",\"reason\":\"user_not_found\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 05:44:15'),
(79, 'LOG202601141BAE1A22', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 05:44:26'),
(80, 'LOG2026011490DE9C29', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:10:44'),
(81, 'LOG20260114CAD8D488', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:10:56'),
(82, 'LOG2026011418931687', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:11:15'),
(83, 'LOG2026011481A22F43', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:11:19'),
(84, 'LOG20260114EF13421E', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:11:20'),
(85, 'LOG20260114989DF04B', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:11:21'),
(86, 'LOG20260114E2A6DC76', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:11:25'),
(87, 'LOG20260114D6957F72', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:11:43'),
(88, 'LOG2026011421DCE5A1', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:12:12'),
(89, 'LOG202601141CE3B516', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:12:16'),
(90, 'LOG202601142ADB4B74', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:12:31'),
(91, 'LOG2026011424B5F6A8', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:12:34'),
(92, 'LOG20260114DA51EFFA', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:12:35'),
(93, 'LOG2026011418C49FF0', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:12:36'),
(94, 'LOG2026011436E90874', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:12:36'),
(95, 'LOG20260114142CF0E1', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:12:37'),
(96, 'LOG202601143A1EBF9B', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:12:38'),
(97, 'LOG2026011442FACCB3', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:12:38'),
(98, 'LOG20260114E904B475', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:12:38'),
(99, 'LOG20260114A677CE24', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:12:39'),
(100, 'LOG2026011445C75DDE', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:12:42'),
(101, 'LOG2026011451C5AA8B', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:12:45'),
(102, 'LOG20260114E7DE9037', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:12:47'),
(103, 'LOG20260114DFD2E1B4', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:28:30'),
(104, 'LOG20260114FC85A174', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:31:01'),
(105, 'LOG20260114494A23AD', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:31:32'),
(106, 'LOG2026011489B7EA2D', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:31:40'),
(107, 'LOG2026011431D0F8A8', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:32:12'),
(108, 'LOG2026011449A23097', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:33:13'),
(109, 'LOG202601145BF41592', '3', 'CREATE_SERVICE_DETAILS', '{\"service_id\":12}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'service_details', '12', '2026-01-14 08:33:32'),
(110, 'LOG2026011443292726', '3', 'CREATE_SERVICE_DETAILS', '{\"service_id\":12}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'service_details', '12', '2026-01-14 08:45:39'),
(111, 'LOG202601148346334A', '3', 'ADMIN_LOGOUT', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 09:30:50'),
(112, 'LOG202601141513C4EC', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 09:31:42'),
(113, 'LOG20260114A4373DC3', '3', 'APPOINTMENT_STATUS_UPDATE', '{\"appointment_id\":\"12\",\"new_status\":\"no-show\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'appointment', '12', '2026-01-14 09:33:42'),
(114, 'LOG20260115C9364287', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-15 04:03:50'),
(115, 'LOG20260115900E1FB8', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-15 04:04:36'),
(116, 'LOG202601151CAD3312', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-15 04:06:39'),
(117, 'LOG20260115B9237A8E', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-15 04:10:25'),
(118, 'LOG2026011501E8D78A', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-15 04:10:56'),
(119, 'LOG20260115115D2CE1', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-15 04:14:42'),
(120, 'LOG20260115FC818ECA', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-15 05:06:20'),
(121, 'LOG202601150DBBF238', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'PostmanRuntime/7.51.0', '', '', '2026-01-15 06:30:38'),
(122, 'LOG202601151A6090B1', '3', 'CREATE_COUNTER', '{\"counter_id\":4,\"name\":\"Counter D - Document Authentication\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'counter', '4', '2026-01-15 07:00:43'),
(123, 'LOG20260115E4C8CDF0', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-15 17:16:37'),
(124, 'LOG20260115403FB3F6', '3', 'CREATE_SERVICE', '{\"service_id\":13,\"title\":\"Marriage Certificate\",\"centers\":[1]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'service', '13', '2026-01-15 17:22:52'),
(125, 'LOG202601156C2D8FC6', '3', 'UPDATE_SERVICE', '{\"service_id\":\"13\",\"updates\":[\"category\",\"title\",\"description\",\"fees\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'service', '13', '2026-01-15 17:23:32'),
(126, 'LOG202601151B586162', '3', 'UPDATE_SERVICE', '{\"service_id\":\"13\",\"updates\":[\"category\",\"title\",\"description\",\"fees\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'service', '13', '2026-01-15 17:34:17'),
(127, 'LOG20260115033936FD', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-15 18:21:18'),
(128, 'LOG20260115C6B52FA3', '3', 'DELETE_SERVICE', '{\"service_id\":\"13\",\"title\":\"Marriage Certificate\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'service', '13', '2026-01-15 18:26:36'),
(129, 'LOG20260116232B6991', '3', 'CREATE_SERVICE', '{\"service_id\":14,\"title\":\"Marriage Certificate\",\"centers\":[1]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'service', '14', '2026-01-15 19:08:38'),
(130, 'LOG2026011675B9FA01', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-16 08:28:15'),
(131, 'LOG202601168A9467AD', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-16 09:25:48'),
(132, 'LOG202601162C6F9947', '3', 'UPDATE_MISCELLANEOUS_APPLICATION', '{\"application_id\":\"MISC202601168E18F3AA\",\"updates\":[\"status\",\"form_data\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC202601168E18F3AA', '2026-01-16 09:26:08'),
(133, 'LOG2026011630443591', '3', 'UPDATE_MISCELLANEOUS_APPLICATION', '{\"application_id\":\"MISC202601168E18F3AA\",\"updates\":[\"status\",\"form_data\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC202601168E18F3AA', '2026-01-16 09:26:32'),
(134, 'LOG202601164D193391', '3', 'UPDATE_MISCELLANEOUS_APPLICATION', '{\"application_id\":\"MISC20260116FC717B77\",\"updates\":[\"status\",\"form_data\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC20260116FC717B77', '2026-01-16 09:32:15'),
(135, 'LOG20260116DE8399C4', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-16 09:34:31'),
(136, 'LOG202601166DA2FE0D', '3', 'UPDATE_MISCELLANEOUS_APPLICATION', '{\"application_id\":\"MISC20260116FC717B77\",\"updates\":[\"status\",\"form_data\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC20260116FC717B77', '2026-01-16 09:34:50'),
(137, 'LOG202601162BE15E0C', '3', 'UPDATE_MISCELLANEOUS_APPLICATION', '{\"application_id\":\"MISC20260116FC717B77\",\"updates\":[\"status\",\"form_data\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC20260116FC717B77', '2026-01-16 09:35:27'),
(138, 'LOG2026011600B754FA', '3', 'UPDATE_MISCELLANEOUS_APPLICATION', '{\"application_id\":\"MISC20260116FC717B77\",\"updates\":[\"status\",\"form_data\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC20260116FC717B77', '2026-01-16 09:41:07'),
(139, 'LOG20260116DD4C65A6', '3', 'UPDATE_MISCELLANEOUS_APPLICATION', '{\"application_id\":\"MISC202601168E18F3AA\",\"updates\":[\"status\",\"form_data\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC202601168E18F3AA', '2026-01-16 09:41:53'),
(140, 'LOG2026011666642AC7', '3', 'UPDATE_MISCELLANEOUS_APPLICATION', '{\"application_id\":\"MISC202601168E18F3AA\",\"updates\":[\"status\",\"form_data\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC202601168E18F3AA', '2026-01-16 09:42:04'),
(141, 'LOG2026011605DE49EC', '3', 'UPDATE_MISCELLANEOUS_APPLICATION', '{\"application_id\":\"MISC20260116FC717B77\",\"updates\":[\"status\",\"form_data\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC20260116FC717B77', '2026-01-16 09:42:14'),
(142, 'LOG202601169B96E9A4', '3', 'UPDATE_MISCELLANEOUS_APPLICATION', '{\"application_id\":\"MISC202601168E18F3AA\",\"updates\":[\"status\",\"form_data\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC202601168E18F3AA', '2026-01-16 09:44:07'),
(143, 'LOG20260116AB844051', '3', 'UPDATE_MISCELLANEOUS_APPLICATION', '{\"application_id\":\"MISC202601168E18F3AA\",\"updates\":[\"status\",\"form_data\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC202601168E18F3AA', '2026-01-16 09:48:43'),
(144, 'LOG20260116F076333B', '3', 'UPDATE_MISCELLANEOUS_APPLICATION', '{\"application_id\":\"MISC202601168E18F3AA\",\"updates\":[\"status\",\"form_data\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC202601168E18F3AA', '2026-01-16 09:48:54'),
(145, 'LOG2026011637D0D550', '3', 'UPDATE_MISCELLANEOUS_APPLICATION', '{\"application_id\":\"MISC202601168E18F3AA\",\"updates\":[\"status\",\"form_data\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC202601168E18F3AA', '2026-01-16 09:49:11'),
(146, 'LOG202601168C690620', '3', 'UPDATE_MISCELLANEOUS_APPLICATION', '{\"application_id\":\"MISC202601168E18F3AA\",\"updates\":[\"status\",\"form_data\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC202601168E18F3AA', '2026-01-16 09:55:42'),
(147, 'LOG20260116E2EBF60C', '3', 'UPDATE_MISCELLANEOUS_APPLICATION', '{\"application_id\":\"MISC202601168E18F3AA\",\"updates\":[\"status\",\"form_data\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC202601168E18F3AA', '2026-01-16 09:55:51'),
(148, 'LOG202601161460628E', '3', 'UPDATE_MISCELLANEOUS_APPLICATION', '{\"application_id\":\"MISC202601168E18F3AA\",\"updates\":[\"status\",\"admin_notes\",\"form_data\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC202601168E18F3AA', '2026-01-16 09:56:07'),
(149, 'LOG2026011674EBBF60', '3', 'UPDATE_MISCELLANEOUS_APPLICATION', '{\"application_id\":\"MISC202601168E18F3AA\",\"updates\":[\"status\",\"admin_notes\",\"form_data\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC202601168E18F3AA', '2026-01-16 09:59:54'),
(150, 'LOG202601160FDD33B1', '3', 'UPDATE_MISCELLANEOUS_APPLICATION', '{\"application_id\":\"MISC202601168E18F3AA\",\"updates\":[\"status\",\"admin_notes\",\"form_data\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC202601168E18F3AA', '2026-01-16 10:00:05'),
(151, 'LOG2026011696AB7340', '3', 'UPDATE_MISCELLANEOUS_APPLICATION', '{\"application_id\":\"MISC202601168E18F3AA\",\"updates\":[\"status\",\"admin_notes\",\"form_data\",\"updated_at\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC202601168E18F3AA', '2026-01-16 10:00:53');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `admin_id` varchar(50) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `role` enum('admin','officer','supervisor') DEFAULT 'officer',
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `admin_id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `role`, `permissions`, `last_login`, `is_active`, `two_factor_secret`, `created_at`, `updated_at`) VALUES
(1, 'ADMIN001', 'officer123', 'admin@consular.gov.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin', '[\"all\"]', NULL, 1, NULL, '2025-11-05 13:53:27', '2025-11-05 13:53:27'),
(2, 'OFF001', 'officer456', 'officer@consular.gov.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Consular', 'Officer', 'officer', '[\"applications\", \"appointments\"]', NULL, 1, NULL, '2025-11-05 13:53:27', '2025-11-05 13:53:27'),
(3, '3', 'ujwaladmin', 'ujwalthakkar020@gmail.com', '$2y$10$H/4k9jiWeHFJJZmphv1xcOp5vRgHAh07eKy3nLJvQCPBA5lNvmRpa', 'ujwal', 'thakkar', 'admin', '[\"all\"]', '2026-01-16 09:34:31', 1, NULL, '2025-11-10 12:05:04', '2026-01-16 14:04:31');

-- --------------------------------------------------------

--
-- Table structure for table `application_files`
--

CREATE TABLE `application_files` (
  `id` int(11) NOT NULL,
  `file_id` varchar(50) NOT NULL,
  `application_id` varchar(50) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL COMMENT 'Size in bytes',
  `mime_type` varchar(100) DEFAULT NULL,
  `document_type` varchar(100) DEFAULT NULL COMMENT 'Type of document (passport, birth_certificate, etc.)',
  `uploaded_by` int(11) DEFAULT NULL COMMENT 'User ID who uploaded',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `appointment_id` int(11) NOT NULL,
  `booked_by` int(11) NOT NULL,
  `booked_for_service` int(11) NOT NULL,
  `at_counter` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `slot` int(11) NOT NULL,
  `appointment_status` enum('scheduled','completed','cancelled','no-show') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`appointment_id`, `booked_by`, `booked_for_service`, `at_counter`, `appointment_date`, `slot`, `appointment_status`, `created_at`, `updated_at`) VALUES
(4, 1, 1, 1, '2025-11-12', 1, 'completed', '2025-11-09 10:08:19', '2025-11-10 22:55:19'),
(5, 1, 1, 1, '2025-11-24', 1, 'scheduled', '2025-11-22 08:18:18', '2025-12-08 16:58:33'),
(9, 1, 12, 3, '2025-11-27', 1, 'scheduled', '2025-11-26 13:01:10', '2025-11-26 13:01:10'),
(10, 1, 12, 3, '2025-12-09', 9, 'scheduled', '2025-12-08 15:54:16', '2025-12-08 15:54:16'),
(12, 1, 1, 1, '2025-12-22', 1, 'no-show', '2025-12-20 07:13:41', '2026-01-14 14:03:42'),
(13, 1, 1, 1, '2026-01-19', 1, 'scheduled', '2026-01-14 14:07:19', '2026-01-14 14:07:19');

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `booking_id` int(11) NOT NULL,
  `booked_date` date NOT NULL,
  `booked_slot` int(11) NOT NULL,
  `appointment` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`booking_id`, `booked_date`, `booked_slot`, `appointment`, `created_at`) VALUES
(3, '2025-11-10', 1, 4, '2025-11-09 10:08:19'),
(4, '2025-11-24', 1, 5, '2025-11-22 08:18:18'),
(5, '2025-11-27', 1, 9, '2025-11-26 13:01:10'),
(6, '2025-12-09', 9, 10, '2025-12-08 15:54:16'),
(7, '2025-12-22', 1, 12, '2025-12-20 07:13:41'),
(8, '2026-01-19', 1, 13, '2026-01-14 14:07:19');

-- --------------------------------------------------------

--
-- Table structure for table `counter`
--

CREATE TABLE `counter` (
  `counter_id` int(11) NOT NULL,
  `center_id` int(11) NOT NULL,
  `counter_name` varchar(100) NOT NULL,
  `service_handled` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`service_handled`)),
  `is_active` tinyint(1) DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `counter`
--

INSERT INTO `counter` (`counter_id`, `center_id`, `counter_name`, `service_handled`, `is_active`, `updated_at`) VALUES
(1, 1, 'Counter A - Visa Services', '[1, 2]', 1, '2025-11-26 11:55:02'),
(2, 1, 'Counter B - Visa Services', '[1, 2]', 1, '2025-11-26 12:56:32'),
(3, 1, 'Counter C - Passport Services', '[12,11]', 1, '2025-11-26 08:27:27'),
(4, 1, 'Counter D - Document Authentication', '[3]', 1, '2026-01-15 11:30:43');

-- --------------------------------------------------------

--
-- Table structure for table `miscellaneous_applications`
--

CREATE TABLE `miscellaneous_applications` (
  `id` int(11) NOT NULL,
  `application_id` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `service_id` int(11) NOT NULL,
  `form_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`form_data`)),
  `filled_pdf_path` varchar(500) DEFAULT NULL,
  `status` enum('submitted','in-progress','approved','rejected','completed') DEFAULT 'submitted',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `admin_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `notification_id` varchar(50) NOT NULL,
  `type` enum('email','sms','push') DEFAULT 'email',
  `recipient_email` varchar(255) DEFAULT NULL,
  `recipient_phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `template_id` varchar(100) DEFAULT NULL,
  `application_id` varchar(50) DEFAULT NULL,
  `appointment_id` varchar(50) DEFAULT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `status` enum('pending','sent','failed','bounced') DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `notification_id`, `type`, `recipient_email`, `recipient_phone`, `subject`, `content`, `template_id`, `application_id`, `appointment_id`, `user_id`, `status`, `sent_at`, `error_message`, `created_at`) VALUES
(1, 'NOTIFA9D4B9922EB1', 'email', 'ujwalthakkar020@gmail.com', NULL, 'Appointment Confirmation', 'Dear ujwal thakkar, your appointment has been confirmed.', 'appointment_confirmed', NULL, '1', '1', 'pending', NULL, NULL, '2025-11-09 09:29:59'),
(2, 'NOTIFA1B3C922222E', 'email', 'ujwalthakkar020@gmail.com', NULL, 'Appointment Confirmation', 'Dear ujwal thakkar, your appointment has been confirmed.', 'appointment_confirmed', NULL, '3', '1', 'pending', NULL, NULL, '2025-11-09 09:33:36'),
(3, 'NOTIFC9FA77E644A1', 'email', 'ujwalthakkar020@gmail.com', NULL, 'Appointment Confirmation', 'Dear ujwal thakkar, your appointment has been confirmed.', 'appointment_confirmed', NULL, '4', '1', 'pending', NULL, NULL, '2025-11-09 10:08:19'),
(4, 'NOTIF50B1DC4835AB', 'email', 'ujwalthakkar020@gmail.com', NULL, 'Appointment Confirmation', 'Dear ujwal thakkar, your appointment has been confirmed.', 'appointment_confirmed', NULL, '5', '1', 'pending', NULL, NULL, '2025-11-22 08:18:18'),
(5, 'NOTIF5288B65BBE8B', 'email', 'ujwalthakkar020@gmail.com', NULL, 'Appointment Confirmation', 'Dear ujwal thakkar, your appointment has been confirmed.', 'appointment_confirmed', NULL, '9', '1', 'pending', NULL, NULL, '2025-11-26 13:01:10'),
(6, 'NOTIF3820F5ECA319', 'email', 'ujwalthakkar020@gmail.com', NULL, 'Appointment Confirmation', 'Dear ujwal thakkar, your appointment has been confirmed.', 'appointment_confirmed', NULL, '10', '1', 'pending', NULL, NULL, '2025-12-08 15:54:16'),
(7, 'NOTIF2232C1686F0C', 'email', 'ujwalthakkar020@gmail.com', NULL, 'Appointment Confirmation', 'Dear ujwal thakkar, your appointment has been confirmed.', 'appointment_confirmed', NULL, '12', '1', 'pending', NULL, NULL, '2025-12-20 07:13:41'),
(8, 'NOTIFD9D5C4A22C4B', 'email', 'ujwalthakkar020@gmail.com', NULL, 'Appointment Confirmation', 'Dear ujwal thakkar, your appointment has been confirmed.', 'appointment_confirmed', NULL, '13', '1', 'pending', NULL, NULL, '2026-01-14 14:07:19');

-- --------------------------------------------------------

--
-- Table structure for table `notification_templates`
--

CREATE TABLE `notification_templates` (
  `id` int(11) NOT NULL,
  `template_id` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('email','sms','push') NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variables`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification_templates`
--

INSERT INTO `notification_templates` (`id`, `template_id`, `name`, `type`, `category`, `subject`, `content`, `variables`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'app_submitted', 'Application Submitted', 'email', 'application', 'Application Submitted Successfully', 'Dear {{applicant_name}}, your application {{application_id}} has been submitted successfully.', NULL, 1, '2025-11-05 13:53:28', '2025-11-05 13:53:28'),
(2, 'app_approved', 'Application Approved', 'email', 'application', 'Application Approved', 'Dear {{applicant_name}}, your application {{application_id}} has been approved.', NULL, 1, '2025-11-05 13:53:28', '2025-11-05 13:53:28'),
(3, 'appointment_confirmed', 'Appointment Confirmed', 'email', 'appointment', 'Appointment Confirmation', 'Dear {{client_name}}, your appointment on {{appointment_date}} at {{appointment_time}} is confirmed at {{center_name}}, Counter {{counter_number}}.', NULL, 1, '2025-11-05 13:53:28', '2025-11-05 13:53:28'),
(4, 'appointment_reminder', 'Appointment Reminder', 'email', 'appointment', 'Appointment Reminder - Tomorrow', 'Dear {{client_name}}, this is a reminder for your appointment tomorrow at {{appointment_time}} at {{center_name}}, Counter {{counter_number}}.', NULL, 1, '2025-11-05 13:53:28', '2025-11-05 13:53:28'),
(5, 'appointment_cancelled', 'Appointment Cancelled', 'email', 'appointment', 'Appointment Cancelled', 'Dear {{client_name}}, your appointment on {{appointment_date}} at {{appointment_time}} has been cancelled.', NULL, 1, '2025-11-05 13:53:28', '2025-11-05 13:53:28');

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `service_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `processing_time` varchar(50) DEFAULT NULL,
  `fees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`fees`)),
  `required_documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`required_documents`)),
  `eligibility_requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`eligibility_requirements`)),
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service`
--

INSERT INTO `service` (`service_id`, `category`, `title`, `description`, `processing_time`, `fees`, `required_documents`, `eligibility_requirements`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'Visa', 'Tourist Visa', 'Application for tourist visa', '5-7 business days', '[{\"type\":\"standard\",\"amount\":50},{\"type\":\"express\",\"amount\":100}]', '[\"Passport copy\", \"Photograph\", \"Travel itinerary\"]', '[\"Valid passport\", \"Sufficient funds\"]', 1, 1, '2025-11-08 13:08:00', '2025-12-19 03:48:33'),
(2, 'Visa', 'Business Visa', 'Application for business visa', '7-10 business days', '[{\"type\":\"standard\",\"amount\":75},{\"type\":\"express\",\"amount\":150}]', '[\"Passport copy\", \"Photograph\", \"Business invitation letter\"]', '[\"Valid passport\", \"Business registration\"]', 1, 2, '2025-11-08 13:08:00', '2025-12-19 03:48:39'),
(3, 'Document Verification', 'Document Authentication', 'Authentication of official documents', '3-5 business days', '[{\"type\":\"standard\", \"amount\": 75}, {\"type\":\"express\", \"amount\": 150}]', '[\"Original documents\", \"ID proof\"]', '[\"Documents must be original\"]', 1, 3, '2025-11-08 13:08:00', '2025-11-13 18:12:53'),
(11, 'Passport', 'Passport Renewal', 'Renew your passport', '4-6 weeks', '[{\"type\":\"standard\",\"amount\":100}]', '[\"Old passport\",\"Photos\"]', '[\"Citizen\"]', 1, 1, '2025-11-26 05:46:37', '2025-11-26 05:46:37'),
(12, 'Passport', 'new passport', 'apply for a new passport', '4-6 weeks', '[{\"type\":\"standard\",\"amount\":150}]', '[\"Photo\",\"Id\",\"Address proof\"]', '[\"Age 18+\"]', 1, 1, '2025-11-26 06:52:21', '2025-11-26 06:57:55'),
(14, 'Miscellaneous', 'Marriage Certificate', 'Apply for Marriage Certificate', '1 week', '[{\"type\":\"standard\",\"amount\":450}]', '[\"Passport\",\"Visa\",\"Address Proof\"]', '[\"Any\"]', 1, 99, '2026-01-15 19:08:38', '2026-01-15 19:08:38');

-- --------------------------------------------------------

--
-- Table structure for table `service_details`
--

CREATE TABLE `service_details` (
  `service_id` int(11) NOT NULL,
  `overview` longtext DEFAULT NULL,
  `visa_fees` longtext DEFAULT NULL,
  `documents_required` longtext DEFAULT NULL,
  `photo_specifications` longtext DEFAULT NULL,
  `processing_time` longtext DEFAULT NULL,
  `downloads_form` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_details`
--

INSERT INTO `service_details` (`service_id`, `overview`, `visa_fees`, `documents_required`, `photo_specifications`, `processing_time`, `downloads_form`, `created_at`, `updated_at`) VALUES
(1, '<p><strong>Overview</strong></p><p></p><p>This visa allows you to travel to India for holidays and social functions.</p><ul><li><p>Generally, the High Commission of India issues a visa valid for 6 months; however these are not given by default. Applicants can be granted a lesser duration if a lesser duration is written on the application form and/if the consular services decide to do so. In such cases, visa fees remain unchanged. Please note that the Tourist visa cannot be exchanged and cannot be extended in India.</p></li></ul><p>The following persons are not eligible for tourist visas:</p><p>Certain professions require a “journalist” visa. The sectors of activity concerned by this requirement are: cinema, television, media, writing, publishing, press, photography, communication, advertising… (This list is non-exhaustive).</p><p>In case your profession falls in one of these categories please refer to the <a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"text-blue-600 underline c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/JOURNALIST-VISA-CHECK-LIST.pdf\"><u>Journalist Visa checklist </u></a>.</p><p>Pakistani nationals must apply for a Visit Visa. In order to check the documents required please<a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"text-blue-600 underline c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/VISA-FORM-PAKISTAN-Tourist.pdf\"><u> click here </u></a>.</p><p>All applicants going on a cruise must apply for a <strong>Regular Visa</strong> application.</p><p>Please note: the validity of a visa begins on the day it is issued by the High Commission of India and not on the date of departure. Applications <strong>will not</strong> be accepted more than 30 days prior to departure.</p><p></p>', '<p><strong>Visa Fees</strong></p><p>Your payment allows you to deposit an application. The latter can be accepted, refused or modified by the High Commission of India in its full right. Once fees have been paid, they cannot be refunded, even in a case of an application refusal or cancellation.</p><p>The Visa Application Centre has an additional fee requirement:</p><p>Service Fee: R 116.35</p><p>Passport and Consular Service Fee. R165.61</p><p>Above fees are over and above the Consular Fee.</p><p>All fees inclusive of VAT.</p><p>Modes of payment</p><ul><li><p>Credit Card or Debit Card</p></li><li><p>Deposit into the bank account as per the below details.</p></li><li><p>Please note EFT payments are not accepted<br></p><p>Name of Bank: Nedbank</p><p>Account Number: 1012505804</p><p>Branch Code: 198 765</p><p>Account Name: VFS Visa Processing SA Pty Ltd</p><p>Please use passport number as beneficiary reference number on payment.</p></li></ul><p>Fees Chart</p><p>The price of your application is made up of fixed service charges and variable consular charges. Please consult the fees chart below in order to calculate the total amount to pay.</p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/pdf_img.png\" alt=\"pdf img\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/Visa-Fee-SA-Nationals-Dec-2025.pdf\"><u>Click here for Visa fees chart</u></a></p>', '<p><strong>Documents Required</strong></p><p>Kindly print the check list which corresponds with the visa category you wish to apply for and attach it with your application. It must be filled in and signed. Incomplete applications will not be accepted.</p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/pdf_img.png\" alt=\"pdf img\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/tourist-visa-checklist.pdf\"><u>Print Tourist Visa Checklist</u></a></p>', '<p><strong>Photo Specifications</strong></p><p></p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/photoSpecs.jpg\" alt=\"photo\"><p><strong>Photographs should be in colour and strictly as per the specifications below.</strong></p><ul><li><p>Un-mounted - 2 inch x 2 inch in size.</p></li><li><p>Face should cover about 70 to 80% of the photo area.</p></li><li><p>Frame Subject with Full Face, Front view, Eyes open</p></li><li><p>Taken within the last 6 months to reflect your current appearance.</p></li><li><p>The photographs must be clear, well defined and taken against a plain white background.</p></li><li><p>Appropriate brightness and contrast showing your skin tones naturally</p></li><li><p>Even lighting (no shadows across or behind the face)</p></li><li><p>Face must be square to the camera with a neutral expression, neither frowning nor smiling and with your mouth closed.</p></li><li><p>If you must wear a head covering for religious reasons, make sure your full facial features are not obscured.</p></li><li><p>Non-tinted prescription glasses are allowed as long as your eyes are clearly visible. Make sure that the frame does not cover any part of your eyes. Sunglasses are not acceptable.</p></li><li><p>Headphones, wireless hands-free devices, or similar items are not acceptable in your photo.</p></li><li><p>Photograph for Child/babies - The photograph should show the baby or child awake, looking straight at the camera with mouth closed and nothing covering the mouth. It should also show both edges of the face clearly (no toys, blankets, chair backs or other people visible) and no hair across the eyes.</p></li></ul><p><strong>Please Note:</strong> A photo service is available in all of our centres.</p>', '<p><strong>Processing Time</strong></p><p>The High Commission of India and VFS Visa Processing SA Pty Ltd cannot, in any circumstances, be held responsible for any applications which are not completed in time for your intended date of departure. Kindly note that obtaining a visa is not automatic; your application may be accepted, modified or refused by the High Commission of India and/or its consulate in its full right.</p><p>Applications processed individually</p><p>Each application is processed individually by the High Commission of India: as a result processing times may vary between applications that appear to be similar.</p><p>Minimum processing times</p><p>The company VFS Visa Processing SA Pty Ltd has no influence over the processing times of a visa application: these depend essentially on the High Commission of India. We are therefore not in a position to guarantee maximum processing times. We are only able to communicate minimum processing times, provided for information purposes only.</p><p>The mission/Consulate makes all possible efforts to process the visa applications within the stipulated timeframe yet this must not be construed as an obligation on the part of Mission/Consulate.</p>', '<p><strong>Download Forms</strong></p><p>Online Application Form</p><p>All applicants are required to complete the online application form and submit the same at the preferred India visa application centre. Please&nbsp;<a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://indianvisaonline.gov.in/visa/index.html\"><u>click here</u></a>&nbsp;to complete your application form online.</p><p></p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/pdf_img.png\" alt=\"pdf\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/DECLARATION-FORM1.pdf\"><u>Declaration form (Mandatory for all applicants)</u></a></p><p><strong>Additional Forms -</strong></p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/pdf_img.png\" alt=\"pdf img\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/Declaration-for-Ex-Indian-status.pdf\"><u>Declaration for Ex-Indian status</u></a></p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/pdf_img.png\" alt=\"pdf img\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/CONSENT-FORM-FOR-MINORS.pdf\"><u>Consent form for minors</u></a></p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/pdf_img.png\" alt=\"pdf img\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/Consent-form-minors-with-one-parent.pdf\"><u>Consent form – minors with one parent.</u></a></p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/pdf_img.png\" alt=\"pdf img\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/DECLARATION-FORM-FOR-SECOND-PASSPORT.pdf\"><u>Declaration form for second passport)</u></a></p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/pdf_img.png\" alt=\"pdf img\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/Foreign-National-Form-Recent.pdf\"><u>Foreign National Form</u></a></p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/pdf_img.png\" alt=\"pdf img\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/optional-courier-delivery-undertaking-form.pdf\"><u>Optional Courier Delivery Undertaking form</u></a></p><p><br></p><p>Scheduling Of An Appointment.</p><p>Kindly note it is mandatory to schedule an appointment prior to submission of any application, Please <a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://row1.vfsglobal.com/GlobalAppointment/Account/RegisteredLogin?q=shSA0YnE4pLF9Xzwon/x/PpYJNc6tVYvHRijnlGjPCDn4kgIB9K1bSEdoAvrvzQ01ULf2rsPBDtXThniRiprEMbvoLEpn4ak0LJ2GEC+JsQ=\"><u>click here</u></a> to schedule.</p><p><strong>Collection Of Passports</strong></p><p>Applicants are advised to collect their passports personally strictly upon producing their receipt at the time of application. An Authorization letter should be given to a representative to collect the passport and who is able to check all particulars in the passport. The collector must carry his/her ID and produce the same on demand. Agent/collector needs to produce an authorization letter from the applicant.</p><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/Authorization-Letter.pdf\"><u>Click here</u></a> for the authorization letter.</p>', '2025-12-19 10:34:30', '2025-12-21 05:52:45'),
(2, '<p><strong>Overview</strong></p><p>This visa allows you to travel to India for the below purposes (this list is non-exhaustive):</p><ul><li><p>Searching for customers, selling products</p></li><li><p>Searching for suppliers, buying products, giving specifications, checking orders, quality control, audits</p></li><li><p>Visiting an exhibition or trade fair, exhibiting products in a trade fair</p></li><li><p>Managing human resources, recruiting manpower</p></li><li><p>Attending business meetings or technical meetings</p></li><li><p>Carrying out plant or machinery repairs which are under warranty or subject to a maintenance contract</p></li><li><p>Installing or commissioning equipment or machinery supplied under contract</p></li><li><p>Providing a technical support or a transfer of know-how which is charged to the Indian company</p></li><li><p>Giving a training or carrying out a coaching activity</p></li><li><p>Attending a board meeting, exploring business venture opportunities, setting up a company</p></li><li><p>Business partners or directors managing an Indian company set up in their name</p></li><li><p>Taking part in an official business tour relative to projects of national importance</p></li></ul><p></p><p><strong>The following persons are not eligible for business visas:</strong></p><p></p><ul><li><p>Applicants wishing to work in India and live there as an expatriate should apply for an Employment Visa</p></li><li><p>Dependant/s of Business Visa Holders is required to apply for Business Dependant Visa and follow the Business Visa Checklist.</p></li></ul><ul><li><p>The High Commission of India and its Consulates in South Africa reserve the right to grant the visa for a shorter duration than that requested. In such cases no refund of fees paid would be made.</p></li></ul><p></p><p><strong>Please note:</strong> the validity of a visa begins on the day it is issued by the High Commission of India and not on the date of departure. Applicants should apply as per the jurisdictions mentioned below. Applications will not be accepted more than 30 days prior to departure.</p><p></p>', '<ul><li><p><strong>Visa Fees</strong></p><p>Your payment allows you to deposit an application. The latter can be accepted, refused or modified by the High Commission of India in its full right. Once fees have been paid, they cannot be refunded, even in a case of an application refusal or cancellation.</p><p>The Visa Application Centre has an additional fee requirement:</p><p>Service Fee: R 116.35</p><p>Passport and Consular Service Fee. R165.61</p><p>Above fees are over and above the Consular Fee.</p><p>All fees inclusive of VAT.</p><p>Modes of payment</p></li><li><p>Credit Card or Debit Card</p></li><li><p>Deposit into the bank account as per the below details.</p></li><li><p>Please note EFT payments are not accepted<br><br></p><p>Name of Bank: Nedbank</p><p>Account Number: 1012505804</p><p>Branch Code: 198 765</p><p>Account Name: VFS Visa Processing SA Pty Ltd</p><p>Please use passport number as beneficiary reference number on payment.</p></li></ul><p>Fees Chart</p><p>The price of your application is made up of fixed service charges and variable consular charges. Please consult the fees chart below in order to calculate the total amount to pay.</p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/pdf_img.png\" alt=\"pdf img\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/Visa-Fee-SA-Nationals-Dec-2025.pdf\"><u>Click here for Visa fees chart</u></a></p><p><br></p>', '<p><strong>Documents Required</strong></p><p>Kindly print the check list which corresponds with the visa category you wish to apply for and attach it with your application. It must be filled in and signed. Incomplete applications will not be accepted.</p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/pdf_img.png\" alt=\"pdf img\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/Business-checklist-new.pdf\"><u>business checklist</u></a></p>', '<p><strong>Photo Specifications</strong></p><p></p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/photoSpecs.jpg\" alt=\"Photo\"><p>Photographs should be in colour and strictly as per the specifications below.</p><ul><li><p>Un-mounted - 2 inch x 2 inch in size.</p></li><li><p>Face should cover about 70 to 80% of the photo area.</p></li><li><p>Frame Subject with Full Face, Front view, Eyes open</p></li><li><p>Taken within the last 6 months to reflect your current appearance.</p></li><li><p>The photographs must be clear, well defined and taken against a plain white background.</p></li><li><p>Appropriate brightness and contrast showing your skin tones naturally</p></li><li><p>Even lighting (no shadows across or behind the face)</p></li><li><p>Face must be square to the camera with a neutral expression, neither frowning nor smiling and with your mouth closed.</p></li><li><p>If you must wear a head covering for religious reasons, make sure your full facial features are not obscured.</p></li><li><p>Non-tinted prescription glasses are allowed as long as your eyes are clearly visible. Make sure that the frame does not cover any part of your eyes. Sunglasses are not acceptable.</p></li><li><p>Headphones, wireless hands-free devices, or similar items are not acceptable in your photo.</p></li><li><p>Photograph for Child/babies - The photograph should show the baby or child awake, looking straight at the camera with mouth closed and nothing covering the mouth. It should also show both edges of the face clearly (no toys, blankets, chair backs or other people visible) and no hair across the eyes.</p></li></ul><p><strong>Please Note:</strong></p><p>A photo service is available in all of our centres.</p>', '<ul><li><p><strong>Processing Time</strong></p><p>The High Commission of India and VFS Visa Processing SA Pty Ltd cannot, in any circumstances, be held responsible for any applications which are not completed in time for your intended date of departure. Kindly note that obtaining a visa is not automatic; your application may be accepted, modified or refused by the High Commission of India and/or its consulate in its full right.</p><p>Applications processed individually</p><p>Each application is processed individually by the High Commission of India: as a result processing times may vary between applications that appear to be similar.</p><p>Minimum processing times</p><p>The company VFS Visa Processing SA Pty Ltd has no influence over the processing times of a visa application: these depend essentially on the High Commission of India. We are therefore not in a position to guarantee maximum processing times. We are only able to communicate minimum processing times, provided for information purposes only.</p><p>The mission/Consulate makes all possible efforts to process the visa applications within the stipulated timeframe yet this must not be construed as an obligation on the part of Mission/Consulate.</p><ul><li><p>To process an application for South African national minimum 5 working days from the date of submission.</p></li><li><p>In case of foreign nationals minimum processing time is 7 working days.</p></li><li><p>Processing time of applications received from Pakistani nationals and persons of Pakistani origin will be approximately 7-8 weeks.</p></li></ul></li></ul><p><br></p>', '<p><strong>Download Forms</strong></p><p>Online Application Form</p><p>All applicants are required to complete the online application form and submit the same at the preferred India visa application centre. Please&nbsp;<a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://indianvisaonline.gov.in/visa/index.html\"><u>click here</u></a>&nbsp;to complete your application form online.</p><p></p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/pdf_img.png\" alt=\"pdf\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/DECLARATION-FORM1.pdf\"><u>Declaration form (Mandatory for all applicants)</u></a></p><p><strong>Additional Forms -</strong></p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/pdf_img.png\" alt=\"pdf img\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/Declaration-for-Ex-Indian-status.pdf\"><u>Declaration for Ex-Indian status</u></a></p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/pdf_img.png\" alt=\"pdf img\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/CONSENT-FORM-FOR-MINORS.pdf\"><u>Consent form for minors</u></a></p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/pdf_img.png\" alt=\"pdf img\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/Consent-form-minors-with-one-parent.pdf\"><u>Consent form – minors with one parent.</u></a></p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/pdf_img.png\" alt=\"pdf img\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/DECLARATION-FORM-FOR-SECOND-PASSPORT.pdf\"><u>Declaration form for second passport)</u></a></p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/pdf_img.png\" alt=\"pdf img\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/Additional-Form-southafrica.pdf\"><u>Foreign National Form</u></a></p><img src=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/images/pdf_img.png\" alt=\"pdf img\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/optional-courier-delivery-undertaking-form.pdf\"><u>Optional Courier Delivery Undertaking form</u></a></p><p><br></p><p>Scheduling Of An Appointment.</p><p>Kindly note it is mandatory to schedule an appointment prior to submission of any application, Please <a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://row1.vfsglobal.com/GlobalAppointment/Account/RegisteredLogin?q=shSA0YnE4pLF9Xzwon/x/PpYJNc6tVYvHRijnlGjPCDn4kgIB9K1bSEdoAvrvzQ01ULf2rsPBDtXThniRiprEMbvoLEpn4ak0LJ2GEC+JsQ=\"><u>click here</u></a> to schedule.</p><p><strong>Collection Of Passports</strong></p><p>Applicants are advised to collect their passports personally strictly upon producing their receipt at the time of application. An Authorization letter should be given to a representative to collect the passport and who is able to check all particulars in the passport. The collector must carry his/her ID and produce the same on demand. Agent/collector needs to produce an authorization letter from the applicant.</p><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://visa.vfsglobal.com/one-pager/India/SouthAfrica/visa-category/pdf/Authorization-Letter.pdf\"><u>Click here</u></a> for the authorization letter.</p>', '2025-12-19 11:32:55', '2025-12-21 05:53:26'),
(12, '<p><strong>Overview</strong></p><p>In compliance with the regulations of International Civil Aviation Organization (ICAO), only machine readable printed passports are now to be accepted for international travel.</p><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/Guidelines-for-ICAO.pdf\"><u>Click here</u></a> for Guidelines for ICAO compliant photographs for passport applications.</p><p>A new passport needs to be obtained where the existing Passports have run out of available pages for visas etc. A new passport can be issued one year before final expiry or on final expiry of any passport issued for full validity of 10 years (5 years in the case of minors).</p><p><strong>NB: All applicants are required to be present at the Visa Application Centre for individual processing of applications.</strong></p><p>Reference instructions regarding issue/re-issue of Passports to Indian citizens:</p><ul><li><p>A New / fresh Police Verification Report is mandatory for all cases of issue/re-issue of Passports and in certain cases passports will be issued/re-issued only after receipt of fresh Police Verification Report from the Indian authorities.</p></li></ul><p>The following categories of applicants applying for fresh/re-issue of Passports may please be asked to submit documentary proof in support of such claim:</p><ol type=\"i\"><li><p>Applicants who have been resident abroad (outside India) continuously for over 5 years and hold a Permanent Residence / Work Permit / Green Card or Permanent Visa;</p></li><li><p>Application in respect of minors for a 5-year valid passport where the minor is born abroad and never resided in India during the preceding five years when application for re-issue is submitted by both parents along with <a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/3-Annexure-D-Affidavit-for-Child-Passport.pdf\"><u>Annexure \'D\'</u></a>; and</p></li><li><p>In case of application in respect of minors for fresh/re-issue of passport, the parent(s) need to submit an undertaking that the child has not acquired any foreign passport or foreign citizenship and that on acquiring of foreign citizenship, the Indian passport will be surrendered.</p></li></ol><p>Cases of passport re-issue can be considered as follows:</p><ul><li><p>If there are insufficient pages in the current passport / passport is lost or damaged</p></li><li><p>the validity of visa/ resident card has also expired</p></li><li><p>If the passport is expired and the visa/resident card is valid</p></li><li><p>If adding / deleting spouse name / change of name subsequent to marriage/divorce death of spouse etc.</p></li><li><p>If the applicant has changed their signature or any change of address</p></li><li><p>If the applicant has changed their appearance</p></li><li><p>Apply for reissue of passport prior to Visa/permit expiring.</p></li><li><p>In case for lost passport, applicant to be present for submission mandatorily.</p></li></ul><p>Applicants should apply as per the jurisdictions mentioned below.</p><table style=\"min-width: 50px;\"><colgroup><col style=\"min-width: 25px;\"><col style=\"min-width: 25px;\"></colgroup><tbody><tr><th colspan=\"1\" rowspan=\"1\"><p>Jurisdiction</p></th><th colspan=\"1\" rowspan=\"1\"><p>Provinces</p></th></tr><tr><td colspan=\"1\" rowspan=\"1\"><p>Johannesburg</p></td><td colspan=\"1\" rowspan=\"1\"><p>Gauteng, Limpopo, North West and Mpumalanga provinces</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p>Durban</p></td><td colspan=\"1\" rowspan=\"1\"><p>Kwa Zulu Natal, Free State and Eastern Cape Provinces</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p>Cape Town</p></td><td colspan=\"1\" rowspan=\"1\"><p>Western Cape and Northern Cape Province</p></td></tr></tbody></table><p></p>', '<p><strong>Visa Fees</strong></p><p>Your payment allows you to deposit an application. The latter can be accepted, refused or modified by the High Commission of India in its full right. Once fees have been paid, they cannot be refunded, even in a case of an application refusal or cancellation.</p><p><strong>Modes of payment</strong> – No EFT’s Payments are accepted at VFS Centres.</p><ul><li><p>Credit Card or Debit Card at the centre.</p></li><li><p>Cash Deposits into the below bank account:<br><br></p><p>Name of Bank: Nedbank</p><p>Account Number: 1012505804</p><p>Branch Code: 198 765</p><p>Account Name: VFS Visa Processing SA Pty Ltd</p></li></ul><p>Please use passport number as beneficiary reference number on payment.</p><p><strong>Revised Passport, Travel Document and Consular Services Fee effective from 1st April 2025</strong></p><img src=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/images/pdf_img.png\" alt=\"pdf img\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/revise-ppt-pcc-fee-new.pdf\"><u>Click here for Visa fees chart</u></a></p>', '<p><strong>Documents Required</strong></p><p>Kindly print the check list which corresponds with the service you wish to apply for and attach it with your application. It must be filled in and signed. Incomplete applications will not be accepted.</p><p></p><img src=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/images/pdf_img.png\" alt=\"pdf img\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/Passport-Checklist-Updated-June-2025.pdf\"><u>Passport Issue/Re-Issue Checklist</u></a></p>', '<p>Photo Specifications</p><p></p><img src=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/images/photoSpecs.jpg\" alt=\"photo\"><p>Photographs should be in colour and strictly as per the specifications below.</p><ul><li><p>Un-mounted - 2 inch x 2 inch in size.</p></li><li><p>Face should cover about 70 to 80% of the photo area.</p></li><li><p>Frame Subject with Full Face, Front view, Eyes open</p></li><li><p>Taken within the last 6 months to reflect your current appearance.</p></li><li><p>The photographs must be clear, well defined and taken against a plain white background.</p></li><li><p>Appropriate brightness and contrast showing your skin tones naturally</p></li><li><p>Even lighting (no shadows across or behind the face)</p></li><li><p>Face must be square to the camera with a neutral expression, neither frowning nor smiling and with your mouth closed.</p></li><li><p>If you must wear a head covering for religious reasons, make sure your full facial features are not obscured.</p></li><li><p>Non-tinted prescription glasses are allowed as long as your eyes are clearly visible. Make sure that the frame does not cover any part of your eyes. Sunglasses are not acceptable.</p></li><li><p>Headphones, wireless hands-free devices, or similar items are not acceptable in your photo.</p></li><li><p>Photograph for Child/babies - The photograph should show the baby or child awake, looking straight at the camera with mouth closed and nothing covering the mouth. It should also show both edges of the face clearly (no toys, blankets, chair backs or other people visible) and no hair across the eyes.</p></li></ul><p><strong>Please Note:</strong></p><p>A photo service is available in all of our centres.</p>', '<p>Processing Time</p><p>The High Commission of India / Consulate and VFS Visa Processing SA Pty Ltd cannot, in any circumstances, be held responsible for any applications which are not completed in time for your intended date of departure. Kindly note that obtaining a passport is not automatic; your application may be accepted, modified or refused by the High Commission of India / Consulate in its full right.</p><p>Applications processed individually</p><p>Each application is processed individually by the High Commission of India / consulate: as a result processing times may vary between applications that appear to be similar. It is particularly frequent for families and groups who have submitted their applications simultaneously to receive their passports with a few days gap.</p><p>Minimum processing times</p><p>The company VFS Visa Processing SA Pty Ltd has no influence over the processing times of a passport application: these depend essentially on the High Commission of India. We are therefore not in a position to guarantee maximum processing times. We are only able to communicate minimum processing times, provided for information purposes only.</p><table style=\"min-width: 50px;\"><colgroup><col style=\"min-width: 25px;\"><col style=\"min-width: 25px;\"></colgroup><tbody><tr><th colspan=\"2\" rowspan=\"1\"><p>MINIMUM PROCESSING TIMES FOR A PASSPORT APPLICATION</p></th></tr><tr><th colspan=\"1\" rowspan=\"1\"><p>JURISDICTION</p></th><th colspan=\"1\" rowspan=\"1\"><p>MINIMUM PROCESSING TIME</p></th></tr><tr><td colspan=\"1\" rowspan=\"1\"><p>Johannesburg<br>(Gauteng, Limpopo, North West and Mpumalanga provinces)</p></td><td colspan=\"1\" rowspan=\"1\"><p>Minimum 1 month but may take longer</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p>Durban<br>(Kwa Zulu Natal, Free State and Eastern Cape Provinces)</p></td><td colspan=\"1\" rowspan=\"1\"><p>Minimum 1 month but may take longer</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p>Cape Town<br>(Western Cape and Northern Cape Province)</p></td><td colspan=\"1\" rowspan=\"1\"><p>Minimum 1 month but may take longer</p></td></tr></tbody></table><p>The High Commission of India / consulate reserves the right to withhold any application submitted for an in-depth examination</p><p>For locating the centres at above mentioned locations as per the jurisdiction and for timing of submission of the application kindly refer <a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://services.vfsglobal.com/zaf/en/ind/contact-us\"><u>contact us</u></a>.</p>', '<p><strong>Download Forms</strong></p><p>ONLINE APPLICATION FORM</p><p>Link: <a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://portal6.passportindia.gov.in/\"><u>Click Here</u></a></p><p>Important Notice: All applying in the Durban, Johannesburg, Cape Town offices are required to use the link <a target=\"_blank\" rel=\"noopener noreferrer nofollow\" href=\"https://embassy.passportindia.gov.in\"><u>https://embassy.passportindia.gov.in</u></a> to complete the online application form for Passport and PCC applications.</p><p>The Government of India has now introduced the Online Registration Form for all overseas Indian nationals seeking passport services. The procedure would now enable the Indian nationals residing overseas to obtain printed passports.</p><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/SOP-applicant-portal.pdf\"><u>Click here</u></a> for step by step instructions on how to fill in the online application form.</p><p>As a private outsourcing partner of the High Commission of India, Pretoria, VFS Visa Processing SA Pty Ltd has no influence over the content of the website or the form and are not in a position to modify its content or structure.</p><p>Instructions to fill in the online application form <a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://portal6.passportindia.gov.in/\"><u>Click Here</u></a></p><p>Step 1:</p><p>Select “Online Registration Form”</p><p>Step 2:</p><p>Choose Mission: as per the jurisdictions mentioned below</p><table style=\"min-width: 50px;\"><colgroup><col style=\"min-width: 25px;\"><col style=\"min-width: 25px;\"></colgroup><tbody><tr><th colspan=\"1\" rowspan=\"1\"><p>Jurisdiction</p></th><th colspan=\"1\" rowspan=\"1\"><p>Provinces</p></th></tr><tr><td colspan=\"1\" rowspan=\"1\"><p>Johannesburg</p></td><td colspan=\"1\" rowspan=\"1\"><p>Gauteng, Limpopo, North West and Mpumalanga provinces</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p>Durban</p></td><td colspan=\"1\" rowspan=\"1\"><p>Kwa Zulu Natal, Free State and Eastern Cape Provinces</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p>Cape Town</p></td><td colspan=\"1\" rowspan=\"1\"><p>Western Cape and Northern Cape Province</p></td></tr></tbody></table><p>Step 3:</p><p>Fill details in online application form and click “Save &amp; Continue”</p><p>Step 4:</p><p>Click “Generate PDF”</p><p>Step 5:</p><p>Take printout of the PDF File</p><p>Step 6:</p><p>Fill blank columns by blue/black pen and sign on all three pages</p><p>Step 7:</p><p>Schedule an appointment.</p><p>Kindly note it is mandatory to schedule an appointment prior to submission of any application, Please <a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://row1.vfsglobal.com/GlobalAppointment/Account/RegisteredLogin?q=shSA0YnE4pLF9Xzwon/x/PpYJNc6tVYvHRijnlGjPCDn4kgIB9K1bSEdoAvrvzQ01ULf2rsPBDtXThniRiprEMbvoLEpn4ak0LJ2GEC+JsQ=\"><u>click here</u></a> to schedule.</p><p>Frequent errors / Important Information</p><ul><li><p>It is recommended to note the registration number for future use.</p></li><li><p>You should not to use any special characters or punctuation in any questions on the form: these could lead to errors during the processing of your application.</p></li><li><p>All the details in the application form should be as per your current passport unless you are requesting some changes in them. In case of a change, new details need to be filled in and required supporting documents need to be provided as per the checklist.</p></li><li><p>In case of a new born baby’s passport, details should be as per birth certificate and supporting documents as mentioned in the checklist.</p></li><li><p>In case of minor, Q28 needs to be filled in by parents by printing their name and signing.</p></li><li><p>Q29 is only for the signature / thumb impression of the applicant. In case applicant is unable to sign or provide thumb impression, Q 29 boxes need to be left blank. Parents cannot sign on behalf of the child in this column.</p></li><li><p>Where names of people are asked (parents, grandparents, spouse) please ensure to write their full name and not just the first name or surname.</p></li><li><p>No manual corrections should be made on the application form. In case of a mistake you will be required to fill a new form or modify the existing one online. Applicant will be responsible for any wrong entries in the application form</p></li><li><p>It is an offence under the Passports Act, 1967 to knowingly furnish false information or to suppress material information when applying for Passport Services.</p></li><li><p>If you observe printing mistake in your newly issued passport, please inform the centre immediately</p></li><li><p>In all cases of re-issue of passport applications (except in lost/stolen cases, where the prescribed procedure for new passport need to be followed) last held/expired passport in original needs to be submitted along with the duly filled-in application form and other required documents. Old Passports will be returned to the applicant after cancellation along with the new passport. Valid visas in the old passport(s) are not cancelled and can be used after cancellation of the passport until the date of expiry of the visa.</p></li><li><p>The International Civil Aviation Organisation (ICAO) has set a deadline of the 24th November 2015 for globally phasing out all non-Machine Readable Passports (MRPs). From 25th November 2015 onwards, foreign Governments may deny visa or entry to any person travelling with a non-Machine Readable Passport. All handwritten passports with pasted photos earlier issued by Government of India are considered non-Machine Readable Passports. All 20-years validity passports will also fall in this category. The government started issuing Machine Readable Passports since 2001. All new Indian passports are ICAO-compliant Machine Readable Passports. Indian citizens residing in France and holding handwritten passports as well as 20-years passports with validity beyond the 24th November 2015 should therefore apply for re-issue of passports and obtain Machine Readable Passports well before the deadline, in order to avoid any inconvenience in obtaining foreign visa or immigration problems.</p><p>Please note that if the supporting documents provided are not satisfactory, the applicant will be asked to provide more documentary evidence.</p></li></ul><p>Your application may require additional documents as per your situation or the age of the applicant. You may find all the additional forms on this page.</p><table style=\"min-width: 50px;\"><colgroup><col style=\"min-width: 25px;\"><col style=\"min-width: 25px;\"></colgroup><tbody><tr><th colspan=\"1\" rowspan=\"1\"><p><strong>Form (click on the title to print)</strong></p></th><th colspan=\"1\" rowspan=\"1\"><p><strong>Description</strong></p></th></tr><tr><td colspan=\"1\" rowspan=\"1\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/Declaration-by-application.pdf\"><u>Declaration Form</u></a></p></td><td colspan=\"1\" rowspan=\"1\"><p>Declaration Form to be filled by all applicants when submitting any application</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/ANNEXURE-C.pdf\"><u>Annexure C</u></a></p></td><td colspan=\"1\" rowspan=\"1\"><p>Affidavit for a single parent where child is born out of wedlock or other parent’s whereabouts is unknown</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/Passport-by-Woman.pdf\"><u>Change of name after Marriage</u></a></p></td><td colspan=\"1\" rowspan=\"1\"><p>Affidavit for name change after marriage</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/annexure-d-affidavit-for-child-passport.pdf\"><u>Annexure D</u></a></p></td><td colspan=\"1\" rowspan=\"1\"><p>Specimen Declaration by Applicant\'s parents</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/ANNEXURE-E.pdf\"><u>Annexure E</u></a></p></td><td colspan=\"1\" rowspan=\"1\"><p>Affidavit for change in name/deed poll</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/ANNEXURE-G.pdf\"><u>Annexure G</u></a></p></td><td colspan=\"1\" rowspan=\"1\"><p>Affidavit for a single parent unable to take the consent of other parent</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/ANNEXURE-H.pdf\"><u>Annexure H</u></a></p></td><td colspan=\"1\" rowspan=\"1\"><p>Declaration of parents if applicant is a minor / Affidavit required to be completed by applicant in case their minor child is applying for a passport in India or elsewhere and they are currently present in France.</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/ANNEXURE-K.pdf\"><u>Annexure K</u></a></p></td><td colspan=\"1\" rowspan=\"1\"><p>Disputed spouse</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/ANNEXURE-L.pdf\"><u>Annexure L</u></a></p></td><td colspan=\"1\" rowspan=\"1\"><p>Affidavit for lost/damaged passport</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/Personal-Particulars-Form-new.pdf\"><u>Personal Particulars Form</u></a></p></td><td colspan=\"1\" rowspan=\"1\"><p>Personal Particulars Form</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/3-Annexure-D-Affidavit-for-Child-Passport.pdf\"><u>Affidavit For Child Passport</u></a></p></td><td colspan=\"1\" rowspan=\"1\"><p>Specimen Declaration by Applicant’s Parents</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/Affidavit-For-Inclusion-Of-Spouse-Name-New.pdf\"><u>Affidavit Inclusion Of Spouse.</u></a></p></td><td colspan=\"1\" rowspan=\"1\"><p>Affidavit of Inclusion Of Spouse Form</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/expired-visa-permit-declaration.pdf\"><u>Expired VISA, Permit Declaration</u></a></p></td><td colspan=\"1\" rowspan=\"1\"><p>Declaration for expired Visa, Permit</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/postal-application-undertaking.pdf\"><u>Postal Application Undertaking</u></a></p></td><td colspan=\"1\" rowspan=\"1\"><p>Undertaking to be completed for postal applications only</p></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/optional-courier-delivery-undertaking-form.pdf\"><u>Courier Declaration</u></a></p></td><td colspan=\"1\" rowspan=\"1\"><p>Optional Courier Delivery Undertaking form</p></td></tr></tbody></table><p>COLLECTION OF PASSPORTS</p><p>Applicants are advised to collect their passports personally strictly upon producing their receipt at the time of application. An Authorization letter should be given to a representative to collect the passport and who is able to check all particulars in the passport. The collector must carry his/her ID and produce the same on demand. Agent/collector needs to produce an authorization letter from the applicant.</p><table style=\"min-width: 50px;\"><colgroup><col style=\"min-width: 25px;\"><col style=\"min-width: 25px;\"></colgroup><tbody><tr><td colspan=\"1\" rowspan=\"1\"><img src=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/images/pdf_img.png\" alt=\"pdf img\"></td><td colspan=\"1\" rowspan=\"1\"><p><a target=\"_blank\" rel=\"noopener noreferrer nofollow\" class=\"c_orange\" href=\"https://www.vfsglobal.com/one-pager/India/SouthAfrica/passport-services/pdf/Authorization-Letter.pdf\"><u>Click here </u></a>for authorization letter</p></td></tr></tbody></table><p><br></p>', '2026-01-14 13:15:39', '2026-01-14 13:15:39');

-- --------------------------------------------------------

--
-- Table structure for table `system_config`
--

CREATE TABLE `system_config` (
  `id` int(11) NOT NULL,
  `config_key` varchar(255) NOT NULL,
  `config_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`config_value`)),
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `updated_by` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_config`
--

INSERT INTO `system_config` (`id`, `config_key`, `config_value`, `description`, `is_public`, `updated_by`, `updated_at`) VALUES
(1, 'site_settings', '{\"title\":\"Indian Consular Services\",\"description\":\"Official portal for Indian consular services\"}', 'General site settings', 1, NULL, '2025-11-05 13:53:28'),
(2, 'appointment_settings', '{\"slot_duration_minutes\":30,\"max_appointments_per_slot\":3,\"advance_booking_days\":4,\"cancellation_hours\":12}', 'Appointment system settings', 0, NULL, '2026-01-14 14:20:39'),
(3, 'contact_info', '{\"phone\":\"+27 11 895 0460\",\"email\":\"consular.johannesburg@mea.gov.in\",\"address\":\"Consulate General of India, Johannesburg\"}', 'Contact information', 1, NULL, '2025-11-05 13:53:28');

-- --------------------------------------------------------

--
-- Table structure for table `time_slots`
--

CREATE TABLE `time_slots` (
  `slot_id` int(11) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `duration` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `time_slots`
--

INSERT INTO `time_slots` (`slot_id`, `start_time`, `end_time`, `duration`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '09:00:00', '09:30:00', 30, 1, '2025-11-08 13:08:00', '2025-11-10 23:29:33'),
(2, '09:30:00', '10:00:00', 30, 1, '2025-11-08 13:08:00', '2025-11-10 23:29:33'),
(3, '10:00:00', '10:30:00', 30, 1, '2025-11-08 13:08:00', '2025-11-10 23:29:33'),
(4, '10:30:00', '11:00:00', 30, 1, '2025-11-08 13:08:00', '2025-11-08 13:08:00'),
(5, '11:00:00', '11:30:00', 30, 1, '2025-11-08 13:08:00', '2025-11-08 13:08:00'),
(6, '11:30:00', '12:00:00', 30, 1, '2025-11-08 13:08:00', '2025-11-22 08:20:20'),
(7, '14:00:00', '14:30:00', 30, 1, '2025-11-08 13:08:00', '2025-11-10 23:29:33'),
(8, '14:30:00', '15:00:00', 30, 1, '2025-11-08 13:08:00', '2025-11-08 13:08:00'),
(9, '15:00:00', '15:30:00', 30, 1, '2025-11-08 13:08:00', '2025-11-11 00:02:22'),
(10, '15:30:00', '16:00:00', 30, 1, '2025-11-08 13:08:00', '2025-11-11 00:02:41'),
(11, '16:00:00', '16:30:00', 30, 1, '2025-11-08 13:08:00', '2025-11-11 00:02:41'),
(12, '16:30:00', '17:00:00', 30, 1, '2025-11-08 13:08:00', '2025-11-11 00:02:41');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_no` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `gender` enum('Male','Female','Other','Prefer not to say') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `passport_no` varchar(50) DEFAULT NULL,
  `passport_expiry` date DEFAULT NULL,
  `email_validated` tinyint(1) DEFAULT 0,
  `account_status` enum('active','inactive','suspended','pending') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `first_name`, `last_name`, `email`, `phone_no`, `password_hash`, `gender`, `date_of_birth`, `nationality`, `passport_no`, `passport_expiry`, `email_validated`, `account_status`, `created_at`, `updated_at`) VALUES
(1, 'ujwal', 'thakkar', 'ujwalthakkar020@gmail.com', '9022222754', '$2y$10$H/4k9jiWeHFJJZmphv1xcOp5vRgHAh07eKy3nLJvQCPBA5lNvmRpa', 'Male', '2026-01-08', 'Indian', 'A123345475', '2026-01-13', 1, 'active', '2025-11-09 06:14:58', '2026-01-14 14:07:19');

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL,
  `log_id` varchar(50) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `affected_resource_type` varchar(100) DEFAULT NULL,
  `affected_resource_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`id`, `log_id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `affected_resource_type`, `affected_resource_id`, `created_at`) VALUES
(1, 'LOG20251109AB32F552', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'PostmanRuntime/7.49.1', '', '', '2025-11-09 01:50:39'),
(2, 'LOG20251109CDCC52D4', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-11-09 03:49:48'),
(5, 'LOG2025110984FE97AE', '1', 'BOOKING_CREATED', '{\"booking_id\":3,\"appointment_id\":4,\"service_id\":1,\"date\":\"2025-11-10\"}', '::1', 'PostmanRuntime/7.49.1', '', '', '2025-11-09 05:38:19'),
(6, 'LOG20251110D35860A8', 'UNKNOWN', 'USER_LOGIN_FAILED', '{\"email\":\"ujwaladmin\",\"reason\":\"invalid_credentials\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-11-10 07:26:40'),
(7, 'LOG2025111000D737CB', 'UNKNOWN', 'USER_LOGIN_FAILED', '{\"email\":\"ujwaladmin\",\"reason\":\"invalid_credentials\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-11-10 07:32:57'),
(8, 'LOG20251110DDD483A6', 'UNKNOWN', 'USER_LOGIN_FAILED', '{\"email\":\"ujwaladmin\",\"reason\":\"invalid_credentials\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-11-10 07:33:07'),
(9, 'LOG2025111095E76BE7', 'UNKNOWN', 'USER_LOGIN_FAILED', '{\"email\":\"ujwaladmin\",\"reason\":\"invalid_credentials\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-11-10 07:35:16'),
(10, 'LOG202511221AB021A9', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-11-22 02:57:04'),
(11, 'LOG2025112225454221', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-11-22 02:57:38'),
(12, 'LOG20251122CD88EFC1', '1', 'BOOKING_CREATED', '{\"booking_id\":4,\"appointment_id\":5,\"service_id\":1,\"date\":\"2025-11-24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-11-22 03:48:18'),
(13, 'LOG20251122EDDCE2C2', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-11-22 04:56:28'),
(14, 'LOG202511268A3163A7', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-11-26 05:19:03'),
(15, 'LOG20251126C4A40BFC', '1', 'BOOKING_CREATED', '{\"booking_id\":5,\"appointment_id\":9,\"service_id\":12,\"date\":\"2025-11-27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-11-26 08:31:10'),
(16, 'LOG202512085EBCAE85', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-12-08 10:19:21'),
(17, 'LOG2025120844D9BB54', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-12-08 10:26:25'),
(18, 'LOG202512082C84B6B3', '1', 'BOOKING_CREATED', '{\"booking_id\":6,\"appointment_id\":10,\"service_id\":12,\"date\":\"2025-12-09\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-12-08 11:24:16'),
(19, 'LOG20251220AD9301EB', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'PostmanRuntime/7.51.0', '', '', '2025-12-20 02:39:30'),
(20, 'LOG20251220EB013FEA', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-20 02:42:07'),
(21, 'LOG20251220FD6ABB58', '1', 'BOOKING_CREATED', '{\"booking_id\":7,\"appointment_id\":12,\"service_id\":1,\"date\":\"2025-12-22\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-20 02:43:41'),
(22, 'LOG2025122200C3B1F0', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-22 04:17:47'),
(23, 'LOG20251224639A15B7', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-24 05:14:51'),
(24, 'LOG20251224DCA0926E', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-24 05:20:52'),
(25, 'LOG202512241B4BFB68', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-24 05:22:45'),
(26, 'LOG202512299D020F43', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'PostmanRuntime/7.51.0', '', '', '2025-12-28 19:46:36'),
(27, 'LOG20251229B5D872A6', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-28 22:19:48'),
(28, 'LOG20251230E722AD8D', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-30 07:33:06'),
(29, 'LOG20251230D9A3F3DB', '1', 'OCR_EXTRACTION', '{\"document_type\":\"passport\",\"success\":true,\"confidence\":0.95}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-30 07:33:10'),
(30, 'LOG202512307420C162', '1', 'OCR_EXTRACTION', '{\"document_type\":\"birth_certificate\",\"success\":true,\"confidence\":0.6999999999999998}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-30 07:39:28'),
(31, 'LOG20251230DF8641E5', '1', 'OCR_EXTRACTION', '{\"document_type\":\"birth_certificate\",\"success\":true,\"confidence\":0.6999999999999998}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-30 07:40:59'),
(32, 'LOG20251230ED9222A1', '1', 'OCR_EXTRACTION', '{\"document_type\":\"birth_certificate\",\"success\":false,\"confidence\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-30 11:14:46'),
(33, 'LOG2025123089DB7C3E', '1', 'OCR_EXTRACTION', '{\"document_type\":\"birth_certificate\",\"success\":false,\"confidence\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-30 12:53:22'),
(34, 'LOG202512309D499786', '1', 'OCR_EXTRACTION', '{\"document_type\":\"passport\",\"success\":true,\"confidence\":0.75}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-30 12:53:37'),
(35, 'LOG202512309206F82C', '1', 'OCR_EXTRACTION', '{\"document_type\":\"passport\",\"success\":false,\"confidence\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-30 12:53:53'),
(36, 'LOG20251230F0D0D9C9', '1', 'OCR_EXTRACTION', '{\"document_type\":\"birth_certificate\",\"success\":true,\"confidence\":0.6999999999999998}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-30 12:54:32'),
(37, 'LOG2025123007EC1FE7', '1', 'OCR_EXTRACTION', '{\"document_type\":\"birth_certificate\",\"success\":true,\"confidence\":0.6999999999999998}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-30 13:01:06'),
(38, 'LOG2025123087144E29', '1', 'OCR_EXTRACTION', '{\"document_type\":\"birth_certificate\",\"success\":true,\"confidence\":0.6999999999999998}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-30 13:01:36'),
(39, 'LOG202512307F5AF54A', '1', 'OCR_EXTRACTION', '{\"document_type\":\"birth_certificate\",\"success\":true,\"confidence\":0.6999999999999998}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-30 13:07:24'),
(40, 'LOG2025123003CDB826', '1', 'OCR_EXTRACTION', '{\"document_type\":\"birth_certificate\",\"success\":true,\"confidence\":0.6999999999999998}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-30 13:08:28'),
(41, 'LOG20251230E3709461', '1', 'OCR_EXTRACTION', '{\"document_type\":\"birth_certificate\",\"success\":true,\"confidence\":0.6999999999999998}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-30 13:12:24'),
(42, 'LOG2025123004EBD161', '1', 'OCR_EXTRACTION', '{\"document_type\":\"birth_certificate\",\"success\":true,\"confidence\":0.6999999999999998}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-30 13:13:43'),
(43, 'LOG20251230C101D424', '1', 'OCR_EXTRACTION', '{\"document_type\":\"birth_certificate\",\"success\":false,\"confidence\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-30 13:15:36'),
(44, 'LOG20251230DC5AD663', '1', 'OCR_EXTRACTION', '{\"document_type\":\"passport\",\"success\":true,\"confidence\":0.75}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-30 13:16:42'),
(45, 'LOG20251230B0E8D4B3', '1', 'OCR_EXTRACTION', '{\"document_type\":\"birth_certificate\",\"success\":true,\"confidence\":0.6999999999999998}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2025-12-30 13:17:00'),
(46, 'LOG20260113EF677BCB', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-13 13:47:59'),
(47, 'LOG20260114EB89BF23', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 08:32:24'),
(48, 'LOG20260114DEDDA0FE', 'UNKNOWN', 'USER_LOGIN_FAILED', '{\"email\":null,\"reason\":\"user_not_found\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 09:31:11'),
(49, 'LOG2026011404FB9A0E', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 09:31:32'),
(50, 'LOG202601149799A874', '1', 'BOOKING_CREATED', '{\"booking_id\":8,\"appointment_id\":13,\"service_id\":1,\"date\":\"2026-01-19\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-14 09:37:19'),
(51, 'LOG202601159F351E4C', 'UNKNOWN', 'USER_LOGIN_FAILED', '{\"email\":null,\"reason\":\"user_not_found\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-15 04:04:58'),
(52, 'LOG2026011518DF0E9F', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-15 04:43:46'),
(53, 'LOG20260115DB06E2E0', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-15 04:57:43'),
(54, 'LOG202601154609570B', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-15 04:58:13'),
(55, 'LOG20260115E13330A6', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-15 05:05:46'),
(56, 'LOG202601163FEAEC35', '1', 'MISCELLANEOUS_APPLICATION_SUBMITTED', '{\"application_id\":\"MISC2026011682534D8D\",\"service_id\":\"14\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC2026011682534D8D', '2026-01-15 19:57:49'),
(57, 'LOG20260116E33C0990', '1', 'USER_LOGIN_SUCCESS', '{\"method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '', '', '2026-01-16 08:14:39'),
(58, 'LOG20260116AF115419', '1', 'MISCELLANEOUS_APPLICATION_SUBMITTED', '{\"application_id\":\"MISC202601164EEDC8BC\",\"service_id\":\"14\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC202601164EEDC8BC', '2026-01-16 08:18:48'),
(59, 'LOG20260116AC014B47', '1', 'MISCELLANEOUS_APPLICATION_SUBMITTED', '{\"application_id\":\"MISC20260116A4C524CC\",\"service_id\":\"14\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC20260116A4C524CC', '2026-01-16 08:44:50'),
(60, 'LOG2026011663F5342A', '1', 'MISCELLANEOUS_APPLICATION_SUBMITTED', '{\"application_id\":\"MISC2026011650A5D60A\",\"service_id\":\"14\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC2026011650A5D60A', '2026-01-16 08:49:50'),
(61, 'LOG202601169F7E2610', '1', 'MISCELLANEOUS_APPLICATION_SUBMITTED', '{\"application_id\":\"MISC2026011649107AB7\",\"service_id\":\"14\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC2026011649107AB7', '2026-01-16 08:51:55'),
(62, 'LOG20260116CEC05A55', '1', 'MISCELLANEOUS_APPLICATION_SUBMITTED', '{\"application_id\":\"MISC20260116FC717B77\",\"service_id\":\"14\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC20260116FC717B77', '2026-01-16 08:55:32'),
(63, 'LOG202601162290F846', '1', 'MISCELLANEOUS_APPLICATION_SUBMITTED', '{\"application_id\":\"MISC202601168E18F3AA\",\"service_id\":\"14\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'miscellaneous_application', 'MISC202601168E18F3AA', '2026-01-16 08:57:37');

-- --------------------------------------------------------

--
-- Table structure for table `verification_center`
--

CREATE TABLE `verification_center` (
  `center_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) NOT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `operating_hours` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`operating_hours`)),
  `provides_services` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provides_services`)),
  `has_counters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`has_counters`)),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `verification_center`
--

INSERT INTO `verification_center` (`center_id`, `name`, `address`, `city`, `state`, `country`, `postal_code`, `phone`, `email`, `operating_hours`, `provides_services`, `has_counters`, `latitude`, `longitude`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'Central Verification Center', '123 Main Street', 'Mumbai', 'Maharashtra', 'India', '400001', '+91-22-12345678', 'central@verification.com', '{\"monday\": \"09:00-17:00\", \"tuesday\": \"09:00-17:00\", \"wednesday\": \"09:00-17:00\", \"thursday\": \"09:00-17:00\", \"friday\": \"09:00-17:00\", \"saturday\": \"09:00-13:00\"}', '[1,2,3,11,12,13,14]', '[1,2,3,4]', 19.07600000, 72.87770000, 1, 1, '2025-11-08 13:08:00', '2026-01-15 23:38:38');

-- --------------------------------------------------------

--
-- Table structure for table `visa_countries`
--

CREATE TABLE `visa_countries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `flag_url` varchar(255) DEFAULT NULL,
  `embassy_name` varchar(255) DEFAULT NULL,
  `vfs_partner` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visa_countries`
--

INSERT INTO `visa_countries` (`id`, `name`, `slug`, `flag_url`, `embassy_name`, `vfs_partner`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'South Africa', 'south-africa', '/flags/za.png', 'High Commission of India, Pretoria', 'VFS Global', 1, 0, '2025-11-21 14:05:21', '2025-11-21 14:05:21');

-- --------------------------------------------------------

--
-- Table structure for table `visa_downloads`
--

CREATE TABLE `visa_downloads` (
  `id` int(11) NOT NULL,
  `visa_type_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_url` varchar(500) NOT NULL,
  `file_size_kb` int(11) DEFAULT NULL,
  `is_checklist` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visa_downloads`
--

INSERT INTO `visa_downloads` (`id`, `visa_type_id`, `title`, `file_url`, `file_size_kb`, `is_checklist`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(14, 13, 'Tourist Visa Application Form', '/uploads/visa-forms/sa-tourist-form.pdf', NULL, 0, 0, 1, '2025-11-21 14:37:08', '2025-11-21 14:37:08'),
(15, 13, 'Document Checklist - Tourist', '/uploads/visa-forms/sa-tourist-checklist.pdf', NULL, 1, 0, 1, '2025-11-21 14:37:08', '2025-11-21 14:37:08'),
(16, 14, 'Business Visa Form', '/uploads/visa-forms/sa-business-form.pdf', NULL, 0, 0, 1, '2025-11-21 14:37:08', '2025-11-21 14:37:08'),
(17, 15, 'Medical Visa Form', '/uploads/visa-forms/sa-medical-form.pdf', NULL, 0, 0, 1, '2025-11-21 14:37:08', '2025-11-21 14:37:08');

-- --------------------------------------------------------

--
-- Table structure for table `visa_types`
--

CREATE TABLE `visa_types` (
  `id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `short_description` text DEFAULT NULL,
  `overview` longtext DEFAULT NULL,
  `processing_time` varchar(200) DEFAULT NULL,
  `validity` varchar(200) DEFAULT NULL,
  `fees_json` longtext DEFAULT NULL CHECK (json_valid(`fees_json`)),
  `documents_json` longtext DEFAULT NULL CHECK (json_valid(`documents_json`)),
  `photo_specifications` longtext DEFAULT NULL,
  `important_notes` longtext DEFAULT NULL,
  `online_form_url` varchar(500) DEFAULT NULL,
  `appointment_info` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visa_types`
--

INSERT INTO `visa_types` (`id`, `country_id`, `name`, `slug`, `short_description`, `overview`, `processing_time`, `validity`, `fees_json`, `documents_json`, `photo_specifications`, `important_notes`, `online_form_url`, `appointment_info`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(13, 1, 'Tourist Visa', 'tourist-visa', 'For tourism, visiting family/friends', 'South African Tourist Visa allows Indian citizens to visit for tourism purposes for up to 90 days...', '5–7 working days (standard), 48 hours (urgent)', 'Up to 90 days', '{\"consular_fee\": 4350, \"vfs_fee\": 2250, \"sms_fee\": 100, \"total_inr\": 6700}', '[\"Valid passport (6 months + 2 blank pages)\",\"2 recent photos (35x45mm)\",\"Flight tickets\",\"Hotel booking or invitation letter\",\"Bank statements last 3 months\",\"Employment proof\"]', '<ul><li>Size: 35mm × 45mm</li><li>White background</li><li>70–80% face</li><li>No glasses, no smile</li></ul>', 'Online application on indianvisaonline.gov.in is mandatory before booking appointment.', 'https://indianvisaonline.gov.in/visa/index.html', NULL, 1, 0, '2025-11-21 14:36:00', '2025-11-21 14:36:00'),
(14, 1, 'Business Visa', 'business-visa', 'For business meetings, conferences', 'For attending business meetings, conferences, or exploring opportunities...', '5–10 working days', 'Up to 90 days (multiple entry possible)', '{\"consular_fee\": 8750, \"vfs_fee\": 2250, \"total_inr\": 11000}', '[\"Invitation letter from South African company\",\"Company registration proof\",\"Passport\",\"Photos\",\"Bank statements\"]', '<ul><li>35mm × 45mm</li><li>White background</li><li>Recent (within 6 months)</li></ul>', 'Yellow fever certificate required if coming from endemic area.', 'https://indianvisaonline.gov.in', NULL, 1, 0, '2025-11-21 14:36:00', '2025-11-21 14:36:00'),
(15, 1, 'Medical Visa', 'medical-visa', 'For medical treatment in South Africa', 'For patients seeking medical treatment and one attendant...', '7–15 working days', 'Up to 6 months', '{\"consular_fee\": 8750, \"vfs_fee\": 2250, \"total_inr\": 11000}', '[\"Hospital letter\",\"Passport\",\"Photos\",\"Financial proof\",\"Medical reports\"]', '<ul><li>35mm × 45mm</li><li>White background</li></ul>', 'Attendant gets Medical Attendant Visa.', NULL, NULL, 1, 0, '2025-11-21 14:36:00', '2025-11-21 14:36:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `log_id` (`log_id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_resource_type` (`affected_resource_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_id` (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `application_files`
--
ALTER TABLE `application_files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `file_id` (`file_id`),
  ADD KEY `idx_application_id` (`application_id`),
  ADD KEY `idx_document_type` (`document_type`),
  ADD KEY `idx_uploaded_by` (`uploaded_by`);

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`appointment_id`),
  ADD UNIQUE KEY `idx_appointment_unique_booking` (`at_counter`,`appointment_date`,`slot`,`appointment_status`),
  ADD KEY `idx_appointment_user` (`booked_by`),
  ADD KEY `idx_appointment_date` (`appointment_date`),
  ADD KEY `idx_appointment_counter` (`at_counter`),
  ADD KEY `idx_appointment_slot` (`slot`),
  ADD KEY `idx_appointment_service` (`booked_for_service`),
  ADD KEY `idx_appointment_status` (`appointment_status`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `idx_booking_date` (`booked_date`),
  ADD KEY `idx_booking_slot` (`booked_slot`),
  ADD KEY `idx_booking_appointment` (`appointment`);

--
-- Indexes for table `counter`
--
ALTER TABLE `counter`
  ADD PRIMARY KEY (`counter_id`),
  ADD KEY `idx_counter_center` (`center_id`),
  ADD KEY `idx_counter_active` (`is_active`);

--
-- Indexes for table `miscellaneous_applications`
--
ALTER TABLE `miscellaneous_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_id` (`application_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_service_id` (`service_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_submitted_at` (`submitted_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `notification_id` (`notification_id`),
  ADD KEY `idx_notification_id` (`notification_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_application_id` (`application_id`),
  ADD KEY `idx_appointment_id` (`appointment_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `template_id` (`template_id`),
  ADD KEY `idx_template_id` (`template_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `idx_service_active` (`is_active`),
  ADD KEY `idx_service_category` (`category`),
  ADD KEY `idx_service_display_order` (`display_order`);

--
-- Indexes for table `service_details`
--
ALTER TABLE `service_details`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `system_config`
--
ALTER TABLE `system_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_key` (`config_key`),
  ADD KEY `idx_config_key` (`config_key`),
  ADD KEY `idx_public` (`is_public`);

--
-- Indexes for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`slot_id`),
  ADD KEY `idx_timeslot_active` (`is_active`),
  ADD KEY `idx_timeslot_start` (`start_time`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `passport_no` (`passport_no`),
  ADD KEY `idx_user_email` (`email`),
  ADD KEY `idx_user_passport` (`passport_no`),
  ADD KEY `idx_user_account_status` (`account_status`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `log_id` (`log_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_resource_type` (`affected_resource_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `verification_center`
--
ALTER TABLE `verification_center`
  ADD PRIMARY KEY (`center_id`),
  ADD KEY `idx_center_city` (`city`),
  ADD KEY `idx_center_country` (`country`),
  ADD KEY `idx_center_active` (`is_active`),
  ADD KEY `idx_center_location` (`latitude`,`longitude`);

--
-- Indexes for table `visa_countries`
--
ALTER TABLE `visa_countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `visa_downloads`
--
ALTER TABLE `visa_downloads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `visa_type_id` (`visa_type_id`);

--
-- Indexes for table `visa_types`
--
ALTER TABLE `visa_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_slug_per_country` (`country_id`,`slug`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `application_files`
--
ALTER TABLE `application_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `counter`
--
ALTER TABLE `counter`
  MODIFY `counter_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `miscellaneous_applications`
--
ALTER TABLE `miscellaneous_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `system_config`
--
ALTER TABLE `system_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `slot_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `verification_center`
--
ALTER TABLE `verification_center`
  MODIFY `center_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `visa_countries`
--
ALTER TABLE `visa_countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `visa_downloads`
--
ALTER TABLE `visa_downloads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `visa_types`
--
ALTER TABLE `visa_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `application_files`
--
ALTER TABLE `application_files`
  ADD CONSTRAINT `fk_app_file_application` FOREIGN KEY (`application_id`) REFERENCES `miscellaneous_applications` (`application_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_app_file_user` FOREIGN KEY (`uploaded_by`) REFERENCES `user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `appointment_ibfk_1` FOREIGN KEY (`booked_by`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointment_ibfk_2` FOREIGN KEY (`booked_for_service`) REFERENCES `service` (`service_id`),
  ADD CONSTRAINT `appointment_ibfk_3` FOREIGN KEY (`at_counter`) REFERENCES `counter` (`counter_id`),
  ADD CONSTRAINT `appointment_ibfk_4` FOREIGN KEY (`slot`) REFERENCES `time_slots` (`slot_id`);

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`booked_slot`) REFERENCES `time_slots` (`slot_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`appointment`) REFERENCES `appointment` (`appointment_id`) ON DELETE CASCADE;

--
-- Constraints for table `counter`
--
ALTER TABLE `counter`
  ADD CONSTRAINT `counter_ibfk_1` FOREIGN KEY (`center_id`) REFERENCES `verification_center` (`center_id`) ON DELETE CASCADE;

--
-- Constraints for table `miscellaneous_applications`
--
ALTER TABLE `miscellaneous_applications`
  ADD CONSTRAINT `miscellaneous_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `miscellaneous_applications_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE;

--
-- Constraints for table `service_details`
--
ALTER TABLE `service_details`
  ADD CONSTRAINT `fk_service_details_service` FOREIGN KEY (`service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `visa_downloads`
--
ALTER TABLE `visa_downloads`
  ADD CONSTRAINT `visa_downloads_ibfk_1` FOREIGN KEY (`visa_type_id`) REFERENCES `visa_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `visa_types`
--
ALTER TABLE `visa_types`
  ADD CONSTRAINT `visa_types_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `visa_countries` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
