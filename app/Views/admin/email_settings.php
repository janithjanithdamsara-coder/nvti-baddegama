<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

$isConfigured = defined('SMTP_PASS') && !empty(SMTP_PASS) && SMTP_PASS !== 'YOUR_BREVO_MASTER_SMTP_KEY' && defined('SMTP_USER') && SMTP_USER !== 'YOUR_GMAIL_ADDRESS@gmail.com' && !empty(SMTP_USER);
$mailService = (strpos(SMTP_HOST, 'gmail') !== false) ? 'Google Gmail SMTP' : 'Brevo (Sendinblue)';

$emailSettings = $emailSettings ?? EmailSettings::getAll();
$masterEnabled = ($emailSettings['mail_master_enabled'] ?? '1') === '1';
$classes       = $classes ?? [];
$students      = $students ?? [];
$teachers      = $teachers ?? [];
$emailLogs     = $emailLogs ?? [];
$logFilter     = $logFilter ?? '';
$logStats      = $logStats ?? ['sent' => 0, 'skipped' => 0, 'failed' => 0, 'total' => 0];
?>

<div class="app-wrapper">
  <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <div class="container-fluid">
      <?= Flash::render() ?>

      <!-- Header Banner -->
      <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
          <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-envelope-at text-primary me-2"></i>Email Control Center &amp; SMTP Settings
          </h4>
          <p class="text-muted small mb-0">Toggle automated email notifications ON/OFF, send selective emails when needed, and review delivery history.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <?php if ($masterEnabled): ?>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold">
              <i class="bi bi-broadcast me-1"></i> Auto Emails: ON
            </span>
          <?php else: ?>
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-semibold">
              <i class="bi bi-pause-circle-fill me-1"></i> Auto Emails: PAUSED
            </span>
          <?php endif; ?>

          <?php if ($isConfigured): ?>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold">
              <i class="bi bi-check-circle-fill me-1"></i> <?= htmlspecialchars($mailService) ?>
            </span>
          <?php else: ?>
            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill fw-semibold">
              <i class="bi bi-exclamation-triangle-fill me-1"></i> SMTP Setup Required
            </span>
          <?php endif; ?>

          <a href="#emailHistorySection" class="badge bg-light text-dark border px-3 py-2 rounded-pill text-decoration-none fw-semibold">
            <i class="bi bi-clock-history me-1 text-secondary"></i>
            Sent: <span class="text-success"><?= (int)$logStats['sent'] ?></span> &bull;
            Skipped: <span class="text-warning"><?= (int)$logStats['skipped'] ?></span> &bull;
            Failed: <span class="text-danger"><?= (int)$logStats['failed'] ?></span>
          </a>
        </div>
      </div>

      <!-- ROW 1: Email Toggles + Custom Selective Email Composer -->
      <div class="row g-4 mb-4">
        <!-- Automated Email Toggles -->
        <div class="col-lg-5">
          <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
              <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-toggles2 me-2 text-primary"></i>Automated Email Controls
              </h5>
              <span class="badge bg-light text-secondary border">On-Demand Control</span>
            </div>
            <div class="card-body p-4">
              <form action="index.php?page=admin_save_email_settings" method="POST">
                <?= Csrf::field() ?>

                <!-- Master Switch -->
                <div class="p-3 rounded-3 border mb-3 <?= $masterEnabled ? 'bg-success-subtle border-success-subtle' : 'bg-danger-subtle border-danger-subtle' ?>">
                  <div class="form-check form-switch d-flex align-items-center justify-content-between ps-0 mb-0">
                    <label class="form-check-label pe-3" for="sw_master" style="cursor:pointer;">
                      <span class="fw-bold d-block text-dark">
                        <i class="bi bi-power me-1 <?= $masterEnabled ? 'text-success' : 'text-danger' ?>"></i>Master Email Switch
                      </span>
                      <span class="text-muted small">Turn OFF to pause all automated portal emails at once.</span>
                    </label>
                    <input class="form-check-input ms-2 flex-shrink-0" type="checkbox" role="switch" id="sw_master" name="mail_master_enabled" value="1" style="width: 2.8em; height: 1.4em; cursor:pointer;" <?= $masterEnabled ? 'checked' : '' ?>>
                  </div>
                </div>

                <!-- Individual Category Toggles -->
                <div class="list-group list-group-flush border rounded-3 mb-3">
                  <label class="list-group-item d-flex align-items-center justify-content-between py-2.5 px-3" style="cursor:pointer;">
                    <span class="pe-3">
                      <span class="fw-semibold text-dark d-block small"><i class="bi bi-journal-arrow-up text-primary me-1"></i>New Study Material Emails</span>
                      <span class="text-muted" style="font-size: 0.74rem;">Email students when notes or videos are posted.</span>
                    </span>
                    <span class="form-check form-switch mb-0">
                      <input class="form-check-input" type="checkbox" role="switch" name="mail_notify_new_material" value="1" <?= ($emailSettings['mail_notify_new_material'] ?? '1') === '1' ? 'checked' : '' ?>>
                    </span>
                  </label>

                  <label class="list-group-item d-flex align-items-center justify-content-between py-2.5 px-3" style="cursor:pointer;">
                    <span class="pe-3">
                      <span class="fw-semibold text-dark d-block small"><i class="bi bi-clipboard-check text-danger me-1"></i>New Assignment Emails</span>
                      <span class="text-muted" style="font-size: 0.74rem;">Email students when coursework &amp; deadlines are created.</span>
                    </span>
                    <span class="form-check form-switch mb-0">
                      <input class="form-check-input" type="checkbox" role="switch" name="mail_notify_new_assignment" value="1" <?= ($emailSettings['mail_notify_new_assignment'] ?? '1') === '1' ? 'checked' : '' ?>>
                    </span>
                  </label>

                  <label class="list-group-item d-flex align-items-center justify-content-between py-2.5 px-3" style="cursor:pointer;">
                    <span class="pe-3">
                      <span class="fw-semibold text-dark d-block small"><i class="bi bi-person-check-fill text-success me-1"></i>Student Approval Emails</span>
                      <span class="text-muted" style="font-size: 0.74rem;">Send confirmation email when approving a pending student.</span>
                    </span>
                    <span class="form-check form-switch mb-0">
                      <input class="form-check-input" type="checkbox" role="switch" name="mail_notify_student_approval" value="1" <?= ($emailSettings['mail_notify_student_approval'] ?? '1') === '1' ? 'checked' : '' ?>>
                    </span>
                  </label>

                  <label class="list-group-item d-flex align-items-center justify-content-between py-2.5 px-3" style="cursor:pointer;">
                    <span class="pe-3">
                      <span class="fw-semibold text-dark d-block small"><i class="bi bi-key-fill text-warning me-1"></i>Password Reset Link Emails</span>
                      <span class="text-muted" style="font-size: 0.74rem;">Send reset token link on Forgot Password requests.</span>
                    </span>
                    <span class="form-check form-switch mb-0">
                      <input class="form-check-input" type="checkbox" role="switch" name="mail_notify_password_reset" value="1" <?= ($emailSettings['mail_notify_password_reset'] ?? '1') === '1' ? 'checked' : '' ?>>
                    </span>
                  </label>

                  <label class="list-group-item d-flex align-items-center justify-content-between py-2.5 px-3" style="cursor:pointer;">
                    <span class="pe-3">
                      <span class="fw-semibold text-dark d-block small"><i class="bi bi-shield-lock-fill text-info me-1"></i>Security &amp; Password Change Alerts</span>
                      <span class="text-muted" style="font-size: 0.74rem;">Notify users when their password is updated.</span>
                    </span>
                    <span class="form-check form-switch mb-0">
                      <input class="form-check-input" type="checkbox" role="switch" name="mail_notify_security_alerts" value="1" <?= ($emailSettings['mail_notify_security_alerts'] ?? '1') === '1' ? 'checked' : '' ?>>
                    </span>
                  </label>

                  <label class="list-group-item d-flex align-items-center justify-content-between py-2.5 px-3 bg-light" style="cursor:pointer;">
                    <span class="pe-3">
                      <span class="fw-semibold text-dark d-block small"><i class="bi bi-check2-square text-secondary me-1"></i>Pre-check &quot;Send Email&quot; in Forms</span>
                      <span class="text-muted" style="font-size: 0.74rem;">If OFF, upload/approve checkboxes stay unchecked until you tick them manually.</span>
                    </span>
                    <span class="form-check form-switch mb-0">
                      <input class="form-check-input" type="checkbox" role="switch" name="mail_action_default_checked" value="1" <?= ($emailSettings['mail_action_default_checked'] ?? '1') === '1' ? 'checked' : '' ?>>
                    </span>
                  </label>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-3 fw-semibold" style="background: #7A1418; border-color: #7A1418;">
                  <i class="bi bi-save2-fill me-2"></i>Save Email Preferences
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- Custom Selective Email Composer -->
        <div class="col-lg-7">
          <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
              <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-send-plus-fill me-2 text-danger"></i>Send Custom / Selective Email
              </h5>
              <span class="badge bg-danger-subtle text-danger fw-medium">Send Only When Needed</span>
            </div>
            <div class="card-body p-4">
              <form action="index.php?page=admin_send_custom_email" method="POST" id="customEmailForm">
                <?= Csrf::field() ?>

                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold small text-secondary">Who should receive this email? *</label>
                    <select name="recipient_mode" id="recipientModeSelect" class="form-select" onchange="toggleRecipientMode(this.value)">
                      <option value="single">Single / Custom Email Address</option>
                      <option value="selected_students">Selected Students (Pick from List)</option>
                      <option value="class">Specific Batch / Class</option>
                      <option value="all_students">All Active Students (<?= count($students) ?>)</option>
                      <option value="all_teachers">All Active Lecturers (<?= count($teachers) ?>)</option>
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold small text-secondary">Quick Message Template (Optional)</label>
                    <select id="emailTemplateSelect" class="form-select" onchange="applyEmailTemplate(this.value)">
                      <option value="">-- Write Custom Message --</option>
                      <option value="exam">Exam / Practical Assessment Notice</option>
                      <option value="schedule">Class Postponed / Schedule Change</option>
                      <option value="docs">Document Verification Reminder</option>
                      <option value="general">General Portal Announcement</option>
                    </select>
                  </div>
                </div>

                <!-- Mode: Single Email -->
                <div id="modeSingleBox" class="mb-3">
                  <label class="form-label fw-semibold small text-secondary">Recipient Email Address *</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="single_email" id="singleEmailInput" class="form-control" placeholder="student@gmail.com">
                  </div>
                </div>

                <!-- Mode: Selected Students Checklist -->
                <div id="modeSelectedStudentsBox" class="mb-3 d-none">
                  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <label class="form-label fw-semibold small text-secondary mb-0">
                      Select Students (<span id="selectedStudentsCount" class="text-primary fw-bold">0</span> selected)
                    </label>
                    <div class="d-flex align-items-center gap-2">
                      <input type="text" id="studentSearchFilter" class="form-control form-control-sm" placeholder="Search name, code, batch..." style="width: 190px;" oninput="filterStudentChecklist(this.value)">
                      <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1" onclick="toggleAllVisibleStudents(true)">All</button>
                      <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1" onclick="toggleAllVisibleStudents(false)">Clear</button>
                    </div>
                  </div>
                  <div class="border rounded-3 p-2 bg-light" style="max-height: 190px; overflow-y: auto;" id="studentChecklistContainer">
                    <?php if (empty($students)): ?>
                      <div class="text-muted small text-center py-3">No active students with email addresses found.</div>
                    <?php else: ?>
                      <?php foreach ($students as $st): ?>
                        <?php $searchBlob = strtolower(($st['name'] ?? '') . ' ' . ($st['email'] ?? '') . ' ' . ($st['student_code'] ?? '') . ' ' . ($st['class_name'] ?? '')); ?>
                        <label class="d-flex align-items-center justify-content-between bg-white border rounded-2 px-2.5 py-1.5 mb-1.5 student-check-row" data-search="<?= htmlspecialchars($searchBlob, ENT_QUOTES, 'UTF-8') ?>" style="cursor:pointer;">
                          <span class="d-flex align-items-center gap-2">
                            <input type="checkbox" class="form-check-input mt-0 custom-student-cb" name="student_ids[]" value="<?= (int)$st['id'] ?>" onchange="updateSelectedStudentCount()">
                            <span class="small fw-semibold text-dark"><?= htmlspecialchars($st['name']) ?></span>
                            <?php if (!empty($st['student_code'])): ?>
                              <span class="badge bg-light text-secondary border font-monospace" style="font-size:0.68rem;"><?= htmlspecialchars($st['student_code']) ?></span>
                            <?php endif; ?>
                          </span>
                          <span class="text-muted small font-monospace" style="font-size:0.74rem;"><?= htmlspecialchars($st['email']) ?></span>
                        </label>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Mode: Specific Batch -->
                <div id="modeClassBox" class="mb-3 d-none">
                  <label class="form-label fw-semibold small text-secondary">Target Batch / Class *</label>
                  <select name="class_id" class="form-select">
                    <option value="">-- Choose Batch / Cohort --</option>
                    <?php foreach ($classes as $cl): ?>
                      <option value="<?= (int)$cl['id'] ?>">
                        <?= htmlspecialchars($cl['class_name']) ?> (<?= htmlspecialchars($cl['academic_year']) ?>) &mdash; <?= (int)$cl['active_student_count'] ?> active student(s)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Mode: All Students / All Teachers Notice -->
                <div id="modeBulkNoticeBox" class="alert alert-info py-2 px-3 small mb-3 d-none">
                  <i class="bi bi-people-fill me-1"></i>
                  <span id="modeBulkNoticeText"></span>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold small text-secondary">Email Subject *</label>
                  <input type="text" name="subject" id="customEmailSubject" class="form-control" placeholder="e.g. Important Notice: Practical Assessment Schedule" required>
                </div>

                <div class="mb-3">
                  <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label fw-semibold small text-secondary mb-1">Message Body *</label>
                    <small class="text-muted" style="font-size: 0.72rem;">Tip: Use <code>{name}</code> to insert the recipient's name automatically.</small>
                  </div>
                  <textarea name="message" id="customEmailMessage" class="form-control" rows="5" placeholder="Dear {name},&#10;&#10;Write your announcement or notice here..." required></textarea>
                </div>

                <?php if (!$masterEnabled): ?>
                  <div class="alert alert-warning py-2 px-3 small d-flex align-items-center justify-content-between mb-3">
                    <span><i class="bi bi-exclamation-triangle-fill me-1"></i> Master Email Switch is <strong>PAUSED</strong>.</span>
                    <label class="form-check-label fw-semibold ms-2 mb-0" style="cursor:pointer;">
                      <input type="checkbox" class="form-check-input me-1" name="override_master" value="1"> Send anyway
                    </label>
                  </div>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-2 justify-content-end">
                  <button type="button" class="btn btn-outline-secondary rounded-3 px-3 fw-semibold" onclick="openCustomEmailPreview()">
                    <i class="bi bi-eye me-1"></i> Preview Email
                  </button>
                  <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold" style="background: #7A1418; border-color: #7A1418;" onclick="return confirmCustomEmailSend();">
                    <i class="bi bi-send-fill me-1"></i> Send Email Now
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- ROW 2: Current SMTP Configuration + Test Email & Summary -->
      <div class="row g-4 mb-4">
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
                        <span class="badge bg-secondary-subtle text-secondary text-uppercase"><?= strtoupper(SMTP_ENCRYPTION) ?></span>
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

        <!-- Test Email Form & Active Status Summary -->
        <div class="col-lg-5">
          <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom">
              <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-send me-2 text-success"></i>Test Email Delivery
              </h5>
            </div>
            <div class="card-body p-4">
              <p class="text-muted small">
                Send a diagnostic test email through your configured SMTP server (works even if automated emails are paused).
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

          <!-- Live Automated Emails Status -->
          <div class="card border-0 shadow-sm rounded-4 bg-light">
            <div class="card-body p-4">
              <h6 class="fw-bold text-dark mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Live Notification Status</h6>
              <ul class="list-unstyled mb-0 small text-secondary">
                <?php
                  $statusItems = [
                    ['label' => 'New Study Materials', 'on' => EmailSettings::isCategoryEnabled('material')],
                    ['label' => 'New Assignments', 'on' => EmailSettings::isCategoryEnabled('assignment')],
                    ['label' => 'Student Approvals', 'on' => EmailSettings::isCategoryEnabled('approval')],
                    ['label' => 'Password Reset Links', 'on' => EmailSettings::isCategoryEnabled('password_reset')],
                    ['label' => 'Security Alerts', 'on' => EmailSettings::isCategoryEnabled('security_alert')],
                  ];
                ?>
                <?php foreach ($statusItems as $item): ?>
                  <li class="mb-2 d-flex justify-content-between align-items-center">
                    <span><?= htmlspecialchars($item['label']) ?></span>
                    <?php if ($item['on']): ?>
                      <span class="badge bg-success-subtle text-success rounded-pill px-2.5">Active</span>
                    <?php else: ?>
                      <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5">Off</span>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- ROW 3: Email Send History & Logs -->
      <div class="card border-0 shadow-sm rounded-4" id="emailHistorySection">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
          <div>
            <h5 class="fw-bold mb-0 text-dark">
              <i class="bi bi-clock-history me-2 text-primary"></i>Email Delivery History
            </h5>
            <small class="text-muted">Recent sent, skipped, and failed email notifications (latest 40 records)</small>
          </div>

          <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="btn-group btn-group-sm" role="group">
              <a href="index.php?page=admin_email_settings#emailHistorySection" class="btn <?= $logFilter === '' ? 'btn-dark' : 'btn-outline-secondary' ?>">All (<?= (int)$logStats['total'] ?>)</a>
              <a href="index.php?page=admin_email_settings&log_status=sent#emailHistorySection" class="btn <?= $logFilter === 'sent' ? 'btn-success' : 'btn-outline-success' ?>">Sent (<?= (int)$logStats['sent'] ?>)</a>
              <a href="index.php?page=admin_email_settings&log_status=skipped#emailHistorySection" class="btn <?= $logFilter === 'skipped' ? 'btn-warning' : 'btn-outline-warning' ?>">Skipped (<?= (int)$logStats['skipped'] ?>)</a>
              <a href="index.php?page=admin_email_settings&log_status=failed#emailHistorySection" class="btn <?= $logFilter === 'failed' ? 'btn-danger' : 'btn-outline-danger' ?>">Failed (<?= (int)$logStats['failed'] ?>)</a>
            </div>

            <?php if ((int)$logStats['total'] > 0): ?>
              <form action="index.php?page=admin_clear_email_logs" method="POST" class="d-inline m-0" onsubmit="return confirm('Clear all email delivery history records?');">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-sm btn-outline-danger rounded-3">
                  <i class="bi bi-trash3 me-1"></i>Clear
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
              <thead class="table-light">
                <tr>
                  <th class="ps-4 py-3">Date / Time</th>
                  <th class="py-3">Recipient</th>
                  <th class="py-3">Type</th>
                  <th class="py-3">Subject &amp; Details</th>
                  <th class="py-3">Status</th>
                  <th class="text-end pe-4 py-3">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($emailLogs)): ?>
                  <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                      <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                      No email logs recorded yet. Send a test or custom email to see delivery history here.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($emailLogs as $log): ?>
                    <tr>
                      <td class="ps-4 py-3 text-muted font-monospace" style="font-size: 0.76rem; white-space: nowrap;">
                        <?= date('M d, Y h:i A', strtotime($log['created_at'])) ?>
                      </td>
                      <td class="py-3">
                        <div class="fw-semibold text-dark"><?= htmlspecialchars($log['recipient_name'] ?: 'Recipient') ?></div>
                        <div class="text-muted small font-monospace"><?= htmlspecialchars($log['recipient_email']) ?></div>
                      </td>
                      <td class="py-3">
                        <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.7rem;">
                          <?= htmlspecialchars($log['email_type']) ?>
                        </span>
                      </td>
                      <td class="py-3">
                        <div class="fw-semibold text-dark"><?= htmlspecialchars($log['subject']) ?></div>
                        <?php if (!empty($log['error_message'])): ?>
                          <div class="text-muted small" style="font-size: 0.75rem;"><?= htmlspecialchars($log['error_message']) ?></div>
                        <?php endif; ?>
                      </td>
                      <td class="py-3">
                        <?php if ($log['status'] === 'sent'): ?>
                          <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                            <i class="bi bi-check-circle-fill me-1"></i>Sent
                          </span>
                        <?php elseif ($log['status'] === 'skipped'): ?>
                          <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1">
                            <i class="bi bi-slash-circle me-1"></i>Skipped
                          </span>
                        <?php else: ?>
                          <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">
                            <i class="bi bi-x-circle-fill me-1"></i>Failed
                          </span>
                        <?php endif; ?>
                      </td>
                      <td class="text-end pe-4 py-3">
                        <?php if (!empty($log['html_body']) && filter_var($log['recipient_email'], FILTER_VALIDATE_EMAIL)): ?>
                          <form action="index.php?page=admin_resend_email" method="POST" class="d-inline m-0" onsubmit="return confirm('Send this email again to <?= htmlspecialchars(addslashes($log['recipient_email']), ENT_QUOTES, 'UTF-8') ?>?');">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="log_id" value="<?= (int)$log['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.76rem;">
                              <i class="bi bi-arrow-repeat me-1"></i>Resend
                            </button>
                          </form>
                        <?php else: ?>
                          <span class="text-muted small">&mdash;</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- Preview Modal for Custom Selective Email -->
