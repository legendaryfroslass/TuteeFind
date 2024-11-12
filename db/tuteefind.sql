-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 11, 2024 at 09:27 AM
-- Server version: 8.0.39-1
-- PHP Version: 8.3.11

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
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int NOT NULL,
  `professor_id` int NOT NULL,
  `activity` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `datetime` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(60) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `photo` varchar(150) NOT NULL,
  `created_on` date NOT NULL,
  `emailaddress` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `id` int NOT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `middlename` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `faculty_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `age` int DEFAULT NULL,
  `emailaddress` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `prof_username` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `prof_password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archive_requests`
--

CREATE TABLE `archive_requests` (
  `request_id` int NOT NULL,
  `tutor_id` int DEFAULT NULL,
  `tutee_id` int DEFAULT NULL,
  `status` enum('pending','accepted','rejected') COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archive_requests`
--

INSERT INTO `archive_requests` (`request_id`, `tutor_id`, `tutee_id`, `status`) VALUES
(94, 96, 58, ''),
(106, 111, 60, 'accepted'),
(109, 111, 58, 'accepted');

-- --------------------------------------------------------

--
-- Table structure for table `archive_tutee`
--

CREATE TABLE `archive_tutee` (
  `id` int NOT NULL,
  `firstname` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `lastname` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `age` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `sex` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `number` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `guardianname` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `fblink` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `barangay` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tutee_bday` date NOT NULL,
  `school` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `grade` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `emailaddress` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `archive_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archive_tutee`
--

INSERT INTO `archive_tutee` (`id`, `firstname`, `lastname`, `age`, `sex`, `number`, `guardianname`, `fblink`, `barangay`, `tutee_bday`, `school`, `grade`, `emailaddress`, `photo`, `password`, `archive_at`) VALUES
(2, 'John Paul', 'Gracio', '21', 'Male', '09196332121', 'Eljohn', 'johnpaul.gracio.96', 'Pasolo', '2024-09-11', 'Valenzuela Elementary School', '2', 'ceit.monis@gmail.com', '', '245bc97ec8d9d70c0a8c2c6048e6afea53965f080a86e9bf1b4130bf3b7af432', '2024-09-08 13:27:10'),
(3, 'Juan', 'Dela Cruz', '6', 'Male', '09771240013', 'eljohn cuaresma', 'https://www.facebook.com/eljohn.cuaresma.54/', 'Maysan', '0000-00-00', 'Maysan Elementary School', '1', 'eljohn.cuaresma6@gmail.com', '../uploads/Hi_Cuaresma.jpg', '$2y$10$29X1u01l3fGN8KRRcu8VkumSidiy3S.eC74jpHlUqcsxo8Auq1Tgi', '2024-11-06 10:33:38');

-- --------------------------------------------------------

--
-- Table structure for table `archive_tutee_progress`
--

CREATE TABLE `archive_tutee_progress` (
  `id` int NOT NULL,
  `tutee_id` int DEFAULT NULL,
  `tutor_id` int DEFAULT NULL,
  `week_number` int DEFAULT NULL,
  `uploaded_files` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archive_tutee_progress`
--

INSERT INTO `archive_tutee_progress` (`id`, `tutee_id`, `tutor_id`, `week_number`, `uploaded_files`, `description`, `date`) VALUES
(303, 58, 111, 1, '../uploads/kda.jpg', 'dwadawda', '2024-11-05 20:07:52');

-- --------------------------------------------------------

--
-- Table structure for table `archive_tutor`
--

CREATE TABLE `archive_tutor` (
  `id` int NOT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `age` int NOT NULL,
  `sex` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `number` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `barangay` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `student_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `course` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `year_section` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `professor` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fblink` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emailaddress` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `archive_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `bio` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archive_tutor`
--

INSERT INTO `archive_tutor` (`id`, `firstname`, `lastname`, `age`, `sex`, `number`, `barangay`, `student_id`, `course`, `year_section`, `photo`, `professor`, `fblink`, `emailaddress`, `password`, `archive_at`, `bio`) VALUES
(111, 'Eljohn', 'Fernandez', 21, 'Female', '09682226610', 'Dalandanan', '21-1251', 'BSIT', '2-2', NULL, '21-1261', 'jasmine/me.', 'eljohn.cuaresma6@gmail.com', '$2y$10$bgddRa1O7U2vfD/FM0p1vOkOP77Zehm2zCpFZ4H/vWQpsgLSrGCj.', '2024-11-06 10:33:31', '');

-- --------------------------------------------------------

--
-- Table structure for table `archive_tutor_ratings`
--

CREATE TABLE `archive_tutor_ratings` (
  `id` int NOT NULL,
  `tutee_id` int DEFAULT NULL,
  `tutor_id` int DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `comment` text COLLATE utf8mb4_general_ci,
  `pdf_content` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archive_tutor_ratings`
--

INSERT INTO `archive_tutor_ratings` (`id`, `tutee_id`, `tutor_id`, `rating`, `comment`, `pdf_content`) VALUES
(65, 58, 111, 0, 'test', '');

-- --------------------------------------------------------

--
-- Table structure for table `archive_tutor_sessions`
--

CREATE TABLE `archive_tutor_sessions` (
  `id` int NOT NULL,
  `tutor_id` int DEFAULT NULL,
  `tutee_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archive_tutor_sessions`
--

INSERT INTO `archive_tutor_sessions` (`id`, `tutor_id`, `tutee_id`) VALUES
(45, 111, 58);

-- --------------------------------------------------------

--
-- Table structure for table `districts`
--

CREATE TABLE `districts` (
  `id` int NOT NULL,
  `barangay` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `district` enum('District 1','District 2') COLLATE utf8mb4_general_ci NOT NULL
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
  `id` int NOT NULL,
  `tutor_id` int DEFAULT NULL,
  `event_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `rendered_hours` int DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `attached_file` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int NOT NULL,
  `tutor_id` int DEFAULT NULL,
  `tutee_id` int DEFAULT NULL,
  `sender_type` enum('tutor','tutee') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'unread',
  `date_sent` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `professor`
--

CREATE TABLE `professor` (
  `id` int NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `middlename` varchar(20) NOT NULL,
  `age` int NOT NULL,
  `faculty_id` varchar(20) NOT NULL,
  `emailaddress` varchar(50) NOT NULL,
  `prof_password` varchar(250) NOT NULL,
  `prof_username` varchar(250) NOT NULL,
  `prof_photo` varchar(250) NOT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `professor`
--

INSERT INTO `professor` (`id`, `firstname`, `lastname`, `middlename`, `age`, `faculty_id`, `emailaddress`, `prof_password`, `prof_username`, `prof_photo`, `last_login`) VALUES
(333, 'Jasmine', 'Fernandez', 'Meralles', 27, '21-1261', 'jasminefernandez031@gmail.com', '$2y$10$2eFaUB10.mTuWi99dbbF3eJVM.ZFsMHXWJtmRk4rqUsTDGYvOD1Oy', 'prof1', 'profile.jpg', NULL),
(334, 'John Paul', 'Gracio', 'Meralles', 27, '21-1262', 'jasmine.elisolutions@gmail.com', '$2y$10$haetOD1F0s.MU7i38VCzhuB6veZGLv3BgPt4kwv4o2W5rbTflDP4O', 'prof2', 'profile.jpg', NULL),
(335, 'Clarisse', 'Dizon', 'Claus', 27, '21-1263', 'fernandezjasmine095@gmail.com', '$2y$10$dLnTr9yHVj2IO0605fdPN.lvS1Hu1IdngHWrDRFCavqNyib.hR39S', 'prof3', 'profile.jpg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `request_id` int NOT NULL,
  `tutor_id` int DEFAULT NULL,
  `tutee_id` int DEFAULT NULL,
  `status` enum('pending','accepted','rejected','removed') COLLATE utf8mb4_general_ci DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tutee`
--

CREATE TABLE `tutee` (
  `id` int NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `age` varchar(255) NOT NULL,
  `sex` varchar(255) NOT NULL,
  `number` varchar(255) NOT NULL,
  `guardianname` varchar(255) NOT NULL,
  `fblink` varchar(255) NOT NULL,
  `barangay` varchar(50) NOT NULL,
  `emailaddress` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `tutee_bday` varchar(255) NOT NULL,
  `school` varchar(255) NOT NULL,
  `grade` varchar(255) NOT NULL,
  `bio` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tutee`
--

INSERT INTO `tutee` (`id`, `firstname`, `lastname`, `age`, `sex`, `number`, `guardianname`, `fblink`, `barangay`, `emailaddress`, `photo`, `password`, `tutee_bday`, `school`, `grade`, `bio`) VALUES
(60, 'Elmerson', 'Reyes', '8', 'Male', '09771240013', 'Elmerson Mirasol Cuaresma', 'https://www.facebook.com/eljohn.cuaresma.54/', 'Malinta', 'cuaresmaeljohn@gmail.com', '../uploads/John_Reyes.jpg', '$2y$10$O/M8Ffd9YdZB7yWOr5tCXu0mytdWl7YUqVZQAJoYKPwieqbOUDdsG', '2016-06-20', 'Maysan Elementary School', 'Grade 5', ''),
(61, 'John Paul', 'Gracio', '9', 'Male', '09480921896', 'mamamo', 'jp.com', 'Bignay', 'johnpaul.gracio.27@gmail.com', '', '$2y$10$7u2VGqajREwlQXFpHtxlAeVGZ2txdOP4146xFsSn8PCB6kpPy1PoS', '2015-08-27', 'plv', 'Grade 3', ''),
(62, 'Oreo', 'Dizon', '8', 'Male', '09499808419', 'Clarisse Dizon', 'https://www.facebook.com/clarisse.dizon.5264/', 'Coloong', 'itsoreodadoggo@gmail.com', '../uploads/Oreo_Dizon.jpg', '$2y$10$FqaAlTDZ3lvuJF/lJo7HW.CTTjKFTQMwhzf3LMJMk.KjzfsJlA3gC', '2015-01-01', 'San Diego Parochial School', 'Grade 3', '');

-- --------------------------------------------------------

--
-- Table structure for table `tutee_progress`
--

CREATE TABLE `tutee_progress` (
  `id` int NOT NULL,
  `tutee_id` int NOT NULL,
  `tutor_id` int NOT NULL,
  `week_number` int NOT NULL,
  `uploaded_files` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `date` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `rendered_hours` int NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tutee_summary`
--

CREATE TABLE `tutee_summary` (
  `tutee_id` int NOT NULL,
  `tutor_id` int NOT NULL,
  `completed_weeks` int DEFAULT '0',
  `registered_weeks` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tutor`
--

CREATE TABLE `tutor` (
  `id` int NOT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `age` int DEFAULT NULL,
  `sex` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `number` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `barangay` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `student_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `course` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `year_section` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `professor` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `fblink` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `bio` text COLLATE utf8mb4_general_ci NOT NULL,
  `emailaddress` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tutor`
--

INSERT INTO `tutor` (`id`, `firstname`, `lastname`, `age`, `sex`, `number`, `barangay`, `student_id`, `course`, `year_section`, `photo`, `professor`, `fblink`, `bio`, `emailaddress`, `password`) VALUES
(117, 'Jasmine', 'Fernandez', 21, 'Female', '9682226610', 'Dalandanan', '21-1251', 'BSIT', '2-2', NULL, '21-1261', 'jasmine/me.', 'bio', 'fernandezjasmine095@gmail.com', '$2y$10$/g5d8L0aBDGWU6TVBXRI7epWPKPIoJFVnahP9vy0Xyk7YHKDPYlIK'),
(118, 'Lyka', 'Fernandez', 20, 'Female', '9682226610', 'Parada', '21-1252', 'BSED', '2-3', NULL, '21-1261', 'lyka/me.', 'bio', 'fernandezjasmine095@gmail.com', '$2y$10$0ZAoqUCf1wyFgP3nDGO3DOCoW8ZKRsHgLI1qKr5xpzYvQEIUTioyK'),
(119, 'Marbie', 'Fernandez', 19, 'Female', '9682226610', 'Maysan', '21-1253', 'BSIT', '2-4', NULL, '21-1262', 'marbie/me.', 'bio', 'fernandezjasmine095@gmail.com', '$2y$10$fi2ytEf7c4x9jDOyCNRNyeiUV764yIPrUkl7jyauNrIZ9mlj8Qcay'),
(120, 'Clara', 'Fernandez', 19, 'Female', '9682226610', 'Maysan', '21-1254', 'BSIT', '2-5', NULL, '21-1262', 'marbie/me.', 'bio', 'fernandezjasmine095@gmail.com', '$2y$10$JwDJ5grUAEnKjG9P86pwQ.m2zRkkYz9YpAKdJsK2HZo6KGpKo83HG'),
(121, 'Tiny', 'Fernandez', 19, 'Female', '9682226610', 'Maysan', '21-1255', 'BSIT', '2-6', NULL, '21-1262', 'jasmine/me.', 'bio', 'fernandezjasmine095@gmail.com', '$2y$10$kQapw9dlhf/JIXXxVusw9OGWskXY52MPlZxSQOr2NKigYenbj4wHS');

-- --------------------------------------------------------

--
-- Table structure for table `tutor_ratings`
--

CREATE TABLE `tutor_ratings` (
  `id` int NOT NULL,
  `tutee_id` int DEFAULT NULL,
  `tutor_id` int DEFAULT NULL,
  `rating` text COLLATE utf8mb4_general_ci,
  `comment` text COLLATE utf8mb4_general_ci,
  `pdf_content` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tutor_sessions`
--

CREATE TABLE `tutor_sessions` (
  `id` int NOT NULL,
  `tutor_id` int NOT NULL,
  `tutee_id` int NOT NULL,
  `status` enum('ongoing','requested','completed') COLLATE utf8mb4_general_ci DEFAULT 'ongoing'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `professor_id` (`professor_id`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutee_id` (`tutee_id`);

--
-- Indexes for table `tutee_summary`
--
ALTER TABLE `tutee_summary`
  ADD PRIMARY KEY (`tutee_id`);

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tutor_id` (`tutor_id`,`tutee_id`),
  ADD KEY `tutee_id` (`tutee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `archive_professor`
--
ALTER TABLE `archive_professor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `archive_requests`
--
ALTER TABLE `archive_requests`
  MODIFY `request_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `archive_tutee`
--
ALTER TABLE `archive_tutee`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `archive_tutee_progress`
--
ALTER TABLE `archive_tutee_progress`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=304;

--
-- AUTO_INCREMENT for table `archive_tutor`
--
ALTER TABLE `archive_tutor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=218;

--
-- AUTO_INCREMENT for table `archive_tutor_ratings`
--
ALTER TABLE `archive_tutor_ratings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `archive_tutor_sessions`
--
ALTER TABLE `archive_tutor_sessions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `districts`
--
ALTER TABLE `districts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=241;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `professor`
--
ALTER TABLE `professor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=336;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `tutee`
--
ALTER TABLE `tutee`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `tutee_progress`
--
ALTER TABLE `tutee_progress`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=343;

--
-- AUTO_INCREMENT for table `tutor`
--
ALTER TABLE `tutor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `tutor_ratings`
--
ALTER TABLE `tutor_ratings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `tutor_sessions`
--
ALTER TABLE `tutor_sessions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `professor` (`id`);

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `fk_receiver` FOREIGN KEY (`tutee_id`) REFERENCES `tutee` (`id`),
  ADD CONSTRAINT `fk_sender` FOREIGN KEY (`tutor_id`) REFERENCES `tutor` (`id`);

--
-- Constraints for table `tutee_progress`
--
ALTER TABLE `tutee_progress`
  ADD CONSTRAINT `tutee_progress_ibfk_1` FOREIGN KEY (`tutee_id`) REFERENCES `tutee` (`id`);

--
-- Constraints for table `tutor_ratings`
--
ALTER TABLE `tutor_ratings`
  ADD CONSTRAINT `tutor_ratings_ibfk_1` FOREIGN KEY (`tutee_id`) REFERENCES `tutee` (`id`),
  ADD CONSTRAINT `tutor_ratings_ibfk_2` FOREIGN KEY (`tutor_id`) REFERENCES `tutor` (`id`);

--
-- Constraints for table `tutor_sessions`
--
ALTER TABLE `tutor_sessions`
  ADD CONSTRAINT `tutor_sessions_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `tutor` (`id`),
  ADD CONSTRAINT `tutor_sessions_ibfk_2` FOREIGN KEY (`tutee_id`) REFERENCES `tutee` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
