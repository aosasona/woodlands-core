-- A lot of overlap with the staff table but keeping them separate for future extensibility
CREATE TABLE IF NOT EXISTS students (
  `student_id` int PRIMARY KEY AUTO_INCREMENT,
  `first_name` varchar(64),
  `last_name` varchar(64),
  `date_of_birth` date NOT NULL,
  `gender` enum('M', 'F') NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp DEFAULT (now()),
  `last_modified_at` timestamp DEFAULT (now()),

  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
);
