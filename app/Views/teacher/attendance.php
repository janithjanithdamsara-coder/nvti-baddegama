<?php
/**
 * Teacher Attendance & Monitor Management View
 * EduCore LMS - NVTI Baddegama
 */

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

$activeTab = $tab ?? 'daily';
?>

<div class="app-wrapper">
  <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <div class="container-fluid">
      <?= Flash::render() ?>

      <!-- Header Banner -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
        <div class="card-body p-4">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 fw-semibold">
                  <i class="bi bi-calendar2-check-fill me-1"></i> Academic Attendance Portal
                </span>
                <span class="badge bg-primary-subtle text-primary border px-2.5 py-1">
                  NVQ Level 5 ICT
                </span>
              </div>
              <h3 class="fw-bold text-dark mb-1">Attendance &amp; Class Monitors</h3>
              <p class="text-muted small mb-0">
                <?= htmlspecialchars($className ?? 'NVTI Baddegama ICT L5 - Batch 2026') ?> &bull; Instructor: <strong><?= e(Auth::user()['name']) ?></strong>
              </p>
            </div>

            <!-- Quick Nav Tabs -->
            <ul class="nav nav-pills bg-light p-1.5 rounded-3 border" role="tablist">
              <li class="nav-item">
                <a class="nav-link <?= ($activeTab === 'daily') ? 'active fw-bold' : 'text-secondary' ?> py-1.5 px-3" href="index.php?page=teacher_attendance&tab=daily&date=<?= urlencode($selectedDate) ?>">
                  <i class="bi bi-calendar-day me-1"></i> Daily Sheet
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= ($activeTab === 'monitors') ? 'active fw-bold' : 'text-secondary' ?> py-1.5 px-3" href="index.php?page=teacher_attendance&tab=monitors">
                  <i class="bi bi-people-fill me-1"></i> Class Monitors
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= ($activeTab === 'monthly') ? 'active fw-bold' : 'text-secondary' ?> py-1.5 px-3" href="index.php?page=teacher_attendance&tab=monthly&month=<?= urlencode($selectedMonth) ?>">
                  <i class="bi bi-grid-3x3-gap-fill me-1"></i> Monthly Matrix &amp; Export
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- ================= TAB 1: DAILY ATTENDANCE ================= -->
      <?php if ($activeTab === 'daily'): 
        $isLocked = (int)($dayLock['is_locked'] ?? 0) === 1;
        $verifStatus = $dayLock['verification_status'] ?? 'pending_verification';
        $markedRole = $dayLock['marked_by_role'] ?? 'student';
        $isSubmittedByMonitor = $isLocked && $markedRole === 'student' && $verifStatus === 'pending_verification';
        $isVerifiedOfficial = $isLocked && $verifStatus === 'verified';
        $isNotSubmitted = !$dayLock || !$isLocked;
      ?>
        <!-- Date Selector Header & Quick Action Bar -->
        <div class="card border rounded-4 shadow-sm bg-white mb-3">
          <div class="card-body p-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
              <form action="index.php" method="GET" class="d-flex align-items-center gap-2">
                <input type="hidden" name="page" value="teacher_attendance">
                <input type="hidden" name="tab" value="daily">
                <label for="attDate" class="small fw-bold text-dark text-nowrap mb-0"><i class="bi bi-calendar-event text-primary me-1"></i>Select Date:</label>
                <input type="date" class="form-control form-control-sm border-secondary-subtle" id="attDate" name="date" value="<?= e($selectedDate) ?>" max="<?= date('Y-m-d') ?>" required>
                <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">Load Sheet</button>
              </form>

              <!-- Quick Mark Helpers & Holiday Button -->
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-success btn-sm fw-semibold rounded-pill px-3 shadow-xs" onclick="markAllStatus('present')" <?= ($holiday || $isVerifiedOfficial) ? 'disabled' : '' ?>>
                  <i class="bi bi-check-all me-1"></i> All Present
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm fw-semibold rounded-pill px-3 shadow-xs" onclick="markAllStatus('absent')" <?= ($holiday || $isVerifiedOfficial) ? 'disabled' : '' ?>>
                  <i class="bi bi-x-circle me-1"></i> All Absent
                </button>
                <button type="button" class="btn btn-outline-warning text-dark btn-sm fw-semibold rounded-pill px-3 shadow-xs" data-bs-toggle="modal" data-bs-target="#declareHolidayModal">
                  <i class="bi bi-calendar2-x-fill text-warning me-1"></i> <?= $holiday ? 'Update Holiday' : 'Mark Holiday / Closed' ?>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Holiday / Institute Closure Notice Banner -->
        <?php if ($holiday): ?>
          <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white mb-4" style="border-top: 5px solid #f59e0b !important; background: linear-gradient(180deg, #fffbeb 0%, #ffffff 100%) !important;">
            <div class="rounded-circle p-3 mx-auto mb-2 d-flex align-items-center justify-content-center text-warning bg-white shadow-sm" style="width: 64px; height: 64px;">
              <i class="bi bi-calendar2-heart-fill fs-2 text-warning"></i>
            </div>
            <span class="badge bg-warning text-dark fw-bold rounded-pill px-3 py-1 mb-2">OFFICIAL HOLIDAY / INSTITUTE CLOSURE</span>
            <h4 class="fw-bold text-dark mb-1"><?= e($holiday['holiday_name']) ?></h4>
            <p class="text-secondary small mb-3" style="max-width: 620px; margin: 0 auto;">
              <strong>Category:</strong> <?= e(ucwords(str_replace('_', ' ', $holiday['holiday_type']))) ?> &bull; 
              <?= e($holiday['description'] ?: 'NVTI Baddegama is officially closed today. No student roll call is required.') ?>
            </p>
            <div class="d-flex align-items-center justify-content-center gap-2">
              <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#declareHolidayModal">
                <i class="bi bi-pencil me-1"></i> Edit Details
              </button>
              <form action="index.php?page=teacher_remove_holiday" method="POST" onsubmit="return confirm('Remove holiday declaration for this date and re-open attendance marking?');" class="d-inline">
                <?= Csrf::field() ?>
                <input type="hidden" name="date" value="<?= e($selectedDate) ?>">
                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3.5 fw-semibold">
                  <i class="bi bi-trash3 me-1"></i> Remove Holiday (Re-open Roll Call)
                </button>
              </form>
            </div>
          </div>
        <?php endif; ?>

        <!-- Weekend Alert Notice (NVTI Diploma 5 Days / Week) -->
        <?php if ($isWeekend && !$holiday): ?>
          <div class="alert border-0 shadow-sm rounded-4 p-3 mb-4 text-dark" style="background: #f8fafc; border-left: 5px solid #64748b !important;">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle p-2 d-flex align-items-center justify-content-center text-secondary bg-white shadow-sm flex-shrink-0" style="width: 44px; height: 44px;">
                <i class="bi bi-calendar-week fs-4"></i>
              </div>
              <div>
                <span class="badge bg-secondary text-white fw-bold rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">WEEKEND (SATURDAY / SUNDAY)</span>
                <h6 class="fw-bold mb-0 text-dark">Weekend Session &bull; NVTI Baddegama Regular Academic Days are Monday to Friday (5 Days)</h6>
                <small class="text-secondary">Official diploma attendance runs Monday through Friday. If you held a special weekend revision or lab session, you may mark roll call below.</small>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <!-- Submission & Verification Status Alert Card -->
        <?php if (!$holiday): ?>
          <?php if ($isSubmittedByMonitor): ?>
            <div class="alert border-0 shadow-sm rounded-4 p-3 mb-4 text-dark" style="background: linear-gradient(90deg, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #f59e0b !important;">
              <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                  <div class="rounded-circle p-2 d-flex align-items-center justify-content-center text-warning bg-white shadow-sm flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="bi bi-clock-history fs-4"></i>
                  </div>
                  <div>
                    <div class="d-flex align-items-center gap-2 mb-0.5">
                      <span class="badge bg-warning text-dark fw-bold rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">SUBMITTED BY CLASS MONITOR</span>
                      <span class="text-muted small">&bull; Pending Sir / Admin Verification</span>
                    </div>
                    <h6 class="fw-bold mb-0 text-dark">Monitor <?= e($dayLock['marked_by_name'] ?? 'Class Monitor') ?> submitted roll call at <?= date('h:i A', strtotime($dayLock['submitted_at'])) ?></h6>
                    <small class="text-secondary">Please inspect the students below, make any corrections if needed, and click <strong>"Verify &amp; Approve Attendance"</strong> to finalize and lock the official daily report.</small>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <button type="submit" form="teacherDailyForm" name="action" value="verify" class="btn btn-success btn-sm fw-bold px-3 py-2 rounded-pill shadow-sm d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Verify &amp; Approve Attendance</span>
                  </button>
                  <form action="index.php?page=teacher_attendance_unlock" method="POST" onsubmit="return confirm('Unlock this date so student monitor can re-mark?');" class="d-inline">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="date" value="<?= e($selectedDate) ?>">
                    <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 fw-semibold">
                      <i class="bi bi-unlock-fill me-1"></i> Unlock
                    </button>
                  </form>
                </div>
              </div>
            </div>
          <?php elseif ($isVerifiedOfficial): ?>
            <!-- FINALIZED & LOCKED BANNER -->
            <div class="alert border-0 shadow-sm rounded-4 p-4 mb-4 text-dark" style="background: linear-gradient(90deg, #f0fdf4 0%, #dcfce7 100%); border-left: 5px solid #10b981 !important;">
              <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                  <div class="rounded-circle p-2 d-flex align-items-center justify-content-center text-success bg-white shadow-sm flex-shrink-0" style="width: 50px; height: 50px;">
                    <i class="bi bi-shield-lock-fill fs-3 text-success"></i>
                  </div>
                  <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                      <span class="badge bg-success text-white fw-bold rounded-pill px-3 py-1" style="font-size: 0.74rem;">
                        <i class="bi bi-patch-check-fill me-1"></i> ATTENDANCE FINALIZED &amp; LOCKED
                      </span>
                      <span class="text-success fw-semibold small">&bull; Record Closed for Today</span>
                    </div>
                    <h5 class="fw-bold mb-1 text-dark">Today's Attendance Officially Finalized &amp; Locked</h5>
                    <p class="text-secondary small mb-0">
                      අද දින (<?= date('l, d F Y', strtotime($selectedDate)) ?>) පැමිණීම සාර්ථකව අවසන් කර Lock කරන ලදී. ඊළඟ පැමිණීම සටහන් කිරීම හෙට දින විවෘත වේ.
                      <br><span class="text-muted">Recorded &amp; Finalized by <?= e($dayLock['verified_by_name'] ?? $dayLock['marked_by_name'] ?? 'Instructor') ?> (Role: <?= strtoupper(e($dayLock['marked_by_role'] ?? 'OFFICIAL')) ?>) at <?= date('h:i A', strtotime($dayLock['verified_at'] ?? $dayLock['submitted_at'])) ?></span>
                    </p>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <!-- Unlock and Re-edit Button -->
                  <form action="index.php?page=teacher_attendance_unlock" method="POST" onsubmit="return confirm('Unlock this sheet so you can make edits or corrections?');" class="d-inline">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="date" value="<?= e($selectedDate) ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3.5 py-2 fw-semibold shadow-xs">
                      <i class="bi bi-unlock-fill me-1"></i> Unlock &amp; Edit Sheet
                    </button>
                  </form>
                </div>
              </div>
            </div>
          <?php else: ?>
            <div class="alert border-0 shadow-sm rounded-4 p-3 mb-4 text-dark" style="background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 100%); border-left: 5px solid #64748b !important;">
              <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                  <div class="rounded-circle p-2 d-flex align-items-center justify-content-center text-secondary bg-white shadow-sm flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="bi bi-hourglass-split fs-4"></i>
                  </div>
                  <div>
                    <div class="d-flex align-items-center gap-2 mb-0.5">
                      <span class="badge bg-secondary text-white fw-bold rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">PENDING SUBMISSION</span>
                    </div>
                    <h6 class="fw-bold mb-0 text-dark">Class Monitor has not submitted attendance for <?= date('l, d F Y', strtotime($selectedDate)) ?> yet</h6>
                    <small class="text-secondary">You can mark students directly below and click <strong>"Save &amp; Finalize Attendance"</strong> to close today's sheet.</small>
                  </div>
                </div>
                <div>
                  <button type="submit" form="teacherDailyForm" name="action" value="save" class="btn btn-primary btn-sm fw-bold px-3.5 py-2 rounded-pill shadow-sm" style="background: #7A1418; border-color: #7A1418;">
                    <i class="bi bi-save me-1"></i> Save &amp; Finalize Attendance
                  </button>
                </div>
              </div>
            </div>
          <?php endif; ?>
        <?php endif; ?>

        <?php if (!$holiday): ?>
        <!-- Daily Roll Call Grid Form -->
        <form action="index.php?page=teacher_attendance_save" method="POST" id="teacherDailyForm">
          <?= Csrf::field() ?>
          <input type="hidden" name="date" value="<?= e($selectedDate) ?>">

          <div class="card border rounded-4 shadow-sm bg-white overflow-hidden mb-4">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
              <div>
                <strong class="text-dark"><i class="bi bi-people-fill text-primary me-2"></i>Class Roll Call (<?= count($students) ?> Students)</strong>
                <span class="text-muted small ms-2">&bull; Date: <?= date('l, d F Y', strtotime($selectedDate)) ?></span>
              </div>
              <div>
                <?php if ($isVerifiedOfficial): ?>
                  <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-bold">
                    <i class="bi bi-lock-fill me-1"></i> Finalized &amp; Locked for Today
                  </span>
                <?php else: ?>
                  <button type="submit" class="btn btn-primary btn-sm px-3.5 py-1.5 fw-bold rounded-pill shadow-sm" style="background: #7A1418; border-color: #7A1418;">
                    <i class="bi bi-check2-circle me-1"></i> <?= $isSubmittedByMonitor ? 'Verify, Approve &amp; Finalize' : 'Save &amp; Finalize' ?>
                  </button>
                <?php endif; ?>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                  <tr>
                    <th style="width: 45px;" class="text-center py-2.5">#</th>
                    <th style="width: 290px;" class="py-2.5">Student Information</th>
                    <th style="width: 160px;" class="py-2.5">MIS &amp; NIC Number</th>
                    <th style="width: 330px;" class="text-center py-2.5">Attendance Status</th>
                    <th class="py-2.5 text-end pe-3">Record Details</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $idx = 1; foreach ($students as $st): 
                    $stId = (int)$st['user_id'];
                    $curAtt = $existingAttendance[$stId]['status'] ?? 'present';
                    $markedBy = $existingAttendance[$stId]['marked_by_name'] ?? null;
                  ?>
                    <tr>
                      <td class="text-center text-muted fw-bold"><?= $idx++ ?></td>
                      <td>
                        <div class="d-flex align-items-center gap-2.5">
                          <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-primary fw-bold flex-shrink-0" style="width: 36px; height: 36px; font-size: 0.82rem;">
                            <?= strtoupper(substr($st['name'], 0, 1)) ?>
                          </div>
                          <div>
                            <div class="fw-bold text-dark mb-0 text-truncate" style="max-width: 230px;"><?= e($st['name']) ?></div>
                            <small class="text-muted font-monospace"><?= e($st['student_code']) ?></small>
                          </div>
                        </div>
                      </td>
                      <td>
                        <span class="badge bg-light text-dark border font-monospace"><?= e($st['mis_number'] ?? 'N/A') ?></span>
                        <div class="text-muted" style="font-size: 0.72rem;"><?= e($st['nic'] ?? '') ?></div>
                      </td>
                      <td class="text-center">
                        <?php if ($isVerifiedOfficial): ?>
                          <!-- Clean Finalized Read-Only Badge (Inputs closed) -->
                          <input type="hidden" name="attendance[<?= $stId ?>][status]" value="<?= e($curAtt) ?>">
                          <?php if ($curAtt === 'present'): ?>
                            <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 0.8rem;">
                              <i class="bi bi-check-circle-fill me-1 text-success"></i> Present
                            </span>
                          <?php elseif ($curAtt === 'late'): ?>
                            <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background: #fefce8; color: #854d0e; border: 1px solid #fef08a; font-size: 0.8rem;">
                              <i class="bi bi-clock-fill me-1 text-warning"></i> Late
                            </span>
                          <?php elseif ($curAtt === 'absent'): ?>
                            <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; font-size: 0.8rem;">
                              <i class="bi bi-x-circle-fill me-1 text-danger"></i> Absent
                            </span>
                          <?php else: ?>
                            <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 0.8rem;">
                              <i class="bi bi-info-circle-fill me-1 text-primary"></i> Excused
                            </span>
                          <?php endif; ?>
                        <?php else: ?>
                          <!-- Editable Radio Selection -->
                          <div class="attendance-pill-group" role="group">
                            <!-- Present -->
                            <input type="radio" class="btn-check" name="attendance[<?= $stId ?>][status]" id="t_status_<?= $stId ?>_present" value="present" <?= ($curAtt === 'present') ? 'checked' : '' ?>>
                            <label class="att-pill-label att-pill-present" for="t_status_<?= $stId ?>_present">
                              <i class="bi bi-check-circle-fill me-1"></i> Present
                            </label>

                            <!-- Late -->
                            <input type="radio" class="btn-check" name="attendance[<?= $stId ?>][status]" id="t_status_<?= $stId ?>_late" value="late" <?= ($curAtt === 'late') ? 'checked' : '' ?>>
                            <label class="att-pill-label att-pill-late" for="t_status_<?= $stId ?>_late">
                              <i class="bi bi-clock-fill me-1"></i> Late
                            </label>

                            <!-- Absent -->
                            <input type="radio" class="btn-check" name="attendance[<?= $stId ?>][status]" id="t_status_<?= $stId ?>_absent" value="absent" <?= ($curAtt === 'absent') ? 'checked' : '' ?>>
                            <label class="att-pill-label att-pill-absent" for="t_status_<?= $stId ?>_absent">
                              <i class="bi bi-x-circle-fill me-1"></i> Absent
                            </label>

                            <!-- Excused -->
                            <input type="radio" class="btn-check" name="attendance[<?= $stId ?>][status]" id="t_status_<?= $stId ?>_excused" value="excused" <?= ($curAtt === 'excused') ? 'checked' : '' ?>>
                            <label class="att-pill-label att-pill-excused" for="t_status_<?= $stId ?>_excused">
                              Excused
                            </label>
                          </div>
                        <?php endif; ?>
                      </td>
                      <td class="text-end pe-3">
                        <?php if ($markedBy): ?>
                          <span class="badge bg-light text-secondary border" style="font-size: 0.72rem;">
                            <i class="bi bi-person-check me-1"></i> Marked by: <?= e($markedBy) ?>
                          </span>
                        <?php else: ?>
                          <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">Not Recorded</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div class="card-footer bg-white p-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 border-top">
              <?php if ($isVerifiedOfficial): ?>
                <span class="small text-muted"><i class="bi bi-lock-fill text-success me-1"></i> Attendance is finalized and locked. Next roll call opens tomorrow morning.</span>
                <form action="index.php?page=teacher_attendance_unlock" method="POST" onsubmit="return confirm('Do you want to unlock this sheet to make corrections?');" class="d-inline">
                  <?= Csrf::field() ?>
                  <input type="hidden" name="date" value="<?= e($selectedDate) ?>">
                  <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-4 py-2 fw-semibold shadow-xs">
                    <i class="bi bi-unlock-fill me-1"></i> Unlock &amp; Edit Sheet
                  </button>
                </form>
              <?php else: ?>
                <span class="small text-muted"><i class="bi bi-shield-check text-success me-1"></i> Instructors hold ultimate authority to inspect, correct, and officially verify records.</span>
                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm rounded-pill" style="background: #7A1418; border-color: #7A1418;">
                  <i class="bi bi-check2-circle me-1"></i> <?= $isSubmittedByMonitor ? 'Verify, Approve &amp; Finalize Attendance' : 'Save &amp; Finalize Attendance' ?>
                </button>
              <?php endif; ?>
            </div>
          </div>
        </form>
        <?php endif; ?>

      <!-- ================= TAB 2: CLASS MONITORS ================= -->
      <?php elseif ($activeTab === 'monitors'): ?>
        <div class="row g-4">
          <!-- Assign Form Column -->
          <div class="col-lg-5">
            <div class="card border rounded-4 shadow-sm bg-white">
              <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-person-plus-fill text-primary me-2"></i>Appoint Class Monitor</h5>
                <small class="text-muted">Delegate daily attendance marking to 1 or 2 students</small>
              </div>
              <div class="card-body p-4">
                <form action="index.php?page=teacher_attendance_assign" method="POST">
                  <?= Csrf::field() ?>

                  <div class="mb-3">
                    <label for="monitorStudent" class="form-label fw-semibold small text-dark">Select Student from Batch</label>
                    <select class="form-select border-secondary-subtle" id="monitorStudent" name="student_id" required>
                      <option value="">-- Choose Student --</option>
                      <?php foreach ($students as $st): ?>
                        <option value="<?= (int)$st['user_id'] ?>">
                          <?= e($st['name']) ?> (<?= e($st['student_code']) ?> &bull; <?= e($st['mis_number'] ?? 'No MIS') ?>)
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="monitorRole" class="form-label fw-semibold small text-dark">Monitor Assignment Role</label>
                    <select class="form-select border-secondary-subtle" id="monitorRole" name="monitor_role" required>
                      <option value="primary">Primary Monitor (Principal Mark)</option>
                      <option value="backup">Backup Monitor (Marks if Primary Absent)</option>
                    </select>
                    <div class="form-text small text-muted">
                      Both monitors can mark if active, but once one marks a date, it locks for that day.
                    </div>
                  </div>

                  <div class="mb-4">
                    <label for="monitorStatus" class="form-label fw-semibold small text-dark">Initial Status</label>
                    <select class="form-select border-secondary-subtle" id="monitorStatus" name="status" required>
                      <option value="active">Active (Permitted to mark)</option>
                      <option value="disabled">Disabled (Cannot mark)</option>
                    </select>
                  </div>

                  <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3">
                    <i class="bi bi-check-circle me-1"></i> Save Monitor Assignment
                  </button>
                </form>
              </div>
            </div>
          </div>

          <!-- Current Monitors List Column -->
          <div class="col-lg-7">
            <div class="card border rounded-4 shadow-sm bg-white overflow-hidden">
              <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                  <h5 class="fw-bold text-dark mb-0"><i class="bi bi-shield-check text-success me-2"></i>Current Appointed Monitors</h5>
                  <small class="text-muted">Active delegates can access the Excel attendance grid</small>
                </div>
                <span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1">
                  <?= count($monitors) ?> Assigned
                </span>
              </div>

              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
                  <thead class="table-light text-secondary text-uppercase" style="font-size: 0.78rem;">
                    <tr>
                      <th>Student</th>
                      <th>Role</th>
                      <th>Status</th>
                      <th class="text-end">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($monitors)): ?>
                      <tr>
                        <td colspan="4" class="text-center py-4 text-muted">No monitors appointed yet. Use the form on the left to appoint.</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($monitors as $m): ?>
                        <tr>
                          <td>
                            <div class="fw-bold text-dark mb-0"><?= e($m['student_name']) ?></div>
                            <small class="text-muted font-monospace"><?= e($m['student_code']) ?> &bull; <?= e($m['mis_number'] ?? 'N/A') ?></small>
                          </td>
                          <td>
                            <?php if ($m['monitor_role'] === 'primary'): ?>
                              <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                <i class="bi bi-star-fill me-1"></i> Primary
                              </span>
                            <?php else: ?>
                              <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                <i class="bi bi-shield me-1"></i> Backup
                              </span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?php if ($m['status'] === 'active'): ?>
                              <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                <i class="bi bi-check-circle-fill me-1"></i> Active
                              </span>
                            <?php else: ?>
                              <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                <i class="bi bi-slash-circle me-1"></i> Disabled
                              </span>
                            <?php endif; ?>
                          </td>
                          <td class="text-end">
                            <form action="index.php?page=teacher_attendance_toggle" method="POST" class="d-inline">
                              <?= Csrf::field() ?>
                              <input type="hidden" name="monitor_id" value="<?= (int)$m['id'] ?>">
                              <?php if ($m['status'] === 'active'): ?>
                                <input type="hidden" name="status" value="disabled">
                                <button type="submit" class="btn btn-sm btn-outline-warning fw-semibold" title="Temporarily disable monitor access">
                                  <i class="bi bi-pause-fill me-1"></i> Disable
                                </button>
                              <?php else: ?>
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="btn btn-sm btn-outline-success fw-semibold" title="Reactivate monitor access">
                                  <i class="bi bi-play-fill me-1"></i> Enable
                                </button>
                              <?php endif; ?>
                            </form>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

              <div class="card-footer bg-light p-3 border-top">
                <small class="text-muted d-block">
                  <i class="bi bi-shield-check text-success me-1"></i>
                  <strong>Data Integrity Rule:</strong> If you disable or remove a monitor, all attendance logs previously submitted by that student are <strong>100% permanently preserved</strong> in the database.
                </small>
              </div>
            </div>
          </div>
        </div>

      <!-- ================= TAB 3: MONTHLY MATRIX & EXPORT ================= -->
      <?php elseif ($activeTab === 'monthly'): ?>
        <?php
          $mStudents = $monthlyData['students'] ?? [];
          $mDates = $monthlyData['dates'] ?? [];
          $mMatrix = $monthlyData['matrix'] ?? [];
        ?>
        <div class="card border rounded-4 shadow-sm bg-white overflow-hidden mb-4">
          <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
              <h5 class="fw-bold text-dark mb-0"><i class="bi bi-file-earmark-spreadsheet-fill text-success me-2"></i>Monthly Attendance Sheet (Excel Matrix)</h5>
              <small class="text-muted">Detailed daily roll call matrix for <?= date('F Y', strtotime($selectedMonth . '-01')) ?></small>
            </div>

            <div class="d-flex align-items-center gap-2">
              <form action="index.php" method="GET" class="d-flex align-items-center gap-2">
                <input type="hidden" name="page" value="teacher_attendance">
                <input type="hidden" name="tab" value="monthly">
                <input type="month" class="form-control form-control-sm border-secondary-subtle" name="month" value="<?= e($selectedMonth) ?>" max="<?= date('Y-m') ?>" required>
                <button type="submit" class="btn btn-outline-primary btn-sm fw-semibold">View</button>
              </form>

              <a href="index.php?page=teacher_attendance_export&month=<?= urlencode($selectedMonth) ?>" class="btn btn-success btn-sm fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm">
                <i class="bi bi-download"></i> Export Excel (.CSV)
              </a>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.86rem;">
              <thead class="table-light text-secondary text-center text-uppercase" style="font-size: 0.74rem;">
                <tr>
                  <th style="width: 40px;">#</th>
                  <th style="min-width: 130px;" class="text-start">Code / MIS</th>
                  <th style="min-width: 180px;" class="text-start">Student Name</th>
                  <?php if (empty($mDates)): ?>
                    <th>Recorded Dates</th>
                  <?php else: ?>
                    <?php foreach ($mDates as $d): 
                      $dStr = $d['date'];
                      $isHol = isset($monthlyHolidays[$dStr]);
                    ?>
                      <th style="width: 44px; <?= $isHol ? 'background: #fffbeb !important; border-bottom: 2px solid #f59e0b !important;' : '' ?>" title="<?= $isHol ? ('HOLIDAY: ' . e($monthlyHolidays[$dStr]['holiday_name'])) : (e($dStr) . ' (' . e($d['marked_by_name'] ?? 'Recorded') . ')') ?>">
                        <?= date('d', strtotime($dStr)) ?><br>
                        <span style="font-size: 0.65rem;" class="<?= $isHol ? 'text-warning fw-bold' : 'text-muted' ?>"><?= date('D', strtotime($dStr)) ?></span>
                      </th>
                    <?php endforeach; ?>
                  <?php endif; ?>
                  <th style="min-width: 80px;">Total</th>
                  <th style="min-width: 80px;">Present</th>
                  <th style="min-width: 80px;">%</th>
                  <th style="min-width: 140px;">NVQ 5 Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($mStudents)): ?>
                  <tr>
                    <td colspan="10" class="text-center py-4 text-muted">No students found.</td>
                  </tr>
                <?php else: ?>
                  <?php $idx = 1; foreach ($mStudents as $st): 
                    $stId = (int)$st['user_id'];
                    $pCount = 0;
                    $totalWorkingSessions = 0;
                  ?>
                    <tr>
                      <td class="text-center text-muted fw-bold"><?= $idx++ ?></td>
                      <td>
                        <div class="fw-bold text-dark font-monospace" style="font-size: 0.8rem;"><?= e($st['student_code']) ?></div>
                        <small class="text-muted font-monospace" style="font-size: 0.72rem;"><?= e($st['mis_number'] ?? 'N/A') ?></small>
                      </td>
                      <td class="fw-semibold text-dark"><?= e($st['name']) ?></td>

                      <?php if (empty($mDates)): ?>
                        <td class="text-center text-muted">No lectures logged this month</td>
                      <?php else: ?>
                        <?php foreach ($mDates as $d): 
                          $dStr = $d['date'];
                          $isHol = isset($monthlyHolidays[$dStr]);
                          $curSt = $mMatrix[$stId][$dStr] ?? null;

                          if ($isHol) {
                            $badge = '<span class="badge bg-warning-subtle text-dark border border-warning p-1" title="Holiday: ' . e($monthlyHolidays[$dStr]['holiday_name']) . '">H</span>';
                          } elseif ($curSt === 'present') {
                            $badge = '<span class="badge bg-success-subtle text-success p-1" title="Present">P</span>';
                            $pCount++;
                            $totalWorkingSessions++;
                          } elseif ($curSt === 'late') {
                            $badge = '<span class="badge bg-warning-subtle text-warning p-1" title="Late">L</span>';
                            $pCount += 0.8;
                            $totalWorkingSessions++;
                          } elseif ($curSt === 'absent') {
                            $badge = '<span class="badge bg-danger-subtle text-danger p-1" title="Absent">A</span>';
                            $totalWorkingSessions++;
                          } elseif ($curSt === 'excused') {
                            $badge = '<span class="badge bg-info-subtle text-info p-1" title="Excused">E</span>';
                            $pCount++;
                            $totalWorkingSessions++;
                          } else {
                            $badge = '<span class="text-muted opacity-25">-</span>';
                            $totalWorkingSessions++;
                          }
                        ?>
                          <td class="text-center p-1"><?= $badge ?></td>
                        <?php endforeach; ?>
                      <?php endif; ?>

                      <td class="text-center fw-bold"><?= $totalWorkingSessions ?></td>
                      <td class="text-center fw-bold text-primary"><?= round($pCount, 1) ?></td>
                      <?php
                        $rate = $totalWorkingSessions > 0 ? round(($pCount / $totalWorkingSessions) * 100, 1) : 100.0;
                      ?>
                      <td class="text-center fw-bold <?= ($rate >= 80) ? 'text-success' : 'text-danger' ?>">
                        <?= $rate ?>%
                      </td>
                      <td class="text-center">
                        <?php if ($rate >= 80): ?>
                          <span class="badge bg-success text-white fw-semibold" style="font-size: 0.74rem;">
                            <i class="bi bi-check-circle me-1"></i> Eligible (&ge; 80%)
                          </span>
                        <?php else: ?>
                          <span class="badge bg-danger text-white fw-semibold" style="font-size: 0.74rem;">
                            <i class="bi bi-exclamation-triangle me-1"></i> Ineligible (&lt; 80%)
                          </span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <div class="card-footer bg-light p-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-top">
            <div class="d-flex align-items-center gap-3 small text-muted flex-wrap">
              <span><strong>Legend:</strong></span>
              <span><span class="badge bg-success-subtle text-success">P</span> Present</span>
              <span><span class="badge bg-warning-subtle text-warning">L</span> Late (0.8)</span>
              <span><span class="badge bg-danger-subtle text-danger">A</span> Absent</span>
              <span><span class="badge bg-info-subtle text-info">E</span> Excused</span>
              <span><span class="badge bg-warning-subtle text-dark border border-warning">H</span> Holiday (Reason Logged)</span>
            </div>
            <div class="small text-muted">
              NVQ Level 5 Institutional Benchmark: <strong>&ge; 80%</strong> attendance required for final CBT exam admission.
            </div>
          </div>
        </div>

        <!-- Monthly Holiday Reason Summary List -->
        <?php if (!empty($monthlyHolidays)): ?>
          <div class="card border rounded-4 shadow-sm bg-white p-3 mb-4">
            <h6 class="fw-bold text-dark mb-2.5 d-flex align-items-center gap-2">
              <i class="bi bi-calendar2-heart-fill text-warning"></i>
              <span>Official Holidays &amp; Closures Logged in <?= date('F Y', strtotime($selectedMonth . '-01')) ?></span>
            </h6>
            <div class="table-responsive">
              <table class="table table-sm table-hover mb-0" style="font-size: 0.84rem;">
                <thead class="table-light">
                  <tr>
                    <th style="width: 140px;">Date</th>
                    <th>Holiday / Reason Name</th>
                    <th style="width: 180px;">Category</th>
                    <th>Description / Reason</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($monthlyHolidays as $h): ?>
                    <tr>
                      <td class="font-monospace fw-bold text-dark"><?= date('d M Y (D)', strtotime($h['date'])) ?></td>
                      <td class="fw-bold text-dark"><?= e($h['holiday_name']) ?></td>
                      <td><span class="badge bg-warning-subtle text-dark border border-warning-subtle"><?= e(ucwords(str_replace('_', ' ', $h['holiday_type']))) ?></span></td>
                      <td class="text-secondary"><?= e($h['description'] ?: 'Official NVTI Baddegama holiday') ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>
      <?php endif; ?>

    </div>
  </main>
