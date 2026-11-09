<?php
/**
 * Dynamic Navbar Component
 * - Public Pages: Official NVTI Maroon Header
 * - Portal Pages: Sleek Unified Top Navigation Bar (Desktop & Mobile)
 */

$currentPage = $_GET['page'] ?? 'home';
$isLoggedIn = Auth::check();
$isPublicPage = in_array($currentPage, ['home', 'login', 'register', 'activate', 'forgot_password']);

$role = Auth::role();
$roleBadgeText = match ($role) {
    'admin'   => 'Administrator',
    'teacher' => 'Lecturer Portal',
    'student' => 'Student Portal',
    default   => 'Portal'
};

$pageDisplayName = match ($currentPage) {
    'dashboard', 'student_dashboard', 'teacher_dashboard', 'admin_dashboard' => 'Dashboard',
    'student_batch_attendance' => 'Batch Attendance',
    'student_materials', 'teacher_materials', 'admin_materials' => 'Learning Materials',
    'student_curriculum', 'student_syllabus' => 'Course Curriculum & Syllabus',
    'student_papers' => 'Past Papers & Model Papers',
    'student_assignments', 'teacher_assignments', 'admin_assignments' => 'Assignments & Marks',
    'student_attendance', 'teacher_attendance' => 'Attendance Records',
    'student_documents' => 'My Documents & Certificates',
    'admin_documents', 'teacher_documents' => 'Student Verification Documents',
    'admin_papers', 'student_papers' => 'Exam & Model Papers Bank',
    'student_timetable', 'teacher_timetable', 'admin_timetable' => 'Class Timetable',
    'profile' => 'My Profile',
    'change_password' => 'Change Password',
    'admin_students' => 'Student Management',
    'admin_teachers' => 'Teacher Accounts',
    'admin_courses' => 'Courses & Subjects',
    'admin_logs' => 'Security Logs',
    default => ucfirst(str_replace('_', ' ', $currentPage))
};

$userFullName = Auth::name();
$userEmail = Auth::user()['email'] ?? '';
$userInitial = !empty($userFullName) ? strtoupper(substr($userFullName, 0, 1)) : 'U';
?>

<?php if (!$isLoggedIn || $isPublicPage): ?>
  <!-- ================= PUBLIC NVTI MAROON NAVBAR ================= -->
  <nav class="navbar navbar-expand-lg navbar-nvti-maroon sticky-top">
    <div class="container-fluid px-3 px-md-4">
      <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none py-1" href="index.php">
        <img src="assets/images/logo/NVTI_logo.png" onerror="this.onerror=null; this.src='https://nvtibaddegama.site/images/logo/NVTI_logo.png';" alt="NVTI Logo" style="height: 36px; width: auto; max-width: 140px; object-fit: contain; filter: brightness(0) invert(1);">
        <span class="fs-4 fw-bold text-white tracking-tight ms-1">Baddegama</span>
      </a>

      <button class="navbar-toggler border-0 shadow-none text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" style="filter: brightness(0) invert(1);">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 align-items-center gap-1">
          <li class="nav-item">
            <a class="nav-link nav-link-custom <?= ($currentPage === 'home') ? 'active' : '' ?>" href="index.php?page=home">
              Home
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-custom" href="index.php?page=home#instructors">
              Instructors
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-custom" href="index.php?page=home#curriculum">
              Curriculum
            </a>
          </li>
        </ul>

        <ul class="navbar-nav ms-auto align-items-center gap-2">
          <?php if ($isLoggedIn): ?>
            <li class="nav-item">
              <a class="btn-navbar-white d-inline-flex align-items-center gap-1" href="index.php?page=dashboard">
                <i class="bi bi-speedometer2"></i> Return to Portal
              </a>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link text-white fw-semibold px-2 d-inline-flex align-items-center gap-1" href="index.php?page=register">
                <i class="bi bi-person-plus"></i> Register
              </a>
            </li>
            <li class="nav-item">
              <a class="btn-navbar-white d-inline-flex align-items-center gap-1" href="index.php?page=login">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
              </a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

