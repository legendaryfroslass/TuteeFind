-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2024 at 05:01 AM
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
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `activity` varchar(255) NOT NULL,
  `datetime` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `professor_id`, `activity`, `datetime`) VALUES
(26, 331, 'Log-in', 'November 9, 2024 01:42:42 PM');

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
  `birthday` date DEFAULT NULL,
  `employment_status` varchar(255) DEFAULT NULL,
  `prof_username` varchar(255) DEFAULT NULL,
  `prof_password` varchar(255) DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archive_professor`
--

INSERT INTO `archive_professor` (`id`, `lastname`, `firstname`, `middlename`, `faculty_id`, `age`, `birthday`, `employment_status`, `prof_username`, `prof_password`, `archived_at`) VALUES
(100, 'Fernandez', 'Jasmine', 'Meralles', '21-1261', 27, '0000-00-00', 'Part-time', 'prof1', '$2y$10$AWnZxcbOYqNkqhDGj8v0/uBz7qeJ3aFV71wV2Xb2lI3rmHDXsAleS', '2024-11-06 10:33:23');

-- --------------------------------------------------------

--
-- Table structure for table `archive_requests`
--

CREATE TABLE `archive_requests` (
  `request_id` int(11) NOT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `tutee_id` int(11) DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT NULL
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
  `id` int(11) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `age` varchar(255) NOT NULL,
  `sex` varchar(255) NOT NULL,
  `number` varchar(255) NOT NULL,
  `guardianname` varchar(255) NOT NULL,
  `fblink` varchar(255) DEFAULT NULL,
  `barangay` varchar(50) NOT NULL,
  `tutee_bday` date NOT NULL,
  `school` varchar(50) NOT NULL,
  `grade` varchar(20) NOT NULL,
  `emailaddress` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `archive_at` timestamp NOT NULL DEFAULT current_timestamp()
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
  `id` int(11) NOT NULL,
  `tutee_id` int(11) DEFAULT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `week_number` int(11) DEFAULT NULL,
  `uploaded_files` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
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
  `id` int(11) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `age` int(11) NOT NULL,
  `sex` varchar(10) NOT NULL,
  `number` varchar(20) NOT NULL,
  `barangay` varchar(255) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `course` varchar(100) NOT NULL,
  `year_section` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `professor` varchar(50) DEFAULT NULL,
  `fblink` varchar(255) DEFAULT NULL,
  `emailaddress` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `archive_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archive_tutor`
--

INSERT INTO `archive_tutor` (`id`, `firstname`, `lastname`, `age`, `sex`, `number`, `barangay`, `student_id`, `course`, `year_section`, `photo`, `professor`, `fblink`, `emailaddress`, `password`, `archive_at`) VALUES
(111, 'Eljohn', 'Fernandez', 21, 'Female', '09682226610', 'Dalandanan', '21-1251', 'BSIT', '2-2', NULL, '21-1261', 'jasmine/me.', 'eljohn.cuaresma6@gmail.com', '$2y$10$bgddRa1O7U2vfD/FM0p1vOkOP77Zehm2zCpFZ4H/vWQpsgLSrGCj.', '2024-11-06 10:33:31');

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
  `id` int(11) NOT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `tutee_id` int(11) DEFAULT NULL
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

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `tutor_id`, `event_name`, `rendered_hours`, `description`, `attached_file`, `created_at`) VALUES
(21, 116, 'PLV', 20, 'HIV', 'bball.jpg', '2024-11-10 01:37:40'),
(22, 116, 'PLV', 5, 'dawda', 'Brown and Black Vintage National Heroes Day Philippines Instagram Post (1).png', '2024-11-10 03:36:57');

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

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `tutor_id`, `tutee_id`, `sender_type`, `message`, `created_at`, `is_read`) VALUES
(230, 116, 60, 'tutee', '312', '2024-11-07 10:19:46', 1),
(231, 116, 60, 'tutee', 'qeqweqeq', '2024-11-07 10:26:22', 1),
(232, 116, 61, 'tutor', 'yow', '2024-11-09 08:34:11', 0),
(233, 116, 61, 'tutee', 'hello', '2024-11-09 08:41:10', 1);

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
  `age` int(100) NOT NULL,
  `birthday` date NOT NULL,
  `faculty_id` varchar(20) NOT NULL,
  `emailaddress` varchar(50) NOT NULL,
  `employment_status` varchar(20) NOT NULL,
  `prof_password` varchar(250) NOT NULL,
  `prof_username` varchar(250) NOT NULL,
  `prof_photo` varchar(250) NOT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `professor`
--

INSERT INTO `professor` (`id`, `firstname`, `lastname`, `middlename`, `age`, `birthday`, `faculty_id`, `emailaddress`, `employment_status`, `prof_password`, `prof_username`, `prof_photo`, `last_login`) VALUES
(331, 'John Paul', 'Gracio', 'Meralles', 27, '0000-00-00', '21-1262', 'jasmine.elisolutions@gmail.com', 'Part-time', '$2y$10$bQJFqSTf4GHCQm.P.qZcQu9nXyZU5tQI0pkJmn7qLyKB.kiQhf7VO', 'prof2', 'profile.jpg', '2024-11-09 13:42:42'),
(332, 'Clarisse', 'Dizon', 'Claus', 27, '0000-00-00', '21-1263', 'fernandezjasmine095@gmail.com', 'Full-time', '$2y$10$aNNiXtxOzza7WZvgzVKs3.lClp9Vz8qKsaCFimFh/Vz9tgJ/3V/GS', 'prof3', 'profile.jpg', NULL);

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

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`request_id`, `tutor_id`, `tutee_id`, `status`) VALUES
(111, 116, 61, 'accepted'),
(112, 116, 60, 'accepted');

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
  `photo` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `tutee_bday` varchar(255) NOT NULL,
  `school` varchar(255) NOT NULL,
  `grade` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tutee`