</div>

<!-- ================= MODAL: DECLARE HOLIDAY / CLOSURE ================= -->
<div class="modal fade" id="declareHolidayModal" tabindex="-1" aria-labelledby="declareHolidayModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <div class="d-flex align-items-center gap-2">
          <div class="rounded-circle p-2 bg-warning bg-opacity-25 text-warning d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
            <i class="bi bi-calendar2-x-fill text-dark fs-6"></i>
          </div>
          <div>
            <h5 class="modal-title fw-bold text-dark" id="declareHolidayModalLabel">Mark Date as Holiday / Closed Day</h5>
            <small class="text-muted">Record official institute holiday or special closure</small>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="index.php?page=teacher_declare_holiday" method="POST">
        <?= Csrf::field() ?>
        <input type="hidden" name="date" value="<?= e($selectedDate) ?>">
        <div class="modal-body py-3">
          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Selected Date</label>
            <input type="text" class="form-control form-control-sm bg-light fw-bold" value="<?= date('l, d F Y', strtotime($selectedDate)) ?>" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Holiday / Closure Reason Name <span class="text-danger">*</span></label>
            <input type="text" name="holiday_name" class="form-control form-control-sm" placeholder="e.g. Vap Full Moon Poya Day / Adverse Weather Closure" value="<?= e($holiday['holiday_name'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Category</label>
            <select name="holiday_type" class="form-select form-select-sm">
              <option value="public_holiday" <?= (($holiday['holiday_type'] ?? '') === 'public_holiday') ? 'selected' : '' ?>>Public Holiday / Poya Day</option>
              <option value="mercantile_holiday" <?= (($holiday['holiday_type'] ?? '') === 'mercantile_holiday') ? 'selected' : '' ?>>Mercantile Holiday</option>
              <option value="special_closure" <?= (($holiday['holiday_type'] ?? '') === 'special_closure') ? 'selected' : '' ?>>Special Closure / Emergency</option>
              <option value="curriculum_recess" <?= (($holiday['holiday_type'] ?? '') === 'curriculum_recess') ? 'selected' : '' ?>>Curriculum Recess / Vacation</option>
              <option value="other" <?= (($holiday['holiday_type'] ?? '') === 'other') ? 'selected' : '' ?>>Other Institutional Reason</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label small fw-bold text-dark">Description / Additional Notes</label>
            <textarea name="description" rows="2" class="form-control form-control-sm" placeholder="Special notes regarding this holiday..."><?= e($holiday['description'] ?? '') ?></textarea>
            <small class="text-muted" style="font-size: 0.72rem;">This reason will be officially included in the monthly attendance reports and CSV export.</small>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning btn-sm rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-check-lg me-1"></i> Save Holiday Declaration
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function markAllStatus(targetStatus) {
  const radios = document.querySelectorAll('input[type="radio"][value="' + targetStatus + '"]');
  radios.forEach(r => {
    if (!r.disabled) {
      r.checked = true;
      r.dispatchEvent(new Event('change'));
    }
  });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
