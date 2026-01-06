-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 06, 2026 at 03:28 AM
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
-- Database: `wbbms_langkaan2`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `details` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('Active','Archived') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `title`, `details`, `image`, `date`, `time`, `location`, `status`) VALUES
(2, 'dasdaddsadsssssssssdddsdss', 'adasdasddasdadsadds', '1767444084_announcement3.png', '2026-01-10', '09:00:00', 'city homes resort ville 1 covered court r5 barangay langkaan 2', 'Active'),
(3, 'Barangay General Assembly 2022', 'Inaanyayahan ang lahat na dumalo sa ating quarterly meeting.', '', '2022-10-15', '08:00:00', 'Barangay Hall', 'Active'),
(4, 'Paskong Barangay 2022', 'Christmas Party at Gift Giving para sa mga bata.', '', '2022-12-20', '16:00:00', 'Covered Court', 'Active'),
(5, 'Libreng Bakuna sa Aso', 'Anti-rabies vaccination drive para sa mga alagang hayop.', '', '2023-02-10', '09:00:00', 'Barangay Plaza', 'Active'),
(6, 'Summer Sports Fest 2023', 'Basketball at Volleyball League registration is now open.', '', '2023-04-05', '07:00:00', 'Sports Complex', 'Active'),
(7, 'Oplan Linis Barangay', 'Tapat mo, linis mo program launch.', '', '2023-07-22', '06:00:00', 'Zone 1 to Zone 4', 'Active'),
(8, 'Medical at Dental Mission', 'Libreng check-up at bunot ng ngipin.', '', '2023-09-15', '08:00:00', 'Health Center', 'Active'),
(9, 'Distribusyon ng Senior Citizen Pension', 'Pagbibigay ng monthly pension para sa mga rehistradong senior.', '', '2023-11-18', '13:00:00', 'Multi-purpose Hall', 'Active'),
(10, 'Voters Registration Assistance', 'Tulong para sa mga magpaparehistro sa COMELEC.', '', '2024-01-08', '08:00:00', 'Barangay Hall', 'Active'),
(11, 'Womens Month Celebration', 'Zumba at Seminar para sa mga kababaihan.', '', '2024-03-08', '15:00:00', 'Barangay Plaza', 'Active'),
(12, 'Flores de Mayo 2024', 'Sagala at prusisyon para sa kapistahan\r\n', '1767632692_Resident Dashboard.png', '2024-05-25', '17:00:00', 'Main Roadd', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `barangay_officials`
--

CREATE TABLE `barangay_officials` (
  `official_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `position` varchar(100) NOT NULL,
  `term_start` date NOT NULL,
  `term_end` date DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay_officials`
--

INSERT INTO `barangay_officials` (`official_id`, `full_name`, `position`, `term_start`, `term_end`, `status`, `image`) VALUES
(1, 'Hon. David John Paulo Laudato', 'Barangay Captain', '2023-01-21', '2026-01-21', 'Active', 'off_1767603769_695b7e3945dd5.jpg'),
(2, 'Sec. Juan Paolo Melad', 'Secretary', '2025-08-13', '2026-01-21', 'Active', 'off_1767603751_695b7e27980f1.png'),
(4, 'Luchi Antonio', 'Treasurer', '2023-01-21', '2026-01-21', 'Active', 'off_1767603804_695b7e5cee3ab.jpg'),
(5, 'Hon. Alberto Bautista', 'Kagawad', '2023-01-21', '2026-01-21', 'Active', 'off_1767603814_695b7e66cd04d.jpg'),
(6, 'Hon. Alberto Jr G. Agustin', 'Kagawad', '2023-01-21', '2026-01-21', 'Active', 'off_1767603822_695b7e6e7f1b3.png'),
(7, 'Hon. Alfeo S. Sollegue', 'Kagawad', '2023-01-21', '2026-01-21', 'Active', 'off_1767603830_695b7e7653c5f.png'),
(8, 'Hon. Danilo S. Galinato', 'Kagawad', '2023-01-21', '2026-01-21', 'Active', 'off_1767603837_695b7e7da49a1.png'),
(9, 'Hon. Fernando B. Laudato Jr.', 'Kagawad', '2023-01-21', '2026-01-21', 'Active', 'off_1767603846_695b7e86ca7bd.png'),
(10, 'Hon. Mark Henry Barcena', 'Kagawad', '2023-01-21', '2026-01-21', 'Active', 'off_1767603853_695b7e8dc923d.jpg'),
(11, 'Hon. Enrico Sango', 'Kagawad', '2023-01-21', '2026-01-21', 'Active', 'off_1767603865_695b7e99af1d2.jpg'),
(12, 'Sk Chair. Jhimwell Rivera', 'SK Chairman', '2025-01-21', '2026-01-05', 'Inactive', 'off_1767603759_695b7e2f22039.png');

-- --------------------------------------------------------

--
-- Table structure for table `blotter_records`
--

CREATE TABLE `blotter_records` (
  `blotter_id` int(11) NOT NULL,
  `complainant` varchar(150) NOT NULL,
  `respondent` varchar(150) NOT NULL,
  `incident_type` varchar(100) NOT NULL,
  `incident_date` date NOT NULL,
  `incident_location` text NOT NULL,
  `narrative` text NOT NULL,
  `status` enum('Pending','Hearing','Settled','Unsettled') DEFAULT 'Pending',
  `hearing_schedule` datetime DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `status_archive` enum('Active','Archived') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blotter_records`
--

INSERT INTO `blotter_records` (`blotter_id`, `complainant`, `respondent`, `incident_type`, `incident_date`, `incident_location`, `narrative`, `status`, `hearing_schedule`, `recorded_by`, `status_archive`) VALUES
(1, 'John Mark Lastrollo', 'Unidentified Person', 'Theft', '2026-01-01', 'dasdsadsad', 'asdasdas', 'Hearing', '2026-01-08 11:25:00', NULL, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `complainant_name` varchar(150) NOT NULL,
  `respondent_name` varchar(150) NOT NULL,
  `complaint_type` varchar(100) NOT NULL,
  `incident_date` date NOT NULL,
  `incident_place` text NOT NULL,
  `details` text NOT NULL,
  `status` enum('Pending','Active','Resolved','Processed','Archived') DEFAULT 'Pending',
  `admin_feedback` text DEFAULT NULL,
  `date_filed` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `resident_id`, `complainant_name`, `respondent_name`, `complaint_type`, `incident_date`, `incident_place`, `details`, `status`, `admin_feedback`, `date_filed`) VALUES
(1, 3, 'John Mark Lastrollo', 'Unidentified Person', 'Theft', '2026-01-01', 'dasdsadsad', 'asdasdas', 'Processed', NULL, '2026-01-06 10:23:21'),
(2, 3, 'John Mark Lastrollo', 'sdasdas', 'Noise Complaint', '2026-01-05', 'dasdasdsa', 'asdasdasdasd', 'Pending', NULL, '2026-01-06 10:23:36');

-- --------------------------------------------------------

--
-- Table structure for table `complaint_conversations`
--

CREATE TABLE `complaint_conversations` (
  `id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `sender_role` varchar(50) NOT NULL,
  `sender_name` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `financial_records`
--

CREATE TABLE `financial_records` (
  `finance_id` int(11) NOT NULL,
  `transaction_type` enum('Collection','Expense') NOT NULL,
  `description` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  `recorded_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `health_appointments`
--

CREATE TABLE `health_appointments` (
  `appointment_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `service_type` varchar(100) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` varchar(50) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_appointments`
--

INSERT INTO `health_appointments` (`appointment_id`, `resident_id`, `service_type`, `appointment_date`, `appointment_time`, `reason`, `status`, `created_at`) VALUES
(1, 3, 'Blood Pressure Monitoring', '2026-01-14', 'Morning (8:00 AM - 12:00 PM)', 'sadasdasd', 'Pending', '2026-01-05 14:21:17');

-- --------------------------------------------------------

--
-- Table structure for table `health_records`
--

CREATE TABLE `health_records` (
  `record_id` int(11) NOT NULL,
  `resident_name` varchar(150) NOT NULL,
  `age` int(3) NOT NULL,
  `concern` varchar(255) NOT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `date_visit` date NOT NULL,
  `attended_by` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `history_logs`
--

CREATE TABLE `history_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `history_logs`
--

INSERT INTO `history_logs` (`log_id`, `user_id`, `action`, `timestamp`) VALUES
(1, 1, 'Logged out from the system', '2026-01-03 20:03:48'),
(2, 1, 'Logged out from the system', '2026-01-03 20:04:26'),
(3, 1, 'Logged out from the system', '2026-01-03 20:08:40'),
(4, 1, 'Logged in to the system', '2026-01-03 20:08:53'),
(5, 1, 'Logged out from the system', '2026-01-03 20:10:21'),
(6, 1, 'Logged out from the system', '2026-01-03 20:13:51'),
(7, 1, 'Logged in to the system', '2026-01-03 20:15:14'),
(8, 1, 'Logged out from the system', '2026-01-03 23:04:01'),
(9, 1, 'Logged in to the system', '2026-01-03 23:36:29'),
(10, 1, 'Updated resident status for dasdd adsadad to Archived', '2026-01-04 01:26:54'),
(11, 1, 'Updated resident status for dasdd adsadad to Active', '2026-01-04 01:43:40'),
(12, 1, 'Updated resident status for dasdd adsadad to Archived', '2026-01-04 01:43:48'),
(13, 1, 'Logged in to the system', '2026-01-04 10:32:46'),
(14, 1, 'Logged out from the system', '2026-01-04 14:39:35'),
(15, 1, 'Logged in to the system', '2026-01-04 14:40:11'),
(16, 1, 'Logged in to the system', '2026-01-04 18:44:09'),
(17, 1, 'Updated resident status for John Mark Lastrollo to Archived', '2026-01-04 19:44:37'),
(18, 1, 'Updated resident status for John Mark Lastrollo to Active', '2026-01-04 19:44:42'),
(19, 1, 'Updated resident status for John Mark Lastrollo to Archived', '2026-01-04 19:44:52'),
(20, 1, 'Updated resident status for John Mark Lastrollo to Active', '2026-01-04 19:45:01'),
(21, 1, 'Logged out from the system', '2026-01-04 23:10:50'),
(22, 1, 'Logged in to the system', '2026-01-04 23:11:33'),
(23, 1, 'Logged in to the system', '2026-01-04 23:34:21'),
(24, 1, 'Logged in to the system', '2026-01-05 09:41:43'),
(25, 1, 'Logged in to the system', '2026-01-05 17:02:10'),
(26, 1, 'Logged out from the system', '2026-01-05 17:20:20'),
(27, 6, 'Logged in to the system', '2026-01-05 17:21:59'),
(28, 6, 'Logged out from the system', '2026-01-05 17:23:58'),
(29, 6, 'Logged in to the system', '2026-01-05 17:39:58'),
(30, 6, 'Logged out from the system', '2026-01-05 18:21:48'),
(31, 6, 'Logged in to the system', '2026-01-05 18:24:20'),
(32, 6, 'Logged out from the system', '2026-01-05 19:31:12'),
(33, 6, 'Logged in to the system', '2026-01-05 19:31:38'),
(34, 6, 'Logged out from the system', '2026-01-05 19:31:42'),
(35, 1, 'Logged in to the system', '2026-01-05 19:43:58'),
(36, 1, 'Logged out from the system', '2026-01-05 20:03:29'),
(37, 1, 'Logged in to the system', '2026-01-05 20:03:42'),
(38, 1, 'Logged out from the system', '2026-01-05 20:04:31'),
(39, 6, 'Logged in to the system', '2026-01-05 20:24:28'),
(40, 6, 'Logged out from the system', '2026-01-05 20:24:34'),
(41, 1, 'Logged in to the system', '2026-01-05 20:24:56'),
(42, 1, 'Logged out from the system', '2026-01-05 20:25:24'),
(43, 1, 'Logged in to the system', '2026-01-05 20:26:57'),
(44, 1, 'Logged out from the system', '2026-01-05 20:53:06'),
(45, 1, 'Logged in to the system', '2026-01-05 20:53:22'),
(46, 1, 'Logged out from the system', '2026-01-05 21:17:21'),
(47, 6, 'Logged in to the system', '2026-01-05 21:18:00'),
(48, 6, 'Logged out from the system', '2026-01-05 21:20:25'),
(49, 6, 'Logged in to the system', '2026-01-05 21:20:57'),
(50, 6, 'Logged out from the system', '2026-01-05 21:20:59'),
(51, 1, 'Logged in to the system', '2026-01-05 21:21:11'),
(52, 1, 'Logged out from the system', '2026-01-05 21:21:14'),
(53, 1, 'Logged in to the system', '2026-01-05 21:21:59'),
(54, 1, 'Logged out from the system', '2026-01-05 21:22:40'),
(55, 6, 'Logged in to the system', '2026-01-05 21:29:00'),
(56, 6, 'Logged out from the system', '2026-01-05 22:52:12'),
(57, 6, 'Logged in to the system', '2026-01-05 22:53:35'),
(58, 6, 'Logged out from the system', '2026-01-05 22:55:45'),
(59, 6, 'Logged in to the system', '2026-01-05 22:56:00'),
(60, 6, 'Logged out from the system', '2026-01-05 22:56:06'),
(61, 6, 'Logged in to the system', '2026-01-05 22:56:41'),
(62, 6, 'Logged out from the system', '2026-01-05 22:58:48'),
(63, 6, 'Logged in to the system', '2026-01-05 22:59:03'),
(64, 6, 'Logged out from the system', '2026-01-05 22:59:35'),
(65, 6, 'Logged in to the system', '2026-01-05 23:01:45'),
(66, 6, 'Logged out from the system', '2026-01-05 23:02:43'),
(67, 6, 'Logged in to the system', '2026-01-05 23:05:28'),
(68, 6, 'Logged out from the system', '2026-01-05 23:09:00'),
(69, 3, 'Logged in to the system', '2026-01-05 23:26:44'),
(70, 3, 'Logged out from the system', '2026-01-05 23:26:52'),
(71, 3, 'Logged in to the system', '2026-01-05 23:40:16'),
(72, 3, 'Logged out from the system', '2026-01-06 00:01:59'),
(73, 1, 'Logged in to the system', '2026-01-06 00:02:22'),
(74, 1, 'Logged out from the system', '2026-01-06 00:02:41'),
(75, 3, 'Logged in to the system', '2026-01-06 00:03:00'),
(76, 3, 'Logged out from the system', '2026-01-06 00:08:29'),
(77, 1, 'Logged in to the system', '2026-01-06 00:08:36'),
(78, 1, 'Logged out from the system', '2026-01-06 00:10:45'),
(79, 3, 'Logged in to the system', '2026-01-06 00:11:08'),
(80, 3, 'Logged out from the system', '2026-01-06 00:12:07'),
(81, 1, 'Logged in to the system', '2026-01-06 00:12:18'),
(82, 1, 'Logged out from the system', '2026-01-06 00:13:21'),
(83, 3, 'Logged in to the system', '2026-01-06 00:13:52'),
(84, 3, 'Logged out from the system', '2026-01-06 00:34:35'),
(85, 1, 'Logged in to the system', '2026-01-06 00:34:50'),
(86, 1, 'Logged out from the system', '2026-01-06 00:38:24'),
(87, 3, 'Logged in to the system', '2026-01-06 00:38:38'),
(88, 3, 'Logged out from the system', '2026-01-06 00:45:04'),
(89, 1, 'Logged in to the system', '2026-01-06 00:45:15'),
(90, 1, 'Logged out from the system', '2026-01-06 00:45:26'),
(91, 3, 'Logged in to the system', '2026-01-06 00:45:44'),
(92, 3, 'Logged out from the system', '2026-01-06 02:42:19'),
(93, 1, 'Logged in to the system', '2026-01-06 02:48:36'),
(94, 1, 'Logged out from the system', '2026-01-06 02:48:58'),
(95, 6, 'Logged in to the system', '2026-01-06 02:50:53'),
(96, 6, 'Logged out from the system', '2026-01-06 03:00:40'),
(97, 3, 'Logged in to the system', '2026-01-06 03:00:59'),
(98, 3, 'Logged out from the system', '2026-01-06 03:23:34'),
(99, 3, 'Logged in to the system', '2026-01-06 03:53:23'),
(100, 3, 'Logged out from the system', '2026-01-06 03:53:49'),
(101, 1, 'Logged in to the system', '2026-01-06 03:54:14'),
(102, 1, 'Logged out from the system', '2026-01-06 03:59:44'),
(103, 1, 'Logged in to the system', '2026-01-06 03:59:55'),
(104, 1, 'Logged out from the system', '2026-01-06 08:18:29'),
(105, 1, 'Logged in to the system', '2026-01-06 08:19:17'),
(106, 1, 'Converted Complaint #1 to Official Blotter Record', '2026-01-06 08:51:15'),
(107, 1, 'Logged out from the system', '2026-01-06 09:01:45'),
(108, 6, 'Logged in to the system', '2026-01-06 09:02:13'),
(109, 6, 'Logged out from the system', '2026-01-06 09:20:57'),
(110, 1, 'Logged in to the system', '2026-01-06 09:21:06'),
(111, 1, 'Logged out from the system', '2026-01-06 09:24:36'),
(112, 6, 'Logged in to the system', '2026-01-06 09:24:54'),
(113, 6, 'Logged out from the system', '2026-01-06 09:26:11'),
(114, 1, 'Logged in to the system', '2026-01-06 09:26:21'),
(115, 1, 'Logged out from the system', '2026-01-06 09:51:37'),
(116, 6, 'Logged in to the system', '2026-01-06 09:51:50'),
(117, 6, 'Logged out from the system', '2026-01-06 09:56:07'),
(118, 1, 'Logged in to the system', '2026-01-06 09:56:23'),
(119, 1, 'Archived Complaint #3', '2026-01-06 10:00:02'),
(120, 1, 'Logged out from the system', '2026-01-06 10:23:01'),
(121, 6, 'Logged in to the system', '2026-01-06 10:23:09'),
(122, 6, 'Logged out from the system', '2026-01-06 10:23:42'),
(123, 1, 'Logged in to the system', '2026-01-06 10:23:52'),
(124, 1, 'Filed Complaint #1 to Official Blotter', '2026-01-06 10:24:00'),
(125, 1, 'Updated blotter case #1 status to Hearing', '2026-01-06 10:24:10');

-- --------------------------------------------------------

--
-- Table structure for table `issuance`
--

CREATE TABLE `issuance` (
  `issuance_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `request_control_no` varchar(50) DEFAULT NULL,
  `document_type` varchar(100) NOT NULL,
  `purpose` text NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'Pending',
  `payment_status` varchar(50) DEFAULT 'Unpaid',
  `request_date` datetime DEFAULT current_timestamp(),
  `date_released` datetime DEFAULT NULL,
  `business_name` varchar(255) DEFAULT NULL,
  `business_location` varchar(255) DEFAULT NULL,
  `processed_by` varchar(100) DEFAULT NULL,
  `print_token` varchar(64) DEFAULT NULL,
  `print_expiry` datetime DEFAULT NULL,
  `download_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `issuance`
--

INSERT INTO `issuance` (`issuance_id`, `resident_id`, `request_control_no`, `document_type`, `purpose`, `price`, `status`, `payment_status`, `request_date`, `date_released`, `business_name`, `business_location`, `processed_by`, `print_token`, `print_expiry`, `download_count`) VALUES
(1, 3, 'REQ-0001', 'Barangay Clearance', 'Employment purposes', 50.00, 'Pending', 'Unpaid', '2026-01-05 10:32:39', NULL, NULL, NULL, NULL, NULL, NULL, 0),
(2, 3, 'REQ-20260105-9612', 'Barangay Clearance', 'for enrollment', 50.00, 'Pending', 'Unpaid', '2026-01-05 18:54:34', NULL, NULL, NULL, NULL, NULL, NULL, 0),
(3, 3, 'REQ-20260105-F505', 'Barangay Clearance', 'for apply job', 50.00, 'Pending', 'For Verification', '2026-01-05 18:55:23', NULL, NULL, NULL, NULL, NULL, NULL, 0),
(4, 3, 'REQ-20260105-B09F', 'Certificate of Residency', 'sdas', 50.00, 'Pending', 'Unpaid', '2026-01-05 18:56:49', NULL, NULL, NULL, NULL, NULL, NULL, 0),
(5, 3, 'REQ-20260105-D117', 'Certificate of Residency', 'dad', 50.00, 'Pending', 'Unpaid', '2026-01-05 18:59:11', NULL, NULL, NULL, NULL, NULL, NULL, 0),
(6, 3, 'REQ-20260105-9140', 'Barangay Clearance', 'dsada', 50.00, 'Pending', 'Unpaid', '2026-01-05 18:59:59', NULL, NULL, NULL, NULL, NULL, NULL, 0),
(7, 3, 'REQ-20260105-AF52', 'Barangay Clearance', 'dsada', 50.00, 'Pending', 'For Verification', '2026-01-05 19:02:58', NULL, NULL, NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `issuance_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `proof_image` varchar(255) DEFAULT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Verified'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `issuance_id`, `amount`, `payment_method`, `reference_no`, `proof_image`, `payment_date`, `status`) VALUES
(1, 3, 50.00, 'Online', NULL, 'PAY_3_1767610523.jpg', '2026-01-05 18:55:23', 'Pending'),
(2, 7, 50.00, 'Online', NULL, 'PAY_7_1767610978.jpg', '2026-01-05 19:02:58', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `resident_profiles`
--

CREATE TABLE `resident_profiles` (
  `resident_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `suffix_name` varchar(10) DEFAULT NULL,
  `birthdate` date NOT NULL,
  `birthplace` varchar(100) DEFAULT NULL,
  `age` int(3) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `is_pwd` enum('Yes','No') DEFAULT 'No',
  `pwd_id_file` varchar(255) DEFAULT NULL,
  `civil_status` varchar(50) NOT NULL,
  `contact_no` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `purok` varchar(50) DEFAULT NULL,
  `household_no` varchar(50) DEFAULT NULL,
  `resident_since` int(4) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `monthly_income` varchar(50) DEFAULT NULL,
  `voter_status` enum('Registered','Not Registered') DEFAULT 'Not Registered',
  `is_family_head` enum('Yes','No') DEFAULT 'No',
  `status` enum('Active','Archived','Pending','Rejected') NOT NULL DEFAULT 'Pending',
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resident_profiles`
--

INSERT INTO `resident_profiles` (`resident_id`, `user_id`, `first_name`, `middle_name`, `last_name`, `suffix_name`, `birthdate`, `birthplace`, `age`, `gender`, `is_pwd`, `pwd_id_file`, `civil_status`, `contact_no`, `email`, `address`, `city`, `province`, `purok`, `household_no`, `resident_since`, `occupation`, `monthly_income`, `voter_status`, `is_family_head`, `status`, `image`) VALUES
(3, 6, 'John Mark', 'Molina', 'Lastrollo', '', '2005-01-04', 'Taguig City', 21, 'Male', 'No', NULL, 'Single', '09876665432', 'jmlas@example.coms', 'blk 78 lot 13 st. john ph3 city homes resort ville1', 'Dasmarinas City', 'Cavite', 'Phase 3', '4', 2026, 'student', 'Below PHP 10,000', 'Registered', 'No', 'Active', 'RES_6_1767622907.png'),
(4, 7, 'dasdd', 'adsad', 'adsadad', NULL, '2005-02-05', NULL, 20, 'Male', 'No', NULL, 'Single', '09876665432', 'las@gmail.com', 'blk 78 lot 13 st. john ph3 city homes resort ville1', NULL, NULL, 'Phase 3', NULL, NULL, 'student', NULL, 'Registered', 'No', 'Archived', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `role` enum('Admin','Staff','Resident') NOT NULL DEFAULT 'Resident',
  `status` enum('Active','Inactive','Pending','Rejected') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `position`, `password`, `first_name`, `last_name`, `role`, `status`, `created_at`) VALUES
(1, 'sec.juan@langkaan2.com', 'Secretary', '$2y$10$DhWHy4gSI5E1Fz3h0R9Z7.ph8r/baDBKOYm6pFVGh3r03qzZ/W3lW', 'Juan Paolo', 'Melad', 'Admin', 'Active', '2026-01-02 17:09:50'),
(2, 'maria.staff1@langkaan2.com', 'Staff 1', '$2y$10$QAEm.aLWYsflyZALgjmOEeJb059vYx9DM5yWXOSOwBMfC9RbYlU7a', 'Maria', 'Santos', 'Staff', 'Active', '2026-01-02 17:09:50'),
(3, 'roberto.staff2@langkaan2.com', 'Staff 2', '$2y$10$L4ANKri6QlEK0vqvzfrlaenURicz3qQhdkE2v6J5Sd37YaB5orzzW', 'Roberto', 'Gomez', 'Staff', 'Active', '2026-01-02 17:09:50'),
(6, 'jmlas@example.com', NULL, '$2y$10$TZRnU06SuoECF0YXcHGjY.YJoSJv/VDGIdFXX92Aoy18rfGfG8pGK', 'John Mark', 'Lastrollo', 'Resident', 'Active', '2026-01-03 15:29:07'),
(7, 'las@gmail.com', NULL, '$2y$10$.CKp9Vt5B8c9JrBz8xEzQuPJDDIIQoVY7ph9vpzFqYY1mE7i.GvCe', 'dasdd', 'adsadad', 'Resident', 'Inactive', '2026-01-03 16:57:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`);

--
-- Indexes for table `barangay_officials`
--
ALTER TABLE `barangay_officials`
  ADD PRIMARY KEY (`official_id`);

--
-- Indexes for table `blotter_records`
--
ALTER TABLE `blotter_records`
  ADD PRIMARY KEY (`blotter_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`);

--
-- Indexes for table `complaint_conversations`
--
ALTER TABLE `complaint_conversations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `financial_records`
--
ALTER TABLE `financial_records`
  ADD PRIMARY KEY (`finance_id`);

--
-- Indexes for table `health_appointments`
--
ALTER TABLE `health_appointments`
  ADD PRIMARY KEY (`appointment_id`);

--
-- Indexes for table `health_records`
--
ALTER TABLE `health_records`
  ADD PRIMARY KEY (`record_id`);

--
-- Indexes for table `history_logs`
--
ALTER TABLE `history_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `issuance`
--
ALTER TABLE `issuance`
  ADD PRIMARY KEY (`issuance_id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `issuance_id` (`issuance_id`);

--
-- Indexes for table `resident_profiles`
--
ALTER TABLE `resident_profiles`
  ADD PRIMARY KEY (`resident_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `barangay_officials`
--
ALTER TABLE `barangay_officials`
  MODIFY `official_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `blotter_records`
--
ALTER TABLE `blotter_records`
  MODIFY `blotter_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `complaint_conversations`
--
ALTER TABLE `complaint_conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financial_records`
--
ALTER TABLE `financial_records`
  MODIFY `finance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `health_appointments`
--
ALTER TABLE `health_appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `health_records`
--
ALTER TABLE `health_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `history_logs`
--
ALTER TABLE `history_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `issuance`
--
ALTER TABLE `issuance`
  MODIFY `issuance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `resident_profiles`
--
ALTER TABLE `resident_profiles`
  MODIFY `resident_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `resident_profiles`
--
ALTER TABLE `resident_profiles`
  ADD CONSTRAINT `fk_resident_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
