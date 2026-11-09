<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="app-wrapper">
  <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <div class="container-fluid">
      <?= Flash::render() ?>

      <!-- Header Banner (Official NVTI Maroon) -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 text-white overflow-hidden position-relative" style="background: linear-gradient(135deg, #7A1418 0%, #991b1b 100%); padding: 1.85rem 2.25rem;">
        <!-- Watermark Background Crest (Subtle Opacity to avoid button overlap) -->
        <div class="position-absolute end-0 top-50 translate-middle-y me-2 pointer-events-none d-none d-xl-block" style="font-size: 7rem; line-height: 0; opacity: 0.05 !important;">
          <i class="bi bi-shield-lock-fill text-white"></i>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 position-relative" style="z-index: 5;">
          <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
              <span class="badge rounded-pill font-monospace px-3 py-1.5 fw-semibold shadow-xs" style="background: rgba(255, 255, 255, 0.22) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.38); font-size: 0.74rem;">
                <i class="bi bi-shield-check me-1"></i> Staff &amp; Administration
              </span>
              <span class="badge rounded-pill px-3 py-1.5 fw-semibold shadow-xs" style="background: rgba(255, 255, 255, 0.22) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.38); font-size: 0.74rem;">
                <i class="bi bi-mortarboard me-1"></i> NVQ Level 5 Faculty
              </span>
            </div>
            <h3 class="fw-bold mb-1.5 text-white" style="letter-spacing: -0.3px;">Faculty &amp; Administrator Accounts</h3>
            <p class="mb-0 text-white opacity-85 small" style="max-width: 680px;">Managing dedicated administrator accounts, lecturer shift allocations (09:00-12:00 &amp; 13:00-16:00), and academic modules.</p>
          </div>

          <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0 align-self-start align-self-md-center">
            <button class="btn btn-warning rounded-pill px-3.5 py-2 fw-bold text-dark d-inline-flex align-items-center gap-2 shadow-xs" data-bs-toggle="modal" data-bs-target="#addAdminModal">
              <i class="bi bi-shield-plus fs-6"></i> Add Administrator
            </button>
            <button class="btn btn-light rounded-pill px-3.5 py-2 fw-bold text-danger d-inline-flex align-items-center gap-2 shadow" style="color: #7A1418 !important;" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
              <i class="bi bi-person-plus-fill fs-6"></i> Register Lecturer
            </button>
          </div>
        </div>
      </div>

      <!-- SECTION 1: DEDICATED ADMINISTRATOR ACCOUNTS -->
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
          <span class="badge rounded-circle p-1.5 text-white d-flex align-items-center justify-content-center" style="background: #7A1418; width: 28px; height: 28px;">
            <i class="bi bi-shield-lock-fill" style="font-size: 0.85rem;"></i>
          </span>
          System &amp; Academic Administrators
        </h5>
        <span class="badge bg-light text-secondary border rounded-pill px-3 py-1 font-monospace">
          <?= count($adminUsers ?? []) ?> Active Administrators
        </span>
      </div>

      <div class="row g-3 mb-5">
        <?php foreach (($adminUsers ?? []) as $adm): 
          $isPrimary = ($adm['id'] == 1);
        ?>
          <div class="col-12 col-md-4">
            <div class="card border rounded-4 bg-white shadow-sm h-100 p-3.5 transition-hover position-relative" style="<?= $isPrimary ? 'border-top: 3px solid #7A1418 !important;' : 'border-top: 3px solid #1967d2 !important;' ?>">
              <div class="d-flex align-items-start gap-3">
                <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0 shadow-sm" 
                     style="width: 46px; height: 46px; font-size: 1.05rem; background: <?= $isPrimary ? 'linear-gradient(135deg, #7A1418, #90161b)' : 'linear-gradient(135deg, #1967d2, #1557b0)' ?>;">
                  <?= strtoupper(substr($adm['name'], 0, 1)) ?>
                </div>

                <div class="flex-grow-1 min-w-0">
                  <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                    <span class="badge rounded-pill <?= $isPrimary ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' ?> fw-bold" style="font-size: 0.7rem;">
                      <i class="bi <?= $isPrimary ? 'bi-shield-shaded' : 'bi-person-check-fill' ?> me-1"></i>
                      <?= $isPrimary ? 'Primary Administrator' : 'Academic Administrator' ?>
                    </span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size: 0.65rem;">Active</span>
                  </div>

                  <h6 class="fw-bold text-dark mb-0 text-truncate" title="<?= e($adm['name']) ?>">
                    <?= e($adm['name']) ?>
                  </h6>
                  <div class="text-muted small text-truncate font-monospace" style="font-size: 0.78rem;">
                    <i class="bi bi-envelope me-1"></i><a href="mailto:<?= e($adm['email']) ?>" class="text-decoration-none text-secondary"><?= e($adm['email']) ?></a>
                  </div>
                  <div class="text-muted small mt-2 pt-2 border-top d-flex justify-content-between align-items-center" style="font-size: 0.72rem;">
                    <span>User ID: <strong>#<?= $adm['id'] ?></strong></span>
                    <span class="text-success"><i class="bi bi-key-fill me-1"></i>Full Admin Access</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- SECTION 2: FACULTY LECTURERS & TEACHING SHIFTS -->
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
          <span class="badge rounded-circle p-1.5 text-white d-flex align-items-center justify-content-center" style="background: #1967d2; width: 28px; height: 28px;">
            <i class="bi bi-person-video3" style="font-size: 0.85rem;"></i>
          </span>
          Lecturer Profiles &amp; Teaching Shifts
        </h5>
        <span class="badge bg-light text-secondary border rounded-pill px-3 py-1 font-monospace">
          <?= count($teachers) ?> Registered Lecturers
        </span>
      </div>

      <!-- Teacher List Cards -->
      <div class="row g-4 mb-4">
        <?php foreach ($teachers as $idx => $tch): ?>
          <?php
            $isMorning = str_contains(strtolower($tch['preferred_slot'] ?? ''), 'morning') || $idx === 0;
          ?>
          <div class="col-lg-6">
            <div class="card border rounded-4 shadow-sm h-100 overflow-hidden bg-white">
              <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                <div class="d-flex align-items-center gap-3">
                  <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm" style="width: 44px; height: 44px; background: <?= $isMorning ? '#d97706' : '#4f46e5' ?>;">
                    <i class="bi <?= $isMorning ? 'bi-sun-fill' : 'bi-sunset-fill' ?>"></i>
                  </div>
                  <div>
                    <h5 class="fw-bold mb-0 text-dark"><?= e($tch['name']) ?></h5>
                    <span class="badge bg-light text-dark border small font-monospace"><?= e($tch['teacher_code'] ?? 'TCH') ?></span>
                  </div>
                </div>

                <div>
                  <?php if ($isMorning): ?>
                    <span class="badge slot-badge-morning px-2.5 py-1.5 rounded-pill"><i class="bi bi-sun me-1"></i> Morning (09:00 - 12:00)</span>
                  <?php else: ?>
                    <span class="badge slot-badge-afternoon px-2.5 py-1.5 rounded-pill"><i class="bi bi-sunset me-1"></i> Afternoon (13:00 - 16:00)</span>
                  <?php endif; ?>
                </div>
              </div>

              <div class="card-body p-4">
                <div class="row g-3 mb-3">
                  <div class="col-sm-6">
                    <span class="text-muted small d-block">Email Address:</span>
                    <strong class="text-dark small"><a href="mailto:<?= e($tch['email']) ?>"><?= e($tch['email']) ?></a></strong>
                  </div>
                  <div class="col-sm-6">
                    <span class="text-muted small d-block">Phone:</span>
                    <strong class="text-dark small"><?= e($tch['phone'] ?? '077xxxxxxx') ?></strong>
                  </div>
                  <div class="col-12">
                    <span class="text-muted small d-block">Qualifications &amp; Bio:</span>
                    <span class="small text-secondary"><?= e($tch['qualification'] ?? 'NVTI Certified Lecturer') ?></span>
                  </div>
                  <?php if (!empty($tch['specialization'])): ?>
                    <div class="col-12">
                      <span class="text-muted small d-block">Specialization:</span>
                      <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= e($tch['specialization']) ?></span>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="p-3 bg-light rounded-3 mb-3">
                  <span class="fw-semibold small text-dark d-block mb-2"><i class="bi bi-book-fill text-primary me-1"></i> Assigned Subjects:</span>
                  <?php if (empty($tch['assigned_subjects'])): ?>
                    <span class="text-muted small">No subjects assigned yet.</span>
                  <?php else: ?>
                    <div class="d-flex flex-wrap gap-2">
                      <?php foreach ($tch['assigned_subjects'] as $as): ?>
                        <span class="badge bg-white text-dark border px-2.5 py-1.5 shadow-xs">
                          <strong><?= e($as['subject_code']) ?></strong> - <?= e($as['subject_name']) ?>
                          <span class="text-muted">(<?= e($as['class_name']) ?>)</span>
                        </span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

              <div class="card-footer bg-light px-4 py-2.5 d-flex justify-content-between align-items-center">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Status: <?= ucfirst($tch['status']) ?></span>
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#editTeacherModal<?= $tch['id'] ?>">
                  <i class="bi bi-pencil-square me-1"></i> Edit Shifts &amp; Details
                </button>
              </div>
            </div>
          </div>

          <!-- Modal: Edit Teacher -->
          <div class="modal fade" id="editTeacherModal<?= $tch['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                <div class="modal-header bg-edu-navy text-white py-3">
                  <h6 class="modal-title fw-bold">Edit Lecturer: <?= e($tch['name']) ?></h6>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="index.php?page=admin_update_teacher" method="POST">
                  <?= Csrf::field() ?>
                  <input type="hidden" name="teacher_id" value="<?= $tch['id'] ?>">
                  <div class="modal-body p-4">
                    <div class="mb-3">
                      <label class="form-label fw-semibold small text-secondary">Preferred Teaching Shift</label>
                      <select name="preferred_slot" class="form-select">
                        <option value="Morning (09:00 - 12:00)" <?= str_contains($tch['preferred_slot'] ?? '', 'Morning') ? 'selected' : '' ?>>Morning Shift (09:00 - 12:00)</option>
                        <option value="Afternoon (13:00 - 16:00)" <?= str_contains($tch['preferred_slot'] ?? '', 'Afternoon') ? 'selected' : '' ?>>Afternoon Shift (13:00 - 16:00)</option>
                        <option value="Full Day / Flexible" <?= str_contains($tch['preferred_slot'] ?? '', 'Flexible') ? 'selected' : '' ?>>Full Day / Flexible</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-semibold small text-secondary">Contact Phone</label>
                      <input type="text" name="phone" class="form-control" value="<?= e($tch['phone'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-semibold small text-secondary">Qualifications</label>
                      <textarea name="qualification" class="form-control" rows="2"><?= e($tch['qualification'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-semibold small text-secondary">Specialization</label>
                      <input type="text" name="specialization" class="form-control" value="<?= e($tch['specialization'] ?? '') ?>" placeholder="e.g. PHP, MySQL, Networking">
                    </div>
                  </div>
                  <div class="modal-footer bg-light py-2 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">Update Details</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    </div>
  </main>
</div>

<!-- ================= MODAL 1: ADD NEW ADMINISTRATOR ================= -->
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <form action="index.php?page=admin_create_admin" method="POST">
        <?= Csrf::field() ?>
        <div class="modal-header border-bottom py-3 px-4 bg-light">
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle p-2 text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #7A1418;">
              <i class="bi bi-shield-plus fs-5"></i>
            </div>
            <div>
              <h6 class="modal-title fw-bold text-dark mb-0" id="addAdminModalLabel">Create Administrator Account</h6>
              <small class="text-muted">Grant full administrator access to faculty or senior staff</small>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-bold text-dark small mb-1">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g. U.G.P.K. Nishantha" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold text-dark small mb-1">Official Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" placeholder="e.g. nishantha@nvti.edu.lk" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold text-dark small mb-1">Password <span class="text-danger">*</span></label>
            <input type="text" name="password" class="form-control" value="nvtiadmin123" required>
            <small class="text-muted" style="font-size: 0.72rem;">User can change this password after logging in via "Change Password".</small>
          </div>
        </div>

        <div class="modal-footer bg-light px-4 py-3 border-top">
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" style="background: #7A1418; border-color: #7A1418;">
            <i class="bi bi-shield-check me-1"></i> Create Administrator
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= MODAL 2: ADD NEW LECTURER ================= -->
<div class="modal fade" id="addTeacherModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
      <div class="modal-header bg-edu-navy text-white py-3">
        <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2 text-warning"></i>Register Lecturer</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="index.php?page=admin_create_teacher" method="POST">
        <?= Csrf::field() ?>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Lecturer Full Name *</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Mr. Sunil Fernando" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Email Address *</label>
            <input type="email" name="email" class="form-control" placeholder="sunil@nvti.edu.lk" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Contact Phone</label>
            <input type="text" name="phone" class="form-control" placeholder="07xxxxxxxx">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Teaching Shift *</label>
            <select name="preferred_slot" class="form-select" required>
              <option value="Morning (09:00 - 12:00)">Morning Shift (09:00 - 12:00)</option>
              <option value="Afternoon (13:00 - 16:00)">Afternoon Shift (13:00 - 16:00)</option>
              <option value="Full Day / Flexible">Full Day / Flexible</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Academic Qualifications</label>
            <textarea name="qualification" class="form-control" rows="2" placeholder="e.g. BSc in IT, CCNA, NVQ Assessor"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Initial Password *</label>
            <input type="password" name="password" class="form-control" value="TeacherPassword123!" required>
          </div>
        </div>
        <div class="modal-footer bg-light py-2 px-4">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">Save Lecturer Account</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
