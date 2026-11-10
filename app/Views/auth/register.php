<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<style>
/* Modern Rounded & Fluid Form Styles */
.reg-auth-wrapper {
  min-height: calc(100vh - 160px);
  display: flex;
  align-items: center;
  padding: 2.5rem 0;
  background: radial-gradient(circle at 50% 0%, #fef2f2 0%, #f8fafc 60%, #f1f5f9 100%);
}

.reg-auth-card {
  border: 1px solid rgba(226, 232, 240, 0.85);
  border-radius: 26px;
  background: #ffffff;
  box-shadow: 0 20px 48px -12px rgba(15, 23, 42, 0.09), 0 4px 18px -4px rgba(15, 23, 42, 0.03);
  overflow: hidden;
  transition: all 0.3s ease;
}

.reg-auth-header {
  background: linear-gradient(180deg, #fdf8f8 0%, #ffffff 100%);
  border-bottom: 1px solid #f1f5f9;
  padding: 2.2rem 2rem 1.8rem;
  text-align: center;
}

.reg-brand-logo {
  height: 54px;
  width: auto;
  object-fit: contain;
  filter: drop-shadow(0 2px 5px rgba(0,0,0,0.06));
}

.reg-badge-nvq {
  background: #fef2f2;
  color: #7A1418;
  border: 1px solid #fecaca;
  font-size: 0.76rem;
  font-weight: 700;
  letter-spacing: 0.4px;
  padding: 0.35rem 0.9rem;
  border-radius: 9999px;
  display: inline-block;
  margin-bottom: 0.8rem;
}

.reg-section-badge {
  background: linear-gradient(135deg, #7A1418 0%, #991b1b 100%);
  color: #ffffff;
  width: 26px;
  height: 26px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 700;
  box-shadow: 0 2px 6px rgba(122, 20, 24, 0.25);
}

.reg-section-title {
  font-size: 0.98rem;
  font-weight: 700;
  color: #1e293b;
  letter-spacing: -0.2px;
}

.reg-form-control {
  border-radius: 12px !important;
  border: 1.5px solid #e2e8f0;
  padding: 0.68rem 1rem;
  font-size: 0.92rem;
  transition: all 0.2s ease-in-out;
  background-color: #fafbfc;
}

.reg-form-control:focus {
  background-color: #ffffff;
  border-color: #7A1418;
  box-shadow: 0 0 0 4px rgba(122, 20, 24, 0.08);
}

.reg-input-group {
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
}

.reg-input-group .input-group-text {
  background-color: #f8fafc;
  border: 1.5px solid #e2e8f0;
  color: #64748b;
  padding-left: 1rem;
  padding-right: 0.85rem;
  border-top-left-radius: 14px;
  border-bottom-left-radius: 14px;
}

.reg-input-group .form-control {
  border: 1.5px solid #e2e8f0;
  border-left: none;
  border-top-right-radius: 14px;
  border-bottom-right-radius: 14px;
  padding: 0.7rem 1rem;
  background-color: #fafbfc;
}

.reg-input-group .form-control:focus {
  background-color: #ffffff;
  border-color: #7A1418;
  box-shadow: 0 0 0 3px rgba(122, 20, 24, 0.08);
}

.reg-btn-submit {
  background: linear-gradient(135deg, #7A1418 0%, #991b1b 100%);
  border: none;
  color: #ffffff;
  border-radius: 14px;
  padding: 0.85rem 1.5rem;
  font-weight: 700;
  font-size: 1rem;
  letter-spacing: 0.2px;
  box-shadow: 0 6px 18px rgba(122, 20, 24, 0.25);
  transition: all 0.22s ease-in-out;
}

.reg-btn-submit:hover {
  background: linear-gradient(135deg, #641014 0%, #7A1418 100%);
  color: #ffffff;
  transform: translateY(-2px);
  box-shadow: 0 10px 24px rgba(122, 20, 24, 0.32);
}

.reg-helper-card {
  background: #fdf8f8;
  border: 1px dashed #fecaca;
  border-radius: 14px;
  padding: 0.85rem 1.1rem;
}
</style>

<div class="reg-auth-wrapper">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-10 col-lg-8">
        
        <?= Flash::render() ?>

        <div class="reg-auth-card mb-4">
          
          <!-- Top Branding Header -->
          <div class="reg-auth-header">
            <div class="d-flex justify-content-center align-items-center mb-2.5">
              <img src="assets/images/logo/NVTI_logo_dark.png" 
                   onerror="this.onerror=null; this.src='assets/images/logo/NVTI_logo.png';" 
                   alt="NVTI Logo" 
                   class="reg-brand-logo">
            </div>
            <div>
              <span class="reg-badge-nvq">
                <i class="bi bi-mortarboard-fill me-1"></i> NVQ Level 5 &bull; ICT National Diploma
              </span>
            </div>
            <h4 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.3px;">Student Registration</h4>
            <p class="text-muted small mb-0">National Vocational Training Institute &bull; Baddegama</p>
          </div>

          <!-- Form Body -->
          <div class="p-4 p-md-5">
            <?php $old = $oldReg ?? []; ?>
            <div id="regClientAlert" class="alert alert-danger rounded-4 shadow-sm d-none mb-4 p-3 small" role="alert" style="background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid #dc2626; color: #991b1b;">
              <div class="d-flex align-items-start gap-2.5">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 32px; height: 32px; background: #fee2e2; color: #dc2626;">
                  <i class="bi bi-exclamation-octagon-fill fs-6"></i>
                </div>
                <div>
                  <strong class="d-block mb-0.5">Action Required</strong>
                  <span id="regClientAlertMsg" class="text-dark"></span>
                </div>
              </div>
            </div>

            <form action="index.php?page=register_process" method="POST" autocomplete="off" id="studentRegisterForm" novalidate>
              <?= Csrf::field() ?>

              <!-- Hidden Course Binding -->
              <input type="hidden" name="course_id" value="<?= (int)($course['id'] ?? 1) ?>">

              <!-- Active Batch Assignment Notice / Selector -->
              <?php if (!empty($activeBatches) && count($activeBatches) > 1): ?>
                <div class="mb-4">
                  <label class="form-label fw-semibold small text-secondary">Select Active Academic Batch *</label>
                  <select name="class_id" class="form-select reg-form-control" required>
                    <?php foreach ($activeBatches as $ab): ?>
                      <option value="<?= (int)$ab['id'] ?>" <?= ((int)($old['class_id'] ?? 0) === (int)$ab['id']) ? 'selected' : '' ?>>
                        <?= e($ab['class_name']) ?> (<?= e($ab['academic_year']) ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php else: ?>
                <input type="hidden" name="class_id" value="<?= (int)($class['id'] ?? 0) ?>">
                <div class="alert border-0 rounded-4 p-3 mb-4 d-flex align-items-center gap-2.5" style="background: #f0fdf4; border-left: 4px solid #10b981 !important;">
                  <i class="bi bi-broadcast text-success fs-5"></i>
                  <div class="small">
                    <span class="text-muted">Enrolling Into Active Batch:</span>
                    <strong class="text-dark ms-1"><?= e($class['class_name'] ?? 'Current Active Intake') ?></strong>
                  </div>
                </div>
              <?php endif; ?>

              <!-- SECTION 1: PERSONAL INFORMATION -->
              <div class="mb-4 pb-2">
                <div class="d-flex align-items-center gap-2 mb-3.5 pb-2 border-bottom">
                  <span class="reg-section-badge">1</span>
                  <span class="reg-section-title">Personal Details</span>
                </div>

                <div class="row g-3">
                  <!-- Full Name -->
                  <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold small text-secondary">Full Name (with initials) *</label>
                    <div class="input-group reg-input-group">
                      <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                      <input type="text" name="name" id="regName" class="form-control" placeholder="e.g. Kasun Madushanka Silva" value="<?= e($old['name'] ?? '') ?>" required autofocus>
                    </div>
                  </div>

                  <!-- National Identity Card (NIC) -->
                  <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold small text-secondary">National Identity Card (NIC) *</label>
                    <div class="input-group reg-input-group">
                      <span class="input-group-text"><i class="bi bi-card-heading"></i></span>
                      <input type="text" name="nic" id="regNic" class="form-control text-uppercase" placeholder="e.g. 200112345678 or 991234567V" value="<?= e($old['nic'] ?? '') ?>" required>
                    </div>
                    <div id="nicLiveStatus" class="small mt-1 d-none" style="font-size: 0.76rem;"></div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">12-digit new NIC or 9-digit old NIC with V/X.</small>
                  </div>

                  <!-- Mobile Phone -->
                  <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold small text-secondary">Mobile Phone Number *</label>
                    <div class="input-group reg-input-group">
                      <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                      <input type="tel" name="phone" id="phoneInput" class="form-control" placeholder="e.g. 0771234567" value="<?= e($old['phone'] ?? '') ?>" required>
                    </div>
                    <div id="phoneLiveStatus" class="small mt-1 d-none" style="font-size: 0.76rem;"></div>
                  </div>

                  <!-- WhatsApp Number -->
                  <div class="col-12 col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <label class="form-label fw-semibold small text-secondary mb-0">WhatsApp Number</label>
                      <button type="button" class="btn btn-link p-0 text-decoration-none small text-danger fw-semibold" style="font-size: 0.72rem;" onclick="copyPhoneToWhatsapp()">
                        <i class="bi bi-arrow-repeat me-1"></i>Same as mobile
                      </button>
                    </div>
                    <div class="input-group reg-input-group">
                      <span class="input-group-text text-success"><i class="bi bi-whatsapp"></i></span>
                      <input type="tel" name="whatsapp_number" id="whatsappInput" class="form-control" placeholder="e.g. 0771234567" value="<?= e($old['whatsapp_number'] ?? '') ?>">
                    </div>
                  </div>

                  <!-- Residential Address -->
                  <div class="col-12">
                    <label class="form-label fw-semibold small text-secondary">Residential / Postal Address</label>
                    <textarea name="address" id="regAddress" class="form-control reg-form-control" rows="2" placeholder="Street, Village / Town, City"><?= e($old['address'] ?? '') ?></textarea>
                  </div>
                </div>
              </div>

              <!-- SECTION 2: PORTAL LOGIN CREDENTIALS -->
              <div class="mb-4 pb-2">
                <div class="d-flex align-items-center gap-2 mb-3.5 pb-2 border-bottom">
                  <span class="reg-section-badge">2</span>
                  <span class="reg-section-title">Portal Account Credentials</span>
                </div>

                <div class="row g-3">
                  <!-- Email Address -->
                  <div class="col-12">
                    <label class="form-label fw-semibold small text-secondary">Login Email Address *</label>
                    <div class="input-group reg-input-group">
                      <span class="input-group-text"><i class="bi bi-envelope-at-fill"></i></span>
                      <input type="email" name="email" id="regEmail" class="form-control" placeholder="e.g. student@gmail.com" value="<?= e($old['email'] ?? '') ?>" required>
                    </div>
                    <div id="emailLiveStatus" class="small mt-1 d-none" style="font-size: 0.76rem;"></div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Approval notification and login details will be sent to this email.</small>
                  </div>

                  <!-- Create Password -->
                  <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold small text-secondary">Create Password *</label>
                    <div class="input-group reg-input-group">
                      <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                      <input type="password" name="password" id="regPassword" class="form-control" placeholder="e.g. Kasun@2026" minlength="8" required oninput="checkPasswordRules()">
                      <button class="btn btn-light border border-start-0" type="button" onclick="togglePassword('regPassword', 'toggleIcon1')" style="border-radius: 0 14px 14px 0 !important;">
                        <i class="bi bi-eye" id="toggleIcon1"></i>
                      </button>
                    </div>
                  </div>

                  <!-- Confirm Password -->
                  <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold small text-secondary">Confirm Password *</label>
                    <div class="input-group reg-input-group">
                      <span class="input-group-text"><i class="bi bi-shield-lock-fill"></i></span>
                      <input type="password" name="confirm_password" id="regConfirmPassword" class="form-control" placeholder="Re-enter password" minlength="8" required oninput="checkPasswordMatch()">
                      <button class="btn btn-light border border-start-0" type="button" onclick="togglePassword('regConfirmPassword', 'toggleIcon2')" style="border-radius: 0 14px 14px 0 !important;">
                        <i class="bi bi-eye" id="toggleIcon2"></i>
                      </button>
                    </div>
                    <div id="regMatchMsg" class="small mt-1 d-none" style="font-size: 0.75rem;"></div>
                  </div>
                </div>

                <!-- Live Interactive Password Format Checklist (JS) -->
                <div class="reg-helper-card mt-3">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-dark small d-flex align-items-center gap-1.5">
                      <i class="bi bi-shield-check text-danger fs-6"></i> Password Format Requirements
                    </span>
                    <span id="regStrengthBadge" class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size: 0.7rem;">Enter Password</span>
                  </div>
                  <div class="row g-1.5 small" style="font-size: 0.78rem;">
                    <div class="col-12 col-sm-6" id="ruleLen">
                      <i class="bi bi-circle text-muted me-1"></i> <span class="text-muted">Minimum 8 characters</span>
                    </div>
                    <div class="col-12 col-sm-6" id="ruleUpper">
                      <i class="bi bi-circle text-muted me-1"></i> <span class="text-muted">One uppercase letter (A-Z)</span>
                    </div>
                    <div class="col-12 col-sm-6" id="ruleLower">
                      <i class="bi bi-circle text-muted me-1"></i> <span class="text-muted">One lowercase letter (a-z)</span>
                    </div>
                    <div class="col-12 col-sm-6" id="ruleDigit">
                      <i class="bi bi-circle text-muted me-1"></i> <span class="text-muted">At least one number (0-9)</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- SUBMIT BUTTON -->
              <div class="pt-2">
                <button type="submit" class="btn reg-btn-submit w-100 d-flex align-items-center justify-content-center gap-2 mb-3">
                  <i class="bi bi-person-check-fill fs-5"></i>
                  <span>Submit Registration for Approval</span>
                  <i class="bi bi-arrow-right fs-5"></i>
                </button>
              </div>

              <!-- Return to Login Link -->
              <div class="text-center pt-3 border-top">
                <span class="text-muted small">Already have an account? </span>
                <a href="index.php?page=login" class="small fw-bold text-decoration-none" style="color: #7A1418;">
                  <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Portal
                </a>
              </div>

            </form>
          </div>
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

function updateRuleState(elId, passed, label) {
  const el = document.getElementById(elId);
  if (!el) return;
  if (passed) {
    el.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i> <span class="text-success fw-semibold">' + label + '</span>';
  } else {
    el.innerHTML = '<i class="bi bi-circle text-muted me-1"></i> <span class="text-muted">' + label + '</span>';
  }
}

function checkPasswordRules() {
  const pwd = document.getElementById('regPassword').value;
  const hasLen = pwd.length >= 8;
  const hasUpper = /[A-Z]/.test(pwd);
  const hasLower = /[a-z]/.test(pwd);
  const hasDigit = /[0-9]/.test(pwd);

  updateRuleState('ruleLen', hasLen, 'Minimum 8 characters');
  updateRuleState('ruleUpper', hasUpper, 'One uppercase letter (A-Z)');
  updateRuleState('ruleLower', hasLower, 'One lowercase letter (a-z)');
  updateRuleState('ruleDigit', hasDigit, 'At least one number (0-9)');

  const passedCount = [hasLen, hasUpper, hasLower, hasDigit].filter(Boolean).length;
  const badge = document.getElementById('regStrengthBadge');
  if (!pwd) {
    badge.className = 'badge bg-secondary-subtle text-secondary rounded-pill';
    badge.textContent = 'Enter Password';
  } else if (passedCount === 4) {
    badge.className = 'badge bg-success text-white rounded-pill';
    badge.textContent = 'Strong & Valid';
  } else if (passedCount >= 2) {
    badge.className = 'badge bg-warning text-dark rounded-pill';
    badge.textContent = 'Almost There (' + passedCount + '/4)';
  } else {
    badge.className = 'badge bg-danger text-white rounded-pill';
    badge.textContent = 'Weak (' + passedCount + '/4)';
  }

  checkPasswordMatch();
  return passedCount === 4;
}

function checkPasswordMatch() {
  const pwd = document.getElementById('regPassword').value;
  const cpwd = document.getElementById('regConfirmPassword').value;
  const msg = document.getElementById('regMatchMsg');
  if (!cpwd) {
    msg.classList.add('d-none');
    return false;
  }
  msg.classList.remove('d-none');
  if (pwd === cpwd) {
    msg.innerHTML = '<span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Passwords match</span>';
    return true;
  } else {
    msg.innerHTML = '<span class="text-danger fw-semibold"><i class="bi bi-x-circle-fill me-1"></i>Passwords do not match</span>';
    return false;
  }
}

const duplicateState = {
  nic: { exists: false, message: '' },
  phone: { exists: false, message: '' },
  email: { exists: false, message: '' }
};

function setupLiveDuplicateCheck(inputId, statusId, fieldKey, isReadyFn) {
  const input = document.getElementById(inputId);
  const statusEl = document.getElementById(statusId);
  if (!input || !statusEl) return;

  let timer = null;
  let lastChecked = '';

  function renderStatus(state, text) {
    if (state === 'idle') {
      statusEl.className = 'small mt-1 d-none';
      statusEl.textContent = '';
      input.classList.remove('is-invalid', 'is-valid');
      return;
    }
    if (state === 'error') {
      statusEl.className = 'small mt-1.5 px-2.5 py-1.5 rounded-3 d-flex align-items-start gap-1.5 fw-semibold';
      statusEl.style.cssText = 'font-size: 0.76rem; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;';
      statusEl.innerHTML = '<i class="bi bi-x-circle-fill flex-shrink-0 mt-0.5"></i><span>' + text + '</span>';
      input.classList.add('is-invalid');
      input.classList.remove('is-valid');
      return;
    }
    if (state === 'ok') {
      statusEl.className = 'small mt-1 d-flex align-items-center gap-1 text-success fw-semibold';
      statusEl.style.cssText = 'font-size: 0.74rem;';
      statusEl.innerHTML = '<i class="bi bi-check-circle-fill"></i><span>Available</span>';
      input.classList.remove('is-invalid');
      input.classList.add('is-valid');
    }
  }

  async function runCheck() {
    const val = input.value.trim();
    if (!val || !isReadyFn(val)) {
      duplicateState[fieldKey] = { exists: false, message: '' };
      lastChecked = '';
      renderStatus('idle', '');
      return;
    }
    if (val === lastChecked) return;
    lastChecked = val;

    try {
      const resp = await fetch('index.php?page=ajax_check_register&field=' + encodeURIComponent(fieldKey) + '&value=' + encodeURIComponent(val));
      const data = await resp.json();
      if (input.value.trim() !== val) return;

      if (data && data.exists) {
        duplicateState[fieldKey] = { exists: true, message: data.message };
        renderStatus('error', data.message);
      } else {
        duplicateState[fieldKey] = { exists: false, message: '' };
        renderStatus('ok', '');
      }
    } catch (err) {
      duplicateState[fieldKey] = { exists: false, message: '' };
    }
  }

  input.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(runCheck, 350);
  });
  input.addEventListener('blur', function () {
    clearTimeout(timer);
    runCheck();
  });
  if (input.value.trim()) {
    runCheck();
  }
}

setupLiveDuplicateCheck('regNic', 'nicLiveStatus', 'nic', (v) => /^([0-9]{9}[VvXx]|[0-9]{12})$/.test(v.toUpperCase()));
setupLiveDuplicateCheck('phoneInput', 'phoneLiveStatus', 'phone', (v) => v.replace(/[^0-9]/g, '').length >= 9);
setupLiveDuplicateCheck('regEmail', 'emailLiveStatus', 'email', (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v));

// Client-side validation to prevent page refresh & lost inputs!
document.getElementById('studentRegisterForm')?.addEventListener('submit', function (e) {
  const alertBox = document.getElementById('regClientAlert');
  const alertMsg = document.getElementById('regClientAlertMsg');
  const errors = [];

  const nameEl = document.getElementById('regName');
  const nicEl = document.getElementById('regNic');
  const phoneEl = document.getElementById('phoneInput');
  const emailEl = document.getElementById('regEmail');
  const pwdEl = document.getElementById('regPassword');
  const cpwdEl = document.getElementById('regConfirmPassword');

  [nameEl, nicEl, phoneEl, emailEl, pwdEl, cpwdEl].forEach(el => el?.classList.remove('is-invalid'));

  if (!nameEl.value.trim()) {
    errors.push('Full Name is required.');
    nameEl.classList.add('is-invalid');
  }
  const nicVal = nicEl.value.trim().toUpperCase();
  if (!nicVal || !/^([0-9]{9}[VX]|[0-9]{12})$/.test(nicVal)) {
    errors.push('Please enter a valid 12-digit NIC or 9-digit NIC ending in V/X.');
    nicEl.classList.add('is-invalid');
  } else if (duplicateState.nic.exists) {
    errors.push(duplicateState.nic.message);
    nicEl.classList.add('is-invalid');
  }
  if (!phoneEl.value.trim() || phoneEl.value.replace(/[^0-9]/g, '').length < 9) {
    errors.push('Please enter a valid mobile phone number.');
    phoneEl.classList.add('is-invalid');
  } else if (duplicateState.phone.exists) {
    errors.push(duplicateState.phone.message);
    phoneEl.classList.add('is-invalid');
  }
  const emailVal = emailEl.value.trim();
  if (!emailVal || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
    errors.push('Please enter a valid email address.');
    emailEl.classList.add('is-invalid');
  } else if (duplicateState.email.exists) {
    errors.push(duplicateState.email.message);
    emailEl.classList.add('is-invalid');
  }
  if (!checkPasswordRules()) {
    errors.push('Password must be at least 8 characters and include 1 uppercase, 1 lowercase, and 1 number.');
    pwdEl.classList.add('is-invalid');
  }
  if (pwdEl.value !== cpwdEl.value) {
    errors.push('Password confirmation does not match.');
    cpwdEl.classList.add('is-invalid');
  }

  if (errors.length > 0) {
    e.preventDefault();
    alertMsg.innerHTML = errors.join('<br>');
    alertBox.classList.remove('d-none');
    alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
  } else {
    alertBox.classList.add('d-none');
  }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
