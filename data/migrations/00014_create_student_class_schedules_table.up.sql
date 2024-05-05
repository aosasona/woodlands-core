CREATE TABLE `student_class_schedules` (
  `schedule_id` int PRIMARY KEY AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `session_id` int NOT NULL
);
--split
CREATE UNIQUE INDEX `student_class_schedules_index_1` ON `student_class_schedules` (`student_id`, `session_id`);
--split
ALTER TABLE `student_class_schedules` ADD FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;
--split
ALTER TABLE `student_class_schedules` ADD FOREIGN KEY (`session_id`) REFERENCES `module_sessions` (`session_id`) ON DELETE CASCADE;
