-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 27, 2026 at 08:52 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eventdna`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(10) UNSIGNED NOT NULL,
  `event_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `checked_in` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(10) UNSIGNED NOT NULL,
  `post_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('ACTIVE','REMOVED') NOT NULL DEFAULT 'ACTIVE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `connections`
--

CREATE TABLE `connections` (
  `connection_id` int(10) UNSIGNED NOT NULL,
  `requester_id` int(10) UNSIGNED NOT NULL,
  `recipient_id` int(10) UNSIGNED NOT NULL,
  `status` enum('PENDING','ACCEPTED','REJECTED','REMOVED') NOT NULL DEFAULT 'PENDING',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responded_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `email_verification_tokens`
--

CREATE TABLE `email_verification_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_verification_tokens`
--

INSERT INTO `email_verification_tokens` (`id`, `user_id`, `token_hash`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 2, '192c2094e8d35853e799a485a0a2a9c5f6c121aa38d4571f30b4fb9335ef4a8d', '2026-09-26 06:54:11', NULL, '2026-09-25 06:54:11'),
(2, 3, '414be701b32144c8b7cfc267da472869693b798fa724730cb665813cfdb2ce39', '2026-09-26 06:54:24', NULL, '2026-09-25 06:54:24'),
(3, 3, 'fe16a16e9038b3d245e8d31e541174ae5472f6d759fe39c513d37e6b0aed50ab', '2026-09-25 07:19:02', '2026-09-25 07:19:02', '2026-09-25 07:18:34'),
(4, 3, '76e5a60fb6d54122e02cb183ec4b96e62376ac93e6772f24a975ee7d2010103d', '2026-09-26 07:18:56', NULL, '2026-09-25 07:18:56'),
(5, 4, '9b3a6f4eff44018dba4f9de1e8a428cfa12a34abc39b77494b871e42d571ee43', '2026-09-26 05:02:29', '2026-09-26 05:02:29', '2026-09-26 05:01:58'),
(6, 5, '32749a28cdf8c18544c5cb11395c62dcbe9c74ba390f627873a61f199a811fc0', '2026-09-27 18:34:06', NULL, '2026-09-26 18:34:06'),
(7, 6, 'ecadc8a385fee1d1e774c4e5cc84ef0039502b3d7317a946b99a58b413ba2d35', '2026-09-27 18:38:34', NULL, '2026-09-26 18:38:34');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(10) UNSIGNED NOT NULL,
  `organizer_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `cover_photo` varchar(500) DEFAULT NULL,
  `event_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(200) NOT NULL,
  `address` varchar(300) DEFAULT NULL,
  `capacity` int(10) UNSIGNED NOT NULL,
  `visibility` enum('PUBLIC','INVITE_ONLY') NOT NULL DEFAULT 'PUBLIC',
  `registration_open` datetime NOT NULL,
  `registration_close` datetime NOT NULL,
  `status` enum('ACTIVE','CANCELLED') NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `organizer_id`, `name`, `description`, `cover_photo`, `event_date`, `start_time`, `end_time`, `location`, `address`, `capacity`, `visibility`, `registration_open`, `registration_close`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 'Ai Innovation Summit 2026', 'AI Innovation Summit 2026 is a one-day technology summit bringing together AI researchers, software engineers, entrepreneurs, university students, business leaders, and technology enthusiasts to explore the rapidly evolving landscape of Artificial Intelligence.\r\n\r\nThe summit will feature keynote presentations, expert panel discussions, live AI demonstrations, networking sessions, startup showcases, and practical workshops covering Generative AI, Machine Learning, AI Agents, Computer Vision, AI-powered software development, and responsible AI.\r\n\r\nParticipants will have the opportunity to discover emerging technologies, connect with industry professionals, showcase innovative projects, and explore how AI can be applied to solve real-world problems across different industries.', 'https://orlandosydney.com/wp-content/uploads/2023/08/Business-Networking-Photo-Example-for-Professionals-at-the-ICC-Sydney-Convention-Centre.-Photography.-By-orlandosydney.com-OS1_7380.jpg', '2026-09-30', '09:30:00', '17:00:00', 'Bandaranaike Memorial International Conference Hall', 'BMICH, Colombo 07, Sri Lanka', 150, 'PUBLIC', '2026-09-26 00:00:00', '2026-09-28 23:59:59', 'ACTIVE', '2026-09-26 07:49:45', '2026-09-26 20:17:53'),
(5, 2, 'UX/UI Masterclass: Designing for Humans', 'A deep dive into UX/UI.', 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0', '2026-09-04', '09:00:00', '15:00:00', 'Colombo', NULL, 500, 'PUBLIC', '2026-08-01 00:00:00', '2026-09-03 23:59:59', 'ACTIVE', '2026-09-26 20:17:42', '2026-09-26 20:17:42'),
(6, 2, 'Seed to Series A: Founders Mixer', 'Network with top VCs.', 'https://images.unsplash.com/photo-1556761175-4b46a572b786', '2026-09-10', '14:00:00', '18:00:00', 'Kandy', NULL, 300, 'PUBLIC', '2026-08-01 00:00:00', '2026-09-09 23:59:59', 'ACTIVE', '2026-09-26 20:17:42', '2026-09-26 20:17:42'),
(7, 2, 'HealthTech Innovators Gala', 'Showcasing health innovations.', 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528', '2026-09-18', '10:00:00', '16:00:00', 'Galle', NULL, 450, 'PUBLIC', '2026-08-01 00:00:00', '2026-09-17 23:59:59', 'ACTIVE', '2026-09-26 20:17:42', '2026-09-26 20:17:42');

-- --------------------------------------------------------

--
-- Table structure for table `event_interests`
--

CREATE TABLE `event_interests` (
  `event_id` int(10) UNSIGNED NOT NULL,
  `interest_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_interests`
--

INSERT INTO `event_interests` (`event_id`, `interest_id`) VALUES
(1, 2),
(1, 7),
(1, 12),
(1, 25),
(1, 35),
(1, 57),
(1, 71),
(1, 74),
(1, 76),
(1, 79);

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `registration_id` int(10) UNSIGNED NOT NULL,
  `event_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `status` enum('PENDING','APPROVED','REGISTERED','REJECTED','REMOVED','CANCELLED') NOT NULL DEFAULT 'REGISTERED',
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_registrations`
--

INSERT INTO `event_registrations` (`registration_id`, `event_id`, `user_id`, `status`, `registered_at`, `approved_at`) VALUES
(1, 7, 3, 'REGISTERED', '2026-09-26 20:25:42', NULL),
(2, 1, 3, 'REGISTERED', '2026-09-26 20:26:13', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `group_id` int(10) UNSIGNED NOT NULL,
  `creator_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('ACTIVE','ARCHIVED') NOT NULL DEFAULT 'ACTIVE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`group_id`, `creator_id`, `name`, `description`, `created_at`, `status`) VALUES
