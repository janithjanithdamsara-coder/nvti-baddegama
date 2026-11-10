<?php
/**
 * Main Front Controller & Router
 * NVTI Baddegama National Diploma Portal (ICT NVQ Level 5)
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Helpers/Auth.php';
require_once __DIR__ . '/app/Helpers/Sanitizer.php';
require_once __DIR__ . '/app/Helpers/Csrf.php';
require_once __DIR__ . '/app/Helpers/Flash.php';
require_once __DIR__ . '/app/Helpers/Uploader.php';
require_once __DIR__ . '/app/Helpers/Mailer.php';

require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/AdminController.php';
require_once __DIR__ . '/app/Controllers/TeacherController.php';
require_once __DIR__ . '/app/Controllers/StudentController.php';

$page = $_GET['page'] ?? 'home';

// If user requests dashboard, route according to authenticated role
if ($page === 'dashboard') {
    if (!Auth::check()) {
        header('Location: index.php?page=login');
        exit;
    } else {
        switch (Auth::role()) {
            case 'admin':
                $page = 'admin_dashboard';
                break;
            case 'teacher':
                $page = 'teacher_dashboard';
                break;
            case 'student':
            default:
                $page = 'student_dashboard';
                break;
        }
    }
}

// Router Dispatch
switch ($page) {
    // ---------------- HOME / LANDING ROUTE ----------------
    case 'home':
        $pageTitle = "Home - Official Learning Management System";
        require_once __DIR__ . '/app/Views/home.php';
        break;

    case 'reset_session':
        Auth::logout();
        header('Location: index.php?page=home');
        exit;
        break;
    // ---------------- AUTH ROUTES ----------------
    case 'login':
        (new AuthController())->showLogin();
        break;
    case 'login_process':
        (new AuthController())->handleLogin();
        break;
    case 'register':
        (new AuthController())->showRegister();
        break;
    case 'register_process':
        (new AuthController())->handleRegister();
        break;
    case 'activate':
        (new AuthController())->showActivate();
        break;
    case 'activate_process':
        (new AuthController())->handleActivate();
        break;
    case 'change_password':
        (new AuthController())->showChangePassword();
        break;
    case 'change_password_process':
        (new AuthController())->handleChangePassword();
        break;
    case 'forgot_password':
        (new AuthController())->showForgotPassword();
        break;
    case 'forgot_password_process':
        (new AuthController())->handleForgotPassword();
        break;
    case 'reset_password':
        (new AuthController())->showResetPassword();
        break;
    case 'reset_password_process':
        (new AuthController())->handleResetPassword();
        break;
    case 'logout':
        (new AuthController())->logout();
        break;
    case 'profile':
        if (Auth::role() === 'student') {
            (new StudentController())->profile();
        } else {
            (new AuthController())->showChangePassword();
        }
        break;

    // ---------------- ADMIN ROUTES ----------------
    case 'admin_dashboard':
        (new AdminController())->dashboard();
        break;
    case 'admin_students':
        (new AdminController())->students();
        break;
    case 'admin_create_batch':
        (new AdminController())->createBatch();
        break;
    case 'admin_archive_batch':
        (new AdminController())->archiveBatch();
        break;
    case 'admin_restore_batch':
        (new AdminController())->restoreBatch();
        break;
    case 'admin_create_student':
        (new AdminController())->createStudent();
        break;
    case 'admin_update_student_status':
        (new AdminController())->updateStudentStatus();
        break;
    case 'admin_delete_student':
        (new AdminController())->deleteStudent();
        break;
    case 'admin_applications':
        (new AdminController())->applications();
        break;
    case 'admin_sync_applications':
        (new AdminController())->syncApplications();
        break;
    case 'admin_application_status':
        (new AdminController())->updateApplicationStatus();
        break;
    case 'admin_enroll_applicant':
        (new AdminController())->enrollApplicant();
        break;
    case 'admin_save_application':
        (new AdminController())->saveApplication();
        break;
    case 'admin_export_applications_csv':
        (new AdminController())->exportApplicationsCsv();
        break;
    case 'admin_teachers':
        (new AdminController())->teachers();
        break;
    case 'admin_create_admin':
        (new AdminController())->createAdmin();
        break;
    case 'admin_create_teacher':
        (new AdminController())->createTeacher();
        break;
    case 'admin_update_teacher':
        (new AdminController())->updateTeacher();
        break;
    case 'admin_timetable':
        (new AdminController())->timetable();
        break;
    case 'admin_create_session':
        (new AdminController())->createSession();
        break;
    case 'admin_delete_session':
        (new AdminController())->deleteSession();
        break;
    case 'admin_courses':
        (new AdminController())->courses();
        break;
    case 'admin_update_curriculum_module':
        (new AdminController())->updateCurriculumModule();
        break;
    case 'admin_create_curriculum_module':
        (new AdminController())->createCurriculumModule();
        break;
    case 'admin_delete_curriculum_module':
        (new AdminController())->deleteCurriculumModule();
        break;
    case 'admin_create_course':
        (new AdminController())->createCourse();
        break;
    case 'admin_create_subject':
        (new AdminController())->createSubject();
        break;
    case 'admin_materials':
        (new AdminController())->materials();
        break;
    case 'admin_upload_material':
        (new AdminController())->uploadMaterial();
        break;
    case 'admin_create_assignment':
        (new AdminController())->createAssignment();
        break;
    case 'admin_update_assignment_deadline':
        (new AdminController())->updateAssignmentDeadline();
        break;
    case 'admin_delete_material':
        (new AdminController())->deleteMaterial();
        break;
    case 'admin_toggle_material_visibility':
        (new AdminController())->toggleMaterialVisibility();
        break;
    case 'admin_bulk_materials':
        (new AdminController())->bulkMaterials();
        break;
    case 'admin_module_materials_action':
        (new AdminController())->moduleMaterialsAction();
        break;
    case 'admin_global_materials_action':
        (new AdminController())->globalMaterialsAction();
        break;
    case 'admin_delete_assignment':
        (new AdminController())->deleteAssignment();
        break;
    case 'admin_create_classroom':
        (new AdminController())->createClassroom();
        break;
    case 'admin_delete_classroom':
        (new AdminController())->deleteClassroom();
        break;
    case 'admin_assignments':
        (new AdminController())->assignments();
        break;
    case 'admin_papers':
        (new AdminController())->papers();
        break;
    case 'admin_upload_paper':
        (new AdminController())->uploadPaper();
        break;
    case 'admin_update_paper':
        (new AdminController())->updatePaper();
        break;
    case 'admin_delete_paper':
        (new AdminController())->deletePaper();
        break;
    case 'admin_documents':
        (new AdminController())->documents();
        break;
    case 'admin_verify_document':
        (new AdminController())->verifyDocument();
        break;
    case 'admin_get_student_documents':
        (new AdminController())->getStudentDocumentsJson();
        break;
    case 'admin_logs':
        (new AdminController())->logs();
        break;
    case 'admin_email_settings':
        (new AdminController())->showEmailSettings();
        break;
    case 'admin_test_email':
        (new AdminController())->testEmail();
        break;

    // ---------------- TEACHER ROUTES ----------------
    case 'teacher_dashboard':
        (new TeacherController())->dashboard();
        break;
    case 'teacher_materials':
        (new TeacherController())->materials();
        break;
    case 'teacher_upload_material':
        (new TeacherController())->uploadMaterial();
        break;
    case 'teacher_delete_material':
        (new TeacherController())->deleteMaterial();
        break;
    case 'teacher_toggle_material_visibility':
        (new TeacherController())->toggleMaterialVisibility();
        break;
    case 'teacher_assignments':
        (new TeacherController())->assignments();
        break;
    case 'teacher_create_assignment':
        (new TeacherController())->createAssignment();
        break;
    case 'teacher_submissions':
        (new TeacherController())->submissions();
        break;
    case 'teacher_grade_submission':
        (new TeacherController())->gradeSubmission();
        break;
    case 'teacher_timetable':
        (new TeacherController())->timetable();
        break;
    case 'teacher_attendance':
        (new TeacherController())->attendance();
        break;
    case 'teacher_attendance_save':
        (new TeacherController())->saveAttendance();
        break;
    case 'teacher_attendance_unlock':
        (new TeacherController())->unlockAttendance();
        break;
    case 'teacher_attendance_assign':
        (new TeacherController())->assignMonitor();
        break;
    case 'teacher_attendance_toggle':
        (new TeacherController())->toggleMonitor();
        break;
    case 'teacher_attendance_export':
        (new TeacherController())->exportAttendanceCsv();
        break;
    case 'teacher_declare_holiday':
        (new TeacherController())->declareHoliday();
        break;
    case 'teacher_remove_holiday':
        (new TeacherController())->removeHoliday();
        break;
    case 'teacher_documents':
        (new TeacherController())->documents();
        break;
    case 'teacher_verify_document':
        (new TeacherController())->verifyDocument();
        break;

    // ---------------- STUDENT ROUTES ----------------
    case 'student_dashboard':
        (new StudentController())->dashboard();
        break;
    case 'student_materials':
        (new StudentController())->materials();
        break;
    case 'student_assignments':
        (new StudentController())->assignments();
        break;
    case 'student_submit_assignment':
        (new StudentController())->submitAssignment();
        break;
    case 'student_timetable':
        (new StudentController())->timetable();
        break;
    case 'student_attendance':
        (new StudentController())->attendance();
        break;
    case 'student_batch_attendance':
        (new StudentController())->batchAttendance();
        break;
    case 'student_batch_attendance_save':
        (new StudentController())->saveBatchAttendance();
        break;
    case 'student_update_profile':
        (new StudentController())->updateProfile();
        break;
    case 'student_papers':
        (new StudentController())->pastPapers();
        break;
    case 'student_curriculum':
    case 'student_syllabus':
        (new StudentController())->curriculum();
        break;
    case 'student_documents':
        (new StudentController())->documents();
        break;
    case 'student_upload_document':
        (new StudentController())->uploadDocument();
        break;

    // ---------------- SECURE DOWNLOAD HANDLERS ----------------
    case 'download_material':
        Auth::requireLogin();
        $matId = (int)($_GET['id'] ?? 0);
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT file_path, original_filename, is_hidden FROM materials WHERE id = ?");
        $stmt->execute([$matId]);
        $row = $stmt->fetch();

        if (!$row || empty($row['file_path'])) {
            http_response_code(404);
            die("Study material file not found.");
        }

        if (Auth::role() === 'student' && !empty($row['is_hidden'])) {
            http_response_code(403);
            die("<div style='font-family:sans-serif;padding:30px;text-align:center;'><h3>Access Restricted</h3><p>This study material has been archived or hidden by the course instructor / administrator.</p><a href='index.php?page=student_materials'>Return to Classroom Hub</a></div>");
        }

        // Increment download counter
        $inc = $db->prepare("UPDATE materials SET download_count = download_count + 1 WHERE id = ?");
        $inc->execute([$matId]);

        $filePath = MATERIAL_UPLOAD_PATH . DIRECTORY_SEPARATOR . $row['file_path'];
        $fileName = $row['original_filename'] ?: basename($filePath);
        Uploader::streamDownload($filePath, $fileName);
        break;

    case 'view_material':
        Auth::requireLogin();
        $matId = (int)($_GET['id'] ?? 0);
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM materials WHERE id = ?");
        $stmt->execute([$matId]);
        $row = $stmt->fetch();

        if (!$row) {
            http_response_code(404);
            die("Study material not found.");
        }

        if (Auth::role() === 'student' && !empty($row['is_hidden'])) {
            http_response_code(403);
            die("<div style='font-family:sans-serif;padding:30px;text-align:center;'><h3>Access Restricted</h3><p>This study material has been archived or hidden by the course instructor / administrator.</p><a href='index.php?page=student_materials'>Return to Classroom Hub</a></div>");
        }

        if ($row['material_type'] === 'youtube' || $row['material_type'] === 'url') {
            header('Location: ' . $row['external_url']);
            exit;
        }

        if (empty($row['file_path'])) {
            http_response_code(404);
            die("File attachment not found.");
        }

        $filePath = MATERIAL_UPLOAD_PATH . DIRECTORY_SEPARATOR . $row['file_path'];
        $fileName = $row['original_filename'] ?: basename($filePath);
        Uploader::streamInlineView($filePath, $fileName);
        break;

    case 'download_paper':
        Auth::requireLogin();
        $paperId = (int)($_GET['id'] ?? 0);
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT file_path, original_filename FROM exam_papers WHERE id = ?");
        $stmt->execute([$paperId]);
        $row = $stmt->fetch();

        if (!$row || empty($row['file_path'])) {
            http_response_code(404);
            die("Examination paper not found.");
        }

        $inc = $db->prepare("UPDATE exam_papers SET download_count = download_count + 1 WHERE id = ?");
        $inc->execute([$paperId]);

        $filePath = MATERIAL_UPLOAD_PATH . DIRECTORY_SEPARATOR . $row['file_path'];
        $fileName = $row['original_filename'] ?: basename($filePath);
        Uploader::streamDownload($filePath, $fileName);
        break;

    case 'view_paper':
        Auth::requireLogin();
        $paperId = (int)($_GET['id'] ?? 0);
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT file_path, original_filename FROM exam_papers WHERE id = ?");
        $stmt->execute([$paperId]);
        $row = $stmt->fetch();

        if (!$row || empty($row['file_path'])) {
            http_response_code(404);
            die("Examination paper not found.");
        }

        $filePath = MATERIAL_UPLOAD_PATH . DIRECTORY_SEPARATOR . $row['file_path'];
        $fileName = $row['original_filename'] ?: basename($filePath);
        Uploader::streamInlineView($filePath, $fileName);
        break;

    case 'view_curriculum_guide':
        Auth::requireLogin();
        $guidePath = MATERIAL_UPLOAD_PATH . DIRECTORY_SEPARATOR . 'tvec_curriculum_guide_k72t001.pdf';
        if (!file_exists($guidePath)) {
            http_response_code(404);
            die("Curriculum guide book file not found.");
        }
        Uploader::streamInlineView($guidePath, 'TVEC_NVQ_L5_ICT_Curriculum_Guide_K72T001.pdf');
        break;

    case 'view_student_document':
        Auth::requireLogin();
        $docId = (int)($_GET['id'] ?? 0);
        require_once __DIR__ . '/app/Helpers/DocumentManager.php';
        $doc = DocumentManager::getDocumentById($docId);

        if (!$doc || empty($doc['file_path'])) {
            http_response_code(404);
            die("Verification document not found.");
        }

        $currentRole = Auth::role();
        $currentId   = Auth::id();
        $isOwner     = ($currentRole === 'student' && (int)$doc['student_id'] === $currentId);
        $isStaff     = in_array($currentRole, ['admin', 'teacher'], true);

        if (!$isOwner && !$isStaff) {
            http_response_code(403);
            die("Unauthorized access to this student verification document.");
        }

        $filePath = $doc['file_path'];
        $fileName = $doc['original_filename'] ?: basename($filePath);
        Uploader::streamInlineView($filePath, $fileName);
        break;

    case 'download_submission':
        Auth::requireLogin();
        $subId = (int)($_GET['id'] ?? 0);
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT sub.*, a.teacher_id
            FROM submissions sub
            JOIN assignments a ON sub.assignment_id = a.id
            WHERE sub.id = ?
        ");
        $stmt->execute([$subId]);
        $sub = $stmt->fetch();

        if (!$sub || empty($sub['file_path'])) {
            http_response_code(404);
            die("Submitted coursework file not found.");
        }

        // Permission check: Admin, or Assigned Teacher, or Submitting Student
        $currentRole = Auth::role();
        $currentId   = Auth::id();
        $isOwnerStudent = ($currentRole === 'student' && $sub['student_id'] == $currentId);
        $isTeacherAssigned = ($currentRole === 'teacher' && $sub['teacher_id'] == $currentId);
        $isAdmin = ($currentRole === 'admin');

        if (!$isAdmin && !$isTeacherAssigned && !$isOwnerStudent) {
            http_response_code(403);
            die("Unauthorized access to this student submission.");
        }

        $filePath = SUBMISSION_UPLOAD_PATH . DIRECTORY_SEPARATOR . $sub['file_path'];
        $fileName = $sub['original_filename'] ?: basename($filePath);
        Uploader::streamDownload($filePath, $fileName);
        break;

    default:
        http_response_code(404);
        echo "<div style='font-family:sans-serif;padding:30px;text-align:center;'>
                <h2>404 - Page Not Found</h2>
                <p>The requested page <code>" . htmlspecialchars($page) . "</code> does not exist.</p>
                <a href='index.php'>Return to Home</a>
              </div>";
        break;
}
