<?php
/**
 * Comprehensive Safe Schema Synchronization & Migration Script
 * Run via CLI on VPS: php database/migrate_all.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    echo "Checking database schema synchronization...\n";

    // 1. Ensure materials.is_hidden column exists
    $colCheck = $db->query("SHOW COLUMNS FROM `materials` LIKE 'is_hidden'")->fetch();
    if (!$colCheck) {
        $db->exec("ALTER TABLE `materials` ADD COLUMN `is_hidden` TINYINT(1) NOT NULL DEFAULT 0 AFTER `download_count`");
        echo "[SUCCESS] Added missing column `materials.is_hidden`.\n";
    } else {
        echo "[OK] Column `materials.is_hidden` already exists.\n";
    }

    // 2. Ensure student_profiles extra columns exist
    $profileCols = [
        'mis_number'       => "ADD COLUMN `mis_number` VARCHAR(50) DEFAULT NULL AFTER `student_code`",
        'whatsapp_number'  => "ADD COLUMN `whatsapp_number` VARCHAR(20) DEFAULT NULL AFTER `phone`",
        'guardian_name'    => "ADD COLUMN `guardian_name` VARCHAR(150) DEFAULT NULL AFTER `emergency_contact`",
        'guardian_address' => "ADD COLUMN `guardian_address` TEXT DEFAULT NULL AFTER `guardian_name`",
        'guardian_phone'   => "ADD COLUMN `guardian_phone` VARCHAR(20) DEFAULT NULL AFTER `guardian_address`"
    ];

    foreach ($profileCols as $col => $alterSql) {
        $chk = $db->query("SHOW COLUMNS FROM `student_profiles` LIKE '$col'")->fetch();
        if (!$chk) {
            $db->exec("ALTER TABLE `student_profiles` $alterSql");
            echo "[SUCCESS] Added missing column `student_profiles.$col`.\n";
        } else {
            echo "[OK] Column `student_profiles.$col` already exists.\n";
        }
    }

    // 3. Ensure student_applications table exists
    $db->exec("CREATE TABLE IF NOT EXISTS `student_applications` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "[OK] Verified `student_applications` table.\n";

    echo "[COMPLETED] Database is 100% synchronized and healthy!\n";
} catch (Exception $e) {
    echo "[ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
