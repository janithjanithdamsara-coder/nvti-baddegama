-- EduCore LMS Database Schema
-- NVTI Baddegama Diploma Project (ICT NVQ Level 5)

CREATE DATABASE IF NOT EXISTS `educore_lms` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `educore_lms`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `login_logs`;
DROP TABLE IF EXISTS `announcements`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `grades`;
DROP TABLE IF EXISTS `submissions`;
DROP TABLE IF EXISTS `assignments`;
DROP TABLE IF EXISTS `materials`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `class_sessions`;
DROP TABLE IF EXISTS `teacher_assignments`;
DROP TABLE IF EXISTS `enrollments`;
DROP TABLE IF EXISTS `class_subjects`;
DROP TABLE IF EXISTS `subjects`;
DROP TABLE IF EXISTS `classes`;
DROP TABLE IF EXISTS `courses`;
DROP TABLE IF EXISTS `teacher_profiles`;
DROP TABLE IF EXISTS `student_profiles`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Users Table
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'teacher', 'student') NOT NULL DEFAULT 'student',
  `status` ENUM('active', 'pending', 'suspended') NOT NULL DEFAULT 'active',
  `profile_image` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`email`),
  INDEX (`role`),
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Student Profiles
CREATE TABLE `student_profiles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `student_code` VARCHAR(50) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `nic` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `emergency_contact` VARCHAR(20) DEFAULT NULL,
  `course_id` INT DEFAULT NULL,
  `class_id` INT DEFAULT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Teacher Profiles
CREATE TABLE `teacher_profiles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `teacher_code` VARCHAR(50) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `qualification` VARCHAR(255) DEFAULT NULL,
  `specialization` VARCHAR(255) DEFAULT NULL,
  `preferred_slot` VARCHAR(50) DEFAULT 'Morning (09:00 - 12:00)',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Courses Table
CREATE TABLE `courses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `course_code` VARCHAR(30) NOT NULL UNIQUE,
  `course_name` VARCHAR(200) NOT NULL,
  `nvq_level` VARCHAR(20) DEFAULT 'Level 5',
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Classes / Batches Table
CREATE TABLE `classes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT NOT NULL,
  `class_name` VARCHAR(150) NOT NULL,
  `academic_year` VARCHAR(50) DEFAULT '2026/2027',
  `status` ENUM('active', 'archived') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Subjects Table
CREATE TABLE `subjects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `subject_code` VARCHAR(30) NOT NULL UNIQUE,
  `subject_name` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `credits` INT DEFAULT 4,
  `status` ENUM('active', 'inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Class Subjects (Pivot)
CREATE TABLE `class_subjects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `class_id` INT NOT NULL,
  `subject_id` INT NOT NULL,
  UNIQUE KEY `uniq_class_subject` (`class_id`, `subject_id`),
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Teacher Assignments (Assign Teacher to Class Subject)
CREATE TABLE `teacher_assignments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `teacher_id` INT NOT NULL,
  `class_subject_id` INT NOT NULL,
  `assigned_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_teacher_subject` (`teacher_id`, `class_subject_id`),
  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`class_subject_id`) REFERENCES `class_subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Enrollments (Student Enrolled in Class)
CREATE TABLE `enrollments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `class_id` INT NOT NULL,
  `enrolled_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('active', 'completed', 'dropped') DEFAULT 'active',
  UNIQUE KEY `uniq_enrollment` (`student_id`, `class_id`),
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Class Sessions (Timetable Management - morning/afternoon)
CREATE TABLE `class_sessions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `teacher_id` INT NOT NULL,
  `class_id` INT NOT NULL,
  `subject_id` INT NOT NULL,
  `session_title` VARCHAR(200) NOT NULL,
  `day_of_week` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') DEFAULT 'Monday',
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `session_type` ENUM('physical', 'online', 'hybrid') DEFAULT 'physical',
  `hall_or_link` VARCHAR(255) DEFAULT 'ICT Lab 01',
  `status` ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
  `notes` TEXT DEFAULT NULL,
  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Categories for Learning Materials
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_name` VARCHAR(100) NOT NULL UNIQUE,
  `icon` VARCHAR(50) DEFAULT 'bi-folder',
  `description` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Learning Materials
