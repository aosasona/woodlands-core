CREATE TABLE `departments` (
  `department_id` int PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255) UNIQUE NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT (now()),
  `last_modified_at` timestamp DEFAULT (now())
)
