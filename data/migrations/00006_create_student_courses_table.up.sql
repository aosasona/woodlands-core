CREATE TABLE `student_courses` (
  `course_id` int NOT NULL,
  `student_id` int UNIQUE NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT (now()),
  PRIMARY KEY (`course_id`, `student_id`)
);
--split
ALTER TABLE `student_courses` ADD FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE;
--split
ALTER TABLE `student_courses` ADD FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;
--split
CREATE INDEX `student_courses_index_1` ON `student_courses` (`course_id`);
