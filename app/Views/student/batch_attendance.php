<?php
/**
 * Student Class Monitor - Batch Attendance Excel Sheet View
 * NVTI Baddegama National Diploma Portal
 */

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
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
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-semibold">
                  <i class="bi bi-shield-fill-check me-1"></i> Class Monitor Portal
                </span>
                <span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1">
                  <?= htmlspecialchars(ucfirst($monitorInfo['monitor_role'] ?? 'Monitor')) ?>
                </span>
              </div>
              <h3 class="fw-bold text-dark mb-1">Batch Attendance Sheet (Excel Grid)</h3>
              <p class="text-muted small mb-0">
                <?= htmlspecialchars($className ?? 'NVTI Baddegama ICT L5 - Batch 2026') ?> &bull; Logged in as: <strong><?= e(Auth::user()['name']) ?></strong>
              </p>
            </div>

            <!-- Date Selector Form -->
            <form action="index.php" method="GET" class="d-flex align-items-center gap-2 bg-light p-2 rounded-3 border">
              <input type="hidden" name="page" value="student_batch_attendance">
              <label for="attDate" class="small fw-semibold text-muted text-nowrap mb-0">Select Date:</label>
              <input type="date" class="form-control form-control-sm border-secondary-subtle" id="attDate" name="date" value="<?= e($selectedDate) ?>" max="<?= date('Y-m-d') ?>" required>
              <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">Load</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Lock Status Alert / Info Banner -->
      <?php 
        $verifStatus = $dayLock['verification_status'] ?? 'pending_verification';
        $isVerified = $isLocked && $verifStatus === 'verified';
        $markedByAdmin = ($dayLock && (($dayLock['marked_by_role'] ?? '') === 'admin' || ($dayLock['marked_by_user_role'] ?? '') === 'admin'));
      ?>

      <!-- 1. Special Holiday / Closure Alert -->
      <?php if (!empty($holiday)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white mb-4" style="border-top: 5px solid #f59e0b !important; background: linear-gradient(180deg, #fffbeb 0%, #ffffff 100%) !important;">
          <div class="rounded-circle p-3 mx-auto mb-2 d-flex align-items-center justify-content-center text-warning bg-white shadow-sm" style="width: 64px; height: 64px;">
            <i class="bi bi-calendar2-heart-fill fs-2 text-warning"></i>
          </div>
          <span class="badge bg-warning text-dark fw-bold rounded-pill px-3 py-1 mb-2">OFFICIAL HOLIDAY / INSTITUTE CLOSURE</span>
          <h4 class="fw-bold text-dark mb-1"><?= e($holiday['holiday_name']) ?></h4>
          <p class="text-secondary small mb-0" style="max-width: 620px; margin: 0 auto;">
            <strong>Category:</strong> <?= e(ucwords(str_replace('_', ' ', $holiday['holiday_type']))) ?> &bull; 
            <?= e($holiday['description'] ?: 'NVTI Baddegama is officially closed today. No student roll call is required.') ?>
          </p>
        </div>

      <!-- 2. Weekend Notice (Monday to Friday Regular Academic) -->
      <?php elseif (!empty($isWeekend) && !$isLocked): ?>
        <div class="alert border-0 shadow-sm rounded-4 p-3 mb-4 text-dark" style="background: #f8fafc; border-left: 5px solid #64748b !important;">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center text-secondary bg-white shadow-sm flex-shrink-0" style="width: 44px; height: 44px;">
              <i class="bi bi-calendar-week fs-4"></i>
            </div>
            <div>
              <span class="badge bg-secondary text-white fw-bold rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">WEEKEND (SATURDAY / SUNDAY)</span>
              <h6 class="fw-bold mb-0 text-dark">Weekend Session &bull; Regular NVTI Classes are Monday to Friday (5 Days)</h6>
              <small class="text-secondary">Official classes run Monday through Friday. If Sir held an extra weekend class or revision session, you may submit roll call below.</small>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- 3. Marked Directly by Admin Banner -->
      <?php if (!empty($markedByAdmin) && empty($holiday)): ?>
        <div class="alert border-0 shadow-sm rounded-4 p-4 mb-4 text-dark" style="background: linear-gradient(90deg, #fef2f2 0%, #fee2e2 100%); border-left: 5px solid #7A1418 !important;">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle p-2 d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0" style="background: #7A1418; width: 50px; height: 50px;">
                <i class="bi bi-shield-check fs-3"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                  <span class="badge text-white fw-bold rounded-pill px-3 py-1" style="background: #7A1418; font-size: 0.74rem;">
                    <i class="bi bi-check-all me-1"></i> MARKED &amp; FINALIZED BY ADMINISTRATOR
                  </span>
                  <span class="text-danger fw-semibold small">&bull; Official Record Finalized</span>
                </div>
                <h5 class="fw-bold mb-1 text-dark">Today's Attendance marked &amp; finalized by NVTI Admin (<?= e($dayLock['marked_by_name'] ?? 'System Administrator') ?>)</h5>
                <p class="text-secondary small mb-0">The Administrator has directly marked and locked the official attendance sheet for <strong><?= date('l, d F Y', strtotime($selectedDate)) ?></strong>. Class monitors do not need to submit attendance today. The sheet is officially closed.</p>
              </div>
            </div>
            <div>
              <button class="btn btn-outline-danger rounded-pill px-3.5 py-2 fw-semibold shadow-xs" type="button" data-bs-toggle="collapse" data-bs-target="#monitorRollCallCollapse">
                <i class="bi bi-person-lines-fill me-1"></i> View Submitted List
              </button>
            </div>
          </div>
        </div>

      <!-- 4. Verified & Approved by Instructor (Teacher) -->
      <?php elseif ($isVerified && empty($holiday)): ?>
        <div class="alert border-0 shadow-sm rounded-4 p-4 mb-4 text-dark" style="background: linear-gradient(90deg, #f0fdf4 0%, #dcfce7 100%); border-left: 5px solid #10b981 !important;">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle p-2 d-flex align-items-center justify-content-center text-success bg-white shadow-sm flex-shrink-0" style="width: 50px; height: 50px;">
                <i class="bi bi-patch-check-fill fs-3"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                  <span class="badge bg-success text-white fw-bold rounded-pill px-3 py-1" style="font-size: 0.74rem;">
                    <i class="bi bi-lock-fill me-1"></i> OFFICIAL &amp; VERIFIED
                  </span>
                  <span class="text-success fw-semibold small">&bull; Record Closed for Today</span>
                </div>
                <h5 class="fw-bold mb-1 text-dark">Verified &amp; Approved by Instructor (<?= e($dayLock['verified_by_name'] ?? 'Sir') ?>)</h5>
                <p class="text-secondary small mb-0">අද දින පැමිණීම Sir විසින් සහතික කර Lock කර ඇත. Name list එක close කර ඇත. මීළඟ දින පැමිණීම සඳහා Next Date එක තෝරන්න.</p>
              </div>
            </div>
            <div>
              <button class="btn btn-outline-success rounded-pill px-3.5 py-2 fw-semibold shadow-xs" type="button" data-bs-toggle="collapse" data-bs-target="#monitorRollCallCollapse">
                <i class="bi bi-person-lines-fill me-1"></i> View Submitted List
              </button>
            </div>
          </div>
        </div>

      <!-- 5. Submitted by Monitor & Pending Verification -->
      <?php elseif ($isLocked && empty($holiday)): ?>
        <?php if ($lockedBySelf): ?>
          <div class="alert border-0 shadow-sm rounded-4 p-3 mb-4 text-dark" style="background: linear-gradient(90deg, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #f59e0b !important;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center text-warning bg-white shadow-sm flex-shrink-0" style="width: 44px; height: 44px;">
                  <i class="bi bi-hourglass-split fs-4"></i>
                </div>
                <div>
                  <div class="d-flex align-items-center gap-2 mb-0.5">
                    <span class="badge bg-warning text-dark fw-bold rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">SUBMITTED TO SIR</span>
                    <span class="text-muted small">&bull; Pending Instructor Approval</span>
                  </div>
                  <h6 class="fw-bold mb-0 text-dark">You submitted roll call at <?= e(date('h:i A', strtotime($dayLock['submitted_at'] ?? 'now'))) ?> for <?= date('l, d F Y', strtotime($selectedDate)) ?></h6>
                  <small class="text-secondary">අද දවසේ roll call එක සාර්ථකව Submit කර ඇත. Instructor (Sir) විසින් approve කරන තුරු Sheet එක Locked වේ.</small>
                </div>
              </div>
              <div>
                <button class="btn btn-outline-warning text-dark rounded-pill px-3.5 py-2 fw-semibold shadow-xs" type="button" data-bs-toggle="collapse" data-bs-target="#monitorRollCallCollapse">
                  <i class="bi bi-person-lines-fill me-1"></i> View Submitted List
                </button>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="alert border-0 shadow-sm rounded-4 p-3 mb-4 text-dark" style="background: linear-gradient(90deg, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #f59e0b !important;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center text-warning bg-white shadow-sm flex-shrink-0" style="width: 44px; height: 44px;">
                  <i class="bi bi-lock-fill fs-4"></i>
                </div>
                <div>
                  <div class="d-flex align-items-center gap-2 mb-0.5">
                    <span class="badge bg-warning text-dark fw-bold rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">SUBMITTED BY <?= strtoupper(e($dayLock['marked_by_name'] ?? 'MONITOR')) ?></span>
                    <span class="text-muted small">&bull; Pending Sir's Verification</span>
                  </div>
                  <h6 class="fw-bold mb-0 text-dark">Attendance already submitted for <?= date('l, d F Y', strtotime($selectedDate)) ?></h6>
                  <small class="text-secondary">This sheet was submitted by <strong><?= e($dayLock['marked_by_name'] ?? 'Monitor') ?></strong> at <?= e(date('h:i A', strtotime($dayLock['submitted_at'] ?? 'now'))) ?>.</small>
                </div>
              </div>
              <div>
                <button class="btn btn-outline-secondary rounded-pill px-3.5 py-2 fw-semibold shadow-xs" type="button" data-bs-toggle="collapse" data-bs-target="#monitorRollCallCollapse">
                  <i class="bi bi-person-lines-fill me-1"></i> View Submitted List
                </button>
              </div>
            </div>
          </div>
        <?php endif; ?>

      <!-- 6. Sheet Open for Student Monitor Marking -->
      <?php elseif (empty($holiday)): ?>
        <div class="alert alert-info border-0 shadow-sm rounded-4 p-3 mb-4 text-dark d-flex align-items-center justify-content-between gap-3 flex-wrap" style="background: #e0f2fe; border-left: 5px solid #0284c7 !important;">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center text-info bg-white shadow-sm flex-shrink-0" style="width: 44px; height: 44px;">
              <i class="bi bi-pencil-square fs-4 text-primary"></i>
            </div>
            <div>
              <div class="d-flex align-items-center gap-2 mb-0.5">
                <span class="badge bg-primary text-white fw-bold rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">SHEET OPEN FOR MARKING</span>
              </div>
              <h6 class="fw-bold mb-0 text-dark">Roll Call for <?= date('l, d F Y', strtotime($selectedDate)) ?></h6>
              <small class="text-secondary">Mark each student as Present, Late, Absent, or Excused. Click "Submit Attendance" to send to Instructor for approval.</small>
            </div>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-att-present rounded-pill px-3 shadow-xs" onclick="markAllStatus('present')">
              <i class="bi bi-check-all me-1"></i> All Present
            </button>
            <button type="button" class="btn btn-sm btn-att-absent rounded-pill px-3 shadow-xs" onclick="markAllStatus('absent')">
              <i class="bi bi-x-circle me-1"></i> All Absent
            </button>
          </div>
        </div>
      <?php endif; ?>

      <?php if (empty($holiday)): ?>
      <div class="<?= ($isLocked || $isVerified) ? 'collapse' : '' ?>" id="monitorRollCallCollapse">
      <!-- Excel-Style Grid Form -->
      <form action="index.php?page=student_batch_attendance_save" method="POST" id="attendanceGridForm">
        <?= Csrf::field() ?>
        <input type="hidden" name="date" value="<?= e($selectedDate) ?>">

        <div class="card border rounded-4 shadow-sm bg-white overflow-hidden mb-4">
          <div class="card-header bg-light py-3 px-4 d-flex justify-content-between align-items-center border-bottom flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-table text-primary"></i>
              <strong class="text-dark">Class Roll Call Grid (<?= count($students) ?> Students)</strong>
            </div>
            <div class="d-flex align-items-center gap-2">
              <?php if (!$isLocked && !$isVerified): ?>
                <button type="button" class="btn btn-sm btn-att-present rounded-pill px-2.5 shadow-xs" onclick="markAllStatus('present')">
                  <i class="bi bi-check-all me-1"></i> All Present
                </button>
                <button type="button" class="btn btn-sm btn-att-absent rounded-pill px-2.5 shadow-xs" onclick="markAllStatus('absent')">
                  <i class="bi bi-x-circle me-1"></i> All Absent
                </button>
              <?php endif; ?>
              <span class="badge bg-white text-secondary border px-2.5 py-1">
                Date: <?= e($selectedDate) ?>
              </span>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 mobile-rollcall-table" style="font-size: 0.88rem;">
              <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                <tr>
                  <th style="width: 45px;" class="text-center py-2.5">#</th>
                  <th style="width: 290px;" class="py-2.5">Student Information</th>
                  <th style="width: 170px;" class="py-2.5">MIS &amp; NIC Number</th>
                  <th style="width: 360px;" class="text-center py-2.5">Attendance Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($students)): ?>
                  <tr>
                    <td colspan="4" class="text-center py-4 text-muted">No students enrolled in this batch.</td>
                  </tr>
                <?php else: ?>
                  <?php $idx = 1; foreach ($students as $st): 
                    $stId = (int)$st['user_id'];
                    $curAtt = $existingAttendance[$stId]['status'] ?? 'present';
                    $isDisabled = ($isLocked && !$lockedBySelf) || $isVerified;
                  ?>
                    <tr class="student-row" id="row_<?= $stId ?>">
                      <td class="text-center text-muted fw-bold"><?= $idx++ ?></td>
                      <td>
                        <div class="d-flex align-items-center gap-2.5">
                          <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-primary fw-bold flex-shrink-0" style="width: 36px; height: 36px; font-size: 0.82rem;">
                            <?= strtoupper(substr($st['name'], 0, 1)) ?>
                          </div>
                          <div>
                            <div class="fw-bold text-dark mb-0 text-truncate" style="max-width: 240px;"><?= e($st['name']) ?></div>
                            <small class="text-muted font-monospace"><i class="bi bi-person-vcard text-secondary me-1"></i>NIC: <?= e($st['nic'] ?? 'N/A') ?></small>
                          </div>
                        </div>
                      </td>
                      <td>
                        <span class="badge bg-light text-dark border font-monospace fw-bold px-2 py-1"><i class="bi bi-person-badge text-primary me-1"></i><?= e($st['mis_number'] ?? 'N/A') ?></span>
                      </td>
                      <td class="text-center">
                        <div class="attendance-pill-group" role="group">
                          <!-- Present -->
                          <input type="radio" 
                                 class="btn-check status-radio status-present" 
                                 name="attendance[<?= $stId ?>][status]" 
                                 id="status_<?= $stId ?>_present" 
                                 value="present" 
                                 <?= ($curAtt === 'present') ? 'checked' : '' ?>
                                 <?= $isDisabled ? 'disabled' : '' ?>>
                          <label class="att-pill-label att-pill-present" for="status_<?= $stId ?>_present">
                            <i class="bi bi-check-circle-fill me-1"></i> Present
                          </label>

                          <!-- Late -->
                          <input type="radio" 
                                 class="btn-check status-radio status-late" 
                                 name="attendance[<?= $stId ?>][status]" 
                                 id="status_<?= $stId ?>_late" 
                                 value="late" 
                                 <?= ($curAtt === 'late') ? 'checked' : '' ?>
                                 <?= $isDisabled ? 'disabled' : '' ?>>
                          <label class="att-pill-label att-pill-late" for="status_<?= $stId ?>_late">
                            <i class="bi bi-clock-fill me-1"></i> Late
                          </label>

                          <!-- Absent -->
                          <input type="radio" 
                                 class="btn-check status-radio status-absent" 
                                 name="attendance[<?= $stId ?>][status]" 
                                 id="status_<?= $stId ?>_absent" 
                                 value="absent" 
                                 <?= ($curAtt === 'absent') ? 'checked' : '' ?>
                                 <?= $isDisabled ? 'disabled' : '' ?>>
                          <label class="att-pill-label att-pill-absent" for="status_<?= $stId ?>_absent">
                            <i class="bi bi-x-circle-fill me-1"></i> Absent
                          </label>

                          <!-- Excused -->
                          <input type="radio" 
                                 class="btn-check status-radio status-excused" 
                                 name="attendance[<?= $stId ?>][status]" 
                                 id="status_<?= $stId ?>_excused" 
                                 value="excused" 
                                 <?= ($curAtt === 'excused') ? 'checked' : '' ?>
                                 <?= $isDisabled ? 'disabled' : '' ?>>
                          <label class="att-pill-label att-pill-excused" for="status_<?= $stId ?>_excused">
                            <i class="bi bi-info-circle-fill me-1"></i> Excused
                          </label>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Bottom Footer Bar -->
          <div class="card-footer bg-white p-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 border-top">
            <div class="small text-muted">
              <i class="bi bi-shield-check me-1 text-primary"></i> Submissions are routed to Instructor (Sir) for verification before updating official attendance.
            </div>
            <div>
              <?php if (!$isLocked || ($lockedBySelf && !$isVerified)): ?>
                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2" style="background: #7A1418; border-color: #7A1418;">
                  <i class="bi bi-cloud-arrow-up-fill"></i>
                  <span>Submit Roll Call to Instructor</span>
                </button>
              <?php else: ?>
                <button type="button" class="btn btn-secondary px-4 py-2 fw-semibold" disabled>
                  <i class="bi bi-lock-fill me-1"></i> Locked for Changes
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </form>
      </div>
      <?php endif; ?>

    </div>
  </main>
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
