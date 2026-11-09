<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

$isMorningSlot = str_contains(strtolower($profile['preferred_slot'] ?? ''), 'morning');
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
              <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1.5 rounded-pill"><i class="bi bi-person-badge-fill me-1"></i> Lecturer Portal</span>
              <?php if ($isMorningSlot): ?>
                <span class="badge slot-badge-morning rounded-pill"><i class="bi bi-sun-fill me-1"></i> Morning Session: 09:00 - 12:00</span>
              <?php else: ?>
                <span class="badge slot-badge-afternoon rounded-pill"><i class="bi bi-sunset-fill me-1"></i> Afternoon Session: 13:00 - 16:00</span>
              <?php endif; ?>
            </div>
            <h3 class="fw-bold mb-1 text-dark"><?= e(Auth::name()) ?></h3>
            <p class="mb-0 text-muted"><?= e($profile['qualification'] ?? 'NVTI Baddegama Diploma Faculty') ?> &bull; Code: <?= e($profile['teacher_code'] ?? 'NVTI-TCH') ?></p>
          </div>
          <div class="d-flex gap-2">
            <a href="index.php?page=teacher_attendance" class="btn btn-outline-success fw-bold btn-sm px-3 shadow-sm rounded-pill">
              <i class="bi bi-calendar2-check-fill me-1"></i> Attendance &amp; Monitors
            </a>
            <a href="index.php?page=teacher_materials" class="btn btn-primary fw-bold btn-sm px-3 shadow-sm rounded-pill">
              <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload Material
            </a>
            <a href="index.php?page=teacher_assignments" class="btn btn-outline-secondary btn-sm px-3 rounded-pill">
              <i class="bi bi-journal-plus me-1"></i> Create Assignment
            </a>
          </div>
        </div>
      </div>

      <!-- KPI Stat Cards -->
      <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-4">
          <div class="card stat-card p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-semibold">Uploaded Materials</span>
                <h4 class="fw-bold mb-0 text-dark"><?= (int)$materialsCount ?></h4>
              </div>
              <div class="stat-icon bg-primary-subtle text-primary">
                <i class="bi bi-file-earmark-arrow-up-fill"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-md-4">
          <div class="card stat-card p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-semibold">Assignments Created</span>
                <h4 class="fw-bold mb-0 text-dark"><?= (int)$assignmentsCount ?></h4>
              </div>
              <div class="stat-icon bg-success-subtle text-success">
                <i class="bi bi-journal-text"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-md-4">
          <div class="card stat-card p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-semibold">Pending to Grade</span>
                <h4 class="fw-bold mb-0 text-danger"><?= (int)$pendingGradingCount ?></h4>
              </div>
              <div class="stat-icon bg-danger-subtle text-danger">
                <i class="bi bi-exclamation-circle-fill"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4 mb-4">
        <!-- Teaching Schedule (Morning / Afternoon) -->
        <div class="col-lg-7">
          <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
              <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar-week-fill text-primary me-2"></i>My Teaching Schedule</h5>
              <a href="index.php?page=teacher_timetable" class="btn btn-sm btn-outline-primary">Full Timetable</a>
            </div>
            <div class="table-responsive p-3">
              <table class="table table-hover table-custom mb-0">
                <thead>
                  <tr>
                    <th>Day</th>
                    <th>Time Window</th>
                    <th>Shift</th>
                    <th>Subject & Class</th>
                    <th>Venue</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($sessions)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">No scheduled lecture sessions for your account.</td></tr>
                  <?php else: ?>
                    <?php foreach ($sessions as $s): ?>
                      <?php $isMorn = (strtotime($s['start_time']) < strtotime('12:30:00')); ?>
                      <tr>
                        <td><strong><?= e($s['day_of_week']) ?></strong></td>
                        <td><span class="fw-bold text-dark"><?= date('h:i A', strtotime($s['start_time'])) ?> - <?= date('h:i A', strtotime($s['end_time'])) ?></span></td>
                        <td>
                          <?php if ($isMorn): ?>
                            <span class="badge slot-badge-morning"><i class="bi bi-sun-fill me-1"></i> Morning (09-12)</span>
                          <?php else: ?>
                            <span class="badge slot-badge-afternoon"><i class="bi bi-sunset-fill me-1"></i> Afternoon (13-16)</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <div class="fw-bold text-dark"><?= e($s['subject_name']) ?></div>
                          <small class="text-muted"><?= e($s['class_name']) ?></small>
                        </td>
                        <td><span class="badge bg-light text-secondary border"><?= e($s['hall_or_link']) ?></span></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Recent Submissions To Grade -->
        <div class="col-lg-5">
          <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
              <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clipboard-check text-success me-2"></i>Recent Submissions</h5>
              <a href="index.php?page=teacher_submissions" class="btn btn-sm btn-outline-success">Review All</a>
            </div>
            <div class="card-body p-3">
              <?php if (empty($recentSubmissions)): ?>
                <p class="text-muted text-center py-4">No student submissions submitted yet.</p>
              <?php else: ?>
                <div class="list-group list-group-flush">
                  <?php foreach ($recentSubmissions as $sub): ?>
                    <div class="list-group-item px-0 py-2 border-bottom">
                      <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                          <strong class="text-dark small"><?= e($sub['student_name']) ?></strong>
                          <span class="badge bg-light text-dark border ms-1" style="font-size:0.7rem;"><?= e($sub['student_code']) ?></span>
                        </div>
                        <?php if ($sub['status'] === 'graded'): ?>
                          <span class="badge bg-success-subtle text-success small"><?= $sub['marks'] ?> / <?= $sub['total_marks'] ?></span>
                        <?php else: ?>
                          <span class="badge bg-warning-subtle text-warning small">To Grade</span>
                        <?php endif; ?>
                      </div>
                      <div class="small text-muted text-truncate"><?= e($sub['assignment_title']) ?></div>
                      <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted"><?= date('M d, H:i', strtotime($sub['submitted_at'])) ?></small>
                        <a href="index.php?page=teacher_submissions&assignment_id=<?= $sub['assignment_id'] ?>" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:0.75rem;">
                          <i class="bi bi-pencil-square me-1"></i> Grade
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

    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
