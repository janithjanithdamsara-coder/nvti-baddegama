<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container py-4 py-md-5">
  <div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
      <?= Flash::render() ?>

      <div class="card border rounded-4 shadow-sm overflow-hidden bg-white mb-4">
        
        <!-- ================= TOP BRANDING HEADER ================= -->
        <div class="card-header bg-white text-center py-4 px-3 border-bottom">
          <div class="d-flex justify-content-center align-items-center mb-2.5">
            <img src="assets/images/logo/NVTI_logo.png" 
                 onerror="this.onerror=null; this.src='https://nvtibaddegama.site/images/logo/NVTI_logo.png';" 
                 alt="NVTI Logo" 
                 style="height: 52px; width: auto; max-width: 160px; object-fit: contain;">
          </div>
          <div class="mb-2">
            <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background: #fef2f2; color: #7A1418; border: 1px solid #fecaca; font-size: 0.78rem;">
              <i class="bi bi-mortarboard-fill me-1"></i> NVQ Level 5 &bull; Batch 2026
            </span>
          </div>
          <h4 class="fw-bold mb-1 text-dark">Student Registration</h4>
          <div class="fw-bold mb-1" style="color: #7A1418; font-size: 1rem;">
            National Diploma - Information &amp; Communication Technology
          </div>
          <p class="text-muted small mb-0">National Vocational Training Institute &bull; Baddegama</p>
        </div>

        <div class="card-body p-4 p-md-5">
          <form action="index.php?page=register_process" method="POST" autocomplete="off" id="studentRegisterForm">
            <?= Csrf::field() ?>

            <!-- Hidden Default Course & Class -->
            <input type="hidden" name="course_id" value="<?= (int)($course['id'] ?? 1) ?>">
            <input type="hidden" name="class_id" value="<?= (int)($class['id'] ?? 1) ?>">

            <!-- ================= SECTION 1: PERSONAL INFORMATION ================= -->
            <div class="mb-4">
              <div class="d-flex align-items-center gap-2 mb-3 pb-1 border-bottom">
                <span class="badge rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="background: #7A1418; color: #fff; width: 22px; height: 22px; font-size: 0.72rem;">1</span>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 0.95rem;">Personal Information</h6>
              </div>

              <div class="row g-3">
                <!-- Full Name -->
                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold small text-secondary">Full Name (with initials) *</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person-fill"></i></span>
                    <input type="text" name="name" class="form-control border-start-0" placeholder="e.g. Kasun Madushanka Silva" required autofocus>
                  </div>
                </div>

                <!-- National Identity Card (NIC) -->
                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold small text-secondary">National Identity Card (NIC) *</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-card-heading"></i></span>
                    <input type="text" name="nic" class="form-control border-start-0 text-uppercase" placeholder="e.g. 200112345678 or 991234567V" required>
                  </div>
                  <small class="text-muted" style="font-size: 0.72rem;">12-digit new NIC or 9-digit old NIC with V/X.</small>
                </div>

                <!-- Mobile Phone -->
                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold small text-secondary">Mobile Phone Number *</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-telephone-fill"></i></span>
                    <input type="tel" name="phone" id="phoneInput" class="form-control border-start-0" placeholder="e.g. 0771234567" required>
                  </div>
                </div>

                <!-- WhatsApp Number -->
                <div class="col-12 col-md-6">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label fw-semibold small text-secondary mb-0">WhatsApp Number</label>
                    <button type="button" class="btn btn-link p-0 text-decoration-none small text-primary" style="font-size: 0.72rem;" onclick="copyPhoneToWhatsapp()">Same as mobile</button>
                  </div>
                  <div class="input-group">
                    <span class="input-group-text bg-light text-success border-end-0"><i class="bi bi-whatsapp"></i></span>
                    <input type="tel" name="whatsapp_number" id="whatsappInput" class="form-control border-start-0" placeholder="e.g. 0771234567">
                  </div>
                </div>

                <!-- Address -->
                <div class="col-12">
                  <label class="form-label fw-semibold small text-secondary">Residential / Postal Address</label>
                  <textarea name="address" class="form-control" rows="2" placeholder="Street, Village/Town, City"></textarea>
                </div>
              </div>
            </div>

            <!-- ================= SECTION 2: LOGIN CREDENTIALS ================= -->
            <div class="mb-4">
              <div class="d-flex align-items-center gap-2 mb-3 pb-1 border-bottom">
                <span class="badge rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="background: #7A1418; color: #fff; width: 22px; height: 22px; font-size: 0.72rem;">2</span>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 0.95rem;">Portal Account &amp; Password</h6>
              </div>

              <div class="row g-3">
                <!-- Email Address -->
                <div class="col-12">
                  <label class="form-label fw-semibold small text-secondary">Login Email Address *</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope-at-fill"></i></span>
                    <input type="email" name="email" class="form-control border-start-0" placeholder="e.g. student@gmail.com" required>
                  </div>
                  <small class="text-muted" style="font-size: 0.72rem;">You will use this email address to log in to your student portal.</small>
                </div>

                <!-- Password -->
                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold small text-secondary">Create Password *</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="password" id="regPassword" class="form-control border-start-0 border-end-0" placeholder="Min 6 characters" minlength="6" required>
                    <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassword('regPassword', 'toggleIcon1')">
                      <i class="bi bi-eye" id="toggleIcon1"></i>
                    </button>
                  </div>
                </div>

                <!-- Confirm Password -->
                <div class="col-12 col-md-6">
                  <label class="form-label fw-semibold small text-secondary">Confirm Password *</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-shield-lock-fill"></i></span>
                    <input type="password" name="confirm_password" id="regConfirmPassword" class="form-control border-start-0 border-end-0" placeholder="Re-type password" minlength="6" required>
                    <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassword('regConfirmPassword', 'toggleIcon2')">
                      <i class="bi bi-eye" id="toggleIcon2"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- ================= SUBMIT BUTTON ================= -->
            <div class="pt-2">
              <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2 mb-3" style="font-size: 1rem;">
                <i class="bi bi-person-check-fill fs-5"></i>
                <span>Complete Registration &amp; Enter Portal</span>
                <i class="bi bi-arrow-right fs-5"></i>
              </button>
            </div>

            <!-- Return to Sign In -->
            <div class="text-center pt-2 border-top">
              <span class="text-muted small">Already have an account? </span>
              <a href="index.php?page=login" class="small fw-bold text-decoration-none text-primary">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Portal
              </a>
            </div>

          </form>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
function togglePassword(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(iconId);
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.remove('bi-eye');
    icon.classList.add('bi-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.remove('bi-eye-slash');
    icon.classList.add('bi-eye');
  }
}

function copyPhoneToWhatsapp() {
  const phone = document.getElementById('phoneInput').value;
  if (phone) {
    document.getElementById('whatsappInput').value = phone;
  }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
