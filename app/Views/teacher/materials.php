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
          <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-cloud-arrow-up-fill text-primary me-2"></i>Study Materials & Resources</h4>
          <p class="text-muted small mb-0">Publish PDF notes, past papers, model papers, lab tutorials, YouTube lessons & web links</p>
        </div>

        <button class="btn btn-primary btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadMaterialModal">
          <i class="bi bi-plus-circle-fill me-1"></i> Upload / Add Resource
        </button>
      </div>

      <!-- Materials List -->
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom">
          <h5 class="fw-bold mb-0 text-dark">My Published Learning Materials (<?= count($materials) ?>)</h5>
        </div>

        <div class="table-responsive">
          <table class="table table-hover table-custom mb-0">
            <thead>
              <tr>
                <th>Category</th>
                <th>Title & Description</th>
                <th>Subject & Class</th>
                <th>Type</th>
                <th>Uploaded</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($materials)): ?>
                <tr>
                  <td colspan="6" class="text-center py-5 text-muted">
                    <i class="bi bi-folder2-open fs-1 d-block mb-2 text-secondary"></i>
                    You haven't uploaded any study materials yet.<br>
                    Click the <strong>Upload / Add Resource</strong> button above to upload PDF notes, past papers, or add YouTube links.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($materials as $m): ?>
                  <tr>
                    <td>
                      <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1.5 px-2">
                        <i class="bi <?= e($m['category_icon']) ?> me-1"></i> <?= e($m['category_name']) ?>
                      </span>
                    </td>
                    <td>
                      <div class="fw-bold text-dark"><?= e($m['title']) ?></div>
                      <small class="text-muted"><?= e($m['description']) ?></small>
                    </td>
                    <td>
                      <div class="small fw-semibold"><?= e($m['subject_name']) ?></div>
                      <small class="text-muted"><?= e($m['class_name']) ?></small>
                    </td>
                    <td>
                      <?php if ($m['material_type'] === 'file'): ?>
                        <span class="badge bg-danger-subtle text-danger"><i class="bi bi-file-earmark-pdf me-1"></i> File</span>
                        <div class="text-muted" style="font-size: 0.72rem;"><?= round(($m['file_size_bytes'] ?? 0) / 1024) ?> KB</div>
                      <?php elseif ($m['material_type'] === 'youtube'): ?>
                        <span class="badge bg-danger text-white"><i class="bi bi-youtube me-1"></i> YouTube</span>
                      <?php else: ?>
                        <span class="badge bg-info-subtle text-info"><i class="bi bi-link-45deg me-1"></i> Web Link</span>
                      <?php endif; ?>
                      <div class="mt-1">
                        <?php if (!empty($m['is_hidden'])): ?>
                          <span class="badge bg-secondary text-white rounded-pill" style="font-size: 0.68rem;"><i class="bi bi-eye-slash-fill me-1"></i> Hidden</span>
                        <?php else: ?>
                          <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size: 0.68rem;"><i class="bi bi-eye-fill me-1"></i> Visible</span>
                        <?php endif; ?>
                      </div>
                    </td>
                    <td class="small text-muted"><?= date('M d, Y', strtotime($m['created_at'])) ?></td>
                    <td class="text-end">
                      <div class="d-flex justify-content-end gap-1">
                        <!-- Toggle Visibility -->
                        <form action="index.php?page=teacher_toggle_material_visibility" method="POST" class="d-inline">
                          <?= Csrf::field() ?>
                          <input type="hidden" name="material_id" value="<?= $m['id'] ?>">
                          <button type="submit" class="btn btn-sm <?= !empty($m['is_hidden']) ? 'btn-outline-success' : 'btn-outline-secondary' ?>" title="<?= !empty($m['is_hidden']) ? 'Unhide (Make visible to students)' : 'Hide from students' ?>">
                            <i class="bi <?= !empty($m['is_hidden']) ? 'bi-eye' : 'bi-eye-slash' ?>"></i>
                          </button>
                        </form>

                        <?php if ($m['material_type'] === 'file'): ?>
                          <a href="index.php?page=download_material&id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary" title="Download File">
                            <i class="bi bi-download"></i>
                          </a>
                        <?php else: ?>
                          <a href="<?= e($m['external_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-info" title="Open Link">
                            <i class="bi bi-box-arrow-up-right"></i>
                          </a>
                        <?php endif; ?>

                        <form action="index.php?page=teacher_delete_material" method="POST" onsubmit="return confirm('Delete this study material?');" class="d-inline">
                          <?= Csrf::field() ?>
                          <input type="hidden" name="material_id" value="<?= $m['id'] ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                            <i class="bi bi-trash"></i>
                          </button>
                        </form>
                      </div>
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

