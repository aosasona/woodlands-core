CREATE TABLE student_tutors (
  `student_id` int UNIQUE,
  `staff_id` int,
  `assigned_on` timestamp NOT NULL DEFAULT (now()),
  `last_modified_at` timestamp NOT NULL DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`student_id`, `staff_id`)
);
--split
ALTER TABLE student_tutors ADD FOREIGN KEY (`student_id`) REFERENCES students(`student_id`) ON DELETE CASCADE;
--split
ALTER TABLE student_tutors ADD FOREIGN KEY (`staff_id`) REFERENCES staff(`staff_id`) ON DELETE CASCADE;
