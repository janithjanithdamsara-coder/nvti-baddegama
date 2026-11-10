<?php
/**
 * Safe Database Migration for student_applications table
 * Run via CLI: php database/migrate_applications.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();

    $sql = "CREATE TABLE IF NOT EXISTS `student_applications` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($sql);
    echo "[SUCCESS] Table student_applications created or already exists.\n";

    // Populate initial sample applications if table is empty
    $count = (int)$db->query("SELECT COUNT(*) FROM student_applications")->fetchColumn();
    if ($count === 0) {
        $demoApps = [
            [
                'student_id' => 'APP-2026-001',
                'full_name' => 'Kavindu Chamod Wickramasinghe',
                'nic' => '200418902341',
                'address' => 'No. 45, Temple Road, Baddegama',
                'dob' => '2004-06-15',
                'contact_no' => '0771234567',
                'whatsapp_no' => '0771234567',
                'ol_pass_status' => 'Yes',
                'ol_english_grade' => 'A',
                'ol_maths_grade' => 'B',
                'ol_science_grade' => 'B',
                'al_category' => 'tech',
                'course_option_one' => 'National Diploma in ICT (NVQ Level 5)',
                'course_option_two' => 'Computer Graphic Design (NVQ Level 4)',
                'status' => 'called_for_interview',
                'application_date' => '2026-11-01 10:30:00'
            ],
            [
                'student_id' => 'APP-2026-002',
                'full_name' => 'Sanduni Nethmini De Silva',
                'nic' => '200384501928',
                'address' => 'Galle Road, Hikkaduwa',
                'dob' => '2003-11-20',
                'contact_no' => '0719876543',
                'whatsapp_no' => '0719876543',
                'ol_pass_status' => 'Yes',
                'ol_english_grade' => 'B',
                'ol_maths_grade' => 'A',
                'ol_science_grade' => 'A',
                'al_category' => 'science',
                'course_option_one' => 'National Diploma in ICT (NVQ Level 5)',
                'course_option_two' => 'Software Developer (NVQ Level 4)',
                'status' => 'selected',
                'application_date' => '2026-11-02 14:15:00'
            ],
            [
                'student_id' => 'APP-2026-003',
                'full_name' => 'Tharindu Madushanka Perera',
                'nic' => '200421004812',
                'address' => 'Medakeembiya, Baddegama',
                'dob' => '2004-03-08',
                'contact_no' => '0783344556',
                'whatsapp_no' => '0783344556',
                'ol_pass_status' => 'Yes',
                'ol_english_grade' => 'C',
                'ol_maths_grade' => 'C',
                'ol_science_grade' => 'B',
                'al_category' => 'commerce',
                'course_option_one' => 'National Diploma in ICT (NVQ Level 5)',
                'course_option_two' => 'Computer Hardware Technician',
                'status' => 'pending',
                'application_date' => '2026-11-05 09:45:00'
            ],
            [
                'student_id' => 'APP-2026-004',
                'full_name' => 'Dilshan Anjana Senaratne',
                'nic' => '200319803456',
                'address' => 'Nagoda, Galle',
                'dob' => '2003-08-25',
                'contact_no' => '0704455667',
                'whatsapp_no' => '0704455667',
                'ol_pass_status' => 'Yes',
                'ol_english_grade' => 'B',
                'ol_maths_grade' => 'B',
                'ol_science_grade' => 'C',
                'al_category' => 'tech',
                'course_option_one' => 'National Diploma in ICT (NVQ Level 5)',
                'course_option_two' => 'Web Design & Development',
                'status' => 'pending',
                'application_date' => '2026-11-07 16:20:00'
            ],
            [
                'student_id' => 'APP-2026-005',
                'full_name' => 'Hansika Chathurangi Liyanage',
                'nic' => '200475601290',
                'address' => 'Udugama Road, Baddegama',
                'dob' => '2004-12-05',
                'contact_no' => '0765566778',
                'whatsapp_no' => '0765566778',
                'ol_pass_status' => 'Yes',
                'ol_english_grade' => 'A',
                'ol_maths_grade' => 'A',
                'ol_science_grade' => 'A',
                'al_category' => 'tech',
                'course_option_one' => 'National Diploma in ICT (NVQ Level 5)',
                'course_option_two' => 'Information Communication Technology (NVQ 4)',
                'status' => 'selected',
                'application_date' => '2026-11-08 11:10:00'
            ]
        ];

        $ins = $db->prepare("INSERT INTO student_applications 
            (student_id, full_name, nic, address, dob, contact_no, whatsapp_no, ol_pass_status, ol_english_grade, ol_maths_grade, ol_science_grade, al_category, course_option_one, course_option_two, status, application_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($demoApps as $a) {
            $ins->execute([
                $a['student_id'], $a['full_name'], $a['nic'], $a['address'], $a['dob'],
                $a['contact_no'], $a['whatsapp_no'], $a['ol_pass_status'], $a['ol_english_grade'],
                $a['ol_maths_grade'], $a['ol_science_grade'], $a['al_category'],
                $a['course_option_one'], $a['course_option_two'], $a['status'], $a['application_date']
            ]);
        }
        echo "[SUCCESS] Inserted 5 initial demo applications.\n";
    } else {
        echo "[INFO] Existing applications in table: $count.\n";
    }

    echo "[COMPLETED] Database is fully ready for Online Candidate Applications Hub!\n";
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}
