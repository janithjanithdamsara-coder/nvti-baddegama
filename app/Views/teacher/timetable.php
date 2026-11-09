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
          <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-clock-fill text-primary me-2"></i>My Teaching Schedule & Sessions</h4>
          <p class="text-muted small mb-0">Weekly timetable for NVTI Baddegama ICT Level 5 Diploma classes</p>
        </div>
      </div>

      <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom">
          <h5 class="fw-bold mb-0 text-dark">Assigned Class Sessions</h5>
        </div>

        <div class="table-responsive">
          <table class="table table-hover table-custom mb-0">
            <thead>
              <tr>
                <th>Day</th>
                <th>Time Window</th>
                <th>Shift Type</th>
                <th>Subject & Batch</th>
                <th>Venue / Lab</th>
                <th>Notes</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($sessions)): ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">No timetable sessions assigned to you yet.</td></tr>
              <?php else: ?>
                <?php foreach ($sessions as $s): ?>
                  <?php $isMorn = (strtotime($s['start_time']) < strtotime('12:30:00')); ?>
                  <tr>
                    <td><strong><?= e($s['day_of_week']) ?></strong></td>
                    <td><span class="fw-bold text-dark"><?= date('h:i A', strtotime($s['start_time'])) ?> - <?= date('h:i A', strtotime($s['end_time'])) ?></span></td>
                    <td>
                      <?php if ($isMorn): ?>
                        <span class="badge slot-badge-morning"><i class="bi bi-sun-fill me-1"></i> Morning Session (09:00 - 12:00)</span>
                      <?php else: ?>
                        <span class="badge slot-badge-afternoon"><i class="bi bi-sunset-fill me-1"></i> Afternoon Session (13:00 - 16:00)</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="fw-bold text-dark"><?= e($s['subject_name']) ?></div>
                      <small class="text-muted"><?= e($s['class_name']) ?></small>
                    </td>
                    <td><span class="badge bg-light text-secondary border"><?= e($s['hall_or_link']) ?></span></td>
                    <td class="small text-muted"><?= e($s['notes'] ?? 'Regular Lecture') ?></td>
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
