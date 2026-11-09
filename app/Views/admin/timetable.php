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
          <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-calendar3 text-primary me-2"></i>Class Timetable & Daily Shift Management</h4>
          <p class="text-muted small mb-0">Managing Teacher 01 (Morning 09:00 - 12:00) and Teacher 02 (Afternoon 13:00 - 16:00) schedules</p>
        </div>

        <button class="btn btn-primary btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addSessionModal">
          <i class="bi bi-calendar-plus me-1"></i> Add Timetable Session
        </button>
      </div>

      <!-- Quick Shift Explanation Banner -->
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-4 border-warning bg-white">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle bg-warning bg-opacity-25 text-warning d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="bi bi-sun-fill fs-4"></i>
              </div>
              <div>
                <span class="badge slot-badge-morning mb-1">Morning Session Slot</span>
                <h6 class="fw-bold mb-0 text-dark">09:00 AM – 12:00 PM (3 Hours)</h6>
                <small class="text-muted">Conducted by Teacher 01 (Database & Hardware/Networking)</small>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-4 border-primary bg-white">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle bg-primary bg-opacity-25 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="bi bi-sunset-fill fs-4"></i>
              </div>
              <div>
                <span class="badge slot-badge-afternoon mb-1">Afternoon Session Slot</span>
                <h6 class="fw-bold mb-0 text-dark">01:00 PM – 04:00 PM (3 Hours)</h6>
                <small class="text-muted">Conducted by Teacher 02 (Web Development & Software Eng.)</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Timetable Sessions List -->
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom">
          <h5 class="fw-bold mb-0 text-dark">All Scheduled Timetable Sessions</h5>
        </div>

        <div class="table-responsive">
          <table class="table table-hover table-custom mb-0">
            <thead>
              <tr>
                <th>Day</th>
                <th>Time Window</th>
                <th>Shift Type</th>
                <th>Subject & Batch</th>
                <th>Assigned Lecturer</th>
                <th>Location / Lab</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($sessions)): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">No timetable sessions found.</td></tr>
              <?php else: ?>
                <?php foreach ($sessions as $s): ?>
                  <?php
                    $isMorning = (strtotime($s['start_time']) < strtotime('12:30:00'));
                  ?>
                  <tr>
                    <td>
                      <strong class="text-dark"><?= e($s['day_of_week']) ?></strong>
                    </td>
                    <td>
                      <span class="fw-bold text-dark"><?= date('h:i A', strtotime($s['start_time'])) ?> - <?= date('h:i A', strtotime($s['end_time'])) ?></span>
                    </td>
                    <td>
                      <?php if ($isMorning): ?>
                        <span class="badge slot-badge-morning"><i class="bi bi-sun-fill me-1"></i> Morning (09-12)</span>
                      <?php else: ?>
                        <span class="badge slot-badge-afternoon"><i class="bi bi-sunset-fill me-1"></i> Afternoon (13-16)</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="fw-bold text-dark"><?= e($s['subject_name']) ?></div>
                      <small class="text-muted"><?= e($s['class_name']) ?></small>
                    </td>
                    <td>
                      <span class="text-primary fw-semibold"><i class="bi bi-person me-1"></i><?= e($s['teacher_name']) ?></span>
                    </td>
                    <td>
                      <span class="badge bg-light text-secondary border"><?= e($s['hall_or_link']) ?></span>
                    </td>
                    <td class="text-end">
                      <form action="index.php?page=admin_delete_session" method="POST" onsubmit="return confirm('Delete this scheduled class session?');" class="d-inline">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- Modal: Add Session -->
<div class="modal fade" id="addSessionModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
      <div class="modal-header bg-edu-navy text-white py-3">
        <h5 class="modal-title fw-bold"><i class="bi bi-calendar-plus me-2 text-warning"></i>Schedule Class Session</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="index.php?page=admin_create_session" method="POST">
        <?= Csrf::field() ?>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Session Title *</label>
            <input type="text" name="session_title" class="form-control" placeholder="e.g. Database Practical Lab" required>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-sm-6">
              <label class="form-label fw-semibold small text-secondary">Day of Week *</label>
              <select name="day_of_week" class="form-select" required>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
                <option value="Saturday">Saturday</option>
                <option value="Sunday">Sunday</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold small text-secondary">Shift Window *</label>
              <select name="slot_preset" class="form-select" required>
                <option value="morning">Morning (09:00 - 12:00)</option>
                <option value="afternoon">Afternoon (13:00 - 16:00)</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Assigned Lecturer *</label>
            <select name="teacher_id" class="form-select" required>
              <option value="">-- Choose Lecturer --</option>
              <?php foreach ($teachers as $t): ?>
                <option value="<?= $t['id'] ?>"><?= e($t['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Subject / Module *</label>
            <select name="subject_id" class="form-select" required>
              <option value="">-- Choose Subject --</option>
              <?php foreach ($subjects as $sub): ?>
                <option value="<?= $sub['id'] ?>"><?= e($sub['subject_code']) ?> - <?= e($sub['subject_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Class / Batch *</label>
            <select name="class_id" class="form-select" required>
              <option value="">-- Choose Batch --</option>
              <?php foreach ($classes as $cl): ?>
                <option value="<?= $cl['id'] ?>"><?= e($cl['class_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Location / Lab / Room</label>
            <input type="text" name="hall_or_link" class="form-control" value="ICT Lab 01" placeholder="e.g. ICT Lab 01 / Hall A">
          </div>
        </div>
        <div class="modal-footer bg-light py-2 px-4">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">Add to Timetable</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
