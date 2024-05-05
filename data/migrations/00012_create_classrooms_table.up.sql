CREATE TABLE `classrooms` (
  `classroom_id` int PRIMARY KEY AUTO_INCREMENT,
  `room_code` varchar(16) UNIQUE NOT NULL,
  `capacity` int NOT NULL,
  `added_on` timestamp NOT NULL DEFAULT (now()),
  `last_modified_at` timestamp DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP
);
