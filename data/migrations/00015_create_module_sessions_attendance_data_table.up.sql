CREATE TABLE `module_sessions_attendance_data` (
  `attendance_id` int PRIMARY KEY AUTO_INCREMENT,
  `session_id` int,
  `code` varchar(8) NOT NULL COMMENT 'this is the attendance code that will be used by students to register their attendance',
  `created_at` timestamp NOT NULL DEFAULT (now())
);
--split
CREATE UNIQUE INDEX `module_sessions_attendance_data_index_9` ON `module_sessions_attendance_data` (`session_id`, `code`);
--split
ALTER TABLE `module_sessions_attendance_data` ADD FOREIGN KEY (`session_id`) REFERENCES `module_sessions` (`session_id`) ON DELETE SET NULL;
