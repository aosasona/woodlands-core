CREATE TABLE `module_tutors` (
  `staff_id` int NOT NULL,
  `module_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT (now()),
  PRIMARY KEY (`module_id`, `staff_id`)
);
--split
CREATE INDEX `module_tutors_index_6` ON `module_tutors` (`module_id`);
--split
CREATE INDEX `module_tutors_index_7` ON `module_tutors` (`staff_id`);
--split
ALTER TABLE `module_tutors` ADD FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE CASCADE;
--split
ALTER TABLE `module_tutors` ADD FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE CASCADE;
