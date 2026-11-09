<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="app-wrapper">
  <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <div class="container-fluid">
      <?= Flash::render() ?>

      <!-- Welcome Banner -->
      <div class="card border rounded-4 mb-4 p-4 bg-white shadow-sm">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
          <div>
            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-success-subtle text-success fw-bold px-3 py-1.5 rounded-pill"><i class="bi bi-mortarboard-fill me-1"></i> Student Portal</span>
              <?php if (!empty($profile['mis_number'])): ?>
                <span class="badge bg-primary text-white font-monospace px-2.5 py-1 rounded-pill fw-bold">
                  <i class="bi bi-person-badge-fill me-1"></i><?= e($profile['mis_number']) ?>
                </span>
              <?php endif; ?>
              <span class="badge bg-light text-dark border fw-bold rounded-pill"><?= e($profile['student_code'] ?? 'NVTI-ST') ?></span>
            </div>
            <h3 class="fw-bold mb-1 text-dark">Hello, <?= e(Auth::name()) ?>!</h3>
            <p class="mb-0 text-muted"><?= e($profile['course_name'] ?? 'National Diploma in ICT') ?> &bull; <?= e($profile['class_name'] ?? 'NVTI Baddegama') ?></p>
          </div>
          <div class="d-flex gap-2">
            <a href="index.php?page=student_materials" class="btn btn-primary fw-bold btn-sm px-3 shadow-sm rounded-pill">
              <i class="bi bi-book-half me-1"></i> Study Notes
            </a>
            <a href="index.php?page=student_attendance" class="btn btn-outline-success btn-sm px-3 rounded-pill fw-semibold">
              <i class="bi bi-graph-up-arrow me-1"></i> My Attendance
            </a>
            <a href="index.php?page=student_assignments" class="btn btn-outline-secondary btn-sm px-3 rounded-pill">
              <i class="bi bi-pencil-square me-1"></i> Assignments
            </a>
          </div>
        </div>
      </div>

      <!-- Verification Documents Status Callout -->
      <?php if (isset($docSummary) && !$docSummary['is_all_verified']): ?>
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: linear-gradient(90deg, #fff7ed 0%, #ffedd5 100%); border-left: 5px solid #ea580c !important;">
          <div class="card-body p-3.5 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle bg-white p-2.5 d-flex align-items-center justify-content-center shadow-sm text-warning flex-shrink-0" style="width: 46px; height: 46px;">
                <i class="bi bi-file-earmark-check-fill fs-4 text-warning"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 mb-0.5">
                  <span class="badge bg-warning text-dark fw-bold rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">ENROLLMENT VERIFICATION</span>
                  <span class="text-muted small">&bull; <?= $docSummary['uploaded'] ?> of <?= $docSummary['total'] ?> Certificates Uploaded</span>
                </div>
                <h6 class="fw-bold mb-0 text-dark">
                  <?php if (!$docSummary['is_complete']): ?>
                    <?= $docSummary['missing'] ?> Mandatory Verification Document<?= $docSummary['missing'] > 1 ? 's' : '' ?> Pending Upload
                  <?php else: ?>
                    All 7 Certificates Uploaded &bull; Pending Instructor's Final Verification
                  <?php endif; ?>
                </h6>
                <small class="text-secondary">Please ensure all 7 official certificates (O/L, A/L, GN, Character, Leaving, Birth, NIC) are uploaded for diploma verification.</small>
              </div>
            </div>
            <a href="index.php?page=student_documents" class="btn btn-warning btn-sm fw-bold px-3 py-2 rounded-pill shadow-sm text-nowrap d-inline-flex align-items-center gap-1.5" style="background: #ea580c; border-color: #ea580c; color: #fff;">
              <i class="bi bi-cloud-arrow-up-fill"></i>
              <span><?= !$docSummary['is_complete'] ? 'Upload Documents' : 'View Document Status' ?> &rarr;</span>
            </a>
          </div>
        </div>
      <?php endif; ?>

      <?php
        require_once __DIR__ . '/../../Helpers/AttendanceManager.php';
        $activeMonitor = AttendanceManager::getActiveMonitorStatus((int)Auth::id());
        if ($activeMonitor):
      ?>
        <!-- Class Monitor Special Duty Card (NVTI Maroon Brand Theme) -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #7A1418 0%, #90161b 100%);">
          <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle bg-white p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 52px; height: 52px; color: #7A1418;">
                <i class="bi bi-calendar2-check-fill fs-4"></i>
              </div>
              <div>
                <span class="badge bg-white bg-opacity-25 text-white mb-1 px-2.5 py-1">
                  <i class="bi bi-shield-check me-1"></i> Appointed <?= htmlspecialchars(ucfirst($activeMonitor['monitor_role'])) ?> Monitor
                </span>
                <h5 class="fw-bold mb-1">Class Roll Call Duty</h5>
                <p class="small mb-0 opacity-75">You are authorized by instructor to mark daily batch attendance on the Excel Sheet.</p>
              </div>
            </div>
            <a href="index.php?page=student_batch_attendance" class="btn btn-light fw-bold px-4 py-2 shadow-sm text-nowrap" style="color: #7A1418 !important;">
              <i class="bi bi-table me-1"></i> Open Batch Attendance Sheet &rarr;
            </a>
          </div>
        </div>
      <?php endif; ?>

      <!-- Quick Action Cards (4 Responsive Metrics) -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
          <div class="card stat-card p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-semibold">Subjects</span>
                <h4 class="fw-bold mb-0 text-dark"><?= count($subjects) ?></h4>
              </div>
              <div class="stat-icon bg-primary-subtle text-primary">
                <i class="bi bi-journals"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="card stat-card p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-semibold">Study Notes</span>
                <h4 class="fw-bold mb-0 text-dark"><?= count($recentMaterials) ?></h4>
              </div>
              <div class="stat-icon bg-info-subtle text-info">
                <i class="bi bi-file-earmark-pdf-fill"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="card stat-card p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-semibold">Coursework</span>
                <h4 class="fw-bold mb-0 text-dark"><?= count($assignments) ?></h4>
              </div>
              <div class="stat-icon bg-warning-subtle text-warning">
                <i class="bi bi-clipboard2-check-fill"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="card stat-card p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-semibold">Attendance</span>
                <h4 class="fw-bold mb-0 <?= ($attendanceRate >= 80) ? 'text-success' : 'text-danger' ?>">
                  <?= $attendanceRate ?>%
                </h4>
              </div>
              <div class="stat-icon bg-success-subtle text-success">
                <i class="bi bi-graph-up-arrow"></i>
              </div>
            </div>
            <div class="mt-2 text-end">
              <a href="index.php?page=student_attendance" class="small text-decoration-none fw-semibold">View Eligibility &rarr;</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Upcoming Sessions & Learning Materials -->
      <div class="row g-4 mb-4">
        <!-- Daily Class Schedule -->
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
              <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history text-primary me-2"></i>Class Timetable</h5>
              <a href="index.php?page=student_timetable" class="btn btn-sm btn-outline-primary">Full Schedule</a>
            </div>
            <div class="card-body p-3">
              <?php if (empty($sessions)): ?>
                <p class="text-muted text-center py-4">No scheduled sessions for your class.</p>
              <?php else: ?>
                <div class="list-group list-group-flush">
                  <?php foreach ($sessions as $s): ?>
                    <?php $isMorn = (strtotime($s['start_time']) < strtotime('12:30:00')); ?>
                    <div class="list-group-item px-0 py-2.5 border-bottom">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <div>
                          <strong><?= e($s['day_of_week']) ?></strong>: 
                          <span class="text-dark fw-bold"><?= date('h:i A', strtotime($s['start_time'])) ?> - <?= date('h:i A', strtotime($s['end_time'])) ?></span>
                        </div>
                        <?php if ($isMorn): ?>
                          <span class="badge slot-badge-morning"><i class="bi bi-sun-fill me-1"></i> Morning (09-12)</span>
                        <?php else: ?>
                          <span class="badge slot-badge-afternoon"><i class="bi bi-sunset-fill me-1"></i> Afternoon (13-16)</span>
                        <?php endif; ?>
                      </div>
                      <div class="text-muted small">
                        <strong><?= e($s['subject_name']) ?></strong> (<?= e($s['subject_code']) ?>) &bull; Lecturer: <?= e($s['teacher_name']) ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Recent Uploaded Materials -->
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
              <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-arrow-down text-primary me-2"></i>Recent Notes &amp; Materials</h5>
              <a href="index.php?page=student_materials" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-3">
              <?php if (empty($recentMaterials)): ?>
                <p class="text-muted text-center py-4">No study materials uploaded yet.</p>
              <?php else: ?>
                <div class="list-group list-group-flush">
                  <?php foreach ($recentMaterials as $m): ?>
                    <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center border-bottom">
                      <div>
                        <div class="fw-bold text-dark text-truncate" style="max-width: 320px;"><?= e($m['title']) ?></div>
                        <small class="text-muted"><?= e($m['subject_name']) ?> &bull; <?= e($m['teacher_name']) ?></small>
                      </div>
                      <div class="d-flex align-items-center gap-1.5">
                        <a href="index.php?page=view_material&id=<?= $m['id'] ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5" title="View / Read Material">
                          <i class="bi bi-eye"></i> View
                        </a>
                        <a href="index.php?page=download_material&id=<?= $m['id'] ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                          <i class="bi bi-download"></i>
                        </a>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Active Coursework Submissions -->
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
          <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clipboard2-check text-primary me-2"></i>Active Coursework &amp; Submissions</h5>
          <a href="index.php?page=student_assignments" class="btn btn-sm btn-outline-primary">All Assignments</a>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Title</th>
                <th>Subject</th>
                <th>Deadline</th>
                <th>Status</th>
                <th>Marks</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($assignments)): ?>
                <tr>
                  <td colspan="6" class="text-center py-4 text-muted">No active coursework found.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($assignments as $a): ?>
                  <tr>
                    <td class="fw-bold text-dark"><?= e($a['title']) ?></td>
                    <td><?= e($a['subject_name']) ?></td>
                    <td>
                      <span class="badge bg-light text-dark border">
                        <i class="bi bi-clock me-1"></i><?= date('M d, Y', strtotime($a['deadline'])) ?>
                      </span>
                    </td>
                    <td>
                      <?php if ($a['submission_status'] === 'graded'): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Graded</span>
                      <?php elseif ($a['submission_status'] === 'submitted'): ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Submitted</span>
                      <?php elseif ($a['submission_status'] === 'late'): ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Late</span>
                      <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border">Not Submitted</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?= ($a['marks'] !== null) ? '<strong>' . e($a['marks']) . '</strong> / ' . e($a['max_marks']) : '<span class="text-muted">-</span>' ?>
                    </td>
                    <td class="text-end">
                      <a href="index.php?page=student_assignments" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <?= ($a['submission_id']) ? 'View Status' : 'Submit Now' ?>
                      </a>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
