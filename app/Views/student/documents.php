<?php
/**
 * Student Verification Documents & Certificates Portal View
 * NVTI Baddegama National Diploma Portal
 */

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="app-wrapper">
  <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <div class="container-fluid">
      <?= Flash::render() ?>

      <!-- Header Banner -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
        <div class="card-body p-4">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-semibold">
                  <i class="bi bi-shield-check me-1"></i> Student Verification Portal
                </span>
                <span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1">
                  NVQ Level 5 CBT
                </span>
              </div>
              <h3 class="fw-bold text-dark mb-1">Official Certificates &amp; Verification Documents</h3>
              <p class="text-muted small mb-0">
                ලියාපදිංචි සහතික හා ලේඛන උඩුගත කිරීම &bull; Student: <strong><?= e(Auth::user()['name']) ?></strong> (<?= e($profile['student_code'] ?? 'N/A') ?>)
              </p>
            </div>

            <!-- Progress Summary Pill Box -->
            <div class="bg-light p-3 rounded-4 border d-flex align-items-center gap-3">
              <div class="text-center pe-3 border-end">
                <div class="fs-4 fw-bold text-primary mb-0"><?= $summary['uploaded'] ?>/<?= $summary['total'] ?></div>
                <div class="text-muted text-uppercase" style="font-size: 0.7rem; font-weight: 600;">Uploaded</div>
              </div>
              <div class="text-center pe-3 border-end">
                <div class="fs-4 fw-bold text-success mb-0"><?= $summary['verified'] ?></div>
                <div class="text-muted text-uppercase" style="font-size: 0.7rem; font-weight: 600;">Verified</div>
              </div>
              <div class="text-center">
                <div class="fs-4 fw-bold <?= $summary['is_complete'] ? 'text-success' : 'text-warning' ?> mb-0"><?= $summary['percent'] ?>%</div>
                <div class="text-muted text-uppercase" style="font-size: 0.7rem; font-weight: 600;">Complete</div>
              </div>
            </div>
          </div>

          <!-- Overall Progress Bar -->
          <div class="mt-4 pt-2 border-top">
            <div class="d-flex justify-content-between align-items-center mb-1.5 small">
              <span class="fw-semibold text-dark">Document Submission Progress</span>
              <span class="fw-bold <?= $summary['is_complete'] ? 'text-success' : 'text-primary' ?>"><?= $summary['uploaded'] ?> of <?= $summary['total'] ?> Required Documents Submitted</span>
            </div>
            <div class="progress" style="height: 10px; border-radius: 999px;">
              <div class="progress-bar <?= $summary['is_complete'] ? 'bg-success' : 'bg-primary' ?>" 
                   role="progressbar" 
                   style="width: <?= $summary['percent'] ?>%; transition: width 0.6s ease;" 
                   aria-valuenow="<?= $summary['percent'] ?>" 
                   aria-valuemin="0" 
                   aria-valuemax="100">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Information & Guidelines Alert -->
      <div class="alert border-0 shadow-sm rounded-4 p-3 mb-4 text-dark d-flex align-items-center justify-content-between gap-3 flex-wrap" style="background: linear-gradient(90deg, #eff6ff 0%, #e0f2fe 100%); border-left: 5px solid #0284c7 !important;">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle p-2 d-flex align-items-center justify-content-center text-primary bg-white shadow-sm flex-shrink-0" style="width: 44px; height: 44px;">
            <i class="bi bi-info-circle-fill fs-4 text-primary"></i>
          </div>
          <div>
            <h6 class="fw-bold mb-0.5 text-dark">Submission Instructions / උපදෙස්</h6>
            <p class="small text-secondary mb-0">
              Please upload clear scanned copies or clear photos of all 7 mandatory documents (PDF, JPG, JPEG, PNG, max 25MB each). 
              Your instructor ("Sir") or administrator will review and verify each document to approve your final diploma enrollment.
            </p>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <?php if ($summary['is_complete']): ?>
            <span class="badge bg-success text-white py-2 px-3 rounded-pill fw-semibold shadow-sm">
              <i class="bi bi-check-circle-fill me-1"></i> All 7 Documents Uploaded
            </span>
          <?php else: ?>
            <span class="badge bg-warning text-dark py-2 px-3 rounded-pill fw-semibold shadow-sm">
              <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= $summary['missing'] ?> Document<?= $summary['missing'] > 1 ? 's' : '' ?> Pending
            </span>
          <?php endif; ?>
        </div>
      </div>

      <!-- 7 Document Cards Grid -->
      <div class="row g-4 mb-4">
        <?php foreach ($docTypes as $key => $meta): 
          $doc = $summary['docs'][$key] ?? null;
          $isUploaded = ($doc !== null);
          $status = $doc['status'] ?? 'missing';
          $docId = $doc['id'] ?? null;
        ?>
          <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 border rounded-4 shadow-sm bg-white overflow-hidden d-flex flex-column transition-hover" style="border-color: <?= $isUploaded ? '#e2e8f0' : '#fed7aa' ?> !important;">
              <!-- Card Top Header -->
              <div class="card-body p-4 d-flex flex-column flex-grow-1">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #fff1f2; color: #7A1418;">
                    <i class="bi <?= e($meta['icon']) ?> fs-4"></i>
                  </div>

                  <!-- Status Badge -->
                  <div>
                    <?php if (!$isUploaded): ?>
                      <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1">
                        <i class="bi bi-dash-circle me-1"></i> Not Uploaded
                      </span>
                    <?php elseif ($status === 'verified'): ?>
                      <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold">
                        <i class="bi bi-check-circle-fill me-1"></i> Verified
                      </span>
                    <?php elseif ($status === 'rejected'): ?>
                      <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-bold">
                        <i class="bi bi-x-circle-fill me-1"></i> Rejected
                      </span>
                    <?php else: ?>
                      <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 fw-bold">
                        <i class="bi bi-hourglass-split me-1"></i> Under Review
                      </span>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Document Titles -->
                <h5 class="fw-bold text-dark mb-1"><?= e($meta['title']) ?></h5>
                <div class="text-primary small fw-semibold mb-2" style="font-size: 0.82rem; color: #7A1418 !important;">
                  <?= e($meta['title_si']) ?>
                </div>
                <p class="text-muted small mb-3 flex-grow-1" style="font-size: 0.84rem; line-height: 1.45;">
                  <?= e($meta['description']) ?>
                </p>

                <!-- Document Details if Uploaded -->
                <?php if ($isUploaded): ?>
                  <div class="bg-light p-2.5 rounded-3 border mb-3 small">
                    <div class="d-flex justify-content-between align-items-center text-truncate mb-1">
                      <span class="text-muted"><i class="bi bi-paperclip me-1"></i>File:</span>
                      <span class="fw-semibold text-dark text-truncate ms-2" style="max-width: 170px;" title="<?= e($doc['original_filename']) ?>">
                        <?= e($doc['original_filename']) ?>
                      </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.78rem;">
                      <span>Uploaded:</span>
                      <span><?= date('d M Y, h:i A', strtotime($doc['uploaded_at'])) ?></span>
                    </div>
                    <?php if (!empty($doc['file_size_bytes'])): ?>
                      <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.78rem;">
                        <span>Size:</span>
                        <span><?= round($doc['file_size_bytes'] / 1024, 1) ?> KB</span>
                      </div>
                    <?php endif; ?>

                    <?php if ($status === 'verified'): ?>
                      <div class="text-success mt-1 pt-1 border-top fw-semibold" style="font-size: 0.78rem;">
                        <i class="bi bi-patch-check-fill me-1"></i> Verified by <?= e($doc['verifier_name'] ?? 'Instructor') ?>
                      </div>
                    <?php elseif ($status === 'rejected'): ?>
                      <div class="text-danger mt-1 pt-1 border-top fw-semibold" style="font-size: 0.78rem;">
                        <i class="bi bi-exclamation-octagon-fill me-1"></i> Rejected Note: <?= e($doc['admin_notes'] ?? 'Please re-upload a clearer copy.') ?>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="d-flex align-items-center gap-2 mt-auto pt-2">
                  <?php if ($isUploaded): ?>
                    <button type="button" 
                            class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold flex-fill d-inline-flex align-items-center justify-content-center gap-1.5"
                            onclick="previewDocument(<?= (int)$docId ?>, '<?= e(addslashes($meta['title'])) ?>', '<?= e(addslashes($doc['original_filename'])) ?>')">
                      <i class="bi bi-eye-fill"></i> View
                    </button>
                  <?php endif; ?>

                  <button type="button" 
                          class="btn <?= $isUploaded ? 'btn-outline-secondary' : 'btn-primary' ?> btn-sm rounded-pill px-3 fw-bold flex-fill d-inline-flex align-items-center justify-content-center gap-1.5"
                          <?= !$isUploaded ? 'style="background: #7A1418; border-color: #7A1418;"' : '' ?>
                          onclick="openUploadModal('<?= e($key) ?>', '<?= e(addslashes($meta['title'])) ?>', '<?= e(addslashes($meta['title_si'])) ?>')">
                    <i class="bi bi-cloud-arrow-up-fill"></i> <?= $isUploaded ? 'Replace' : 'Upload' ?>
                  </button>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    </div>
  </main>
