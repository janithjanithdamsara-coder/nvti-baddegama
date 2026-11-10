<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
$photo = !empty($student['profile_image']) ? $student['profile_image'] : '';
?>

<div class="app-wrapper">
  <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <div class="container-fluid" style="max-width: 960px;">
      <?= Flash::render() ?>

      <!-- Header Card with Student Avatar & Badges -->
      <div class="card border rounded-4 shadow-sm overflow-hidden mb-4 bg-white">
        <div class="card-header bg-white p-4 border-bottom">
          <div class="d-flex flex-column flex-sm-row align-items-center gap-3.5 text-center text-sm-start">
            <div class="position-relative">
              <?php if (!empty($photo)): ?>
                <img src="<?= e($photo) ?>" onerror="this.onerror=null; this.src='assets/images/software_developer_logo.png';" alt="Avatar" class="rounded-circle shadow-sm border" style="width: 82px; height: 82px; object-fit: cover;">
              <?php else: ?>
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-2 shadow-sm" style="width: 82px; height: 82px;">
                  <?= strtoupper(substr($student['name'] ?? 'S', 0, 1)) ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="flex-grow-1">
              <h3 class="fw-bold mb-1 text-dark"><?= e($student['name']) ?></h3>
              <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-sm-start gap-1.5 mt-1">
                <?php if (!empty($student['mis_number'])): ?>
                  <span class="badge bg-primary text-white font-monospace px-2.5 py-1 rounded-pill fw-bold">
                    <i class="bi bi-person-badge-fill me-1"></i><?= e($student['mis_number']) ?>
                  </span>
                <?php endif; ?>
                <span class="badge bg-light text-dark border font-monospace px-2.5 py-1 rounded-pill">
                  <?= e($student['student_code'] ?? 'NVTI-ST') ?>
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                  <?= e($student['nvq_level'] ?? 'NVQ Level 5') ?>
                </span>
                <span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1 rounded-pill">
                  <i class="bi bi-mortarboard me-1"></i><?= e($student['class_name'] ?? 'Batch 2026') ?>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Navigation Tabs (Clean Responsive Pills - Zero Overflow) -->
        <div class="px-3 px-md-4 py-3 border-bottom bg-light">
          <ul class="nav nav-pills flex-wrap justify-content-center justify-content-md-start gap-2" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active rounded-pill px-3 py-1.5 small fw-semibold" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                <i class="bi bi-person-lines-fill me-1"></i> Overview
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link rounded-pill px-3 py-1.5 small fw-semibold" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit" type="button" role="tab">
                <i class="bi bi-pencil-square me-1"></i> Edit Profile
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link rounded-pill px-3 py-1.5 small fw-semibold" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                <i class="bi bi-shield-lock me-1"></i> Security
              </button>
            </li>
          </ul>
        </div>

        <div class="card-body p-3 p-md-4">
          <div class="tab-content" id="profileTabsContent">

            <!-- TAB 1: OVERVIEW -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
              <div class="row g-4">
                <!-- Academic & Registration -->
                <div class="col-md-6">
                  <div class="p-3 bg-light rounded-3 h-100 border">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                      <i class="bi bi-mortarboard-fill text-primary me-2"></i>Academic Registration
                    </h6>
                    <div class="mb-2.5">
                      <span class="text-muted small d-block">MIS Number</span>
                      <strong class="text-primary font-monospace"><?= e($student['mis_number'] ?? 'Not Assigned') ?></strong>
                    </div>
                    <div class="mb-2.5">
                      <span class="text-muted small d-block">Student System ID</span>
                      <strong class="text-dark font-monospace"><?= e($student['student_code']) ?></strong>
                    </div>
                    <div class="mb-2.5">
                      <span class="text-muted small d-block">Course of Study</span>
                      <strong class="text-dark"><?= e($student['course_name'] ?? 'National Diploma in ICT') ?></strong>
                    </div>
                    <div>
                      <span class="text-muted small d-block">Enrolled Class / Batch</span>
                      <strong class="text-dark"><?= e($student['class_name'] ?? 'Batch 2026') ?></strong>
                    </div>
                  </div>
                </div>

                <!-- Personal Contact -->
                <div class="col-md-6">
                  <div class="p-3 bg-light rounded-3 h-100 border">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                      <i class="bi bi-person-vcard-fill text-primary me-2"></i>Personal Identification
                    </h6>
                    <div class="mb-2.5">
                      <span class="text-muted small d-block">National Identity Card (NIC)</span>
                      <strong class="text-dark font-monospace"><?= e($student['nic'] ?? 'Not set') ?></strong>
                    </div>
                    <div class="mb-2.5">
                      <span class="text-muted small d-block">Email Address</span>
                      <strong class="text-dark"><?= e($student['email']) ?></strong>
                    </div>
                    <div class="mb-2.5">
                      <span class="text-muted small d-block">Primary Contact Phone</span>
                      <strong class="text-dark"><?= e($student['phone'] ?? 'Not set') ?></strong>
                    </div>
                    <div>
                      <span class="text-muted small d-block">WhatsApp Contact</span>
                      <strong class="text-success"><i class="bi bi-whatsapp me-1"></i><?= e($student['whatsapp_number'] ?? 'Not set') ?></strong>
                    </div>
                  </div>
                </div>

                <!-- Residential Address -->
                <div class="col-12">
                  <div class="p-3 bg-light rounded-3 border">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">
                      <i class="bi bi-geo-alt-fill text-danger me-2"></i>Permanent Residential Address
                    </h6>
                    <p class="mb-0 text-dark"><?= nl2br(e($student['address'] ?? 'Address not recorded.')) ?></p>
                  </div>
                </div>

                <!-- Guardian Information -->
                <div class="col-12">
                  <div class="p-3 bg-light rounded-3 border">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                      <i class="bi bi-people-fill text-secondary me-2"></i>Parent / Guardian Information (භාරකරුගේ විස්තර)
                    </h6>
                    <div class="row g-3">
                      <div class="col-md-4">
                        <span class="text-muted small d-block">Guardian Full Name</span>
                        <strong class="text-dark"><?= e($student['guardian_name'] ?? 'Not provided') ?></strong>
                      </div>
                      <div class="col-md-4">
                        <span class="text-muted small d-block">Guardian Contact Phone</span>
                        <strong class="text-dark"><?= e($student['guardian_phone'] ?? 'Not provided') ?></strong>
                      </div>
                      <div class="col-md-4">
                        <span class="text-muted small d-block">Guardian Address</span>
                        <span class="text-dark"><?= e($student['guardian_address'] ?? 'Same as student address') ?></span>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>

            <!-- TAB 2: EDIT PROFILE -->
            <div class="tab-pane fade" id="edit" role="tabpanel">
              <form action="index.php?page=student_update_profile" method="POST" enctype="multipart/form-data">
                <?= Csrf::field() ?>

                <div class="row g-3">
                  <div class="col-12">
                    <div class="p-3 rounded-3 bg-light border mb-2">
                      <label class="form-label fw-semibold text-dark small mb-1">
                        <i class="bi bi-camera-fill text-primary me-1"></i> Profile Photo (Upload or Change Photo)
                      </label>
                      <input type="file" name="profile_photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                      <small class="text-muted">JPG, PNG, or WEBP (Max 5MB). Photo is shown on your student portal and instructors' attendance sheet.</small>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold small text-secondary">Student Full Name *</label>
                    <input type="text" name="name" class="form-control" value="<?= e($student['name']) ?>" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold small text-secondary">National Identity Card (NIC) *</label>
                    <input type="text" name="nic" class="form-control font-monospace" value="<?= e($student['nic'] ?? '') ?>" placeholder="e.g. 200125409876 or 991234567V">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold small text-secondary">MIS Number (NVTI / VTA Reg No.)</label>
                    <input type="text" name="mis_number" class="form-control font-monospace" value="<?= e($student['mis_number'] ?? '') ?>" placeholder="e.g. MIS/ICT/2026/001">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold small text-secondary">Primary Mobile Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= e($student['phone'] ?? '') ?>" placeholder="077xxxxxxx">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold small text-secondary">WhatsApp Phone Number</label>
                    <div class="input-group">
                      <span class="input-group-text bg-light text-success"><i class="bi bi-whatsapp"></i></span>
                      <input type="text" name="whatsapp_number" class="form-control" value="<?= e($student['whatsapp_number'] ?? '') ?>" placeholder="07xxxxxxxx">
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold small text-secondary">Email Address (Registered)</label>
                    <input type="email" class="form-control bg-light" value="<?= e($student['email']) ?>" readonly disabled>
                    <small class="text-muted" style="font-size: 0.72rem;">Email change requires administrator verification.</small>
                  </div>

                  <div class="col-12">
                    <label class="form-label fw-semibold small text-secondary">Permanent Residential Address</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Full home address"><?= e($student['address'] ?? '') ?></textarea>
                  </div>

                  <!-- Guardian Information -->
                  <div class="col-12 mt-4">
                    <h6 class="fw-bold text-dark border-bottom pb-2">Parent / Guardian Details (භාරකරුගේ තොරතුරු)</h6>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold small text-secondary">Guardian Full Name</label>
                    <input type="text" name="guardian_name" class="form-control" value="<?= e($student['guardian_name'] ?? '') ?>" placeholder="e.g. K. Sunimal Madushanka (Father)">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold small text-secondary">Guardian Contact Phone</label>
                    <input type="text" name="guardian_phone" class="form-control" value="<?= e($student['guardian_phone'] ?? '') ?>" placeholder="07xxxxxxxx">
                  </div>

                  <div class="col-12">
                    <label class="form-label fw-semibold small text-secondary">Guardian Residential Address</label>
                    <textarea name="guardian_address" class="form-control" rows="2" placeholder="Guardian address if different from student's address"><?= e($student['guardian_address'] ?? '') ?></textarea>
                  </div>
                </div>

                <div class="mt-4 pt-3 border-top text-end">
                  <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> Save &amp; Update Profile
                  </button>
                </div>
              </form>
            </div>

            <!-- TAB 3: SECURITY & PASSWORD -->
            <div class="tab-pane fade" id="security" role="tabpanel">
              <div class="p-3 bg-light rounded-3 border mb-4">
                <h6 class="fw-bold text-dark mb-1"><i class="bi bi-shield-lock-fill text-primary me-2"></i>Change Your Password</h6>
                <p class="text-muted small mb-0">Use a strong password with at least 8 characters, combining numbers and symbols.</p>
              </div>

              <form action="index.php?page=change_password_process" method="POST">
                <?= Csrf::field() ?>

                <div class="mb-3" style="max-width: 480px;">
                  <label class="form-label fw-semibold small text-secondary">Current Password *</label>
                  <div class="input-group">
                    <input type="password" name="current_password" class="form-control border-end-0" placeholder="••••••••" required>
                    <button class="btn btn-light border border-start-0 text-secondary" type="button" data-toggle-password tabindex="-1" title="Show/Hide Password" aria-label="Show password">
                      <i class="bi bi-eye"></i>
                    </button>
                  </div>
                </div>

                <div class="mb-3" style="max-width: 480px;">
                  <label class="form-label fw-semibold small text-secondary">New Password *</label>
                  <div class="input-group">
                    <input type="password" name="new_password" class="form-control border-end-0" placeholder="At least 8 characters" minlength="8" required>
                    <button class="btn btn-light border border-start-0 text-secondary" type="button" data-toggle-password tabindex="-1" title="Show/Hide Password" aria-label="Show password">
                      <i class="bi bi-eye"></i>
                    </button>
                  </div>
                </div>

                <div class="mb-4" style="max-width: 480px;">
                  <label class="form-label fw-semibold small text-secondary">Confirm New Password *</label>
                  <div class="input-group">
                    <input type="password" name="confirm_password" class="form-control border-end-0" placeholder="Confirm new password" minlength="8" required>
                    <button class="btn btn-light border border-start-0 text-secondary" type="button" data-toggle-password tabindex="-1" title="Show/Hide Password" aria-label="Show password">
                      <i class="bi bi-eye"></i>
                    </button>
                  </div>
                </div>

                <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                  <i class="bi bi-key-fill me-1"></i> Update Password
                </button>
              </form>
            </div>

          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