CREATE TABLE `materials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `teacher_id` INT NOT NULL,
  `class_id` INT NOT NULL,
  `subject_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `material_type` ENUM('file', 'youtube', 'url') NOT NULL DEFAULT 'file',
  `file_path` VARCHAR(255) DEFAULT NULL,
  `original_filename` VARCHAR(255) DEFAULT NULL,
  `external_url` TEXT DEFAULT NULL,
  `file_size_bytes` BIGINT DEFAULT 0,
  `download_count` INT DEFAULT 0,
  `is_hidden` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Assignments
CREATE TABLE `assignments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `teacher_id` INT NOT NULL,
  `class_id` INT NOT NULL,
  `subject_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `instructions` TEXT NOT NULL,
  `reference_file` VARCHAR(255) DEFAULT NULL,
  `total_marks` DECIMAL(5, 2) NOT NULL DEFAULT 100.00,
  `deadline` DATETIME NOT NULL,
  `status` ENUM('active', 'closed') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Submissions
CREATE TABLE `submissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `assignment_id` INT NOT NULL,
  `student_id` INT NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `original_filename` VARCHAR(255) NOT NULL,
  `student_comment` TEXT DEFAULT NULL,
  `submitted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `is_late` TINYINT(1) DEFAULT 0,
  `marks` DECIMAL(5, 2) DEFAULT NULL,
  `feedback` TEXT DEFAULT NULL,
  `graded_by` INT DEFAULT NULL,
  `graded_at` DATETIME DEFAULT NULL,
  `status` ENUM('submitted', 'graded', 'resubmit_required') DEFAULT 'submitted',
  UNIQUE KEY `uniq_student_assignment` (`assignment_id`, `student_id`),
  FOREIGN KEY (`assignment_id`) REFERENCES `assignments`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`graded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Announcements
CREATE TABLE `announcements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `created_by` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `content` TEXT NOT NULL,
  `target_role` ENUM('all', 'teachers', 'students') DEFAULT 'all',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Notifications
CREATE TABLE `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `link` VARCHAR(255) DEFAULT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Password Resets
CREATE TABLE `password_resets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) NOT NULL,
  `token_hash` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Audit Logs
CREATE TABLE `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(50) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Login Logs
CREATE TABLE `login_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) NOT NULL,
  `status` ENUM('success', 'failed') NOT NULL,
  `ip_address` VARCHAR(50) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- SEED DATA
-- ========================================================

-- Categories
INSERT INTO `categories` (`id`, `category_name`, `icon`, `description`) VALUES
(1, 'PDF Notes', 'bi-file-earmark-pdf-fill', 'Comprehensive lecture slide notes and handouts'),
(2, 'Past Papers', 'bi-archive-fill', 'Past semester and national examination papers'),
(3, 'Model Papers', 'bi-file-text-fill', 'Practice and model question papers with guide answers'),
(4, 'Tutorials & Lab Sheets', 'bi-journal-code', 'Practical exercises, worksheets and lab manuals'),
(5, 'Diagrams & Images', 'bi-file-image-fill', 'System architecture, network diagrams, ERD charts'),
(6, 'YouTube Video Lessons', 'bi-youtube', 'Curated YouTube video tutorials & online lectures'),
(7, 'Other Resources & Links', 'bi-link-45deg', 'Reference links, documentation, online tools & repos');

