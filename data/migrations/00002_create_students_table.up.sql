-- A lot of overlap with the staff table but keeping them separate for future extensibility
CREATE TABLE IF NOT EXISTS `students` (
  `student_id` int PRIMARY KEY AUTO_INCREMENT,
  `first_name` varchar(64) NOT NULL,
  `last_name` varchar(64) NOT NULL,
  `date_of_birth` date NOT NULL,
  `department_id` int NOT NULL,
  `nationality` varchar(64) NOT NULL,
  `gender` enum('M', 'F') NOT NULL,
  `user_id` int UNIQUE NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT (now()),
  `last_modified_at` timestamp NOT NULL DEFAULT (now()),

  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL
);

-- split

ALTER TABLE `students` AUTO_INCREMENT = 100000;
