<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<style>
/* Guaranteed styling for Login Card */
.login-auth-card {
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  background: #ffffff;
  box-shadow: 0 16px 36px -12px rgba(15, 23, 42, 0.08), 0 4px 14px -3px rgba(15, 23, 42, 0.03);
  overflow: hidden;
  transition: all 0.25s ease;
}
.login-auth-card:hover {
  box-shadow: 0 20px 42px -10px rgba(15, 23, 42, 0.11), 0 6px 18px -4px rgba(15, 23, 42, 0.04);
}
.login-brand-header {
  background: linear-gradient(180deg, #fdf8f8 0%, #ffffff 100%);
  border-bottom: 1px solid #f1f5f9;
  padding: 2.2rem 2rem 1.8rem;
  text-align: center;
}
.login-brand-logo {
  height: 52px;
  width: auto;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.06));
}
.login-badge-nvq {
  background: #fef2f2;
  color: #7A1418;
  border: 1px solid #fecaca;
  font-size: 0.76rem;
  font-weight: 700;
  letter-spacing: 0.4px;
  padding: 0.35rem 0.8rem;
  border-radius: 9999px;
  display: inline-block;
  margin-bottom: 0.75rem;
}
.login-input-group .input-group-text {
  background-color: #f8fafc;
  border-color: #cbd5e1;
  color: #64748b;
  padding-left: 1.1rem;
  padding-right: 1.1rem;
}
.login-input-group .form-control {
  border-color: #cbd5e1;
  font-size: 0.95rem;
  padding-top: 0.7rem;
  padding-bottom: 0.7rem;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.login-input-group .form-control:focus {
  border-color: #7A1418;
  box-shadow: 0 0 0 0.25rem rgba(122, 20, 24, 0.14);
}
.login-password-toggle {
  background-color: #f8fafc;
  border-color: #cbd5e1;
  color: #64748b;
  transition: all 0.2s ease;
}
.login-password-toggle:hover {
  background-color: #f1f5f9;
  color: #334155;
}
.btn-login-submit {
  background: linear-gradient(135deg, #7A1418 0%, #8e181c 100%) !important;
  color: #ffffff !important;
  border: none !important;
  font-weight: 700 !important;
  font-size: 1rem;
  padding: 0.8rem 1.25rem;
  border-radius: 10px;
  box-shadow: 0 4px 14px rgba(122, 20, 24, 0.25);
  transition: all 0.25s ease;
}
.btn-login-submit:hover {
  background: linear-gradient(135deg, #611013 0%, #7A1418 100%) !important;
  box-shadow: 0 6px 18px rgba(122, 20, 24, 0.35);
  transform: translateY(-1px);
}
.login-activation-card {
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
  border-radius: 12px;
  padding: 1rem 1.2rem;
  transition: all 0.2s ease;
}
.login-activation-card:hover {
  background: #eff6ff;
  border-color: #93c5fd;
}
.demo-card-header-btn {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 0.65rem 1rem;
  font-size: 0.85rem;
  color: #475569;
  font-weight: 600;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  transition: all 0.2s ease;
}
.demo-card-header-btn:hover {
  background: #f8fafc;
  color: #1e293b;
}
</style>

<div class="container py-4 py-md-5">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">

      <?php if (!empty($_GET['timeout'])): ?>
        <div class="alert alert-warning alert-dismissible fade show rounded-3 small" role="alert">
          <i class="bi bi-clock-history me-1"></i> Your session has expired due to inactivity. Please log in again.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <?= Flash::render() ?>

      <div class="login-auth-card mb-4">
        <!-- Top Branding Header -->
        <div class="login-brand-header">
          <div class="d-flex justify-content-center align-items-center gap-3 mb-2">
            <img src="assets/images/logo/NVTI_logo_dark.png" onerror="this.onerror=null; this.src='assets/images/logo/NVTI_logo.png';" alt="NVTI Logo" class="login-brand-logo" style="height: 54px; width: auto; object-fit: contain;">
          </div>
          <div>
            <span class="login-badge-nvq">
              <i class="bi bi-patch-check-fill me-1 text-danger"></i> NVQ Level 5 &bull; ICT Diploma Portal
            </span>
          </div>
          <h4 class="fw-bold mb-1 text-dark">Portal Sign In</h4>
          <p class="text-muted small mb-0">National Vocational Training Institute &bull; Baddegama</p>
        </div>

        <!-- Form Body -->
        <div class="p-4 p-md-4">
          <form action="index.php?page=login_process" method="POST" autocomplete="off" id="loginForm">
            <?= Csrf::field() ?>

            <!-- Identifier (Email, MIS Number, NIC) -->
            <div class="mb-3">
              <label for="loginEmail" class="form-label fw-semibold text-dark small mb-1.5">
                <i class="bi bi-person-badge text-primary me-1"></i> Email / MIS Number / NIC
              </label>
              <div class="input-group login-input-group">
                <span class="input-group-text border-end-0">
                  <i class="bi bi-person text-secondary"></i>
                </span>
                <input type="text" 
                       class="form-control border-start-0" 
                       id="loginEmail" 
                       name="email" 
                       placeholder="e.g. MIS/ICT/2024/001 or NIC or Email" 
                       required 
                       autofocus>
              </div>
              <div class="d-flex align-items-center justify-content-between mt-1">
                <small class="text-muted" style="font-size: 0.76rem;">
                  <i class="bi bi-info-circle me-1"></i>Students can enter their official MIS or NIC
                </small>
              </div>
            </div>

            <!-- Password -->
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-1.5">
                <label for="loginPassword" class="form-label fw-semibold text-dark small mb-0">
                  <i class="bi bi-shield-lock text-primary me-1"></i> Password
                </label>
                <a href="index.php?page=forgot_password" class="small text-decoration-none text-primary fw-medium" tabindex="-1">
                  Forgot Password?
                </a>
              </div>
              <div class="input-group login-input-group">
                <span class="input-group-text border-end-0">
                  <i class="bi bi-key text-secondary"></i>
                </span>
                <input type="password" 
                       class="form-control border-start-0 border-end-0" 
                       id="loginPassword" 
                       name="password" 
                       placeholder="Enter your account password" 
                       required>
                <button class="btn login-password-toggle border-start-0" 
                        type="button" 
                        id="togglePasswordBtn" 
                        onclick="togglePasswordVisibility()" 
                        tabindex="-1"
                        title="Show/Hide Password">
                  <i class="bi bi-eye" id="togglePasswordIcon"></i>
                </button>
              </div>
            </div>

            <!-- Remember Me -->
            <div class="d-flex align-items-center justify-content-between mb-4 pt-1">
              <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" id="rememberMe" name="remember" checked>
                <label class="form-check-label text-muted small user-select-none" for="rememberMe">
                  Remember this device
                </label>
              </div>
              <span class="badge bg-light text-secondary border small fw-normal">
                <i class="bi bi-lock-fill me-1 text-success"></i> Secure SSL
              </span>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-login-submit w-100 d-flex align-items-center justify-content-center gap-2 mb-3">
              <span>Sign In to Portal</span>
              <i class="bi bi-arrow-right-short fs-5"></i>
            </button>
          </form>

          <!-- Student Self-Registration Card (Method 3) -->
          <div class="login-activation-card mt-3" style="background: linear-gradient(180deg, #fdf8f8 0%, #ffffff 100%); border: 1.5px solid #fecaca;">
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2.5">
              <div class="d-flex align-items-center gap-2.5">
                <div class="rounded-circle text-white p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px; background: #7A1418;">
                  <i class="bi bi-person-plus-fill fs-6"></i>
                </div>
                <div>
                  <div class="fw-bold text-dark small">New Student to Diploma?</div>
                  <div class="text-muted" style="font-size: 0.77rem;">Create your profile &amp; register online</div>
                </div>
              </div>
              <a href="index.php?page=register" class="btn btn-sm btn-primary fw-bold px-3 py-1.5 text-nowrap rounded-pill shadow-sm" style="background: #7A1418; border-color: #7A1418;">
                <i class="bi bi-person-plus me-1"></i> Register Here
              </a>
            </div>
          </div>

          <!-- Secondary Activation Link (For Students pre-entered by Admin) -->
          <div class="d-flex align-items-center justify-content-between mt-2.5 px-1">
            <span class="text-muted small" style="font-size: 0.76rem;">Pre-enrolled by NVTI?</span>
            <a href="index.php?page=activate" class="text-decoration-none small fw-semibold text-primary" style="font-size: 0.78rem;">
              <i class="bi bi-key me-1"></i> Activate with MIS / NIC &rarr;
            </a>
          </div>

          <!-- Quick Demo Accounts for NVQ 5 Assessment (Collapsible to keep UI neat) -->
          <div class="mt-3">
            <button class="demo-card-header-btn" type="button" data-bs-toggle="collapse" data-bs-target="#demoAccountsCollapse" aria-expanded="false" aria-controls="demoAccountsCollapse">
              <span class="d-flex align-items-center gap-2">
                <i class="bi bi-lightning-charge-fill text-warning"></i>
                <span>One-Click Demo Logins</span>
                <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.7rem;">Examiner Testing</span>
              </span>
              <i class="bi bi-chevron-down text-muted" id="demoCollapseArrow"></i>
            </button>

            <div class="collapse mt-2" id="demoAccountsCollapse">
              <div class="p-3 bg-light rounded-3 border">
                <p class="text-muted small mb-2" style="font-size: 0.75rem;">
                  Click any role below to automatically fill the credentials for rapid testing:
                </p>
                <div class="row g-2">
                  <div class="col-6">
                    <button type="button" class="btn btn-outline-danger btn-sm w-100 text-truncate text-start" onclick="fillLoginAndFlash('admin@nvti.edu.lk', 'AdminPassword123!', 'Admin')">
                      <i class="bi bi-shield-shaded me-1"></i> <strong>Admin</strong>
                    </button>
                  </div>
                  <div class="col-6">
                    <button type="button" class="btn btn-outline-success btn-sm w-100 text-truncate text-start" onclick="fillLoginAndFlash('student@nvti.edu.lk', 'StudentPassword123!', 'Student')">
                      <i class="bi bi-person-check me-1"></i> <strong>Student (ICT)</strong>
                    </button>
                  </div>
                  <div class="col-6">
                    <button type="button" class="btn btn-outline-warning btn-sm w-100 text-truncate text-start" onclick="fillLoginAndFlash('teacher1@nvti.edu.lk', 'TeacherPassword123!', 'T1 (Nishantha)')" title="Senior Instructor: U.G.P.K. Nishantha (09:00 - 12:00)">
                      <i class="bi bi-sun me-1"></i> <strong>Teacher 1</strong>
                    </button>
                  </div>
                  <div class="col-6">
                    <button type="button" class="btn btn-outline-primary btn-sm w-100 text-truncate text-start" onclick="fillLoginAndFlash('teacher2@nvti.edu.lk', 'TeacherPassword123!', 'T2 (Afternoon)')" title="Afternoon Teacher (13:00 - 16:00)">
                      <i class="bi bi-sunset me-1"></i> <strong>Teacher 2</strong>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Quick Back to Home Link -->
      <div class="text-center">
        <a href="index.php?page=home" class="text-decoration-none text-muted small hover-primary d-inline-flex align-items-center gap-1">
          <i class="bi bi-arrow-left"></i> Back to NVTI Diploma Homepage
        </a>
      </div>

    </div>
  </div>
</div>

<script>
// Show/Hide Password Toggle
function togglePasswordVisibility() {
  const passInput = document.getElementById('loginPassword');
  const icon = document.getElementById('togglePasswordIcon');
  if (!passInput || !icon) return;
  
  if (passInput.type === 'password') {
    passInput.type = 'text';
    icon.classList.remove('bi-eye');
    icon.classList.add('bi-eye-slash');
  } else {
    passInput.type = 'password';
    icon.classList.remove('bi-eye-slash');
    icon.classList.add('bi-eye');
  }
}

// Quick Fill with feedback
function fillLoginAndFlash(email, password, roleName) {
  if (typeof window.fillLogin === 'function') {
    window.fillLogin(email, password);
  } else {
    const eF = document.getElementById('loginEmail');
    const pF = document.getElementById('loginPassword');
    if (eF) eF.value = email;
    if (pF) pF.value = password;
  }
}

// Rotate collapse icon on toggle
document.addEventListener('DOMContentLoaded', function() {
  const collapseEl = document.getElementById('demoAccountsCollapse');
  const arrowEl = document.getElementById('demoCollapseArrow');
  if (collapseEl && arrowEl) {
    collapseEl.addEventListener('show.bs.collapse', function () {
      arrowEl.classList.remove('bi-chevron-down');
      arrowEl.classList.add('bi-chevron-up');
    });
    collapseEl.addEventListener('hide.bs.collapse', function () {
      arrowEl.classList.remove('bi-chevron-up');
      arrowEl.classList.add('bi-chevron-down');
    });
  }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
