CREATE TABLE `student_attendance` (
  `attendance_id` int,
  `student_id` int,
  `status` ENUM ('present', 'absent', 'late') NOT NULL DEFAULT 'present',
  `recorded_at` timestamp NOT NULL DEFAULT (now()),
  PRIMARY KEY (`attendance_id`, `student_id`)
);

--split
CREATE INDEX `student_attendance_index_1` ON `student_attendance` (`student_id`);
--split
CREATE INDEX `student_attendance_index_2` ON `student_attendance` (`attendance_id`);
--split
ALTER TABLE `student_attendance` ADD FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;
--split
ALTER TABLE `student_attendance` ADD FOREIGN KEY (`attendance_id`) REFERENCES `module_sessions_attendance_data` (`attendance_id`) ON DELETE CASCADE;