</div>

<!-- ================= UPLOAD MODAL ================= -->
<div class="modal fade" id="uploadDocModal" tabindex="-1" aria-labelledby="uploadDocModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <form action="index.php?page=student_upload_document" method="POST" enctype="multipart/form-data">
        <?= Csrf::field() ?>
        <input type="hidden" name="doc_type" id="modalDocType" value="">

        <div class="modal-header border-bottom py-3 px-4 bg-light">
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
              <i class="bi bi-cloud-arrow-up-fill"></i>
            </div>
            <div>
              <h6 class="modal-title fw-bold text-dark mb-0" id="uploadDocModalLabel">Upload Verification Document</h6>
              <small class="text-muted" id="modalDocSubTitle">Select file to submit</small>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-bold text-dark small">Document Name / වර්ගය</label>
            <input type="text" class="form-control form-control-sm bg-light fw-bold" id="modalDocNameDisplay" readonly>
          </div>

          <div class="mb-3">
            <label for="documentFile" class="form-label fw-bold text-dark small">Choose File (PDF, JPG, PNG) <span class="text-danger">*</span></label>
            <input type="file" class="form-control" id="documentFile" name="document_file" accept=".pdf,.jpg,.jpeg,.png" required>
            <div class="form-text small text-muted">
              <i class="bi bi-info-circle me-1"></i> Maximum file size: 25 MB. Clear scanned PDF or photo required.
            </div>
          </div>

          <div class="alert alert-light border rounded-3 p-3 small text-muted mb-0">
            <strong><i class="bi bi-shield-check me-1 text-primary"></i> Note:</strong> Once uploaded, this document will be submitted to the instructor (Sir) for official inspection and verification.
          </div>
        </div>

        <div class="modal-footer bg-light border-top px-4 py-2.5">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold" style="background: #7A1418; border-color: #7A1418;">
            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload &amp; Submit
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= INLINE DOCUMENT PREVIEW MODAL ================= -->
<div class="modal fade" id="previewDocModal" tabindex="-1" aria-labelledby="previewDocModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 900px;">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-bottom py-3 px-4 bg-light">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-file-earmark-text-fill text-primary fs-5"></i>
          <div>
            <h6 class="modal-title fw-bold text-dark mb-0" id="previewDocTitle">Document Preview</h6>
            <small class="text-muted" id="previewDocFilename"></small>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <a href="#" id="previewNewTabBtn" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-box-arrow-up-right me-1"></i> Open in New Tab
          </a>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      <div class="modal-body p-0" style="min-height: 520px; background: #525659;">
        <iframe id="docPreviewFrame" src="" style="width: 100%; height: 600px; border: none;"></iframe>
      </div>
    </div>
  </div>
</div>

<script>
function openUploadModal(typeKey, title, titleSi) {
  document.getElementById('modalDocType').value = typeKey;
  document.getElementById('modalDocNameDisplay').value = title + ' (' + titleSi + ')';
  document.getElementById('modalDocSubTitle').textContent = title;
  document.getElementById('documentFile').value = '';
  
  const modal = new bootstrap.Modal(document.getElementById('uploadDocModal'));
  modal.show();
}

function previewDocument(docId, title, filename) {
  const url = 'index.php?page=view_student_document&id=' + docId;
  document.getElementById('previewDocTitle').textContent = title;
  document.getElementById('previewDocFilename').textContent = filename;
  document.getElementById('previewNewTabBtn').href = url;
  document.getElementById('docPreviewFrame').src = url;

  const modal = new bootstrap.Modal(document.getElementById('previewDocModal'));
  modal.show();
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
