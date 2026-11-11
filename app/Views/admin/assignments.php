<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

$currentFilterVal = !empty($showAllBatches) ? 'all' : (string)($selectedClassId ?? $activeClassId ?? 1);
$defaultDeadline = date('Y-m-d\T23:59', strtotime('+7 days'));
?>

<div class="app-wrapper">
  <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <div class="container-fluid">
      <?= Flash::render() ?>

      <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
          <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
            <div>
              <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-clipboard-check-fill text-primary me-2"></i>Assignments &amp; Deadlines Hub</h4>
              <p class="text-muted small mb-0">Create coursework assignments, assign lecturers, and extend deadlines across diploma batches.</p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
              <div class="d-flex align-items-center gap-2">
                <label for="adminAsgBatchFilter" class="small fw-bold text-secondary mb-0">Batch:</label>
                <select id="adminAsgBatchFilter" class="form-select form-select-sm rounded-pill fw-semibold border-secondary-subtle shadow-xs" style="min-width: 220px;"
                        onchange="window.location.href='index.php?page=admin_assignments&class_id=' + this.value">
                  <option value="all" <?= !empty($showAllBatches) ? 'selected' : '' ?>>All Batches (සියලුම Batches)</option>
                  <?php foreach (($classes ?? []) as $cl): ?>
                    <option value="<?= (int)$cl['id'] ?>" <?= (empty($showAllBatches) && (int)$cl['id'] === (int)($selectedClassId ?? 0)) ? 'selected' : '' ?>>
                      <?= e($cl['class_name']) ?> (<?= ($cl['status'] ?? '') === 'active' ? 'Active' : 'Archived' ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5" style="background: #7A1418; border-color: #7A1418;" data-bs-toggle="modal" data-bs-target="#adminCreateAssignmentModal">
                <i class="bi bi-plus-circle-fill"></i> Create New Assignment
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
          <h6 class="fw-bold mb-0 text-dark">Coursework Assignments (<?= count($assignments) ?>)</h6>
          <span class="small text-muted">Click <strong>Extend / Edit Deadline</strong> to change or extend submission due dates.</span>
        </div>

        <div class="table-responsive">
          <table class="table table-hover table-custom mb-0 align-middle">
            <thead>
              <tr>
                <th>Assignment Title</th>
                <th>Subject &amp; Batch</th>
                <th>Lecturer</th>
                <th>Deadline</th>
                <th>Marks</th>
                <th>Submissions</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($assignments)): ?>
                <tr><td colspan="8" class="text-center py-5 text-muted">No assignments found for the selected batch. Click <strong>Create New Assignment</strong> to publish one.</td></tr>
              <?php else: ?>
                <?php foreach ($assignments as $a): ?>
                  <?php $isPast = (strtotime($a['deadline']) < time()); ?>
                  <tr>
                    <td>
                      <div class="fw-bold text-dark"><?= e($a['title']) ?></div>
                      <small class="text-muted"><?= e(mb_strimwidth((string)$a['instructions'], 0, 70, '...')) ?></small>
                    </td>
                    <td>
                      <div class="small fw-semibold text-dark"><?= e($a['subject_code'] ?? '') ?> <?= e($a['subject_name']) ?></div>
                      <small class="text-muted"><?= e($a['class_name']) ?></small>
                    </td>
                    <td>
                      <span class="text-primary small fw-semibold"><i class="bi bi-person me-1"></i><?= e($a['teacher_name']) ?></span>
                    </td>
                    <td>
                      <span class="fw-semibold <?= $isPast ? 'text-danger' : 'text-dark' ?> small">
                        <i class="bi bi-calendar-event me-1"></i><?= date('M d, Y h:i A', strtotime($a['deadline'])) ?>
                      </span>
                    </td>
                    <td>
                      <span class="badge bg-secondary-subtle text-secondary"><?= number_format((float)$a['total_marks'], 0) ?> Marks</span>
                    </td>
                    <td>
                      <span class="badge bg-primary px-2.5 py-1.5"><?= (int)$a['submission_count'] ?> Submitted</span>
                    </td>
                    <td>
                      <?php if ($isPast): ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Closed</span>
                      <?php else: ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end text-nowrap">
                      <button type="button"
                              class="btn btn-sm btn-outline-warning text-dark fw-semibold rounded-pill px-2.5 me-1"
                              onclick="openExtendDeadlineModal('adminDeadlineModal', <?= (int)$a['id'] ?>, '<?= e(addslashes($a['title'])) ?>', '<?= date('Y-m-d\TH:i', strtotime($a['deadline'])) ?>')">
                        <i class="bi bi-calendar2-plus me-1"></i> <?= $isPast ? 'Re-open / Extend' : 'Extend Deadline' ?>
                      </button>

                      <form action="index.php?page=admin_delete_assignment" method="POST" class="d-inline" onsubmit="return confirm('Delete this assignment and all its submission records?');">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="assignment_id" value="<?= (int)$a['id'] ?>">
                        <input type="hidden" name="subject_id" value="<?= (int)$a['subject_id'] ?>">
                        <input type="hidden" name="class_id" value="<?= (int)$a['class_id'] ?>">
                        <input type="hidden" name="return_to" value="assignments">
                        <input type="hidden" name="filter_class" value="<?= e($currentFilterVal) ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" title="Delete Assignment">
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