<?php else: ?>
  <!-- ================= PORTAL UNIFIED TOPBAR (Fixed at Top) ================= -->
  <header class="app-portal-topbar fixed-top bg-white border-bottom px-3 px-md-4 py-2 d-flex align-items-center justify-content-between">
    <!-- Left Section: Toggle, Role Pill & Breadcrumb -->
    <div class="d-flex align-items-center gap-2 min-w-0">
      <!-- Sidebar Toggle Button (Handles Desktop Collapse & Mobile Drawer) -->
      <button class="btn btn-sm btn-light border px-2 py-1.5 rounded-2 d-inline-flex align-items-center justify-content-center text-dark shadow-none sidebar-topbar-toggle-btn" id="appSidebarToggleBtn" type="button" title="Toggle Navigation Sidebar">
        <i data-lucide="panel-left-close" class="sidebar-toggle-icon" style="width: 18px; height: 18px;"></i>
      </button>

      <!-- Role Badge -->
      <span class="badge rounded-pill px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 flex-shrink-0" style="font-size: 0.74rem; background: #fef2f2; color: #7A1418; border: 1px solid #fecaca;">
        <i data-lucide="shield-check" style="width: 13px; height: 13px; stroke-width: 2.2;"></i>
        <?= htmlspecialchars($roleBadgeText) ?>
      </span>

      <!-- Breadcrumb Slash & Current Page Name -->
      <div class="d-none d-sm-flex align-items-center gap-2 text-truncate text-secondary small ms-1">
        <span class="text-muted opacity-50">/</span>
        <span class="fw-bold text-dark text-truncate"><?= e($pageDisplayName) ?></span>
      </div>
    </div>

    <!-- Right Section: Public Site Link & User Profile Dropdown (Right-Aligned via ms-auto) -->
    <div class="d-flex align-items-center gap-2 gap-md-3 ms-auto">
      <!-- Public Site Link Button -->
      <a href="index.php?page=home" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 px-md-3 py-1 d-inline-flex align-items-center gap-1.5 fw-medium text-decoration-none" title="Visit Public Website">
        <i data-lucide="external-link" style="width: 14px; height: 14px;"></i>
        <span class="d-none d-sm-inline small">Public Site</span>
      </a>

      <!-- User Profile Dropdown -->
      <div class="dropdown">
        <button class="btn btn-light border-0 p-1 pe-2 rounded-pill d-inline-flex align-items-center gap-2 shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="background: #f8fafc; border: 1px solid #e2e8f0 !important;" title="Account Menu">
          <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 30px; height: 30px; font-size: 0.8rem; background: linear-gradient(135deg, #7A1418, #90161b); box-shadow: 0 2px 4px rgba(122, 20, 24, 0.2);">
            <?= $userInitial ?>
          </div>
          <span class="fw-semibold text-dark small d-none d-md-inline text-truncate" style="max-width: 160px;">
            <?= e($userFullName) ?>
          </span>
          <i data-lucide="chevron-down" class="text-muted d-none d-md-inline" style="width: 14px; height: 14px;"></i>
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow border py-2 mt-1" style="min-width: 230px; border-radius: 12px; border-color: #e2e8f0;">
          <li class="px-3 py-2 mb-1">
            <span class="badge text-uppercase fw-bold mb-1" style="font-size: 0.65rem; background: #fef2f2; color: #7A1418; border: 1px solid #fecaca;">
              ROLE: <?= strtoupper($role) ?>
            </span>
            <div class="fw-bold text-dark small text-truncate"><?= e($userFullName) ?></div>
            <div class="text-muted font-monospace text-truncate" style="font-size: 0.72rem;"><?= e($userEmail) ?></div>
          </li>
          <li><hr class="dropdown-divider my-1"></li>
          <li>
            <a class="dropdown-item d-flex align-items-center gap-2 py-1.5 small" href="index.php?page=profile">
              <i data-lucide="user" style="width: 15px; height: 15px;"></i> My Profile
            </a>
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center gap-2 py-1.5 small" href="index.php?page=change_password">
              <i data-lucide="key-round" style="width: 15px; height: 15px;"></i> Change Password
            </a>
          </li>
          <li><hr class="dropdown-divider my-1"></li>
          <li>
            <a class="dropdown-item d-flex align-items-center gap-2 py-1.5 small text-danger fw-semibold" href="index.php?page=logout">
              <i data-lucide="log-out" style="width: 15px; height: 15px;"></i> Sign Out
            </a>
          </li>
        </ul>
      </div>
    </div>
  </header>
<?php endif; ?>
