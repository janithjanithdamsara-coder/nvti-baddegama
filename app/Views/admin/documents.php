<?php
/**
 * Admin / Instructor Student Document Verification & Certificate Management View
 * NVTI Baddegama National Diploma Portal
 */

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

$currentRole = Auth::user()['role'] ?? 'admin';
$verifyActionPage = ($currentRole === 'teacher') ? 'teacher_verify_document' : 'admin_verify_document';
$baseDocsPage = ($currentRole === 'teacher') ? 'teacher_documents' : 'admin_documents';
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
                  <i class="bi bi-shield-lock-fill me-1"></i> Verification Officer Portal
                </span>
                <span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1">
                  <?= ($currentRole === 'teacher') ? 'Lecturer / Sir View' : 'Administrator View' ?>
                </span>
              </div>
              <h3 class="fw-bold text-dark mb-1">Student Certificates &amp; Verification Documents</h3>
              <p class="text-muted small mb-0">
                Manage, inspect, and officially verify student enrollment documents (O/L, A/L, GN, Character, School Leaving, Birth &amp; NIC).
              </p>
            </div>

            <!-- Quick Action / Status Summary -->
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-light text-dark border p-2.5 rounded-3 fw-medium">
                <i class="bi bi-mortarboard me-1 text-primary"></i> ICT NVQ Level 5 &bull; Batch 2026
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Metrics KPI Row -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small fw-semibold">Total Students</span>
              <div class="rounded-3 p-2 bg-light text-primary">
                <i class="bi bi-people-fill fs-5"></i>
              </div>
            </div>
            <h3 class="fw-bold text-dark mb-0"><?= $totalStudents ?></h3>
            <small class="text-muted">Enrolled in batch</small>
          </div>
        </div>

        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100" style="border-left: 4px solid #10b981 !important;">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small fw-semibold">Fully Completed (7/7)</span>
              <div class="rounded-3 p-2 bg-success-subtle text-success">
                <i class="bi bi-check-circle-fill fs-5"></i>
              </div>
            </div>
            <h3 class="fw-bold text-success mb-0"><?= $completedStudents ?></h3>
            <small class="text-muted">All 7 docs uploaded</small>
          </div>
        </div>

        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100" style="border-left: 4px solid #f59e0b !important;">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small fw-semibold">Pending Review</span>
              <div class="rounded-3 p-2 bg-warning-subtle text-warning">
                <i class="bi bi-hourglass-split fs-5"></i>
              </div>
            </div>
            <h3 class="fw-bold text-warning mb-0"><?= $pendingReviewCount ?></h3>
            <small class="text-muted">Requires Sir's verification</small>
          </div>
        </div>

        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100" style="border-left: 4px solid #0ea5e9 !important;">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small fw-semibold">All 7 Verified</span>
              <div class="rounded-3 p-2 bg-info-subtle text-info">
                <i class="bi bi-patch-check-fill fs-5"></i>
              </div>
            </div>
            <h3 class="fw-bold text-info mb-0"><?= $verifiedAllCount ?></h3>
            <small class="text-muted">Officially verified</small>
          </div>
        </div>
      </div>

      <!-- Cohort Switcher Tabs & Filter Card -->
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="btn-group p-1 bg-white border rounded-pill shadow-xs" role="group">
          <a href="index.php?page=<?= e($baseDocsPage) ?>&batch_status=active" class="btn btn-sm rounded-pill px-3 fw-bold <?= ($batchStatusFilter === 'active') ? 'btn-danger shadow-xs' : 'btn-light text-secondary' ?>" style="<?= ($batchStatusFilter === 'active') ? 'background: #7A1418; border-color: #7A1418;' : '' ?>">
            <i class="bi bi-broadcast me-1"></i> Active Cohorts
          </a>
          <a href="index.php?page=<?= e($baseDocsPage) ?>&batch_status=archived" class="btn btn-sm rounded-pill px-3 fw-bold <?= ($batchStatusFilter === 'archived') ? 'btn-dark shadow-xs' : 'btn-light text-secondary' ?>">
            <i class="bi bi-archive-fill me-1"></i> Old Batches (Alumni)
          </a>
          <a href="index.php?page=<?= e($baseDocsPage) ?>&batch_status=all" class="btn btn-sm rounded-pill px-3 fw-bold <?= ($batchStatusFilter === 'all') ? 'btn-secondary text-white' : 'btn-light text-secondary' ?>">
            <i class="bi bi-collection me-1"></i> All Records
          </a>
        </div>
      </div>

      <!-- Filter & Search Card -->
      <div class="card border rounded-4 shadow-sm bg-white mb-4">
        <div class="card-body p-3">
          <form action="index.php" method="GET" class="row g-2 align-items-center">
            <input type="hidden" name="page" value="<?= e($baseDocsPage) ?>">
            <input type="hidden" name="batch_status" value="<?= e($batchStatusFilter) ?>">

            <div class="col-12 col-md-4">
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-secondary-subtle"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control border-secondary-subtle" name="search" placeholder="Search by name, MIS, NIC, code..." value="<?= e($search) ?>">
              </div>
            </div>

            <div class="col-6 col-md-3">
              <select name="status" class="form-select form-select-sm border-secondary-subtle" onchange="this.form.submit()">
                <option value="all" <?= ($statusFilter === 'all') ? 'selected' : '' ?>>All Statuses</option>
                <option value="completed" <?= ($statusFilter === 'completed') ? 'selected' : '' ?>>Completed (7/7 Uploaded)</option>
                <option value="pending_review" <?= ($statusFilter === 'pending_review') ? 'selected' : '' ?>>Pending Review</option>
                <option value="verified" <?= ($statusFilter === 'verified') ? 'selected' : '' ?>>All 7 Verified</option>
                <option value="partial" <?= ($statusFilter === 'partial') ? 'selected' : '' ?>>Partially Uploaded (&lt;7)</option>
                <option value="none" <?= ($statusFilter === 'none') ? 'selected' : '' ?>>None Uploaded (0/7)</option>
              </select>
            </div>

            <div class="col-6 col-md-3">
              <select name="class_id" class="form-select form-select-sm border-secondary-subtle" onchange="this.form.submit()">
                <option value="">All Batches (Filtered by Cohort)</option>
                <?php foreach ($classes as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= ($classFilter == $c['id']) ? 'selected' : '' ?>>
                    <?= e($c['class_name']) ?> (<?= ($c['status'] === 'archived') ? 'Archived' : 'Active' ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
              <button type="submit" class="btn btn-primary btn-sm flex-grow-1 fw-semibold" style="background: #7A1418; border-color: #7A1418;">
                Filter
              </button>
              <a href="index.php?page=<?= e($baseDocsPage) ?>" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                <i class="bi bi-arrow-counterclockwise"></i>
              </a>
            </div>
          </form>
        </div>
      </div>

      <!-- Students Table -->
      <div class="card border rounded-4 shadow-sm bg-white overflow-hidden mb-4">
        <div class="card-header bg-light py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-check-fill text-primary"></i>
            <strong class="text-dark">Student Verification Roster (<?= count($students) ?> Students Found)</strong>
          </div>
          <div class="d-flex align-items-center gap-2 small text-muted">
            <span class="d-inline-flex align-items-center gap-1"><span class="badge bg-success p-1 rounded-circle"></span> Verified</span>
            <span class="d-inline-flex align-items-center gap-1 ms-2"><span class="badge bg-warning p-1 rounded-circle"></span> Review</span>
            <span class="d-inline-flex align-items-center gap-1 ms-2"><span class="badge bg-danger p-1 rounded-circle"></span> Rejected</span>
            <span class="d-inline-flex align-items-center gap-1 ms-2"><span class="badge bg-secondary p-1 rounded-circle"></span> Missing</span>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
            <thead class="table-light text-secondary text-uppercase" style="font-size: 0.74rem; letter-spacing: 0.5px;">
              <tr>
                <th style="width: 45px;" class="text-center py-2.5">#</th>
                <th style="width: 250px;" class="py-2.5">Student Details</th>
                <th style="width: 150px;" class="py-2.5">MIS &amp; NIC</th>
                <th style="width: 320px;" class="py-2.5 text-center">Certificate Badges (7 Types)</th>
                <th style="width: 140px;" class="py-2.5 text-center">Status</th>
                <th class="py-2.5 text-end pe-4" style="width: 130px;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($students)): ?>
                <tr>
                  <td colspan="6" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                    No students found matching your criteria.
                  </td>
                </tr>
              <?php else: ?>
                <?php $idx = 1; foreach ($students as $st): 
                  $sId = (int)$st['user_id'];
                  $docs = $st['documents'];
                ?>
                  <tr>
                    <td class="text-center text-muted fw-bold"><?= $idx++ ?></td>
                    <td>
                      <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-primary fw-bold flex-shrink-0" style="width: 36px; height: 36px; font-size: 0.82rem;">
                          <?= strtoupper(substr($st['name'], 0, 1)) ?>
                        </div>
                        <div>
                          <div class="fw-bold text-dark mb-0 text-truncate" style="max-width: 200px;"><?= e($st['name']) ?></div>
                          <small class="text-muted font-monospace"><?= e($st['student_code']) ?></small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-light text-dark border font-monospace"><?= e($st['mis_number'] ?? 'N/A') ?></span>
                      <div class="text-muted" style="font-size: 0.72rem;"><?= e($st['nic'] ?? '') ?></div>
                    </td>
                    <td class="text-center">
                      <div class="d-inline-flex flex-wrap gap-1 justify-content-center" style="max-width: 320px;">
                        <?php 
                        $badgeShortCodes = [
                          'ol_certificate'        => 'O/L',
                          'al_certificate'        => 'A/L',
                          'grama_niladhari'       => 'GN',
                          'character_certificate' => 'Char',
                          'school_leaving'        => 'Leave',
                          'birth_certificate'     => 'Birth',
                          'nic_copy'              => 'NIC'
                        ];

                        foreach ($docTypes as $tKey => $tMeta): 
                          $doc = $docs[$tKey] ?? null;
                          $label = $badgeShortCodes[$tKey] ?? $tKey;
                          
                          if (!$doc) {
                            echo '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size: 0.72rem;" title="' . e($tMeta['title']) . ': Not Uploaded">' . $label . ' <i class="bi bi-dash"></i></span>';
                          } elseif ($doc['status'] === 'verified') {
                            echo '<button type="button" class="btn btn-xs badge bg-success text-white border-0 px-2 py-1" style="font-size: 0.72rem; cursor: pointer;" title="' . e($tMeta['title']) . ': Verified" onclick="openReviewModal(' . (int)$doc['id'] . ', \'' . e(addslashes($st['name'])) . '\', \'' . e(addslashes($tMeta['title'])) . '\', \'' . e(addslashes($doc['original_filename'])) . '\', \'' . e($doc['status']) . '\', \'' . e(addslashes($doc['admin_notes'] ?? '')) . '\')">' . $label . ' <i class="bi bi-check-lg"></i></button>';
                          } elseif ($doc['status'] === 'rejected') {
                            echo '<button type="button" class="btn btn-xs badge bg-danger text-white border-0 px-2 py-1" style="font-size: 0.72rem; cursor: pointer;" title="' . e($tMeta['title']) . ': Rejected" onclick="openReviewModal(' . (int)$doc['id'] . ', \'' . e(addslashes($st['name'])) . '\', \'' . e(addslashes($tMeta['title'])) . '\', \'' . e(addslashes($doc['original_filename'])) . '\', \'' . e($doc['status']) . '\', \'' . e(addslashes($doc['admin_notes'] ?? '')) . '\')">' . $label . ' <i class="bi bi-x-lg"></i></button>';
                          } else {
                            echo '<button type="button" class="btn btn-xs badge bg-warning text-dark border-0 px-2 py-1" style="font-size: 0.72rem; cursor: pointer;" title="' . e($tMeta['title']) . ': Pending Review" onclick="openReviewModal(' . (int)$doc['id'] . ', \'' . e(addslashes($st['name'])) . '\', \'' . e(addslashes($tMeta['title'])) . '\', \'' . e(addslashes($doc['original_filename'])) . '\', \'' . e($doc['status']) . '\', \'' . e(addslashes($doc['admin_notes'] ?? '')) . '\')">' . $label . ' <i class="bi bi-clock"></i></button>';
                          }
                        endforeach; 
                        ?>
                      </div>
                    </td>
                    <td class="text-center">
                      <?php if ($st['status_badge'] === 'verified'): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold">
                          <i class="bi bi-patch-check-fill me-1"></i> Verified
                        </span>
                      <?php elseif ($st['is_complete']): ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-bold">
                          <i class="bi bi-check-circle me-1"></i> Completed (7/7)
                        </span>
                      <?php elseif ($st['uploaded_count'] > 0): ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 fw-semibold">
                          <?= $st['uploaded_count'] ?>/7 Uploaded
                        </span>
                      <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1">
                          0/7 Missing
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end pe-4">
                      <?php if ($st['uploaded_count'] > 0): ?>
                        <button type="button" 
                                class="btn btn-primary btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1"
                                style="background: #7A1418; border-color: #7A1418;"
                                onclick="openStudentDrawer(<?= htmlspecialchars(json_encode($st), ENT_QUOTES, 'UTF-8') ?>)">
                          <i class="bi bi-search"></i> Inspect
                        </button>
                      <?php else: ?>
                        <button type="button" class="btn btn-light btn-sm rounded-pill px-3 text-muted border" disabled>
                          <i class="bi bi-dash"></i> None
                        </button>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="card-footer bg-white p-3 text-muted small border-top d-flex justify-content-between align-items-center">
          <span><i class="bi bi-shield-check text-primary me-1"></i> Official NVTI Baddegama Academic Enrollment Verification</span>
          <span>Click any certificate pill to preview &amp; verify immediately.</span>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- ================= STUDENT FULL INSPECTION DRAWER/MODAL ================= -->
<div class="modal fade" id="studentInspectModal" tabindex="-1" aria-labelledby="studentInspectModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-bottom py-3 px-4 bg-light">
        <div class="d-flex align-items-center gap-2">
          <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
            <i class="bi bi-folder2-open fs-5"></i>
          </div>
          <div>
            <h6 class="modal-title fw-bold text-dark mb-0" id="drawerStudentName">Student Document Folder</h6>
            <small class="text-muted" id="drawerStudentMeta"></small>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <div class="list-group list-group-flush border rounded-3 overflow-hidden" id="drawerDocList">
          <!-- Populated by JavaScript -->
        </div>
      </div>

      <div class="modal-footer bg-light border-top px-4 py-2.5">
        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- ================= DOCUMENT REVIEW & VERIFICATION MODAL ================= -->
<div class="modal fade" id="reviewDocModal" tabindex="-1" aria-labelledby="reviewDocModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-fullscreen-sm-down modal-dialog-centered" style="max-width: 950px;">
    <div class="modal-content border-0 rounded-4 shadow overflow-hidden">
      <div class="modal-header border-bottom py-2.5 px-3 px-md-4 bg-light flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2 min-w-0 flex-grow-1">
          <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
            <i class="bi bi-shield-check fs-5"></i>
          </div>
          <div class="min-w-0">
            <h6 class="modal-title fw-bold text-dark mb-0 text-truncate" id="reviewDocModalTitle">Inspect Document</h6>
            <small class="text-muted d-block text-truncate" id="reviewDocModalSub"></small>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-auto">
          <a href="#" id="reviewDocNewTabBtn" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-box-arrow-up-right me-1"></i> Open Full View
          </a>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>

      <div class="modal-body p-0" style="height: clamp(260px, 55vh, 520px); background: #525659; overflow: hidden;">
        <iframe id="reviewDocFrame" src="" style="width: 100%; height: 100%; border: none; display: block;"></iframe>
      </div>

      <!-- Action Verification Form Footer -->
      <div class="modal-footer bg-light border-top p-3">
        <form action="index.php?page=<?= e($verifyActionPage) ?>" method="POST" id="verifyDocForm" class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 w-100 m-0">
          <?= Csrf::field() ?>
          <input type="hidden" name="document_id" id="verifyFormDocId" value="">
          <input type="hidden" name="return_url" value="<?= e($_SERVER['REQUEST_URI'] ?? 'index.php?page=' . $baseDocsPage) ?>">

          <div class="input-group input-group-sm flex-grow-1">
            <span class="input-group-text bg-white border-secondary-subtle"><i class="bi bi-chat-left-dots"></i></span>
            <input type="text" class="form-control border-secondary-subtle" name="admin_notes" id="verifyFormNotes" placeholder="Reason / note (e.g. Approved or specify issue if rejecting)...">
          </div>

          <div class="d-flex align-items-center justify-content-end gap-2 flex-shrink-0">
            <button type="submit" name="status" value="rejected" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold flex-fill flex-md-grow-0">
              <i class="bi bi-x-circle-fill me-1"></i> Reject
            </button>
            <button type="submit" name="status" value="verified" class="btn btn-success btn-sm rounded-pill px-4 fw-bold shadow-sm flex-fill flex-md-grow-0">
              <i class="bi bi-check-circle-fill me-1"></i> Approve &amp; Verify
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
const docTypesMeta = <?= json_encode($docTypes) ?>;

function openReviewModal(docId, studentName, docTitle, filename, currentStatus, currentNotes) {
  const url = 'index.php?page=view_student_document&id=' + docId;
  document.getElementById('reviewDocModalTitle').textContent = docTitle + ' \u2022 ' + studentName;
  document.getElementById('reviewDocModalSub').textContent = 'File: ' + filename + ' (Current Status: ' + currentStatus.toUpperCase() + ')';
  document.getElementById('reviewDocNewTabBtn').href = url;
  document.getElementById('reviewDocFrame').src = url;
  
  document.getElementById('verifyFormDocId').value = docId;
  document.getElementById('verifyFormNotes').value = currentNotes || '';

  const modal = new bootstrap.Modal(document.getElementById('reviewDocModal'));
  modal.show();
}

function openStudentDrawer(student) {
  document.getElementById('drawerStudentName').textContent = student.name + ' (' + student.student_code + ')';
  document.getElementById('drawerStudentMeta').textContent = (student.mis_number || 'No MIS') + ' • ' + (student.nic || 'No NIC') + ' • ' + (student.class_name || 'NVTI Baddegama');

  const listEl = document.getElementById('drawerDocList');
  listEl.innerHTML = '';

  for (const [key, meta] of Object.entries(docTypesMeta)) {
    const doc = student.documents[key] || null;
    const item = document.createElement('div');
    item.className = 'list-group-item p-3 d-flex justify-content-between align-items-center';

    let statusHtml = '';
    let actionHtml = '';

    if (!doc) {
      statusHtml = '<span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1">Not Uploaded</span>';
      actionHtml = '<span class="text-muted small">Pending student upload</span>';
    } else {
      if (doc.status === 'verified') {
        statusHtml = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Verified</span>';
      } else if (doc.status === 'rejected') {
        statusHtml = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-bold"><i class="bi bi-x-circle-fill me-1"></i> Rejected</span>';
      } else {
        statusHtml = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 fw-bold"><i class="bi bi-clock me-1"></i> Pending Review</span>';
      }

      actionHtml = `<button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold" onclick="openReviewModal(${doc.id}, '${escapeJs(student.name)}', '${escapeJs(meta.title)}', '${escapeJs(doc.original_filename)}', '${doc.status}', '${escapeJs(doc.admin_notes || '')}')">
                      <i class="bi bi-eye-fill me-1"></i> Inspect &amp; Verify
                    </button>`;
    }

    item.innerHTML = `
      <div class="d-flex align-items-center gap-3">
        <div class="rounded-3 p-2 d-flex align-items-center justify-content-center text-primary bg-light" style="width: 40px; height: 40px;">
          <i class="bi ${meta.icon} fs-5"></i>
        </div>
        <div>
          <div class="fw-bold text-dark mb-0">${meta.title}</div>
          <div class="text-muted small" style="font-size: 0.78rem;">${meta.title_si}</div>
          ${doc && doc.admin_notes ? '<div class="text-danger small mt-0.5"><i class="bi bi-info-circle me-1"></i>Note: ' + escapeJs(doc.admin_notes) + '</div>' : ''}
        </div>
      </div>
      <div class="d-flex align-items-center gap-3">
        ${statusHtml}
        ${actionHtml}
      </div>
    `;

    listEl.appendChild(item);
  }

  const modal = new bootstrap.Modal(document.getElementById('studentInspectModal'));
  modal.show();
}

function escapeJs(str) {
  if (!str) return '';
  return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
