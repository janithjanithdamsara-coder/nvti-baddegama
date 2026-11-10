<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="app-wrapper">
  <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <div class="container-fluid">
      <?= Flash::render() ?>

      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
          <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-calendar-event-fill text-primary me-2"></i>My Diploma Class Timetable</h4>
          <p class="text-muted small mb-0">Weekly schedule of lecture sessions for NVTI Baddegama ICT NVQ Level 5</p>
        </div>
      </div>

      <!-- Quick Shift Info -->
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-4 border-warning bg-white">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle bg-warning bg-opacity-25 text-warning d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="bi bi-sun-fill fs-4"></i>
              </div>
              <div>
                <span class="badge slot-badge-morning mb-1">Morning Session</span>
                <h6 class="fw-bold mb-0 text-dark">09:00 AM – 12:00 PM</h6>
                <small class="text-muted">Teacher 01: Database Systems & Computer Hardware</small>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-4 border-primary bg-white">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle bg-primary bg-opacity-25 text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="bi bi-sunset-fill fs-4"></i>
              </div>
              <div>
                <span class="badge slot-badge-afternoon mb-1">Afternoon Session</span>
                <h6 class="fw-bold mb-0 text-dark">01:00 PM – 04:00 PM</h6>
                <small class="text-muted">Teacher 02: Web Application Development & Software Eng.</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Timetable Card -->
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom">
          <h5 class="fw-bold mb-0 text-dark">Weekly Session Schedule</h5>
        </div>

        <!-- Desktop Timetable -->
        <div class="d-none d-md-block table-responsive">
          <table class="table table-hover table-custom mb-0">
            <thead>
              <tr>
                <th>Day</th>
                <th>Time Window</th>
                <th>Shift</th>
                <th>Subject &amp; Module</th>
                <th>Lecturer</th>
                <th>Location / Lab</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($sessions)): ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">No scheduled sessions found for your batch.</td></tr>
              <?php else: ?>
                <?php foreach ($sessions as $s): ?>
                  <?php $isMorn = (strtotime($s['start_time']) < strtotime('12:30:00')); ?>
                  <tr>
                    <td><strong><?= e($s['day_of_week']) ?></strong></td>
                    <td>
                      <span class="fw-bold text-dark"><?= date('h:i A', strtotime($s['start_time'])) ?> - <?= date('h:i A', strtotime($s['end_time'])) ?></span>
                    </td>
                    <td>
                      <?php if ($isMorn): ?>
                        <span class="badge slot-badge-morning"><i class="bi bi-sun-fill me-1"></i> Morning (09-12)</span>
                      <?php else: ?>
                        <span class="badge slot-badge-afternoon"><i class="bi bi-sunset-fill me-1"></i> Afternoon (13-16)</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="fw-bold text-dark"><?= e($s['subject_name']) ?></div>
                      <small class="text-muted"><?= e($s['subject_code']) ?></small>
                    </td>
                    <td>
                      <span class="text-primary fw-semibold"><i class="bi bi-person me-1"></i><?= e($s['teacher_name']) ?></span>
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

        <!-- Mobile Timetable Cards (< 768px) -->
        <div class="d-md-none p-3 bg-light">
          <?php if (empty($sessions)): ?>
            <div class="text-center py-4 text-muted small">No scheduled sessions found for your batch.</div>
          <?php else: ?>
            <div class="d-flex flex-column gap-2.5">
              <?php foreach ($sessions as $s): ?>
                <?php $isMorn = (strtotime($s['start_time']) < strtotime('12:30:00')); ?>
                <div class="card border rounded-4 p-3 bg-white shadow-xs">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-dark fs-6"><?= e($s['day_of_week']) ?></span>
                    <?php if ($isMorn): ?>
                      <span class="badge slot-badge-morning rounded-pill px-2.5 py-1"><i class="bi bi-sun-fill me-1"></i> Morning</span>
                    <?php else: ?>
                      <span class="badge slot-badge-afternoon rounded-pill px-2.5 py-1"><i class="bi bi-sunset-fill me-1"></i> Afternoon</span>
                    <?php endif; ?>
                  </div>
                  <div class="fw-bold text-dark mb-0.5"><?= e($s['subject_name']) ?></div>
                  <div class="small text-muted font-monospace mb-2"><?= e($s['subject_code']) ?></div>
                  <div class="pt-2 border-top d-flex flex-wrap justify-content-between align-items-center gap-2 small">
                    <span class="fw-semibold text-dark"><i class="bi bi-clock text-primary me-1"></i><?= date('h:i A', strtotime($s['start_time'])) ?> - <?= date('h:i A', strtotime($s['end_time'])) ?></span>
                    <span class="badge bg-light text-secondary border"><?= e($s['hall_or_link']) ?></span>
                  </div>
                  <div class="small text-primary fw-medium mt-1.5">
                    <i class="bi bi-person me-1"></i><?= e($s['teacher_name']) ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
