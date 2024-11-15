-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 15, 2024 at 02:39 PM
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
-- Database: `tuteefind`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(60) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `photo` varchar(150) NOT NULL,
  `created_on` date NOT NULL,
  `emailaddress` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `firstname`, `lastname`, `photo`, `created_on`, `emailaddress`) VALUES
(1, 'admin', '$2y$10$zm6b0X.uFzTQbFQXyIZVL.647baX37tdvdVuJhOjK5cCSxfQqqD5y', 'Jasmine', 'Fernandez', 'tuteefind.jpg', '2018-04-02', 'findtutee@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `archive_professor`
--

CREATE TABLE `archive_professor` (
  `id` int(11) NOT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `middlename` varchar(255) DEFAULT NULL,
  `faculty_id` varchar(255) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `emailaddress` varchar(255) DEFAULT NULL,
  `prof_username` varchar(255) DEFAULT NULL,
  `prof_password` varchar(255) DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archive_requests`
--

CREATE TABLE `archive_requests` (
  `request_id` int(11) NOT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `tutee_id` int(11) DEFAULT NULL,
  `status` enum('pending','accepted','rejected','removed') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archive_tutee`
--

CREATE TABLE `archive_tutee` (
  `id` int(11) NOT NULL,
  `firstname` varchar(30) DEFAULT NULL,
  `lastname` varchar(30) DEFAULT NULL,
  `age` varchar(255) DEFAULT NULL,
  `sex` varchar(255) DEFAULT NULL,
  `number` varchar(255) DEFAULT NULL,
  `guardianname` varchar(255) DEFAULT NULL,
  `fblink` varchar(255) DEFAULT NULL,
  `barangay` varchar(50) DEFAULT NULL,
  `tutee_bday` date DEFAULT NULL,
  `school` varchar(50) DEFAULT NULL,
  `grade` varchar(20) DEFAULT NULL,
  `emailaddress` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `archive_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archive_tutee_progress`
--

CREATE TABLE `archive_tutee_progress` (
  `id` int(11) NOT NULL,
  `tutee_id` int(11) DEFAULT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `week_number` int(11) DEFAULT NULL,
  `uploaded_files` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archive_tutor`
--

CREATE TABLE `archive_tutor` (
  `id` int(11) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `number` varchar(20) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `student_id` varchar(50) NOT NULL,
  `course` varchar(100) DEFAULT NULL,
  `year_section` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `professor` varchar(50) DEFAULT NULL,
  `fblink` varchar(255) DEFAULT NULL,
  `emailaddress` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `archive_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `bio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archive_tutor_ratings`
--

CREATE TABLE `archive_tutor_ratings` (
  `id` int(11) NOT NULL,
  `tutee_id` int(11) DEFAULT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `pdf_content` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archive_tutor_sessions`
--

CREATE TABLE `archive_tutor_sessions` (
  `id` int(11) NOT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `tutee_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `districts`
--

CREATE TABLE `districts` (
  `id` int(11) NOT NULL,
  `barangay` varchar(255) NOT NULL,
  `district` enum('District 1','District 2') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `districts`
--

INSERT INTO `districts` (`id`, `barangay`, `district`) VALUES
(1, 'Arkong Bato', 'District 1'),
(2, 'Balangkas', 'District 1'),
(3, 'Bignay', 'District 1'),
(4, 'Bisig', 'District 1'),
(5, 'Canumay East', 'District 1'),
(6, 'Canumay West', 'District 1'),
(7, 'Coloong', 'District 1'),
(8, 'Dalandanan', 'District 1'),
(9, 'Isla', 'District 1'),
(10, 'Lawang Bato', 'District 1'),
(11, 'Lingunan', 'District 1'),
(12, 'Mabolo', 'District 1'),
(13, 'Malanday', 'District 1'),
(14, 'Malinta', 'District 1'),
(15, 'Palasan', 'District 1'),
(16, 'Pariancillo Villa', 'District 1'),
(17, 'Pasolo', 'District 1'),
(18, 'Poblacion', 'District 1'),
(19, 'Pulo', 'District 1'),
(20, 'Punturin', 'District 1'),
(21, 'Rincon', 'District 1'),
(22, 'Tagalag', 'District 1'),
(23, 'Veinte Reales', 'District 1'),
(24, 'Wawang Pulo', 'District 1'),
(25, 'Bagbaguin', 'District 2'),
(26, 'Gen. T. de Leon', 'District 2'),
(27, 'Karuhatan', 'District 2'),
(28, 'Mapulang Lupa', 'District 2'),
(29, 'Marulas', 'District 2'),
(30, 'Maysan', 'District 2'),
(31, 'Parada', 'District 2'),
(32, 'Paso de Blas', 'District 2'),
(33, 'Ugong', 'District 2');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `event_name` varchar(255) NOT NULL,
  `rendered_hours` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `attached_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `tutee_id` int(11) DEFAULT NULL,
  `sender_type` enum('tutor','tutee') DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(20) DEFAULT 'unread',
  `date_sent` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `professor`
--

CREATE TABLE `professor` (
  `id` int(11) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `middlename` varchar(20) NOT NULL,
  `age` int(11) NOT NULL,
  `faculty_id` varchar(20) NOT NULL,
  `emailaddress` varchar(50) DEFAULT NULL,
  `prof_password` varchar(250) NOT NULL,
  `prof_username` varchar(250) NOT NULL,
  `prof_photo` varchar(250) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `professor`
--

INSERT INTO `professor` (`id`, `firstname`, `lastname`, `middlename`, `age`, `faculty_id`, `emailaddress`, `prof_password`, `prof_username`, `prof_photo`, `last_login`) VALUES
(1, 'Jasmine', 'Fernandez', 'Meralles', 27, '21-1261', 'jasminefernandez031@gmail.com', '$2y$10$tBHaytdxK5Y/NYISs6IgJesJipSX4Mh7T/2PP9uhx7Sr/MFA6IhXC', 'prof1', NULL, NULL),
(2, 'John Paul', 'Gracio', 'Meralles', 27, '21-1262', 'jasmine.elisolutions@gmail.com', '$2y$10$z.c0oT.hD9pssSA3A8cNleTLWfy95/wQtiv1FeIbzOn6ivhDLvJ1O', 'prof2', NULL, NULL),
(3, 'Clarisse', 'Dizon', 'Claus', 27, '21-1263', 'fernandezjasmine095@gmail.com', '$2y$10$3f8vlnnEmwmafR/dWoCKeOky6qpTJSL5vIljuIaeDdgQpplh5oej.', 'prof3', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `professor_logs`
--

CREATE TABLE `professor_logs` (
  `id` int(11) NOT NULL,
  `professor_id` int(11) DEFAULT NULL,
  `activity` varchar(255) NOT NULL,
  `datetime` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `request_id` int(11) NOT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `tutee_id` int(11) DEFAULT NULL,
  `status` enum('pending','accepted','rejected','removed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tutee`
--

CREATE TABLE `tutee` (
  `id` int(11) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `age` varchar(255) NOT NULL,
  `sex` varchar(255) NOT NULL,
  `number` varchar(255) NOT NULL,
  `guardianname` varchar(255) NOT NULL,
  `fblink` varchar(255) NOT NULL,
  `barangay` varchar(50) NOT NULL,
  `emailaddress` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `tutee_bday` varchar(255) NOT NULL,
  `school` varchar(255) NOT NULL,
  `grade` varchar(255) NOT NULL,
  `bio` text NOT NULL,
  `address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tutee`
--

INSERT INTO `tutee` (`id`, `firstname`, `lastname`, `age`, `sex`, `number`, `guardianname`, `fblink`, `barangay`, `emailaddress`, `photo`, `password`, `tutee_bday`, `school`, `grade`, `bio`, `address`) VALUES
(1, 'Steve', 'Thunder', '6', 'Male', '1234', 'Steve Thunder', 'fawadawd', 'Mapulang Lupa', 'mularwarren@gmail.com', NULL, '$2y$10$4VAGquJsh3XBW5AanLgrpesohzlbeAihHuPiRR/83iXQYCMlTjJt2', '2018-06-06', 'PLV', 'Grade 1', 'Tell About yourself.', 'dwadawda');

-- --------------------------------------------------------

--
-- Table structure for table `tutee_progress`
--

CREATE TABLE `tutee_progress` (
  `id` int(11) NOT NULL,
  `tutee_id` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `week_number` int(11) NOT NULL,
  `uploaded_files` varchar(255) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `date` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `rendered_hours` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tutee_summary`
--

CREATE TABLE `tutee_summary` (
  `tutee_id` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `completed_weeks` int(11) DEFAULT 0,
  `registered_weeks` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tutor`
--

CREATE TABLE `tutor` (
  `id` int(11) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `number` varchar(20) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `year_section` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `professor` varchar(50) NOT NULL,
  `fblink` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `emailaddress` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tutor`
--

INSERT INTO `tutor` (`id`, `firstname`, `lastname`, `age`, `sex`, `number`, `barangay`, `student_id`, `course`, `year_section`, `photo`, `professor`, `fblink`, `bio`, `emailaddress`, `password`) VALUES
(11, 'Jasmine', 'Fernandez', 21, 'Female', '9682226610', 'Dalandanan', '21-1251', 'BSIT', '2-2', NULL, '21-1261', 'jasmine/me.', 'bio', 'fernandezjasmine095@gmail.com', '$2y$10$ZjqsBry3Ob.1YHorQyS6leC/.jxAkNU6oRt86GNXj6GohHVQTZ3Fu'),
(12, 'Lyka', 'Fernandez', 20, 'Female', '9682226610', 'Parada', '21-1252', 'BSED', '2-3', NULL, '21-1261', 'lyka/me.', 'bio', 'fernandezjasmine095@gmail.com', '$2y$10$PK40wBpiJDUmNa35OTW5cOqrmXqJbHq1vU1BmLVqypcCXMY7X4JUm'),
(13, 'Marbie', 'Fernandez', 19, 'Female', '9682226610', 'Maysan', '21-1253', 'BSIT', '2-4', NULL, '21-1262', 'marbie/me.', 'bio', 'fernandezjasmine095@gmail.com', '$2y$10$cMhvzr7n9Z9KMqfgwqiCTuAaoiaJ6NUNgj9W.yOYd54MV4LOhLJa.'),
(14, 'Clara', 'Fernandez', 19, 'Female', '9682226610', 'Maysan', '21-1254', 'BSIT', '2-5', NULL, '21-1262', 'marbie/me.', 'bio', 'fernandezjasmine095@gmail.com', '$2y$10$JTrpeqGXT0mEJBVFutiAd.qsWGRJA8K72HkjxIz7DfrfS5k52L/Ia'),
(15, 'Tiny', 'Fernandez', 19, 'Female', '9682226610', 'Maysan', '21-1255', 'BSIT', '2-6', NULL, '21-1262', 'jasmine/me.', 'bio', 'fernandezjasmine095@gmail.com', '$2y$10$qgVfab7KKbRsAdPCcqgA4eYFhN2L9kikkV/hB7VEXN/EsrBQO/cP2');

-- --------------------------------------------------------

--
-- Table structure for table `tutor_ratings`
--

CREATE TABLE `tutor_ratings` (
  `id` int(11) NOT NULL,
  `tutee_id` int(11) DEFAULT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `rating` text DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `pdf_content` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tutor_sessions`
--

CREATE TABLE `tutor_sessions` (
  `id` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `tutee_id` int(11) NOT NULL,
  `status` enum('ongoing','requested','completed') DEFAULT 'ongoing'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `archive_professor`
--
ALTER TABLE `archive_professor`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `archive_requests`
--
ALTER TABLE `archive_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `tutor_id` (`tutor_id`),
  ADD KEY `tutee_id` (`tutee_id`);

--
-- Indexes for table `archive_tutee`
--
ALTER TABLE `archive_tutee`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `archive_tutee_progress`
--
ALTER TABLE `archive_tutee_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutee_id` (`tutee_id`),
  ADD KEY `tutor_id` (`tutor_id`);

--
-- Indexes for table `archive_tutor`
--
ALTER TABLE `archive_tutor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`);
ALTER TABLE `archive_tutor` ADD FULLTEXT KEY `emailaddress` (`emailaddress`);

--
-- Indexes for table `archive_tutor_ratings`
--
ALTER TABLE `archive_tutor_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutee_id` (`tutee_id`),
  ADD KEY `tutor_id` (`tutor_id`);

--
-- Indexes for table `archive_tutor_sessions`
--
ALTER TABLE `archive_tutor_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutor_id` (`tutor_id`),
  ADD KEY `tutee_id` (`tutee_id`);

--
-- Indexes for table `districts`
--
ALTER TABLE `districts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `professor`
--
ALTER TABLE `professor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uc_faculty_id` (`faculty_id`);

--
-- Indexes for table `professor_logs`
--
ALTER TABLE `professor_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `fk_sender` (`tutor_id`),
  ADD KEY `fk_receiver` (`tutee_id`);

--
-- Indexes for table `tutee`
--
ALTER TABLE `tutee`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tutee_progress`
--
ALTER TABLE `tutee_progress`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tutor`
--
ALTER TABLE `tutor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_id` (`student_id`);

--
-- Indexes for table `tutor_ratings`
--
ALTER TABLE `tutor_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutee_id` (`tutee_id`),
  ADD KEY `tutor_id` (`tutor_id`);

--
-- Indexes for table `tutor_sessions`
--
ALTER TABLE `tutor_sessions`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `districts`
--
ALTER TABLE `districts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `professor`
--
ALTER TABLE `professor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `professor_logs`
--
ALTER TABLE `professor_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tutee`
--
ALTER TABLE `tutee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tutee_progress`
--
ALTER TABLE `tutee_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tutor`
--
ALTER TABLE `tutor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tutor_ratings`
--
ALTER TABLE `tutor_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tutor_sessions`
--
ALTER TABLE `tutor_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
