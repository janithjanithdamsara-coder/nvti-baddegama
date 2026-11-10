<?php
/**
 * Candidate Online Applications & Admissions Hub
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
      <div class="card border-0 shadow-sm rounded-4 mb-4 text-white overflow-hidden position-relative" style="background: linear-gradient(135deg, #7A1418 0%, #991b1b 100%); padding: 1.85rem 2.25rem;">
        <div class="position-absolute end-0 top-50 translate-middle-y me-3 pointer-events-none d-none d-xl-block" style="font-size: 7rem; line-height: 0; opacity: 0.06 !important;">
          <i class="bi bi-person-lines-fill text-white"></i>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 position-relative" style="z-index: 5;">
          <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
              <span class="badge rounded-pill font-monospace px-3 py-1.5 fw-semibold shadow-xs" style="background: rgba(255, 255, 255, 0.22) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.38); font-size: 0.74rem;">
                <i class="bi bi-cloud-arrow-down-fill me-1"></i> nvtibaddegama.site API
              </span>
              <span class="badge rounded-pill px-3 py-1.5 fw-semibold shadow-xs" style="background: rgba(255, 255, 255, 0.22) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.38); font-size: 0.74rem;">
                <i class="bi bi-mortarboard-fill me-1"></i> NVQ Level 5 Intake
              </span>
            </div>
            <h3 class="fw-bold mb-1.5 text-white" style="letter-spacing: -0.3px;">Candidate Online Applications Hub</h3>
            <p class="mb-0 text-white opacity-85 small" style="max-width: 680px;">
              Review admission applications submitted via <strong>nvtibaddegama.site</strong>, inspect O/L &amp; A/L qualifications, shortlist for interviews, and 1-click enroll students into Batch 2026.
            </p>
          </div>

          <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0 align-self-start align-self-md-center">
            <!-- Sync Button -->
            <form action="index.php?page=admin_sync_applications" method="POST" class="d-inline">
              <?= Csrf::field() ?>
              <button type="submit" class="btn btn-warning rounded-pill px-3.5 py-2 fw-bold text-dark d-inline-flex align-items-center gap-2 shadow-sm" title="Fetch fresh applications from nvtibaddegama.site API">
                <i class="bi bi-arrow-repeat fs-6"></i>
                <span>Sync from NVTI Website</span>
              </button>
            </form>

            <!-- Export CSV -->
            <a href="index.php?page=admin_export_applications_csv" class="btn btn-light rounded-pill px-3 py-2 fw-semibold text-dark shadow-sm d-inline-flex align-items-center gap-1.5">
              <i class="bi bi-file-earmark-spreadsheet text-success"></i>
              <span>Export CSV</span>
            </a>

            <!-- Manual Add Modal Button -->
            <button class="btn btn-outline-light rounded-pill px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-xs" data-bs-toggle="modal" data-bs-target="#newApplicationModal">
              <i class="bi bi-plus-circle-fill fs-6"></i>
              <span>+ Paper Form</span>
            </button>
          </div>
        </div>
      </div>

      <!-- KPI Summary Metrics -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-lg-2-4 col-md-4">
          <div class="card border rounded-4 bg-white shadow-sm p-3 h-100">
            <div class="d-flex align-items-center gap-2.5 mb-1">
              <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                <i class="bi bi-inbox-fill fs-6"></i>
              </div>
              <small class="text-muted fw-bold text-uppercase" style="font-size: 0.68rem;">Total Received</small>
            </div>
            <div class="fs-4 fw-bold text-dark mb-0"><?= $totalApps ?></div>
          </div>
        </div>

        <div class="col-6 col-lg-2-4 col-md-4">
          <div class="card border rounded-4 bg-white shadow-sm p-3 h-100">
            <div class="d-flex align-items-center gap-2.5 mb-1">
              <div class="rounded-circle p-2 bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                <i class="bi bi-laptop-fill fs-6"></i>
              </div>
              <small class="text-muted fw-bold text-uppercase" style="font-size: 0.68rem;">ICT Diploma (L5)</small>
            </div>
            <div class="fs-4 fw-bold text-info mb-0"><?= $ictApps ?></div>
          </div>
        </div>

        <div class="col-6 col-lg-2-4 col-md-4">
          <div class="card border rounded-4 bg-white shadow-sm p-3 h-100">
            <div class="d-flex align-items-center gap-2.5 mb-1">
              <div class="rounded-circle p-2 bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                <i class="bi bi-clock-history fs-6"></i>
              </div>
              <small class="text-muted fw-bold text-uppercase" style="font-size: 0.68rem;">Pending Review</small>
            </div>
            <div class="fs-4 fw-bold text-warning mb-0"><?= $pendingApps ?></div>
          </div>
        </div>

        <div class="col-6 col-lg-2-4 col-md-4">
          <div class="card border rounded-4 bg-white shadow-sm p-3 h-100">
            <div class="d-flex align-items-center gap-2.5 mb-1">
              <div class="rounded-circle p-2 text-primary d-flex align-items-center justify-content-center" style="background: #eff6ff; width: 36px; height: 36px;">
                <i class="bi bi-person-check-fill fs-6"></i>
              </div>
              <small class="text-muted fw-bold text-uppercase" style="font-size: 0.68rem;">Interview / Selected</small>
            </div>
            <div class="fs-4 fw-bold text-primary mb-0"><?= ($interviewApps + $selectedApps) ?></div>
          </div>
        </div>

        <div class="col-12 col-lg-2-4 col-md-4">
          <div class="card border rounded-4 bg-white shadow-sm p-3 h-100" style="border-left: 4px solid #10b981 !important;">
            <div class="d-flex align-items-center gap-2.5 mb-1">
              <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                <i class="bi bi-patch-check-fill fs-6"></i>
              </div>
              <small class="text-success fw-bold text-uppercase" style="font-size: 0.68rem;">Enrolled in Batch</small>
            </div>
            <div class="fs-4 fw-bold text-success mb-0"><?= $enrolledApps ?></div>
          </div>
        </div>
      </div>

      <!-- Search & Filters Bar -->
      <div class="card border rounded-4 shadow-sm bg-white p-3 mb-4">
        <form action="index.php" method="GET" class="row g-2.5 align-items-center">
          <input type="hidden" name="page" value="admin_applications">

          <!-- Search Input -->
          <div class="col-12 col-md-4 col-xl-3">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light border-secondary-subtle"><i class="bi bi-search text-muted"></i></span>
              <input type="text" name="search" class="form-control border-secondary-subtle" placeholder="Search Candidate, NIC, ID..." value="<?= e($search) ?>">
            </div>
          </div>

          <!-- Status Filter -->
          <div class="col-6 col-md-3 col-xl-2">
            <select name="status" class="form-select form-select-sm border-secondary-subtle">
              <option value="all" <?= ($statusFilter === 'all') ? 'selected' : '' ?>>All Statuses</option>
              <option value="pending" <?= ($statusFilter === 'pending') ? 'selected' : '' ?>>Pending Review</option>
              <option value="called_for_interview" <?= ($statusFilter === 'called_for_interview') ? 'selected' : '' ?>>Called for Interview</option>
              <option value="selected" <?= ($statusFilter === 'selected') ? 'selected' : '' ?>>Selected</option>
              <option value="enrolled" <?= ($statusFilter === 'enrolled') ? 'selected' : '' ?>>Enrolled to Batch</option>
              <option value="rejected" <?= ($statusFilter === 'rejected') ? 'selected' : '' ?>>Rejected</option>
            </select>
          </div>

          <!-- A/L Stream Filter -->
          <div class="col-6 col-md-3 col-xl-2">
            <select name="al" class="form-select form-select-sm border-secondary-subtle">
              <option value="all" <?= ($alFilter === 'all') ? 'selected' : '' ?>>All A/L Streams</option>
              <option value="tech" <?= ($alFilter === 'tech') ? 'selected' : '' ?>>A/L Technology</option>
              <option value="science" <?= ($alFilter === 'science') ? 'selected' : '' ?>>A/L Science</option>
              <option value="commerce" <?= ($alFilter === 'commerce') ? 'selected' : '' ?>>A/L Commerce</option>
              <option value="arts" <?= ($alFilter === 'arts') ? 'selected' : '' ?>>A/L Arts</option>
            </select>
          </div>

          <!-- O/L Pass Filter -->
          <div class="col-6 col-md-2 col-xl-2">
            <select name="ol" class="form-select form-select-sm border-secondary-subtle">
              <option value="all" <?= ($olFilter === 'all') ? 'selected' : '' ?>>All O/L Status</option>
              <option value="passed" <?= ($olFilter === 'passed') ? 'selected' : '' ?>>O/L Passed</option>
              <option value="failed" <?= ($olFilter === 'failed') ? 'selected' : '' ?>>O/L Failed</option>
            </select>
          </div>

          <!-- Submit & Reset -->
          <div class="col-6 col-md-2 col-xl-3 d-flex gap-1.5 justify-content-end">
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold" style="background: #7A1418; border-color: #7A1418;">
              <i class="bi bi-funnel me-1"></i> Filter
            </button>
            <?php if (!empty($search) || $statusFilter !== 'all' || $alFilter !== 'all' || $olFilter !== 'all'): ?>
              <a href="index.php?page=admin_applications" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5" title="Reset Filters">
                <i class="bi bi-x-circle"></i>
              </a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <!-- Candidate Applications Table -->
      <div class="card border rounded-4 shadow-sm bg-white overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-people-fill text-primary"></i>
            <strong class="text-dark">Applicant Candidates (<?= count($applications) ?> Records)</strong>
          </div>
          <span class="badge bg-light text-secondary border px-2.5 py-1 font-monospace">
            Active Batch: <?= e($batches[0]['class_name'] ?? 'NVTI Baddegama ICT L5 - Batch 2026') ?>
          </span>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
            <thead class="table-light text-secondary text-uppercase" style="font-size: 0.74rem; letter-spacing: 0.5px;">
              <tr>
                <th style="width: 45px;" class="text-center py-2.5">#</th>
                <th style="width: 280px;" class="py-2.5">Candidate Details</th>
                <th style="width: 140px;" class="py-2.5">NIC &amp; Age</th>
                <th style="width: 180px;" class="py-2.5">Contact &amp; WhatsApp</th>
                <th style="width: 190px;" class="py-2.5">Qualifications (O/L &amp; A/L)</th>
                <th style="width: 220px;" class="py-2.5">Course Preferences</th>
                <th style="width: 130px;" class="text-center py-2.5">Status</th>
                <th class="py-2.5 text-end pe-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($applications)): ?>
                <tr>
                  <td colspan="8" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                    No candidate applications match the selected filter criteria.<br>
                    <span class="small">Click <strong>"Sync from NVTI Website"</strong> to fetch online submissions or <strong>"+ Paper Form"</strong> to add an offline candidate.</span>
                  </td>
                </tr>
              <?php else: ?>
                <?php $idx = 1; foreach ($applications as $app): 
                  $appId = (int)$app['id'];
                  $status = $app['status'];
                  $cleanPhone = preg_replace('/[^0-9]/', '', $app['whatsapp_no'] ?: $app['contact_no']);
                  if (str_starts_with($cleanPhone, '0')) {
                    $waNumber = '94' . substr($cleanPhone, 1);
                  } else {
                    $waNumber = $cleanPhone;
                  }
                ?>
                  <tr>
                    <td class="text-center text-muted fw-bold"><?= $idx++ ?></td>

                    <!-- Candidate Name & Student ID -->
                    <td>
                      <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0 shadow-xs" 
                             style="width: 40px; height: 40px; font-size: 0.95rem; background: linear-gradient(135deg, #7A1418, #991b1b);">
                          <?= strtoupper(substr($app['full_name'], 0, 1)) ?>
                        </div>
                        <div class="min-w-0">
                          <div class="fw-bold text-dark mb-0 text-truncate" title="<?= e($app['full_name']) ?>" style="max-width: 220px;">
                            <?= e($app['full_name']) ?>
                          </div>
                          <div class="text-muted small font-monospace d-flex align-items-center gap-1.5" style="font-size: 0.74rem;">
                            <span class="badge bg-light text-dark border font-monospace"><?= e($app['student_id']) ?></span>
                            <span>&bull; <?= date('M d, Y', strtotime($app['application_date'])) ?></span>
                          </div>
                        </div>
                      </div>
                    </td>

                    <!-- NIC & Age -->
                    <td>
                      <span class="badge bg-light text-dark border font-monospace px-2 py-1"><?= e($app['nic']) ?></span>
                      <?php if (!empty($app['dob'])): ?>
                        <?php 
                          $age = (new DateTime($app['dob']))->diff(new DateTime())->y;
                        ?>
                        <div class="text-muted small mt-0.5" style="font-size: 0.72rem;">
                          <?= date('Y-m-d', strtotime($app['dob'])) ?> (<?= $age ?> yrs)
                        </div>
                      <?php endif; ?>
                    </td>

                    <!-- Contact & WhatsApp -->
                    <td>
                      <div class="d-flex align-items-center gap-1 text-dark small fw-medium">
                        <i class="bi bi-telephone text-secondary me-0.5"></i>
                        <a href="tel:<?= e($app['contact_no']) ?>" class="text-dark text-decoration-none font-monospace"><?= e($app['contact_no']) ?></a>
                      </div>
                      <?php if (!empty($app['whatsapp_no'])): ?>
                        <div class="mt-1">
                          <a href="https://wa.me/<?= $waNumber ?>?text=<?= urlencode("Hello " . $app['full_name'] . ", regarding your NVTI Baddegama ICT NVQ Level 5 Diploma application:") ?>" target="_blank" rel="noopener noreferrer" class="badge rounded-pill text-white px-2 py-1 text-decoration-none d-inline-flex align-items-center gap-1" style="background: #25D366; font-size: 0.7rem;">
                            <i class="bi bi-whatsapp"></i>
                            <span><?= e($app['whatsapp_no']) ?></span>
                          </a>
                        </div>
                      <?php endif; ?>
                    </td>

                    <!-- Qualifications: O/L & A/L -->
                    <td>
                      <div class="d-flex align-items-center gap-1.5 mb-1">
                        <?php if ($app['ol_pass_status'] === 'Yes'): ?>
                          <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size: 0.68rem;">O/L Passed</span>
                        <?php else: ?>
                          <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill" style="font-size: 0.68rem;">O/L Failed</span>
                        <?php endif; ?>

                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill text-capitalize" style="font-size: 0.68rem;">
                          A/L <?= e($app['al_category']) ?>
                        </span>
                      </div>
                      <div class="text-muted small font-monospace" style="font-size: 0.72rem;">
                        Eng: <strong><?= e($app['ol_english_grade'] ?: '-') ?></strong> &bull; 
                        Math: <strong><?= e($app['ol_maths_grade'] ?: '-') ?></strong> &bull; 
                        Sci: <strong><?= e($app['ol_science_grade'] ?: '-') ?></strong>
                      </div>
                    </td>

                    <!-- Course Preferences -->
                    <td>
                      <div class="small fw-semibold text-dark text-truncate" style="max-width: 210px;" title="<?= e($app['course_option_one']) ?>">
                        1. <?= e($app['course_option_one']) ?>
                      </div>
                      <?php if (!empty($app['course_option_two'])): ?>
                        <div class="text-muted small text-truncate" style="max-width: 210px; font-size: 0.74rem;" title="<?= e($app['course_option_two']) ?>">
                          2. <?= e($app['course_option_two']) ?>
                        </div>
                      <?php endif; ?>
                    </td>

                    <!-- Status Badge -->
                    <td class="text-center">
                      <?php if ($status === 'enrolled'): ?>
                        <span class="badge bg-success text-white rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                          <i class="bi bi-check-all me-1"></i> Enrolled
                        </span>
                      <?php elseif ($status === 'selected'): ?>
                        <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                          <i class="bi bi-star-fill me-1"></i> Selected
                        </span>
                      <?php elseif ($status === 'called_for_interview'): ?>
                        <span class="badge bg-info text-white rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.7rem;">
                          <i class="bi bi-calendar-event me-1"></i> Interview
                        </span>
                      <?php elseif ($status === 'rejected'): ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">
                          Rejected
                        </span>
                      <?php else: ?>
                        <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.7rem;">
                          Pending Review
                        </span>
                      <?php endif; ?>
                    </td>

                    <!-- Actions -->
                    <td class="text-end pe-3">
                      <div class="d-flex align-items-center justify-content-end gap-1.5">
                        <!-- Inspect Modal -->
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 32px; height: 32px;" title="View Candidate Dossier" onclick='openDossierModal(<?= json_encode($app) ?>)'>
                          <i class="bi bi-eye"></i>
                        </button>

                        <?php if ($status !== 'enrolled'): ?>
                          <!-- 1-Click Enroll Modal Button -->
                          <button type="button" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 fw-bold d-inline-flex align-items-center gap-1 shadow-xs" style="font-size: 0.74rem;" onclick='openEnrollModal(<?= json_encode($app) ?>)' title="Convert applicant into active LMS student">
                            <i class="bi bi-person-plus-fill"></i> Enroll
                          </button>
                        <?php else: ?>
                          <span class="badge bg-light text-muted border rounded-pill px-2 py-1" style="font-size: 0.68rem;" title="Enrolled to LMS">
                            User #<?= e($app['enrolled_user_id'] ?? '') ?>
                          </span>
                        <?php endif; ?>

                        <!-- Quick Status Dropdown -->
                        <div class="dropdown">
                          <button class="btn btn-sm btn-light border rounded-circle d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 30px; height: 30px;" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                          </button>
                          <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 py-1 small">
                            <li><h6 class="dropdown-header small text-uppercase" style="font-size: 0.68rem;">Update Status</h6></li>
                            <li>
                              <form action="index.php?page=admin_application_status" method="POST">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="application_id" value="<?= $appId ?>">
                                <input type="hidden" name="status" value="called_for_interview">
                                <button type="submit" class="dropdown-item py-1.5"><i class="bi bi-calendar-check text-info me-2"></i> Call for Interview</button>
                              </form>
                            </li>
                            <li>
                              <form action="index.php?page=admin_application_status" method="POST">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="application_id" value="<?= $appId ?>">
                                <input type="hidden" name="status" value="selected">
                                <button type="submit" class="dropdown-item py-1.5"><i class="bi bi-star-fill text-primary me-2"></i> Mark as Selected</button>
                              </form>
                            </li>
                            <li>
                              <form action="index.php?page=admin_application_status" method="POST">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="application_id" value="<?= $appId ?>">
                                <input type="hidden" name="status" value="pending">
                                <button type="submit" class="dropdown-item py-1.5"><i class="bi bi-arrow-counterclockwise text-secondary me-2"></i> Mark as Pending</button>
                              </form>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                              <form action="index.php?page=admin_application_status" method="POST" onsubmit="return confirm('Reject this application?');">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="application_id" value="<?= $appId ?>">
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="dropdown-item py-1.5 text-danger"><i class="bi bi-x-circle text-danger me-2"></i> Mark as Rejected</button>
                              </form>
                            </li>
                          </ul>
                        </div>
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

<!-- ================= MODAL 1: VIEW CANDIDATE DOSSIER ================= -->
<div class="modal fade" id="dossierModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 rounded-4 shadow overflow-hidden">
      <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #7A1418 0%, #991b1b 100%);">
        <div>
          <span class="badge bg-white text-dark rounded-pill px-2.5 py-0.5 font-monospace fw-bold small">CANDIDATE ADMISSION RECORD</span>
          <h5 class="modal-title fw-bold text-white mb-0" id="dossierName">Candidate Profile</h5>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light">
        <div class="row g-3">
          <!-- Personal Info -->
          <div class="col-12 col-md-6">
            <div class="card border rounded-4 bg-white p-3 h-100 shadow-xs">
              <h6 class="fw-bold text-dark border-bottom pb-2 mb-2"><i class="bi bi-person-badge text-primary me-1"></i> Personal Information</h6>
              <div class="small mb-1.5"><strong class="text-muted">Student Reference ID:</strong> <span id="dossierRef" class="font-monospace fw-bold text-dark"></span></div>
              <div class="small mb-1.5"><strong class="text-muted">NIC Number:</strong> <span id="dossierNic" class="font-monospace fw-bold text-dark"></span></div>
              <div class="small mb-1.5"><strong class="text-muted">Date of Birth:</strong> <span id="dossierDob" class="text-dark"></span></div>
              <div class="small mb-1.5"><strong class="text-muted">Permanent Address:</strong> <div id="dossierAddress" class="text-secondary mt-0.5"></div></div>
            </div>
          </div>

          <!-- Contact Details -->
          <div class="col-12 col-md-6">
            <div class="card border rounded-4 bg-white p-3 h-100 shadow-xs">
              <h6 class="fw-bold text-dark border-bottom pb-2 mb-2"><i class="bi bi-telephone-fill text-success me-1"></i> Contact Channels</h6>
              <div class="small mb-2"><strong class="text-muted">Calling Phone:</strong> <span id="dossierPhone" class="font-monospace text-dark"></span></div>
              <div class="small mb-3"><strong class="text-muted">WhatsApp Number:</strong> <span id="dossierWa" class="font-monospace text-dark"></span></div>
              <div class="pt-2 border-top">
                <a href="#" id="dossierWaBtn" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1.5 w-100 justify-content-center">
                  <i class="bi bi-whatsapp"></i> Chat with Candidate on WhatsApp
                </a>
              </div>
            </div>
          </div>

          <!-- Academic Qualifications -->
          <div class="col-12">
            <div class="card border rounded-4 bg-white p-3 shadow-xs">
              <h6 class="fw-bold text-dark border-bottom pb-2 mb-2"><i class="bi bi-mortarboard-fill text-warning me-1"></i> Educational Qualifications</h6>
              <div class="row g-2 text-center mb-3">
                <div class="col-3">
                  <div class="bg-light p-2 rounded-3 border">
                    <small class="text-muted d-block" style="font-size: 0.72rem;">G.C.E. O/L</small>
                    <strong id="dossierOlPass" class="text-dark"></strong>
                  </div>
                </div>
                <div class="col-3">
                  <div class="bg-light p-2 rounded-3 border">
                    <small class="text-muted d-block" style="font-size: 0.72rem;">English</small>
                    <strong id="dossierEng" class="text-primary fs-6"></strong>
                  </div>
                </div>
                <div class="col-3">
                  <div class="bg-light p-2 rounded-3 border">
                    <small class="text-muted d-block" style="font-size: 0.72rem;">Mathematics</small>
                    <strong id="dossierMath" class="text-primary fs-6"></strong>
                  </div>
                </div>
                <div class="col-3">
                  <div class="bg-light p-2 rounded-3 border">
                    <small class="text-muted d-block" style="font-size: 0.72rem;">Science</small>
                    <strong id="dossierSci" class="text-primary fs-6"></strong>
                  </div>
                </div>
              </div>

              <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border">
                <span class="small fw-bold text-dark">G.C.E. A/L Stream:</span>
                <span id="dossierAlStream" class="badge bg-primary text-white rounded-pill px-3 py-1 fw-bold text-uppercase"></span>
              </div>
            </div>
          </div>

          <!-- Course Choices -->
          <div class="col-12">
            <div class="card border rounded-4 bg-white p-3 shadow-xs">
              <h6 class="fw-bold text-dark border-bottom pb-2 mb-2"><i class="bi bi-journal-bookmark text-primary me-1"></i> Course Selections at NVTI</h6>
              <div class="small mb-1"><strong class="text-dark">1st Choice:</strong> <span id="dossierOpt1" class="text-primary fw-bold"></span></div>
              <div class="small"><strong class="text-dark">2nd Choice:</strong> <span id="dossierOpt2" class="text-secondary"></span></div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top px-4 py-2.5">
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- ================= MODAL 2: 1-CLICK ENROLL CANDIDATE ================= -->
<div class="modal fade" id="enrollModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow overflow-hidden">
      <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
        <div>
          <div class="badge bg-white text-success rounded-pill px-2.5 py-0.5 fw-bold font-monospace small mb-1">OFFICIAL DIPLOMA ADMISSION</div>
          <h5 class="modal-title fw-bold text-white mb-0">Enroll Candidate to Active Batch</h5>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="index.php?page=admin_enroll_applicant" method="POST">
        <?= Csrf::field() ?>
        <input type="hidden" name="application_id" id="enrollAppId" value="">

        <div class="modal-body p-4 bg-light">
          <div class="card border rounded-4 bg-white p-3.5 mb-3 shadow-xs">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-xs flex-shrink-0" style="width: 46px; height: 46px; background: #059669; font-size: 1.15rem;">
                <i class="bi bi-person-check-fill"></i>
              </div>
              <div>
                <h6 class="fw-bold text-dark mb-0.5" id="enrollCandidateName"></h6>
                <div class="text-muted small font-monospace" id="enrollCandidateNic"></div>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Enroll into Academic Batch:</label>
            <select name="class_id" class="form-select border-secondary-subtle rounded-3 fw-semibold">
              <?php foreach ($batches as $b): ?>
                <option value="<?= (int)$b['id'] ?>"><?= e($b['class_name']) ?> (<?= e($b['academic_year']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="alert alert-success border-0 rounded-4 small p-3 mb-0" style="background: #ecfdf5; border-left: 4px solid #10b981 !important;">
            <div class="fw-bold text-dark mb-1"><i class="bi bi-magic me-1 text-success"></i> Automated Account Setup:</div>
            <ul class="mb-0 ps-3 text-secondary">
              <li>Candidate will be registered in LMS with <strong>Student Role</strong>.</li>
              <li>A unique institutional email (<span class="font-monospace">name.XXXX@nvti.edu.lk</span>) will be created.</li>
              <li>Initial secure login password will be generated and candidate status set to <strong>Active</strong>.</li>
            </ul>
          </div>
        </div>

        <div class="modal-footer bg-white border-top px-4 py-2.5">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-check-circle-fill me-1"></i> Confirm &amp; Enroll Student
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= MODAL 3: MANUAL PAPER APPLICATION ENTRY ================= -->
<div class="modal fade" id="newApplicationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 rounded-4 shadow overflow-hidden">
      <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #7A1418 0%, #991b1b 100%);">
        <div>
          <span class="badge bg-white text-dark rounded-pill px-2.5 py-0.5 font-monospace fw-bold small">OFFLINE / WALK-IN INTAKE</span>
          <h5 class="modal-title fw-bold text-white mb-0">Add Paper Application</h5>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="index.php?page=admin_save_application" method="POST">
        <?= Csrf::field() ?>
        <div class="modal-body p-4 bg-light">
          <div class="row g-3">
            <!-- Full Name -->
            <div class="col-12 col-md-8">
              <label class="form-label small fw-bold text-dark">Candidate Full Name *</label>
              <input type="text" name="full_name" class="form-control rounded-3" placeholder="e.g. Kavindu Chamod Wickramasinghe" required>
            </div>

            <!-- NIC -->
            <div class="col-12 col-md-4">
              <label class="form-label small fw-bold text-dark">NIC Number *</label>
              <input type="text" name="nic" class="form-control rounded-3 font-monospace" placeholder="e.g. 200418902341" required>
            </div>

            <!-- Calling Phone -->
            <div class="col-12 col-md-6">
              <label class="form-label small fw-bold text-dark">Contact Calling Phone *</label>
              <input type="text" name="contact_no" class="form-control rounded-3 font-monospace" placeholder="e.g. 0771234567" required>
            </div>

            <!-- WhatsApp Phone -->
            <div class="col-12 col-md-6">
              <label class="form-label small fw-bold text-dark">WhatsApp Number</label>
              <input type="text" name="whatsapp_no" class="form-control rounded-3 font-monospace" placeholder="e.g. 0771234567">
            </div>

            <!-- DOB -->
            <div class="col-12 col-md-6">
              <label class="form-label small fw-bold text-dark">Date of Birth</label>
              <input type="date" name="dob" class="form-control rounded-3">
            </div>

            <!-- Permanent Address -->
            <div class="col-12 col-md-6">
              <label class="form-label small fw-bold text-dark">Permanent Address</label>
              <input type="text" name="address" class="form-control rounded-3" placeholder="e.g. No. 45, Temple Road, Baddegama">
            </div>

            <!-- O/L Pass & Grades -->
            <div class="col-12">
              <div class="card border rounded-4 bg-white p-3 shadow-xs">
                <label class="form-label small fw-bold text-dark mb-2">G.C.E. O/L Results</label>
                <div class="row g-2">
                  <div class="col-6 col-md-3">
                    <label class="small text-muted">O/L Status</label>
                    <select name="ol_pass_status" class="form-select form-select-sm rounded-3">
                      <option value="Yes" selected>Passed</option>
                      <option value="No">Failed</option>
                    </select>
                  </div>
                  <div class="col-6 col-md-3">
                    <label class="small text-muted">English Grade</label>
                    <input type="text" name="ol_english_grade" class="form-control form-control-sm rounded-3" placeholder="A, B, C, S">
                  </div>
                  <div class="col-6 col-md-3">
                    <label class="small text-muted">Maths Grade</label>
                    <input type="text" name="ol_maths_grade" class="form-control form-control-sm rounded-3" placeholder="A, B, C, S">
                  </div>
                  <div class="col-6 col-md-3">
                    <label class="small text-muted">Science Grade</label>
                    <input type="text" name="ol_science_grade" class="form-control form-control-sm rounded-3" placeholder="A, B, C, S">
                  </div>
                </div>
              </div>
            </div>

            <!-- A/L Stream -->
            <div class="col-12 col-md-6">
              <label class="form-label small fw-bold text-dark">G.C.E. A/L Stream *</label>
              <select name="al_category" class="form-select rounded-3">
                <option value="tech" selected>Technology</option>
                <option value="science">Physical / Bio Science</option>
                <option value="commerce">Commerce</option>
                <option value="arts">Arts</option>
                <option value="other">Other / NVQ 4 Pathway</option>
              </select>
            </div>

            <!-- Course Choice 1 -->
            <div class="col-12 col-md-6">
              <label class="form-label small fw-bold text-dark">Course Option 1 *</label>
              <input type="text" name="course_option_one" class="form-control rounded-3" value="National Diploma in ICT (NVQ Level 5)" required>
            </div>

            <!-- Course Choice 2 -->
            <div class="col-12">
              <label class="form-label small fw-bold text-dark">Course Option 2 (Optional)</label>
              <input type="text" name="course_option_two" class="form-control rounded-3" placeholder="e.g. Computer Graphic Design (NVQ Level 4)">
            </div>
          </div>
        </div>

        <div class="modal-footer bg-white border-top px-4 py-2.5">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm" style="background: #7A1418; border-color: #7A1418;">
            <i class="bi bi-save me-1"></i> Save Candidate Record
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openDossierModal(app) {
  document.getElementById('dossierName').textContent = app.full_name;
  document.getElementById('dossierRef').textContent = app.student_id;
  document.getElementById('dossierNic').textContent = app.nic;
  document.getElementById('dossierDob').textContent = app.dob || 'Not provided';
  document.getElementById('dossierAddress').textContent = app.address || 'Not provided';
  document.getElementById('dossierPhone').textContent = app.contact_no;
  document.getElementById('dossierWa').textContent = app.whatsapp_no || app.contact_no;

  document.getElementById('dossierOlPass').textContent = app.ol_pass_status === 'Yes' ? 'Passed' : 'Failed';
  document.getElementById('dossierEng').textContent = app.ol_english_grade || '-';
  document.getElementById('dossierMath').textContent = app.ol_maths_grade || '-';
  document.getElementById('dossierSci').textContent = app.ol_science_grade || '-';
  document.getElementById('dossierAlStream').textContent = app.al_category || 'General';

  document.getElementById('dossierOpt1').textContent = app.course_option_one;
  document.getElementById('dossierOpt2').textContent = app.course_option_two || 'None';

  let cleanPhone = (app.whatsapp_no || app.contact_no || '').replace(/[^0-9]/g, '');
  if (cleanPhone.startsWith('0')) cleanPhone = '94' + cleanPhone.substring(1);
  const waUrl = 'https://wa.me/' + cleanPhone + '?text=' + encodeURIComponent('Hello ' + app.full_name + ', regarding your NVTI Baddegama ICT NVQ Level 5 Diploma application:');
  document.getElementById('dossierWaBtn').href = waUrl;

  const modal = new bootstrap.Modal(document.getElementById('dossierModal'));
  modal.show();
}

function openEnrollModal(app) {
  document.getElementById('enrollAppId').value = app.id;
  document.getElementById('enrollCandidateName').textContent = app.full_name;
  document.getElementById('enrollCandidateNic').textContent = 'NIC: ' + app.nic + ' • Student ID: ' + app.student_id;

  const modal = new bootstrap.Modal(document.getElementById('enrollModal'));
  modal.show();
}
</script>

<style>
@media (min-width: 992px) {
  .col-lg-2-4 {
    flex: 0 0 auto;
    width: 20%;
  }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
