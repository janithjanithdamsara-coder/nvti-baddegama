<?php
/**
 * Student Controller
 * NVTI Baddegama National Diploma Portal
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Csrf.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Helpers/Sanitizer.php';
require_once __DIR__ . '/../Helpers/Uploader.php';

class StudentController {

    public function __construct() {
        Auth::requireRole('student');
    }

    public function dashboard(): void {
        $db = Database::getConnection();
        $studentId = Auth::id();

        // Student Profile & Enrolled Class
        $stmt = $db->prepare("
            SELECT sp.*, c.class_name, co.course_name, co.course_code
            FROM student_profiles sp
            LEFT JOIN classes c ON sp.class_id = c.id
            LEFT JOIN courses co ON sp.course_id = co.id
            WHERE sp.user_id = ?
        ");
        $stmt->execute([$studentId]);
        $profile = $stmt->fetch();
        $classId = (int)($profile['class_id'] ?? 1);

        // Enrolled Subjects
        $subStmt = $db->prepare("
            SELECT s.*
            FROM class_subjects cs
            JOIN subjects s ON cs.subject_id = s.id
            WHERE cs.class_id = ?
        ");
        $subStmt->execute([$classId]);
        $subjects = $subStmt->fetchAll();

        // Recent Materials for this class
        $matStmt = $db->prepare("
            SELECT m.*, cat.category_name, cat.icon as category_icon, s.subject_name, u.name as teacher_name
            FROM materials m
            JOIN categories cat ON m.category_id = cat.id
            JOIN subjects s ON m.subject_id = s.id
            JOIN users u ON m.teacher_id = u.id
            WHERE m.class_id = ?
            ORDER BY m.created_at DESC LIMIT 5
        ");
        $matStmt->execute([$classId]);
        $recentMaterials = $matStmt->fetchAll();

        // Active Assignments & Student Submission Status
        $asgStmt = $db->prepare("
            SELECT a.*, s.subject_name, u.name as teacher_name,
                   sub.id as submission_id, sub.status as submission_status, sub.marks, sub.submitted_at
            FROM assignments a
            JOIN subjects s ON a.subject_id = s.id
            JOIN users u ON a.teacher_id = u.id
            LEFT JOIN submissions sub ON a.id = sub.assignment_id AND sub.student_id = ?
            WHERE a.class_id = ?
            ORDER BY a.deadline ASC LIMIT 5
        ");
        $asgStmt->execute([$studentId, $classId]);
        $assignments = $asgStmt->fetchAll();

        // Timetable sessions
        $sessStmt = $db->prepare("
            SELECT cs.*, s.subject_name, s.subject_code, u.name as teacher_name
            FROM class_sessions cs
            JOIN subjects s ON cs.subject_id = s.id
            JOIN users u ON cs.teacher_id = u.id
            WHERE cs.class_id = ?
            ORDER BY FIELD(cs.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), cs.start_time ASC
        ");
        $sessStmt->execute([$classId]);
        $sessions = $sessStmt->fetchAll();

        // Attendance Overview Stats for Dashboard
        $attStmt = $db->prepare("
            SELECT COUNT(*) as total, 
                   SUM(CASE WHEN status IN ('present', 'late') THEN 1 ELSE 0 END) as attended
            FROM attendance WHERE student_id = ?
        ");
        $attStmt->execute([$studentId]);
        $attData = $attStmt->fetch();
        $totalAtt = (int)($attData['total'] ?? 0);
        $attended = (int)($attData['attended'] ?? 0);
        $attendanceRate = $totalAtt > 0 ? round(($attended / $totalAtt) * 100, 1) : 100.0;

        // Student Verification Documents summary
        require_once __DIR__ . '/../Helpers/DocumentManager.php';
        $docSummary = DocumentManager::getStudentSummary($studentId);

        $pageTitle = "Student Dashboard";
        require_once __DIR__ . '/../Views/student/dashboard.php';
    }

    public function materials(): void {
        $db = Database::getConnection();
        $studentId = Auth::id();

        // Get student's class
        $st = $db->prepare("SELECT class_id FROM student_profiles WHERE user_id = ?");
        $st->execute([$studentId]);
        $prof = $st->fetch();
        $classId = (int)($prof['class_id'] ?? 1);

        // Filters
        $catFilter = (int)($_GET['category_id'] ?? 0);
        $subFilter = (int)($_GET['subject_id'] ?? 0);
        $search    = trim($_GET['search'] ?? '');
        $activeTab = trim($_GET['tab'] ?? 'classroom'); // 'classroom', 'stream', 'curriculum', 'all'

        // Categories
        $categories = $db->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();

        // Subjects in student's class
        $subStmt = $db->prepare("
            SELECT s.id, s.subject_name, s.subject_code, s.description,
                   u.name as teacher_name, u.email as teacher_email
            FROM class_subjects cs
            JOIN subjects s ON cs.subject_id = s.id
            LEFT JOIN teacher_assignments ta ON ta.class_subject_id = cs.id
            LEFT JOIN users u ON ta.teacher_id = u.id
            WHERE cs.class_id = ?
        ");
        $subStmt->execute([$classId]);
        $subjects = $subStmt->fetchAll();

        // Query materials
        $sql = "
            SELECT m.*, cat.category_name, cat.icon as category_icon, s.subject_name, s.subject_code, u.name as teacher_name
            FROM materials m
            JOIN categories cat ON m.category_id = cat.id
            JOIN subjects s ON m.subject_id = s.id
            JOIN users u ON m.teacher_id = u.id
            WHERE m.class_id = ?
        ";
        $params = [$classId];

        if ($catFilter > 0) {
            $sql .= " AND m.category_id = ?";
            $params[] = $catFilter;
        }
        if ($subFilter > 0) {
            $sql .= " AND m.subject_id = ?";
            $params[] = $subFilter;
        }
        if (!empty($search)) {
            $sql .= " AND (m.title LIKE ? OR m.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $sql .= " ORDER BY m.created_at DESC";

        $matStmt = $db->prepare($sql);
        $matStmt->execute($params);
        $materials = $matStmt->fetchAll();

        // TVEC Curriculum Modules with Sub-topics (Anu Kotas)
        $modules = $db->query("
            SELECT cm.*,
                   (SELECT COUNT(*) FROM module_topics mt WHERE mt.module_id = cm.id) as topic_count
            FROM curriculum_modules cm
            ORDER BY cm.semester ASC, cm.module_no ASC
        ")->fetchAll();

        $topicsRaw = $db->query("SELECT * FROM module_topics ORDER BY module_id ASC, topic_no ASC")->fetchAll();
        $moduleTopics = [];
        foreach ($topicsRaw as $tp) {
            $moduleTopics[$tp['module_id']][] = $tp;
        }

        // Google Classroom style stream posts (Materials & Assignments chronologically)
        $streamPosts = [];
        $feedMat = $db->prepare("
            SELECT m.id, m.title, m.description, m.material_type, m.file_path, m.original_filename, m.external_url,
                   m.created_at, 'material' as post_type, cat.category_name, cat.icon as category_icon,
                   s.subject_name, s.subject_code, u.name as teacher_name
            FROM materials m
            JOIN categories cat ON m.category_id = cat.id
            JOIN subjects s ON m.subject_id = s.id
            JOIN users u ON m.teacher_id = u.id
            WHERE m.class_id = ?
            ORDER BY m.created_at DESC LIMIT 15
        ");
        $feedMat->execute([$classId]);
        foreach ($feedMat->fetchAll() as $item) {
            $streamPosts[] = $item;
        }

        $feedAsg = $db->prepare("
            SELECT a.id, a.title, a.instructions as description, a.deadline, a.created_at,
                   'assignment' as post_type, 'Assignment' as category_name, 'bi-clipboard-check' as category_icon,
                   s.subject_name, s.subject_code, u.name as teacher_name
            FROM assignments a
            JOIN subjects s ON a.subject_id = s.id
            JOIN users u ON a.teacher_id = u.id
            WHERE a.class_id = ?
            ORDER BY a.created_at DESC LIMIT 15
        ");
        $feedAsg->execute([$classId]);
        foreach ($feedAsg->fetchAll() as $item) {
            $streamPosts[] = $item;
        }

        usort($streamPosts, function($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        $pageTitle = "Learning Materials & Classroom Hub";
        require __DIR__ . '/../Views/student/materials.php';
    }

    public function pastPapers(): void {
        $db = Database::getConnection();
        Auth::requireRole(['student', 'teacher', 'admin']);

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
        $papers = $stmt->fetchAll();

        // Statistics
        $totalPastPapers = (int)$db->query("SELECT COUNT(*) FROM exam_papers WHERE paper_type = 'past_paper'")->fetchColumn();
        $totalModelPapers = (int)$db->query("SELECT COUNT(*) FROM exam_papers WHERE paper_type = 'model_paper'")->fetchColumn();
        $totalMarkingSchemes = (int)$db->query("SELECT COUNT(*) FROM exam_papers WHERE paper_type = 'marking_scheme'")->fetchColumn();
        $sem1Count = (int)$db->query("SELECT COUNT(*) FROM exam_papers WHERE semester = 1")->fetchColumn();
        $sem2Count = (int)$db->query("SELECT COUNT(*) FROM exam_papers WHERE semester = 2")->fetchColumn();

        // Unique modules for filter
        $modules = $db->query("SELECT DISTINCT module_code, subject_title, semester FROM exam_papers ORDER BY semester, module_code")->fetchAll();

        $pageTitle = "Past Papers & Model Papers (Semesters 1 & 2)";
        require_once __DIR__ . '/../Views/student/past_papers.php';
    }

    public function curriculum(): void {
        $db = Database::getConnection();
        Auth::requireRole(['student', 'teacher', 'admin']);

        $semFilter  = (int)($_GET['semester'] ?? 0);
        $typeFilter = trim($_GET['type'] ?? '');
        $search     = trim($_GET['search'] ?? '');

        // Modules query
        $sql = "
            SELECT cm.*,
                   (SELECT COUNT(*) FROM module_topics mt WHERE mt.module_id = cm.id) as topic_count
            FROM curriculum_modules cm
            WHERE 1=1
        ";
        $params = [];

        if ($semFilter > 0) {
            $sql .= " AND cm.semester = ?";
            $params[] = $semFilter;
        }
        if (!empty($typeFilter)) {
            $sql .= " AND cm.module_type = ?";
            $params[] = $typeFilter;
        }
        if (!empty($search)) {
            $sql .= " AND (cm.module_code LIKE ? OR cm.module_title LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $sql .= " ORDER BY cm.semester ASC, cm.module_no ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $modules = $stmt->fetchAll();

        // Topics query
        $topicsRaw = $db->query("SELECT * FROM module_topics ORDER BY module_id ASC, topic_no ASC")->fetchAll();
        $moduleTopics = [];
        foreach ($topicsRaw as $tp) {
            $moduleTopics[$tp['module_id']][] = $tp;
        }

        // Stats
        $totalModules = (int)$db->query("SELECT COUNT(*) FROM curriculum_modules")->fetchColumn();
        $totalTopics = (int)$db->query("SELECT COUNT(*) FROM module_topics")->fetchColumn();
        $sem1ModCount = (int)$db->query("SELECT COUNT(*) FROM curriculum_modules WHERE semester = 1")->fetchColumn();
        $sem2ModCount = (int)$db->query("SELECT COUNT(*) FROM curriculum_modules WHERE semester = 2")->fetchColumn();

        $pageTitle = "NVQ Level 5 Curriculum Syllabus & Sub-Topics";
        require __DIR__ . '/../Views/student/curriculum.php';
    }

    public function assignments(): void {
        $db = Database::getConnection();
        $studentId = Auth::id();

        // Get student class
        $st = $db->prepare("SELECT class_id FROM student_profiles WHERE user_id = ?");
        $st->execute([$studentId]);
        $prof = $st->fetch();
        $classId = (int)($prof['class_id'] ?? 1);

        // Fetch assignments with student submission status
        $sql = "
            SELECT a.*, s.subject_name, s.subject_code, u.name as teacher_name,
                   sub.id as submission_id, sub.file_path, sub.original_filename as sub_orig_name,
                   sub.student_comment, sub.submitted_at, sub.is_late, sub.marks, sub.feedback,
                   sub.status as submission_status
            FROM assignments a
            JOIN subjects s ON a.subject_id = s.id
            JOIN users u ON a.teacher_id = u.id
            LEFT JOIN submissions sub ON a.id = sub.assignment_id AND sub.student_id = ?
            WHERE a.class_id = ?
            ORDER BY a.deadline ASC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$studentId, $classId]);
        $assignments = $stmt->fetchAll();

        $pageTitle = "My Coursework & Assignments";
        require_once __DIR__ . '/../Views/student/assignments.php';
    }

    public function submitAssignment(): void {
        Csrf::checkOrAbort();
        $studentId    = Auth::id();
        $assignmentId = (int)($_POST['assignment_id'] ?? 0);
        $comment      = trim($_POST['student_comment'] ?? '');

        if ($assignmentId <= 0) {
            Flash::error("Invalid assignment.");
            header('Location: index.php?page=student_assignments');
            exit;
        }

        if (empty($_FILES['submission_file']) || $_FILES['submission_file']['error'] === UPLOAD_ERR_NO_FILE) {
            Flash::error("Please choose a file to submit (PDF, DOCX, ZIP).");
            header('Location: index.php?page=student_assignments');
            exit;
        }

        $db = Database::getConnection();

        // Check assignment deadline
        $chk = $db->prepare("SELECT deadline, total_marks FROM assignments WHERE id = ?");
        $chk->execute([$assignmentId]);
        $asg = $chk->fetch();

        if (!$asg) {
            Flash::error("Assignment not found.");
            header('Location: index.php?page=student_assignments');
            exit;
        }

        $isLate = (strtotime($asg['deadline']) < time()) ? 1 : 0;

        // Perform file upload
        $up = Uploader::upload($_FILES['submission_file'], SUBMISSION_UPLOAD_PATH, ['pdf', 'doc', 'docx', 'zip', 'rar', 'txt']);
        if (!$up['success']) {
            Flash::error("Submission failed: " . $up['error']);
            header('Location: index.php?page=student_assignments');
            exit;
        }

        try {
            // Upsert submission (if resubmitting)
            $stmt = $db->prepare("
                INSERT INTO submissions (assignment_id, student_id, file_path, original_filename, student_comment, submitted_at, is_late, status)
                VALUES (?, ?, ?, ?, ?, NOW(), ?, 'submitted')
                ON DUPLICATE KEY UPDATE
                    file_path = VALUES(file_path),
                    original_filename = VALUES(original_filename),
                    student_comment = VALUES(student_comment),
                    submitted_at = NOW(),
                    is_late = VALUES(is_late),
                    status = 'submitted'
            ");
            $stmt->execute([$assignmentId, $studentId, $up['filename'], $up['original_filename'], $comment, $isLate]);

            Auth::logAudit($studentId, 'SUBMIT_ASSIGNMENT', "Submitted assignment ID $assignmentId (" . $up['original_filename'] . ")");
            
            if ($isLate) {
                Flash::warning("Assignment submitted successfully, but marked as LATE (submitted after deadline).");
            } else {
                Flash::success("Assignment submitted successfully! Lecturer has been notified.");
            }
        } catch (Exception $e) {
            Flash::error("Error saving submission record: " . $e->getMessage());
        }

        header('Location: index.php?page=student_assignments');
        exit;
    }

    public function timetable(): void {
        $db = Database::getConnection();
        $studentId = Auth::id();

        $st = $db->prepare("SELECT class_id FROM student_profiles WHERE user_id = ?");
        $st->execute([$studentId]);
        $prof = $st->fetch();
        $classId = (int)($prof['class_id'] ?? 1);

        $stmt = $db->prepare("
            SELECT cs.*, s.subject_name, s.subject_code, u.name as teacher_name
            FROM class_sessions cs
            JOIN subjects s ON cs.subject_id = s.id
            JOIN users u ON cs.teacher_id = u.id
            WHERE cs.class_id = ?
            ORDER BY FIELD(cs.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), cs.start_time ASC
        ");
        $stmt->execute([$classId]);
        $sessions = $stmt->fetchAll();

        $pageTitle = "My Class Timetable";
        require_once __DIR__ . '/../Views/student/timetable.php';
    }

    public function profile(): void {
        $db = Database::getConnection();
        $studentId = Auth::id();

        $stmt = $db->prepare("
            SELECT u.name, u.email, u.profile_image, u.created_at, sp.*, c.class_name, co.course_name, co.nvq_level
            FROM users u
            LEFT JOIN student_profiles sp ON u.id = sp.user_id
            LEFT JOIN classes c ON sp.class_id = c.id
            LEFT JOIN courses co ON sp.course_id = co.id
            WHERE u.id = ?
        ");
        $stmt->execute([$studentId]);
        $student = $stmt->fetch();

        $pageTitle = "My Student Profile";
        require_once __DIR__ . '/../Views/student/profile.php';
    }

    public function updateProfile(): void {
        Csrf::checkOrAbort();
        $studentId = Auth::id();

        $name           = trim($_POST['name'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');
        $whatsappNumber = trim($_POST['whatsapp_number'] ?? '');
        $nic            = trim($_POST['nic'] ?? '');
        $misNumber      = trim($_POST['mis_number'] ?? '');
        $address        = trim($_POST['address'] ?? '');
        $guardianName   = trim($_POST['guardian_name'] ?? '');
        $guardianAddress= trim($_POST['guardian_address'] ?? '');
        $guardianPhone  = trim($_POST['guardian_phone'] ?? '');

        if (empty($name)) {
            Flash::error("Your name cannot be empty.");
            header('Location: index.php?page=profile');
            exit;
        }

        $db = Database::getConnection();
        $profileImage = null;

        // Check for photo upload
        if (!empty($_FILES['profile_photo']['name'])) {
            $up = Uploader::uploadImage($_FILES['profile_photo'], 'students');
            if ($up['success']) {
                $profileImage = $up['path'];
            } else {
                Flash::error("Photo upload error: " . $up['error']);
                header('Location: index.php?page=profile');
                exit;
            }
        }

        try {
            $db->beginTransaction();

            // Update user record
            if ($profileImage) {
                $uStmt = $db->prepare("UPDATE users SET name = ?, profile_image = ? WHERE id = ?");
                $uStmt->execute([$name, $profileImage, $studentId]);
                $_SESSION['user']['profile_image'] = $profileImage;
            } else {
                $uStmt = $db->prepare("UPDATE users SET name = ? WHERE id = ?");
                $uStmt->execute([$name, $studentId]);
            }
            $_SESSION['user']['name'] = $name;

            // Update student_profiles
            $spStmt = $db->prepare("
                UPDATE student_profiles 
                SET phone = ?, whatsapp_number = ?, nic = ?, mis_number = ?, address = ?,
                    guardian_name = ?, guardian_address = ?, guardian_phone = ?
                WHERE user_id = ?
            ");
            $spStmt->execute([
                $phone, $whatsappNumber, $nic, $misNumber, $address,
                $guardianName, $guardianAddress, $guardianPhone,
                $studentId
            ]);

            $db->commit();
            Auth::logAudit($studentId, 'UPDATE_STUDENT_PROFILE', "Student updated their profile details.");
            Flash::success("Your student profile has been updated successfully!");
        } catch (Exception $e) {
            $db->rollBack();
            Flash::error("Failed to update profile: " . $e->getMessage());
        }

        header('Location: index.php?page=profile');
        exit;
    }

    public function attendance(): void {
        $db = Database::getConnection();
        $studentId = Auth::id();

        // Get student's class and course info
        $st = $db->prepare("
            SELECT sp.*, c.class_name, co.course_name 
            FROM student_profiles sp
            LEFT JOIN classes c ON sp.class_id = c.id
            LEFT JOIN courses co ON sp.course_id = co.id
            WHERE sp.user_id = ?
        ");
        $st->execute([$studentId]);
        $profile = $st->fetch();

        // Attendance records
        $attStmt = $db->prepare("
            SELECT a.*, s.subject_name, cs.session_title, cs.day_of_week, cs.start_time, cs.end_time, u.name as teacher_name
            FROM attendance a
            LEFT JOIN class_sessions cs ON a.session_id = cs.id
            LEFT JOIN subjects s ON cs.subject_id = s.id
            LEFT JOIN users u ON cs.teacher_id = u.id
            WHERE a.student_id = ?
            ORDER BY a.date DESC
        ");
        $attStmt->execute([$studentId]);
        $attendanceLogs = $attStmt->fetchAll();

        // Calculate Overall / Semester Stats
        $totalSessions = count($attendanceLogs);
        $presentCount = 0;
        $lateCount = 0;
        $absentCount = 0;

        // Current Month stats
        $currentMonth = date('Y-m');
        $monthTotal = 0;
        $monthPresent = 0;

        // Last 7 days stats
        $weekAgo = date('Y-m-d', strtotime('-7 days'));
        $weekTotal = 0;
        $weekPresent = 0;

        foreach ($attendanceLogs as $log) {
            if ($log['status'] === 'present') {
                $presentCount++;
            } elseif ($log['status'] === 'late') {
                $lateCount++;
            } elseif ($log['status'] === 'absent') {
                $absentCount++;
            }

            // Monthly
            if (str_starts_with($log['date'], $currentMonth)) {
                $monthTotal++;
                if (in_array($log['status'], ['present', 'late'])) {
                    $monthPresent++;
                }
            }

            // Weekly
            if ($log['date'] >= $weekAgo) {
                $weekTotal++;
                if (in_array($log['status'], ['present', 'late'])) {
                    $weekPresent++;
                }
            }
        }

        $overallRate = $totalSessions > 0 ? round((($presentCount + ($lateCount * 0.8)) / $totalSessions) * 100, 1) : 100.0;
        $monthlyRate = $monthTotal > 0 ? round(($monthPresent / max(1, $monthTotal)) * 100, 1) : 100.0;
        $weeklyRate = $weekTotal > 0 ? round(($weekPresent / max(1, $weekTotal)) * 100, 1) : 100.0;

        $pageTitle = "Attendance Tracking & Eligibility";
        require_once __DIR__ . '/../Views/student/attendance.php';
    }

    /**
     * Batch Attendance (Class Monitor Access)
     */
    public function batchAttendance(): void {
        require_once __DIR__ . '/../Helpers/AttendanceManager.php';
        $studentId = Auth::id();
        $classId = 1; // Primary ICT NVQ Level 5 Batch

        // Verify active monitor authorization
        $monitorInfo = AttendanceManager::getActiveMonitorStatus($studentId, $classId);
        if (!$monitorInfo) {
            Flash::error("Access denied. Only appointed Class Monitors can mark batch attendance.");
            header("Location: index.php?page=student_dashboard");
            exit;
        }

        $selectedDate = trim($_GET['date'] ?? date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
            $selectedDate = date('Y-m-d');
        }

        $dayLock = AttendanceManager::getDayLock($classId, $selectedDate);
        $isLocked = ($dayLock && (int)($dayLock['is_locked'] ?? 0) === 1);
        $lockedBySelf = ($isLocked && (int)($dayLock['marked_by_user_id'] ?? 0) === $studentId);
        $holiday = AttendanceManager::getHoliday($selectedDate, $classId);
        $isWeekend = ((int)date('N', strtotime($selectedDate)) >= 6);

        $students = AttendanceManager::getClassStudents($classId);
        $existingAttendance = AttendanceManager::getAttendanceForDate($classId, $selectedDate);

        $className = "NVTI Baddegama ICT L5 - Batch 2026";
        $pageTitle = "Batch Attendance Sheet - Monitor Access";
        require_once __DIR__ . '/../Views/student/batch_attendance.php';
    }

    /**
     * Save Batch Attendance (Class Monitor Action)
     */
    public function saveBatchAttendance(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=student_batch_attendance");
            exit;
        }

        Csrf::validate();
        require_once __DIR__ . '/../Helpers/AttendanceManager.php';

        $studentId = Auth::id();
        $classId = 1;

        // Verify monitor authorization
        $monitorInfo = AttendanceManager::getActiveMonitorStatus($studentId, $classId);
        if (!$monitorInfo) {
            Flash::error("Unauthorized access.");
            header("Location: index.php?page=student_dashboard");
            exit;
        }

        $date = trim($_POST['date'] ?? date('Y-m-d'));
        $attendanceData = $_POST['attendance'] ?? [];

        if (empty($attendanceData) || !is_array($attendanceData)) {
            Flash::error("No attendance data submitted.");
            header("Location: index.php?page=student_batch_attendance&date=" . urlencode($date));
            exit;
        }

        $result = AttendanceManager::saveBatchAttendance($classId, $date, $attendanceData, $studentId, 'student');

        if (!$result['success']) {
            Flash::error($result['error'] ?? "Failed to save attendance.");
        } else {
            Flash::success("Attendance for " . htmlspecialchars($date) . " has been successfully submitted and locked for the batch.");
        }

        header("Location: index.php?page=student_batch_attendance&date=" . urlencode($date));
        exit;
    }

    /**
     * Student Verification Documents & Certificates Portal
     */
    public function documents(): void {
        require_once __DIR__ . '/../Helpers/DocumentManager.php';

        $studentId = Auth::id();
        $db = Database::getConnection();

        // Student Profile Info
        $stmt = $db->prepare("
            SELECT sp.*, c.class_name, co.course_name
            FROM student_profiles sp
            LEFT JOIN classes c ON sp.class_id = c.id
            LEFT JOIN courses co ON sp.course_id = co.id
            WHERE sp.user_id = ?
        ");
        $stmt->execute([$studentId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        $docTypes = DocumentManager::getDocTypes();
        $summary = DocumentManager::getStudentSummary($studentId);

        $pageTitle = "My Verification Documents & Certificates";
        require_once __DIR__ . '/../Views/student/documents.php';
    }

    /**
     * Handle Document Upload for a specific certificate
     */
    public function uploadDocument(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=student_documents");
            exit;
        }

        Csrf::validate();
        require_once __DIR__ . '/../Helpers/DocumentManager.php';

        $studentId = Auth::id();
        $docType = trim($_POST['doc_type'] ?? '');
        $file = $_FILES['document_file'] ?? null;

        if (!$file || empty($file['name'])) {
            Flash::error("Please choose a valid PDF or image file to upload.");
            header("Location: index.php?page=student_documents");
            exit;
        }

        $res = DocumentManager::uploadDocument($studentId, $docType, $file);

        if (!$res['success']) {
            Flash::error($res['error'] ?? "Failed to upload document.");
        } else {
            Flash::success("{$res['title']} has been successfully uploaded and submitted for verification!");
        }

        header("Location: index.php?page=student_documents");
        exit;
    }
}
