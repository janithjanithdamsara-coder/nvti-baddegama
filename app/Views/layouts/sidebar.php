<?php
/**
 * Modern Clean Full-Height Sidebar (Lucide Icons + Maroon Theme)
 * NVTI Baddegama - National Diploma Portal
 */

$currentRoute = $_GET['page'] ?? 'dashboard';
$role = Auth::role();

$roleBadgeText = match ($role) {
    'admin'   => 'Administrator',
    'teacher' => 'Lecturer Portal',
    'student' => 'Student Portal',
    default   => 'Portal'
};
?>
<aside class="sidebar" id="appSidebar">
  <!-- Top Brand Box with Official NVTI Crest & Logo -->
  <div class="sidebar-brand-box mb-3 pb-2.5 border-bottom">
    <div class="d-flex align-items-center justify-content-between gap-2">
      <a href="index.php?page=dashboard" class="d-flex align-items-center gap-2.5 text-decoration-none min-w-0 flex-grow-1" title="NVTI Baddegama National Diploma">
        <img src="assets/images/logo/NVTI_logo.png" onerror="this.onerror=null; this.src='https://nvtibaddegama.site/images/logo/NVTI_logo.png';" alt="NVTI Logo" class="sidebar-brand-logo" style="height: 40px; width: auto; object-fit: contain;">
        <div class="lh-sm sidebar-brand-text min-w-0">
          <div class="fw-bold text-dark fs-6 sidebar-brand-title text-truncate">NVTI Baddegama</div>
          <small class="text-muted d-block sidebar-brand-sub" style="font-size: 0.72rem; font-weight: 500;">ICT NVQ Level 5</small>
        </div>
      </a>
      <!-- Quick Inline Collapse Toggle Button -->
      <button class="btn btn-sm btn-light border p-1 rounded-2 d-none d-lg-inline-flex align-items-center justify-content-center text-secondary shadow-none sidebar-inline-toggle-btn" id="appSidebarInlineToggleBtn" type="button" title="Collapse Sidebar" style="width: 28px; height: 28px;">
        <i data-lucide="chevron-left" class="sidebar-inline-toggle-icon" style="width: 16px; height: 16px;"></i>
      </button>
    </div>

    <!-- Active Portal Role Indicator -->
    <div class="mt-2.5 sidebar-role-pill">
      <span class="badge w-100 py-1.5 rounded-pill fw-semibold text-center d-inline-block text-truncate" style="background: #fef2f2; color: #7A1418; border: 1px solid #fecaca; font-size: 0.74rem;">
        <i data-lucide="shield-check" class="me-1 align-middle" style="width: 13px; height: 13px; stroke-width: 2.2;"></i> <?= htmlspecialchars($roleBadgeText) ?>
      </span>
    </div>
  </div>

  <!-- Navigation Menu Container (Natural & Balanced Vertical Flow) -->
  <div class="sidebar-nav-container">
    <!-- ================= ADMIN NAVIGATION ================= -->
    <?php if ($role === 'admin'): ?>
      <a href="index.php?page=admin_dashboard" class="sidebar-link <?= in_array($currentRoute, ['admin_dashboard', 'dashboard']) ? 'active' : '' ?>" title="Admin Dashboard">
        <i data-lucide="layout-dashboard" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Admin Dashboard</span>
      </a>
      <a href="index.php?page=admin_students" class="sidebar-link <?= $currentRoute === 'admin_students' ? 'active' : '' ?>" title="Student Management">
        <i data-lucide="users" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Student Management</span>
      </a>
      <a href="index.php?page=admin_teachers" class="sidebar-link <?= $currentRoute === 'admin_teachers' ? 'active' : '' ?>" title="Teacher Accounts">
        <i data-lucide="user-check" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Teacher Accounts</span>
      </a>
      <a href="index.php?page=admin_documents" class="sidebar-link <?= $currentRoute === 'admin_documents' ? 'active' : '' ?>" title="Student Verification Docs">
        <i data-lucide="file-check" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Student Verification Docs</span>
      </a>
      <a href="index.php?page=teacher_attendance" class="sidebar-link <?= $currentRoute === 'teacher_attendance' ? 'active' : '' ?>" title="Attendance & Monitors">
        <i data-lucide="calendar-check-2" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Attendance &amp; Monitors</span>
      </a>
      <a href="index.php?page=admin_timetable" class="sidebar-link <?= $currentRoute === 'admin_timetable' ? 'active' : '' ?>" title="Class Timetable">
        <i data-lucide="calendar" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Class Timetable</span>
      </a>
      <a href="index.php?page=admin_courses" class="sidebar-link <?= $currentRoute === 'admin_courses' ? 'active' : '' ?>" title="Courses & Subjects">
        <i data-lucide="book-marked" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Courses &amp; Subjects</span>
      </a>
      <a href="index.php?page=admin_materials" class="sidebar-link <?= $currentRoute === 'admin_materials' ? 'active' : '' ?>" title="Study Materials">
        <i data-lucide="folder" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Study Materials</span>
      </a>
      <a href="index.php?page=admin_papers" class="sidebar-link <?= $currentRoute === 'admin_papers' ? 'active' : '' ?>" title="Exam Papers Bank">
        <i data-lucide="file-text" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Past &amp; Model Papers</span>
      </a>
      <a href="index.php?page=admin_assignments" class="sidebar-link <?= $currentRoute === 'admin_assignments' ? 'active' : '' ?>" title="Assignments Overview">
        <i data-lucide="clipboard-check" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Assignments Overview</span>
      </a>
      <a href="index.php?page=admin_logs" class="sidebar-link <?= $currentRoute === 'admin_logs' ? 'active' : '' ?>" title="Security & Audit Logs">
        <i data-lucide="shield" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Security &amp; Audit Logs</span>
      </a>
      <a href="index.php?page=admin_email_settings" class="sidebar-link <?= $currentRoute === 'admin_email_settings' ? 'active' : '' ?>" title="Email &amp; SMTP Settings">
        <i data-lucide="mail" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Email &amp; SMTP Settings</span>
      </a>

    <!-- ================= TEACHER NAVIGATION ================= -->
    <?php elseif ($role === 'teacher'): ?>
      <div class="sidebar-heading">Lecturer Menu</div>
      <a href="index.php?page=teacher_dashboard" class="sidebar-link <?= in_array($currentRoute, ['teacher_dashboard', 'dashboard']) ? 'active' : '' ?>" title="Teacher Dashboard">
        <i data-lucide="layout-dashboard" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Teacher Dashboard</span>
      </a>
      <a href="index.php?page=teacher_documents" class="sidebar-link <?= $currentRoute === 'teacher_documents' ? 'active' : '' ?>" title="Student Verification Docs">
        <i data-lucide="file-check" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Student Verification Docs</span>
      </a>
      <a href="index.php?page=teacher_attendance" class="sidebar-link <?= $currentRoute === 'teacher_attendance' ? 'active' : '' ?>" title="Attendance & Monitors">
        <i data-lucide="calendar-check-2" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Attendance &amp; Monitors</span>
      </a>
      <a href="index.php?page=teacher_materials" class="sidebar-link <?= $currentRoute === 'teacher_materials' ? 'active' : '' ?>" title="Upload Materials">
        <i data-lucide="upload-cloud" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Upload Materials</span>
      </a>
      <a href="index.php?page=teacher_assignments" class="sidebar-link <?= $currentRoute === 'teacher_assignments' ? 'active' : '' ?>" title="Create Assignments">
        <i data-lucide="file-plus" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Create Assignments</span>
      </a>
      <a href="index.php?page=teacher_submissions" class="sidebar-link <?= $currentRoute === 'teacher_submissions' ? 'active' : '' ?>" title="Submissions & Grading">
        <i data-lucide="clipboard-check" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Submissions &amp; Grading</span>
      </a>
      <a href="index.php?page=teacher_timetable" class="sidebar-link <?= $currentRoute === 'teacher_timetable' ? 'active' : '' ?>" title="Teaching Schedule">
        <i data-lucide="clock" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Teaching Schedule</span>
      </a>

    <!-- ================= STUDENT NAVIGATION ================= -->
    <?php elseif ($role === 'student'): ?>
      <div class="sidebar-heading">Student Menu</div>
      <a href="index.php?page=student_dashboard" class="sidebar-link <?= in_array($currentRoute, ['student_dashboard', 'dashboard']) ? 'active' : '' ?>" title="Student Dashboard">
        <i data-lucide="layout-dashboard" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Student Dashboard</span>
      </a>
      <a href="index.php?page=student_documents" class="sidebar-link <?= $currentRoute === 'student_documents' ? 'active' : '' ?>" title="My Documents & Certificates">
        <i data-lucide="award" class="sidebar-icon"></i>
        <span class="sidebar-link-text">My Documents &amp; Certificates</span>
      </a>

      <?php
        require_once __DIR__ . '/../../Helpers/AttendanceManager.php';
        $activeMonitor = AttendanceManager::getActiveMonitorStatus((int)Auth::id());
        if ($activeMonitor):
      ?>
        <!-- Dedicated Class Monitor Action -->
        <a href="index.php?page=student_batch_attendance" class="sidebar-link <?= $currentRoute === 'student_batch_attendance' ? 'active' : '' ?>" style="background: #fef2f2; color: #7A1418; font-weight: 600;" title="Batch Attendance">
          <i data-lucide="calendar-check-2" class="sidebar-icon" style="color: #7A1418;"></i>
          <span class="sidebar-link-text">Batch Attendance</span>
          <span class="badge ms-auto sidebar-badge-monitor" style="font-size: 0.65rem; background-color: #7A1418; color: #ffffff;">Monitor</span>
        </a>
      <?php endif; ?>

      <a href="index.php?page=student_materials" class="sidebar-link <?= $currentRoute === 'student_materials' ? 'active' : '' ?>" title="Learning Materials">
        <i data-lucide="book-open" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Learning Materials</span>
      </a>
      <a href="index.php?page=student_curriculum" class="sidebar-link <?= in_array($currentRoute, ['student_curriculum', 'student_syllabus']) ? 'active' : '' ?>" title="Course Curriculum & Syllabus">
        <i data-lucide="book-open-check" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Course Curriculum</span>
      </a>
      <a href="index.php?page=student_papers" class="sidebar-link <?= $currentRoute === 'student_papers' ? 'active' : '' ?>" title="Past Papers & Model Papers">
        <i data-lucide="file-text" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Past Papers &amp; Model</span>
      </a>
      <a href="index.php?page=student_assignments" class="sidebar-link <?= $currentRoute === 'student_assignments' ? 'active' : '' ?>" title="My Assignments & Marks">
        <i data-lucide="file-edit" class="sidebar-icon"></i>
        <span class="sidebar-link-text">My Assignments &amp; Marks</span>
      </a>
      <a href="index.php?page=student_attendance" class="sidebar-link <?= $currentRoute === 'student_attendance' ? 'active' : '' ?>" title="Attendance & Eligibility">
        <i data-lucide="trending-up" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Attendance &amp; Eligibility</span>
      </a>
      <a href="index.php?page=student_timetable" class="sidebar-link <?= $currentRoute === 'student_timetable' ? 'active' : '' ?>" title="Class Timetable">
        <i data-lucide="calendar" class="sidebar-icon"></i>
        <span class="sidebar-link-text">Class Timetable</span>
      </a>
    <?php endif; ?>

    <?php if ($role === 'student'): 
      $stBatchInfo = null;
      try {
          $sdb = Database::getConnection();
          $stBatchStmt = $sdb->prepare("
              SELECT c.class_name, c.status, c.academic_year 
              FROM student_profiles sp 
              JOIN classes c ON sp.class_id = c.id 
              WHERE sp.user_id = ?
          ");
          $stBatchStmt->execute([Auth::id()]);
          $stBatchInfo = $stBatchStmt->fetch();
      } catch (\Exception $e) {}
      $isAlumni = ($stBatchInfo && $stBatchInfo['status'] === 'archived');
      $batchDisplayName = $stBatchInfo ? preg_replace('/.*Batch\s*/i', 'Batch ', $stBatchInfo['class_name']) : 'Batch 2026';
    ?>
      <!-- Official NVTI Academic Status Widget (Student Portal Only) -->
      <div class="sidebar-academic-widget p-2.5 rounded-3 my-2.5" style="background: <?= $isAlumni ? '#f8fafc' : 'linear-gradient(180deg, #fdf8f8 0%, #f8fafc 100%)' ?>; border: 1px solid <?= $isAlumni ? '#cbd5e1' : '#fecaca' ?>;">
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="badge rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="background: <?= $isAlumni ? '#475569' : '#7A1418' ?>; color: #ffffff; width: 22px; height: 22px;">
            <i data-lucide="graduation-cap" style="width: 13px; height: 13px;"></i>
          </span>
          <span class="fw-bold text-dark text-truncate" style="font-size: 0.75rem;">NVQ L5 &bull; <?= htmlspecialchars($batchDisplayName) ?></span>
        </div>
        <div class="text-secondary small" style="font-size: 0.7rem; line-height: 1.35;">
          <?php if ($isAlumni): ?>
            <span class="badge bg-secondary text-white" style="font-size: 0.65rem;">🎓 Alumni / Completed Cohort</span>
          <?php else: ?>
            Semester 1 &bull; 80% Min. Attendance
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Clean Subtle Divider Before Account Links -->
    <div class="my-2.5 border-top opacity-50"></div>
    <a href="index.php?page=profile" class="sidebar-link <?= $currentRoute === 'profile' ? 'active' : '' ?>" title="My Profile">
      <i data-lucide="user" class="sidebar-icon"></i>
      <span class="sidebar-link-text">My Profile</span>
    </a>
    <a href="index.php?page=change_password" class="sidebar-link <?= $currentRoute === 'change_password' ? 'active' : '' ?>" title="Change Password">
      <i data-lucide="key-round" class="sidebar-icon"></i>
      <span class="sidebar-link-text">Change Password</span>
    </a>
    <a href="index.php?page=home" target="_blank" class="sidebar-link text-decoration-none" title="Visit Public Website">
      <i data-lucide="globe" class="sidebar-icon"></i>
      <span class="sidebar-link-text">Visit Public Website</span>
      <i data-lucide="external-link" class="ms-auto text-muted sidebar-ext-icon" style="width: 14px; height: 14px;"></i>
    </a>
    <a href="index.php?page=logout" class="sidebar-link text-danger fw-semibold" title="Sign Out">
      <i data-lucide="log-out" class="sidebar-icon text-danger"></i>
      <span class="sidebar-link-text">Sign Out</span>
    </a>
  </div>

  <!-- Student / User Profile Box (Flows naturally with border-top, NO giant 400px empty space) -->
  <div class="sidebar-pinned-user-box pt-2.5 mt-2.5 border-top">
    <a href="index.php?page=profile" class="d-flex align-items-center gap-2.5 text-decoration-none p-1.5 rounded-3 sidebar-user-pill" title="View My Profile">
      <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 34px; height: 34px; font-size: 0.82rem; background: linear-gradient(135deg, #7A1418, #90161b); box-shadow: 0 2px 6px rgba(122, 20, 24, 0.25);">
        <?= strtoupper(substr(Auth::name(), 0, 1)) ?>
      </div>
      <div class="lh-sm min-w-0 flex-grow-1 sidebar-user-info">
        <div class="fw-bold text-dark small text-truncate"><?= e(Auth::name()) ?></div>
        <small class="text-muted d-block text-truncate font-monospace" style="font-size: 0.68rem;"><?= e(Auth::user()['email']) ?></small>
      </div>
    </a>
  </div>
</aside>
