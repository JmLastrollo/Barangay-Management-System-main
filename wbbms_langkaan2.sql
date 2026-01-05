-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 05, 2026 at 06:31 AM
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
(2, 'dasdaddsads', 'adasdasddasdadsadds', '1767444084_announcement3.png', '2026-01-10', '09:00:00', 'city homes resort ville 1 covered court r5 barangay langkaan 2', 'Active'),
(3, 'Barangay General Assembly 2022', 'Inaanyayahan ang lahat na dumalo sa ating quarterly meeting.', '', '2022-10-15', '08:00:00', 'Barangay Hall', 'Active'),
(4, 'Paskong Barangay 2022', 'Christmas Party at Gift Giving para sa mga bata.', '', '2022-12-20', '16:00:00', 'Covered Court', 'Active'),
(5, 'Libreng Bakuna sa Aso', 'Anti-rabies vaccination drive para sa mga alagang hayop.', '', '2023-02-10', '09:00:00', 'Barangay Plaza', 'Active'),
(6, 'Summer Sports Fest 2023', 'Basketball at Volleyball League registration is now open.', '', '2023-04-05', '07:00:00', 'Sports Complex', 'Active'),
(7, 'Oplan Linis Barangay', 'Tapat mo, linis mo program launch.', '', '2023-07-22', '06:00:00', 'Zone 1 to Zone 4', 'Active'),
(8, 'Medical at Dental Mission', 'Libreng check-up at bunot ng ngipin.', '', '2023-09-15', '08:00:00', 'Health Center', 'Active'),
(9, 'Distribusyon ng Senior Citizen Pension', 'Pagbibigay ng monthly pension para sa mga rehistradong senior.', '', '2023-11-18', '13:00:00', 'Multi-purpose Hall', 'Active'),
(10, 'Voters Registration Assistance', 'Tulong para sa mga magpaparehistro sa COMELEC.', '', '2024-01-08', '08:00:00', 'Barangay Hall', 'Active'),
(11, 'Womens Month Celebration', 'Zumba at Seminar para sa mga kababaihan.', '', '2024-03-08', '15:00:00', 'Barangay Plaza', 'Active'),
(12, 'Flores de Mayo 2024', 'Sagala at prusisyon para sa kapistahan.', '', '2024-05-25', '17:00:00', 'Main Road', 'Active');

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
(1, 'Cap. David Laudato', 'Barangay Captain', '2023-01-21', '2026-01-21', 'Active', 'off_1767507834_695a077aa0ae9.jpg'),
(2, 'Sec. Juan Paolo Melad', 'Secretary', '2025-08-13', '2026-01-21', 'Active', 'off_1767509958_695a0fc6db3a2.png'),
(4, 'Luchi Antonio', 'Treasurer', '2023-01-21', '2026-01-21', 'Active', 'off_1767513240_695a1c984178a.jpg'),
(5, 'Hon. Alberto Bautista', 'Kagawad', '2023-01-21', '2026-01-21', 'Active', 'off_1767513268_695a1cb441e33.jpg'),
(6, 'Hon. Alberto Jr G. Agustin', 'Kagawad', '2023-01-21', '2026-01-21', 'Active', 'off_1767513289_695a1cc900455.png'),
(7, 'Hon. Alfeo S. Sollegue', 'Kagawad', '2023-01-21', '2026-01-21', 'Active', 'off_1767513309_695a1cdd72589.png'),
(8, 'Hon. Danilo S. Galinato', 'Kagawad', '2023-01-21', '2026-01-21', 'Active', 'off_1767513332_695a1cf4cc6ac.png'),
(9, 'Hon. Fernando B. Laudato Jr.', 'Kagawad', '2023-01-21', '2026-01-21', 'Active', 'off_1767513366_695a1d16dde0a.png'),
(10, 'Hon. Mark Henry Barcena', 'Kagawad', '2023-01-21', '2026-01-21', 'Active', 'off_1767513430_695a1d56ed6fe.jpg'),
(11, 'Hon. Enrico Sango', 'Kagawad', '2023-01-21', '2026-01-21', 'Active', 'off_1767513461_695a1d7554f71.jpg'),
(12, 'Sk Chair. Jhimwell Rivera', 'SK Chairman', '2025-01-21', '2026-01-05', 'Active', 'off_1767514776_695a22989dacb.png');

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
  `recorded_by` int(11) DEFAULT NULL,
  `status_archive` enum('Active','Archived') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `complainant_name` varchar(150) NOT NULL,
  `contact_no` varchar(20) NOT NULL,
  `complaint_details` text NOT NULL,
  `date_filed` datetime DEFAULT current_timestamp(),
  `status` enum('Pending','Verified','Dismissed') DEFAULT 'Pending',
  `evidence_file` varchar(255) DEFAULT NULL
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
(24, 1, 'Logged in to the system', '2026-01-05 09:41:43');

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
(1, 3, 'REQ-0001', 'Barangay Clearance', 'Employment purposes', 50.00, 'Pending', 'Unpaid', '2026-01-05 10:32:39', NULL, NULL, NULL, NULL, NULL, NULL, 0);

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

INSERT INTO `resident_profiles` (`resident_id`, `user_id`, `first_name`, `middle_name`, `last_name`, `suffix_name`, `birthdate`, `birthplace`, `age`, `gender`, `is_pwd`, `civil_status`, `contact_no`, `email`, `address`, `city`, `province`, `purok`, `household_no`, `resident_since`, `occupation`, `monthly_income`, `voter_status`, `is_family_head`, `status`, `image`) VALUES
(3, 6, 'John Mark', 'Molina', 'Lastrollo', '', '2005-01-04', 'Taguig City', 20, 'Male', 'No', 'Single', '09876665432', 'jmlas@example.coms', 'blk 78 lot 13 st. john ph3 city homes resort ville1', 'Dasmarinas City', 'Cavite', 'Phase 3', '4', 2026, 'student', 'Below PHP 10,000', 'Registered', 'No', 'Active', NULL),
(4, 7, 'dasdd', 'adsad', 'adsadad', NULL, '2005-02-05', NULL, 20, 'Male', 'No', 'Single', '09876665432', 'las@gmail.com', 'blk 78 lot 13 st. john ph3 city homes resort ville1', NULL, NULL, 'Phase 3', NULL, NULL, 'student', NULL, 'Registered', 'No', 'Archived', '');

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
(6, 'jmlas@example.com', NULL, '$2y$10$/BEsCMG7aLItdfWWDtbA0eq2tq88qepK7YHdduUvt0iqV9qK1YpJW', 'John Mark', 'Lastrollo', 'Resident', 'Active', '2026-01-03 15:29:07'),
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
-- Indexes for table `financial_records`
--
ALTER TABLE `financial_records`
  ADD PRIMARY KEY (`finance_id`);

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
  MODIFY `blotter_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financial_records`
--
ALTER TABLE `financial_records`
  MODIFY `finance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `health_records`
--
ALTER TABLE `health_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `history_logs`
--
ALTER TABLE `history_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `issuance`
--
ALTER TABLE `issuance`
  MODIFY `issuance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

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