<div class="modal fade" id="customEmailPreviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 rounded-4 shadow overflow-hidden">
      <div class="modal-header bg-light border-bottom py-3 px-4">
        <h6 class="modal-title fw-bold text-dark mb-0"><i class="bi bi-eye me-2 text-primary"></i>Email Preview</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" style="background: #f1f5f9;">
        <div style="max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 14px; overflow: hidden; border: 1px solid #e2e8f0;">
          <div style="background: linear-gradient(135deg, #7A1418 0%, #991b1b 100%); padding: 24px 28px; color: #ffffff;">
            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: #fecaca; font-weight: 700; margin-bottom: 6px;">NVTI BADDEGAMA &bull; OFFICIAL NOTICE</div>
            <h5 id="previewEmailSubject" style="margin: 0; font-weight: 700; color: #ffffff;">Subject Preview</h5>
          </div>
          <div style="padding: 28px; color: #334155; font-size: 14px; line-height: 1.7;">
            <div id="previewEmailBody" style="white-space: pre-wrap;"></div>
            <div style="text-align: center; margin: 24px 0 8px;">
              <span style="display: inline-block; background: #7A1418; color: #ffffff; padding: 10px 24px; font-weight: 600; font-size: 14px; border-radius: 999px;">Open Student Portal &rarr;</span>
            </div>
          </div>
          <div style="background: #f8fafc; padding: 14px 24px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0;">
            Sent by <?= htmlspecialchars(APP_NAME) ?> Administration &bull; NVTI Baddegama
          </div>
        </div>
      </div>
      <div class="modal-footer bg-white border-top px-4 py-2.5">
        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