-- Users
-- Password for all seed users:
-- Admin: AdminPassword123!
-- Teacher1 & Teacher2: TeacherPassword123!
-- Student: StudentPassword123!
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`) VALUES
(1, 'NVTI System Administrator', 'admin@nvti.edu.lk', '$2y$10$sSByduCO59jN3m3I8m.4gebf0Fme0tt2F1ddPC6L5kiAxOJB36jaS', 'admin', 'active'),
(2, 'Mr. Samantha Perera (Morning Lecturer)', 'teacher1@nvti.edu.lk', '$2y$10$vvNdpPl3f5zgycPrUOyQ0.H3iYdIleDbnAxwYD2./ZZMwimCuRhka', 'teacher', 'active'),
(3, 'Mr. Kusal Bandara (Afternoon Lecturer)', 'teacher2@nvti.edu.lk', '$2y$10$vvNdpPl3f5zgycPrUOyQ0.H3iYdIleDbnAxwYD2./ZZMwimCuRhka', 'teacher', 'active'),
(4, 'Kasun Madushanka (Student)', 'student@nvti.edu.lk', '$2y$10$Dl968L.fnbOts/69YsKpweo3/d0exi2K.ZFzH4h0iJGrF6WoS46i2', 'student', 'active');

-- Teacher Profiles
INSERT INTO `teacher_profiles` (`user_id`, `teacher_code`, `phone`, `qualification`, `specialization`, `preferred_slot`) VALUES
(2, 'NVTI-TCH-001', '0771234567', 'BSc in Information Systems, NVQ Assessor', 'Database Systems & Networking', 'Morning (09:00 - 12:00)'),
(3, 'NVTI-TCH-002', '0719876543', 'BIT (UCSC), MSc in Computer Science', 'Web Development & Software Engineering', 'Afternoon (13:00 - 16:00)');

-- Course
INSERT INTO `courses` (`id`, `course_code`, `course_name`, `nvq_level`, `description`, `status`) VALUES
(1, 'NVTI-ICT-L5', 'National Diploma in Information Communication Technology', 'NVQ Level 5', 'Comprehensive NVQ Level 5 diploma covering Software Development, Database Management, and Network Security at NVTI Baddegama Center.', 'active');

-- Class
INSERT INTO `classes` (`id`, `course_id`, `class_name`, `academic_year`, `status`) VALUES
(1, 1, 'NVTI Baddegama ICT L5 - Batch 2026', '2026/2027', 'active');

-- Student Profile
INSERT INTO `student_profiles` (`user_id`, `student_code`, `phone`, `nic`, `address`, `emergency_contact`, `course_id`, `class_id`) VALUES
(4, 'NVTI-ST-2026-001', '0761122334', '200125409876', 'Galle Road, Baddegama', '0712233445', 1, 1);

-- Enrollments
INSERT INTO `enrollments` (`student_id`, `class_id`, `status`) VALUES
(4, 1, 'active');

-- Subjects
INSERT INTO `subjects` (`id`, `subject_code`, `subject_name`, `description`, `credits`, `status`) VALUES
(1, 'ICT501', 'Database Systems & SQL Design', 'Relational database design, MySQL, normalization, queries, stored procedures and administration.', 4, 'active'),
(2, 'ICT502', 'Web Application Development with PHP', 'Modern web architecture, secure PHP scripting, MVC patterns, Bootstrap 5, and frontend integration.', 4, 'active'),
(3, 'ICT503', 'Computer Networking & Network Security', 'TCP/IP networking, routing, switching, firewalls, and server administration.', 3, 'active'),
(4, 'ICT504', 'Software Engineering & Analysis', 'System analysis, UML modeling, testing, and documentation for ICT diploma projects.', 3, 'active');

-- Class Subjects mapping
INSERT INTO `class_subjects` (`id`, `class_id`, `subject_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4);

-- Teacher Assignments
-- Teacher 1 (Morning): Database Systems (Subj 1) and Networking (Subj 3)
-- Teacher 2 (Afternoon): Web Dev (Subj 2) and Software Eng (Subj 4)
INSERT INTO `teacher_assignments` (`teacher_id`, `class_subject_id`) VALUES
(2, 1),
(2, 3),
(3, 2),
(3, 4);

-- Timetable / Class Sessions
INSERT INTO `class_sessions` (`teacher_id`, `class_id`, `subject_id`, `session_title`, `day_of_week`, `start_time`, `end_time`, `session_type`, `hall_or_link`, `status`, `notes`) VALUES
(2, 1, 1, 'Database Systems & ER Modeling (Morning Session)', 'Monday', '09:00:00', '12:00:00', 'physical', 'ICT Lab 01 (Baddegama)', 'scheduled', 'Teacher 01 Morning Session'),
(3, 1, 2, 'PHP Web Development & LMS Architecture (Afternoon Session)', 'Monday', '13:00:00', '16:00:00', 'physical', 'ICT Lab 02 (Baddegama)', 'scheduled', 'Teacher 02 Afternoon Session'),
(2, 1, 3, 'Network Security & Firewalls (Morning Session)', 'Wednesday', '09:00:00', '12:00:00', 'physical', 'Hardware & Network Lab', 'scheduled', 'Teacher 01 Morning Session'),
(3, 1, 4, 'Software Engineering & UML Diagrams (Afternoon Session)', 'Wednesday', '13:00:00', '16:00:00', 'physical', 'ICT Lab 02 (Baddegama)', 'scheduled', 'Teacher 02 Afternoon Session');

