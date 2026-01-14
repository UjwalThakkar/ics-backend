-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 01:58 PM
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
(29, 'LOG20251113F595DCC9', '3', 'LOGIN_SUCCESS', '{\"method\":\"2FA_completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-11-13 12:27:55');

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
(3, '3', 'ujwaladmin', 'ujwalthakkar020@gmail.com', '$2y$10$H/4k9jiWeHFJJZmphv1xcOp5vRgHAh07eKy3nLJvQCPBA5lNvmRpa', 'ujwal', 'thakkar', 'admin', '[\"all\"]', '2025-11-13 12:27:55', 1, NULL, '2025-11-10 12:05:04', '2025-11-13 16:57:55');

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
(4, 1, 1, 1, '2025-11-12', 1, 'completed', '2025-11-09 10:08:19', '2025-11-10 22:55:19');

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
(3, '2025-11-10', 1, 4, '2025-11-09 10:08:19');

-- --------------------------------------------------------

--
-- Table structure for table `counter`
--

CREATE TABLE `counter` (
  `counter_id` int(11) NOT NULL,
  `center_id` int(11) NOT NULL,
  `counter_name` varchar(100) NOT NULL,
  `service_handled` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`service_handled`)),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `counter`
--

INSERT INTO `counter` (`counter_id`, `center_id`, `counter_name`, `service_handled`, `is_active`) VALUES
(1, 1, 'Counter A - Visa Services', '[1, 2]', 1),
(2, 1, 'Counter B - Visa Services', '[1, 2]', 1),
(3, 1, 'Counter C - Document Services', '[3]', 1);

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
(3, 'NOTIFC9FA77E644A1', 'email', 'ujwalthakkar020@gmail.com', NULL, 'Appointment Confirmation', 'Dear ujwal thakkar, your appointment has been confirmed.', 'appointment_confirmed', NULL, '4', '1', 'pending', NULL, NULL, '2025-11-09 10:08:19');

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
(1, 'Visa', 'Tourist Visa Application', 'Application for tourist visa', '5-7 business days', '[{\"type\":\"standard\", \"amount\": 50}, {\"type\":\"express\", \"amount\": 100}]', '[\"Passport copy\", \"Photograph\", \"Travel itinerary\"]', '[\"Valid passport\", \"Sufficient funds\"]', 1, 1, '2025-11-08 13:08:00', '2025-11-13 18:13:06'),
(2, 'Visa', 'Business Visa Application', 'Application for business visa', '7-10 business days', '[{\"type\":\"standard\", \"amount\": 75}, {\"type\":\"express\", \"amount\": 150}]', '[\"Passport copy\", \"Photograph\", \"Business invitation letter\"]', '[\"Valid passport\", \"Business registration\"]', 1, 2, '2025-11-08 13:08:00', '2025-11-13 18:12:58'),
(3, 'Document Verification', 'Document Authentication', 'Authentication of official documents', '3-5 business days', '[{\"type\":\"standard\", \"amount\": 75}, {\"type\":\"express\", \"amount\": 150}]', '[\"Original documents\", \"ID proof\"]', '[\"Documents must be original\"]', 1, 3, '2025-11-08 13:08:00', '2025-11-13 18:12:53'),
(10, 'Passport', 'Passport Renewal', 'Renew your passport', '4-6 weeks', '[{\"type\":\"express\",\"amount\":150}]', '[\"Old passport\",\"Photos\"]', '[\"Citizen\"]', 1, 1, '2025-11-12 10:00:26', '2025-11-12 10:32:31');

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
(2, 'appointment_settings', '{\"slot_duration_minutes\":30,\"max_appointments_per_slot\":3,\"advance_booking_days\":60,\"cancellation_hours\":12}', 'Appointment system settings', 0, NULL, '2025-11-11 00:12:17'),
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
(6, '11:30:00', '12:00:00', 30, 1, '2025-11-08 13:08:00', '2025-11-08 13:08:00'),
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
(1, 'ujwal', 'thakkar', 'ujwalthakkar020@gmail.com', NULL, '$2y$10$H/4k9jiWeHFJJZmphv1xcOp5vRgHAh07eKy3nLJvQCPBA5lNvmRpa', 'Male', '1990-01-15', 'Indian', 'A1234567', '2030-12-31', 0, 'active', '2025-11-09 06:14:58', '2025-11-09 10:08:19');

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
(9, 'LOG2025111095E76BE7', 'UNKNOWN', 'USER_LOGIN_FAILED', '{\"email\":\"ujwaladmin\",\"reason\":\"invalid_credentials\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '', '', '2025-11-10 07:35:16');

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
(1, 'Central Verification Center', '123 Main Street', 'Mumbai', 'Maharashtra', 'India', '400001', '+91-22-12345678', 'central@verification.com', '{\"monday\": \"09:00-17:00\", \"tuesday\": \"09:00-17:00\", \"wednesday\": \"09:00-17:00\", \"thursday\": \"09:00-17:00\", \"friday\": \"09:00-17:00\", \"saturday\": \"09:00-13:00\"}', '[1, 2, 3]', '[1, 2, 3]', 19.07600000, 72.87770000, 1, 1, '2025-11-08 13:08:00', '2025-11-08 13:08:00');

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `counter`
--
ALTER TABLE `counter`
  MODIFY `counter_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `verification_center`
--
ALTER TABLE `verification_center`
  MODIFY `center_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
