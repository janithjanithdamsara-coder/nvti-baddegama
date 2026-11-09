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
          <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-clipboard-data-fill text-primary me-2"></i>Review Submissions & Enter Marks</h4>
          <p class="text-muted small mb-0">Evaluate student work, download submitted files, award marks and write feedback</p>
        </div>

        <!-- Assignment Filter Dropdown -->
        <form action="index.php" method="GET" class="d-flex align-items-center gap-2">
          <input type="hidden" name="page" value="teacher_submissions">
          <select name="assignment_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <?php foreach ($teacherAssignments as $ta): ?>
              <option value="<?= $ta['id'] ?>" <?= ($ta['id'] == $assignmentId) ? 'selected' : '' ?>>
                <?= e($ta['subject_code']) ?>: <?= e($ta['title']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>

      <?php if ($selectedAssignment): ?>
        <!-- Assignment Info Banner -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 p-3 bg-light">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
              <span class="badge bg-primary text-white mb-1">Active Assignment Details</span>
              <h5 class="fw-bold text-dark mb-1"><?= e($selectedAssignment['title']) ?></h5>
              <div class="small text-muted">
                <i class="bi bi-calendar-check me-1"></i> Deadline: <strong><?= date('M d, Y h:i A', strtotime($selectedAssignment['deadline'])) ?></strong> | 
                <i class="bi bi-award me-1 ms-2"></i> Maximum Marks: <strong><?= number_format($selectedAssignment['total_marks'], 0) ?></strong>
              </div>
            </div>
            <div>
              <span class="badge bg-dark px-3 py-2 fs-6"><?= count($submissions) ?> Submissions</span>
            </div>
          </div>
        </div>

        <!-- Submissions List -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
          <div class="table-responsive">
            <table class="table table-hover table-custom mb-0">
              <thead>
                <tr>
                  <th>Student</th>
                  <th>Submitted File</th>
                  <th>Submitted Time</th>
                  <th>Status</th>
                  <th>Marks</th>
                  <th>Feedback</th>
                  <th class="text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($submissions)): ?>
                  <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                      <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                      No students have submitted answers for this assignment yet.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($submissions as $sub): ?>
                    <tr>
                      <td>
                        <div class="fw-bold text-dark"><?= e($sub['student_name']) ?></div>
                        <span class="badge bg-light text-dark border small"><?= e($sub['student_code']) ?></span>
                        <div class="text-muted" style="font-size:0.75rem;"><?= e($sub['student_email']) ?></div>
                      </td>
                      <td>
                        <a href="index.php?page=download_submission&id=<?= $sub['id'] ?>" class="btn btn-sm btn-outline-primary fw-semibold">
                          <i class="bi bi-file-earmark-arrow-down me-1"></i> <?= e($sub['original_filename']) ?>
                        </a>
                        <?php if (!empty($sub['student_comment'])): ?>
                          <div class="small text-muted mt-1 fst-italic">"<?= e($sub['student_comment']) ?>"</div>
                        <?php endif; ?>
                      </td>
                      <td>
                        <span class="small fw-semibold"><?= date('M d, Y h:i A', strtotime($sub['submitted_at'])) ?></span>
                        <?php if (!empty($sub['is_late'])): ?>
                          <span class="badge bg-danger text-white ms-1" style="font-size:0.65rem;">LATE</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if ($sub['status'] === 'graded'): ?>
                          <span class="badge bg-success-subtle text-success border border-success-subtle">Graded</span>
                        <?php elseif ($sub['status'] === 'resubmit_required'): ?>
                          <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Resubmit Req.</span>
                        <?php else: ?>
                          <span class="badge bg-secondary-subtle text-secondary">Pending Grading</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if ($sub['marks'] !== null): ?>
                          <strong class="text-success fs-6"><?= $sub['marks'] ?></strong>
                          <span class="text-muted small">/ <?= number_format($selectedAssignment['total_marks'], 0) ?></span>
                        <?php else: ?>
                          <span class="text-muted small">Not graded</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <span class="small text-secondary"><?= e($sub['feedback'] ?? 'None yet') ?></span>
                      </td>
                      <td class="text-end">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#gradeModal<?= $sub['id'] ?>">
                          <i class="bi bi-pencil-fill me-1"></i> Grade
                        </button>
                      </td>
                    </tr>

                    <!-- Grade Modal -->
                    <div class="modal fade" id="gradeModal<?= $sub['id'] ?>" tabindex="-1">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                          <div class="modal-header bg-edu-navy text-white py-3">
                            <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-warning"></i>Grade Student Submission</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                          </div>
                          <form action="index.php?page=teacher_grade_submission" method="POST">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
                            <input type="hidden" name="assignment_id" value="<?= $selectedAssignment['id'] ?>">
                            <div class="modal-body p-4 text-start">
                              <div class="p-3 bg-light rounded-3 mb-3">
                                <div class="fw-bold text-dark"><?= e($sub['student_name']) ?> (<?= e($sub['student_code']) ?>)</div>
                                <div class="small text-muted">Assignment: <?= e($selectedAssignment['title']) ?> (Max Marks: <?= number_format($selectedAssignment['total_marks'], 0) ?>)</div>
                              </div>

                              <div class="mb-3">
                                <label class="form-label fw-semibold small text-secondary">Award Marks (Max: <?= number_format($selectedAssignment['total_marks'], 0) ?>) *</label>
                                <input type="number" step="0.5" min="0" max="<?= $selectedAssignment['total_marks'] ?>" name="marks" class="form-control" value="<?= $sub['marks'] ?? '' ?>" required>
                              </div>

                              <div class="mb-3">
                                <label class="form-label fw-semibold small text-secondary">Submission Status *</label>
                                <select name="status" class="form-select" required>
                                  <option value="graded" <?= ($sub['status'] === 'graded') ? 'selected' : '' ?>>Graded (Completed)</option>
                                  <option value="resubmit_required" <?= ($sub['status'] === 'resubmit_required') ? 'selected' : '' ?>>Resubmission Required</option>
                                </select>
                              </div>

                              <div class="mb-3">
                                <label class="form-label fw-semibold small text-secondary">Lecturer Feedback / Remarks</label>
                                <textarea name="feedback" class="form-control" rows="3" placeholder="Write feedback, praise, corrections, or suggestions for the student..."><?= e($sub['feedback'] ?? '') ?></textarea>
                              </div>
                            </div>
                            <div class="modal-footer bg-light py-2 px-4">
                              <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                              <button type="submit" class="btn btn-success btn-sm px-4 fw-bold">Save Marks & Feedback</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
          <i class="bi bi-journal-x fs-1 mb-2 text-secondary"></i>
          <h5>No Assignments Created Yet</h5>
          <p class="small">Create an assignment first to view and grade student submissions.</p>
          <a href="index.php?page=teacher_assignments" class="btn btn-primary btn-sm px-4">Go to Assignments</a>
        </div>
      <?php endif; ?>

    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
