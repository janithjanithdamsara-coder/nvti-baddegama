<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

$isEligible = ($overallRate >= 80.0);
$progressColor = ($overallRate >= 80) ? 'bg-success' : (($overallRate >= 75) ? 'bg-warning' : 'bg-danger');
$monthColor = ($monthlyRate >= 80) ? 'bg-success' : (($monthlyRate >= 75) ? 'bg-warning' : 'bg-danger');
$weekColor = ($weeklyRate >= 80) ? 'bg-success' : (($weeklyRate >= 75) ? 'bg-warning' : 'bg-danger');
?>

<div class="app-wrapper">
  <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <div class="container-fluid">
      <?= Flash::render() ?>

      <!-- Header Banner -->
      <div class="card border rounded-4 mb-4 p-4 bg-white shadow-sm">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
          <div>
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 rounded-pill">
                <i class="bi bi-graph-up-arrow me-1"></i> Attendance Monitoring
              </span>
              <?php if (!empty($profile['mis_number'])): ?>
                <span class="badge bg-light text-dark border font-monospace"><?= e($profile['mis_number']) ?></span>
              <?php endif; ?>
            </div>
            <h3 class="fw-bold mb-1 text-dark">Attendance &amp; Exam Eligibility</h3>
            <p class="mb-0 text-muted small">
              Official record for <?= e($profile['course_name'] ?? 'National Diploma in ICT') ?> &bull; <?= e($profile['class_name'] ?? 'Batch 2026') ?>
            </p>
          </div>

          <div class="d-flex align-items-center gap-2">
            <?php if ($isEligible): ?>
              <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-semibold shadow-2xs">
                <i class="bi bi-check-circle-fill me-1"></i> Exam Eligible (&ge; 80%)
              </span>
            <?php else: ?>
              <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-semibold shadow-2xs">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Below 80% Requirement
              </span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Main Progress Bars Card -->
      <div class="row g-4 mb-4">
        <!-- Overall Progress -->
        <div class="col-lg-6">
          <div class="card border rounded-4 p-4 bg-white shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h5 class="fw-bold text-dark mb-0">Overall Semester Attendance</h5>
              <span class="fs-4 fw-bold <?= ($overallRate >= 80) ? 'text-success' : 'text-danger' ?>">
                <?= $overallRate ?>%
              </span>
            </div>
            <p class="text-muted small mb-3">
              NVQ Level 5 Institutional rule: Minimum <strong>80% attendance</strong> is required to sit for CBT Semester Final Assessments.
            </p>

            <!-- Big Visual Progress Bar -->
            <div class="progress rounded-pill mb-3" style="height: 22px; background-color: #f1f5f9;">
              <div class="progress-bar <?= $progressColor ?> progress-bar-striped progress-bar-animated fw-bold" 
                   role="progressbar" 
                   style="width: <?= min(100, $overallRate) ?>%; font-size: 0.85rem;" 
                   aria-valuenow="<?= $overallRate ?>" 
                   aria-valuemin="0" 
                   aria-valuemax="100">
                <?= $overallRate ?>%
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center small text-muted">
              <span>0%</span>
              <span class="badge bg-light text-secondary border"><i class="bi bi-flag-fill text-warning me-1"></i>80% Exam Pass Mark</span>
              <span>100%</span>
            </div>
          </div>
        </div>

        <!-- Monthly & Weekly Progress Bars -->
        <div class="col-lg-6">
          <div class="card border rounded-4 p-4 bg-white shadow-sm h-100">
            <h5 class="fw-bold text-dark mb-3">Periodic Breakdown (මාසික සහ සතිපතා)</h5>

            <!-- Monthly Progress -->
            <div class="mb-3.5">
              <div class="d-flex justify-content-between align-items-center small mb-1">
                <span class="fw-semibold text-secondary">
                  <i class="bi bi-calendar-month text-primary me-1"></i> This Month Attendance (<?= date('F Y') ?>)
                </span>
                <strong class="text-dark"><?= $monthlyRate ?>%</strong>
              </div>
              <div class="progress rounded-pill" style="height: 12px; background-color: #f1f5f9;">
                <div class="progress-bar <?= $monthColor ?>" style="width: <?= min(100, $monthlyRate) ?>%;"></div>
              </div>
            </div>

            <!-- Weekly Progress -->
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center small mb-1">
                <span class="fw-semibold text-secondary">
                  <i class="bi bi-calendar2-week text-info me-1"></i> Last 7 Days (Past Week)
                </span>
                <strong class="text-dark"><?= $weeklyRate ?>%</strong>
              </div>
              <div class="progress rounded-pill" style="height: 12px; background-color: #f1f5f9;">
                <div class="progress-bar <?= $weekColor ?>" style="width: <?= min(100, $weeklyRate) ?>%;"></div>
              </div>
            </div>

            <div class="p-2.5 bg-light rounded-3 small text-muted border">
              <i class="bi bi-info-circle me-1 text-primary"></i> Attendance is logged daily by ICT Lecturers at NVTI Baddegama Computer Labs 01 &amp; 02.
            </div>
          </div>
        </div>
      </div>

      <!-- Quick KPI Counters -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="card border rounded-4 p-3 bg-white shadow-2xs text-center">
            <span class="text-muted small fw-semibold d-block mb-1">Total Sessions</span>
            <h3 class="fw-bold text-dark mb-0"><?= $totalSessions ?></h3>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border rounded-4 p-3 bg-white shadow-2xs text-center">
            <span class="text-muted small fw-semibold d-block mb-1">Days Present</span>
            <h3 class="fw-bold text-success mb-0"><?= $presentCount ?></h3>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border rounded-4 p-3 bg-white shadow-2xs text-center">
            <span class="text-muted small fw-semibold d-block mb-1">Late Arrivals</span>
            <h3 class="fw-bold text-warning mb-0"><?= $lateCount ?></h3>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border rounded-4 p-3 bg-white shadow-2xs text-center">
            <span class="text-muted small fw-semibold d-block mb-1">Days Absent</span>
            <h3 class="fw-bold text-danger mb-0"><?= $absentCount ?></h3>
          </div>
        </div>
      </div>

      <!-- Ready-Made Monthly Report Cards Hub -->
      <div class="card border-0 rounded-4 shadow-sm bg-white p-4 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
          <div>
            <h5 class="fw-bold text-dark mb-1">
              <i class="bi bi-calendar-check-fill text-primary me-2"></i>Monthly Attendance Reports (මාසික වාර්තා)
            </h5>
            <p class="text-secondary small mb-0">Select any month to inspect detailed session records and attendance eligibility.</p>
          </div>
          <span class="badge bg-light text-secondary border rounded-pill px-3 py-1.5 small align-self-start align-self-md-auto">
            <?= count($monthlyBreakdown ?? []) ?> Months Recorded
          </span>
        </div>

        <?php if (!empty($monthlyBreakdown)): ?>
          <div class="row g-3">
            <?php foreach ($monthlyBreakdown as $mKey => $mInfo): 
              $isEligible = ($mInfo['rate'] >= 80.0);
              $barColor = ($mInfo['rate'] >= 80) ? 'bg-success' : (($mInfo['rate'] >= 60) ? 'bg-warning' : 'bg-danger');
            ?>
              <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                <div class="card border rounded-4 h-100 p-3 shadow-2xs hover-shadow transition cursor-pointer month-report-card" 
                     id="monthCard_<?= e($mKey) ?>"
                     onclick="filterAttendanceByMonth('<?= e($mKey) ?>', true)"
                     style="transition: all 0.2s ease; border-color: #e2e8f0; background: #fafafa;">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                      <h6 class="fw-bold text-dark mb-0"><?= e($mInfo['name']) ?></h6>
                      <small class="text-muted font-monospace" style="font-size: 0.72rem;"><?= (int)$mInfo['total'] ?> Working Days</small>
                    </div>
                    <?php if ($isEligible): ?>
                      <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                        <i class="bi bi-check-circle-fill me-0.5"></i> &ge;80%
                      </span>
                    <?php else: ?>
                      <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                        <i class="bi bi-exclamation-triangle-fill me-0.5"></i> &lt;80%
                      </span>
                    <?php endif; ?>
                  </div>

                  <div class="d-flex justify-content-between align-items-baseline mb-1">
                    <span class="small text-secondary fw-semibold">Attendance Rate</span>
                    <strong class="fs-6 <?= $isEligible ? 'text-success' : 'text-danger' ?>"><?= $mInfo['rate'] ?>%</strong>
                  </div>

                  <div class="progress rounded-pill mb-2.5" style="height: 8px; background-color: #e2e8f0;">
                    <div class="progress-bar <?= $barColor ?>" style="width: <?= min(100, $mInfo['rate']) ?>%;"></div>
                  </div>

                  <div class="d-flex justify-content-between align-items-center pt-2 border-top text-muted small" style="font-size: 0.75rem;">
                    <span>
                      <strong class="text-success"><?= (int)$mInfo['present'] ?></strong> Present &bull; 
                      <strong class="text-danger"><?= (int)$mInfo['absent'] ?></strong> Absent
                    </span>
                    <span class="text-primary fw-bold">View &rarr;</span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Month Filter Tabs & Clean Attendance Log Table -->
      <div class="card border rounded-4 shadow-sm bg-white overflow-hidden" id="attendanceLogSection">
        <div class="card-header bg-white py-3 px-4 border-bottom">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
              <h5 class="fw-bold text-dark mb-1" id="logSectionTitle">
                <i class="bi bi-clock-history text-primary me-2"></i>Attendance Log
              </h5>
              <p class="text-secondary small mb-0" id="logSectionSubtitle">Showing all recorded sessions</p>
            </div>
            
            <!-- Quick Month Selector for Mobile -->
            <div class="d-block d-md-none">
              <select class="form-select form-select-sm rounded-pill fw-semibold border-secondary-subtle" onchange="filterAttendanceByMonth(this.value, false)">
                <option value="all">All Months (සියලු මාස)</option>
                <?php foreach (($monthlyBreakdown ?? []) as $mKey => $mInfo): ?>
                  <option value="<?= e($mKey) ?>"><?= e($mInfo['name']) ?> (<?= $mInfo['rate'] ?>%)</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Desktop Horizontal Month Filter Tabs -->
          <div class="d-none d-md-flex align-items-center gap-1.5 flex-wrap mt-3 pt-3 border-top">
            <span class="small fw-bold text-secondary me-1"><i class="bi bi-funnel me-1"></i>Filter Month:</span>
            <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold month-tab-btn active-month-tab" data-month="all" onclick="filterAttendanceByMonth('all', false)">
              All Months
            </button>
            <?php foreach (($monthlyBreakdown ?? []) as $mKey => $mInfo): ?>
              <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold month-tab-btn" data-month="<?= e($mKey) ?>" onclick="filterAttendanceByMonth('<?= e($mKey) ?>', false)">
                <?= e($mInfo['short']) ?>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <?php if (empty($attendanceLogs)): ?>
          <div class="p-5 text-center text-muted">
            <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary"></i>
            No attendance records recorded yet.
          </div>
        <?php else: ?>
          <!-- Clean Focused Table: Date, Day, Status only (No module, no remarks) -->
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="attendanceTable">
              <thead class="table-light text-uppercase small text-secondary" style="font-size: 0.74rem; letter-spacing: 0.5px;">
                <tr>
                  <th style="width: 60px;" class="text-center py-3">#</th>
                  <th style="min-width: 160px;" class="ps-3 py-3">Date (දිනය)</th>
                  <th style="min-width: 140px;" class="py-3">Day of Week (සතියේ දිනය)</th>
                  <th style="min-width: 160px;" class="text-center py-3">Attendance Status (පැමිණීම)</th>
                </tr>
              </thead>
              <tbody id="attendanceTableBody">
                <?php 
                  $rowNum = 1;
                  foreach ($attendanceLogs as $log): 
                    $logMonth = date('Y-m', strtotime($log['date']));
                ?>
                  <tr class="attendance-row" data-month="<?= e($logMonth) ?>">
                    <td class="text-center text-muted font-monospace row-index-cell" style="font-size: 0.8rem;"><?= $rowNum++ ?></td>
                    <td class="ps-3 fw-bold text-dark font-monospace" style="font-size: 0.9rem;">
                      <i class="bi bi-calendar-event text-secondary me-1.5"></i><?= date('Y-m-d', strtotime($log['date'])) ?>
                    </td>
                    <td>
                      <span class="badge bg-light text-dark border px-2.5 py-1 font-monospace" style="font-size: 0.76rem;">
                        <?= date('l', strtotime($log['date'])) ?>
                      </span>
                    </td>
                    <td class="text-center">
                      <?php if ($log['status'] === 'present'): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.8rem;">
                          <i class="bi bi-check-circle-fill me-1"></i> Present
                        </span>
                      <?php elseif ($log['status'] === 'late'): ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.8rem;">
                          <i class="bi bi-clock-fill me-1"></i> Late
                        </span>
                      <?php elseif ($log['status'] === 'excused'): ?>
                        <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.8rem;">
                          <i class="bi bi-info-circle-fill me-1"></i> Excused
                        </span>
                      <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.8rem;">
                          <i class="bi bi-x-circle-fill me-1"></i> Absent
                        </span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <div id="noRecordsNotice" class="p-5 text-center text-muted d-none">
            <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary"></i>
            No attendance records found for the selected month.
          </div>
        <?php endif; ?>

      </div>

    </div>
  </main>
