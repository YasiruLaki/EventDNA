-- EventDNA - full schema
-- Target: MariaDB 10.4 (XAMPP).
--
-- Tables are created in dependency order and dropped in reverse, so this
-- file can be re-run from scratch while the design is still moving.

CREATE DATABASE IF NOT EXISTS eventdna
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE eventdna;

DROP TABLE IF EXISTS moderation_log;
DROP TABLE IF EXISTS match_log;
DROP TABLE IF EXISTS opportunity;
DROP TABLE IF EXISTS notification;
DROP TABLE IF EXISTS resource;
DROP TABLE IF EXISTS reply;
DROP TABLE IF EXISTS thread;
DROP TABLE IF EXISTS group_interest;
DROP TABLE IF EXISTS group_membership;
DROP TABLE IF EXISTS community_group;
DROP TABLE IF EXISTS connection;
DROP TABLE IF EXISTS registration;
DROP TABLE IF EXISTS event;
DROP TABLE IF EXISTS organizer_verification_request;
DROP TABLE IF EXISTS password_reset_token;
DROP TABLE IF EXISTS user_goal;
DROP TABLE IF EXISTS user_interest;
DROP TABLE IF EXISTS user_skill;
DROP TABLE IF EXISTS networking_goal;
DROP TABLE IF EXISTS interest;
DROP TABLE IF EXISTS skill;
DROP TABLE IF EXISTS `user`;


-- ============================================================
-- core identity
-- ============================================================