<!-- Modal: Upload Material -->
<div class="modal fade" id="uploadMaterialModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
      <div class="modal-header bg-edu-navy text-white py-3">
        <h5 class="modal-title fw-bold"><i class="bi bi-cloud-arrow-up-fill me-2 text-warning"></i>Upload Learning Material</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="index.php?page=teacher_upload_material" method="POST" enctype="multipart/form-data">
        <?= Csrf::field() ?>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-md-7">
              <label class="form-label fw-semibold small text-secondary">Material Title *</label>
              <input type="text" name="title" class="form-control" placeholder="e.g. Chapter 03: Relational Normalization Notes" required>
            </div>

            <div class="col-md-5">
              <label class="form-label fw-semibold small text-secondary">Subject & Batch *</label>
              <select name="subject_class" class="form-select" required>
                <option value="">-- Choose Assigned Subject --</option>
                <?php foreach ($assignedClasses as $ac): ?>
                  <option value="<?= $ac['subject_id'] ?>_<?= $ac['class_id'] ?>">
                    <?= e($ac['subject_code']) ?> - <?= e($ac['class_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small text-secondary">Category *</label>
              <select name="category_id" class="form-select" required>
                <option value="">-- Choose Category --</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>"><?= e($cat['category_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small text-secondary">Resource Type *</label>
              <select name="material_type" id="materialTypeSelect" class="form-select" required onchange="toggleUploadType(this.value)">
                <option value="file">File (PDF, DOCX, ZIP, PPT, Images)</option>
                <option value="youtube">YouTube Video Lecture Link</option>
                <option value="url">External Reference URL / Repo Link</option>
              </select>
            </div>

            <!-- File Upload Input (Conditional) -->
            <div class="col-12" id="fileUploadContainer">
              <label class="form-label fw-semibold small text-secondary">Select Document / File *</label>
              <input type="file" name="file_upload" id="fileUploadInput" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.png,.jpg,.jpeg,.txt,.sql">
              <small class="text-muted">Allowed types: PDF, Word, PowerPoint, ZIP, SQL, Images. Max 25MB.</small>
            </div>

            <!-- External Link Input (Conditional) -->
            <div class="col-12 d-none" id="urlInputContainer">
              <label class="form-label fw-semibold small text-secondary">External URL / YouTube Link *</label>
              <input type="url" name="external_url" id="urlInputField" class="form-control" placeholder="https://www.youtube.com/watch?v=... or https://...">
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold small text-secondary">Description / Lecturer Instructions</label>
              <textarea name="description" class="form-control" rows="2" placeholder="Brief explanation of this study resource for students..."></textarea>
            </div>

            <div class="col-12">
              <input type="hidden" name="email_prompt_present" value="1">
              <div class="form-check form-switch bg-light border rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between">
                <div>
                  <label class="form-check-label fw-semibold text-dark small mb-0" for="teacherMaterialNotifyEmail">
                    <i class="bi bi-envelope-check text-primary me-1"></i> Notify enrolled students via Email
                  </label>
                  <div class="text-muted" style="font-size: 0.74rem;">
                    <?= EmailSettings::isCategoryEnabled('material') ? 'Uncheck if you want to publish silently without sending emails.' : 'Currently disabled globally by Admin in Email Settings.' ?>
                  </div>
                </div>
                <input class="form-check-input ms-2" type="checkbox" role="switch" id="teacherMaterialNotifyEmail" name="send_email_notification" value="1" <?= EmailSettings::shouldDefaultCheckAction('material') ? 'checked' : '' ?> <?= !EmailSettings::isCategoryEnabled('material') ? 'disabled' : '' ?>>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light py-2 px-4">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">Publish to Students</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function toggleUploadType(type) {
  const fileContainer = document.getElementById('fileUploadContainer');
  const urlContainer  = document.getElementById('urlInputContainer');
  const fileInput     = document.getElementById('fileUploadInput');
  const urlInput      = document.getElementById('urlInputField');

  if (type === 'file') {
    fileContainer.classList.remove('d-none');
    urlContainer.classList.add('d-none');
    fileInput.required = true;
    urlInput.required = false;
  } else {
    fileContainer.classList.add('d-none');
    urlContainer.classList.remove('d-none');
    fileInput.required = false;
    urlInput.required = true;
  }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