const totalActiveStudentsCount = <?= (int)count($students) ?>;
const totalActiveTeachersCount = <?= (int)count($teachers) ?>;

function toggleRecipientMode(mode) {
  const singleBox = document.getElementById('modeSingleBox');
  const selectedBox = document.getElementById('modeSelectedStudentsBox');
  const classBox = document.getElementById('modeClassBox');
  const bulkNotice = document.getElementById('modeBulkNoticeBox');
  const bulkText = document.getElementById('modeBulkNoticeText');
  const singleInput = document.getElementById('singleEmailInput');

  singleBox.classList.add('d-none');
  selectedBox.classList.add('d-none');
  classBox.classList.add('d-none');
  bulkNotice.classList.add('d-none');
  singleInput.required = false;

  if (mode === 'single') {
    singleBox.classList.remove('d-none');
    singleInput.required = true;
  } else if (mode === 'selected_students') {
    selectedBox.classList.remove('d-none');
  } else if (mode === 'class') {
    classBox.classList.remove('d-none');
  } else if (mode === 'all_students') {
    bulkNotice.classList.remove('d-none');
    bulkText.textContent = 'This email will be sent to all ' + totalActiveStudentsCount + ' active student(s) with valid email addresses.';
  } else if (mode === 'all_teachers') {
    bulkNotice.classList.remove('d-none');
    bulkText.textContent = 'This email will be sent to all ' + totalActiveTeachersCount + ' active lecturer(s) with valid email addresses.';
  }
}

