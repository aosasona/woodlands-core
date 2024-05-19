CREATE TEMPORARY TABLE `modules_backup` AS SELECT * FROM `modules`;
-- split
DROP TABLE `modules`;
-- split
CREATE TABLE `modules` (
  `module_id` int PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `name` varchar(255) UNIQUE NOT NULL,
  `code` varchar(16) NOT NULL DEFAULT '',
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT (now()),
  `last_modified_at` timestamp DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP
);
-- split
INSERT INTO `modules` SELECT * FROM `modules_backup`;
-- split
DROP TABLE `modules_backup`;
