CREATE TABLE `courses` (
  `course_id` int PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `name` varchar(255) UNIQUE NOT NULL,
  `description` text,
  `department_id` int,
  `start_date` date NOT NULL,
  `end_date` date,
  `created_at` timestamp NOT NULL DEFAULT (now()),
  `last_modified_at` timestamp DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP
);
--split
ALTER TABLE `courses` ADD FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE;