function filterStudentChecklist(query) {
  const q = (query || '').toLowerCase().trim();
  document.querySelectorAll('.student-check-row').forEach(function (row) {
    const hay = row.getAttribute('data-search') || '';
    if (q === '' || hay.indexOf(q) !== -1) {
      row.classList.remove('d-none');
      row.classList.add('d-flex');
    } else {
      row.classList.add('d-none');
      row.classList.remove('d-flex');
    }
  });
}

function toggleAllVisibleStudents(checked) {
  document.querySelectorAll('.student-check-row').forEach(function (row) {
    if (!row.classList.contains('d-none')) {
      const cb = row.querySelector('.custom-student-cb');
      if (cb) cb.checked = checked;
    }
  });
  updateSelectedStudentCount();
}

function updateSelectedStudentCount() {
  const count = document.querySelectorAll('.custom-student-cb:checked').length;
  document.getElementById('selectedStudentsCount').textContent = count;
}

function applyEmailTemplate(type) {
  const subj = document.getElementById('customEmailSubject');
  const msg = document.getElementById('customEmailMessage');

  const templates = {
    exam: {
      subject: 'Important Notice: Upcoming Assessment / Practical Examination - NVTI Baddegama',
      body: "Dear {name},\n\nPlease be informed that your upcoming ICT NVQ Level 5 assessment / practical examination has been scheduled. Please log in to the Student Portal to review the timetable, guidelines, and submission requirements.\n\nEnsure you bring your Student ID and arrive at least 15 minutes early.\n\nBest regards,\nNVTI Baddegama ICT Department"
    },
    schedule: {
      subject: 'Class Schedule Update / Session Notice - NVTI Baddegama',
      body: "Dear {name},\n\nPlease note that there is an update to our upcoming lecture session schedule. Kindly check the Timetable section in the Student Portal for the revised date and time.\n\nThank you for your cooperation.\n\nBest regards,\nNVTI Baddegama Administration"
    },
    docs: {
      subject: 'Reminder: Verification Documents Required - NVTI Baddegama',
      body: "Dear {name},\n\nThis is a kind reminder to upload any pending verification documents (NIC, O/L, A/L, or Birth Certificate) through your Student Portal Profile so we can complete your registration verification.\n\nBest regards,\nNVTI Baddegama Administration"
    },
    general: {
      subject: 'Official Announcement - NVTI Baddegama ICT Diploma',
      body: "Dear {name},\n\nWe would like to share an important update regarding your National Diploma in ICT (NVQ Level 5) programme at NVTI Baddegama.\n\nPlease sign in to the portal for full details.\n\nBest regards,\nNVTI Baddegama ICT Department"
    }
  };

  if (templates[type]) {
    subj.value = templates[type].subject;
    msg.value = templates[type].body;
  }
}