<!-- ================= MODAL 1: CREATE ASSIGNMENT ================= -->
<div class="modal fade" id="adminCreateAssignmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 rounded-4 shadow overflow-hidden">
      <div class="modal-header text-white py-3 px-4" style="background: linear-gradient(135deg, #7A1418 0%, #991b1b 100%);">
        <h6 class="modal-title fw-bold mb-0"><i class="bi bi-clipboard2-plus-fill me-2"></i>Create Coursework Assignment</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="index.php?page=admin_create_assignment" method="POST" enctype="multipart/form-data">
        <?= Csrf::field() ?>
        <input type="hidden" name="return_to" value="assignments">

        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-md-12">
              <label class="form-label fw-semibold small text-secondary">Assignment Title *</label>
              <input type="text" name="title" class="form-control" placeholder="e.g. Lab 03: Database Normalization &amp; ERD" required>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small text-secondary">Target Batch / Cohort *</label>
              <select name="class_id" class="form-select" required>
                <?php foreach (($classes ?? []) as $cl): ?>
                  <option value="<?= (int)$cl['id'] ?>" <?= ((int)$cl['id'] === (int)($selectedClassId ?: ($activeClassId ?? 1))) ? 'selected' : '' ?>>
                    <?= e($cl['class_name']) ?> (<?= ($cl['status'] ?? '') === 'active' ? 'Active' : 'Archived' ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small text-secondary">Subject Module *</label>
              <select name="subject_id" class="form-select" required>
                <option value="">-- Select Subject --</option>
                <?php foreach (($subjects ?? []) as $sub): ?>
                  <option value="<?= (int)$sub['id'] ?>"><?= e($sub['subject_code']) ?> - <?= e($sub['subject_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold small text-secondary">Assigned Lecturer *</label>
              <select name="teacher_id" class="form-select" required>
                <?php foreach (($teachers ?? []) as $t): ?>
                  <option value="<?= (int)$t['id'] ?>"><?= e($t['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-5">
              <label class="form-label fw-semibold small text-secondary">Submission Deadline *</label>
              <input type="datetime-local" name="deadline" class="form-control" value="<?= $defaultDeadline ?>" required>
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold small text-secondary">Total Marks *</label>
              <input type="number" step="0.5" name="total_marks" class="form-control" value="100" required>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold small text-secondary">Assignment Instructions *</label>
              <textarea name="instructions" class="form-control" rows="4" placeholder="Write clear guidelines, deliverables, and submission instructions..." required></textarea>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold small text-secondary">Reference File / Assignment Brief (Optional)</label>
              <input type="file" name="reference_file" class="form-control" accept=".pdf,.doc,.docx,.zip,.sql,.txt">
              <small class="text-muted">PDF, Word document, SQL file, or starter ZIP archive (max 25MB).</small>
            </div>

            <div class="col-12">
              <input type="hidden" name="email_prompt_present" value="1">
              <div class="form-check form-switch bg-light border rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between">
                <div>
                  <label class="form-check-label fw-semibold text-dark small mb-0" for="adminAsgNotifyEmail">
                    <i class="bi bi-envelope-check text-primary me-1"></i> Notify enrolled students via Email
                  </label>
                  <div class="text-muted" style="font-size: 0.74rem;">
                    <?= EmailSettings::isCategoryEnabled('assignment') ? 'Uncheck to publish assignment silently without emailing students.' : 'Currently disabled globally in Email Settings.' ?>
                  </div>
                </div>
                <input class="form-check-input ms-2" type="checkbox" role="switch" id="adminAsgNotifyEmail" name="send_email_notification" value="1" <?= EmailSettings::shouldDefaultCheckAction('assignment') ? 'checked' : '' ?> <?= !EmailSettings::isCategoryEnabled('assignment') ? 'disabled' : '' ?>>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-light border-top px-4 py-2.5">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold" style="background: #7A1418; border-color: #7A1418;">
            <i class="bi bi-send-check-fill me-1"></i> Publish Assignment
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= MODAL 2: EXTEND / CHANGE DEADLINE ================= -->
<div class="modal fade" id="adminDeadlineModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow overflow-hidden">
      <div class="modal-header bg-light border-bottom py-3 px-4">
        <h6 class="modal-title fw-bold text-dark mb-0"><i class="bi bi-calendar2-range text-warning me-2"></i>Extend / Update Assignment Deadline</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="index.php?page=admin_update_assignment_deadline" method="POST">
        <?= Csrf::field() ?>
        <input type="hidden" name="assignment_id" value="" data-deadline-id>
        <input type="hidden" name="return_to" value="assignments">
        <input type="hidden" name="filter_class" value="<?= e($currentFilterVal) ?>">

        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Assignment</label>
            <input type="text" class="form-control bg-light fw-bold" readonly data-deadline-title>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Quick Extend Options</label>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold" onclick="applyDeadlineExtension('#adminDeadlineInput', 1)">+24 Hours</button>
              <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold" onclick="applyDeadlineExtension('#adminDeadlineInput', 3)">+3 Days</button>
              <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold" onclick="applyDeadlineExtension('#adminDeadlineInput', 7)">+1 Week</button>
            </div>
          </div>

          <div class="mb-2">
            <label class="form-label fw-semibold small text-secondary">New Deadline Date &amp; Time *</label>
            <input type="datetime-local" name="deadline" id="adminDeadlineInput" class="form-control" required data-deadline-input>
            <small class="text-muted">Closed assignments are automatically re-opened as Active when extended to a future date.</small>
          </div>
        </div>

        <div class="modal-footer bg-light border-top px-4 py-2.5">
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
