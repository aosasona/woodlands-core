CREATE TABLE `module_sessions` (
  `session_id` int PRIMARY KEY AUTO_INCREMENT,
  `module_id` int NOT NULL,
  `room_id` int,
  `day` ENUM ('mon', 'tue', 'wed', 'thu', 'fri') NOT NULL,
  `from_time` varchar(4) NOT NULL,
  `to_time` varchar(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT (now()),
  `last_modified_at` timestamp DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP
);
--split
ALTER TABLE `module_sessions` ADD FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE CASCADE;
--split
ALTER TABLE `module_sessions` ADD FOREIGN KEY (`room_id`) REFERENCES `classrooms` (`classroom_id`) ON DELETE SET NULL;
