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
          <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-clipboard-check-fill text-primary me-2"></i>Assignments & Student Submissions Overview</h4>
          <p class="text-muted small mb-0">Overview of all active assignments, deadlines, and submission counts across all classes</p>
        </div>
      </div>

      <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
          <table class="table table-hover table-custom mb-0">
            <thead>
              <tr>
                <th>Assignment Title</th>
                <th>Subject & Batch</th>
                <th>Lecturer</th>
                <th>Deadline</th>
                <th>Total Marks</th>
                <th>Submissions</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($assignments)): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">No assignments created yet.</td></tr>
              <?php else: ?>
                <?php foreach ($assignments as $a): ?>
                  <?php
                    $isPast = (strtotime($a['deadline']) < time());
                  ?>
                  <tr>
                    <td>
                      <div class="fw-bold text-dark"><?= e($a['title']) ?></div>
                      <small class="text-muted"><?= e(substr($a['instructions'], 0, 70)) ?>...</small>
                    </td>
                    <td>
                      <div class="small fw-semibold"><?= e($a['subject_name']) ?></div>
                      <small class="text-muted"><?= e($a['class_name']) ?></small>
                    </td>
                    <td>
                      <span class="text-primary small fw-semibold"><i class="bi bi-person me-1"></i><?= e($a['teacher_name']) ?></span>
                    </td>
                    <td>
                      <span class="fw-semibold <?= $isPast ? 'text-danger' : 'text-dark' ?> small">
                        <?= date('M d, Y h:i A', strtotime($a['deadline'])) ?>
                      </span>
                    </td>
                    <td>
                      <span class="badge bg-secondary-subtle text-secondary"><?= number_format($a['total_marks'], 0) ?> Marks</span>
                    </td>
                    <td>
                      <span class="badge bg-primary px-2.5 py-1.5"><?= (int)$a['submission_count'] ?> Submitted</span>
                    </td>
                    <td>
                      <?php if ($isPast): ?>
                        <span class="badge bg-danger-subtle text-danger">Closed</span>
                      <?php else: ?>
                        <span class="badge bg-success-subtle text-success">Active</span>
                      <?php endif; ?>
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
