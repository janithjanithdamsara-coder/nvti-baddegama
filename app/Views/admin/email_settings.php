<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

$isConfigured = defined('SMTP_PASS') && !empty(SMTP_PASS) && SMTP_PASS !== 'YOUR_BREVO_MASTER_SMTP_KEY' && defined('SMTP_USER') && SMTP_USER !== 'YOUR_GMAIL_ADDRESS@gmail.com' && !empty(SMTP_USER);
$mailService = (strpos(SMTP_HOST, 'gmail') !== false) ? 'Google Gmail SMTP' : 'Brevo (Sendinblue)';
?>

<div class="app-wrapper">
  <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <div class="container-fluid">
      <?= Flash::render() ?>

      <!-- Header Banner -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
          <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-envelope-at text-primary me-2"></i>Email &amp; SMTP Settings
          </h4>
          <p class="text-muted small mb-0">Automated transactional email delivery for student welcome notices, admissions, and password resets</p>
        </div>
        <div>
          <?php if ($isConfigured): ?>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fs-6 fw-semibold">
              <i class="bi bi-check-circle-fill me-1"></i> <?= htmlspecialchars($mailService) ?> Configured
            </span>
          <?php else: ?>
            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill fs-6 fw-semibold">
              <i class="bi bi-exclamation-triangle-fill me-1"></i> Setup Required
            </span>
          <?php endif; ?>
        </div>
      </div>

      <div class="row g-4">
        <!-- Configuration Overview -->
        <div class="col-lg-7">
          <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
              <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-sliders me-2 text-primary"></i>Current Mail Configuration
              </h5>
              <span class="badge bg-primary-subtle text-primary fw-medium px-2.5 py-1">config/config.php</span>
            </div>
            <div class="card-body p-4">
              <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0 align-middle">
                  <tbody>
                    <tr>
                      <th class="bg-light text-secondary" style="width: 35%;">Mail Service</th>
                      <td>
                        <span class="fw-bold text-dark"><i class="bi bi-send-check text-success me-1"></i> <?= htmlspecialchars($mailService) ?></span>
                      </td>
                    </tr>
                    <tr>
                      <th class="bg-light text-secondary">SMTP Host</th>
                      <td><code><?= htmlspecialchars(SMTP_HOST) ?></code></td>
                    </tr>
                    <tr>
                      <th class="bg-light text-secondary">SMTP Port &amp; Security</th>
                      <td>
                        <span class="badge bg-info-subtle text-info me-2">Port <?= (int)SMTP_PORT ?></span>
                        <span class="badge bg-secondary-subtle text-secondary uppercase"><?= strtoupper(SMTP_ENCRYPTION) ?></span>
                      </td>
                    </tr>
                    <tr>
                      <th class="bg-light text-secondary">SMTP Login User</th>
                      <td>
                        <?php if (SMTP_USER === 'YOUR_GMAIL_ADDRESS@gmail.com' || empty(SMTP_USER)): ?>
                          <span class="text-danger fw-semibold">Enter your Gmail address in config.php</span>
                        <?php else: ?>
                          <span class="fw-semibold text-dark"><?= htmlspecialchars(SMTP_USER) ?></span>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <tr>
                      <th class="bg-light text-secondary">App Password / Key</th>
                      <td>
                        <?php if (!empty(SMTP_PASS)): ?>
                          <span class="text-success fw-semibold"><i class="bi bi-key-fill me-1"></i> &#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022; (Configured)</span>
                        <?php else: ?>
                          <span class="text-danger fw-semibold">Missing</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <tr>
                      <th class="bg-light text-secondary">Sender (From) Email</th>
                      <td>
                        <code><?= htmlspecialchars(MAIL_FROM_EMAIL) ?></code>
                      </td>
                    </tr>
                    <tr>
                      <th class="bg-light text-secondary">Sender Name</th>
                      <td><?= htmlspecialchars(MAIL_FROM_NAME) ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Step by step guide -->
          <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 px-4 border-bottom">
              <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-info-circle me-2 text-primary"></i>How to get your Brevo SMTP Credentials
              </h5>
            </div>
            <div class="card-body p-4">
              <ol class="mb-0 text-secondary lh-lg ps-3">
                <li>Log in to your <strong><a href="https://app.brevo.com" target="_blank" class="text-primary text-decoration-none fw-semibold">Brevo Account <i class="bi bi-box-arrow-up-right small"></i></a></strong>.</li>
                <li>In top right profile menu, click <strong>SMTP &amp; API</strong>.</li>
                <li>Go to the <strong>SMTP</strong> tab. You will see your <strong>SMTP Server</strong> (<code>smtp-relay.brevo.com</code>), <strong>Port</strong> (<code>465</code> / <code>587</code>), and <strong>Login</strong> email.</li>
                <li>Click <strong>&quot;Generate a new SMTP key&quot;</strong>, name it (e.g. <code>NVTI Diploma</code>), and copy the generated key.</li>
                <li>Go to <strong>Senders, Domains &amp; Dedicated IPs</strong> in Brevo and verify that your Sender Email (e.g. your email or <code>noreply@nvtibaddegama.site</code>) is listed as an <strong>Active / Verified Sender</strong>.</li>
                <li>Open <code>config/config.php</code> in this project and paste your Brevo login and SMTP key!</li>
              </ol>
            </div>
          </div>
        </div>

        <!-- Test Email Form & Actions -->
        <div class="col-lg-5">
          <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom">
              <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-send me-2 text-success"></i>Test Email Delivery
              </h5>
            </div>
            <div class="card-body p-4">
              <p class="text-muted small">
                Send a real test email through Brevo SMTP to verify connectivity and ensure your inbox receives notifications without being marked as spam.
              </p>

              <form action="index.php?page=admin_test_email" method="POST">
                <?= Csrf::field() ?>
                
                <div class="mb-3">
                  <label class="form-label fw-semibold small text-secondary">Recipient Email Address</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="test_email" class="form-control" placeholder="your-email@gmail.com" required value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>">
                  </div>
                  <small class="text-muted">Enter an email where you can check the received test message.</small>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-3 fw-semibold">
                  <i class="bi bi-send-fill me-2"></i>Send Test Email Now
                </button>
              </form>
            </div>
          </div>

          <!-- Where are emails sent in National Diploma Portal -->
          <div class="card border-0 shadow-sm rounded-4 bg-light">
            <div class="card-body p-4">
              <h6 class="fw-bold text-dark mb-2"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Automated Emails in LMS</h6>
              <ul class="list-unstyled mb-0 small text-secondary">
                <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i><strong>Password Reset:</strong> Secure reset links &amp; tokens sent to students and lecturers.</li>
                <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i><strong>Student Welcome:</strong> Auto-sends login credentials when a new student is enrolled.</li>
                <li class="mb-0"><i class="bi bi-check2 text-success me-2"></i><strong>Lecturer Onboarding:</strong> Notifies new teachers with their temporary password.</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
