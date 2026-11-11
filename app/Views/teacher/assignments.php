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
                    <td class="text-end text-nowrap">
                      <button type="button"
                              class="btn btn-sm btn-outline-warning text-dark fw-semibold me-1"
                              onclick="openExtendDeadlineModal('teacherDeadlineModal', <?= (int)$a['id'] ?>, '<?= e(addslashes($a['title'])) ?>', '<?= date('Y-m-d\TH:i', strtotime($a['deadline'])) ?>')">
                        <i class="bi bi-calendar2-plus me-1"></i> <?= $isPast ? 'Re-open / Extend' : 'Extend Deadline' ?>
                      </button>
                      <a href="index.php?page=teacher_submissions&assignment_id=<?= $a['id'] ?>" class="btn btn-sm btn-primary me-1">
                        <i class="bi bi-eye-fill me-1"></i> View &amp; Grade (<?= (int)$a['total_submissions'] ?>)
                      </a>
                      <form action="index.php?page=teacher_delete_assignment" method="POST" class="d-inline" onsubmit="return confirm('Delete this assignment and all its submission records?');">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="assignment_id" value="<?= (int)$a['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Assignment">
                          <i class="bi bi-trash3"></i>
                        </button>
                      </form>
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
              <label class="form-label fw-semibold small text-secondary">Subject &amp; Batch *</label>
              <select name="subject_class" class="form-select" required>
                <option value="">-- Choose Subject --</option>
                <?php foreach ($assignedClasses as $ac): ?>
                  <option value="<?= (int)$ac['subject_id'] ?>_<?= (int)$ac['class_id'] ?>">
                    <?= e($ac['subject_code']) ?> - <?= e($ac['subject_name']) ?> (<?= e($ac['class_name']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small text-secondary">Deadline Date &amp; Time *</label>
              <input type="datetime-local" name="deadline" class="form-control" value="<?= date('Y-m-d\T23:59', strtotime('+7 days')) ?>" required>
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

            <div class="col-12">
              <input type="hidden" name="email_prompt_present" value="1">
              <div class="form-check form-switch bg-light border rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between">
                <div>
                  <label class="form-check-label fw-semibold text-dark small mb-0" for="teacherAsgNotifyEmail">
                    <i class="bi bi-envelope-check text-primary me-1"></i> Notify enrolled students via Email
                  </label>
                  <div class="text-muted" style="font-size: 0.74rem;">
                    <?= EmailSettings::isCategoryEnabled('assignment') ? 'Uncheck to publish assignment silently without sending emails.' : 'Currently disabled globally by Admin in Email Settings.' ?>
                  </div>
                </div>
                <input class="form-check-input ms-2" type="checkbox" role="switch" id="teacherAsgNotifyEmail" name="send_email_notification" value="1" <?= EmailSettings::shouldDefaultCheckAction('assignment') ? 'checked' : '' ?> <?= !EmailSettings::isCategoryEnabled('assignment') ? 'disabled' : '' ?>>
              </div>
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

<!-- Modal: Extend / Update Assignment Deadline -->
<div class="modal fade" id="teacherDeadlineModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
      <div class="modal-header bg-light border-bottom py-3 px-4">
        <h6 class="modal-title fw-bold text-dark mb-0"><i class="bi bi-calendar2-range text-warning me-2"></i>Extend / Change Deadline</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="index.php?page=teacher_update_assignment_deadline" method="POST">
        <?= Csrf::field() ?>
        <input type="hidden" name="assignment_id" value="" data-deadline-id>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Assignment</label>
            <input type="text" class="form-control bg-light fw-bold" readonly data-deadline-title>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Quick Extend</label>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold" onclick="applyDeadlineExtension('#teacherDeadlineInput', 1)">+24 Hours</button>
              <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold" onclick="applyDeadlineExtension('#teacherDeadlineInput', 3)">+3 Days</button>
              <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold" onclick="applyDeadlineExtension('#teacherDeadlineInput', 7)">+1 Week</button>
            </div>
          </div>

          <div class="mb-2">
            <label class="form-label fw-semibold small text-secondary">New Deadline Date &amp; Time *</label>
            <input type="datetime-local" name="deadline" id="teacherDeadlineInput" class="form-control" required data-deadline-input>
            <small class="text-muted">Closed assignments are automatically re-opened when extended to a future date.</small>
          </div>
        </div>
        <div class="modal-footer bg-light py-2 px-4">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning btn-sm rounded-pill px-4 fw-bold text-dark">
            <i class="bi bi-check2-circle me-1"></i> Save New Deadline
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
