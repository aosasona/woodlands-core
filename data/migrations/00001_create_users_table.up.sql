CREATE TABLE IF NOT EXISTS users (
  `user_id` int PRIMARY KEY AUTO_INCREMENT,
  `email_address` varchar(255) UNIQUE NOT NULL,
  `hashed_password` varchar(255) NOT NULL,
  `user_type` ENUM ('staff', 'student') NOT NULL,
  `last_signed_in_at` timestamp,
  `created_at` timestamp NOT NULL DEFAULT (now()),
  `last_modified_at` timestamp NOT NULL DEFAULT (now())
);
