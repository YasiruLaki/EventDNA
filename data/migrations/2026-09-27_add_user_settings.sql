-- Adds per-user notification and privacy preferences for the attendee Settings page.
-- A user with no row here uses the defaults below.
-- Run once in phpMyAdmin (SQL tab) on the `eventdna` database.

CREATE TABLE IF NOT EXISTS `user_settings` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `notify_in_app` tinyint(1) NOT NULL DEFAULT 1,
  `notify_email` tinyint(1) NOT NULL DEFAULT 1,
  `notify_event_updates` tinyint(1) NOT NULL DEFAULT 1,
  `notify_connection_requests` tinyint(1) NOT NULL DEFAULT 1,
  `notify_connection_updates` tinyint(1) NOT NULL DEFAULT 1,
  `notify_community_activity` tinyint(1) NOT NULL DEFAULT 1,
  `profile_visibility` enum('MEMBERS','CONNECTIONS') NOT NULL DEFAULT 'MEMBERS',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  CONSTRAINT `user_settings_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
