<?php
/**
 * Teacher Controller
 * NVTI Baddegama National Diploma Portal
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Csrf.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Helpers/Sanitizer.php';
require_once __DIR__ . '/../Helpers/Uploader.php';

class TeacherController {

    public function __construct() {
        Auth::requireRole(['teacher', 'admin']);
    }

    public function dashboard(): void {
        $db = Database::getConnection();
        $teacherId = Auth::id();

        // Teacher profile & slot
        $stmt = $db->prepare("SELECT * FROM teacher_profiles WHERE user_id = ?");
        $stmt->execute([$teacherId]);
        $profile = $stmt->fetch();

        // Assigned Subjects
        $subStmt = $db->prepare("
            SELECT s.id, s.subject_name, s.subject_code, c.id as class_id, c.class_name
            FROM teacher_assignments ta
            JOIN class_subjects cs ON ta.class_subject_id = cs.id
            JOIN subjects s ON cs.subject_id = s.id
            JOIN classes c ON cs.class_id = c.id
            WHERE ta.teacher_id = ?
        ");
        $subStmt->execute([$teacherId]);
        $assignedSubjects = $subStmt->fetchAll();

        // Counts
        $matCountStmt = $db->prepare("SELECT COUNT(*) FROM materials WHERE teacher_id = ?");
        $matCountStmt->execute([$teacherId]);
        $materialsCount = $matCountStmt->fetchColumn();

        $asgCountStmt = $db->prepare("SELECT COUNT(*) FROM assignments WHERE teacher_id = ?");
        $asgCountStmt->execute([$teacherId]);
        $assignmentsCount = $asgCountStmt->fetchColumn();

        $pendingGradingStmt = $db->prepare("
            SELECT COUNT(*) 
            FROM submissions sub
            JOIN assignments a ON sub.assignment_id = a.id
            WHERE a.teacher_id = ? AND sub.status = 'submitted'
        ");
        $pendingGradingStmt->execute([$teacherId]);
        $pendingGradingCount = $pendingGradingStmt->fetchColumn();

        // Timetable sessions
        $sessStmt = $db->prepare("
            SELECT cs.*, s.subject_name, s.subject_code, c.class_name
            FROM class_sessions cs
            JOIN subjects s ON cs.subject_id = s.id
            JOIN classes c ON cs.class_id = c.id
            WHERE cs.teacher_id = ?
            ORDER BY FIELD(cs.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), cs.start_time ASC
        ");
        $sessStmt->execute([$teacherId]);
        $sessions = $sessStmt->fetchAll();

        // Recent Submissions
        $subListStmt = $db->prepare("
            SELECT s.*, u.name as student_name, sp.student_code, a.title as assignment_title, a.total_marks
            FROM submissions s
            JOIN users u ON s.student_id = u.id
            LEFT JOIN student_profiles sp ON u.id = sp.user_id
            JOIN assignments a ON s.assignment_id = a.id
            WHERE a.teacher_id = ?
            ORDER BY s.submitted_at DESC LIMIT 5
        ");
        $subListStmt->execute([$teacherId]);
        $recentSubmissions = $subListStmt->fetchAll();

        $pageTitle = "Teacher Dashboard";
        require_once __DIR__ . '/../Views/teacher/dashboard.php';
    }

    public function materials(): void {
        $db = Database::getConnection();
        $teacherId = Auth::id();

        // Categories
        $categories = $db->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();

        // Assigned Subjects & Classes for this Teacher
        $subStmt = $db->prepare("
            SELECT s.id as subject_id, s.subject_name, s.subject_code, c.id as class_id, c.class_name
            FROM teacher_assignments ta
            JOIN class_subjects cs ON ta.class_subject_id = cs.id
            JOIN subjects s ON cs.subject_id = s.id
            JOIN classes c ON cs.class_id = c.id
            WHERE ta.teacher_id = ?
        ");
        $subStmt->execute([$teacherId]);
        $assignedClasses = $subStmt->fetchAll();

        // Materials uploaded by this teacher
        $matStmt = $db->prepare("
            SELECT m.*, cat.category_name, cat.icon as category_icon, s.subject_name, s.subject_code, cl.class_name
            FROM materials m
            JOIN categories cat ON m.category_id = cat.id
            JOIN subjects s ON m.subject_id = s.id
            JOIN classes cl ON m.class_id = cl.id
            WHERE m.teacher_id = ?
            ORDER BY m.created_at DESC
        ");
        $matStmt->execute([$teacherId]);
        $materials = $matStmt->fetchAll();

        $pageTitle = "Upload & Manage Learning Materials";
        require_once __DIR__ . '/../Views/teacher/materials.php';
    }

    public function uploadMaterial(): void {
        Csrf::checkOrAbort();
        $teacherId = Auth::id();
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $categoryId  = (int)($_POST['category_id'] ?? 0);
        $subjectClass= $_POST['subject_class'] ?? ''; // format: subjectId_classId
        $materialType= $_POST['material_type'] ?? 'file';

        if (empty($title) || empty($categoryId) || empty($subjectClass)) {
            Flash::error("Please fill in the title, category, and subject.");
            header('Location: index.php?page=teacher_materials');
            exit;
        }

        [$subjectId, $classId] = explode('_', $subjectClass);
        $subjectId = (int)$subjectId;
        $classId   = (int)$classId;

        if (!Csrf::claimSubmission('teacher_upload_material', [$teacherId, $classId, $subjectId, strtolower($title), $materialType], 120)) {
            Flash::info("This study material was already submitted and published.");
            header('Location: index.php?page=teacher_materials');
            exit;
        }

        $db = Database::getConnection();
        $recentDup = $db->prepare("
            SELECT id FROM materials
            WHERE class_id = ? AND subject_id = ? AND LOWER(TRIM(title)) = LOWER(?)
              AND created_at >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)
            LIMIT 1
        ");
        $recentDup->execute([$classId, $subjectId, $title]);
        if ($recentDup->fetchColumn()) {
            Flash::info("Study material <strong>" . htmlspecialchars($title) . "</strong> was already posted moments ago.");
            header('Location: index.php?page=teacher_materials');
            exit;
        }
        $filePath = null;
        $origName = null;
        $url = null;
        $fileSize = 0;

        if ($materialType === 'file') {
            if (empty($_FILES['file_upload']) || $_FILES['file_upload']['error'] === UPLOAD_ERR_NO_FILE) {
                Flash::error("Please choose a file to upload.");
                header('Location: index.php?page=teacher_materials');
                exit;
            }

            $uploadRes = Uploader::upload($_FILES['file_upload'], MATERIAL_UPLOAD_PATH);
            if (!$uploadRes['success']) {
                Flash::error("Upload failed: " . $uploadRes['error']);
                header('Location: index.php?page=teacher_materials');
                exit;
            }

            $filePath = $uploadRes['filename'];
            $origName = $uploadRes['original_filename'];
            $fileSize = $uploadRes['size'];
        } elseif ($materialType === 'youtube' || $materialType === 'url') {
            $url = trim($_POST['external_url'] ?? '');
            if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                Flash::error("Please provide a valid URL.");
                header('Location: index.php?page=teacher_materials');
                exit;
            }
        }

        $stmt = $db->prepare("
            INSERT INTO materials (teacher_id, class_id, subject_id, category_id, title, description, material_type, file_path, original_filename, external_url, file_size_bytes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$teacherId, $classId, $subjectId, $categoryId, $title, $description, $materialType, $filePath, $origName, $url, $fileSize]);

        Auth::logAudit($teacherId, 'UPLOAD_MATERIAL', "Uploaded material: $title ($materialType)");

        // Google Classroom style: Notify all enrolled students via email (if selected & enabled)
        $emailPromptPresent = isset($_POST['email_prompt_present']);
        $wantsEmail = $emailPromptPresent ? !empty($_POST['send_email_notification']) : true;

        $subStmt = $db->prepare("SELECT subject_name FROM subjects WHERE id = ?");
        $subStmt->execute([$subjectId]);
        $subRow = $subStmt->fetch();
        $subName = $subRow['subject_name'] ?? 'Diploma Module';
        $teacherName = Auth::user()['name'] ?? 'Lecturer';

        if ($wantsEmail) {
            $notified = Mailer::notifyNewMaterial($classId, $title, $subName, $teacherName, $description, $materialType);
        } else {
            EmailSettings::logEmail(
                "Class #{$classId} (Enrolled Students)",
                'Batch Students',
                "New Material: {$title} ({$subName})",
                '',
                'material',
                'skipped',
                'Skipped: Lecturer unchecked "Notify students via email" on upload.',
                $teacherId
            );
            $notified = 0;
        }

        if ($notified > 0) {
            Flash::success("Study material '$title' published and $notified students were notified via Email!");
        } else {
            Flash::success("Study material '$title' published successfully!");
        }
        header('Location: index.php?page=teacher_materials');
        exit;
    }

    public function deleteMaterial(): void {
        Csrf::checkOrAbort();
        $id = (int)($_POST['material_id'] ?? 0);
        $teacherId = Auth::id();

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT file_path FROM materials WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$id, $teacherId]);
        $mat = $stmt->fetch();

        if ($mat) {
            if (!empty($mat['file_path'])) {
                $target = MATERIAL_UPLOAD_PATH . DIRECTORY_SEPARATOR . $mat['file_path'];
                if (file_exists($target)) {
                    @unlink($target);
                }
            }
            $del = $db->prepare("DELETE FROM materials WHERE id = ?");
            $del->execute([$id]);

            Auth::logAudit($teacherId, 'DELETE_MATERIAL', "Deleted material ID $id");
            Flash::success("Material deleted successfully.");
        }
        header('Location: index.php?page=teacher_materials');
        exit;
    }

    /**
     * Teacher Toggle Material Visibility
     */
    public function toggleMaterialVisibility(): void {
        Csrf::checkOrAbort();
        $id = (int)($_POST['material_id'] ?? 0);
        $teacherId = Auth::id();

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT title, is_hidden FROM materials WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$id, $teacherId]);
        $mat = $stmt->fetch();

        if ($mat) {
            $newStatus = ((int)$mat['is_hidden'] === 1) ? 0 : 1;
            $upd = $db->prepare("UPDATE materials SET is_hidden = ? WHERE id = ?");
            $upd->execute([$newStatus, $id]);

            $statusLabel = ($newStatus === 1) ? 'Hidden from students' : 'Visible to students';
            Auth::logAudit($teacherId, 'TOGGLE_MATERIAL_VISIBILITY', "Changed visibility of '{$mat['title']}' to $statusLabel");
            Flash::success("Material '{$mat['title']}' is now {$statusLabel}.");
        }

        header('Location: index.php?page=teacher_materials');
        exit;
    }

    public function assignments(): void {
        require_once __DIR__ . '/../Helpers/AttendanceManager.php';
        $db = Database::getConnection();
        $teacherId = Auth::id();

        $activeBatch = AttendanceManager::ensureActiveBatch($db, 1);
        $activeClassId = (int)($activeBatch['id'] ?? 1);
        AttendanceManager::syncBatchSubjects($db, $activeClassId);

        // Active batch subjects (always available for lecturers in current intake)
        $activeSubStmt = $db->prepare("
            SELECT s.id as subject_id, s.subject_name, s.subject_code, c.id as class_id, c.class_name, c.status as class_status
            FROM class_subjects cs
            JOIN subjects s ON cs.subject_id = s.id
            JOIN classes c ON cs.class_id = c.id
            WHERE cs.class_id = ? AND s.status = 'active'
            ORDER BY s.id ASC
        ");
        $activeSubStmt->execute([$activeClassId]);
        $assignedClasses = $activeSubStmt->fetchAll();

        // Merge any explicitly assigned teacher_assignments from other active/archived batches without duplicates
        $subStmt = $db->prepare("
            SELECT s.id as subject_id, s.subject_name, s.subject_code, c.id as class_id, c.class_name, c.status as class_status
            FROM teacher_assignments ta
            JOIN class_subjects cs ON ta.class_subject_id = cs.id
            JOIN subjects s ON cs.subject_id = s.id
            JOIN classes c ON cs.class_id = c.id
            WHERE ta.teacher_id = ? AND cs.class_id != ?
            ORDER BY c.id DESC, s.id ASC
        ");
        $subStmt->execute([$teacherId, $activeClassId]);
        $seenKeys = [];
        foreach ($assignedClasses as $ac) {
            $seenKeys[$ac['subject_id'] . '_' . $ac['class_id']] = true;
        }
        foreach ($subStmt->fetchAll() as $extra) {
            $k = $extra['subject_id'] . '_' . $extra['class_id'];
            if (!isset($seenKeys[$k])) {
                $assignedClasses[] = $extra;
                $seenKeys[$k] = true;
            }
        }

        // Assignments created by teacher (active batch first)
        $asgStmt = $db->prepare("
            SELECT a.*, s.subject_name, s.subject_code, c.class_name,
                   (SELECT COUNT(*) FROM submissions sub WHERE sub.assignment_id = a.id) as total_submissions,
                   (SELECT COUNT(*) FROM submissions sub WHERE sub.assignment_id = a.id AND sub.status = 'graded') as graded_count
            FROM assignments a
            JOIN subjects s ON a.subject_id = s.id
            JOIN classes c ON a.class_id = c.id
            WHERE a.teacher_id = ?
            ORDER BY (a.class_id = ?) DESC, a.deadline DESC, a.created_at DESC
        ");
        $asgStmt->execute([$teacherId, $activeClassId]);
        $assignments = $asgStmt->fetchAll();

        $pageTitle = "Teacher - Assignments";
        require_once __DIR__ . '/../Views/teacher/assignments.php';
    }

    public function createAssignment(): void {
        Csrf::checkOrAbort();
        $teacherId   = Auth::id();
        $title       = trim($_POST['title'] ?? '');
        $subjectClass= $_POST['subject_class'] ?? '';
        $instructions= trim($_POST['instructions'] ?? '');
        $deadline    = $_POST['deadline'] ?? '';
        $totalMarks  = (float)($_POST['total_marks'] ?? 100);

        if (empty($title) || empty($subjectClass) || empty($instructions) || empty($deadline)) {
            Flash::error("Please fill in all required assignment details.");
            header('Location: index.php?page=teacher_assignments');
            exit;
        }

        [$subjectId, $classId] = explode('_', $subjectClass);
        $subjectId = (int)$subjectId;
        $classId   = (int)$classId;

        if (!Csrf::claimSubmission('teacher_create_assignment', [$teacherId, $classId, $subjectId, strtolower($title), $deadline], 120)) {
            Flash::info("Assignment <strong>" . htmlspecialchars($title) . "</strong> was already published.");
            header('Location: index.php?page=teacher_assignments');
            exit;
        }

        $db = Database::getConnection();
        $recentDup = $db->prepare("
            SELECT id FROM assignments
            WHERE class_id = ? AND subject_id = ? AND LOWER(TRIM(title)) = LOWER(?)
              AND created_at >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)
            LIMIT 1
        ");
        $recentDup->execute([$classId, $subjectId, $title]);
        if ($recentDup->fetchColumn()) {
            Flash::info("Assignment <strong>" . htmlspecialchars($title) . "</strong> was already created moments ago.");
            header('Location: index.php?page=teacher_assignments');
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
            INSERT INTO assignments (teacher_id, class_id, subject_id, title, instructions, reference_file, total_marks, deadline)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$teacherId, $classId, $subjectId, $title, $instructions, $refFile, $totalMarks, $deadline]);

        Auth::logAudit($teacherId, 'CREATE_ASSIGNMENT', "Created assignment: $title with deadline $deadline");

        // Google Classroom style: Notify all enrolled students via email (if selected & enabled)
        $emailPromptPresent = isset($_POST['email_prompt_present']);
        $wantsEmail = $emailPromptPresent ? !empty($_POST['send_email_notification']) : true;

        $subStmt = $db->prepare("SELECT subject_name FROM subjects WHERE id = ?");
        $subStmt->execute([$subjectId]);
        $subRow = $subStmt->fetch();
        $subName = $subRow['subject_name'] ?? 'Diploma Module';
        $teacherName = Auth::user()['name'] ?? 'Lecturer';

        if ($wantsEmail) {
            $notified = Mailer::notifyNewAssignment($classId, $title, $subName, $teacherName, $deadline, (int)$totalMarks, $instructions);
        } else {
            EmailSettings::logEmail(
                "Class #{$classId} (Enrolled Students)",
                'Batch Students',
                "New Assignment: {$title} ({$subName})",
                '',
                'assignment',
                'skipped',
                'Skipped: Lecturer unchecked "Notify students via email" when creating assignment.',
                $teacherId
            );
            $notified = 0;
        }

        if ($notified > 0) {
            Flash::success("Assignment '$title' created and $notified students were notified via Email!");
        } else {
            Flash::success("Assignment '$title' created successfully!");
        }
        header('Location: index.php?page=teacher_assignments');
        exit;
    }

    /**
     * Teacher Update / Extend Assignment Deadline
     */
    public function updateAssignmentDeadline(): void {
        Csrf::checkOrAbort();
        $db = Database::getConnection();
        $teacherId    = Auth::id();
        $assignmentId = (int)($_POST['assignment_id'] ?? 0);
        $newDeadline  = trim($_POST['deadline'] ?? '');

        if ($assignmentId <= 0 || empty($newDeadline)) {
            Flash::error("Please provide a valid new deadline date and time.");
            header('Location: index.php?page=teacher_assignments');
            exit;
        }

        $stmt = $db->prepare("UPDATE assignments SET deadline = ?, status = 'active' WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$newDeadline, $assignmentId, $teacherId]);

        if ($stmt->rowCount() > 0) {
            Auth::logAudit($teacherId, 'TEACHER_UPDATE_DEADLINE', "Updated assignment ID $assignmentId deadline to $newDeadline");
            Flash::success("Deadline successfully extended/updated to <strong>" . date('M d, Y h:i A', strtotime($newDeadline)) . "</strong>!");
        } else {
            Flash::error("Could not update deadline (assignment not found or permission denied).");
        }
        header('Location: index.php?page=teacher_assignments');
        exit;
    }

    /**
     * Teacher Delete Own Assignment
     */
    public function deleteAssignment(): void {
        Csrf::checkOrAbort();
        $db = Database::getConnection();
        $teacherId    = Auth::id();
        $assignmentId = (int)($_POST['assignment_id'] ?? 0);

        if ($assignmentId > 0) {
            $fStmt = $db->prepare("SELECT reference_file FROM assignments WHERE id = ? AND teacher_id = ?");
            $fStmt->execute([$assignmentId, $teacherId]);
            $refFile = $fStmt->fetchColumn();

            $del = $db->prepare("DELETE FROM assignments WHERE id = ? AND teacher_id = ?");
            $del->execute([$assignmentId, $teacherId]);

            if ($del->rowCount() > 0) {
                if (!empty($refFile)) {
                    @unlink(ASSIGNMENT_UPLOAD_PATH . DIRECTORY_SEPARATOR . $refFile);
                }
                Auth::logAudit($teacherId, 'TEACHER_DELETE_ASSIGNMENT', "Deleted assignment ID $assignmentId");
                Flash::success("Assignment has been deleted.");
            } else {
                Flash::error("Assignment not found or permission denied.");
            }
        }
        header('Location: index.php?page=teacher_assignments');
        exit;
    }

    public function submissions(): void {
        $db = Database::getConnection();
        $teacherId = Auth::id();
        $assignmentId = (int)($_GET['assignment_id'] ?? 0);

        // Get all assignments for dropdown
        $allAsgStmt = $db->prepare("
            SELECT a.id, a.title, s.subject_code, c.class_name
            FROM assignments a
            JOIN subjects s ON a.subject_id = s.id
            JOIN classes c ON a.class_id = c.id
            WHERE a.teacher_id = ?
            ORDER BY a.created_at DESC
        ");
        $allAsgStmt->execute([$teacherId]);
        $teacherAssignments = $allAsgStmt->fetchAll();

        // If no assignment selected, default to latest
        if ($assignmentId === 0 && !empty($teacherAssignments)) {
            $assignmentId = (int)$teacherAssignments[0]['id'];
        }

        $selectedAssignment = null;
        $submissions = [];

        if ($assignmentId > 0) {
            $curStmt = $db->prepare("SELECT * FROM assignments WHERE id = ? AND teacher_id = ?");
            $curStmt->execute([$assignmentId, $teacherId]);
            $selectedAssignment = $curStmt->fetch();

            if ($selectedAssignment) {
                $subStmt = $db->prepare("
                    SELECT sub.*, u.name as student_name, u.email as student_email, sp.student_code, sp.phone
                    FROM submissions sub
                    JOIN users u ON sub.student_id = u.id
                    LEFT JOIN student_profiles sp ON u.id = sp.user_id
                    WHERE sub.assignment_id = ?
                    ORDER BY sub.submitted_at DESC
                ");
                $subStmt->execute([$assignmentId]);
                $submissions = $subStmt->fetchAll();
            }
        }

        $pageTitle = "Review Submissions & Grade";
        require_once __DIR__ . '/../Views/teacher/submissions.php';
    }

    public function gradeSubmission(): void {
        Csrf::checkOrAbort();
        $teacherId     = Auth::id();
        $submissionId  = (int)($_POST['submission_id'] ?? 0);
        $assignmentId  = (int)($_POST['assignment_id'] ?? 0);
        $marks         = (float)($_POST['marks'] ?? 0);
        $feedback      = trim($_POST['feedback'] ?? '');
        $status        = $_POST['status'] ?? 'graded';

        $db = Database::getConnection();

        // Validate submission ownership and total marks
        $stmt = $db->prepare("
            SELECT sub.id, a.total_marks, a.id as asg_id
            FROM submissions sub
            JOIN assignments a ON sub.assignment_id = a.id
            WHERE sub.id = ? AND a.teacher_id = ?
        ");
        $stmt->execute([$submissionId, $teacherId]);
        $rec = $stmt->fetch();

        if (!$rec) {
            Flash::error("Submission not found or unauthorized.");
            header('Location: index.php?page=teacher_submissions');
            exit;
        }

        if ($marks < 0 || $marks > (float)$rec['total_marks']) {
            Flash::error("Marks must be between 0 and " . $rec['total_marks'] . ".");
            header('Location: index.php?page=teacher_submissions&assignment_id=' . $rec['asg_id']);
            exit;
        }

        $upStmt = $db->prepare("
            UPDATE submissions 
            SET marks = ?, feedback = ?, graded_by = ?, graded_at = NOW(), status = ?
            WHERE id = ?
        ");
        $upStmt->execute([$marks, $feedback, $teacherId, $status, $submissionId]);

        Auth::logAudit($teacherId, 'GRADE_SUBMISSION', "Graded submission ID $submissionId: $marks marks");
        Flash::success("Student submission graded successfully with $marks marks!");
        header('Location: index.php?page=teacher_submissions&assignment_id=' . $rec['asg_id']);
        exit;
    }

    public function timetable(): void {
        $db = Database::getConnection();
        $teacherId = Auth::id();

        $stmt = $db->prepare("
            SELECT cs.*, s.subject_name, s.subject_code, c.class_name
            FROM class_sessions cs
            JOIN subjects s ON cs.subject_id = s.id
            JOIN classes c ON cs.class_id = c.id
            WHERE cs.teacher_id = ?
            ORDER BY FIELD(cs.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), cs.start_time ASC
        ");
        $stmt->execute([$teacherId]);
        $sessions = $stmt->fetchAll();

        $pageTitle = "My Teaching Schedule";
        require_once __DIR__ . '/../Views/teacher/timetable.php';
    }

    /**
     * Teacher Attendance Management Portal
     */
    public function attendance(): void {
        AdminAccess::requireMainAdmin(true);
        require_once __DIR__ . '/../Helpers/AttendanceManager.php';
        $teacherId = Auth::id();

        // Dynamically detect active class if not specified
        $allBatches = AttendanceManager::getAllBatches();
        $requestedClassId = (int)($_GET['class_id'] ?? 0);
        if ($requestedClassId > 0) {
            $classId = $requestedClassId;
        } else {
            $classId = AttendanceManager::getActiveClassId();
        }

        $currentBatch = AttendanceManager::getBatchById($classId) ?: AttendanceManager::getActiveBatch();
        $isBatchArchived = ($currentBatch && $currentBatch['status'] === 'archived');

        $tab = trim($_GET['tab'] ?? 'daily');
        $selectedDate = trim($_GET['date'] ?? date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
            $selectedDate = date('Y-m-d');
        }

        $selectedMonth = trim($_GET['month'] ?? date('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
            $selectedMonth = date('Y-m');
        }

        // Daily tab data (strictly active students if batch is active, or full list if viewing historical archived batch)
        $dayLock = AttendanceManager::getDayLock($classId, $selectedDate);
        $students = AttendanceManager::getClassStudents($classId, $isBatchArchived);
        $existingAttendance = AttendanceManager::getAttendanceForDate($classId, $selectedDate);
        $holiday = AttendanceManager::getHoliday($selectedDate, $classId);
        $isWeekend = ((int)date('N', strtotime($selectedDate)) >= 6);

        // Monitors tab data
        $monitors = AttendanceManager::getClassMonitors($classId);

        // Monthly matrix tab data
        $monthlyData = AttendanceManager::getMonthlyMatrix($classId, $selectedMonth, $isBatchArchived);
        $monthlyHolidays = AttendanceManager::getMonthlyHolidays($selectedMonth, $classId);

        $className = $currentBatch['class_name'] ?? "NVTI Baddegama National Diploma";
        $pageTitle = "Attendance & Class Monitor Management - " . $className;
        require_once __DIR__ . '/../Views/teacher/attendance.php';
    }

    /**
     * Teacher Save Attendance (Teacher can always save / override for active batches)
     */
    public function saveAttendance(): void {
        AdminAccess::requireMainAdmin(true);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=teacher_attendance");
            exit;
        }

        Csrf::validate();
        require_once __DIR__ . '/../Helpers/AttendanceManager.php';
        $teacherId = Auth::id();
        $classId = (int)($_POST['class_id'] ?? 0);
        if ($classId <= 0) {
            $classId = AttendanceManager::getActiveClassId();
        }

        $batch = AttendanceManager::getBatchById($classId);
        if ($batch && $batch['status'] === 'archived') {
            Flash::error("This batch is archived (Old Batch). Attendance records for graduated batches are read-only and preserved permanently.");
            header("Location: index.php?page=teacher_attendance&class_id=" . $classId);
            exit;
        }

        $date = trim($_POST['date'] ?? date('Y-m-d'));
        $attendanceData = $_POST['attendance'] ?? [];

        if (empty($attendanceData) || !is_array($attendanceData)) {
            Flash::error("No attendance data submitted.");
            header("Location: index.php?page=teacher_attendance&class_id=" . $classId . "&date=" . urlencode($date));
            exit;
        }

        $userRole = Auth::role(); // 'admin' or 'teacher'
        $result = AttendanceManager::saveBatchAttendance($classId, $date, $attendanceData, $teacherId, $userRole);
        if (!$result['success']) {
            Flash::error($result['error'] ?? "Failed to save attendance.");
        } else {
            Auth::logAudit($teacherId, 'VERIFY_ATTENDANCE', "Verified and saved attendance for class $classId on $date (Role: $userRole)");
            Flash::success("Attendance for " . htmlspecialchars($date) . " has been officially verified, approved, and locked into daily records.");
        }

        header("Location: index.php?page=teacher_attendance&class_id=" . $classId . "&date=" . urlencode($date));
        exit;
    }

    /**
     * Unlock Day's Attendance so Monitor can re-mark
     */
    public function unlockAttendance(): void {
        AdminAccess::requireMainAdmin(true);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=teacher_attendance");
            exit;
        }

        Csrf::validate();
        require_once __DIR__ . '/../Helpers/AttendanceManager.php';
        $teacherId = Auth::id();
        $classId = (int)($_POST['class_id'] ?? 0);
        if ($classId <= 0) {
            $classId = AttendanceManager::getActiveClassId();
        }
        $date = trim($_POST['date'] ?? '');

        if ($date) {
            AttendanceManager::unlockDay($classId, $date, $teacherId);
            Auth::logAudit($teacherId, 'UNLOCK_ATTENDANCE', "Unlocked attendance for class $classId on $date");
            Flash::success("Attendance sheet for " . htmlspecialchars($date) . " has been unlocked. Class monitors can now edit.");
        }

        header("Location: index.php?page=teacher_attendance&class_id=" . $classId . "&date=" . urlencode($date));
        exit;
    }

    /**
     * Declare Special Holiday or Institute Closure with Reason
     */
    public function declareHoliday(): void {
        AdminAccess::requireMainAdmin(true);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=teacher_attendance");
            exit;
        }

        Csrf::validate();
        require_once __DIR__ . '/../Helpers/AttendanceManager.php';
        $userId = Auth::id();
        $classId = (int)($_POST['class_id'] ?? 0);
        if ($classId <= 0) {
            $classId = AttendanceManager::getActiveClassId();
        }
        $date = trim($_POST['date'] ?? '');
        $name = trim($_POST['holiday_name'] ?? '');
        $type = trim($_POST['holiday_type'] ?? 'public_holiday');
        $desc = trim($_POST['description'] ?? '');

        if (!empty($date) && !empty($name)) {
            AttendanceManager::setHoliday($date, $name, $type, $desc, $classId, $userId);
            Auth::logAudit($userId, 'DECLARE_HOLIDAY', "Declared $date as holiday: $name ($type) for class $classId");
            Flash::success("Date " . htmlspecialchars($date) . " has been officially recorded as a Holiday / Closure: " . htmlspecialchars($name));
        } else {
            Flash::error("Please provide a holiday name/reason and date.");
        }

        header("Location: index.php?page=teacher_attendance&class_id=" . $classId . "&date=" . urlencode($date));
        exit;
    }

    /**
     * Remove Holiday Declaration
     */
    public function removeHoliday(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=teacher_attendance");
            exit;
        }

        Csrf::validate();
        require_once __DIR__ . '/../Helpers/AttendanceManager.php';
        $userId = Auth::id();
        $classId = (int)($_POST['class_id'] ?? 0);
        if ($classId <= 0) {
            $classId = AttendanceManager::getActiveClassId();
        }
        $date = trim($_POST['date'] ?? '');

        if ($date) {
            AttendanceManager::removeHoliday($date, $classId);
            Auth::logAudit($userId, 'REMOVE_HOLIDAY', "Removed holiday declaration for $date (class $classId)");
            Flash::success("Holiday declaration for " . htmlspecialchars($date) . " has been removed. Regular attendance can now be marked.");
        }

        header("Location: index.php?page=teacher_attendance&class_id=" . $classId . "&date=" . urlencode($date));
        exit;
    }

    /**
     * Assign or Update Class Monitor
     */
    public function assignMonitor(): void {
        AdminAccess::requireMainAdmin(true);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=teacher_attendance&tab=monitors");
            exit;
        }

        Csrf::validate();
        require_once __DIR__ . '/../Helpers/AttendanceManager.php';
        $teacherId = Auth::id();
        $classId = (int)($_POST['class_id'] ?? 0);
        if ($classId <= 0) {
            $classId = AttendanceManager::getActiveClassId();
        }
        $studentId = (int)($_POST['student_id'] ?? 0);
        $role = in_array($_POST['monitor_role'] ?? '', ['primary', 'backup']) ? $_POST['monitor_role'] : 'primary';
        $status = in_array($_POST['status'] ?? '', ['active', 'disabled']) ? $_POST['status'] : 'active';

        if ($studentId > 0) {
            AttendanceManager::setMonitor($classId, $studentId, $role, $status, $teacherId);
            Auth::logAudit($teacherId, 'ASSIGN_MONITOR', "Assigned student $studentId as $role monitor ($status) for class $classId");
            Flash::success("Class Monitor successfully assigned.");
        } else {
            Flash::error("Please select a valid student.");
        }

        header("Location: index.php?page=teacher_attendance&class_id=" . $classId . "&tab=monitors");
        exit;
    }

    /**
     * Toggle Monitor Status (Active / Disabled)
     */
    public function toggleMonitor(): void {
        AdminAccess::requireMainAdmin(true);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=teacher_attendance&tab=monitors");
            exit;
        }

        Csrf::validate();
        require_once __DIR__ . '/../Helpers/AttendanceManager.php';
        $teacherId = Auth::id();
        $classId = (int)($_POST['class_id'] ?? 0);
        if ($classId <= 0) {
            $classId = AttendanceManager::getActiveClassId();
        }
        $monitorId = (int)($_POST['monitor_id'] ?? 0);
        $newStatus = ($_POST['status'] === 'active') ? 'active' : 'disabled';

        if ($monitorId > 0) {
            AttendanceManager::toggleMonitorStatus($monitorId, $newStatus);
            Auth::logAudit($teacherId, 'TOGGLE_MONITOR', "Toggled monitor $monitorId to $newStatus");
            Flash::success("Monitor status updated to " . ucfirst($newStatus) . ". All past attendance marked by this student is preserved.");
        }

        header("Location: index.php?page=teacher_attendance&class_id=" . $classId . "&tab=monitors");
        exit;
    }

    /**
     * Export Monthly Attendance to CSV (Official NVTI Benchmark Report)
     */
    public function exportAttendanceCsv(): void {
        AdminAccess::requireMainAdmin(true);
        require_once __DIR__ . '/../Helpers/AttendanceManager.php';
        $classId = (int)($_GET['class_id'] ?? 0);
        if ($classId <= 0) {
            $classId = AttendanceManager::getActiveClassId();
        }
        $batch = AttendanceManager::getBatchById($classId) ?: AttendanceManager::getActiveBatch();
        $isArchived = ($batch && $batch['status'] === 'archived');

        $selectedMonth = trim($_GET['month'] ?? date('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
            $selectedMonth = date('Y-m');
        }

        $data = AttendanceManager::getMonthlyMatrix($classId, $selectedMonth, $isArchived);
        $holidays = AttendanceManager::getMonthlyHolidays($selectedMonth, $classId);
        $students = $data['students'];
        $dates = $data['dates'];
        $matrix = $data['matrix'];

        $filename = "NVTI_Baddegama_Attendance_" . $selectedMonth . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // CSV Header row
        $header = ['#', 'Student Code', 'MIS Number', 'Student Name', 'NIC'];
        foreach ($dates as $d) {
            $dStr = $d['date'];
            $colName = date('d-M', strtotime($dStr));
            if (isset($holidays[$dStr])) {
                $colName .= ' (HOLIDAY)';
            }
            $header[] = $colName;
        }
        $header[] = 'Total Academic Sessions';
        $header[] = 'Present Count';
        $header[] = 'Attendance %';
        $header[] = 'NVQ 5 Exam Eligibility';
        fputcsv($output, $header);

        $idx = 1;
        foreach ($students as $st) {
            $stId = (int)$st['user_id'];
            $row = [
                $idx++,
                $st['student_code'],
                $st['mis_number'] ?? 'N/A',
                $st['name'],
                $st['nic'] ?? 'N/A'
            ];

            $totalWorkingSessions = 0;
            $present = 0;
            foreach ($dates as $d) {
                $dStr = $d['date'];
                $isHoliday = isset($holidays[$dStr]);
                $status = $matrix[$stId][$dStr] ?? '-';

                if ($isHoliday) {
                    $val = 'H (' . substr($holidays[$dStr]['holiday_name'], 0, 15) . ')';
                } elseif ($status === 'present') {
                    $val = 'P';
                    $present++;
                    $totalWorkingSessions++;
                } elseif ($status === 'late') {
                    $val = 'L';
                    $present += 0.8;
                    $totalWorkingSessions++;
                } elseif ($status === 'absent') {
                    $val = 'A';
                    $totalWorkingSessions++;
                } elseif ($status === 'excused') {
                    $val = 'E';
                    $present++;
                    $totalWorkingSessions++;
                } else {
                    $val = '-';
                    $totalWorkingSessions++;
                }
                $row[] = $val;
            }

            $rate = $totalWorkingSessions > 0 ? round(($present / $totalWorkingSessions) * 100, 1) : 100.0;
            $eligibility = $rate >= 80.0 ? 'Eligible (>= 80%)' : 'Ineligible (< 80%)';

            $row[] = $totalWorkingSessions;
            $row[] = round($present, 1);
            $row[] = $rate . '%';
            $row[] = $eligibility;

            fputcsv($output, $row);
        }

        // Output Holiday Log if any holidays exist in this month
        if (!empty($holidays)) {
            fputcsv($output, []);
            fputcsv($output, ['--- OFFICIAL HOLIDAY & INSTITUTIONAL CLOSURE LOG FOR ' . $selectedMonth . ' ---']);
            fputcsv($output, ['Date', 'Holiday / Closure Name', 'Category', 'Description / Reason']);
            foreach ($holidays as $h) {
                fputcsv($output, [
                    $h['date'],
                    $h['holiday_name'],
                    ucwords(str_replace('_', ' ', $h['holiday_type'])),
                    $h['description'] ?: 'NVTI Baddegama official holiday'
                ]);
            }
        }

        fclose($output);
        exit;
    }

    /**
     * Lecturer / Sir: Student Verification Documents & Certificates View
     */
    public function documents(): void {
        require_once __DIR__ . '/../Helpers/DocumentManager.php';

        $db = Database::getConnection();
        $classes = $db->query("SELECT id, class_name FROM classes ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

        $classFilter = isset($_GET['class_id']) && $_GET['class_id'] !== '' ? (int)$_GET['class_id'] : null;
        $statusFilter = trim($_GET['status'] ?? 'all');
        $search = trim($_GET['search'] ?? '');

        $students = DocumentManager::getStudentsDocDirectory($classFilter);
        $docTypes = DocumentManager::getDocTypes();

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

        $pageTitle = "Student Certificates & Verification Documents (Sir Panel)";
        require_once __DIR__ . '/../Views/admin/documents.php';
    }

    /**
     * Lecturer / Sir: Verify or Reject a Student Document
     */
    public function verifyDocument(): void {
        Csrf::checkOrAbort();
        require_once __DIR__ . '/../Helpers/DocumentManager.php';

        $docId = (int)($_POST['document_id'] ?? 0);
        $status = trim($_POST['status'] ?? 'verified');
        $notes = trim($_POST['admin_notes'] ?? '');
        $returnUrl = $_POST['return_url'] ?? 'index.php?page=teacher_documents';

        if ($docId <= 0 || !in_array($status, ['verified', 'rejected', 'pending'], true)) {
            Flash::error("Invalid verification request.");
            header("Location: $returnUrl");
            exit;
        }

        $res = DocumentManager::verifyDocument($docId, $status, $notes, Auth::id());
        if ($res['success']) {
            Auth::logAudit(Auth::id(), 'VERIFY_STUDENT_DOCUMENT', "Document ID $docId status updated to $status by Instructor");
            Flash::success("Document has been marked as " . ucfirst($status) . ".");
        } else {
            Flash::error($res['error'] ?? "Failed to update document verification status.");
        }

        header("Location: $returnUrl");
        exit;
    }
}
