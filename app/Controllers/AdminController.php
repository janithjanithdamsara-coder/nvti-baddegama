<?php
/**
 * Admin Controller
 * NVTI Baddegama National Diploma Portal
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Csrf.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Helpers/Sanitizer.php';

class AdminController {

    public function __construct() {
        Auth::requireRole('admin');
    }

    public function dashboard(): void {
        $db = Database::getConnection();

        // Stats
        $studentCount = $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
        $teacherCount = $db->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
        $courseCount  = $db->query("SELECT COUNT(*) FROM courses WHERE status = 'active'")->fetchColumn();
        $materialCount= $db->query("SELECT COUNT(*) FROM materials")->fetchColumn();
        $assignmentCount = $db->query("SELECT COUNT(*) FROM assignments")->fetchColumn();
        $submissionCount = $db->query("SELECT COUNT(*) FROM submissions")->fetchColumn();

        // Morning & Afternoon Class Sessions
        $sessions = $db->query("
            SELECT cs.*, u.name as teacher_name, sub.subject_name, sub.subject_code, c.class_name 
            FROM class_sessions cs
            JOIN users u ON cs.teacher_id = u.id
            JOIN subjects sub ON cs.subject_id = sub.id
            JOIN classes c ON cs.class_id = c.id
            ORDER BY FIELD(cs.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), cs.start_time ASC
        ")->fetchAll();

        // Pending Students
        $pendingStudents = $db->query("
            SELECT u.id, u.name, u.email, u.created_at, sp.student_code, sp.phone, c.class_name
            FROM users u
            LEFT JOIN student_profiles sp ON u.id = sp.user_id
            LEFT JOIN classes c ON sp.class_id = c.id
            WHERE u.role = 'student' AND u.status = 'pending'
            ORDER BY u.created_at DESC LIMIT 5
        ")->fetchAll();

        // Recent Audit Logs
        $recentLogs = $db->query("
            SELECT al.*, u.name as user_name 
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            ORDER BY al.created_at DESC LIMIT 6
        ")->fetchAll();

        $pageTitle = "Admin Dashboard";
        require_once __DIR__ . '/../Views/admin/dashboard.php';
    }

    public function students(): void {
        $db = Database::getConnection();
        $search = trim($_GET['search'] ?? '');
        $batchStatus = trim($_GET['batch_status'] ?? 'active'); // 'active', 'pending', or 'archived'
        $classFilter = (int)($_GET['class_id'] ?? 0);

        // Fetch all batches with student counts
        $allBatches = $db->query("
            SELECT c.*, COUNT(sp.id) as student_count
            FROM classes c
            LEFT JOIN student_profiles sp ON c.id = sp.class_id
            GROUP BY c.id
            ORDER BY c.id DESC
        ")->fetchAll();

        $activeBatches = array_values(array_filter($allBatches, fn($b) => $b['status'] === 'active'));
        $archivedBatches = array_values(array_filter($allBatches, fn($b) => $b['status'] === 'archived'));

        $pendingStudentsCount = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'student' AND status = 'pending'")->fetchColumn();

        $activeStudentsCount = 0;
        foreach ($activeBatches as $ab) {
            $activeStudentsCount += (int)$ab['student_count'];
        }

        $archivedStudentsCount = 0;
        foreach ($archivedBatches as $arb) {
            $archivedStudentsCount += (int)$arb['student_count'];
        }

        $currentActiveBatch = !empty($activeBatches) ? $activeBatches[0] : null;

        // Query students based on batch status tab and optional class filter
        $sql = "
            SELECT u.id, u.name, u.email, u.status, u.created_at, 
                   sp.student_code, sp.mis_number, sp.phone, sp.nic, sp.class_id,
                   c.class_name, c.status as class_status, c.academic_year, 
                   co.course_name
            FROM users u
            LEFT JOIN student_profiles sp ON u.id = sp.user_id
            LEFT JOIN classes c ON sp.class_id = c.id
            LEFT JOIN courses co ON sp.course_id = co.id
            WHERE u.role = 'student'
        ";
        $params = [];

        if ($batchStatus === 'archived') {
            $sql .= " AND c.status = 'archived'";
        } elseif ($batchStatus === 'pending') {
            $sql .= " AND u.status = 'pending'";
        } else {
            $sql .= " AND (c.status = 'active' OR c.status IS NULL)";
        }

        if ($classFilter > 0) {
            $sql .= " AND c.id = ?";
            $params[] = $classFilter;
        }

        if (!empty($search)) {
            $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR sp.student_code LIKE ? OR sp.nic LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY u.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $students = $stmt->fetchAll();

        // Get courses and classes for modals
        $courses = $db->query("SELECT * FROM courses WHERE status = 'active' ORDER BY id ASC")->fetchAll();

        $pageTitle = "Student Management";
        require_once __DIR__ . '/../Views/admin/students.php';
    }

    public function createBatch(): void {
        Csrf::checkOrAbort();
        $className = trim($_POST['class_name'] ?? '');
        $academicYear = trim($_POST['academic_year'] ?? '');
        $courseId = (int)($_POST['course_id'] ?? 1);
        $setActive = !empty($_POST['set_active']);
        $archivePrevious = !empty($_POST['archive_previous']);

        if (empty($className)) {
            Flash::error('Batch / Class name is required.');
            header('Location: index.php?page=admin_students');
            exit;
        }

        $db = Database::getConnection();

        // If requested to archive previous active batches
        if ($archivePrevious) {
            $oldIds = $db->query("SELECT id FROM classes WHERE status = 'active'")->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($oldIds)) {
                $db->exec("UPDATE classes SET status = 'archived' WHERE status = 'active'");
                // Suspend accounts and disable monitors for those archived batches
                $placeholders = implode(',', array_fill(0, count($oldIds), '?'));
                $suspStmt = $db->prepare("
                    UPDATE users u
                    JOIN student_profiles sp ON u.id = sp.user_id
                    SET u.status = 'suspended'
                    WHERE sp.class_id IN ($placeholders) AND u.role = 'student'
                ");
                $suspStmt->execute($oldIds);

                $monStmt = $db->prepare("UPDATE attendance_monitors SET status = 'disabled' WHERE class_id IN ($placeholders)");
                $monStmt->execute($oldIds);
            }
        }

        $status = $setActive ? 'active' : 'archived';
        $stmt = $db->prepare("INSERT INTO classes (course_id, class_name, academic_year, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$courseId, $className, $academicYear ?: (date('Y') . '/' . (date('Y') + 1)), $status]);
        $newBatchId = (int)$db->lastInsertId();

        // Automatically link active curriculum subjects to this new batch
        require_once __DIR__ . '/../Helpers/AttendanceManager.php';
        AttendanceManager::syncBatchSubjects($db, $newBatchId);

        Auth::logAudit(Auth::id(), 'CREATE_BATCH', "Created new batch: $className (ID: $newBatchId, Status: $status)");
        Flash::success("New academic batch \"$className\" successfully created!");
        header('Location: index.php?page=admin_students&batch_status=' . $status . '&class_id=' . $newBatchId);
        exit;
    }

    public function archiveBatch(): void {
        Csrf::checkOrAbort();
        $classId = (int)($_POST['class_id'] ?? 0);

        if ($classId > 0) {
            $db = Database::getConnection();
            $batch = $db->prepare("SELECT * FROM classes WHERE id = ?");
            $batch->execute([$classId]);
            $b = $batch->fetch();

            if ($b) {
                // 1. Mark batch as archived
                $stmt = $db->prepare("UPDATE classes SET status = 'archived' WHERE id = ?");
                $stmt->execute([$classId]);

                // 2. Automatically suspend all student accounts in this graduated batch so they cannot log into active LMS
                $suspendStmt = $db->prepare("
                    UPDATE users u
                    JOIN student_profiles sp ON u.id = sp.user_id
                    SET u.status = 'suspended'
                    WHERE sp.class_id = ? AND u.role = 'student'
                ");
                $suspendStmt->execute([$classId]);
                $suspendedCount = $suspendStmt->rowCount();

                // 3. Disable any active attendance monitors for this archived batch
                $db->prepare("UPDATE attendance_monitors SET status = 'disabled' WHERE class_id = ?")->execute([$classId]);

                Auth::logAudit(Auth::id(), 'ARCHIVE_BATCH', "Archived batch ID $classId: {$b['class_name']}. Suspended $suspendedCount student accounts and disabled monitors.");
                Flash::success("Batch \"{$b['class_name']}\" moved to Old Batches. {$suspendedCount} student account(s) have been suspended to prevent active LMS login. All student records, attendance, and documents are 100% preserved.");
            }
        }
        header('Location: index.php?page=admin_students&batch_status=archived');
        exit;
    }

    public function restoreBatch(): void {
        Csrf::checkOrAbort();
        $classId = (int)($_POST['class_id'] ?? 0);

        if ($classId > 0) {
            $db = Database::getConnection();
            $stmt = $db->prepare("UPDATE classes SET status = 'active' WHERE id = ?");
            $stmt->execute([$classId]);

            // Reactivate students in this batch
            $reactivateStmt = $db->prepare("
                UPDATE users u
                JOIN student_profiles sp ON u.id = sp.user_id
                SET u.status = 'active'
                WHERE sp.class_id = ? AND u.role = 'student'
            ");
            $reactivateStmt->execute([$classId]);
            $reactivatedCount = $reactivateStmt->rowCount();

            Auth::logAudit(Auth::id(), 'RESTORE_BATCH', "Restored batch ID $classId to active. Reactivated $reactivatedCount students.");
            Flash::success("Batch restored to active status and {$reactivatedCount} student accounts have been reactivated.");
        }
        header('Location: index.php?page=admin_students&batch_status=active');
        exit;
    }

    public function createStudent(): void {
        Csrf::checkOrAbort();
        require_once __DIR__ . '/../Helpers/AttendanceManager.php';
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $nic = strtoupper(trim($_POST['nic'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $requestedClassId = (int)($_POST['class_id'] ?? 0);
        $courseId = (int)($_POST['course_id'] ?? 1);
        $tempPassword = $_POST['password'] ?? 'nvti1234';

        if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::error('Valid student full name and email are required.');
            header('Location: index.php?page=admin_students');
            exit;
        }

        $db = Database::getConnection();

        // Ensure valid active batch (fallback to active batch, never archived ID 1)
        $classId = 0;
        if ($requestedClassId > 0) {
            $clsChk = $db->prepare("SELECT id FROM classes WHERE id = ? AND status = 'active' LIMIT 1");
            $clsChk->execute([$requestedClassId]);
            $classId = (int)$clsChk->fetchColumn();
        }
        if ($classId <= 0) {
            $activeBatch = AttendanceManager::ensureActiveBatch($db, $courseId);
            $classId = (int)$activeBatch['id'];
        }

        // Check if email already exists
        $check = $db->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            Flash::error('A user with this email already exists.');
            header('Location: index.php?page=admin_students');
            exit;
        }

        try {
            $db->beginTransaction();
            $hash = password_hash($tempPassword, PASSWORD_BCRYPT);
            $uStmt = $db->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'student', 'active')");
            $uStmt->execute([$name, $email, $hash]);
            $userId = (int)$db->lastInsertId();

            $studentCode = 'NVTI-ST-' . date('Y') . '-' . str_pad($userId, 3, '0', STR_PAD_LEFT);
            $misNumber = 'MIS/ICT/' . date('Y') . '/' . str_pad($userId, 3, '0', STR_PAD_LEFT);

            $pStmt = $db->prepare("
                INSERT INTO student_profiles (user_id, student_code, mis_number, phone, nic, course_id, class_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $pStmt->execute([$userId, $studentCode, $misNumber, $phone, $nic, $courseId, $classId]);

            if ($classId) {
                $eStmt = $db->prepare("INSERT INTO enrollments (student_id, class_id, status) VALUES (?, ?, 'active')");
                $eStmt->execute([$userId, $classId]);
            }

            Auth::logAudit(Auth::id(), 'ADMIN_CREATE_STUDENT', "Admin registered student $name ($studentCode) in batch ID $classId");
            $db->commit();

            Flash::success("Student \"$name\" ($studentCode) registered successfully.");
        } catch (\Exception $e) {
            $db->rollBack();
            Flash::error("Failed to register student: " . $e->getMessage());
        }

        header('Location: index.php?page=admin_students&class_id=' . $classId);
        exit;
    }

    public function updateStudentStatus(): void {
        Csrf::checkOrAbort();
        $id = (int)($_POST['student_id'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        $redirectTab = trim($_POST['redirect_tab'] ?? 'active');

        if (in_array($status, ['active', 'pending', 'suspended'], true) && $id > 0) {
            $db = Database::getConnection();

            // Fetch student info before updating so we know if they are being approved
            $infoStmt = $db->prepare("
                SELECT u.id, u.name, u.email, u.status as prev_status,
                       sp.student_code, sp.mis_number, sp.class_id,
                       c.class_name, co.course_name
                FROM users u
                LEFT JOIN student_profiles sp ON u.id = sp.user_id
                LEFT JOIN classes c ON sp.class_id = c.id
                LEFT JOIN courses co ON sp.course_id = co.id
                WHERE u.id = ? AND u.role = 'student'
                LIMIT 1
            ");
            $infoStmt->execute([$id]);
            $student = $infoStmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'student'");
                $stmt->execute([$status, $id]);

                Auth::logAudit(Auth::id(), 'UPDATE_STUDENT_STATUS', "Changed student ID $id ({$student['name']}) status to $status");

                // If approving/activating a pending or suspended student -> send official confirmation email!
                if ($status === 'active' && $student['prev_status'] !== 'active') {
                    // Ensure enrollment is active
                    if (!empty($student['class_id'])) {
                        $db->prepare("UPDATE enrollments SET status = 'active' WHERE student_id = ? AND class_id = ?")
                           ->execute([$id, $student['class_id']]);
                    }

                    require_once __DIR__ . '/../Helpers/Mailer.php';
                    $loginUrl = BASE_URL . 'index.php?page=login';
                    $sName = htmlspecialchars($student['name']);
                    $sEmail = $student['email'];
                    $sCode = htmlspecialchars($student['student_code'] ?? 'NVTI-ST');
                    $sMis = htmlspecialchars($student['mis_number'] ?? 'Assigned');
                    $sBatch = htmlspecialchars($student['class_name'] ?? 'NVTI Baddegama ICT L5');
                    $sCourse = htmlspecialchars($student['course_name'] ?? 'National Diploma in ICT (NVQ Level 5)');

                    $subject = "Account Approved & Course Enrollment Confirmed - NVTI Baddegama";
                    $htmlBody = "
                    <div style='background-color: #f8fafc; padding: 30px 15px; font-family: \"Plus Jakarta Sans\", Arial, sans-serif;'>
                      <div style='max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 14px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(0,0,0,0.06);'>
                        <div style='background: linear-gradient(135deg, #7A1418 0%, #991b1b 100%); padding: 26px 28px; text-align: center; color: #ffffff;'>
                          <div style='display: inline-block; background: rgba(255,255,255,0.2); padding: 4px 14px; border-radius: 999px; font-size: 12px; font-weight: bold; letter-spacing: 0.5px; margin-bottom: 10px;'>NVTI BADDEGAMA &bull; VTA</div>
                          <h2 style='margin: 0; font-size: 21px; font-weight: 700;'>Enrollment Verified &amp; Account Approved!</h2>
                        </div>
                        <div style='padding: 28px; color: #334155; font-size: 14px; line-height: 1.6;'>
                          <p style='margin-top: 0;'>Dear <strong>{$sName}</strong>,</p>
                          <p>Great news! Your registration for the <strong>{$sCourse}</strong> has been verified and approved by the NVTI Baddegama Administration. Your student portal account is now <strong>ACTIVE</strong>.</p>
                          <div style='background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #7A1418; border-radius: 8px; padding: 14px 16px; margin: 18px 0;'>
                            <div style='margin-bottom: 6px;'><strong>Student Code:</strong> <span style='font-family: monospace; color: #7A1418;'>{$sCode}</span></div>
                            <div style='margin-bottom: 6px;'><strong>MIS Number:</strong> <span style='font-family: monospace;'>{$sMis}</span></div>
                            <div style='margin-bottom: 6px;'><strong>Batch:</strong> {$sBatch}</div>
                            <div><strong>Login Email:</strong> {$sEmail}</div>
                          </div>
                          <p>You can now sign in to access your study materials, assignments, timetable, and attendance records:</p>
                          <div style='text-align: center; margin: 24px 0;'>
                            <a href='{$loginUrl}' style='display: inline-block; background: #7A1418; color: #ffffff; text-decoration: none; font-weight: 700; padding: 12px 28px; border-radius: 999px; font-size: 14px;'>Sign In to Student Portal &rarr;</a>
                          </div>
                          <p style='color: #64748b; font-size: 12px; margin-bottom: 0;'>If you have any questions, please contact the NVTI Baddegama ICT Department.</p>
                        </div>
                      </div>
                    </div>";

                    $mailRes = Mailer::send($sEmail, $subject, $htmlBody, $student['name']);
                    if (!empty($mailRes['success'])) {
                        Flash::success("Student <strong>{$sName}</strong> has been confirmed &amp; approved! A confirmation email has been sent to <strong>{$sEmail}</strong>.");
                    } else {
                        Flash::warning("Student <strong>{$sName}</strong> has been approved and can now sign in, but the email notification failed: " . htmlspecialchars($mailRes['message'] ?? 'SMTP error'));
                    }
                } else {
                    Flash::success("Student status updated to " . ucfirst($status) . ".");
                }
            }
        }
        $validTab = in_array($redirectTab, ['active', 'pending', 'archived'], true) ? $redirectTab : 'active';
        header('Location: index.php?page=admin_students&batch_status=' . $validTab);
        exit;
    }

    public function deleteStudent(): void {
        Csrf::checkOrAbort();
        $id = (int)($_POST['student_id'] ?? 0);

        if ($id > 0) {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
            $stmt->execute([$id]);

            Auth::logAudit(Auth::id(), 'DELETE_STUDENT', "Deleted student account ID $id");
            Flash::success("Student account removed.");
        }
        header('Location: index.php?page=admin_students');
        exit;
    }

    /**
     * Move a student from an Old/Archived batch into the current Active batch
     */
    public function moveStudentToBatch(): void {
        Csrf::checkOrAbort();
        require_once __DIR__ . '/../Helpers/AttendanceManager.php';
        $studentId = (int)($_POST['student_id'] ?? 0);
        $targetClassId = (int)($_POST['class_id'] ?? 0);

        if ($studentId > 0) {
            $db = Database::getConnection();
            if ($targetClassId <= 0) {
                $activeBatch = AttendanceManager::ensureActiveBatch($db, 1);
                $targetClassId = (int)$activeBatch['id'];
            }

            // Update student_profiles class_id
            $db->prepare("UPDATE student_profiles SET class_id = ? WHERE user_id = ?")
               ->execute([$targetClassId, $studentId]);

            // Ensure active enrollment in target batch
            $enrChk = $db->prepare("SELECT id FROM enrollments WHERE student_id = ? AND class_id = ?");
            $enrChk->execute([$studentId, $targetClassId]);
            if ($enrChk->fetch()) {
                $db->prepare("UPDATE enrollments SET status = 'active' WHERE student_id = ? AND class_id = ?")
                   ->execute([$studentId, $targetClassId]);
            } else {
                $db->prepare("INSERT INTO enrollments (student_id, class_id, status) VALUES (?, ?, 'active')")
                   ->execute([$studentId, $targetClassId]);
            }

            // Ensure user account is active
            $db->prepare("UPDATE users SET status = 'active' WHERE id = ? AND role = 'student'")
               ->execute([$studentId]);

            Auth::logAudit(Auth::id(), 'MOVE_STUDENT_BATCH', "Moved student ID $studentId to active batch ID $targetClassId");
            Flash::success("Student has been moved to the Active Batch and activated.");
        }

        header('Location: index.php?page=admin_students&batch_status=active');
        exit;
    }

    /**
     * Candidate Online Applications Hub
     */
    public function applications(): void {
        Auth::requireRole(['admin']);
        $db = Database::getConnection();

        $search       = trim($_GET['search'] ?? '');
        $statusFilter = trim($_GET['status'] ?? 'all');
        $courseFilter = trim($_GET['course'] ?? 'all');
        $alFilter     = trim($_GET['al'] ?? 'all');
        $olFilter     = trim($_GET['ol'] ?? 'all');

        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(a.full_name LIKE ? OR a.nic LIKE ? OR a.student_id LIKE ? OR a.contact_no LIKE ? OR a.whatsapp_no LIKE ?)";
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if ($statusFilter !== 'all' && in_array($statusFilter, ['pending', 'called_for_interview', 'selected', 'enrolled', 'rejected'])) {
            $where[] = "a.status = ?";
            $params[] = $statusFilter;
        }

        if ($courseFilter !== 'all') {
            $where[] = "(a.course_option_one LIKE ? OR a.course_option_two LIKE ?)";
            $term = "%{$courseFilter}%";
            $params[] = $term;
            $params[] = $term;
        }

        if ($alFilter !== 'all' && in_array($alFilter, ['science', 'commerce', 'arts', 'tech', 'other'])) {
            $where[] = "a.al_category = ?";
            $params[] = $alFilter;
        }

        if ($olFilter === 'passed') {
            $where[] = "a.ol_pass_status = 'Yes'";
        } elseif ($olFilter === 'failed') {
            $where[] = "a.ol_pass_status = 'No'";
        }

        $whereSql = implode(' AND ', $where);

        $stmt = $db->prepare("
            SELECT a.*, u.email as enrolled_email
            FROM student_applications a
            LEFT JOIN users u ON a.enrolled_user_id = u.id
            WHERE {$whereSql}
            ORDER BY a.application_date DESC
        ");
        $stmt->execute($params);
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Compute summary counts
        $totalApps     = (int)$db->query("SELECT COUNT(*) FROM student_applications")->fetchColumn();
        $pendingApps   = (int)$db->query("SELECT COUNT(*) FROM student_applications WHERE status = 'pending'")->fetchColumn();
        $interviewApps = (int)$db->query("SELECT COUNT(*) FROM student_applications WHERE status = 'called_for_interview'")->fetchColumn();
        $selectedApps  = (int)$db->query("SELECT COUNT(*) FROM student_applications WHERE status = 'selected'")->fetchColumn();
        $enrolledApps  = (int)$db->query("SELECT COUNT(*) FROM student_applications WHERE status = 'enrolled'")->fetchColumn();
        $ictApps       = (int)$db->query("SELECT COUNT(*) FROM student_applications WHERE course_option_one LIKE '%ICT%' OR course_option_two LIKE '%ICT%'")->fetchColumn();

        // Active batches for enrollment dropdown
        $batches = $db->query("SELECT id, class_name, academic_year FROM classes WHERE status = 'active' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = "Online Candidate Applications Hub";
        require_once __DIR__ . '/../Views/admin/applications.php';
    }

    /**
     * Sync Applications from NVTI Baddegama Website (REST API via cURL)
     */
    public function syncApplications(): void {
        Auth::requireRole(['admin']);
        Csrf::checkOrAbort();
        $db = Database::getConnection();

        $apiUrl = NVTI_MAIN_SITE_API_URL;
        $apiKey = NVTI_MAIN_SITE_API_KEY;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-API-KEY: {$apiKey}",
            "Accept: application/json"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, "NVTI-Baddegama-LMS-Sync/1.0");

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || !empty($curlError)) {
            Flash::warning("Could not reach remote server ($apiUrl). Error: " . ($curlError ?: 'Connection timed out') . ". Once get_applications.php is uploaded to the cPanel hosting, live sync will populate instantly.");
            header('Location: index.php?page=admin_applications');
            exit;
        }

        $json = json_decode($response, true);
        if (!$json || ($json['status'] ?? '') !== 'success' || !isset($json['data'])) {
            $msg = $json['message'] ?? 'Invalid JSON response from remote website';
            Flash::warning("Remote API response (HTTP $httpCode): $msg");
            header('Location: index.php?page=admin_applications');
            exit;
        }

        $records = $json['data'];
        $syncedNew = 0;
        $syncedUpdated = 0;

        $upsertStmt = $db->prepare("
            INSERT INTO student_applications 
            (external_id, student_id, full_name, nic, address, dob, contact_no, whatsapp_no, 
             ol_pass_status, ol_english_grade, ol_maths_grade, ol_science_grade, 
             al_category, course_option_one, course_option_two, application_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                full_name = VALUES(full_name),
                address = VALUES(address),
                contact_no = VALUES(contact_no),
                whatsapp_no = VALUES(whatsapp_no),
                course_option_one = VALUES(course_option_one),
                course_option_two = VALUES(course_option_two)
        ");

        foreach ($records as $r) {
            $externalId = !empty($r['external_id']) ? (int)$r['external_id'] : (!empty($r['id']) ? (int)$r['id'] : null);
            $studentId  = trim($r['Student_id'] ?? $r['student_id'] ?? ('APP-' . substr(md5($r['nic'] ?? uniqid()), 0, 8)));
            $fullName   = trim($r['full_name'] ?? '');
            $nic        = strtoupper(trim($r['nic'] ?? ''));
            $address    = trim($r['address'] ?? '');
            $dob        = !empty($r['dob']) ? trim($r['dob']) : null;
            $contactNo  = trim($r['contact_no'] ?? '');
            $whatsappNo = trim($r['whatsapp_no'] ?? $contactNo);
            $olStatus   = (strcasecmp($r['ol_pass_status'] ?? '', 'No') === 0) ? 'No' : 'Yes';
            $olEng      = strtoupper(trim($r['ol_english_grade'] ?? ''));
            $olMath     = strtoupper(trim($r['ol_maths_grade'] ?? ''));
            $olSci      = strtoupper(trim($r['ol_science_grade'] ?? ''));
            $alCat      = strtolower(trim($r['al_category'] ?? 'tech'));
            if (!in_array($alCat, ['science', 'commerce', 'arts', 'tech', 'other'])) {
                $alCat = 'tech';
            }
            $opt1       = trim($r['course_option_one'] ?? 'National Diploma in ICT (NVQ Level 5)');
            $opt2       = trim($r['course_option_two'] ?? '');
            $appDate    = !empty($r['application_date']) ? trim($r['application_date']) : date('Y-m-d H:i:s');

            if (!empty($nic) && !empty($fullName)) {
                $upsertStmt->execute([
                    $externalId, $studentId, $fullName, $nic, $address, $dob,
                    $contactNo, $whatsappNo, $olStatus, $olEng, $olMath, $olSci,
                    $alCat, $opt1, $opt2, $appDate
                ]);
                if ($upsertStmt->rowCount() === 1) {
                    $syncedNew++;
                } else {
                    $syncedUpdated++;
                }
            }
        }

        Auth::logAudit(Auth::id(), 'SYNC_APPLICATIONS', "Synced candidate applications from main site. New: $syncedNew, Updated: $syncedUpdated");
        Flash::success("Sync successful! Processed " . count($records) . " applications from NVTI main site ($syncedNew new candidates added, $syncedUpdated refreshed).");
        header('Location: index.php?page=admin_applications');
        exit;
    }

    /**
     * Update Candidate Application Status
     */
    public function updateApplicationStatus(): void {
        Auth::requireRole(['admin']);
        Csrf::checkOrAbort();
        $db = Database::getConnection();

        $appId  = (int)($_POST['application_id'] ?? 0);
        $status = trim($_POST['status'] ?? 'pending');
        $notes  = trim($_POST['admin_notes'] ?? '');

        if ($appId > 0 && in_array($status, ['pending', 'called_for_interview', 'selected', 'enrolled', 'rejected'])) {
            $stmt = $db->prepare("UPDATE student_applications SET status = ?, admin_notes = ? WHERE id = ?");
            $stmt->execute([$status, $notes, $appId]);

            Auth::logAudit(Auth::id(), 'UPDATE_APPLICATION_STATUS', "Updated candidate application ID $appId status to $status");
            Flash::success("Application status updated to " . ucfirst(str_replace('_', ' ', $status)) . ".");
        }

        header('Location: index.php?page=admin_applications');
        exit;
    }

    /**
     * 1-Click Convert Candidate to Enrolled Student in Batch
     */
    public function enrollApplicant(): void {
        Auth::requireRole(['admin']);
        Csrf::checkOrAbort();
        $db = Database::getConnection();

        $appId   = (int)($_POST['application_id'] ?? 0);
        $classId = (int)($_POST['class_id'] ?? 0);
        if ($classId <= 0) {
            require_once __DIR__ . '/../Helpers/AttendanceManager.php';
            $classId = AttendanceManager::getActiveClassId();
        }
        $courseId = 1;

        if ($appId <= 0) {
            Flash::error("Invalid candidate application.");
            header('Location: index.php?page=admin_applications');
            exit;
        }

        $appStmt = $db->prepare("SELECT * FROM student_applications WHERE id = ?");
        $appStmt->execute([$appId]);
        $app = $appStmt->fetch(PDO::FETCH_ASSOC);

        if (!$app) {
            Flash::error("Candidate record not found.");
            header('Location: index.php?page=admin_applications');
            exit;
        }

        if ($app['status'] === 'enrolled' && !empty($app['enrolled_user_id'])) {
            Flash::warning("This candidate has already been enrolled as a student (User ID #{$app['enrolled_user_id']}).");
            header('Location: index.php?page=admin_applications');
            exit;
        }

        // Check if student with this NIC already exists in users/profiles
        $checkNic = $db->prepare("SELECT user_id FROM student_profiles WHERE nic = ?");
        $checkNic->execute([$app['nic']]);
        $existingProfile = $checkNic->fetch(PDO::FETCH_ASSOC);

        if ($existingProfile) {
            $userId = (int)$existingProfile['user_id'];
            $db->prepare("UPDATE student_applications SET status = 'enrolled', is_processed = 1, enrolled_user_id = ? WHERE id = ?")
               ->execute([$userId, $appId]);
            Flash::info("Student with NIC {$app['nic']} already exists in LMS. Application has been linked to User #{$userId}.");
            header('Location: index.php?page=admin_applications');
            exit;
        }

        // Generate student email: e.g. student_code or sanitized firstname.nic@nvti.edu.lk
        $sanitizedName = strtolower(preg_replace('/[^a-zA-Z]/', '', explode(' ', $app['full_name'])[0]));
        $email = $sanitizedName . '.' . substr($app['nic'], -4) . '@nvti.edu.lk';

        // Check uniqueness of generated email
        $chkEmail = $db->prepare("SELECT id FROM users WHERE email = ?");
        $chkEmail->execute([$email]);
        if ($chkEmail->fetch()) {
            $email = $sanitizedName . '.' . $app['nic'] . '@nvti.edu.lk';
        }

        // Generate temporary password
        $rawPassword = 'Nvti@' . substr($app['nic'], -4);
        $passwordHash = password_hash($rawPassword, PASSWORD_BCRYPT);

        // Insert into users
        $insUser = $db->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'student', 'active')");
        $insUser->execute([$app['full_name'], $email, $passwordHash]);
        $userId = (int)$db->lastInsertId();

        // Student code & MIS
        $studentCode = $app['student_id'] ?: ('STU-' . date('Y') . '-' . str_pad($userId, 4, '0', STR_PAD_LEFT));

        // Insert into student_profiles
        $insProf = $db->prepare("
            INSERT INTO student_profiles 
            (user_id, student_code, mis_number, phone, whatsapp_number, nic, address, course_id, class_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insProf->execute([
            $userId, $studentCode, $studentCode, $app['contact_no'], $app['whatsapp_no'],
            $app['nic'], $app['address'], $courseId, $classId
        ]);

        // Insert enrollment
        $insEnr = $db->prepare("INSERT INTO enrollments (student_id, class_id, status) VALUES (?, ?, 'active')");
        $insEnr->execute([$userId, $classId]);

        // Update application record
        $db->prepare("UPDATE student_applications SET status = 'enrolled', is_processed = 1, enrolled_user_id = ? WHERE id = ?")
           ->execute([$userId, $appId]);

        // Fetch batch name
        $bStmt = $db->prepare("SELECT class_name FROM classes WHERE id = ?");
        $bStmt->execute([$classId]);
        $batchName = $bStmt->fetchColumn() ?: 'Active Batch';

        Auth::logAudit(Auth::id(), 'ENROLL_APPLICANT', "Enrolled applicant {$app['full_name']} (ID $appId) as student user #$userId in $batchName");

        Flash::success("Candidate {$app['full_name']} enrolled successfully to {$batchName}! Login Email: <strong>{$email}</strong> | Temp Password: <strong>{$rawPassword}</strong>");
        header('Location: index.php?page=admin_applications');
        exit;
    }

    /**
     * Manual Application Entry (For walk-in candidates submitting paper forms at NVTI Baddegama)
     */
    public function saveApplication(): void {
        Auth::requireRole(['admin']);
        Csrf::checkOrAbort();
        $db = Database::getConnection();

        $fullName   = trim($_POST['full_name'] ?? '');
        $nic        = strtoupper(trim($_POST['nic'] ?? ''));
        $address    = trim($_POST['address'] ?? '');
        $dob        = !empty($_POST['dob']) ? trim($_POST['dob']) : null;
        $contactNo  = trim($_POST['contact_no'] ?? '');
        $whatsappNo = trim($_POST['whatsapp_no'] ?? $contactNo);
        $olStatus   = ($_POST['ol_pass_status'] ?? 'Yes') === 'No' ? 'No' : 'Yes';
        $olEng      = strtoupper(trim($_POST['ol_english_grade'] ?? ''));
        $olMath     = strtoupper(trim($_POST['ol_maths_grade'] ?? ''));
        $olSci      = strtoupper(trim($_POST['ol_science_grade'] ?? ''));
        $alCat      = trim($_POST['al_category'] ?? 'tech');
        $opt1       = trim($_POST['course_option_one'] ?? 'National Diploma in ICT (NVQ Level 5)');
        $opt2       = trim($_POST['course_option_two'] ?? '');
        $studentId  = 'APP-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));

        if (empty($fullName) || empty($nic) || empty($contactNo)) {
            Flash::error("Candidate Full Name, NIC, and Contact Number are required.");
            header('Location: index.php?page=admin_applications');
            exit;
        }

        // Check NIC duplication
        $check = $db->prepare("SELECT id FROM student_applications WHERE nic = ?");
        $check->execute([$nic]);
        if ($check->fetch()) {
            Flash::error("An application with NIC $nic has already been registered in the system.");
            header('Location: index.php?page=admin_applications');
            exit;
        }

        $ins = $db->prepare("
            INSERT INTO student_applications 
            (student_id, full_name, nic, address, dob, contact_no, whatsapp_no, ol_pass_status, 
             ol_english_grade, ol_maths_grade, ol_science_grade, al_category, course_option_one, course_option_two, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $ins->execute([
            $studentId, $fullName, $nic, $address, $dob, $contactNo, $whatsappNo,
            $olStatus, $olEng, $olMath, $olSci, $alCat, $opt1, $opt2
        ]);

        Auth::logAudit(Auth::id(), 'MANUAL_CREATE_APPLICATION', "Registered candidate application for $fullName ($nic)");
        Flash::success("Candidate application for '$fullName' has been registered successfully with ID $studentId.");
        header('Location: index.php?page=admin_applications');
        exit;
    }

    /**
     * Export Applications CSV
     */
    public function exportApplicationsCsv(): void {
        Auth::requireRole(['admin']);
        $db = Database::getConnection();

        $apps = $db->query("
            SELECT student_id, full_name, nic, contact_no, whatsapp_no, dob, 
                   ol_pass_status, ol_english_grade, ol_maths_grade, ol_science_grade, 
                   al_category, course_option_one, course_option_two, status, application_date
            FROM student_applications 
            ORDER BY application_date DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $filename = "NVTI_Baddegama_Applications_" . date('Y-m-d') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($out, [
            'Ref ID', 'Full Name', 'NIC', 'Contact No', 'WhatsApp No', 'DOB',
            'O/L Passed', 'O/L English', 'O/L Maths', 'O/L Science',
            'A/L Stream', 'Course Option 1', 'Course Option 2', 'Status', 'Applied Date'
        ]);

        foreach ($apps as $a) {
            fputcsv($out, [
                $a['student_id'],
                $a['full_name'],
                $a['nic'],
                $a['contact_no'],
                $a['whatsapp_no'],
                $a['dob'],
                $a['ol_pass_status'],
                $a['ol_english_grade'],
                $a['ol_maths_grade'],
                $a['ol_science_grade'],
                strtoupper($a['al_category']),
                $a['course_option_one'],
                $a['course_option_two'],
                ucfirst(str_replace('_', ' ', $a['status'])),
                $a['application_date']
            ]);
        }
        fclose($out);
        exit;
    }

    public function teachers(): void {
        $db = Database::getConnection();
        $teachers = $db->query("
            SELECT u.id, u.name, u.email, u.status, u.profile_image, tp.teacher_code, tp.phone, tp.qualification, tp.specialization, tp.preferred_slot
            FROM users u
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
            WHERE u.role = 'teacher'
            ORDER BY u.id ASC
        ")->fetchAll();

        // Get subjects assigned to each teacher
        foreach ($teachers as &$t) {
            $stmt = $db->prepare("
                SELECT s.subject_name, s.subject_code, c.class_name
                FROM teacher_assignments ta
                JOIN class_subjects cs ON ta.class_subject_id = cs.id
                JOIN subjects s ON cs.subject_id = s.id
                JOIN classes c ON cs.class_id = c.id
                WHERE ta.teacher_id = ?
            ");
            $stmt->execute([$t['id']]);
            $t['assigned_subjects'] = $stmt->fetchAll();
        }

        $allSubjects = $db->query("SELECT * FROM subjects WHERE status = 'active'")->fetchAll();
        $allClasses = $db->query("SELECT * FROM classes WHERE status = 'active'")->fetchAll();
        $adminUsers = $db->query("
            SELECT id, name, email, role, status, profile_image, created_at
            FROM users
            WHERE role = 'admin'
            ORDER BY id ASC
        ")->fetchAll();

        $pageTitle = "Faculty & Administrator Accounts";
        require_once __DIR__ . '/../Views/admin/teachers.php';
    }

    public function createAdmin(): void {
        Csrf::checkOrAbort();
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? 'nvtiadmin123';

        if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::error("Full name and a valid email address are required.");
            header('Location: index.php?page=admin_teachers');
            exit;
        }

        $db = Database::getConnection();
        $check = $db->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            Flash::error("An account with this email address already exists.");
            header('Location: index.php?page=admin_teachers');
            exit;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'admin', 'active', NOW())");
        $stmt->execute([$name, $email, $hash]);

        Auth::logAudit(Auth::id(), 'CREATE_ADMIN', "Created administrator account: $name ($email)");
        Flash::success("Administrator account for $name created successfully!");
        header('Location: index.php?page=admin_teachers');
        exit;
    }

    public function createTeacher(): void {
        Csrf::checkOrAbort();
        require_once __DIR__ . '/../Helpers/Uploader.php';

        $name           = trim($_POST['name'] ?? '');
        $email          = trim($_POST['email'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');
        $qualification  = trim($_POST['qualification'] ?? '');
        $specialization = trim($_POST['specialization'] ?? '');
        $slot           = $_POST['preferred_slot'] ?? 'Morning (09:00 - 12:00)';
        $password       = $_POST['password'] ?? 'TeacherPassword123!';

        if (empty($name) || empty($email) || empty($password)) {
            Flash::error("Name, email and password are required.");
            header('Location: index.php?page=admin_teachers');
            exit;
        }

        $profileImage = null;
        if (!empty($_FILES['profile_photo']['name'])) {
            $upload = Uploader::uploadImage($_FILES['profile_photo'], 'teachers');
            if ($upload['success']) {
                $profileImage = $upload['path'];
            } else {
                Flash::error("Photo upload error: " . $upload['error']);
                header('Location: index.php?page=admin_teachers');
                exit;
            }
        }

        $db = Database::getConnection();
        try {
            $db->beginTransaction();
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $uStmt = $db->prepare("INSERT INTO users (name, email, password, role, status, profile_image) VALUES (?, ?, ?, 'teacher', 'active', ?)");
            $uStmt->execute([$name, $email, $hash, $profileImage]);
            $userId = (int)$db->lastInsertId();

            $teacherCode = 'NVTI-TCH-' . str_pad($userId, 3, '0', STR_PAD_LEFT);
            $pStmt = $db->prepare("INSERT INTO teacher_profiles (user_id, teacher_code, phone, qualification, specialization, preferred_slot) VALUES (?, ?, ?, ?, ?, ?)");
            $pStmt->execute([$userId, $teacherCode, $phone, $qualification, $specialization, $slot]);

            $db->commit();
            Auth::logAudit(Auth::id(), 'CREATE_TEACHER', "Created Teacher $name ($teacherCode)");
            Flash::success("Teacher $name ($teacherCode) created successfully.");
        } catch (Exception $e) {
            $db->rollBack();
            Flash::error("Failed to create teacher: " . $e->getMessage());
        }
        header('Location: index.php?page=admin_teachers');
        exit;
    }

    public function updateTeacher(): void {
        Csrf::checkOrAbort();
        require_once __DIR__ . '/../Helpers/Uploader.php';

        $id             = (int)($_POST['teacher_id'] ?? 0);
        $name           = trim($_POST['name'] ?? '');
        $email          = trim($_POST['email'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');
        $qualification  = trim($_POST['qualification'] ?? '');
        $specialization = trim($_POST['specialization'] ?? '');
        $slot           = $_POST['preferred_slot'] ?? 'Morning (09:00 - 12:00)';
        $profileImage   = trim($_POST['profile_image'] ?? '');

        if ($id <= 0 || empty($name)) {
            Flash::error("Invalid instructor details.");
            header('Location: index.php?page=admin_teachers');
            exit;
        }

        $db = Database::getConnection();

        $curStmt = $db->prepare("SELECT email, profile_image FROM users WHERE id = ? AND role = 'teacher' LIMIT 1");
        $curStmt->execute([$id]);
        $existingTeacher = $curStmt->fetch(PDO::FETCH_ASSOC);
        if (!$existingTeacher) {
            Flash::error("Instructor account not found.");
            header('Location: index.php?page=admin_teachers');
            exit;
        }
        if (empty($email)) {
            $email = $existingTeacher['email'];
        }

        // Handle file upload if provided
        if (!empty($_FILES['profile_photo']['name'])) {
            $upload = Uploader::uploadImage($_FILES['profile_photo'], 'teachers');
            if ($upload['success']) {
                $profileImage = $upload['path'];
            } else {
                Flash::error("Photo upload error: " . $upload['error']);
                header('Location: index.php?page=admin_teachers');
                exit;
            }
        }

        try {
            $db->beginTransaction();
            if (!empty($profileImage)) {
                $uStmt = $db->prepare("UPDATE users SET name = ?, email = ?, profile_image = ? WHERE id = ? AND role = 'teacher'");
                $uStmt->execute([$name, $email, $profileImage, $id]);
            } else {
                $uStmt = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ? AND role = 'teacher'");
                $uStmt->execute([$name, $email, $id]);
            }

            $chkProf = $db->prepare("SELECT id FROM teacher_profiles WHERE user_id = ? LIMIT 1");
            $chkProf->execute([$id]);
            if ($chkProf->fetchColumn()) {
                $pStmt = $db->prepare("
                    UPDATE teacher_profiles 
                    SET phone = ?, qualification = ?, specialization = ?, preferred_slot = ? 
                    WHERE user_id = ?
                ");
                $pStmt->execute([$phone, $qualification, $specialization, $slot, $id]);
            } else {
                $teacherCode = 'NVTI-TCH-' . str_pad($id, 3, '0', STR_PAD_LEFT);
                $pStmt = $db->prepare("
                    INSERT INTO teacher_profiles (user_id, teacher_code, phone, qualification, specialization, preferred_slot)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $pStmt->execute([$id, $teacherCode, $phone, $qualification, $specialization, $slot]);
            }

            $db->commit();
            Auth::logAudit(Auth::id(), 'UPDATE_TEACHER', "Updated instructor details for $name (ID $id)");
            Flash::success("Instructor <strong>" . htmlspecialchars($name) . "</strong> details and profile photo updated successfully.");
        } catch (Exception $e) {
            $db->rollBack();
            Flash::error("Failed to update teacher: " . $e->getMessage());
        }
        header('Location: index.php?page=admin_teachers');
        exit;
    }

    public function timetable(): void {
        $db = Database::getConnection();
        $sessions = $db->query("
            SELECT cs.*, u.name as teacher_name, sub.subject_name, sub.subject_code, c.class_name 
            FROM class_sessions cs
            JOIN users u ON cs.teacher_id = u.id
            JOIN subjects sub ON cs.subject_id = sub.id
            JOIN classes c ON cs.class_id = c.id
            ORDER BY FIELD(cs.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), cs.start_time ASC
        ")->fetchAll();

        $teachers = $db->query("SELECT id, name FROM users WHERE role = 'teacher' AND status = 'active'")->fetchAll();
        $classes  = $db->query("SELECT id, class_name FROM classes WHERE status = 'active'")->fetchAll();
        $subjects = $db->query("SELECT id, subject_name, subject_code FROM subjects WHERE status = 'active'")->fetchAll();

        $pageTitle = "Timetable & Session Management";
        require_once __DIR__ . '/../Views/admin/timetable.php';
    }

    public function createSession(): void {
        Csrf::checkOrAbort();
        $teacherId = (int)($_POST['teacher_id'] ?? 0);
        $classId   = (int)($_POST['class_id'] ?? 0);
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $title     = trim($_POST['session_title'] ?? '');
        $day       = $_POST['day_of_week'] ?? 'Monday';
        $slotType  = $_POST['slot_preset'] ?? 'custom';
        $hall      = trim($_POST['hall_or_link'] ?? 'ICT Lab 01');

        if ($slotType === 'morning') {
            $startTime = '09:00:00';
            $endTime   = '12:00:00';
        } elseif ($slotType === 'afternoon') {
            $startTime = '13:00:00';
            $endTime   = '16:00:00';
        } else {
            $startTime = $_POST['start_time'] ?? '09:00:00';
            $endTime   = $_POST['end_time'] ?? '12:00:00';
        }

        if (empty($teacherId) || empty($classId) || empty($subjectId) || empty($title)) {
            Flash::error("Please fill all required timetable session fields.");
            header('Location: index.php?page=admin_timetable');
            exit;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO class_sessions (teacher_id, class_id, subject_id, session_title, day_of_week, start_time, end_time, hall_or_link) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$teacherId, $classId, $subjectId, $title, $day, $startTime, $endTime, $hall]);

        Auth::logAudit(Auth::id(), 'CREATE_SESSION', "Added class session: $title ($day $startTime-$endTime)");
        Flash::success("Class session scheduled successfully!");
        header('Location: index.php?page=admin_timetable');
        exit;
    }

    public function deleteSession(): void {
        Csrf::checkOrAbort();
        $id = (int)($_POST['session_id'] ?? 0);
        if ($id > 0) {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM class_sessions WHERE id = ?");
            $stmt->execute([$id]);
            Auth::logAudit(Auth::id(), 'DELETE_SESSION', "Deleted timetable session ID $id");
            Flash::success("Session deleted.");
        }
        header('Location: index.php?page=admin_timetable');
        exit;
    }

    public function courses(): void {
        $db = Database::getConnection();
        $courses  = $db->query("SELECT * FROM courses ORDER BY id DESC")->fetchAll();
        $classes  = $db->query("SELECT c.*, co.course_name FROM classes c JOIN courses co ON c.course_id = co.id ORDER BY c.id DESC")->fetchAll();
        $subjects = $db->query("SELECT * FROM subjects ORDER BY id DESC")->fetchAll();
        $curriculumModules = $db->query("SELECT * FROM curriculum_modules ORDER BY semester ASC, module_no ASC")->fetchAll();

        $pageTitle = "Courses, Batches & Curriculum Modules";
        require_once __DIR__ . '/../Views/admin/courses.php';
    }

    public function updateCurriculumModule(): void {
        Csrf::checkOrAbort();
        $id          = (int)($_POST['module_id'] ?? 0);
        $no          = (int)($_POST['module_no'] ?? 1);
        $code        = trim($_POST['module_code'] ?? '');
        $title       = trim($_POST['module_title'] ?? '');
        $semester    = (int)($_POST['semester'] ?? 1);
        $type        = $_POST['module_type'] ?? 'Technical';
        $units       = trim($_POST['related_units'] ?? '');
        $page        = trim($_POST['curriculum_page'] ?? '');
        $status      = $_POST['status'] ?? 'active';

        if ($id > 0 && !empty($code) && !empty($title)) {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                UPDATE curriculum_modules 
                SET module_no = ?, module_code = ?, module_title = ?, semester = ?, module_type = ?, related_units = ?, curriculum_page = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([$no, $code, $title, $semester, $type, $units, $page, $status, $id]);
            Auth::logAudit(Auth::id(), 'UPDATE_CBT_MODULE', "Updated CBT module $code ($title) for Semester $semester");
            Flash::success("CBT Module '$code' updated successfully.");
        } else {
            Flash::error("Failed to update module: Code and Title are required.");
        }
        header('Location: index.php?page=admin_courses');
        exit;
    }

    public function createCurriculumModule(): void {
        Csrf::checkOrAbort();
        $no          = (int)($_POST['module_no'] ?? 1);
        $code        = trim($_POST['module_code'] ?? '');
        $title       = trim($_POST['module_title'] ?? '');
        $semester    = (int)($_POST['semester'] ?? 1);
        $type        = $_POST['module_type'] ?? 'Technical';
        $units       = trim($_POST['related_units'] ?? '');
        $page        = trim($_POST['curriculum_page'] ?? '');

        if (!empty($code) && !empty($title)) {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO curriculum_modules (module_no, module_code, module_title, semester, module_type, related_units, curriculum_page, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([$no, $code, $title, $semester, $type, $units, $page]);
            Auth::logAudit(Auth::id(), 'CREATE_CBT_MODULE', "Created new CBT module $code in Semester $semester");
            Flash::success("New CBT Module '$code' added to Semester $semester.");
        } else {
            Flash::error("Module code and title are required.");
        }
        header('Location: index.php?page=admin_courses');
        exit;
    }

    public function deleteCurriculumModule(): void {
        Csrf::checkOrAbort();
        $id = (int)($_POST['module_id'] ?? 0);
        if ($id > 0) {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM curriculum_modules WHERE id = ?");
            $stmt->execute([$id]);
            Auth::logAudit(Auth::id(), 'DELETE_CBT_MODULE', "Deleted CBT module ID $id");
            Flash::success("CBT Module removed.");
        }
        header('Location: index.php?page=admin_courses');
        exit;
    }

    public function createCourse(): void {
        Csrf::checkOrAbort();
        $code  = trim($_POST['course_code'] ?? '');
        $name  = trim($_POST['course_name'] ?? '');
        $level = trim($_POST['nvq_level'] ?? 'NVQ Level 5');
        $desc  = trim($_POST['description'] ?? '');

        if ($code && $name) {
            $db = Database::getConnection();
            $stmt = $db->prepare("INSERT INTO courses (course_code, course_name, nvq_level, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$code, $name, $level, $desc]);
            Flash::success("Course $name added.");
        }
        header('Location: index.php?page=admin_courses');
        exit;
    }

    public function createSubject(): void {
        Csrf::checkOrAbort();
        $code = trim($_POST['subject_code'] ?? '');
        $name = trim($_POST['subject_name'] ?? '');
        $desc = trim($_POST['description'] ?? '');

        if ($code && $name) {
            $db = Database::getConnection();
            $stmt = $db->prepare("INSERT INTO subjects (subject_code, subject_name, description) VALUES (?, ?, ?)");
            $stmt->execute([$code, $name, $desc]);
            Flash::success("Subject $name added.");
        }
        header('Location: index.php?page=admin_courses');
        exit;
    }

    /**
     * Resolve the target batch/class ID for Admin Materials & Google Classroom Hub.
     * Defaults to the current active batch and ensures active batches have curriculum subjects synced.
     */
    private function resolveMaterialsClassId(PDO $db, ?int $requestedClassId = null): int {
        require_once __DIR__ . '/../Helpers/AttendanceManager.php';

        $classId = 0;
        $batchStatus = 'active';
        if ($requestedClassId !== null && $requestedClassId > 0) {
            $chk = $db->prepare("SELECT id, status FROM classes WHERE id = ? LIMIT 1");
            $chk->execute([$requestedClassId]);
            $row = $chk->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $classId = (int)$row['id'];
                $batchStatus = (string)($row['status'] ?? 'active');
            }
        }

        if ($classId <= 0) {
            $activeBatch = AttendanceManager::ensureActiveBatch($db, 1);
            $classId = (int)($activeBatch['id'] ?? 1);
            $batchStatus = (string)($activeBatch['status'] ?? 'active');
        }

        // Keep active batches synced with all active curriculum subjects (matching Student Portal)
        if ($classId > 0) {
            if ($batchStatus === 'active') {
                AttendanceManager::syncBatchSubjects($db, $classId);
            } else {
                $cntStmt = $db->prepare("SELECT COUNT(*) FROM class_subjects WHERE class_id = ?");
                $cntStmt->execute([$classId]);
                if ((int)$cntStmt->fetchColumn() === 0) {
                    AttendanceManager::syncBatchSubjects($db, $classId);
                }
            }
        }

        return $classId;
    }

    /**
     * Build redirect URL for admin_materials preserving selected batch and subject.
     */
    private function buildMaterialsRedirectUrl(int $classId, int $subjectId = 0): string {
        return "index.php?page=admin_materials&class_id=" . $classId . ($subjectId > 0 ? "&subject_id=" . $subjectId : "");
    }

    /**
     * Safely unlink a material file only if no remaining database rows reference it.
     */
    private function safeUnlinkMaterialFile(PDO $db, ?string $filePath): void {
        if (empty($filePath)) {
            return;
        }
        $chk = $db->prepare("SELECT COUNT(*) FROM materials WHERE file_path = ?");
        $chk->execute([$filePath]);
        if ((int)$chk->fetchColumn() === 0) {
            @unlink(MATERIAL_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filePath);
        }
    }

    public function materials(): void {
        $db = Database::getConnection();
        $subjectId = (int)($_GET['subject_id'] ?? 0);
        $tab = trim($_GET['tab'] ?? 'stream'); // 'stream', 'materials', 'assignments'

        // Fetch all categories for material uploads
        $categories = $db->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all teachers for dropdowns
        $teachers = $db->query("SELECT id, name, email FROM users WHERE role = 'teacher' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Resolve active or explicitly selected batch/class
        $requestedClassId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
        $classId = $this->resolveMaterialsClassId($db, $requestedClassId);
        $activeClassId = $classId;
        $classes = AttendanceManager::getAllBatches();
        $selectedBatch = AttendanceManager::getBatchById($classId);

        if ($subjectId > 0) {
            // ================= DETAIL / SUBJECT STREAM VIEW (Image 2) =================
            $subjStmt = $db->prepare("
                SELECT s.*, cs.id as class_subject_id, cs.class_id, c.class_name,
                       u.id as teacher_id, u.name as teacher_name, u.email as teacher_email
                FROM subjects s
                JOIN class_subjects cs ON s.id = cs.subject_id
                JOIN classes c ON cs.class_id = c.id
                LEFT JOIN teacher_assignments ta ON cs.id = ta.class_subject_id
                LEFT JOIN users u ON ta.teacher_id = u.id
                WHERE s.id = ? AND cs.class_id = ?
            ");
            $subjStmt->execute([$subjectId, $classId]);
            $currentSubject = $subjStmt->fetch(PDO::FETCH_ASSOC);

            if (!$currentSubject) {
                // If not in class_subjects, fetch pure subject info bound to current batch
                $sOnly = $db->prepare("SELECT * FROM subjects WHERE id = ?");
                $sOnly->execute([$subjectId]);
                $currentSubject = $sOnly->fetch(PDO::FETCH_ASSOC) ?: ['id' => $subjectId, 'subject_name' => 'Module', 'subject_code' => 'MOD'];
                $currentSubject['class_name'] = $selectedBatch['class_name'] ?? 'Active Batch';
                $currentSubject['teacher_name'] = 'U.G.P.K. Nishantha';
                $currentSubject['teacher_id'] = 2;
                $currentSubject['class_id'] = $classId;
            }

            // Fetch Materials for this Subject
            $matStmt = $db->prepare("
                SELECT m.*, u.name as teacher_name, c.category_name, c.icon as category_icon
                FROM materials m
                JOIN users u ON m.teacher_id = u.id
                JOIN categories c ON m.category_id = c.id
                WHERE m.subject_id = ? AND m.class_id = ?
                ORDER BY m.created_at DESC
            ");
            $matStmt->execute([$subjectId, $classId]);
            $materials = $matStmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch Assignments for this Subject
            $asgStmt = $db->prepare("
                SELECT a.*, u.name as teacher_name,
                       (SELECT COUNT(*) FROM submissions sub WHERE sub.assignment_id = a.id) as submissions_count
                FROM assignments a
                JOIN users u ON a.teacher_id = u.id
                WHERE a.subject_id = ? AND a.class_id = ?
                ORDER BY a.created_at DESC
            ");
            $asgStmt->execute([$subjectId, $classId]);
            $assignments = $asgStmt->fetchAll(PDO::FETCH_ASSOC);

            // Combine into unified Google Classroom chronological stream feed
            $streamFeed = [];
            foreach ($materials as $m) {
                $streamFeed[] = [
                    'feed_type'   => 'material',
                    'id'          => $m['id'],
                    'title'       => $m['title'],
                    'description' => $m['description'],
                    'author_name' => $m['teacher_name'],
                    'created_at'  => $m['created_at'],
                    'category'    => $m['category_name'],
                    'icon'        => $m['category_icon'] ?? 'file-earmark-text',
                    'material_type' => $m['material_type'],
                    'file_path'   => $m['file_path'],
                    'original_filename' => $m['original_filename'],
                    'external_url'=> $m['external_url'],
                    'file_size'   => $m['file_size_bytes'],
                    'is_hidden'   => (int)($m['is_hidden'] ?? 0),
                    'data'        => $m
                ];
            }
            foreach ($assignments as $a) {
                $streamFeed[] = [
                    'feed_type'   => 'assignment',
                    'id'          => $a['id'],
                    'title'       => $a['title'],
                    'description' => $a['instructions'],
                    'author_name' => $a['teacher_name'],
                    'created_at'  => $a['created_at'],
                    'deadline'    => $a['deadline'],
                    'total_marks' => $a['total_marks'],
                    'reference_file' => $a['reference_file'],
                    'submissions_count' => $a['submissions_count'],
                    'data'        => $a
                ];
            }

            // Sort stream items descending by created_at
            usort($streamFeed, function ($a, $b) {
                return strtotime($b['created_at']) <=> strtotime($a['created_at']);
            });

            $pageTitle = ($currentSubject['subject_name'] ?? 'Classroom') . " - Classroom Stream";
            require_once __DIR__ . '/../Views/admin/materials.php';

        } else {
            // ================= CLASSROOM GRID VIEW (Image 1) =================
            $classroomsStmt = $db->prepare("
                SELECT s.id as subject_id, s.subject_name, s.subject_code, s.description, s.color_theme, s.icon,
                       cs.id as class_subject_id, cs.class_id, c.class_name,
                       u.id as teacher_id, u.name as teacher_name, u.email as teacher_email,
                       (SELECT COUNT(*) FROM materials m WHERE m.subject_id = s.id AND m.class_id = cs.class_id) as materials_count,
                       (SELECT COUNT(*) FROM materials m WHERE m.subject_id = s.id AND m.class_id = cs.class_id AND m.is_hidden = 1) as hidden_materials_count,
                       (SELECT COUNT(*) FROM assignments a WHERE a.subject_id = s.id AND a.class_id = cs.class_id) as assignments_count
                FROM subjects s
                JOIN class_subjects cs ON s.id = cs.subject_id
                JOIN classes c ON cs.class_id = c.id
                LEFT JOIN teacher_assignments ta ON cs.id = ta.class_subject_id
                LEFT JOIN users u ON ta.teacher_id = u.id
                WHERE cs.class_id = ?
                ORDER BY s.id ASC
            ");
            $classroomsStmt->execute([$classId]);
            $classrooms = $classroomsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Total statistics scoped to the selected batch
            $totMatStmt = $db->prepare("SELECT COUNT(*) FROM materials WHERE class_id = ?");
            $totMatStmt->execute([$classId]);
            $totalMaterials = (int)$totMatStmt->fetchColumn();

            $totHidStmt = $db->prepare("SELECT COUNT(*) FROM materials WHERE class_id = ? AND is_hidden = 1");
            $totHidStmt->execute([$classId]);
            $totalHiddenMaterials = (int)$totHidStmt->fetchColumn();

            $totAsgStmt = $db->prepare("SELECT COUNT(*) FROM assignments WHERE class_id = ?");
            $totAsgStmt->execute([$classId]);
            $totalAssignments = (int)$totAsgStmt->fetchColumn();

            // Check other batches that have materials available to copy/import
            $srcStmt = $db->prepare("
                SELECT c.id, c.class_name, c.status,
                       (SELECT COUNT(*) FROM materials m WHERE m.class_id = c.id) as mat_count
                FROM classes c
                WHERE c.id != ?
                HAVING mat_count > 0
                ORDER BY c.id DESC
            ");
            $srcStmt->execute([$classId]);
            $sourceBatches = $srcStmt->fetchAll(PDO::FETCH_ASSOC);

            $pageTitle = "Google Classroom Hub & Subject Modules";
            require_once __DIR__ . '/../Views/admin/materials.php';
        }
    }

    /**
     * Admin Upload Material (PDF / Doc / YouTube / URL)
     */
    public function uploadMaterial(): void {
        Csrf::checkOrAbort();
        require_once __DIR__ . '/../Helpers/Uploader.php';

        $db = Database::getConnection();
        $adminId = Auth::id();

        $title        = trim($_POST['title'] ?? '');
        $description  = trim($_POST['description'] ?? '');
        $subjectId    = (int)($_POST['subject_id'] ?? 0);
        $classId      = $this->resolveMaterialsClassId($db, isset($_POST['class_id']) ? (int)$_POST['class_id'] : null);
        $categoryId   = (int)($_POST['category_id'] ?? 1);
        $teacherId    = (int)($_POST['teacher_id'] ?? $adminId);
        $materialType = $_POST['material_type'] ?? 'file';

        if (empty($title) || $subjectId <= 0) {
            Flash::error("Please provide a title and select a subject.");
            header("Location: " . $this->buildMaterialsRedirectUrl($classId, $subjectId));
            exit;
        }

        $filePath = null;
        $origName = null;
        $url = null;
        $fileSize = 0;

        if ($materialType === 'file') {
            if (empty($_FILES['file_upload']) || $_FILES['file_upload']['error'] === UPLOAD_ERR_NO_FILE) {
                Flash::error("Please choose a file to upload.");
                header("Location: " . $this->buildMaterialsRedirectUrl($classId, $subjectId));
                exit;
            }

            $uploadRes = Uploader::upload($_FILES['file_upload'], MATERIAL_UPLOAD_PATH);
            if (!$uploadRes['success']) {
                Flash::error("Upload failed: " . $uploadRes['error']);
                header("Location: " . $this->buildMaterialsRedirectUrl($classId, $subjectId));
                exit;
            }

            $filePath = $uploadRes['filename'];
            $origName = $uploadRes['original_filename'];
            $fileSize = $uploadRes['size'];
        } elseif ($materialType === 'youtube' || $materialType === 'url') {
            $url = trim($_POST['external_url'] ?? '');
            if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                Flash::error("Please provide a valid URL.");
                header("Location: " . $this->buildMaterialsRedirectUrl($classId, $subjectId));
                exit;
            }
        }

        $stmt = $db->prepare("
            INSERT INTO materials (teacher_id, class_id, subject_id, category_id, title, description, material_type, file_path, original_filename, external_url, file_size_bytes, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$teacherId, $classId, $subjectId, $categoryId, $title, $description, $materialType, $filePath, $origName, $url, $fileSize]);

        Auth::logAudit($adminId, 'ADMIN_UPLOAD_MATERIAL', "Admin uploaded material: $title for subject $subjectId (class $classId)");

        // Google Classroom style: Notify all enrolled students via email
        $subStmt = $db->prepare("SELECT subject_name FROM subjects WHERE id = ?");
        $subStmt->execute([$subjectId]);
        $subRow = $subStmt->fetch();
        $subName = $subRow['subject_name'] ?? 'Diploma Module';
        $posterName = Auth::user()['name'] ?? 'Course Administrator';
        $notified = Mailer::notifyNewMaterial($classId, $title, $subName, $posterName, $description, $materialType);

        if ($notified > 0) {
            Flash::success("Study material '{$title}' posted successfully and $notified students were notified via Email!");
        } else {
            Flash::success("Study material '{$title}' posted successfully to the classroom stream!");
        }
        header("Location: " . $this->buildMaterialsRedirectUrl($classId, $subjectId));
        exit;
    }

    /**
     * Admin Create Assignment (Coursework)
     */
    public function createAssignment(): void {
        Csrf::checkOrAbort();
        require_once __DIR__ . '/../Helpers/Uploader.php';

        $db = Database::getConnection();
        $adminId = Auth::id();

        $title        = trim($_POST['title'] ?? '');
        $instructions = trim($_POST['instructions'] ?? '');
        $subjectId    = (int)($_POST['subject_id'] ?? 0);
        $classId      = $this->resolveMaterialsClassId($db, isset($_POST['class_id']) ? (int)$_POST['class_id'] : null);
        $teacherId    = (int)($_POST['teacher_id'] ?? $adminId);
        $totalMarks   = (float)($_POST['total_marks'] ?? 100);
        $deadline     = trim($_POST['deadline'] ?? '');

        if (empty($title) || empty($instructions) || empty($deadline) || $subjectId <= 0) {
            Flash::error("Please fill in assignment title, instructions, and deadline.");
            header("Location: " . $this->buildMaterialsRedirectUrl($classId, $subjectId));
            exit;
        }

        $refFile = null;
        if (!empty($_FILES['reference_file']) && $_FILES['reference_file']['error'] === UPLOAD_ERR_OK) {
            $up = Uploader::upload($_FILES['reference_file'], ASSIGNMENT_UPLOAD_PATH);
            if ($up['success']) {
                $refFile = $up['filename'];
            }
        }

        $stmt = $db->prepare("
            INSERT INTO assignments (teacher_id, class_id, subject_id, title, instructions, reference_file, total_marks, deadline, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
        ");
        $stmt->execute([$teacherId, $classId, $subjectId, $title, $instructions, $refFile, $totalMarks, $deadline]);

        Auth::logAudit($adminId, 'ADMIN_CREATE_ASSIGNMENT', "Admin created assignment: $title with deadline $deadline (class $classId)");

        // Google Classroom style: Notify all enrolled students via email
        $subStmt = $db->prepare("SELECT subject_name FROM subjects WHERE id = ?");
        $subStmt->execute([$subjectId]);
        $subRow = $subStmt->fetch();
        $subName = $subRow['subject_name'] ?? 'Diploma Module';
        $posterName = Auth::user()['name'] ?? 'Course Administrator';
        $notified = Mailer::notifyNewAssignment($classId, $title, $subName, $posterName, $deadline, (int)$totalMarks, $instructions);

        if ($notified > 0) {
            Flash::success("Assignment '{$title}' posted successfully and $notified students were notified via Email!");
        } else {
            Flash::success("Assignment '{$title}' posted successfully! Students can now submit their coursework.");
        }
        header("Location: " . $this->buildMaterialsRedirectUrl($classId, $subjectId));
        exit;
    }

    /**
     * Admin Update Assignment Deadline
     */
    public function updateAssignmentDeadline(): void {
        Csrf::checkOrAbort();
        $db = Database::getConnection();

        $assignmentId = (int)($_POST['assignment_id'] ?? 0);
        $subjectId    = (int)($_POST['subject_id'] ?? 0);
        $classId      = $this->resolveMaterialsClassId($db, isset($_POST['class_id']) ? (int)$_POST['class_id'] : null);
        $newDeadline  = trim($_POST['deadline'] ?? '');

        if ($assignmentId <= 0 || empty($newDeadline)) {
            Flash::error("Invalid assignment or deadline date.");
            header("Location: " . $this->buildMaterialsRedirectUrl($classId, $subjectId));
            exit;
        }

        $stmt = $db->prepare("UPDATE assignments SET deadline = ? WHERE id = ?");
        $stmt->execute([$newDeadline, $assignmentId]);

        Auth::logAudit(Auth::id(), 'UPDATE_ASSIGNMENT_DEADLINE', "Updated assignment ID $assignmentId deadline to $newDeadline");
        Flash::success("Assignment deadline successfully updated to " . date('M d, Y h:i A', strtotime($newDeadline)) . "!");
        header("Location: " . $this->buildMaterialsRedirectUrl($classId, $subjectId));
        exit;
    }

    /**
     * Admin Delete Material
     */
    public function deleteMaterial(): void {
        Csrf::checkOrAbort();
        $db = Database::getConnection();

        $matId = (int)($_POST['material_id'] ?? 0);
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $classId = $this->resolveMaterialsClassId($db, isset($_POST['class_id']) ? (int)$_POST['class_id'] : null);

        if ($matId > 0) {
            $stmt = $db->prepare("SELECT file_path FROM materials WHERE id = ?");
            $stmt->execute([$matId]);
            $row = $stmt->fetch();

            $del = $db->prepare("DELETE FROM materials WHERE id = ?");
            $del->execute([$matId]);

            if ($row && !empty($row['file_path'])) {
                $this->safeUnlinkMaterialFile($db, $row['file_path']);
            }

            Flash::success("Study material has been deleted from classroom stream.");
        }

        header("Location: " . $this->buildMaterialsRedirectUrl($classId, $subjectId));
        exit;
    }

    /**
     * Admin Toggle Material Visibility (Hide / Unhide)
     */
    public function toggleMaterialVisibility(): void {
        Csrf::checkOrAbort();
        $db = Database::getConnection();

        $matId = (int)($_POST['material_id'] ?? 0);
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $classId = $this->resolveMaterialsClassId($db, isset($_POST['class_id']) ? (int)$_POST['class_id'] : null);

        if ($matId > 0) {
            $stmt = $db->prepare("SELECT title, is_hidden FROM materials WHERE id = ?");
            $stmt->execute([$matId]);
            $mat = $stmt->fetch();

            if ($mat) {
                $newStatus = ((int)$mat['is_hidden'] === 1) ? 0 : 1;
                $upd = $db->prepare("UPDATE materials SET is_hidden = ? WHERE id = ?");
                $upd->execute([$newStatus, $matId]);

                $statusLabel = ($newStatus === 1) ? 'Hidden from students' : 'Visible to students';
                Auth::logAudit(Auth::id(), 'TOGGLE_MATERIAL_VISIBILITY', "Changed visibility of '{$mat['title']}' to $statusLabel");
                Flash::success("Study material '{$mat['title']}' is now {$statusLabel}.");
            }
        }

        header("Location: " . $this->buildMaterialsRedirectUrl($classId, $subjectId));
        exit;
    }

    /**
     * Admin Bulk Material Operations (Hide, Unhide, Delete)
     */
    public function bulkMaterials(): void {
        Csrf::checkOrAbort();
        $db = Database::getConnection();

        $action = trim($_POST['bulk_action'] ?? '');
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $classId = $this->resolveMaterialsClassId($db, isset($_POST['class_id']) ? (int)$_POST['class_id'] : null);
        $materialIds = $_POST['material_ids'] ?? [];

        if (empty($materialIds) || !is_array($materialIds)) {
            Flash::error("Please select at least one material to perform this action.");
            header("Location: " . $this->buildMaterialsRedirectUrl($classId, $subjectId));
            exit;
        }

        $cleanIds = array_map('intval', $materialIds);
        $cleanIds = array_values(array_filter($cleanIds, fn($id) => $id > 0));

        if (empty($cleanIds)) {
            Flash::error("No valid materials selected.");
            header("Location: " . $this->buildMaterialsRedirectUrl($classId, $subjectId));
            exit;
        }

        $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));

        if ($action === 'hide') {
            $stmt = $db->prepare("UPDATE materials SET is_hidden = 1 WHERE id IN ($placeholders)");
            $stmt->execute($cleanIds);
            Auth::logAudit(Auth::id(), 'BULK_HIDE_MATERIALS', "Hidden " . count($cleanIds) . " materials");
            Flash::success(count($cleanIds) . " study materials have been hidden from students.");
        } elseif ($action === 'unhide') {
            $stmt = $db->prepare("UPDATE materials SET is_hidden = 0 WHERE id IN ($placeholders)");
            $stmt->execute($cleanIds);
            Auth::logAudit(Auth::id(), 'BULK_UNHIDE_MATERIALS', "Unhidden " . count($cleanIds) . " materials");
            Flash::success(count($cleanIds) . " study materials are now visible to students.");
        } elseif ($action === 'delete') {
            $fStmt = $db->prepare("SELECT file_path FROM materials WHERE id IN ($placeholders)");
            $fStmt->execute($cleanIds);
            $files = $fStmt->fetchAll();

            $stmt = $db->prepare("DELETE FROM materials WHERE id IN ($placeholders)");
            $stmt->execute($cleanIds);

            foreach ($files as $f) {
                $this->safeUnlinkMaterialFile($db, $f['file_path'] ?? null);
            }
            Auth::logAudit(Auth::id(), 'BULK_DELETE_MATERIALS', "Deleted " . count($cleanIds) . " materials");
            Flash::success(count($cleanIds) . " study materials have been permanently deleted.");
        } else {
            Flash::error("Invalid bulk action requested.");
        }

        header("Location: " . $this->buildMaterialsRedirectUrl($classId, $subjectId));
        exit;
    }

    /**
     * Admin Module Materials Action (Hide all / Unhide all / Delete all in subject module)
     */
    public function moduleMaterialsAction(): void {
        Csrf::checkOrAbort();
        $db = Database::getConnection();

        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $classId = $this->resolveMaterialsClassId($db, isset($_POST['class_id']) ? (int)$_POST['class_id'] : null);
        $action = trim($_POST['module_action'] ?? '');
        $redirectView = trim($_POST['redirect_view'] ?? 'subject'); // 'subject' or 'grid'

        if ($subjectId <= 0) {
            Flash::error("Invalid classroom module specified.");
            header("Location: " . $this->buildMaterialsRedirectUrl($classId));
            exit;
        }

        $sStmt = $db->prepare("SELECT subject_name FROM subjects WHERE id = ?");
        $sStmt->execute([$subjectId]);
        $subName = $sStmt->fetchColumn() ?: "Module";

        if ($action === 'hide_all') {
            $stmt = $db->prepare("UPDATE materials SET is_hidden = 1 WHERE subject_id = ? AND class_id = ?");
            $stmt->execute([$subjectId, $classId]);
            Auth::logAudit(Auth::id(), 'MODULE_HIDE_ALL_MATERIALS', "Hidden all materials for module $subName ($subjectId, class $classId)");
            Flash::success("All materials in '{$subName}' have been hidden from students.");
        } elseif ($action === 'unhide_all') {
            $stmt = $db->prepare("UPDATE materials SET is_hidden = 0 WHERE subject_id = ? AND class_id = ?");
            $stmt->execute([$subjectId, $classId]);
            Auth::logAudit(Auth::id(), 'MODULE_UNHIDE_ALL_MATERIALS', "Unhidden all materials for module $subName ($subjectId, class $classId)");
            Flash::success("All materials in '{$subName}' are now visible to students.");
        } elseif ($action === 'delete_all') {
            $fStmt = $db->prepare("SELECT file_path FROM materials WHERE subject_id = ? AND class_id = ?");
            $fStmt->execute([$subjectId, $classId]);
            $files = $fStmt->fetchAll();

            $stmt = $db->prepare("DELETE FROM materials WHERE subject_id = ? AND class_id = ?");
            $stmt->execute([$subjectId, $classId]);

            foreach ($files as $f) {
                $this->safeUnlinkMaterialFile($db, $f['file_path'] ?? null);
            }
            Auth::logAudit(Auth::id(), 'MODULE_DELETE_ALL_MATERIALS', "Deleted all materials for module $subName ($subjectId, class $classId)");
            Flash::success("All study materials in module '{$subName}' have been deleted.");
        } else {
            Flash::error("Unknown module operation.");
        }

        if ($redirectView === 'grid') {
            header("Location: " . $this->buildMaterialsRedirectUrl($classId));
        } else {
            header("Location: " . $this->buildMaterialsRedirectUrl($classId, $subjectId));
        }
        exit;
    }

    /**
     * Admin Global Batch Material Operations (Hide all, unhide all, or wipe in selected batch)
     */
    public function globalMaterialsAction(): void {
        Csrf::checkOrAbort();
        $db = Database::getConnection();

        $action = trim($_POST['global_action'] ?? '');
        $targetSubjectId = (int)($_POST['target_subject_id'] ?? 0); // 0 = All Modules
        $classId = $this->resolveMaterialsClassId($db, isset($_POST['class_id']) ? (int)$_POST['class_id'] : null);

        if ($action === 'hide_all') {
            if ($targetSubjectId > 0) {
                $stmt = $db->prepare("UPDATE materials SET is_hidden = 1 WHERE subject_id = ? AND class_id = ?");
                $stmt->execute([$targetSubjectId, $classId]);
                Auth::logAudit(Auth::id(), 'MODULE_HIDE_ALL_MATERIALS', "Hidden all materials for subject ID $targetSubjectId (class $classId)");
                Flash::success("All study materials for the selected module are now HIDDEN from students (safe for new batch).");
            } else {
                $stmt = $db->prepare("UPDATE materials SET is_hidden = 1 WHERE class_id = ?");
                $stmt->execute([$classId]);
                $cntStmt = $db->prepare("SELECT COUNT(*) FROM materials WHERE class_id = ?");
                $cntStmt->execute([$classId]);
                $count = (int)$cntStmt->fetchColumn();
                Auth::logAudit(Auth::id(), 'GLOBAL_HIDE_ALL_MATERIALS', "Hidden all $count materials in class $classId");
                Flash::success("All study materials ($count items) in this batch are now HIDDEN from students. You or lecturers can unhide them as lessons progress.");
            }
        } elseif ($action === 'unhide_all') {
            if ($targetSubjectId > 0) {
                $stmt = $db->prepare("UPDATE materials SET is_hidden = 0 WHERE subject_id = ? AND class_id = ?");
                $stmt->execute([$targetSubjectId, $classId]);
                Flash::success("All study materials in the selected module are now VISIBLE to students.");
            } else {
                $stmt = $db->prepare("UPDATE materials SET is_hidden = 0 WHERE class_id = ?");
                $stmt->execute([$classId]);
                $cntStmt = $db->prepare("SELECT COUNT(*) FROM materials WHERE class_id = ?");
                $cntStmt->execute([$classId]);
                $count = (int)$cntStmt->fetchColumn();
                Auth::logAudit(Auth::id(), 'GLOBAL_UNHIDE_ALL_MATERIALS', "Unhidden all $count materials in class $classId");
                Flash::success("All study materials ($count items) in this batch are now VISIBLE to students.");
            }
        } elseif ($action === 'delete_all') {
            if ($targetSubjectId > 0) {
                $fStmt = $db->prepare("SELECT file_path FROM materials WHERE subject_id = ? AND class_id = ?");
                $fStmt->execute([$targetSubjectId, $classId]);
                $files = $fStmt->fetchAll();

                $del = $db->prepare("DELETE FROM materials WHERE subject_id = ? AND class_id = ?");
                $del->execute([$targetSubjectId, $classId]);
                $count = $del->rowCount();

                foreach ($files as $f) {
                    $this->safeUnlinkMaterialFile($db, $f['file_path'] ?? null);
                }
                Auth::logAudit(Auth::id(), 'MODULE_DELETE_ALL_MATERIALS', "Permanently deleted $count materials for module $targetSubjectId (class $classId)");
                Flash::success("Permanently deleted $count study materials from selected module.");
            } else {
                $fStmt = $db->prepare("SELECT file_path FROM materials WHERE class_id = ?");
                $fStmt->execute([$classId]);
                $files = $fStmt->fetchAll();

                $del = $db->prepare("DELETE FROM materials WHERE class_id = ?");
                $del->execute([$classId]);
                $count = $del->rowCount();

                foreach ($files as $f) {
                    $this->safeUnlinkMaterialFile($db, $f['file_path'] ?? null);
                }
                Auth::logAudit(Auth::id(), 'GLOBAL_DELETE_ALL_MATERIALS', "Permanently deleted all $count materials in class $classId");
                Flash::success("Permanently deleted all $count study materials in this batch.");
            }
        } else {
            Flash::error("Invalid action requested.");
        }

        header("Location: " . $this->buildMaterialsRedirectUrl($classId));
        exit;
    }

    /**
     * Copy study materials (and optionally assignments) from a previous batch into the selected batch.
     */
    public function copyBatchMaterials(): void {
        Csrf::checkOrAbort();
        $db = Database::getConnection();

        $sourceClassId = (int)($_POST['source_class_id'] ?? 0);
        $targetClassId = $this->resolveMaterialsClassId($db, isset($_POST['target_class_id']) ? (int)$_POST['target_class_id'] : null);
        $importHidden  = !empty($_POST['import_hidden']) ? 1 : 0;

        if ($sourceClassId <= 0 || $targetClassId <= 0 || $sourceClassId === $targetClassId) {
            Flash::error("Please select two different batches to copy materials between.");
            header("Location: " . $this->buildMaterialsRedirectUrl($targetClassId));
            exit;
        }

        // Ensure target batch has all active subjects synced
        AttendanceManager::syncBatchSubjects($db, $targetClassId);

        $srcStmt = $db->prepare("SELECT * FROM materials WHERE class_id = ?");
        $srcStmt->execute([$sourceClassId]);
        $srcMaterials = $srcStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($srcMaterials)) {
            Flash::error("No study materials found in the selected source batch.");
            header("Location: " . $this->buildMaterialsRedirectUrl($targetClassId));
            exit;
        }

        $dupChk = $db->prepare("SELECT COUNT(*) FROM materials WHERE class_id = ? AND subject_id = ? AND title = ?");
        $insStmt = $db->prepare("
            INSERT INTO materials (teacher_id, class_id, subject_id, category_id, title, description, material_type, file_path, original_filename, external_url, file_size_bytes, is_hidden, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $copied = 0;
        foreach ($srcMaterials as $m) {
            $dupChk->execute([$targetClassId, (int)$m['subject_id'], $m['title']]);
            if ((int)$dupChk->fetchColumn() > 0) {
                continue;
            }
            $insStmt->execute([
                (int)$m['teacher_id'],
                $targetClassId,
                (int)$m['subject_id'],
                (int)$m['category_id'],
                $m['title'],
                $m['description'],
                $m['material_type'],
                $m['file_path'],
                $m['original_filename'],
                $m['external_url'],
                (int)($m['file_size_bytes'] ?? 0),
                $importHidden,
            ]);
            $copied++;
        }

        Auth::logAudit(Auth::id(), 'COPY_BATCH_MATERIALS', "Copied $copied materials from class $sourceClassId to class $targetClassId (hidden=$importHidden)");
        if ($copied > 0) {
            $visMsg = $importHidden ? " (currently hidden — unhide when ready)" : " and made visible to students in this batch";
            Flash::success("Copied $copied study material(s) into the selected batch{$visMsg}!");
        } else {
            Flash::success("All study materials from that batch already exist in the current batch.");
        }

        header("Location: " . $this->buildMaterialsRedirectUrl($targetClassId));
        exit;
    }

    /**
     * Admin Delete Assignment
     */
    public function deleteAssignment(): void {
        Csrf::checkOrAbort();
        $db = Database::getConnection();

        $asgId = (int)($_POST['assignment_id'] ?? 0);
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $classId = $this->resolveMaterialsClassId($db, isset($_POST['class_id']) ? (int)$_POST['class_id'] : null);

        if ($asgId > 0) {
            $del = $db->prepare("DELETE FROM assignments WHERE id = ?");
            $del->execute([$asgId]);

            Flash::success("Assignment has been deleted.");
        }

        header("Location: " . $this->buildMaterialsRedirectUrl($classId, $subjectId));
        exit;
    }

    /**
     * Admin Create a New Class / Subject Module Card (Google Classroom style)
     */
    public function createClassroom(): void {
        Csrf::checkOrAbort();
        $db = Database::getConnection();

        $subjectName = trim($_POST['subject_name'] ?? '');
        $subjectCode = strtoupper(trim($_POST['subject_code'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $colorTheme  = trim($_POST['color_theme'] ?? 'blue');
        $icon        = trim($_POST['icon'] ?? '');
        $teacherId   = (int)($_POST['teacher_id'] ?? 2);
        $classId     = $this->resolveMaterialsClassId($db, isset($_POST['class_id']) ? (int)$_POST['class_id'] : null);

        if (empty($subjectName) || empty($subjectCode)) {
            Flash::error("Please provide both Class / Module Name and Subject Code.");
            header("Location: " . $this->buildMaterialsRedirectUrl($classId));
            exit;
        }

        // Smart icon auto-detection if not specified or set to auto
        if (empty($icon) || $icon === 'auto') {
            $lower = strtolower($subjectName . ' ' . $description);
            if (str_contains($lower, 'database') || str_contains($lower, 'sql') || str_contains($lower, 'dbms')) {
                $icon = 'bi-database-fill-gear';
            } elseif (str_contains($lower, 'program') || str_contains($lower, 'software') || str_contains($lower, 'code') || str_contains($lower, 'java') || str_contains($lower, 'php') || str_contains($lower, 'python')) {
                $icon = 'bi-code-slash';
            } elseif (str_contains($lower, 'web') || str_contains($lower, 'host') || str_contains($lower, 'html')) {
                $icon = 'bi-globe-americas';
            } elseif (str_contains($lower, 'network') || str_contains($lower, 'security') || str_contains($lower, 'cyber')) {
                $icon = 'bi-hdd-network-fill';
            } elseif (str_contains($lower, 'cloud') || str_contains($lower, 'server') || str_contains($lower, 'devops') || str_contains($lower, 'aws')) {
                $icon = 'bi-cloud-check-fill';
            } elseif (str_contains($lower, 'graphic') || str_contains($lower, 'design') || str_contains($lower, 'photo') || str_contains($lower, 'illustrator')) {
                $icon = 'bi-brush-fill';
            } elseif (str_contains($lower, 'cinema') || str_contains($lower, 'video') || str_contains($lower, 'media') || str_contains($lower, 'film')) {
                $icon = 'bi-camera-reels-fill';
            } elseif (str_contains($lower, 'mobile') || str_contains($lower, 'app') || str_contains($lower, 'android')) {
                $icon = 'bi-phone-fill';
            } elseif (str_contains($lower, 'math') || str_contains($lower, 'comput')) {
                $icon = 'bi-calculator-fill';
            } else {
                $icon = 'bi-mortarboard-fill';
            }
        }

        // Check if subject_code exists
        $chk = $db->prepare("SELECT id FROM subjects WHERE subject_code = ?");
        $chk->execute([$subjectCode]);
        $existing = $chk->fetch();

        if ($existing) {
            $subjectId = (int)$existing['id'];
            $up = $db->prepare("UPDATE subjects SET subject_name = ?, description = ?, color_theme = ?, icon = ? WHERE id = ?");
            $up->execute([$subjectName, $description, $colorTheme, $icon, $subjectId]);
        } else {
            $ins = $db->prepare("INSERT INTO subjects (subject_code, subject_name, description, color_theme, icon, credits, status) VALUES (?, ?, ?, ?, ?, 4, 'active')");
            $ins->execute([$subjectCode, $subjectName, $description, $colorTheme, $icon]);
            $subjectId = (int)$db->lastInsertId();
        }

        // Link with class_subjects
        $csStmt = $db->prepare("SELECT id FROM class_subjects WHERE class_id = ? AND subject_id = ?");
        $csStmt->execute([$classId, $subjectId]);
        $cs = $csStmt->fetch();
        if ($cs) {
            $classSubjectId = (int)$cs['id'];
        } else {
            $csIns = $db->prepare("INSERT INTO class_subjects (class_id, subject_id) VALUES (?, ?)");
            $csIns->execute([$classId, $subjectId]);
            $classSubjectId = (int)$db->lastInsertId();
        }

        // Link with teacher_assignments
        $taStmt = $db->prepare("SELECT id FROM teacher_assignments WHERE class_subject_id = ?");
        $taStmt->execute([$classSubjectId]);
        if ($taStmt->fetch()) {
            $upTa = $db->prepare("UPDATE teacher_assignments SET teacher_id = ? WHERE class_subject_id = ?");
            $upTa->execute([$teacherId, $classSubjectId]);
        } else {
            $insTa = $db->prepare("INSERT INTO teacher_assignments (class_subject_id, teacher_id) VALUES (?, ?)");
            $insTa->execute([$classSubjectId, $teacherId]);
        }

        Auth::logAudit(Auth::id(), 'CREATE_CLASSROOM_MODULE', "Created new classroom module: $subjectName ($subjectCode)");
        Flash::success("New class module '{$subjectName}' created successfully and added to Google Classroom Hub!");
        header("Location: " . $this->buildMaterialsRedirectUrl($classId));
        exit;
    }

    /**
     * Admin Delete Class Module
     */
    public function deleteClassroom(): void {
        Csrf::checkOrAbort();
        $db = Database::getConnection();

        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $classId   = $this->resolveMaterialsClassId($db, isset($_POST['class_id']) ? (int)$_POST['class_id'] : null);

        if ($subjectId > 0) {
            // Find class_subject_id
            $csStmt = $db->prepare("SELECT id FROM class_subjects WHERE class_id = ? AND subject_id = ?");
            $csStmt->execute([$classId, $subjectId]);
            $cs = $csStmt->fetch();

            if ($cs) {
                $csId = (int)$cs['id'];
                // Delete teacher assignments for this class_subject
                $db->prepare("DELETE FROM teacher_assignments WHERE class_subject_id = ?")->execute([$csId]);
                // Delete from class_subjects
                $db->prepare("DELETE FROM class_subjects WHERE id = ?")->execute([$csId]);
            }

            // Check if this subject is used in any other class
            $otherUsage = $db->prepare("SELECT COUNT(*) FROM class_subjects WHERE subject_id = ?");
            $otherUsage->execute([$subjectId]);
            if ((int)$otherUsage->fetchColumn() === 0) {
                $db->prepare("DELETE FROM materials WHERE subject_id = ?")->execute([$subjectId]);
                $db->prepare("DELETE FROM assignments WHERE subject_id = ?")->execute([$subjectId]);
                $db->prepare("DELETE FROM subjects WHERE id = ?")->execute([$subjectId]);
            }

            Auth::logAudit(Auth::id(), 'DELETE_CLASSROOM_MODULE', "Deleted class module subject ID $subjectId");
            Flash::success("Class module has been deleted successfully.");
        }

        header("Location: " . $this->buildMaterialsRedirectUrl($classId));
        exit;
    }

    public function assignments(): void {
        $db = Database::getConnection();
        $assignments = $db->query("
            SELECT a.*, u.name as teacher_name, s.subject_name, cl.class_name,
                   (SELECT COUNT(*) FROM submissions sub WHERE sub.assignment_id = a.id) as submission_count
            FROM assignments a
            JOIN users u ON a.teacher_id = u.id
            JOIN subjects s ON a.subject_id = s.id
            JOIN classes cl ON a.class_id = cl.id
            ORDER BY a.created_at DESC
        ")->fetchAll();

        $pageTitle = "Assignments Overview";
        require_once __DIR__ . '/../Views/admin/assignments.php';
    }

    public function logs(): void {
        $db = Database::getConnection();
        $auditLogs = $db->query("
            SELECT al.*, u.name as user_name, u.email as user_email
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            ORDER BY al.id DESC LIMIT 50
        ")->fetchAll();

        $loginLogs = $db->query("SELECT * FROM login_logs ORDER BY id DESC LIMIT 50")->fetchAll();

        $pageTitle = "System Security & Audit Logs";
        require_once __DIR__ . '/../Views/admin/logs.php';
    }

    /**
     * Student Verification Documents & Certificates Management
     */
    public function documents(): void {
        require_once __DIR__ . '/../Helpers/DocumentManager.php';

        $db = Database::getConnection();
        $classes = $db->query("SELECT id, class_name, status FROM classes ORDER BY status ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

        $classFilter = isset($_GET['class_id']) && $_GET['class_id'] !== '' ? (int)$_GET['class_id'] : null;
        $batchStatusFilter = trim($_GET['batch_status'] ?? 'active');
        $statusFilter = trim($_GET['status'] ?? 'all');
        $search = trim($_GET['search'] ?? '');

        // By default, only show students from currently active cohorts (clears archived batch docs)
        $students = DocumentManager::getStudentsDocDirectory($classFilter, $batchStatusFilter);
        $docTypes = DocumentManager::getDocTypes();

        // Metrics
        $totalStudents = count($students);
        $completedStudents = 0;
        $partialStudents = 0;
        $pendingReviewCount = 0;
        $verifiedAllCount = 0;

        foreach ($students as $st) {
            if ($st['status_badge'] === 'verified') $verifiedAllCount++;
            if ($st['is_complete']) $completedStudents++;
            elseif ($st['uploaded_count'] > 0) $partialStudents++;
            if ($st['pending_count'] > 0) $pendingReviewCount++;
        }

        // Filter students by status & search
        if ($statusFilter !== 'all' || !empty($search)) {
            $filtered = [];
            foreach ($students as $st) {
                if (!empty($search)) {
                    $q = strtolower($search);
                    $nameMatch = str_contains(strtolower($st['name']), $q);
                    $codeMatch = str_contains(strtolower($st['student_code'] ?? ''), $q);
                    $misMatch = str_contains(strtolower($st['mis_number'] ?? ''), $q);
                    $nicMatch = str_contains(strtolower($st['nic'] ?? ''), $q);
                    if (!$nameMatch && !$codeMatch && !$misMatch && !$nicMatch) {
                        continue;
                    }
                }

                if ($statusFilter === 'completed' && !$st['is_complete']) continue;
                if ($statusFilter === 'partial' && ($st['is_complete'] || $st['uploaded_count'] === 0)) continue;
                if ($statusFilter === 'none' && $st['uploaded_count'] > 0) continue;
                if ($statusFilter === 'pending_review' && $st['pending_count'] === 0) continue;
                if ($statusFilter === 'verified' && $st['status_badge'] !== 'verified') continue;

                $filtered[] = $st;
            }
            $students = $filtered;
        }

        $pageTitle = "Student Certificates & Verification Documents";
        require_once __DIR__ . '/../Views/admin/documents.php';
    }

    /**
     * Verify or Reject a Student Document
     */
    public function verifyDocument(): void {
        Csrf::checkOrAbort();
        require_once __DIR__ . '/../Helpers/DocumentManager.php';

        $docId = (int)($_POST['document_id'] ?? 0);
        $status = trim($_POST['status'] ?? 'verified');
        $notes = trim($_POST['admin_notes'] ?? '');
        $returnUrl = $_POST['return_url'] ?? 'index.php?page=admin_documents';

        if ($docId <= 0 || !in_array($status, ['verified', 'rejected', 'pending'], true)) {
            Flash::error("Invalid verification request.");
            header("Location: $returnUrl");
            exit;
        }

        $res = DocumentManager::verifyDocument($docId, $status, $notes, Auth::id());
        if ($res['success']) {
            Auth::logAudit(Auth::id(), 'VERIFY_STUDENT_DOCUMENT', "Document ID $docId status updated to $status");
            Flash::success("Document has been successfully marked as " . ucfirst($status) . ".");
        } else {
            Flash::error($res['error'] ?? "Failed to update document verification status.");
        }

        header("Location: $returnUrl");
        exit;
    }

    /**
     * AJAX Endpoint: Get documents of a specific student for viewing in Old Batches / Student Management
     */
    public function getStudentDocumentsJson(): void {
        Auth::requireRole(['admin', 'teacher']);
        require_once __DIR__ . '/../Helpers/DocumentManager.php';
        $studentId = (int)($_GET['student_id'] ?? 0);
        $docs = DocumentManager::getStudentDocsWithMeta($studentId);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'documents' => $docs]);
        exit;
    }

    /**
     * Manage Past Papers, Model Papers & Marking Schemes
     */
    public function papers(): void {
        Auth::requireRole(['admin']);
        $db = Database::getConnection();

        $semFilter  = (int)($_GET['semester'] ?? 0);
        $typeFilter = trim($_GET['paper_type'] ?? '');
        $modFilter  = trim($_GET['module_code'] ?? '');
        $search     = trim($_GET['search'] ?? '');

        $sql = "SELECT * FROM exam_papers WHERE 1=1";
        $params = [];

        if ($semFilter > 0) {
            $sql .= " AND semester = ?";
            $params[] = $semFilter;
        }
        if (!empty($typeFilter)) {
            $sql .= " AND paper_type = ?";
            $params[] = $typeFilter;
        }
        if (!empty($modFilter)) {
            $sql .= " AND module_code = ?";
            $params[] = $modFilter;
        }
        if (!empty($search)) {
            $sql .= " AND (title LIKE ? OR description LIKE ? OR subject_title LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $sql .= " ORDER BY semester ASC, exam_year DESC, module_code ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $papers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Statistics
        $totalPapers = (int)$db->query("SELECT COUNT(*) FROM exam_papers")->fetchColumn();
        $totalPastPapers = (int)$db->query("SELECT COUNT(*) FROM exam_papers WHERE paper_type = 'past_paper'")->fetchColumn();
        $totalModelPapers = (int)$db->query("SELECT COUNT(*) FROM exam_papers WHERE paper_type = 'model_paper'")->fetchColumn();
        $totalMarkingSchemes = (int)$db->query("SELECT COUNT(*) FROM exam_papers WHERE paper_type = 'marking_scheme'")->fetchColumn();
        $sem1Count = (int)$db->query("SELECT COUNT(*) FROM exam_papers WHERE semester = 1")->fetchColumn();
        $sem2Count = (int)$db->query("SELECT COUNT(*) FROM exam_papers WHERE semester = 2")->fetchColumn();

        // Distinct NVTI modules for dropdown
        $curriculumModules = $db->query("SELECT module_code, module_title, semester FROM curriculum_modules ORDER BY semester, module_no")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($curriculumModules)) {
            $curriculumModules = $db->query("SELECT DISTINCT module_code, subject_title as module_title, semester FROM exam_papers ORDER BY semester, module_code")->fetchAll(PDO::FETCH_ASSOC);
        }

        $pageTitle = "Exam Papers & Model Question Bank";
        require_once __DIR__ . '/../Views/admin/papers.php';
    }

    /**
     * Upload a new Past Paper, Model Paper or Marking Scheme
     */
    public function uploadPaper(): void {
        Csrf::checkOrAbort();
        Auth::requireRole(['admin']);
        $db = Database::getConnection();

        $title = trim($_POST['title'] ?? '');
        $semester = (int)($_POST['semester'] ?? 1);
        $moduleCode = trim($_POST['module_code'] ?? '');
        $subjectTitle = trim($_POST['subject_title'] ?? '');
        $paperType = trim($_POST['paper_type'] ?? 'past_paper');
        $examYear = (int)($_POST['exam_year'] ?? date('Y'));
        $description = trim($_POST['description'] ?? '');

        if (empty($title) || empty($moduleCode)) {
            Flash::error("Please provide both Paper Title and Module Code.");
            header("Location: index.php?page=admin_papers");
            exit;
        }

        if (empty($subjectTitle)) {
            $mStmt = $db->prepare("SELECT module_title FROM curriculum_modules WHERE module_code = ? LIMIT 1");
            $mStmt->execute([$moduleCode]);
            $subjectTitle = $mStmt->fetchColumn() ?: $moduleCode;
        }

        $filePath = null;
        $originalFilename = null;
        $fileSizeBytes = 0;

        if (!empty($_FILES['paper_file']['name'])) {
            require_once __DIR__ . '/../Helpers/Uploader.php';
            $uploadRes = Uploader::upload($_FILES['paper_file'], MATERIAL_UPLOAD_PATH, ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx']);
            if (!$uploadRes['success']) {
                Flash::error("File upload failed: " . ($uploadRes['error'] ?? 'Unknown error'));
                header("Location: index.php?page=admin_papers");
                exit;
            }
            $filePath = $uploadRes['filename'];
            $originalFilename = $uploadRes['original_name'];
            $fileSizeBytes = (int)$uploadRes['size'];
        } else {
            Flash::error("Please select a PDF or Image file for the paper.");
            header("Location: index.php?page=admin_papers");
            exit;
        }

        $stmt = $db->prepare("
            INSERT INTO exam_papers 
            (semester, module_code, subject_title, paper_type, exam_year, title, description, file_path, original_filename, file_size_bytes, download_count, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())
        ");
        $stmt->execute([
            $semester,
            $moduleCode,
            $subjectTitle,
            $paperType,
            $examYear,
            $title,
            $description,
            $filePath,
            $originalFilename,
            $fileSizeBytes
        ]);

        Auth::logAudit(Auth::id(), 'UPLOAD_EXAM_PAPER', "Uploaded paper '$title' ($paperType) for $moduleCode");
        Flash::success("Examination Paper '$title' uploaded successfully!");
        header("Location: index.php?page=admin_papers&semester=" . $semester);
        exit;
    }

    /**
     * Update / Edit an existing Exam Paper
     */
    public function updatePaper(): void {
        Csrf::checkOrAbort();
        Auth::requireRole(['admin']);
        $db = Database::getConnection();

        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $semester = (int)($_POST['semester'] ?? 1);
        $moduleCode = trim($_POST['module_code'] ?? '');
        $subjectTitle = trim($_POST['subject_title'] ?? '');
        $paperType = trim($_POST['paper_type'] ?? 'past_paper');
        $examYear = (int)($_POST['exam_year'] ?? date('Y'));
        $description = trim($_POST['description'] ?? '');

        if ($id <= 0 || empty($title) || empty($moduleCode)) {
            Flash::error("Please provide both Paper Title and Module Code.");
            header("Location: index.php?page=admin_papers");
            exit;
        }

        // Fetch existing paper
        $stmt = $db->prepare("SELECT * FROM exam_papers WHERE id = ?");
        $stmt->execute([$id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            Flash::error("Paper not found.");
            header("Location: index.php?page=admin_papers");
            exit;
        }

        if (empty($subjectTitle)) {
            $mStmt = $db->prepare("SELECT module_title FROM curriculum_modules WHERE module_code = ? LIMIT 1");
            $mStmt->execute([$moduleCode]);
            $subjectTitle = $mStmt->fetchColumn() ?: $moduleCode;
        }

        $filePath = $existing['file_path'];
        $originalFilename = $existing['original_filename'];
        $fileSizeBytes = $existing['file_size_bytes'];

        // If a new file is uploaded
        if (!empty($_FILES['paper_file']['name'])) {
            require_once __DIR__ . '/../Helpers/Uploader.php';
            $uploadRes = Uploader::upload($_FILES['paper_file'], MATERIAL_UPLOAD_PATH, ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx']);
            if (!$uploadRes['success']) {
                Flash::error("File replacement failed: " . ($uploadRes['error'] ?? 'Unknown error'));
                header("Location: index.php?page=admin_papers&semester=" . $semester);
                exit;
            }

            // Remove old file if it exists
            if (!empty($existing['file_path'])) {
                $oldFullPath = MATERIAL_UPLOAD_PATH . DIRECTORY_SEPARATOR . $existing['file_path'];
                if (file_exists($oldFullPath) && is_file($oldFullPath)) {
                    @unlink($oldFullPath);
                }
            }

            $filePath = $uploadRes['filename'];
            $originalFilename = $uploadRes['original_name'];
            $fileSizeBytes = (int)$uploadRes['size'];
        }

        $upStmt = $db->prepare("
            UPDATE exam_papers 
            SET semester = ?, module_code = ?, subject_title = ?, paper_type = ?, 
                exam_year = ?, title = ?, description = ?, file_path = ?, 
                original_filename = ?, file_size_bytes = ?
            WHERE id = ?
        ");
        $upStmt->execute([
            $semester,
            $moduleCode,
            $subjectTitle,
            $paperType,
            $examYear,
            $title,
            $description,
            $filePath,
            $originalFilename,
            $fileSizeBytes,
            $id
        ]);

        Auth::logAudit(Auth::id(), 'UPDATE_EXAM_PAPER', "Updated paper ID $id ('$title')");
        Flash::success("Examination Paper '$title' updated successfully!");
        header("Location: index.php?page=admin_papers&semester=" . $semester);
        exit;
    }

    /**
     * Delete an Exam Paper
     */
    public function deletePaper(): void {
        Csrf::checkOrAbort();
        Auth::requireRole(['admin']);
        $db = Database::getConnection();

        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("SELECT file_path, title FROM exam_papers WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            if (!empty($row['file_path'])) {
                $fullPath = MATERIAL_UPLOAD_PATH . DIRECTORY_SEPARATOR . $row['file_path'];
                if (file_exists($fullPath) && is_file($fullPath)) {
                    @unlink($fullPath);
                }
            }
            $del = $db->prepare("DELETE FROM exam_papers WHERE id = ?");
            $del->execute([$id]);

            Auth::logAudit(Auth::id(), 'DELETE_EXAM_PAPER', "Deleted paper ID $id (" . $row['title'] . ")");
            Flash::success("Examination Paper deleted successfully.");
        } else {
            Flash::error("Examination Paper not found.");
        }

        $sem = (int)($_POST['semester'] ?? 0);
        $redirectUrl = "index.php?page=admin_papers" . ($sem > 0 ? "&semester=$sem" : "");
        header("Location: $redirectUrl");
        exit;
    }

    /**
     * Show Brevo / SMTP Email Settings & Diagnostics
     */
    public function showEmailSettings(): void {
        $pageTitle = "Email & Brevo SMTP Configuration";
        require_once __DIR__ . '/../Views/admin/email_settings.php';
    }

    /**
     * Handle Sending a Test Email
     */
    public function testEmail(): void {
        Csrf::checkOrAbort();
        $targetEmail = trim($_POST['test_email'] ?? '');

        if (empty($targetEmail) || !filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
            Flash::error("Please provide a valid destination email address for the test.");
            header("Location: index.php?page=admin_email_settings");
            exit;
        }

        $subject = "Brevo SMTP Test Connection - " . APP_NAME;
        $html = "
        <div style='background-color: #f1f5f9; padding: 30px 15px; font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica, Arial, sans-serif;'>
            <div style='max-width: 520px; margin: 0 auto; background: #ffffff; border-radius: 10px; padding: 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);'>
                <h2 style='color: #10b981; margin-top: 0;'>&#x2705; SMTP Mailer Working Perfectly!</h2>
                <p style='color: #334155;'>Congratulations! Your SMTP email configuration is functioning 100% properly on <strong>" . APP_NAME . "</strong>.</p>
                <div style='background: #f8fafc; border-left: 4px solid #10b981; padding: 12px 16px; margin: 18px 0; font-size: 13px; color: #475569;'>
                    <strong>Server:</strong> " . SMTP_HOST . ":" . SMTP_PORT . "<br>
                    <strong>Sender:</strong> " . MAIL_FROM_NAME . " &lt;" . MAIL_FROM_EMAIL . "&gt;<br>
                    <strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "
                </div>
                <p style='font-size: 13px; color: #64748b; margin-bottom: 0;'>Your NVTI Baddegama National Diploma Portal can now securely dispatch password resets, student welcome emails, and study material notifications.</p>
            </div>
        </div>";

        $res = Mailer::send($targetEmail, $subject, $html, 'Portal Administrator');
        if ($res['success']) {
            Auth::logAudit(Auth::id(), 'TEST_EMAIL_SUCCESS', "Sent test email to $targetEmail via SMTP");
            Flash::success("Awesome! Test email was successfully sent to <strong>" . htmlspecialchars($targetEmail) . "</strong>. Please check your inbox!");
        } else {
            Auth::logAudit(Auth::id(), 'TEST_EMAIL_FAILED', "Failed sending test email to $targetEmail: " . $res['message']);
            Flash::error("Email delivery failed: " . htmlspecialchars($res['message']));
        }

        header("Location: index.php?page=admin_email_settings");
        exit;
    }
}
