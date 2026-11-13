<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

$batchStatus = trim($_GET['batch_status'] ?? 'active');
$classFilter = (int)($_GET['class_id'] ?? 0);
$search = trim($_GET['search'] ?? '');
?>

<div class="app-wrapper">
  <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <div class="container-fluid">
      <?= Flash::render() ?>

      <!-- Header Banner (NVTI Maroon Hub Aesthetic) -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 text-white overflow-hidden position-relative" style="background: linear-gradient(135deg, #7A1418 0%, #991b1b 100%); padding: 1.85rem 2.25rem;">
        <!-- Watermark Background Crest (Subtle Opacity to avoid button overlap) -->
        <div class="position-absolute end-0 top-50 translate-middle-y me-2 pointer-events-none d-none d-xl-block" style="font-size: 7rem; line-height: 0; opacity: 0.05 !important;">
          <i class="bi bi-people-fill text-white"></i>
        </div>

        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 position-relative" style="z-index: 5;">
          <div class="pe-lg-3">
            <h3 class="fw-bold mb-1.5 text-white" style="letter-spacing: -0.3px;">Student &amp; Batch Management</h3>
            <p class="mb-0 text-white opacity-85 small" style="max-width: 680px;">Manage currently enrolled active batches, transition graduated cohorts to alumni archives, and view comprehensive student records.</p>
          </div>

          <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0 align-self-start align-self-lg-center">
            <!-- Register Student Button -->
            <button type="button" class="btn btn-light rounded-pill px-3.5 py-2 fw-bold text-danger d-inline-flex align-items-center gap-2 shadow" style="color: #7A1418 !important;" data-bs-toggle="modal" data-bs-target="#adminRegisterStudentModal">
              <i class="bi bi-person-plus-fill fs-6"></i> Register Student
            </button>

            <!-- Complete / Archive Batch Button -->
            <?php if (!empty($activeBatches)): ?>
              <button type="button" class="btn btn-outline-light rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-xs" data-bs-toggle="modal" data-bs-target="#archiveBatchModal">
                <i class="bi bi-mortarboard fs-6"></i> Graduate / Archive Batch
              </button>
            <?php endif; ?>

            <!-- Add New Batch Button -->
            <button type="button" class="btn btn-warning rounded-pill px-3.5 py-2 fw-bold text-dark d-inline-flex align-items-center gap-2 shadow-xs" data-bs-toggle="modal" data-bs-target="#createBatchModal">
              <i class="bi bi-plus-circle-fill fs-6"></i> New Batch
            </button>
          </div>
        </div>
      </div>

      <!-- Quick Metrics Counter Cards -->
      <div class="row g-3 mb-4">
        <!-- Active Students -->
        <div class="col-6 col-md-3">
          <a href="index.php?page=admin_students&batch_status=active" class="text-decoration-none">
            <div class="card border rounded-4 p-3 bg-white shadow-sm d-flex flex-row align-items-center gap-3 transition-hover" style="<?= ($batchStatus === 'active') ? 'border: 2px solid #7A1418 !important; background: #fff5f5 !important;' : '' ?>">
              <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center text-white flex-shrink-0" style="background: #7A1418; width: 44px; height: 44px;">
                <i class="bi bi-people-fill fs-5"></i>
              </div>
              <div>
                <div class="fs-4 fw-bold text-dark lh-1 mb-1"><?= $activeStudentsCount ?></div>
                <div class="text-muted small fw-medium">Active Batch Students <i class="bi bi-arrow-right-short text-danger"></i></div>
              </div>
            </div>
          </a>
        </div>

        <!-- Old / Archived Students -->
        <div class="col-6 col-md-3">
          <a href="index.php?page=admin_students&batch_status=archived" class="text-decoration-none">
            <div class="card border rounded-4 p-3 bg-white shadow-sm d-flex flex-row align-items-center gap-3 transition-hover" style="<?= ($batchStatus === 'archived') ? 'border: 2px solid #475569 !important; background: #f8fafc !important;' : '' ?>">
              <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center text-white flex-shrink-0" style="background: #475569; width: 44px; height: 44px;">
                <i class="bi bi-archive-fill fs-5"></i>
              </div>
              <div>
                <div class="fs-4 fw-bold text-dark lh-1 mb-1"><?= $archivedStudentsCount ?></div>
                <div class="text-muted small fw-medium">Old Batch (Alumni) <i class="bi bi-arrow-right-short text-secondary"></i></div>
              </div>
            </div>
          </a>
        </div>

        <!-- Active Batches Count -->
        <div class="col-6 col-md-3">
          <div class="card border rounded-4 p-3 bg-white shadow-sm d-flex flex-row align-items-center gap-3">
            <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center text-white flex-shrink-0" style="background: #1967d2; width: 44px; height: 44px;">
              <i class="bi bi-broadcast fs-5"></i>
            </div>
            <div>
              <div class="fs-4 fw-bold text-dark lh-1 mb-1"><?= count($activeBatches) ?></div>
              <div class="text-muted small fw-medium">Active Cohorts</div>
            </div>
          </div>
        </div>

        <!-- Archived Batches Count -->
        <div class="col-6 col-md-3">
          <div class="card border rounded-4 p-3 bg-white shadow-sm d-flex flex-row align-items-center gap-3">
            <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center text-white flex-shrink-0" style="background: #0f766e; width: 44px; height: 44px;">
              <i class="bi bi-mortarboard-fill fs-5"></i>
            </div>
            <div>
              <div class="fs-4 fw-bold text-dark lh-1 mb-1"><?= count($archivedBatches) ?></div>
              <div class="text-muted small fw-medium">Completed Batches</div>
            </div>
          </div>
        </div>
      </div>

      <?php if (!empty($pendingStudentsCount) && $batchStatus !== 'pending'): ?>
        <!-- Pending Registration Approval Callout -->
        <div class="alert border-0 shadow-sm rounded-4 p-3 mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" style="background: linear-gradient(90deg, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #f59e0b !important;">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white p-2.5 d-flex align-items-center justify-content-center shadow-xs text-warning flex-shrink-0" style="width: 44px; height: 44px;">
              <i class="bi bi-person-fill-exclamation fs-4"></i>
            </div>
            <div>
              <span class="badge bg-warning text-dark fw-bold rounded-pill px-2.5 py-0.5 mb-0.5" style="font-size: 0.72rem;">ACTION REQUIRED</span>
              <h6 class="fw-bold text-dark mb-0"><?= (int)$pendingStudentsCount ?> Newly Registered Student<?= $pendingStudentsCount > 1 ? 's' : '' ?> Awaiting Course Verification</h6>
              <small class="text-secondary">Confirm they belong to the diploma course to activate their portal login &amp; send confirmation email.</small>
            </div>
          </div>
          <a href="index.php?page=admin_students&batch_status=pending" class="btn btn-warning btn-sm rounded-pill px-3.5 py-2 fw-bold text-dark shadow-xs text-nowrap">
            <i class="bi bi-check2-square me-1"></i> Review Pending Approvals (<?= (int)$pendingStudentsCount ?>) &rarr;
          </a>
        </div>
      <?php endif; ?>

      <!-- Batch Switcher Tabs & Filter Hub (Spacious & Clean Layout) -->
      <div class="card border rounded-4 bg-white shadow-sm p-3 p-md-3.5 mb-4">
        <!-- Row 1: Batch Status Tabs & Reset -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pb-3 border-bottom">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="fw-bold text-dark text-uppercase me-1 d-none d-sm-inline" style="font-size: 0.78rem; letter-spacing: 0.5px;">
              <i class="bi bi-people-fill text-danger me-1"></i> View Cohort:
            </span>
            <a href="index.php?page=admin_students&batch_status=active" 
               class="btn rounded-pill px-3.5 py-2 fw-semibold <?= ($batchStatus === 'active') ? 'text-white shadow-sm' : 'btn-light border text-secondary' ?>" 
               style="<?= ($batchStatus === 'active') ? 'background: #7A1418; border-color: #7A1418;' : '' ?> font-size: 0.88rem;">
              <i class="bi bi-broadcast me-1.5 <?= ($batchStatus === 'active') ? 'text-warning' : 'text-success' ?>"></i> 
              Active Batch Students (<?= $activeStudentsCount ?>)
            </a>

            <a href="index.php?page=admin_students&batch_status=pending" 
               class="btn rounded-pill px-3.5 py-2 fw-semibold <?= ($batchStatus === 'pending') ? 'btn-warning text-dark shadow-sm border-warning' : 'btn-light border text-secondary' ?>" 
               style="font-size: 0.88rem;">
              <i class="bi bi-hourglass-split me-1.5 <?= ($batchStatus === 'pending') ? 'text-dark' : 'text-warning' ?>"></i> 
              Pending Approval
              <?php if (!empty($pendingStudentsCount)): ?>
                <span class="badge bg-danger text-white rounded-pill ms-1"><?= (int)$pendingStudentsCount ?></span>
              <?php else: ?>
                (0)
              <?php endif; ?>
            </a>

            <a href="index.php?page=admin_students&batch_status=archived" 
               class="btn rounded-pill px-3.5 py-2 fw-semibold <?= ($batchStatus === 'archived') ? 'text-white shadow-sm' : 'btn-light border text-secondary' ?>" 
               style="<?= ($batchStatus === 'archived') ? 'background: #475569; border-color: #475569;' : '' ?> font-size: 0.88rem;">
              <i class="bi bi-archive-fill me-1.5 <?= ($batchStatus === 'archived') ? 'text-warning' : 'text-secondary' ?>"></i> 
              Old / Archived Batches (<?= $archivedStudentsCount ?>)
            </a>
          </div>

          <?php if (!empty($search) || $classFilter > 0): ?>
            <div>
              <a href="index.php?page=admin_students&batch_status=<?= e($batchStatus) ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs" style="font-size: 0.8rem;">
                <i class="bi bi-arrow-counterclockwise"></i> Reset Filters
              </a>
            </div>
          <?php endif; ?>
        </div>

        <!-- Row 2: Search & Batch Dropdown Controls (Clean Grid, Never Cramped or Dropped) -->
        <form action="index.php" method="GET" class="row g-2.5 align-items-center pt-3">
          <input type="hidden" name="page" value="admin_students">
          <input type="hidden" name="batch_status" value="<?= e($batchStatus) ?>">

          <!-- Batch Selector Dropdown -->
          <div class="col-12 col-md-4">
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0 text-muted ps-3"><i class="bi bi-mortarboard-fill text-danger"></i></span>
              <select name="class_id" class="form-select border-start-0 py-2 fw-medium" style="font-size: 0.9rem;" onchange="this.form.submit()">
                <option value="">-- All <?= ($batchStatus === 'archived') ? 'Archived' : 'Active' ?> Batches --</option>
                <?php 
                  $batchList = ($batchStatus === 'archived') ? $archivedBatches : $activeBatches;
                  foreach ($batchList as $b): 
                ?>
                  <option value="<?= (int)$b['id'] ?>" <?= ($classFilter === (int)$b['id']) ? 'selected' : '' ?>>
                    <?= e($b['class_name']) ?> (<?= $b['student_count'] ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Search Input Box -->
          <div class="col-12 col-md-5 flex-grow-1">
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0 text-muted ps-3"><i class="bi bi-search"></i></span>
              <input type="text" name="search" class="form-control border-start-0 py-2" placeholder="Search by name, student code, email, NIC..." value="<?= e($search) ?>" style="font-size: 0.9rem;">
            </div>
          </div>

          <!-- Filter Submit Button -->
          <div class="col-12 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-bold w-100 shadow-sm d-flex align-items-center justify-content-center gap-1.5" style="background: #7A1418; border-color: #7A1418; font-size: 0.92rem;">
              <i class="bi bi-funnel"></i> Filter
            </button>
            <?php if (!empty($search) || $classFilter > 0): ?>
              <a href="index.php?page=admin_students&batch_status=<?= e($batchStatus) ?>" class="btn btn-light border rounded-3 px-3 py-2 text-secondary d-flex align-items-center justify-content-center shadow-xs" title="Reset Filters">
                <i class="bi bi-x-lg"></i>
              </a>
            <?php endif; ?>
          </div>
        </form>

      </div>

      <!-- Students Table Card -->
      <div class="card border shadow-sm rounded-4 mb-4 bg-white">
        <!-- Table Header Banner Info -->
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light rounded-top-4 flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <span class="badge rounded-circle p-1.5 text-white d-flex align-items-center justify-content-center" style="background: <?= ($batchStatus === 'active') ? '#7A1418' : (($batchStatus === 'pending') ? '#d97706' : '#475569') ?>; width: 28px; height: 28px;">
              <i class="bi bi-<?= ($batchStatus === 'active') ? 'people-fill' : (($batchStatus === 'pending') ? 'hourglass-split' : 'archive-fill') ?>" style="font-size: 0.85rem;"></i>
            </span>
            <span class="fw-bold text-dark small">
              <?php if ($batchStatus === 'pending'): ?>
                Pending Student Approvals
              <?php elseif ($batchStatus === 'archived'): ?>
                Archived / Graduated Students Directory
              <?php else: ?>
                Currently Enrolled Students
              <?php endif; ?>
            </span>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill font-monospace" style="font-size: 0.72rem;">
              Showing <?= count($students) ?> students
            </span>
          </div>

          <?php if ($batchStatus === 'pending'): ?>
            <div class="form-check form-switch bg-light border rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-2 mb-0">
              <input class="form-check-input m-0" type="checkbox" role="switch" id="approveSendEmailToggle" <?= EmailSettings::shouldDefaultCheckAction('approval') ? 'checked' : '' ?> <?= !EmailSettings::isCategoryEnabled('approval') ? 'disabled' : '' ?> onchange="syncApproveEmailToggle(this.checked)">
              <label class="form-check-label small fw-semibold text-dark mb-0" for="approveSendEmailToggle" style="cursor:pointer;">
                <i class="bi bi-envelope-check text-success me-1"></i>Send approval email on Approve
              </label>
            </div>
          <?php elseif ($batchStatus === 'active' && !empty($currentActiveBatch)): ?>
            <div class="text-muted small d-none d-md-block">
              Current Cohort: <strong class="text-dark"><?= e($currentActiveBatch['class_name']) ?></strong> (<?= e($currentActiveBatch['academic_year']) ?>)
            </div>
          <?php endif; ?>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" style="font-size: 0.86rem;">
            <thead class="table-light">
              <tr style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">
                <th class="ps-3 py-3">Student Details</th>
                <th class="py-3">MIS Number</th>
                <th class="py-3">Contact Info</th>
                <th class="py-3">Cohort / Batch</th>
                <th class="py-3">Enrollment Status</th>
                <th class="py-3">Registered</th>
                <th class="text-end pe-3 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($students)): ?>
                <tr>
                  <td colspan="7" class="text-center py-5 text-muted">
                    <div class="rounded-circle p-3 bg-light mx-auto mb-2.5 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                      <i class="bi bi-person-x fs-3 text-secondary"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">No students found</h6>
                    <p class="small text-muted mb-0">No records found for <?= ($batchStatus === 'active') ? 'the active batch' : 'archived cohorts' ?>.</p>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($students as $st): ?>
                  <tr>
                    <!-- Name & Avatar -->
                    <td class="ps-3 py-3">
                      <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0" 
                             style="width: 38px; height: 38px; font-size: 0.85rem; background: <?= ($st['class_status'] === 'archived') ? '#475569' : 'linear-gradient(135deg, #7A1418, #90161b)' ?>;">
                          <?= strtoupper(substr($st['name'], 0, 1)) ?>
                        </div>
                        <div>
                          <div class="fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 0.92rem;">
                            <a href="javascript:void(0)" onclick="loadStudentDocs(<?= $st['id'] ?>, '<?= e(addslashes($st['name'])) ?>', '<?= e($st['mis_number'] ?? '') ?>', '<?= e($st['class_name'] ?? '') ?>')" class="text-dark text-decoration-none" title="Click to view documents">
                              <?= e($st['name']) ?>
                            </a>
                            <button type="button" class="btn btn-xs rounded-pill px-2 py-0.5 text-primary border border-primary-subtle bg-primary-subtle" style="font-size: 0.68rem; font-weight: 600;" onclick="loadStudentDocs(<?= $st['id'] ?>, '<?= e(addslashes($st['name'])) ?>', '<?= e($st['mis_number'] ?? '') ?>', '<?= e($st['class_name'] ?? '') ?>')" title="View Verification Documents">
                              <i class="bi bi-file-earmark-check me-0.5"></i> Docs
                            </button>
                          </div>
                          <small class="text-muted font-monospace" style="font-size: 0.74rem;">NIC: <?= e($st['nic'] ?? 'N/A') ?></small>
                        </div>
                      </div>
                    </td>

                    <!-- MIS Number -->
                    <td class="py-3">
                      <span class="badge bg-light text-dark border font-monospace fw-bold px-2.5 py-1" style="font-size: 0.76rem; letter-spacing: 0.3px;">
                        <i class="bi bi-person-badge text-primary me-1"></i><?= e($st['mis_number'] ?? 'N/A') ?>
                      </span>
                    </td>

                    <!-- Contact -->
                    <td class="py-3">
                      <div><a href="mailto:<?= e($st['email']) ?>" class="text-decoration-none small fw-medium"><?= e($st['email']) ?></a></div>
                      <small class="text-muted"><i class="bi bi-telephone text-secondary me-1"></i><?= e($st['phone'] ?? 'N/A') ?></small>
                    </td>

                    <!-- Cohort / Batch -->
                    <td class="py-3">
                      <div class="small fw-bold text-dark"><?= e($st['class_name'] ?? 'General') ?></div>
                      <small class="text-muted font-monospace" style="font-size: 0.72rem;"><?= e($st['academic_year'] ?? '2026/2027') ?></small>
                    </td>

                    <!-- Status -->
                    <td class="py-3">
                      <?php if ($st['class_status'] === 'archived'): ?>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                          <i class="bi bi-mortarboard-fill me-1"></i> Alumni / Graduated
                        </span>
                      <?php elseif ($st['status'] === 'active'): ?>
                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                          <i class="bi bi-check-circle-fill me-1"></i> Active Student
                        </span>
                      <?php elseif ($st['status'] === 'pending'): ?>
                        <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                          <i class="bi bi-clock-fill me-1"></i> Pending
                        </span>
                      <?php else: ?>
                        <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                          <i class="bi bi-pause-circle-fill me-1"></i> Suspended
                        </span>
                      <?php endif; ?>
                    </td>

                    <!-- Enrolled Date -->
                    <td class="small text-muted py-3 font-monospace" style="font-size: 0.75rem;">
                      <?= date('M d, Y', strtotime($st['created_at'])) ?>
                    </td>

                    <!-- Actions -->
                    <td class="text-end pe-3 py-3">
                      <div class="d-inline-flex align-items-center justify-content-end gap-1.5">
                        <?php if ($st['status'] === 'pending'): ?>
                          <form action="index.php?page=admin_update_student_status" method="POST" class="d-inline m-0 approve-student-form" onsubmit="return confirmApproveStudent(this);">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="student_id" value="<?= $st['id'] ?>">
                            <input type="hidden" name="status" value="active">
                            <input type="hidden" name="redirect_tab" value="<?= e($batchStatus) ?>">
                            <input type="hidden" name="email_prompt_present" value="1">
                            <input type="hidden" name="send_email_notification" class="approve-email-input" value="<?= EmailSettings::shouldDefaultCheckAction('approval') ? '1' : '0' ?>">
                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="font-size: 0.76rem;" title="Confirm & Approve Student">
                              <i class="bi bi-check-circle-fill"></i> Approve
                            </button>
                          </form>
                        <?php endif; ?>

                        <div class="dropdown d-inline-block">
                          <button class="btn btn-sm btn-light border dropdown-toggle rounded-pill px-3 py-1 shadow-xs" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' style="font-size: 0.78rem;">
                            Manage
                          </button>
                          <ul class="dropdown-menu dropdown-menu-end shadow border rounded-3" style="z-index: 1060;">
                            <li>
                              <button type="button" class="dropdown-item py-1.5 fw-semibold text-primary" onclick="loadStudentDocs(<?= $st['id'] ?>, '<?= e(addslashes($st['name'])) ?>', '<?= e($st['mis_number'] ?? '') ?>', '<?= e($st['class_name'] ?? '') ?>')">
                                <i class="bi bi-file-earmark-check me-2"></i>Verification Docs
                              </button>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <?php if ($st['class_status'] === 'archived'): ?>
                              <li>
                                <form action="index.php?page=admin_move_student_batch" method="POST" onsubmit="return confirm('Move this student into the current Active Batch and activate their login?');">
                                  <?= Csrf::field() ?>
                                  <input type="hidden" name="student_id" value="<?= $st['id'] ?>">
                                  <button type="submit" class="dropdown-item text-primary fw-semibold py-1.5"><i class="bi bi-arrow-up-right-circle me-2"></i>Move to Active Batch</button>
                                </form>
                              </li>
                            <?php endif; ?>

                            <?php if ($st['status'] !== 'active'): ?>
                              <li>
                                <form action="index.php?page=admin_update_student_status" method="POST">
                                  <?= Csrf::field() ?>
                                  <input type="hidden" name="student_id" value="<?= $st['id'] ?>">
                                  <input type="hidden" name="status" value="active">
                                  <input type="hidden" name="redirect_tab" value="<?= e($batchStatus) ?>">
                                  <input type="hidden" name="email_prompt_present" value="1">
                                  <input type="hidden" name="send_email_notification" value="1">
                                  <button type="submit" class="dropdown-item text-success fw-semibold py-1.5"><i class="bi bi-envelope-check me-2"></i>Approve &amp; Send Email</button>
                                </form>
                              </li>
                              <li>
                                <form action="index.php?page=admin_update_student_status" method="POST">
                                  <?= Csrf::field() ?>
                                  <input type="hidden" name="student_id" value="<?= $st['id'] ?>">
                                  <input type="hidden" name="status" value="active">
                                  <input type="hidden" name="redirect_tab" value="<?= e($batchStatus) ?>">
                                  <input type="hidden" name="email_prompt_present" value="1">
                                  <input type="hidden" name="send_email_notification" value="0">
                                  <button type="submit" class="dropdown-item text-secondary fw-semibold py-1.5"><i class="bi bi-check-circle me-2"></i>Approve Without Email</button>
                                </form>
                              </li>
                            <?php endif; ?>

                            <?php if ($st['status'] !== 'suspended'): ?>
                              <li>
                                <form action="index.php?page=admin_update_student_status" method="POST">
                                  <?= Csrf::field() ?>
                                  <input type="hidden" name="student_id" value="<?= $st['id'] ?>">
                                  <input type="hidden" name="status" value="suspended">
                                  <input type="hidden" name="redirect_tab" value="<?= e($batchStatus) ?>">
                                  <button type="submit" class="dropdown-item text-warning py-1.5"><i class="bi bi-pause-circle me-2"></i>Suspend Account</button>
                                </form>
                              </li>
                            <?php endif; ?>

                            <li><hr class="dropdown-divider my-1"></li>

                            <li>
                              <form action="index.php?page=admin_delete_student" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this student record?');">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="student_id" value="<?= $st['id'] ?>">
                                <button type="submit" class="dropdown-item text-danger py-1.5"><i class="bi bi-trash3 me-2"></i>Delete Record</button>
                              </form>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- ================= MODAL 1: REGISTER NEW STUDENT (ADMIN) ================= -->
<div class="modal fade" id="adminRegisterStudentModal" tabindex="-1" aria-labelledby="adminRegisterStudentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 rounded-4 shadow">
      <form action="index.php?page=admin_create_student" method="POST">
        <?= Csrf::field() ?>

        <div class="modal-header border-bottom py-3 px-4 bg-light">
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle p-2 text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #7A1418;">
              <i class="bi bi-person-plus-fill fs-5"></i>
            </div>
            <div>
              <h6 class="modal-title fw-bold text-dark mb-0" id="adminRegisterStudentModalLabel">Direct Student Registration</h6>
              <small class="text-muted">Register a new student into NVTI Baddegama Diploma portal</small>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label fw-bold text-dark small mb-1">Full Student Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" placeholder="e.g. Kasun Mihiran" required>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label fw-bold text-dark small mb-1">Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control" placeholder="e.g. kasun@gmail.com" required>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label fw-bold text-dark small mb-1">NIC Number <span class="text-danger">*</span></label>
              <input type="text" name="nic" class="form-control" placeholder="e.g. 200215409876" required>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label fw-bold text-dark small mb-1">Phone Number</label>
              <input type="text" name="phone" class="form-control" placeholder="e.g. 0761122334">
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label fw-bold text-dark small mb-1">Assign to Batch / Cohort <span class="text-danger">*</span></label>
              <select name="class_id" class="form-select" required>
                <?php foreach ($allBatches as $b): ?>
                  <option value="<?= (int)$b['id'] ?>" <?= ($b['status'] === 'active') ? 'selected' : '' ?>>
                    <?= e($b['class_name']) ?> (<?= ($b['status'] === 'active') ? 'Active Cohort' : 'Archived' ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label fw-bold text-dark small mb-1">Initial Password</label>
              <input type="text" name="password" class="form-control" value="nvti1234">
              <small class="text-muted" style="font-size: 0.72rem;">Student can change this password after first login.</small>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-light px-4 py-3 border-top">
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" style="background: #7A1418; border-color: #7A1418;">
            <i class="bi bi-check-circle me-1"></i> Register Student
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= MODAL 2: CREATE NEW ACADEMIC BATCH ================= -->
<div class="modal fade" id="createBatchModal" tabindex="-1" aria-labelledby="createBatchModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <form action="index.php?page=admin_create_batch" method="POST">
        <?= Csrf::field() ?>

        <div class="modal-header border-bottom py-3 px-4 bg-light">
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle p-2 text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #b45309;">
              <i class="bi bi-plus-circle-fill fs-5"></i>
            </div>
            <div>
              <h6 class="modal-title fw-bold text-dark mb-0" id="createBatchModalLabel">Create New Academic Batch</h6>
              <small class="text-muted">Start a new intake cohort for NVTI Baddegama</small>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-bold text-dark small mb-1">Batch / Class Name <span class="text-danger">*</span></label>
            <input type="text" name="class_name" class="form-control" placeholder="e.g. NVTI Baddegama ICT L5 - Batch 2027" value="NVTI Baddegama ICT L5 - Batch <?= date('Y') + 1 ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold text-dark small mb-1">Academic Year <span class="text-danger">*</span></label>
            <input type="text" name="academic_year" class="form-control" placeholder="e.g. <?= (date('Y') + 1) . '/' . (date('Y') + 2) ?>" value="<?= (date('Y') + 1) . '/' . (date('Y') + 2) ?>" required>
          </div>

          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="set_active" id="setBatchActive" value="1" checked>
            <label class="form-check-label fw-semibold text-dark small" for="setBatchActive">
              Set as Current Active Intake Batch
            </label>
          </div>

          <div class="form-check form-switch p-3 rounded-3" style="background: #fef2f2; border: 1px solid #fecaca;">
            <input class="form-check-input ms-0 me-2" type="checkbox" name="archive_previous" id="archivePrevBatch" value="1">
            <label class="form-check-label fw-bold text-danger small" for="archivePrevBatch">
              Automatically Archive &amp; Graduate Previous Active Batch
            </label>
            <div class="text-muted small ps-4" style="font-size: 0.74rem;">
              Moves the current batch (<?= !empty($currentActiveBatch) ? e($currentActiveBatch['class_name']) : 'Batch 2026' ?>) to the "Old / Archived Batches" section safely.
            </div>
          </div>
        </div>

        <div class="modal-footer bg-light px-4 py-3 border-top">
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">
            <i class="bi bi-check2-circle me-1"></i> Create Batch
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= MODAL 3: ARCHIVE / GRADUATE ACTIVE BATCH ================= -->
<div class="modal fade" id="archiveBatchModal" tabindex="-1" aria-labelledby="archiveBatchModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <form action="index.php?page=admin_archive_batch" method="POST">
        <?= Csrf::field() ?>

        <div class="modal-header border-bottom py-3 px-4 bg-light">
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle p-2 text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #7A1418;">
              <i class="bi bi-mortarboard-fill fs-5"></i>
            </div>
            <div>
              <h6 class="modal-title fw-bold text-dark mb-0" id="archiveBatchModalLabel">Graduate &amp; Archive Batch</h6>
              <small class="text-muted">Move completed cohort to Alumni archives</small>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <!-- Clear Security & Archival Warning Box -->
          <div class="alert alert-warning border-0 rounded-3 p-3 mb-3 d-flex align-items-start gap-2.5" style="background: #fffbeb;">
            <i class="bi bi-exclamation-triangle-fill text-warning fs-5 flex-shrink-0 mt-0.5"></i>
            <div class="small text-secondary lh-base">
              <strong class="text-dark d-block mb-1">Important: Account Suspension &amp; Data Safety</strong>
              Once graduated, all student user accounts in this batch will be <strong>automatically suspended</strong> so they can no longer log in to active courses on the LMS.
            </div>
          </div>

          <div class="card border rounded-3 p-3 mb-3 bg-light">
            <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-shield-check text-success me-1"></i> What happens during archival:</h6>
            <ul class="text-secondary small ps-3 mb-0" style="font-size: 0.8rem; line-height: 1.6;">
              <li>All students move to the <strong>"Old / Archived Batches"</strong> directory.</li>
              <li>Attendance histories, past assignments, and verification documents are <strong>100% permanently preserved</strong>.</li>
              <li>The active LMS stream will clear and be ready for the new student intake.</li>
            </ul>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold text-dark small mb-1">Select Batch to Graduate &amp; Archive <span class="text-danger">*</span></label>
            <select name="class_id" class="form-select rounded-3 py-2" required>
              <?php foreach ($activeBatches as $ab): ?>
                <option value="<?= (int)$ab['id'] ?>">
                  <?= e($ab['class_name']) ?> (<?= $ab['student_count'] ?> Active Students)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-check mt-3 pt-2 border-top">
            <input class="form-check-input" type="checkbox" id="confirmArchiveConsent" required>
            <label class="form-check-label small text-secondary fw-semibold" for="confirmArchiveConsent">
              I confirm the graduation of this batch and acknowledge that these student accounts will be suspended from LMS login.
            </label>
          </div>
        </div>

        <div class="modal-footer bg-light px-4 py-3 border-top">
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" style="background: #7A1418; border-color: #7A1418;">
            <i class="bi bi-mortarboard-fill me-1"></i> Graduate Batch &amp; Suspend Access
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= STUDENT VERIFICATION DOCUMENTS MODAL ================= -->
<div class="modal fade" id="studentDocsModal" tabindex="-1" aria-labelledby="studentDocsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-bottom py-3 px-4 bg-light">
        <div class="d-flex align-items-center gap-2.5">
          <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
            <i class="bi bi-folder2-open fs-5"></i>
          </div>
          <div>
            <h6 class="modal-title fw-bold text-dark mb-0" id="docsModalStudentName">Student Documents</h6>
            <small class="text-muted" id="docsModalStudentMeta"></small>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <div id="docsModalLoading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="text-muted small mt-2">Loading student verification files...</p>
        </div>

        <div id="docsModalContent" class="d-none">
          <div class="list-group list-group-flush border rounded-3 overflow-hidden" id="docsModalList">
            <!-- Populated via JavaScript -->
          </div>
        </div>
      </div>

      <div class="modal-footer bg-light px-4 py-2.5 border-top">
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
function loadStudentDocs(studentId, studentName, studentCode, className) {
  document.getElementById('docsModalStudentName').textContent = studentName + (studentCode ? ' (' + studentCode + ')' : '');
  document.getElementById('docsModalStudentMeta').textContent = (className ? className + ' • ' : '') + 'Student ID #' + studentId;
  
  const loading = document.getElementById('docsModalLoading');
  const content = document.getElementById('docsModalContent');
  const list = document.getElementById('docsModalList');
  
  loading.classList.remove('d-none');
  content.classList.add('d-none');
  list.innerHTML = '';

  const modal = new bootstrap.Modal(document.getElementById('studentDocsModal'));
  modal.show();

  fetch('index.php?page=admin_get_student_documents&student_id=' + studentId)
    .then(res => res.json())
    .then(data => {
      loading.classList.add('d-none');
      content.classList.remove('d-none');

      if (!data.success || !data.documents) {
        list.innerHTML = '<div class="p-4 text-center text-muted">Failed to retrieve documents.</div>';
        return;
      }

      let html = '';
      for (const [key, item] of Object.entries(data.documents)) {
        const meta = item.meta;
        const isUploaded = item.uploaded && item.doc;
        const doc = item.doc;

        let statusBadge = '';
        let actionBtn = '';

        if (!isUploaded) {
          statusBadge = '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1">Missing / Not Uploaded</span>';
          actionBtn = '<span class="text-muted small fst-italic">No file</span>';
        } else if (doc.status === 'verified') {
          statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1"><i class="bi bi-patch-check-fill me-1"></i> Verified</span>';
          actionBtn = `<a href="index.php?page=view_student_document&id=${doc.id}" target="_blank" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold">
                        <i class="bi bi-eye me-1"></i> View Document
                       </a>`;
        } else if (doc.status === 'rejected') {
          statusBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1"><i class="bi bi-x-circle-fill me-1"></i> Rejected</span>';
          actionBtn = `<a href="index.php?page=view_student_document&id=${doc.id}" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold">
                        <i class="bi bi-eye me-1"></i> Inspect
                       </a>`;
        } else {
          statusBadge = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1"><i class="bi bi-clock-fill me-1"></i> Pending Review</span>';
          actionBtn = `<a href="index.php?page=view_student_document&id=${doc.id}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
                        <i class="bi bi-eye me-1"></i> Inspect File
                       </a>`;
        }

        html += `
          <div class="list-group-item p-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-3 p-2 d-flex align-items-center justify-content-center text-primary bg-light" style="width: 42px; height: 42px;">
                <i class="bi ${meta.icon} fs-5"></i>
              </div>
              <div>
                <div class="fw-bold text-dark small">${meta.title}</div>
                <div class="text-muted" style="font-size: 0.72rem;">${isUploaded ? doc.original_filename : 'Required for NVQ Level 5 Verification'}</div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2 justify-content-between justify-content-sm-end">
              ${statusBadge}
              ${actionBtn}
            </div>
          </div>
        `;
      }

      list.innerHTML = html;
    })
    .catch(err => {
      loading.classList.add('d-none');
      content.classList.remove('d-none');
      list.innerHTML = '<div class="p-4 text-center text-danger">Error connecting to server.</div>';
    });
}

function syncApproveEmailToggle(checked) {
  document.querySelectorAll('.approve-email-input').forEach(function (inp) {
    inp.value = checked ? '1' : '0';
  });
}

function confirmApproveStudent(formEl) {
  const emailInp = formEl.querySelector('.approve-email-input');
  const willEmail = emailInp && emailInp.value === '1';
  const msg = willEmail
    ? 'Confirm this student belongs to the NVTI Diploma course and send approval email?'
    : 'Confirm and approve this student WITHOUT sending an email notification?';
  return confirm(msg);
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
