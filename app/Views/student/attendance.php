<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

$isEligible = ($overallRate >= 80.0);
$progressColor = ($overallRate >= 80) ? 'bg-success' : (($overallRate >= 75) ? 'bg-warning' : 'bg-danger');
$monthColor = ($monthlyRate >= 80) ? 'bg-success' : (($monthlyRate >= 75) ? 'bg-warning' : 'bg-danger');
$weekColor = ($weeklyRate >= 80) ? 'bg-success' : (($weeklyRate >= 75) ? 'bg-warning' : 'bg-danger');
?>

<div class="app-wrapper">
  <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <div class="container-fluid">
      <?= Flash::render() ?>

      <!-- Header Banner -->
      <div class="card border rounded-4 mb-4 p-4 bg-white shadow-sm">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
          <div>
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 rounded-pill">
                <i class="bi bi-graph-up-arrow me-1"></i> Attendance Monitoring
              </span>
              <?php if (!empty($profile['mis_number'])): ?>
                <span class="badge bg-light text-dark border font-monospace"><?= e($profile['mis_number']) ?></span>
              <?php endif; ?>
            </div>
            <h3 class="fw-bold mb-1 text-dark">Attendance &amp; Exam Eligibility</h3>
            <p class="mb-0 text-muted small">
              Official record for <?= e($profile['course_name'] ?? 'National Diploma in ICT') ?> &bull; <?= e($profile['class_name'] ?? 'Batch 2026') ?>
            </p>
          </div>

          <div class="d-flex align-items-center gap-2">
            <?php if ($isEligible): ?>
              <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-semibold shadow-2xs">
                <i class="bi bi-check-circle-fill me-1"></i> Exam Eligible (&ge; 80%)
              </span>
            <?php else: ?>
              <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-semibold shadow-2xs">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Below 80% Requirement
              </span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Main Progress Bars Card -->
      <div class="row g-4 mb-4">
        <!-- Overall Progress -->
        <div class="col-lg-6">
          <div class="card border rounded-4 p-4 bg-white shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h5 class="fw-bold text-dark mb-0">Overall Semester Attendance</h5>
              <span class="fs-4 fw-bold <?= ($overallRate >= 80) ? 'text-success' : 'text-danger' ?>">
                <?= $overallRate ?>%
              </span>
            </div>
            <p class="text-muted small mb-3">
              NVQ Level 5 Institutional rule: Minimum <strong>80% attendance</strong> is required to sit for CBT Semester Final Assessments.
            </p>

            <!-- Big Visual Progress Bar -->
            <div class="progress rounded-pill mb-3" style="height: 22px; background-color: #f1f5f9;">
              <div class="progress-bar <?= $progressColor ?> progress-bar-striped progress-bar-animated fw-bold" 
                   role="progressbar" 
                   style="width: <?= min(100, $overallRate) ?>%; font-size: 0.85rem;" 
                   aria-valuenow="<?= $overallRate ?>" 
                   aria-valuemin="0" 
                   aria-valuemax="100">
                <?= $overallRate ?>%
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center small text-muted">
              <span>0%</span>
              <span class="badge bg-light text-secondary border"><i class="bi bi-flag-fill text-warning me-1"></i>80% Exam Pass Mark</span>
              <span>100%</span>
            </div>
          </div>
        </div>

        <!-- Monthly & Weekly Progress Bars -->
        <div class="col-lg-6">
          <div class="card border rounded-4 p-4 bg-white shadow-sm h-100">
            <h5 class="fw-bold text-dark mb-3">Periodic Breakdown (මාසික සහ සතිපතා)</h5>

            <!-- Monthly Progress -->
            <div class="mb-3.5">
              <div class="d-flex justify-content-between align-items-center small mb-1">
                <span class="fw-semibold text-secondary">
                  <i class="bi bi-calendar-month text-primary me-1"></i> This Month Attendance (<?= date('F Y') ?>)
                </span>
                <strong class="text-dark"><?= $monthlyRate ?>%</strong>
              </div>
              <div class="progress rounded-pill" style="height: 12px; background-color: #f1f5f9;">
                <div class="progress-bar <?= $monthColor ?>" style="width: <?= min(100, $monthlyRate) ?>%;"></div>
              </div>
            </div>

            <!-- Weekly Progress -->
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center small mb-1">
                <span class="fw-semibold text-secondary">
                  <i class="bi bi-calendar2-week text-info me-1"></i> Last 7 Days (Past Week)
                </span>
                <strong class="text-dark"><?= $weeklyRate ?>%</strong>
              </div>
              <div class="progress rounded-pill" style="height: 12px; background-color: #f1f5f9;">
                <div class="progress-bar <?= $weekColor ?>" style="width: <?= min(100, $weeklyRate) ?>%;"></div>
              </div>
            </div>

            <div class="p-2.5 bg-light rounded-3 small text-muted border">
              <i class="bi bi-info-circle me-1 text-primary"></i> Attendance is logged daily by ICT Lecturers at NVTI Baddegama Computer Labs 01 &amp; 02.
            </div>
          </div>
        </div>
      </div>

      <!-- Quick KPI Counters -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="card border rounded-4 p-3 bg-white shadow-2xs text-center">
            <span class="text-muted small fw-semibold d-block mb-1">Total Sessions</span>
            <h3 class="fw-bold text-dark mb-0"><?= $totalSessions ?></h3>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border rounded-4 p-3 bg-white shadow-2xs text-center">
            <span class="text-muted small fw-semibold d-block mb-1">Days Present</span>
            <h3 class="fw-bold text-success mb-0"><?= $presentCount ?></h3>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border rounded-4 p-3 bg-white shadow-2xs text-center">
            <span class="text-muted small fw-semibold d-block mb-1">Late Arrivals</span>
            <h3 class="fw-bold text-warning mb-0"><?= $lateCount ?></h3>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border rounded-4 p-3 bg-white shadow-2xs text-center">
            <span class="text-muted small fw-semibold d-block mb-1">Days Absent</span>
            <h3 class="fw-bold text-danger mb-0"><?= $absentCount ?></h3>
          </div>
        </div>
      </div>

      <!-- Attendance Log Table / Cards -->
      <div class="card border rounded-4 shadow-sm bg-white overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
          <h5 class="fw-bold text-dark mb-0">
            <i class="bi bi-clock-history text-primary me-2"></i>Daily Session Attendance Log
          </h5>
          <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill small">
            <?= count($attendanceLogs) ?> Records Found
          </span>
        </div>

        <?php if (empty($attendanceLogs)): ?>
          <div class="p-5 text-center text-muted">
            <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary"></i>
            No attendance records recorded yet.
          </div>
        <?php else: ?>
          <!-- Desktop Table -->
          <div class="d-none d-md-block table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light text-uppercase small text-muted">
                <tr>
                  <th class="ps-4 py-3">Date</th>
                  <th>Session / Module</th>
                  <th>Time &amp; Shift</th>
                  <th>Lecturer</th>
                  <th>Status</th>
                  <th>Remarks</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($attendanceLogs as $log): ?>
                  <tr>
                    <td class="ps-4 fw-bold text-dark font-monospace">
                      <?= date('Y-m-d', strtotime($log['date'])) ?>
                      <small class="text-muted d-block fw-normal"><?= date('l', strtotime($log['date'])) ?></small>
                    </td>
                    <td>
                      <span class="fw-bold text-dark"><?= e($log['subject_name'] ?? 'ICT Practical / Theory') ?></span>
                      <?php if (!empty($log['session_title'])): ?>
                        <small class="text-muted d-block"><?= e($log['session_title']) ?></small>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="small text-secondary">
                        <?= !empty($log['start_time']) ? date('h:i A', strtotime($log['start_time'])) . ' - ' . date('h:i A', strtotime($log['end_time'])) : 'Regular Shift' ?>
                      </span>
                    </td>
                    <td>
                      <span class="small text-muted"><?= e($log['teacher_name'] ?? 'Senior Instructor') ?></span>
                    </td>
                    <td>
                      <?php if ($log['status'] === 'present'): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                          <i class="bi bi-check-circle-fill me-1"></i>Present
                        </span>
                      <?php elseif ($log['status'] === 'late'): ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 rounded-pill">
                          <i class="bi bi-clock-fill me-1"></i>Late
                        </span>
                      <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">
                          <i class="bi bi-x-circle-fill me-1"></i>Absent
                        </span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="small text-muted"><?= e($log['remarks'] ?? '-') ?></span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Mobile Cards View (< 768px) -->
          <div class="d-block d-md-none p-3">
            <?php foreach ($attendanceLogs as $log): ?>
              <div class="mobile-unit-card">
                <div class="mobile-unit-left">
                  <span class="font-monospace fw-bold text-dark d-block"><?= date('d M Y', strtotime($log['date'])) ?></span>
                  <small class="text-muted"><?= date('l', strtotime($log['date'])) ?></small>
                  <div class="mt-1">
                    <?php if ($log['status'] === 'present'): ?>
                      <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size: 0.68rem;">Present</span>
                    <?php elseif ($log['status'] === 'late'): ?>
                      <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill" style="font-size: 0.68rem;">Late</span>
                    <?php else: ?>
                      <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill" style="font-size: 0.68rem;">Absent</span>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="mobile-unit-right text-end ps-2">
                  <div class="mobile-unit-title"><?= e($log['subject_name'] ?? 'ICT Diploma Lecture') ?></div>
                  <div class="small text-muted mt-0.5"><?= e($log['remarks'] ?? 'Normal session') ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </div>

    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
