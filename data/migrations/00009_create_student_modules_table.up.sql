CREATE TABLE `student_modules` (
  `module_id` int,
  `student_id` int,
  `created_at` timestamp NOT NULL DEFAULT (now()),
  PRIMARY KEY (`module_id`, `student_id`)
);
--split
CREATE INDEX `student_modules_index_1` ON `student_modules` (`module_id`);
--split
CREATE INDEX `student_modules_index_2` ON `student_modules` (`student_id`);
--split
ALTER TABLE `student_modules` ADD FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE CASCADE;
--split
ALTER TABLE `student_modules` ADD FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;
