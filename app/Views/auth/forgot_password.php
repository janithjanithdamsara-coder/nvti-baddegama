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
          <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-2" style="width: 52px; height: 52px;">
            <i class="bi bi-key-fill fs-3"></i>
          </div>
          <h4 class="fw-bold mb-1 text-dark">Reset Password</h4>
          <p class="text-muted small mb-0">Enter your email to receive recovery instructions</p>
        </div>

        <div class="card-body p-4">
          <form action="index.php?page=forgot_password_process" method="POST">
            <?= Csrf::field() ?>

            <div class="mb-4">
              <label for="resetEmail" class="form-label fw-semibold text-secondary small">Registered Email Address</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control border-start-0" id="resetEmail" name="email" placeholder="student@nvti.edu.lk" required autofocus>
              </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3 mb-3">
              <i class="bi bi-send-fill me-1"></i> Send Password Reset Link
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
