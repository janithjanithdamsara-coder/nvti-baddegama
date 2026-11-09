<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="app-wrapper">
  <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <div class="container-fluid">
      <?= Flash::render() ?>

      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
          <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-shield-check text-primary me-2"></i>Security & Audit Trail Logs</h4>
          <p class="text-muted small mb-0">Track all user logins, authentication attempts, file operations and administrative actions</p>
        </div>
      </div>

      <div class="row g-4">
        <!-- Audit Logs -->
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white py-3 px-4 border-bottom">
              <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-journal-text me-2 text-primary"></i>System Audit Trail</h5>
            </div>
            <div class="table-responsive">
              <table class="table table-hover table-custom mb-0">
                <thead>
                  <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Details</th>
                    <th>IP Address</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($auditLogs)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">No audit logs found.</td></tr>
                  <?php else: ?>
                    <?php foreach ($auditLogs as $log): ?>
                      <tr>
                        <td class="small text-muted"><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                        <td>
                          <div class="fw-semibold small text-dark"><?= e($log['user_name'] ?? 'System') ?></div>
                          <small class="text-muted"><?= e($log['user_email'] ?? '') ?></small>
                        </td>
                        <td><span class="badge bg-dark-subtle text-dark"><?= e($log['action']) ?></span></td>
                        <td class="small text-secondary"><?= e($log['details']) ?></td>
                        <td><span class="badge bg-light text-muted border"><?= e($log['ip_address'] ?? '127.0.0.1') ?></span></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Login History -->
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white py-3 px-4 border-bottom">
              <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-fingerprint me-2 text-danger"></i>Login Attempts</h5>
            </div>
            <div class="card-body p-3">
              <?php if (empty($loginLogs)): ?>
                <p class="text-muted text-center py-4">No login logs recorded.</p>
              <?php else: ?>
                <div class="list-group list-group-flush">
                  <?php foreach ($loginLogs as $ll): ?>
                    <div class="list-group-item px-0 py-2 border-bottom">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="small text-dark text-truncate" style="max-width: 170px;"><?= e($ll['email']) ?></strong>
                        <?php if ($ll['status'] === 'success'): ?>
                          <span class="badge bg-success-subtle text-success">Success</span>
                        <?php else: ?>
                          <span class="badge bg-danger-subtle text-danger">Failed</span>
                        <?php endif; ?>
                      </div>
                      <div class="d-flex justify-content-between text-muted" style="font-size: 0.72rem;">
                        <span>IP: <?= e($ll['ip_address']) ?></span>
                        <span><?= date('M d, H:i:s', strtotime($ll['created_at'])) ?></span>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
