-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: woodlands_db
-- Generation Time: May 19, 2024 at 09:04 AM
-- Server version: 11.3.2-MariaDB-1:11.3.2+maria~ubu2204
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `woodlands`
--

-- --------------------------------------------------------

--
-- Table structure for table `classrooms`
--

CREATE TABLE `classrooms` (
  `classroom_id` int(11) NOT NULL,
  `room_code` varchar(16) NOT NULL,
  `capacity` int(11) NOT NULL,
  `added_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_modified_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_modified_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_modules`
--

CREATE TABLE `course_modules` (
  `course_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_modified_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `name`, `description`, `created_at`, `last_modified_at`) VALUES
(15, 'computing', 'the computing module', '2024-05-18 20:26:02', '2024-05-18 20:26:02');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `module_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(16) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_modified_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `module_assessments`
--

CREATE TABLE `module_assessments` (
  `assessment_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `hidden` tinyint(1) DEFAULT 1,
  `due_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_modified_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `module_sessions`
--

CREATE TABLE `module_sessions` (
  `session_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `day` enum('mon','tue','wed','thu','fri') NOT NULL,
  `from_time` varchar(4) NOT NULL,
  `to_time` varchar(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_modified_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `module_sessions_attendance_data`
--

CREATE TABLE `module_sessions_attendance_data` (
  `attendance_id` int(11) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `code` varchar(8) NOT NULL COMMENT 'this is the attendance code that will be used by students to register their attendance',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `module_tutors`
--

CREATE TABLE `module_tutors` (
  `staff_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school_break_schedule`
--

CREATE TABLE `school_break_schedule` (
  `schedule_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `from` date NOT NULL,
  `to` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_modified_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `first_name` varchar(64) NOT NULL,
  `last_name` varchar(64) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female','others') NOT NULL,
  `user_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `hired_on` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_modified_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `first_name`, `last_name`, `role`, `date_of_birth`, `gender`, `user_id`, `department_id`, `hired_on`, `created_at`, `last_modified_at`) VALUES
(100000, 'john', 'diggle', '', '1991-12-31', 'male', 9, NULL, '2023-08-20', '2024-05-12 14:26:07', '2024-05-18 22:02:49'),
(100001, 'ayodeji', 'osasona', 'Head of Department', '2004-06-05', 'male', 8, 15, '2023-07-21', '2024-05-12 03:06:30', '2024-05-19 00:46:37'),
(100002, 'paul', 'shane', '', '1998-07-21', 'male', 10, 15, '2024-05-10', '2024-05-19 00:45:54', '2024-05-19 00:45:54'),
(100003, 'doug', 'garson', '', '1998-10-17', 'male', 11, NULL, '2024-05-10', '2024-05-19 00:55:18', '2024-05-19 00:55:18');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `first_name` varchar(64) NOT NULL,
  `last_name` varchar(64) NOT NULL,
  `date_of_birth` date NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `nationality` varchar(64) NOT NULL,
  `gender` enum('male','female','others') NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_modified_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_attendance`
--

CREATE TABLE `student_attendance` (
  `attendance_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` enum('present','absent','late') NOT NULL DEFAULT 'present',
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_class_schedules`
--

CREATE TABLE `student_class_schedules` (
  `schedule_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_courses`
--

CREATE TABLE `student_courses` (
  `course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_modules`
--

CREATE TABLE `student_modules` (
  `module_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_tutors`
--

CREATE TABLE `student_tutors` (
  `student_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `assigned_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_modified_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `hashed_password` varchar(255) NOT NULL,
  `user_type` enum('staff','student') NOT NULL,
  `last_signed_in_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_modified_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email_address`, `hashed_password`, `user_type`, `last_signed_in_at`, `created_at`, `last_modified_at`) VALUES
(4, 'jdoe@woodlands.ac.uk', '$2y$10$Ckxr3Clugn.2u2st9vbyn..pCUoVqyMA/FJOi11IVw5TQdv3B.i9a', 'student', NULL, '2024-05-06 16:35:57', '2024-05-06 22:15:42'),
(5, 'admin@woodlands.ac.uk', '$2y$10$5XtFuG5IZ87NnMPcDPbk7eJmx.lqyPKIf6eOgH1bN.jIQf7YfLUaO', 'staff', '2024-05-12 03:19:09', '2024-05-06 21:36:09', '2024-05-12 03:19:09'),
(8, 'a.osasona664@woodlands.ac.uk', '$2y$10$kfSLQUE8j/SXjSEjZN7BtOA865.km4lzOH6QgX2zT5HEbgjMlD8jC', 'staff', NULL, '2024-05-12 03:06:30', '2024-05-12 03:06:30'),
(9, 'j.diggle664@woodlands.ac.uk', '$2y$10$Yz1ydNs3Y.vG3ga3sUpWWOPLjM8VePJ2twKvzAAR2RPADN4KhP/Ai', 'staff', NULL, '2024-05-12 14:26:07', '2024-05-12 14:26:07'),
(10, 'p.shane664@woodlands.ac.uk', '$2y$10$TOS5iQx2vRESop.VGhJNuOWjFB3Msfd9Z5WTkhF5slR1mK2RxF9fW', 'staff', NULL, '2024-05-19 00:45:54', '2024-05-19 00:45:54'),
(11, 'd.garson664@woodlands.ac.uk', '$2y$10$MnmnWINjmv8n/fPusQzQ/ezc5tgDgIMNjAKZbZBd9SEF53dSCq/bq', 'staff', NULL, '2024-05-19 00:55:18', '2024-05-19 00:55:18');

-- --------------------------------------------------------

--
-- Table structure for table `__migrations`
--

CREATE TABLE `__migrations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `__migrations`
--

INSERT INTO `__migrations` (`id`, `name`, `created_at`) VALUES
(29, '00000_create_departments', '2024-05-05 02:43:33'),
(30, '00001_create_users_table', '2024-05-05 02:43:33'),
(31, '00002_create_students_table', '2024-05-05 02:43:33'),
(32, '00003_create_staff_table', '2024-05-05 02:43:33'),
(33, '00004_create_student_tutors_table', '2024-05-05 02:43:33'),
(34, '00005_create_courses_table', '2024-05-05 02:43:33'),
(35, '00006_create_student_courses_table', '2024-05-05 02:43:33'),
(36, '00007_create_modules_table', '2024-05-05 02:43:33'),
(37, '00008_create_course_modules_table', '2024-05-05 02:43:33'),
(38, '00009_create_student_modules_table', '2024-05-05 02:43:33'),
(39, '00010_create_module_tutors_table', '2024-05-05 02:43:33'),
(40, '00011_create_module_assessments_table', '2024-05-05 02:43:33'),
(41, '00012_create_classrooms_table', '2024-05-05 02:43:33'),
(42, '00013_create_module_sessions_table', '2024-05-05 02:43:33'),
(43, '00014_create_student_class_schedules_table', '2024-05-05 02:43:33'),
(44, '00015_create_module_sessions_attendance_data_table', '2024-05-05 02:43:33'),
(45, '00016_create_student_attendance_table', '2024-05-05 02:43:33'),
(46, '00017_create_school_break_schedule_table', '2024-05-05 02:43:34'),
(47, '00018_create_default_admin_user', '2024-05-06 21:36:09'),
(48, '00019_modify_students_gender', '2024-05-10 21:42:06'),
(49, '00020_alter_staff_id_starting_point', '2024-05-12 13:29:55'),
(50, '00021_add_module_code', '2024-05-19 09:03:02'),
(51, '00022_restrict_module_code_column', '2024-05-19 09:03:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `classrooms`
--
ALTER TABLE `classrooms`
  ADD PRIMARY KEY (`classroom_id`),
  ADD UNIQUE KEY `room_code` (`room_code`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `course_modules`
--
ALTER TABLE `course_modules`
  ADD PRIMARY KEY (`course_id`,`module_id`),
  ADD KEY `course_modules_index_1` (`course_id`),
  ADD KEY `course_modules_index_2` (`module_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`module_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `uk_module_code` (`code`);

--
-- Indexes for table `module_assessments`
--
ALTER TABLE `module_assessments`
  ADD PRIMARY KEY (`assessment_id`),
  ADD KEY `module_id` (`module_id`);

--
-- Indexes for table `module_sessions`
--
ALTER TABLE `module_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `module_id` (`module_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `module_sessions_attendance_data`
--
ALTER TABLE `module_sessions_attendance_data`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `module_sessions_attendance_data_index_9` (`session_id`,`code`);

--
-- Indexes for table `module_tutors`
--
ALTER TABLE `module_tutors`
  ADD PRIMARY KEY (`module_id`,`staff_id`),
  ADD KEY `module_tutors_index_6` (`module_id`),
  ADD KEY `module_tutors_index_7` (`staff_id`);

--
-- Indexes for table `school_break_schedule`
--
ALTER TABLE `school_break_schedule`
  ADD PRIMARY KEY (`schedule_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `student_attendance`
--
ALTER TABLE `student_attendance`
  ADD PRIMARY KEY (`attendance_id`,`student_id`),
  ADD KEY `student_attendance_index_1` (`student_id`),
  ADD KEY `student_attendance_index_2` (`attendance_id`);

--
-- Indexes for table `student_class_schedules`
--
ALTER TABLE `student_class_schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD UNIQUE KEY `student_class_schedules_index_1` (`student_id`,`session_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD PRIMARY KEY (`course_id`,`student_id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD KEY `student_courses_index_1` (`course_id`);

--
-- Indexes for table `student_modules`
--
ALTER TABLE `student_modules`
  ADD PRIMARY KEY (`module_id`,`student_id`),
  ADD KEY `student_modules_index_1` (`module_id`),
  ADD KEY `student_modules_index_2` (`student_id`);

--
-- Indexes for table `student_tutors`
--
ALTER TABLE `student_tutors`
  ADD PRIMARY KEY (`student_id`,`staff_id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email_address` (`email_address`);

--
-- Indexes for table `__migrations`
--
ALTER TABLE `__migrations`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `classrooms`
--
ALTER TABLE `classrooms`
  MODIFY `classroom_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `module_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `module_assessments`
--
ALTER TABLE `module_assessments`
  MODIFY `assessment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `module_sessions`
--
ALTER TABLE `module_sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `module_sessions_attendance_data`
--
ALTER TABLE `module_sessions_attendance_data`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `school_break_schedule`
--
ALTER TABLE `school_break_schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100004;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100000;

--
-- AUTO_INCREMENT for table `student_class_schedules`
--
ALTER TABLE `student_class_schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `__migrations`
--
ALTER TABLE `__migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `course_modules`
--
ALTER TABLE `course_modules`
  ADD CONSTRAINT `course_modules_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_modules_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE CASCADE;

--
-- Constraints for table `module_assessments`
--
ALTER TABLE `module_assessments`
  ADD CONSTRAINT `module_assessments_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE CASCADE;

--
-- Constraints for table `module_sessions`
--
ALTER TABLE `module_sessions`
  ADD CONSTRAINT `module_sessions_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `module_sessions_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `classrooms` (`classroom_id`) ON DELETE SET NULL;

--
-- Constraints for table `module_sessions_attendance_data`
--
ALTER TABLE `module_sessions_attendance_data`
  ADD CONSTRAINT `module_sessions_attendance_data_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `module_sessions` (`session_id`) ON DELETE SET NULL;

--
-- Constraints for table `module_tutors`
--
ALTER TABLE `module_tutors`
  ADD CONSTRAINT `module_tutors_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `module_tutors_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE CASCADE;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL;

--
-- Constraints for table `student_attendance`
--
ALTER TABLE `student_attendance`
  ADD CONSTRAINT `student_attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_attendance_ibfk_2` FOREIGN KEY (`attendance_id`) REFERENCES `module_sessions_attendance_data` (`attendance_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_class_schedules`
--
ALTER TABLE `student_class_schedules`
  ADD CONSTRAINT `student_class_schedules_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_class_schedules_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `module_sessions` (`session_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD CONSTRAINT `student_courses_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_courses_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_modules`
--
ALTER TABLE `student_modules`
  ADD CONSTRAINT `student_modules_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_modules_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_tutors`
--
ALTER TABLE `student_tutors`
  ADD CONSTRAINT `student_tutors_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_tutors_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
