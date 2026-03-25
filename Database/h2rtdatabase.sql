-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 25, 2026 at 07:39 AM
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
  `category` enum('Depression','Family Issues','Marital Issues','Hopelessness','Other') NOT NULL,
  `description` text DEFAULT NULL,
  `ngo_id` int(11) DEFAULT NULL,
  `team_id` int(11) DEFAULT NULL,
  `case_status` enum('Pending','Assigned','Ongoing','Rehabilitation','Completed','Closed') DEFAULT 'Pending',
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_at` datetime DEFAULT NULL,
  `recommended_counselor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`case_id`, `user_id`, `category`, `description`, `ngo_id`, `team_id`, `case_status`, `assigned_at`, `closed_at`, `recommended_counselor_id`) VALUES
(1, 1, 'Depression', 'After Prototype ', 1, 1, 'Closed', '2026-03-25 06:04:39', '2026-03-25 00:00:00', 1);

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
(1, 1, '2026-02-16 10:42:00', 'XYZ', '2026-02-28 10:42:00', '2026-02-16 05:42:30'),
(2, 1, '2026-03-26 11:21:00', 'Prototype Session', '2026-04-09 11:22:00', '2026-03-25 06:22:07');

-- --------------------------------------------------------

--
-- Table structure for table `counselors`
--

CREATE TABLE `counselors` (
  `counselor_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `specialization` enum('Depression','Family Issues','Marital Issues','Hopelessness','Other') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `counselors`
--

INSERT INTO `counselors` (`counselor_id`, `name`, `email`, `password`, `phone`, `specialization`, `created_at`) VALUES
(1, 'counselor Ali', 'haider@gmail.com', '$2y$10$MyjKujFZCcuePJRzf0FYxO3cQH0cPnT0dFQqMCBI6GUOplj/.XtRm', NULL, 'Depression', '2026-03-25 06:13:23');

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
(2, 1, 'fds', 'Learning', 'fdsa', '2026-03-25 06:24:10'),
(3, 1, 'fds', 'Practicing', 'fdsa', '2026-03-25 06:24:19');

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
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `phone`, `gender`, `issue_type`, `description`, `status`, `is_verified`, `created_at`) VALUES
(1, 'NASIR ABBAS', 'nasiryt.827@gmail.com', '$2y$10$naB0ZF/oT9UaFniZy55xxuvi64ZnZO2FHofZVvWND88osqOXi0jsS', '03176526827', 'Male', 'Depression', 'fadsfd', 'Approved', 1, '2026-02-16 05:22:13');

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
  ADD PRIMARY KEY (`case_id`);

--
-- Indexes for table `counseling_sessions`
--
ALTER TABLE `counseling_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `case_id` (`case_id`);

--
-- Indexes for table `counselors`
--
ALTER TABLE `counselors`
  ADD PRIMARY KEY (`counselor_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `ngos`
--
ALTER TABLE `ngos`
  ADD PRIMARY KEY (`ngo_id`),
  ADD UNIQUE KEY `email` (`email`);

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
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `counselors`
--
ALTER TABLE `counselors`
  MODIFY `counselor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ngos`
--
ALTER TABLE `ngos`
  MODIFY `ngo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `skill_development`
--
ALTER TABLE `skill_development`
  MODIFY `skill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- Constraints for table `counseling_sessions`
--
ALTER TABLE `counseling_sessions`
  ADD CONSTRAINT `counseling_sessions_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`case_id`) ON DELETE CASCADE;

--
-- Constraints for table `skill_development`
--
ALTER TABLE `skill_development`
  ADD CONSTRAINT `skill_development_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`case_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
