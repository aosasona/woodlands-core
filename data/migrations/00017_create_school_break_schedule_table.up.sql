CREATE TABLE `school_break_schedule` (
  `schedule_id` int PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `from` date NOT NULL,
  `to` date NOT NULL,
  `created_at` timestamp DEFAULT (now()),
  `last_modified_at` timestamp DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP
);