-- Announcements
INSERT INTO `announcements` (`created_by`, `title`, `content`, `target_role`) VALUES
(1, 'Welcome to EduCore LMS - NVTI Baddegama', 'Welcome all lecturers and students to the official NVTI Baddegama Diploma LMS. Morning sessions run 09:00 - 12:00 and afternoon sessions run 13:00 - 16:00.', 'all');

-- Sample Materials
INSERT INTO `materials` (`id`, `teacher_id`, `class_id`, `subject_id`, `category_id`, `title`, `description`, `material_type`, `file_path`, `original_filename`, `external_url`, `file_size_bytes`) VALUES
(1, 2, 1, 1, 1, 'Introduction to Relational Databases & Normalization', 'Complete lecture presentation slides for 3NF Normalization.', 'file', 'sample_db_notes.pdf', 'Lecture_01_Normalization.pdf', NULL, 1542000),
(2, 3, 1, 2, 6, 'Complete PHP 8 & MySQL Tutorial Series', 'Video guide covering PDO, prepared statements, and session security.', 'youtube', NULL, NULL, 'https://www.youtube.com/watch?v=OK_JCtrrv-c', 0),
(3, 3, 1, 2, 7, 'PHP Official PDO Manual & Security Guidelines', 'Official documentation link for secure database queries.', 'url', NULL, NULL, 'https://www.php.net/manual/en/book.pdo.php', 0),
(4, 2, 1, 1, 2, '2025 Semester 1 Past Examination Paper', 'Past paper for Database Systems including marking scheme.', 'file', 'sample_past_paper.pdf', 'ICT501_Past_Paper_2025.pdf', NULL, 2300000);

-- Sample Assignment
INSERT INTO `assignments` (`id`, `teacher_id`, `class_id`, `subject_id`, `title`, `instructions`, `reference_file`, `total_marks`, `deadline`, `status`) VALUES
(1, 3, 1, 2, 'Assignment 01: Secure Login & CRUD System in PHP', 'Develop a PHP application with user registration, login with password_hash, and role-based redirect. Upload your project as a ZIP or PDF report.', NULL, 100.00, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 7 DAY), 'active');


-- =====================================================
-- Candidate Applications from NVTI Baddegama Website
-- =====================================================
CREATE TABLE IF NOT EXISTS `student_applications` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `external_id` int(10) UNSIGNED DEFAULT NULL,
  `student_id` varchar(50) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `nic` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `contact_no` varchar(20) NOT NULL,
  `whatsapp_no` varchar(20) DEFAULT NULL,
  `ol_pass_status` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `ol_english_grade` varchar(5) DEFAULT NULL,
  `ol_maths_grade` varchar(5) DEFAULT NULL,
  `ol_science_grade` varchar(5) DEFAULT NULL,
  `al_category` enum('science','commerce','arts','tech','other') NOT NULL DEFAULT 'tech',
  `course_option_one` varchar(100) NOT NULL,
  `course_option_two` varchar(100) DEFAULT NULL,
  `application_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','called_for_interview','selected','enrolled','rejected') NOT NULL DEFAULT 'pending',
  `is_processed` tinyint(1) DEFAULT 0,
  `enrolled_user_id` int(11) DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_app_nic` (`nic`),
  KEY `idx_app_status` (`status`),
  KEY `idx_app_date` (`application_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Runtime System Settings & Email Delivery History
-- =====================================================
CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_key` varchar(100) NOT NULL PRIMARY KEY,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `recipient_email` varchar(255) NOT NULL,
  `recipient_name` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `html_body` mediumtext DEFAULT NULL,
  `email_type` varchar(50) NOT NULL DEFAULT 'general',
  `status` enum('sent','skipped','failed') NOT NULL DEFAULT 'sent',
  `error_message` text DEFAULT NULL,
  `sent_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  KEY `idx_email_logs_created` (`created_at`),
  KEY `idx_email_logs_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
