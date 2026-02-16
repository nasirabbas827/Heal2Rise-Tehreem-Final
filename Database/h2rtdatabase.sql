-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 16, 2026 at 07:59 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `h2rtdatabase`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `cases`
--

CREATE TABLE `cases` (
  `case_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ngo_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `case_status` enum('Assigned','Ongoing','Rehabilitation','Completed','Closed') DEFAULT 'Assigned',
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`case_id`, `user_id`, `ngo_id`, `team_id`, `case_status`, `assigned_at`, `closed_at`) VALUES
(1, 1, 1, 1, 'Ongoing', '2026-02-16 05:33:33', '2026-02-17 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `counseling_sessions`
--

CREATE TABLE `counseling_sessions` (
  `session_id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `session_date` datetime NOT NULL,
  `notes` text DEFAULT NULL,
  `next_session_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `counseling_sessions`
--

INSERT INTO `counseling_sessions` (`session_id`, `case_id`, `session_date`, `notes`, `next_session_date`, `created_at`) VALUES
(1, 1, '2026-02-16 10:42:00', 'XYZ', '2026-02-28 10:42:00', '2026-02-16 05:42:30');

-- --------------------------------------------------------

--
-- Table structure for table `ngos`
--

CREATE TABLE `ngos` (
  `ngo_id` int(11) NOT NULL,
  `ngo_name` varchar(200) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `specialization` enum('Depression','Family Issues','Marital Issues','General Counseling','Rehabilitation') NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ngos`
--

INSERT INTO `ngos` (`ngo_id`, `ngo_name`, `email`, `password`, `phone`, `address`, `specialization`, `is_verified`, `created_at`) VALUES
(1, 'Eedhi', 'ngo@gmail.com', '$2y$10$siOTPRKpgo.nyOE7qqr6o.JZi51.lgbGPdq3NMYtH51xzq7aQrL0C', '03176526827', 'Street jeff xxxx\r\nAp04', 'Depression', 1, '2026-02-16 05:21:51');

-- --------------------------------------------------------

--
-- Table structure for table `progress_updates`
--

CREATE TABLE `progress_updates` (
  `progress_id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `progress_note` text NOT NULL,
  `progress_level` enum('Low','Moderate','Good','Excellent') DEFAULT 'Low',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `progress_updates`
--

INSERT INTO `progress_updates` (`progress_id`, `case_id`, `updated_by`, `progress_note`, `progress_level`, `created_at`) VALUES
(1, 1, 1, 'fdsafd', 'Good', '2026-02-16 05:44:32');

-- --------------------------------------------------------

--
-- Table structure for table `skill_development`
--

CREATE TABLE `skill_development` (
  `skill_id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `skill_name` varchar(150) NOT NULL,
  `skill_status` enum('Learning','Practicing','Completed') DEFAULT 'Learning',
  `remarks` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skill_development`
--

INSERT INTO `skill_development` (`skill_id`, `case_id`, `skill_name`, `skill_status`, `remarks`, `updated_at`) VALUES
(1, 1, 'fds', 'Learning', 'adsfs', '2026-02-16 05:46:13');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `team_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Counselor','Psychiatrist','Social Worker','Monitor') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`team_id`, `full_name`, `email`, `password`, `role`, `phone`, `created_at`) VALUES
(1, 'Nasir Abbas', 'nasiryt.827@gmail.com', '$2y$10$C5E1bddjKrjttzWWz7TC8ea7B89Ngz2ImMEIhh2SjxoMRPn3yFn1y', 'Counselor', '03266029050', '2026-02-16 05:31:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `issue_type` enum('Depression','Family Issues','Marital Issues','Hopelessness','Other') NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Pending','Assigned','Under Treatment','Recovered','Closed') DEFAULT 'Pending',
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `phone`, `gender`, `issue_type`, `description`, `status`, `is_verified`, `created_at`) VALUES
(1, 'NASIR ABBAS', 'nasiryt.827@gmail.com', '$2y$10$naB0ZF/oT9UaFniZy55xxuvi64ZnZO2FHofZVvWND88osqOXi0jsS', '03176526827', 'Male', 'Depression', 'fadsfd', 'Pending', 1, '2026-02-16 05:22:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cases`
--
ALTER TABLE `cases`
  ADD PRIMARY KEY (`case_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ngo_id` (`ngo_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `counseling_sessions`
--
ALTER TABLE `counseling_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `case_id` (`case_id`);

--
-- Indexes for table `ngos`
--
ALTER TABLE `ngos`
  ADD PRIMARY KEY (`ngo_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `progress_updates`
--
ALTER TABLE `progress_updates`
  ADD PRIMARY KEY (`progress_id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `skill_development`
--
ALTER TABLE `skill_development`
  ADD PRIMARY KEY (`skill_id`),
  ADD KEY `case_id` (`case_id`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`team_id`),
  ADD UNIQUE KEY `email` (`email`);

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
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cases`
--
ALTER TABLE `cases`
  MODIFY `case_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `counseling_sessions`
--
ALTER TABLE `counseling_sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ngos`
--
ALTER TABLE `ngos`
  MODIFY `ngo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `progress_updates`
--
ALTER TABLE `progress_updates`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `skill_development`
--
ALTER TABLE `skill_development`
  MODIFY `skill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `team_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cases`
--
ALTER TABLE `cases`
  ADD CONSTRAINT `cases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cases_ibfk_2` FOREIGN KEY (`ngo_id`) REFERENCES `ngos` (`ngo_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cases_ibfk_3` FOREIGN KEY (`team_id`) REFERENCES `team_members` (`team_id`) ON DELETE CASCADE;

--
-- Constraints for table `counseling_sessions`
--
ALTER TABLE `counseling_sessions`
  ADD CONSTRAINT `counseling_sessions_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`case_id`) ON DELETE CASCADE;

--
-- Constraints for table `progress_updates`
--
ALTER TABLE `progress_updates`
  ADD CONSTRAINT `progress_updates_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`case_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `progress_updates_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `team_members` (`team_id`) ON DELETE CASCADE;

--
-- Constraints for table `skill_development`
--
ALTER TABLE `skill_development`
  ADD CONSTRAINT `skill_development_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`case_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
