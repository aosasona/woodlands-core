CREATE TABLE `module_assessments` (
  `assessment_id` int PRIMARY KEY AUTO_INCREMENT,
  `module_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text,
  `hidden` bool DEFAULT true,
  `due_date` timestamp,
  `created_at` timestamp NOT NULL DEFAULT (now()),
  `last_modified_at` timestamp DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP
);
--split
ALTER TABLE `module_assessments` ADD FOREIGN KEY (`module_id`) REFERENCES `modules`(`module_id`) ON DELETE CASCADE;