CREATE TABLE `user` (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('attendee','organizer','admin') NOT NULL DEFAULT 'attendee',
  is_organizer_verified BOOLEAN NOT NULL DEFAULT FALSE,
  account_status ENUM('pending','active','disabled') NOT NULL DEFAULT 'pending',
  -- nullable + unique: token is set to NULL once used, and MySQL allows
  -- repeated NULLs in a unique index
  email_verify_token VARCHAR(64) NULL,
  personal_qr_token VARCHAR(64) NULL,
  profile_photo_path VARCHAR(255) NULL,
  bio TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_email (email),
  UNIQUE KEY uq_user_verify_token (email_verify_token),
  UNIQUE KEY uq_user_qr_token (personal_qr_token),
  KEY idx_user_role_status (role, account_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE skill (
  skill_id INT AUTO_INCREMENT PRIMARY KEY,
  skill_name VARCHAR(80) NOT NULL,
  UNIQUE KEY uq_skill_name (skill_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE interest (
  interest_id INT AUTO_INCREMENT PRIMARY KEY,
  interest_name VARCHAR(80) NOT NULL,
  UNIQUE KEY uq_interest_name (interest_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE networking_goal (
  goal_id INT AUTO_INCREMENT PRIMARY KEY,
  goal_name VARCHAR(80) NOT NULL,
  UNIQUE KEY uq_goal_name (goal_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- The composite PK only indexes user_id first, so the second column gets
-- its own index for the "who else has this skill" lookups in matching.
CREATE TABLE user_skill (
  user_id INT NOT NULL,
  skill_id INT NOT NULL,
  PRIMARY KEY (user_id, skill_id),
  KEY idx_user_skill_skill (skill_id),
  CONSTRAINT fk_user_skill_user FOREIGN KEY (user_id)
    REFERENCES `user` (user_id) ON DELETE CASCADE,
  CONSTRAINT fk_user_skill_skill FOREIGN KEY (skill_id)
    REFERENCES skill (skill_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE user_interest (
  user_id INT NOT NULL,
  interest_id INT NOT NULL,
  PRIMARY KEY (user_id, interest_id),
  KEY idx_user_interest_interest (interest_id),
  CONSTRAINT fk_user_interest_user FOREIGN KEY (user_id)
    REFERENCES `user` (user_id) ON DELETE CASCADE,
  CONSTRAINT fk_user_interest_interest FOREIGN KEY (interest_id)
    REFERENCES interest (interest_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- priority 1 = highest. Unique per user, so reordering goals needs a
-- single UPDATE with CASE - two separate UPDATEs will hit the constraint.
CREATE TABLE user_goal (
  user_id INT NOT NULL,
  goal_id INT NOT NULL,
  priority TINYINT UNSIGNED NOT NULL,
  PRIMARY KEY (user_id, goal_id),
  KEY idx_user_goal_goal (goal_id),
  UNIQUE KEY uq_user_goal_priority (user_id, priority),
  CONSTRAINT fk_user_goal_user FOREIGN KEY (user_id)
    REFERENCES `user` (user_id) ON DELETE CASCADE,
  CONSTRAINT fk_user_goal_goal FOREIGN KEY (goal_id)
    REFERENCES networking_goal (goal_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- account lifecycle
-- ============================================================

-- Weak entity, identified by (user_id, token_hash). The reset link only
-- carries the token, so token_hash needs its own index - the composite PK
-- is no use for a lookup that has no user_id yet.
CREATE TABLE password_reset_token (
  user_id INT NOT NULL,
  token_hash VARCHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (user_id, token_hash),
  KEY idx_prt_token (token_hash),
  KEY idx_prt_expires (expires_at),
  CONSTRAINT fk_prt_user FOREIGN KEY (user_id)
    REFERENCES `user` (user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE organizer_verification_request (
  request_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  full_legal_name VARCHAR(150) NOT NULL,
  organization_name VARCHAR(150) NULL,
  contact_number VARCHAR(20) NOT NULL,
  id_document_path VARCHAR(255) NOT NULL,
  justification TEXT NULL,
  request_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  reviewed_by INT NULL,
  review_notes VARCHAR(255) NULL,
  submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reviewed_at DATETIME NULL,
  KEY idx_ovr_user (user_id),
  KEY idx_ovr_status (request_status, submitted_at),
  CONSTRAINT fk_ovr_user FOREIGN KEY (user_id)
    REFERENCES `user` (user_id) ON DELETE CASCADE,
  -- deleting the reviewing admin must not delete the request itself
  CONSTRAINT fk_ovr_reviewer FOREIGN KEY (reviewed_by)
    REFERENCES `user` (user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- events
-- ============================================================

-- organizer_id is RESTRICT, not CASCADE: deleting an organiser would
-- otherwise silently destroy their events and every registration on them.
-- Reassign or cancel the events first.
CREATE TABLE event (
  event_id INT AUTO_INCREMENT PRIMARY KEY,
  organizer_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT NULL,
  location VARCHAR(255) NOT NULL,
  start_datetime DATETIME NOT NULL,
  end_datetime DATETIME NOT NULL,
  reg_open DATETIME NOT NULL,
  reg_close DATETIME NOT NULL,
  visibility ENUM('public','private') NOT NULL DEFAULT 'public',
  capacity INT NOT NULL,
  event_status ENUM('active','cancelled','completed') NOT NULL DEFAULT 'active',
  -- constant for the life of the event, never rotated
  event_qr_token VARCHAR(64) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_event_qr_token (event_qr_token),
  KEY idx_event_organizer (organizer_id),
  KEY idx_event_browse (visibility, event_status, start_datetime),
  CONSTRAINT chk_event_dates CHECK (end_datetime > start_datetime),
  CONSTRAINT chk_event_reg_window CHECK (reg_close > reg_open AND reg_close <= start_datetime),
  CONSTRAINT chk_event_capacity CHECK (capacity > 0),
  CONSTRAINT fk_event_organizer FOREIGN KEY (organizer_id)
    REFERENCES `user` (user_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- The registered list. Every QR scan checks this table for a matching
-- (attendee_id, event_id) before allowing check-in.
CREATE TABLE registration (
  registration_id INT AUTO_INCREMENT PRIMARY KEY,
  attendee_id INT NOT NULL,
  event_id INT NOT NULL,
  registered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  attendance_status ENUM('registered','checked_in') NOT NULL DEFAULT 'registered',
  checked_in_at DATETIME NULL,
  UNIQUE KEY uq_registration_attendee_event (attendee_id, event_id),
  KEY idx_registration_event (event_id, attendance_status),
  CONSTRAINT fk_registration_attendee FOREIGN KEY (attendee_id)
    REFERENCES `user` (user_id) ON DELETE CASCADE,
  CONSTRAINT fk_registration_event FOREIGN KEY (event_id)
    REFERENCES event (event_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- networking
-- ============================================================

-- The unique key blocks a duplicate A->B request. Blocking B->A when A->B
-- already exists is application logic - SQL cannot express a symmetric
-- unique constraint. See ER spec Section 6.
CREATE TABLE connection (
  connection_id INT AUTO_INCREMENT PRIMARY KEY,
  requester_id INT NOT NULL,
  recipient_id INT NOT NULL,
  event_id INT NULL,
  connection_status ENUM('pending','accepted','rejected','removed') NOT NULL DEFAULT 'pending',
  requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  responded_at DATETIME NULL,
  UNIQUE KEY uq_connection_pair (requester_id, recipient_id),
  KEY idx_connection_recipient (recipient_id, connection_status),
  KEY idx_connection_event (event_id),
  CONSTRAINT chk_connection_not_self CHECK (requester_id <> recipient_id),
  CONSTRAINT fk_connection_requester FOREIGN KEY (requester_id)
    REFERENCES `user` (user_id) ON DELETE CASCADE,
  CONSTRAINT fk_connection_recipient FOREIGN KEY (recipient_id)
    REFERENCES `user` (user_id) ON DELETE CASCADE,
  -- the connection outlives the event it started at
  CONSTRAINT fk_connection_event FOREIGN KEY (event_id)
    REFERENCES event (event_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE opportunity (
  opportunity_id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NULL,
  posted_by INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  opportunity_type ENUM('internship','job','collaboration','mentorship','other') NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_opportunity_event (event_id),
  KEY idx_opportunity_poster (posted_by),
  CONSTRAINT fk_opportunity_event FOREIGN KEY (event_id)
    REFERENCES event (event_id) ON DELETE SET NULL,
  CONSTRAINT fk_opportunity_poster FOREIGN KEY (posted_by)
    REFERENCES `user` (user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Scores are recomputed by the matching engine, so these rows are
-- disposable - cascade rather than block a user delete.
CREATE TABLE match_log (
  match_log_id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  user_a_id INT NOT NULL,
  user_b_id INT NOT NULL,
  compatibility_score DECIMAL(5,2) NOT NULL,
  computed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_match_lookup (event_id, user_a_id, compatibility_score),
  KEY idx_match_user_b (user_b_id),
  CONSTRAINT chk_match_not_self CHECK (user_a_id <> user_b_id),
  CONSTRAINT fk_match_event FOREIGN KEY (event_id)
    REFERENCES event (event_id) ON DELETE CASCADE,
  CONSTRAINT fk_match_user_a FOREIGN KEY (user_a_id)
    REFERENCES `user` (user_id) ON DELETE CASCADE,
  CONSTRAINT fk_match_user_b FOREIGN KEY (user_b_id)
    REFERENCES `user` (user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- community hub
-- ============================================================

-- created_by is RESTRICT: the group and its threads should not disappear
-- because the person who started it was deleted.
CREATE TABLE community_group (
  group_id INT AUTO_INCREMENT PRIMARY KEY,
  group_name VARCHAR(100) NOT NULL,
  description TEXT NULL,
  created_by INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_group_name (group_name),
  KEY idx_group_creator (created_by),
  CONSTRAINT fk_group_creator FOREIGN KEY (created_by)
    REFERENCES `user` (user_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE group_membership (
  user_id INT NOT NULL,
  group_id INT NOT NULL,
  joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  membership_status ENUM('active','removed') NOT NULL DEFAULT 'active',
  PRIMARY KEY (user_id, group_id),
  KEY idx_membership_group (group_id, membership_status),
  CONSTRAINT fk_membership_user FOREIGN KEY (user_id)
    REFERENCES `user` (user_id) ON DELETE CASCADE,
  CONSTRAINT fk_membership_group FOREIGN KEY (group_id)
    REFERENCES community_group (group_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE group_interest (
  group_id INT NOT NULL,
  interest_id INT NOT NULL,
  PRIMARY KEY (group_id, interest_id),
  KEY idx_group_interest_interest (interest_id),
  CONSTRAINT fk_group_interest_group FOREIGN KEY (group_id)
    REFERENCES community_group (group_id) ON DELETE CASCADE,
  CONSTRAINT fk_group_interest_interest FOREIGN KEY (interest_id)
    REFERENCES interest (interest_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE thread (
  thread_id INT AUTO_INCREMENT PRIMARY KEY,
  group_id INT NOT NULL,
  author_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  body TEXT NOT NULL,
  -- soft delete used by moderation, the row stays for the audit trail
  is_removed BOOLEAN NOT NULL DEFAULT FALSE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_thread_group (group_id, is_removed, created_at),
  KEY idx_thread_author (author_id),
  CONSTRAINT fk_thread_group FOREIGN KEY (group_id)
    REFERENCES community_group (group_id) ON DELETE CASCADE,
  CONSTRAINT fk_thread_author FOREIGN KEY (author_id)
    REFERENCES `user` (user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE reply (
  reply_id INT AUTO_INCREMENT PRIMARY KEY,
  thread_id INT NOT NULL,
  author_id INT NOT NULL,
  body TEXT NOT NULL,
  is_removed BOOLEAN NOT NULL DEFAULT FALSE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_reply_thread (thread_id, is_removed, created_at),
  KEY idx_reply_author (author_id),
  CONSTRAINT fk_reply_thread FOREIGN KEY (thread_id)
    REFERENCES thread (thread_id) ON DELETE CASCADE,
  CONSTRAINT fk_reply_author FOREIGN KEY (author_id)
    REFERENCES `user` (user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE resource (
  resource_id INT AUTO_INCREMENT PRIMARY KEY,
  group_id INT NOT NULL,
  uploaded_by INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_resource_group (group_id, uploaded_at),
  KEY idx_resource_uploader (uploaded_by),
  CONSTRAINT fk_resource_group FOREIGN KEY (group_id)
    REFERENCES community_group (group_id) ON DELETE CASCADE,
  CONSTRAINT fk_resource_uploader FOREIGN KEY (uploaded_by)
    REFERENCES `user` (user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- notifications and audit
-- ============================================================

CREATE TABLE notification (
  notification_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  notif_type ENUM('event_update','event_cancelled','connection_request',
                  'connection_accepted','moderation','organizer_request_reviewed',
                  'system') NOT NULL,
  message VARCHAR(500) NOT NULL,
  link_url VARCHAR(255) NULL,
  is_read BOOLEAN NOT NULL DEFAULT FALSE,
  email_sent BOOLEAN NOT NULL DEFAULT FALSE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_notification_inbox (user_id, is_read, created_at),
  CONSTRAINT fk_notification_user FOREIGN KEY (user_id)
    REFERENCES `user` (user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- target_id is deliberately not a foreign key - it points at whichever
-- table target_type names, so no single constraint can cover it.
-- admin_id is RESTRICT so the audit trail cannot lose its author.
CREATE TABLE moderation_log (
  mod_log_id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NOT NULL,
  action_type ENUM('remove_post','remove_reply','remove_member',
                   'disable_account','edit_event','cancel_event') NOT NULL,
  target_type VARCHAR(30) NOT NULL,
  target_id INT NOT NULL,
  reason VARCHAR(255) NULL,
  logged_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_modlog_admin (admin_id, logged_at),
  KEY idx_modlog_target (target_type, target_id),
  CONSTRAINT fk_modlog_admin FOREIGN KEY (admin_id)
    REFERENCES `user` (user_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
