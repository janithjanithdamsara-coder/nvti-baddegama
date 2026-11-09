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
          <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>My Coursework & Assignments</h4>
          <p class="text-muted small mb-0">View assignment briefs, submit your answers (PDF/ZIP), and check marks with lecturer feedback</p>
        </div>
      </div>

      <div class="row g-4">
        <?php if (empty($assignments)): ?>
          <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
              <i class="bi bi-journal-check fs-1 mb-2 text-secondary"></i>
              <h5>No assignments currently scheduled</h5>
              <p class="small">Check back later or review your study materials.</p>
            </div>
          </div>
        <?php else: ?>
          <?php foreach ($assignments as $a): ?>
            <?php
              $isSubmitted = !empty($a['submission_id']);
              $isGraded = ($a['submission_status'] === 'graded');
              $isResubmit = ($a['submission_status'] === 'resubmit_required');
              $isDeadlinePassed = (strtotime($a['deadline']) < time());
            ?>
            <div class="col-lg-6">
              <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column justify-content-between overflow-hidden">
                <div>
                  <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                      <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-1"><?= e($a['subject_code']) ?></span>
                      <h5 class="fw-bold mb-0 text-dark"><?= e($a['title']) ?></h5>
                    </div>

                    <div>
                      <?php if ($isGraded): ?>
                        <span class="badge bg-success px-2.5 py-1.5"><i class="bi bi-check-circle-fill me-1"></i> Graded</span>
                      <?php elseif ($isResubmit): ?>
                        <span class="badge bg-warning text-dark px-2.5 py-1.5"><i class="bi bi-arrow-repeat me-1"></i> Resubmit</span>
                      <?php elseif ($isSubmitted): ?>
                        <span class="badge bg-info text-white px-2.5 py-1.5"><i class="bi bi-clock-history me-1"></i> Submitted</span>
                      <?php elseif ($isDeadlinePassed): ?>
                        <span class="badge bg-danger px-2.5 py-1.5"><i class="bi bi-x-circle-fill me-1"></i> Deadline Passed</span>
                      <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1.5">Pending Submission</span>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between small text-muted mb-3 pb-2 border-bottom">
                      <div><i class="bi bi-person me-1"></i> Lecturer: <strong><?= e($a['teacher_name']) ?></strong></div>
                      <div><i class="bi bi-award me-1"></i> Max Marks: <strong><?= number_format($a['total_marks'], 0) ?></strong></div>
                      <div><i class="bi bi-calendar-event me-1"></i> Deadline: <strong class="<?= $isDeadlinePassed ? 'text-danger' : 'text-dark' ?>"><?= date('M d, Y h:i A', strtotime($a['deadline'])) ?></strong></div>
                    </div>

                    <div class="mb-3">
                      <label class="fw-semibold small text-dark d-block mb-1">Instructions:</label>
                      <div class="p-3 bg-light rounded-3 small text-secondary">
                        <?= nl2br(e($a['instructions'])) ?>
                      </div>
                    </div>

                    <!-- Graded Feedback Alert if marked -->
                    <?php if ($isGraded): ?>
                      <div class="alert alert-success border-success-subtle shadow-2xs p-3 rounded-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                          <strong class="text-success"><i class="bi bi-award-fill me-1"></i> Marks Awarded:</strong>
                          <span class="fs-5 fw-bold text-success"><?= $a['marks'] ?> / <?= number_format($a['total_marks'], 0) ?></span>
                        </div>
                        <?php if (!empty($a['feedback'])): ?>
                          <div class="small mt-1 text-dark"><strong>Lecturer Feedback:</strong> <?= e($a['feedback']) ?></div>
                        <?php endif; ?>
                      </div>
                    <?php endif; ?>

                    <!-- Existing Submission Details -->
                    <?php if ($isSubmitted): ?>
                      <div class="p-2.5 bg-light border rounded-3 mb-3">
                        <small class="text-muted d-block mb-1">Your Submission:</small>
                        <div class="d-flex justify-content-between align-items-center">
                          <span class="fw-semibold small text-primary text-truncate" style="max-width: 220px;">
                            <i class="bi bi-file-earmark-check me-1"></i> <?= e($a['sub_orig_name']) ?>
                          </span>
                          <span class="small text-muted"><?= date('M d, H:i', strtotime($a['submitted_at'])) ?></span>
                        </div>
                        <?php if (!empty($a['student_comment'])): ?>
                          <div class="small text-muted mt-1 fst-italic">"<?= e($a['student_comment']) ?>"</div>
                        <?php endif; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="card-footer bg-light p-3 border-top">
                  <button type="button" class="btn btn-<?= $isSubmitted ? 'outline-primary' : 'primary' ?> w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#submitModal<?= $a['id'] ?>">
                    <i class="bi <?= $isSubmitted ? 'bi-arrow-repeat' : 'bi-cloud-arrow-up-fill' ?> me-1"></i> 
                    <?= $isSubmitted ? 'Resubmit / Update Answer' : 'Upload Assignment Answer' ?>
                  </button>
                </div>
              </div>
            </div>

            <!-- Submit Modal -->
            <div class="modal fade" id="submitModal<?= $a['id'] ?>" tabindex="-1">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                  <div class="modal-header bg-edu-navy text-white py-3">
                    <h5 class="modal-title fw-bold"><i class="bi bi-cloud-arrow-up-fill me-2 text-warning"></i>Submit Assignment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <form action="index.php?page=student_submit_assignment" method="POST" enctype="multipart/form-data">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="assignment_id" value="<?= $a['id'] ?>">

                    <div class="modal-body p-4 text-start">
                      <div class="p-3 bg-light rounded-3 mb-3">
                        <strong class="text-dark d-block"><?= e($a['title']) ?></strong>
                        <small class="text-muted">Deadline: <?= date('M d, Y h:i A', strtotime($a['deadline'])) ?></small>
                      </div>

                      <?php if ($isDeadlinePassed): ?>
                        <div class="alert alert-warning py-2 small mb-3">
                          <i class="bi bi-exclamation-triangle-fill me-1"></i> The deadline has passed. This will be marked as a <strong>Late Submission</strong>.
                        </div>
                      <?php endif; ?>

                      <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Upload Assignment File *</label>
                        <input type="file" name="submission_file" class="form-control" accept=".pdf,.doc,.docx,.zip,.rar,.txt" required>
                        <small class="text-muted">Formats: PDF, Word (DOCX), ZIP archive. Max 25MB.</small>
                      </div>

                      <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Student Notes / Remarks (Optional)</label>
                        <textarea name="student_comment" class="form-control" rows="2" placeholder="Optional comments for your lecturer..."><?= e($a['student_comment'] ?? '') ?></textarea>
                      </div>
                    </div>

                    <div class="modal-footer bg-light py-2 px-4">
                      <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">Confirm & Upload</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
