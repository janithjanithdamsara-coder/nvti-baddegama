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
            <h3 class="fw-bold mb-1 text-dark">Welcome, <?= e(Auth::name()) ?></h3>
            <p class="mb-0 text-muted">NVTI Baddegama Diploma in ICT &bull; Academic Year 2026</p>
          </div>
          <div class="d-flex gap-2">
            <a href="index.php?page=admin_timetable" class="btn btn-primary fw-bold btn-sm px-3 shadow-sm rounded-pill">
              <i class="bi bi-calendar-plus me-1"></i> Manage Timetable
            </a>
            <a href="index.php?page=admin_teachers" class="btn btn-outline-secondary btn-sm px-3 rounded-pill">
              <i class="bi bi-person-plus me-1"></i> Add Teacher
            </a>
          </div>
        </div>
      </div>

      <!-- KPI Stat Cards -->
      <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-2">
          <div class="card stat-card p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-semibold">Students</span>
                <h4 class="fw-bold mb-0 text-dark"><?= (int)$studentCount ?></h4>
              </div>
              <div class="stat-icon bg-success-subtle text-success">
                <i class="bi bi-mortarboard-fill"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-2">
          <div class="card stat-card p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-semibold">Lecturers</span>
                <h4 class="fw-bold mb-0 text-dark"><?= (int)$teacherCount ?></h4>
              </div>
              <div class="stat-icon bg-primary-subtle text-primary">
                <i class="bi bi-person-video3"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-2">
          <div class="card stat-card p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-semibold">Courses</span>
                <h4 class="fw-bold mb-0 text-dark"><?= (int)$courseCount ?></h4>
              </div>
              <div class="stat-icon bg-info-subtle text-info">
                <i class="bi bi-journal-richtext"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-2">
          <div class="card stat-card p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-semibold">Materials</span>
                <h4 class="fw-bold mb-0 text-dark"><?= (int)$materialCount ?></h4>
              </div>
              <div class="stat-icon bg-warning-subtle text-warning">
                <i class="bi bi-file-earmark-pdf-fill"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-2">
          <div class="card stat-card p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-semibold">Assignments</span>
                <h4 class="fw-bold mb-0 text-dark"><?= (int)$assignmentCount ?></h4>
              </div>
              <div class="stat-icon bg-secondary-subtle text-secondary">
                <i class="bi bi-card-checklist"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-2">
          <div class="card stat-card p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-semibold">Submissions</span>
                <h4 class="fw-bold mb-0 text-dark"><?= (int)$submissionCount ?></h4>
              </div>
              <div class="stat-icon bg-danger-subtle text-danger">
                <i class="bi bi-send-check-fill"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Schedule & Sessions (Morning 09-12 vs Afternoon 13-16) -->
      <div class="row g-4 mb-4">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
              <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-clock-history text-primary me-2"></i>Class Schedule (Morning & Afternoon Shifts)
              </h5>
              <a href="index.php?page=admin_timetable" class="btn btn-sm btn-outline-primary">Manage Schedule</a>
            </div>
            <div class="table-responsive p-3">
              <table class="table table-hover table-custom mb-0">
                <thead>
                  <tr>
                    <th>Day & Slot</th>
                    <th>Time Window</th>
                    <th>Lecturer</th>
                    <th>Subject & Batch</th>
                    <th>Location</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($sessions)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">No sessions scheduled yet.</td></tr>
                  <?php else: ?>
                    <?php foreach ($sessions as $s): ?>
                      <?php
                        $isMorning = (strtotime($s['start_time']) < strtotime('12:30:00'));
                      ?>
                      <tr>
                        <td>
                          <strong><?= e($s['day_of_week']) ?></strong>
                          <div class="mt-1">
                            <?php if ($isMorning): ?>
                              <span class="badge slot-badge-morning"><i class="bi bi-sun-fill me-1"></i> Morning Session</span>
                            <?php else: ?>
                              <span class="badge slot-badge-afternoon"><i class="bi bi-sunset-fill me-1"></i> Afternoon Session</span>
                            <?php endif; ?>
                          </div>
                        </td>
                        <td>
                          <span class="fw-bold text-dark"><?= date('h:i A', strtotime($s['start_time'])) ?> - <?= date('h:i A', strtotime($s['end_time'])) ?></span>
                        </td>
                        <td>
                          <div class="fw-semibold text-primary"><?= e($s['teacher_name']) ?></div>
                        </td>
                        <td>
                          <div class="fw-bold text-dark"><?= e($s['subject_name']) ?></div>
                          <small class="text-muted"><?= e($s['class_name']) ?></small>
                        </td>
                        <td>
                          <span class="badge bg-light text-secondary border"><?= e($s['hall_or_link']) ?></span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Recent Audit Trail -->
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 px-4 border-bottom">
              <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-activity text-secondary me-2"></i>Recent Security Activity</h5>
            </div>
            <div class="card-body p-3">
              <?php if (empty($recentLogs)): ?>
                <p class="text-muted text-center py-4">No recent activity logged.</p>
              <?php else: ?>
                <div class="list-group list-group-flush">
                  <?php foreach ($recentLogs as $log): ?>
                    <div class="list-group-item px-0 py-2 border-bottom">
                      <div class="d-flex justify-content-between align-items-start">
                        <span class="badge bg-dark-subtle text-dark small"><?= e($log['action']) ?></span>
                        <small class="text-muted"><?= date('H:i', strtotime($log['created_at'])) ?></small>
                      </div>
                      <div class="small mt-1 text-secondary"><?= e($log['details']) ?></div>
                      <div class="text-muted" style="font-size: 0.72rem;">User: <?= e($log['user_name'] ?? 'System') ?> | IP: <?= e($log['ip_address']) ?></div>
                    </div>
                  <?php endforeach; ?>
                </div>
                <div class="text-center mt-3">
                  <a href="index.php?page=admin_logs" class="btn btn-light btn-sm text-primary w-100 fw-semibold">View All Audit Logs</a>
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