function openCustomEmailPreview() {
  const subj = document.getElementById('customEmailSubject').value.trim() || '(No Subject)';
  const rawMsg = document.getElementById('customEmailMessage').value.trim() || '(No message content)';
  const sampleMsg = rawMsg.replace(/\{name\}/g, 'Kasun Perera');

  document.getElementById('previewEmailSubject').textContent = subj;
  document.getElementById('previewEmailBody').textContent = sampleMsg;

  const modal = new bootstrap.Modal(document.getElementById('customEmailPreviewModal'));
  modal.show();
}

function confirmCustomEmailSend() {
  const mode = document.getElementById('recipientModeSelect').value;
  if (mode === 'selected_students') {
    const cnt = document.querySelectorAll('.custom-student-cb:checked').length;
    if (cnt === 0) {
      alert('Please select at least one student from the list.');
      return false;
    }
    return confirm('Send this custom email to ' + cnt + ' selected student(s)?');
  }
  if (mode === 'all_students') {
    return confirm('Send this email to ALL ' + totalActiveStudentsCount + ' active students?');
  }
  if (mode === 'all_teachers') {
    return confirm('Send this email to ALL ' + totalActiveTeachersCount + ' active lecturers?');
  }
  return true;
}

document.addEventListener('DOMContentLoaded', function () {
  toggleRecipientMode(document.getElementById('recipientModeSelect').value);
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
