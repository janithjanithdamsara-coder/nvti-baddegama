<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-9 col-lg-7">
      <?= Flash::render() ?>

      <div class="card border rounded-4 shadow-sm overflow-hidden mb-4 bg-white">
        <div class="card-header bg-white text-center py-4 border-bottom">
          <img src="assets/images/logo/NVTI_logo.png" onerror="this.onerror=null; this.src='https://nvtibaddegama.site/images/logo/NVTI_logo.png';" alt="NVTI Logo" style="height: 48px; width: auto;" class="mb-2">
          <h4 class="fw-bold mb-1 text-dark">First-Time Student Activation</h4>
          <p class="text-muted small mb-0">Set your student password &amp; access your NVTI Diploma Portal</p>
        </div>

        <div class="card-body p-4 p-md-5">
          <div class="alert alert-info border-0 rounded-3 small mb-4">
            <i class="bi bi-info-circle-fill me-1"></i> If you were selected for the <strong>National Diploma in ICT</strong>, please enter your official <strong>MIS Number</strong> (or NIC) to set up your password.
          </div>

          <form action="index.php?page=activate_process" method="POST" autocomplete="off">
            <?= Csrf::field() ?>

            <div class="mb-3">
              <label for="identifier" class="form-label fw-semibold text-secondary small">MIS Number or NIC Number *</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person-vcard-fill"></i></span>
                <input type="text" class="form-control border-start-0" id="identifier" name="identifier" placeholder="e.g. MIS/ICT/2026/001 or 200125409876" required autofocus>
              </div>
              <small class="text-muted" style="font-size: 0.75rem;">Provided upon selection to the NVTI Baddegama Diploma batch.</small>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label fw-semibold text-secondary small">Create Your Portal Password *</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock-fill"></i></span>
                <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="At least 8 characters" minlength="8" required>
              </div>
            </div>

            <div class="mb-4">
              <label for="confirm_password" class="form-label fw-semibold text-secondary small">Confirm Password *</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-shield-lock-fill"></i></span>
                <input type="password" class="form-control border-start-0" id="confirm_password" name="confirm_password" placeholder="Re-type your password" minlength="8" required>
              </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3 shadow-sm mb-3">
              <i class="bi bi-check2-circle me-2"></i> Activate Account &amp; Sign In
            </button>
          </form>

          <div class="text-center pt-2 border-top">
            <span class="text-muted small">Already activated your password? </span>
            <a href="index.php?page=login" class="small fw-bold text-decoration-none text-primary">
              <i class="bi bi-box-arrow-in-right me-1"></i> Return to Portal Sign In
            </a>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
