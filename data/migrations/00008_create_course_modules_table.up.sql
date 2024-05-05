CREATE TABLE `course_modules` (
  `course_id` int,
  `module_id` int,
  `created_at` timestamp NOT NULL DEFAULT (now()),
  PRIMARY KEY (`course_id`, `module_id`)
);
--split
CREATE INDEX `course_modules_index_1` ON `course_modules` (`course_id`);
--split
CREATE INDEX `course_modules_index_2` ON `course_modules` (`module_id`);
--split
ALTER TABLE `course_modules` ADD FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;
--split
ALTER TABLE `course_modules` ADD FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE CASCADE;