</div>

<style>
.month-tab-btn {
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #e2e8f0;
  transition: all 0.18s ease;
}
.month-tab-btn:hover {
  background: #e2e8f0;
  color: #1e293b;
}
.month-tab-btn.active-month-tab {
  background: #7A1418 !important;
  color: #ffffff !important;
  border-color: #7A1418 !important;
  box-shadow: 0 2px 6px rgba(122, 20, 24, 0.25);
}
.month-report-card:hover {
  transform: translateY(-2px);
  border-color: #7A1418 !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
}
.month-report-card.selected-month-card {
  border: 2px solid #7A1418 !important;
  background: #ffffff !important;
  box-shadow: 0 4px 14px rgba(122, 20, 24, 0.15) !important;
}
</style>

<script>
const monthlyMeta = <?= json_encode($monthlyBreakdown ?? []) ?>;

function filterAttendanceByMonth(mKey, scrollIntoView) {
  const rows = document.querySelectorAll('.attendance-row');
  const tabs = document.querySelectorAll('.month-tab-btn');
  const cards = document.querySelectorAll('.month-report-card');
  const notice = document.getElementById('noRecordsNotice');
  const table = document.getElementById('attendanceTable');
  const title = document.getElementById('logSectionTitle');
  const subtitle = document.getElementById('logSectionSubtitle');

  // Update Month Tab buttons styling
  tabs.forEach(tab => {
    if (tab.getAttribute('data-month') === mKey) {
      tab.classList.add('active-month-tab');
    } else {
      tab.classList.remove('active-month-tab');
    }
  });

  // Update Month Card selection styling
  cards.forEach(card => card.classList.remove('selected-month-card'));
  if (mKey !== 'all') {
    const activeCard = document.getElementById('monthCard_' + mKey);
    if (activeCard) activeCard.classList.add('selected-month-card');
  }

  // Filter rows
  let visibleCount = 0;
  rows.forEach(r => {
    const rowMonth = r.getAttribute('data-month');
    if (mKey === 'all' || rowMonth === mKey) {
      r.style.display = '';
      visibleCount++;
      const idxCell = r.querySelector('.row-index-cell');
      if (idxCell) idxCell.textContent = visibleCount;
    } else {
      r.style.display = 'none';
    }
  });

  // Header texts
  if (mKey === 'all') {
    if (title) title.innerHTML = '<i class="bi bi-clock-history text-primary me-2"></i>Attendance Log (All Months)';
    if (subtitle) subtitle.textContent = `Showing all ${visibleCount} recorded sessions across the academic year`;
  } else if (monthlyMeta && monthlyMeta[mKey]) {
    const mInfo = monthlyMeta[mKey];
    if (title) title.innerHTML = `<i class="bi bi-calendar-month text-primary me-2"></i>${mInfo.name} Attendance Report`;
    if (subtitle) subtitle.innerHTML = `<strong>${mInfo.total} Working Days</strong> &bull; <span class="text-success">${mInfo.present} Present</span> &bull; <span class="text-danger">${mInfo.absent} Absent</span> &bull; Rate: <strong class="${mInfo.rate >= 80 ? 'text-success' : 'text-danger'}">${mInfo.rate}%</strong>`;
  }

  // Empty state handling
  if (visibleCount === 0) {
    if (table) table.classList.add('d-none');
    if (notice) notice.classList.remove('d-none');
  } else {
    if (table) table.classList.remove('d-none');
    if (notice) notice.classList.add('d-none');
  }

  // Scroll smoothly to log section if triggered from a month card
  if (scrollIntoView) {
    const section = document.getElementById('attendanceLogSection');
    if (section) {
      section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }
}

// Support deep linking via URL parameter ?month=2026-08
document.addEventListener('DOMContentLoaded', function () {
  const urlParams = new URLSearchParams(window.location.search);
  const mParam = urlParams.get('month');
  if (mParam && monthlyMeta && monthlyMeta[mParam]) {
    filterAttendanceByMonth(mParam, false);
  }
});
</script>

    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
