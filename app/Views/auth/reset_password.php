<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
      <?= Flash::render() ?>

      <div class="card border rounded-4 shadow-sm overflow-hidden bg-white">
        <div class="card-header bg-white text-center py-4 border-bottom">
          <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-2" style="width: 52px; height: 52px;">
            <i class="bi bi-shield-lock-fill fs-3"></i>
          </div>
          <h4 class="fw-bold mb-1 text-dark">Create New Password</h4>
          <p class="text-muted small mb-0">Enter your new secure password below</p>
        </div>

        <div class="card-body p-4">
          <form action="index.php?page=reset_password_process" method="POST">
            <?= Csrf::field() ?>
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">

            <div class="mb-3">
              <label for="password" class="form-label fw-semibold text-secondary small">New Password</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock-fill"></i></span>
                <input type="password" class="form-control border-start-0 border-end-0" id="password" name="password" required autofocus placeholder="Min 8 chars, 1 uppercase, 1 digit">
                <button class="btn btn-light border border-start-0 text-secondary" type="button" data-toggle-password tabindex="-1" title="Show/Hide Password" aria-label="Show password">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
              <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Must be at least 8 characters with uppercase, lowercase, and a number.</small>
            </div>

            <div class="mb-4">
              <label for="confirm_password" class="form-label fw-semibold text-secondary small">Confirm New Password</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock-check"></i></span>
                <input type="password" class="form-control border-start-0 border-end-0" id="confirm_password" name="confirm_password" required placeholder="Repeat new password">
                <button class="btn btn-light border border-start-0 text-secondary" type="button" data-toggle-password tabindex="-1" title="Show/Hide Password" aria-label="Show password">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3 mb-3">
              <i class="bi bi-check2-circle me-1"></i> Update &amp; Secure My Password
            </button>

            <div class="text-center">
              <a href="index.php?page=login" class="text-decoration-none small text-muted">
                <i class="bi bi-arrow-left me-1"></i> Back to Login
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
