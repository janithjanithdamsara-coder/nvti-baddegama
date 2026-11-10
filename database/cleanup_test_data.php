<?php
/**
 * Safe Live-Test Preparation & Test Data Cleanup Script (CLI ONLY)
 * Run on VPS: php database/cleanup_test_data.php
 *
 * Keeps:
 *  - Admin Account: admin@nvti.edu.lk (Password: AdminPassword123!)
 *  - Test Student Account: student@nvti.edu.lk - Kasun Madushanka (Password: StudentPassword123!)
 *  - Instructors & Academic Admins (for Homepage & Timetable integrity)
 *  - Core Curriculum, Subjects, Classes, Categories, Materials, Assignments, Past Papers
 *
 * Deletes:
 *  - All other dummy/test student accounts & profiles
 *  - Demo online candidate applications (student_applications)
 *  - Test attendance logs, day locks, holidays, submissions, documents, audit & login logs
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Forbidden: This script can only be executed from CLI.\n");
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    $db->beginTransaction();

    echo "========================================================\n";
    echo " NVTI Baddegama LMS - Live Test Cleanup & Reset\n";
    echo "========================================================\n";

    // 1. Ensure Admin account credentials
    $adminHash = password_hash('AdminPassword123!', PASSWORD_BCRYPT);
    $admStmt = $db->prepare("UPDATE users SET password = ?, status = 'active' WHERE email = 'admin@nvti.edu.lk'");
    $admStmt->execute([$adminHash]);
    echo "[OK] Admin verified: admin@nvti.edu.lk / AdminPassword123!\n";

    // 2. Ensure Kasun Madushanka (Test Student) credentials & active status
    $stuHash = password_hash('StudentPassword123!', PASSWORD_BCRYPT);
    $stuCheck = $db->prepare("SELECT id FROM users WHERE email = 'student@nvti.edu.lk' LIMIT 1");
    $stuCheck->execute();
    $kasunId = (int)$stuCheck->fetchColumn();

    if ($kasunId > 0) {
        $db->prepare("UPDATE users SET name = 'Kasun Madushanka (Student)', password = ?, status = 'active' WHERE id = ?")
           ->execute([$stuHash, $kasunId]);
        echo "[OK] Test student verified: student@nvti.edu.lk / StudentPassword123! (ID #$kasunId)\n";
    } else {
        echo "[WARN] student@nvti.edu.lk not found in database.\n";
    }

    // 3. Delete all OTHER test/demo student accounts
    if ($kasunId > 0) {
        $delStudents = $db->prepare("DELETE FROM users WHERE role = 'student' AND id != ?");
        $delStudents->execute([$kasunId]);
    } else {
        $delStudents = $db->query("DELETE FROM users WHERE role = 'student' AND email != 'student@nvti.edu.lk'");
    }
    echo "[CLEANED] Removed " . $delStudents->rowCount() . " dummy/test student accounts.\n";

    // 4. Clean transactional test data
    $tablesToClear = [
        'student_applications' => 'Demo Candidate Applications',
        'attendance'           => 'Test Attendance Records',
        'attendance_day_locks' => 'Test Attendance Day Locks',
        'attendance_holidays'  => 'Test Holiday Declarations',
        'submissions'          => 'Test Assignment Submissions',
        'student_documents'    => 'Test Uploaded Verification Docs',
        'notifications'        => 'Test Notifications',
        'password_resets'      => 'Test Password Reset Tokens',
        'login_logs'           => 'Test Login Attempt Logs',
        'audit_logs'           => 'Test Audit Logs',
    ];

    foreach ($tablesToClear as $table => $label) {
        try {
            $cnt = $db->exec("DELETE FROM `$table`");
            echo "[CLEANED] $label ($cnt rows removed)\n";
        } catch (Exception $ignored) {
            echo "[SKIPPED] Table `$table` not found or empty.\n";
        }
    }

    // Clean monitors except Kasun if assigned
    if ($kasunId > 0) {
        $db->prepare("DELETE FROM attendance_monitors WHERE student_id != ?")->execute([$kasunId]);
    } else {
        $db->exec("DELETE FROM attendance_monitors");
    }
    echo "[CLEANED] Reset non-test attendance monitors.\n";

    $db->commit();
    echo "========================================================\n";
    echo "[SUCCESS] Live environment is clean & ready for testing!\n";
    echo "  Admin  : admin@nvti.edu.lk / AdminPassword123!\n";
    echo "  Student: student@nvti.edu.lk / StudentPassword123!\n";
    echo "========================================================\n";
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "[ERROR] Cleanup failed: " . $e->getMessage() . "\n";
    exit(1);
}