--

INSERT INTO `tutee` (`id`, `firstname`, `lastname`, `age`, `sex`, `number`, `guardianname`, `fblink`, `barangay`, `emailaddress`, `photo`, `password`, `tutee_bday`, `school`, `grade`) VALUES
(60, 'Elmerson', 'Reyes', '8', 'Male', '09771240013', 'Elmerson Mirasol Cuaresma', 'https://www.facebook.com/eljohn.cuaresma.54/', 'Malinta', 'cuaresmaeljohn@gmail.com', '../uploads/John_Reyes.jpg', '$2y$10$O/M8Ffd9YdZB7yWOr5tCXu0mytdWl7YUqVZQAJoYKPwieqbOUDdsG', '2016-06-20', 'Maysan Elementary School', 'Grade 5'),
(61, 'John Paul', 'Gracio', '9', 'Male', '09480921896', 'mamamo', 'jp.com', 'Bignay', 'johnpaul.gracio.27@gmail.com', '', '$2y$10$7u2VGqajREwlQXFpHtxlAeVGZ2txdOP4146xFsSn8PCB6kpPy1PoS', '2015-08-27', 'plv', 'Grade 3');

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

--
-- Dumping data for table `tutee_progress`
--

INSERT INTO `tutee_progress` (`id`, `tutee_id`, `tutor_id`, `week_number`, `uploaded_files`, `description`, `date`, `rendered_hours`, `location`, `subject`) VALUES
(329, 61, 116, 1, '../uploads/tutor (2).xlsx', 'dwa', '2024-11-10 11:11:27', 5, 'dwa', 'dwad'),
(336, 60, 116, 1, '../uploads/Document (2).docx', 'dawd', '2024-11-10 11:57:32', 5, 'dwdwa', 'dwada'),
(337, 60, 116, 2, '../uploads/Document.docx', 'dwada', '2024-11-10 11:58:24', 5, 'dwa', 'dwada'),
(339, 61, 116, 2, '../uploads/Eljohn Cuaresma BSIT 3-Paul.docx', 'dwad', '2024-11-10 12:00:34', 5, 'dwada', 'dawda');

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

--
-- Dumping data for table `tutee_summary`
--

INSERT INTO `tutee_summary` (`tutee_id`, `tutor_id`, `completed_weeks`, `registered_weeks`) VALUES
(60, 116, 2, 2),
(61, 116, 1, 2);

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
  `emailaddress` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tutor`
--

INSERT INTO `tutor` (`id`, `firstname`, `lastname`, `age`, `sex`, `number`, `barangay`, `student_id`, `course`, `year_section`, `photo`, `professor`, `fblink`, `emailaddress`, `password`) VALUES
(116, 'Eljohn', 'Fernandez', 21, 'Female', '09682226610', 'Dalandanan', '21-1252', 'BSIT', '2-2', '../uploads/21-1251.png', '21-1261', 'jasmine/me.', 'cuaresmaeljohn@gmail.com', '$2y$10$bgddRa1O7U2vfD/FM0p1vOkOP77Zehm2zCpFZ4H/vWQpsgLSrGCj.');

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
-- Dumping data for table `tutor_sessions`
--

INSERT INTO `tutor_sessions` (`id`, `tutor_id`, `tutee_id`, `status`) VALUES
(49, 116, 60, 'ongoing'),
(50, 116, 61, 'ongoing');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `archive_professor`
--
ALTER TABLE `archive_professor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `archive_requests`
--
ALTER TABLE `archive_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `archive_tutee`
--
ALTER TABLE `archive_tutee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `archive_tutee_progress`
--
ALTER TABLE `archive_tutee_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=304;

--
-- AUTO_INCREMENT for table `archive_tutor`
--
ALTER TABLE `archive_tutor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=218;

--
-- AUTO_INCREMENT for table `archive_tutor_ratings`
--
ALTER TABLE `archive_tutor_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `archive_tutor_sessions`
--
ALTER TABLE `archive_tutor_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `districts`
--
ALTER TABLE `districts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=234;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `professor`
--
ALTER TABLE `professor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=333;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `tutee`
--
ALTER TABLE `tutee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `tutee_progress`
--
ALTER TABLE `tutee_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=340;

--
-- AUTO_INCREMENT for table `tutor`
--
ALTER TABLE `tutor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT for table `tutor_ratings`
--
ALTER TABLE `tutor_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `tutor_sessions`
--
ALTER TABLE `tutor_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

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
