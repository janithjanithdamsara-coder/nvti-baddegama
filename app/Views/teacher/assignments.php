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
          <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-journal-text text-primary me-2"></i>Assignments Management</h4>
          <p class="text-muted small mb-0">Create coursework, set deadlines, upload briefs, and manage student submissions</p>
        </div>

        <button class="btn btn-primary btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#createAssignmentModal">
          <i class="bi bi-plus-circle me-1"></i> Create New Assignment
        </button>
      </div>

      <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom">
          <h5 class="fw-bold mb-0 text-dark">My Created Assignments (<?= count($assignments) ?>)</h5>
        </div>

        <div class="table-responsive">
          <table class="table table-hover table-custom mb-0">
            <thead>
              <tr>
                <th>Title & Subject</th>
                <th>Deadline</th>
                <th>Total Marks</th>
                <th>Submissions</th>
                <th>Graded</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($assignments)): ?>
                <tr>
                  <td colspan="7" class="text-center py-5 text-muted">
                    <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary"></i>
                    No assignments created yet. Click <strong>Create New Assignment</strong> to create one.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($assignments as $a): ?>
                  <?php $isPast = (strtotime($a['deadline']) < time()); ?>
                  <tr>
                    <td>
                      <div class="fw-bold text-dark"><?= e($a['title']) ?></div>
                      <small class="text-muted"><?= e($a['subject_name']) ?> (<?= e($a['class_name']) ?>)</small>
                    </td>
                    <td>
                      <span class="small fw-semibold <?= $isPast ? 'text-danger' : 'text-dark' ?>">
                        <i class="bi bi-calendar-event me-1"></i><?= date('M d, Y h:i A', strtotime($a['deadline'])) ?>
                      </span>
                    </td>
                    <td>
                      <span class="badge bg-secondary-subtle text-secondary"><?= number_format($a['total_marks'], 0) ?> Marks</span>
                    </td>
                    <td>
                      <span class="badge bg-primary px-2.5 py-1.5"><?= (int)$a['total_submissions'] ?> Submissions</span>
                    </td>
                    <td>
                      <span class="badge bg-success-subtle text-success"><?= (int)$a['graded_count'] ?> Graded</span>
                    </td>
                    <td>
                      <?php if ($isPast): ?>
                        <span class="badge bg-danger-subtle text-danger">Closed</span>
                      <?php else: ?>
                        <span class="badge bg-success-subtle text-success">Active</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end">
                      <a href="index.php?page=teacher_submissions&assignment_id=<?= $a['id'] ?>" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye-fill me-1"></i> View & Grade (<?= (int)$a['total_submissions'] ?>)
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

<!-- Modal: Create Assignment -->
<div class="modal fade" id="createAssignmentModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
      <div class="modal-header bg-edu-navy text-white py-3">
        <h5 class="modal-title fw-bold"><i class="bi bi-journal-plus me-2 text-warning"></i>Create Assignment</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="index.php?page=teacher_create_assignment" method="POST" enctype="multipart/form-data">
        <?= Csrf::field() ?>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-md-7">
              <label class="form-label fw-semibold small text-secondary">Assignment Title *</label>
              <input type="text" name="title" class="form-control" placeholder="e.g. Practical Lab 02: MySQL Complex Queries" required>
            </div>

            <div class="col-md-5">
              <label class="form-label fw-semibold small text-secondary">Subject & Batch *</label>
              <select name="subject_class" class="form-select" required>
                <option value="">-- Choose Subject --</option>
                <?php foreach ($assignedClasses as $ac): ?>
                  <option value="<?= $ac['subject_id'] ?>_<?= $ac['class_id'] ?>">
                    <?= e($ac['subject_code']) ?> - <?= e($ac['class_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small text-secondary">Deadline Date & Time *</label>
              <input type="datetime-local" name="deadline" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small text-secondary">Total Marks *</label>
              <input type="number" step="0.5" name="total_marks" class="form-control" value="100" required>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold small text-secondary">Assignment Instructions & Guidelines *</label>
              <textarea name="instructions" class="form-control" rows="4" placeholder="Detail the submission requirements, format (PDF/ZIP), rubric, etc..." required></textarea>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold small text-secondary">Reference File / Assignment Brief (Optional)</label>
              <input type="file" name="reference_file" class="form-control" accept=".pdf,.doc,.docx,.zip">
              <small class="text-muted">Upload question paper PDF, project template ZIP or guidelines document.</small>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light py-2 px-4">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">Publish Assignment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