(1, 4, 'Ai & Robotics COmmunity', 'bcsbjlvbhzbvjhsbvjhbvjbvjzhdbjhbvzdjhbbcsbjlvbhzbvjhsbvjhbvjbvjzhdbjhbvzdjhbbcsbjlvbhzbvjhsbvjhbvjbvjzhdbjhbvzdjhbbcsbjlvbhzbvjhsbvjhbvjbvjzhdbjhbvzdjhbbcsbjlvbhzbvjhsbvjhbvjbvjzhdbjhbvzdjhbbcsbjlvbhzbvjhsbvjhbvjbvjzhdbjhbvzdjhbbcsbjlvbhzbvjhsbvjhbvjbvjzhdbjhbvzdjhbbcsbjlvbhzbvjhsbvjhbvjbvjzhdbjhbvzdjhb', '2026-09-26 05:04:56', 'ACTIVE'),
(2, 4, 'Postman API Fundamentals', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since 1966, when designers at Letraset and James Mosley, the librarian at St Bride Printing Library in London, took a 1914 Cicero translation and scrambled it to make dummy text for Letraset\'s Body Type sheets.', '2026-09-26 07:00:14', 'ARCHIVED');

-- --------------------------------------------------------

--
-- Table structure for table `group_interests`
--

CREATE TABLE `group_interests` (
  `group_id` int(10) UNSIGNED NOT NULL,
  `interest_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `group_interests`
--

INSERT INTO `group_interests` (`group_id`, `interest_id`) VALUES
(1, 3),
(1, 7),
(1, 11),
(1, 13),
(1, 14),
(1, 17),
(1, 35),
(1, 46),
(1, 57),
(1, 66),
(1, 79),
(2, 9),
(2, 16),
(2, 17),
(2, 25),
(2, 28),
(2, 49),
(2, 73),
(2, 77);

-- --------------------------------------------------------

--
-- Table structure for table `group_members`
--

CREATE TABLE `group_members` (
  `group_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('OWNER','ADMIN','MEMBER') NOT NULL DEFAULT 'MEMBER',
  `status` enum('ACTIVE','REMOVED') NOT NULL DEFAULT 'ACTIVE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `group_members`
--

INSERT INTO `group_members` (`group_id`, `user_id`, `joined_at`, `role`, `status`) VALUES
(1, 4, '2026-09-26 05:04:56', 'OWNER', 'ACTIVE'),
(2, 4, '2026-09-26 07:00:14', 'OWNER', 'ACTIVE');

-- --------------------------------------------------------

--
-- Table structure for table `interests`
--

CREATE TABLE `interests` (
  `interest_id` int(10) UNSIGNED NOT NULL,
  `interest_name` varchar(100) NOT NULL,
  `status` enum('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `interests`
--

INSERT INTO `interests` (`interest_id`, `interest_name`, `status`) VALUES
(1, 'Technology', 'ACTIVE'),
(2, 'Artificial Intelligence', 'ACTIVE'),
(3, 'Machine Learning', 'ACTIVE'),
(4, 'Software Development', 'ACTIVE'),
(5, 'Web Development', 'ACTIVE'),
(6, 'Mobile Technology', 'ACTIVE'),
(7, 'Cybersecurity', 'ACTIVE'),
(8, 'Cloud Computing', 'ACTIVE'),
(9, 'Data Science', 'ACTIVE'),
(10, 'Robotics', 'ACTIVE'),
(11, 'Internet of Things', 'ACTIVE'),
(12, 'Blockchain', 'ACTIVE'),
(13, 'Cryptocurrency', 'ACTIVE'),
(14, 'FinTech', 'ACTIVE'),
(15, 'HealthTech', 'ACTIVE'),
(16, 'EdTech', 'ACTIVE'),
(17, 'Gaming', 'ACTIVE'),
(18, 'Game Development', 'ACTIVE'),
(19, 'Startups', 'ACTIVE'),
(20, 'Entrepreneurship', 'ACTIVE'),
(21, 'Business', 'ACTIVE'),
(22, 'Finance', 'ACTIVE'),
(23, 'Investment', 'ACTIVE'),
(24, 'Marketing', 'ACTIVE'),
(25, 'Digital Marketing', 'ACTIVE'),
(26, 'Branding', 'ACTIVE'),
(27, 'Design', 'ACTIVE'),
(28, 'Graphic Design', 'ACTIVE'),
(29, 'UI/UX', 'ACTIVE'),
(30, 'Photography', 'ACTIVE'),
(31, 'Videography', 'ACTIVE'),
(32, 'Film', 'ACTIVE'),
(33, 'Music', 'ACTIVE'),
(34, 'Writing', 'ACTIVE'),
(35, 'Content Creation', 'ACTIVE'),
(36, 'Social Media', 'ACTIVE'),
(37, 'Education', 'ACTIVE'),
(38, 'Research', 'ACTIVE'),
(39, 'Science', 'ACTIVE'),
(40, 'Medicine', 'ACTIVE'),
(41, 'Healthcare', 'ACTIVE'),
(42, 'Biotechnology', 'ACTIVE'),
(43, 'Psychology', 'ACTIVE'),
(44, 'Public Health', 'ACTIVE'),
(45, 'Engineering', 'ACTIVE'),
(46, 'Architecture', 'ACTIVE'),
(47, 'Law', 'ACTIVE'),
(48, 'Economics', 'ACTIVE'),
(49, 'Leadership', 'ACTIVE'),
(50, 'Management', 'ACTIVE'),
(51, 'Innovation', 'ACTIVE'),
(52, 'Sustainability', 'ACTIVE'),
(53, 'Environment', 'ACTIVE'),
(54, 'Climate Change', 'ACTIVE'),
(55, 'Renewable Energy', 'ACTIVE'),
(56, 'Social Impact', 'ACTIVE'),
(57, 'Community Development', 'ACTIVE'),
(58, 'Volunteering', 'ACTIVE'),
(59, 'Travel', 'ACTIVE'),
(60, 'Sports', 'ACTIVE'),
(61, 'Cricket', 'ACTIVE'),
(62, 'Football', 'ACTIVE'),
(63, 'Basketball', 'ACTIVE'),
(64, 'Tennis', 'ACTIVE'),
(65, 'Chess', 'ACTIVE'),
(66, 'Fitness', 'ACTIVE'),
(67, 'Esports', 'ACTIVE'),
(68, 'Reading', 'ACTIVE'),
(69, 'Books', 'ACTIVE'),
(70, 'Public Speaking', 'ACTIVE'),
(71, 'Debate', 'ACTIVE'),
(72, 'Networking', 'ACTIVE'),
(73, 'Personal Development', 'ACTIVE'),
(74, 'Career Development', 'ACTIVE'),
(75, 'Professional Development', 'ACTIVE'),
(76, 'Creative Arts', 'ACTIVE'),
(77, 'Entrepreneurial Ecosystems', 'ACTIVE'),
(78, 'Open Source', 'ACTIVE'),
(79, 'Academic Communities', 'ACTIVE'),
(80, 'Professional Communities', 'ACTIVE');

-- --------------------------------------------------------

--
-- Table structure for table `networking_goals`
--

CREATE TABLE `networking_goals` (
  `goal_id` int(10) UNSIGNED NOT NULL,
  `goal_name` varchar(100) NOT NULL,
  `status` enum('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `networking_goals`
--

INSERT INTO `networking_goals` (`goal_id`, `goal_name`, `status`) VALUES
(1, 'Find Collaborators', 'ACTIVE'),
(2, 'Find a Mentor', 'ACTIVE'),
(3, 'Become a Mentor', 'ACTIVE'),
(4, 'Find a Mentee', 'ACTIVE'),
(5, 'Find a Co-Founder', 'ACTIVE'),
(6, 'Build a Startup', 'ACTIVE'),
(7, 'Find Business Partners', 'ACTIVE'),
(8, 'Find Investors', 'ACTIVE'),
(9, 'Find Investment Opportunities', 'ACTIVE'),
(10, 'Find Job Opportunities', 'ACTIVE'),
(11, 'Find Internship Opportunities', 'ACTIVE'),
(12, 'Recruit Talent', 'ACTIVE'),
(13, 'Hire Freelancers', 'ACTIVE'),
(14, 'Find Freelance Projects', 'ACTIVE'),
(15, 'Find Clients', 'ACTIVE'),
(16, 'Find Customers', 'ACTIVE'),
(17, 'Explore Career Opportunities', 'ACTIVE'),
(18, 'Explore New Industries', 'ACTIVE'),
(19, 'Build Professional Connections', 'ACTIVE'),
(20, 'Expand Professional Network', 'ACTIVE'),
(21, 'Meet Like-Minded People', 'ACTIVE'),
(22, 'Share Knowledge', 'ACTIVE'),
(23, 'Learn New Skills', 'ACTIVE'),
(24, 'Teach Others', 'ACTIVE'),
(25, 'Exchange Ideas', 'ACTIVE'),
(26, 'Collaborate on Research', 'ACTIVE'),
(27, 'Find Research Partners', 'ACTIVE'),
(28, 'Find Academic Collaborators', 'ACTIVE'),
(29, 'Find Project Partners', 'ACTIVE'),
(30, 'Join a Project', 'ACTIVE'),
(31, 'Start a Project', 'ACTIVE'),
(32, 'Join a Community', 'ACTIVE'),
(33, 'Build a Community', 'ACTIVE'),
(34, 'Join Professional Communities', 'ACTIVE'),
(35, 'Find Study Partners', 'ACTIVE'),
(36, 'Find Study Groups', 'ACTIVE'),
(37, 'Attend Industry Events', 'ACTIVE'),
(38, 'Discover New Opportunities', 'ACTIVE'),
(39, 'Explore Entrepreneurship', 'ACTIVE'),
(40, 'Develop Leadership Skills', 'ACTIVE'),
(41, 'Improve Communication Skills', 'ACTIVE'),
(42, 'Improve Technical Skills', 'ACTIVE'),
(43, 'Gain Industry Insights', 'ACTIVE'),
(44, 'Share Industry Experience', 'ACTIVE'),
(45, 'Discuss Emerging Technologies', 'ACTIVE'),
(46, 'Find Speaking Opportunities', 'ACTIVE'),
(47, 'Find Volunteering Opportunities', 'ACTIVE'),
(48, 'Support Social Causes', 'ACTIVE'),
(49, 'Build Long-Term Professional Relationships', 'ACTIVE'),
(50, 'Exchange Contacts', 'ACTIVE'),
(51, 'Discover Potential Collaborations', 'ACTIVE'),
(52, 'Find Creative Collaborators', 'ACTIVE');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(80) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `reference_type` varchar(80) DEFAULT NULL,
  `reference_id` int(10) UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `user_id`, `token_hash`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 3, '7bffe91595a322c39d7414940e882293b74d23129b0fc1170ea178c2f1fd2113', '2026-09-26 18:41:32', '2026-09-26 18:41:32', '2026-09-26 18:41:01');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `post_id` int(10) UNSIGNED NOT NULL,
  `group_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('ACTIVE','REMOVED') NOT NULL DEFAULT 'ACTIVE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `profile_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `bio` text DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `organization` varchar(200) DEFAULT NULL,
  `job_title` varchar(150) DEFAULT NULL,
  `field` varchar(150) DEFAULT NULL,
  `profile_completed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profiles`
--

INSERT INTO `profiles` (`profile_id`, `user_id`, `bio`, `profile_photo`, `organization`, `job_title`, `field`, `profile_completed`, `created_at`, `updated_at`) VALUES
(1, 3, '', NULL, '', '', '', 1, '2026-09-25 07:31:22', '2026-09-26 18:59:50');

-- --------------------------------------------------------

--
-- Table structure for table `qr_tokens`
--

CREATE TABLE `qr_tokens` (
  `qr_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `event_id` int(10) UNSIGNED DEFAULT NULL,
  `token_hash` char(64) NOT NULL,
  `type` enum('EVENT_CHECKIN','PERSONAL_PROFILE') NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `status` enum('ACTIVE','REVOKED','EXPIRED') NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `resource_id` int(10) UNSIGNED NOT NULL,
  `group_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `description` text DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('ACTIVE','REMOVED') NOT NULL DEFAULT 'ACTIVE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(10) UNSIGNED NOT NULL,
  `role_name` enum('ATTENDEE','ORGANIZER','ADMINISTRATOR') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'ATTENDEE'),
(2, 'ORGANIZER'),
(3, 'ADMINISTRATOR');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` bigint(20) UNSIGNED NOT NULL,
  `session_token_hash` char(64) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `skill_id` int(10) UNSIGNED NOT NULL,
  `skill_name` varchar(100) NOT NULL,
  `status` enum('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`skill_id`, `skill_name`, `status`) VALUES
(1, 'Web Development', 'ACTIVE'),
(2, 'Mobile App Development', 'ACTIVE'),
(3, 'Frontend Development', 'ACTIVE'),
(4, 'Backend Development', 'ACTIVE'),
(5, 'Full-Stack Development', 'ACTIVE'),
(6, 'Software Engineering', 'ACTIVE'),
(7, 'UI/UX Design', 'ACTIVE'),
(8, 'Graphic Design', 'ACTIVE'),
(9, 'Product Design', 'ACTIVE'),
(10, 'Motion Graphics', 'ACTIVE'),
(11, 'Video Editing', 'ACTIVE'),
(12, 'Photography', 'ACTIVE'),
(13, 'Videography', 'ACTIVE'),
(14, '3D Modelling', 'ACTIVE'),
(15, 'Animation', 'ACTIVE'),
(16, 'Game Development', 'ACTIVE'),
(17, 'Database Management', 'ACTIVE'),
(18, 'Data Analysis', 'ACTIVE'),
(19, 'Data Visualization', 'ACTIVE'),
(20, 'Machine Learning', 'ACTIVE'),
(21, 'Artificial Intelligence', 'ACTIVE'),
(22, 'Deep Learning', 'ACTIVE'),
(23, 'Natural Language Processing', 'ACTIVE'),
(24, 'Computer Vision', 'ACTIVE'),
(25, 'Cybersecurity', 'ACTIVE'),
(26, 'Ethical Hacking', 'ACTIVE'),
(27, 'Cloud Computing', 'ACTIVE'),
(28, 'DevOps', 'ACTIVE'),
(29, 'Software Testing', 'ACTIVE'),
(30, 'Quality Assurance', 'ACTIVE'),
(31, 'Project Management', 'ACTIVE'),
(32, 'Product Management', 'ACTIVE'),
(33, 'Business Analysis', 'ACTIVE'),
(34, 'Digital Marketing', 'ACTIVE'),
(35, 'Social Media Marketing', 'ACTIVE'),
(36, 'Content Marketing', 'ACTIVE'),
(37, 'Search Engine Optimization', 'ACTIVE'),
(38, 'Search Engine Marketing', 'ACTIVE'),
(39, 'Brand Management', 'ACTIVE'),
(40, 'Market Research', 'ACTIVE'),
(41, 'Sales', 'ACTIVE'),
(42, 'Business Development', 'ACTIVE'),
(43, 'Entrepreneurship', 'ACTIVE'),
(44, 'Public Speaking', 'ACTIVE'),
(45, 'Presentation', 'ACTIVE'),
(46, 'Communication', 'ACTIVE'),
(47, 'Leadership', 'ACTIVE'),
(48, 'Team Management', 'ACTIVE'),
(49, 'Teamwork', 'ACTIVE'),
(50, 'Negotiation', 'ACTIVE'),
(51, 'Critical Thinking', 'ACTIVE'),
(52, 'Problem Solving', 'ACTIVE'),
(53, 'Research', 'ACTIVE'),
(54, 'Academic Writing', 'ACTIVE'),
(55, 'Technical Writing', 'ACTIVE'),
(56, 'Event Management', 'ACTIVE'),
(57, 'Event Planning', 'ACTIVE'),
(58, 'Financial Management', 'ACTIVE'),
(59, 'Accounting', 'ACTIVE'),
(60, 'Investment Analysis', 'ACTIVE'),
(61, 'Data Science', 'ACTIVE'),
(62, 'Statistics', 'ACTIVE'),
(63, 'Scientific Research', 'ACTIVE'),
(64, 'Teaching', 'ACTIVE'),
(65, 'Mentoring', 'ACTIVE'),
(66, 'Coaching', 'ACTIVE'),
(67, 'Healthcare', 'ACTIVE'),
(68, 'Medicine', 'ACTIVE'),
(69, 'Nursing', 'ACTIVE'),
(70, 'Public Health', 'ACTIVE'),
(71, 'Biotechnology', 'ACTIVE'),
(72, 'Biomedical Research', 'ACTIVE'),
(73, 'Engineering', 'ACTIVE'),
(74, 'Architecture', 'ACTIVE'),
(75, 'Legal Research', 'ACTIVE'),
(76, 'Human Resources', 'ACTIVE'),
(77, 'Recruitment', 'ACTIVE'),
(78, 'Operations Management', 'ACTIVE'),
(79, 'Supply Chain Management', 'ACTIVE'),
(80, 'Foreign Languages', 'ACTIVE'),
(81, 'Translation', 'ACTIVE'),
(82, 'Community Management', 'ACTIVE'),
(83, 'Customer Service', 'ACTIVE'),
(84, 'Time Management', 'ACTIVE'),
(85, 'Organizational Skills', 'ACTIVE');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `account_status` enum('ACTIVE','DISABLED','SUSPENDED') NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password_hash`, `email_verified`, `account_status`, `created_at`, `updated_at`) VALUES
(2, 'John Doe', '123@gmail.com', '$2y$10$Mg529hcpuih3J5D5tAzCteFRKxDwZnINM6Gv5t9tLQ1qxuRUyfUFa', 0, 'ACTIVE', '2026-09-25 06:54:11', '2026-09-25 06:54:11'),
(3, 'Yasiru Lakintha', 'yasirulaki04@gmail.com', '$2y$10$RlEz/pWJBSX733p4ud3kduQoiWgUvnnWh2Dr8WMq893ZSIUIfLVuq', 1, 'ACTIVE', '2026-09-25 06:54:24', '2026-09-26 18:41:32'),
(4, 'Lakintha Samarasekara', 'dev.eventdna@gmail.com', '$2y$10$3qLMp2LmsB35P49.6rCP2O9qd/2dximK8TKAmNHHzfVPZ0xHCwnOi', 1, 'ACTIVE', '2026-09-26 05:01:58', '2026-09-26 05:02:29'),
(5, 'Lakintha Samarasekara', 'yasiru.upwork@gmail.com', '$2y$10$avc1RJlNkuhCA9CYhrv55OuYqj/7X5c9QUigikcuOCSEDlwc.VXui', 0, 'ACTIVE', '2026-09-26 18:34:06', '2026-09-26 18:34:06'),
(6, 'Lakintha Samarasekara', 'yasiru.test@gmail.com', '$2y$10$bzBPRuzyeV7Uvh4aCAbRo.LNg.owlnpaVfemqn4zEfgXvmxUMBC.e', 0, 'ACTIVE', '2026-09-26 18:38:34', '2026-09-26 18:38:34');

-- --------------------------------------------------------

--
-- Table structure for table `user_interests`
--

CREATE TABLE `user_interests` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `interest_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_interests`
--

INSERT INTO `user_interests` (`user_id`, `interest_id`) VALUES
(3, 5),
(3, 6),
(3, 12),
(3, 20),
(3, 23),
(3, 28),
(3, 29),
(3, 37),
(3, 41),
(3, 45),
(3, 55),
(3, 57),
(3, 65),
(3, 67),
(3, 74),
(3, 77);

-- --------------------------------------------------------

--
-- Table structure for table `user_networking_goals`
--

CREATE TABLE `user_networking_goals` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `goal_id` int(10) UNSIGNED NOT NULL,
  `priority` tinyint(3) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_networking_goals`
--

INSERT INTO `user_networking_goals` (`user_id`, `goal_id`, `priority`) VALUES
(3, 1, NULL),
(3, 2, NULL),
(3, 6, NULL),
(3, 15, NULL),
(3, 26, NULL),
(3, 33, NULL),
(3, 37, NULL),
(3, 40, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(2, 1),
(3, 1),
(4, 2),
(5, 1),
(6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_skills`
--

CREATE TABLE `user_skills` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `skill_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_skills`
--

INSERT INTO `user_skills` (`user_id`, `skill_id`) VALUES
(3, 4),
(3, 14),
(3, 21),
(3, 24),
(3, 36),
(3, 39),
(3, 46),
(3, 56),
(3, 66),
(3, 71);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `event_id` (`event_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `connections`
--
ALTER TABLE `connections`
  ADD PRIMARY KEY (`connection_id`),
  ADD KEY `requester_id` (`requester_id`),
  ADD KEY `recipient_id` (`recipient_id`);

--
-- Indexes for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `fk_verification_user` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `fk_events_organizer` (`organizer_id`);

--
-- Indexes for table `event_interests`
--
ALTER TABLE `event_interests`
  ADD PRIMARY KEY (`event_id`,`interest_id`),
  ADD KEY `interest_id` (`interest_id`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD UNIQUE KEY `event_id` (`event_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`group_id`),
  ADD KEY `creator_id` (`creator_id`);

--
-- Indexes for table `group_interests`
--
ALTER TABLE `group_interests`
  ADD PRIMARY KEY (`group_id`,`interest_id`),
  ADD KEY `interest_id` (`interest_id`);

--
-- Indexes for table `group_members`
--
ALTER TABLE `group_members`
  ADD PRIMARY KEY (`group_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `interests`
--
ALTER TABLE `interests`
  ADD PRIMARY KEY (`interest_id`),
  ADD UNIQUE KEY `interest_name` (`interest_name`);

--
-- Indexes for table `networking_goals`
--
ALTER TABLE `networking_goals`
  ADD PRIMARY KEY (`goal_id`),
  ADD UNIQUE KEY `goal_name` (`goal_name`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `fk_reset_user` (`user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `qr_tokens`
--
ALTER TABLE `qr_tokens`
  ADD PRIMARY KEY (`qr_id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`resource_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD UNIQUE KEY `session_token_hash` (`session_token_hash`),
  ADD KEY `fk_sessions_user` (`user_id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`skill_id`),
  ADD UNIQUE KEY `skill_name` (`skill_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_interests`
--
ALTER TABLE `user_interests`
  ADD PRIMARY KEY (`user_id`,`interest_id`),
  ADD KEY `interest_id` (`interest_id`);

--
-- Indexes for table `user_networking_goals`
--
ALTER TABLE `user_networking_goals`
  ADD PRIMARY KEY (`user_id`,`goal_id`),
  ADD KEY `goal_id` (`goal_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `user_skills`
--
ALTER TABLE `user_skills`
  ADD PRIMARY KEY (`user_id`,`skill_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `connections`
--
ALTER TABLE `connections`
  MODIFY `connection_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `registration_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `group_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `interests`
--
ALTER TABLE `interests`
  MODIFY `interest_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `networking_goals`
--
ALTER TABLE `networking_goals`
  MODIFY `goal_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `profile_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `qr_tokens`
--
ALTER TABLE `qr_tokens`
  MODIFY `qr_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `resource_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `session_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `skill_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `connections`
--
ALTER TABLE `connections`
  ADD CONSTRAINT `connections_ibfk_1` FOREIGN KEY (`requester_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `connections_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  ADD CONSTRAINT `fk_verification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_events_organizer` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `event_interests`
--
ALTER TABLE `event_interests`
  ADD CONSTRAINT `event_interests_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_interests_ibfk_2` FOREIGN KEY (`interest_id`) REFERENCES `interests` (`interest_id`) ON DELETE CASCADE;

--
-- Constraints for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_registrations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `group_interests`
--
ALTER TABLE `group_interests`
  ADD CONSTRAINT `group_interests_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_interests_ibfk_2` FOREIGN KEY (`interest_id`) REFERENCES `interests` (`interest_id`) ON DELETE CASCADE;

--
-- Constraints for table `group_members`
--
ALTER TABLE `group_members`
  ADD CONSTRAINT `group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `fk_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `fk_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `qr_tokens`
--
ALTER TABLE `qr_tokens`
  ADD CONSTRAINT `qr_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qr_tokens_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resources_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_interests`
--
ALTER TABLE `user_interests`
  ADD CONSTRAINT `user_interests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_interests_ibfk_2` FOREIGN KEY (`interest_id`) REFERENCES `interests` (`interest_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_networking_goals`
--
ALTER TABLE `user_networking_goals`
  ADD CONSTRAINT `user_networking_goals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_networking_goals_ibfk_2` FOREIGN KEY (`goal_id`) REFERENCES `networking_goals` (`goal_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_skills`
--
ALTER TABLE `user_skills`
  ADD CONSTRAINT `user_skills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`skill_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
