CREATE TABLE `staff` (
  `staff_id` int PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `first_name` varchar(64) NOT NULL,
  `last_name` varchar(64) NOT NULL,
  `role` varchar(255),
  `date_of_birth` date NOT NULL,
  `gender` ENUM ('male', 'female', 'others') NOT NULL,
  `user_id` int UNIQUE NOT NULL,
  `department_id` int,
  `hired_on` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT (now()),
  `last_modified_at` timestamp NOT NULL DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP
);
--split
ALTER TABLE `staff` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
--split
ALTER TABLE `staff` ADD FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL;
