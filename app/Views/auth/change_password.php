<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="app-wrapper">
  <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <div class="container-fluid" style="max-width: 650px;">
      <?= Flash::render() ?>

      <div class="card border rounded-4 shadow-sm overflow-hidden bg-white">
        <div class="card-header bg-white py-3.5 px-4 border-bottom">
          <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-lock me-2 text-primary"></i>Change Password</h5>
          <small class="text-muted">Keep your account secure with a strong password</small>
        </div>

        <div class="card-body p-4">
          <form action="index.php?page=change_password_process" method="POST">
            <?= Csrf::field() ?>

            <div class="mb-3">
              <label class="form-label fw-semibold small text-secondary">Current Password *</label>
              <input type="password" name="current_password" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold small text-secondary">New Password *</label>
              <input type="password" name="new_password" class="form-control" placeholder="At least 6 characters" required>
            </div>

            <div class="mb-4">
              <label class="form-label fw-semibold small text-secondary">Confirm New Password *</label>
              <input type="password" name="confirm_password" class="form-control" placeholder="Re-type new password" required>
            </div>

            <div class="d-flex justify-content-end gap-2">
              <a href="index.php" class="btn btn-outline-secondary px-3">Cancel</a>
              <button type="submit" class="btn btn-primary px-4 fw-bold">
                <i class="bi bi-check-lg me-1"></i> Update Password
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
