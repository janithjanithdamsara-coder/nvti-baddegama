<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

$semFilter  = (int)($_GET['semester'] ?? 0);
$typeFilter = trim($_GET['type'] ?? '');
$search     = trim($_GET['search'] ?? '');
?>

<div class="app-wrapper">
  <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <div class="container-fluid">
      <?= Flash::render() ?>

      <!-- ================= HERO BANNER ================= -->
      <div class="card border rounded-4 mb-4 p-3 p-md-4 bg-white shadow-sm">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
          <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
              <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background: #fef2f2; color: #7A1418; border: 1px solid #fecaca; font-size: 0.75rem;">
                <i data-lucide="award" style="width: 13px; height: 13px;" class="me-1"></i> NVQ Level 5 &bull; National Diploma
              </span>
              <span class="badge bg-light text-secondary border fw-medium rounded-pill">
                CBT Course Code: K72T001
              </span>
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-medium rounded-pill">
                TVEC Sri Lanka Accredited
              </span>
            </div>
            <h3 class="fw-bold mb-1 text-dark">Course Curriculum &amp; Detailed Syllabus</h3>
            <p class="text-muted small mb-0">Official TVEC Curriculum Guide Book breakdown into Semester 1 &amp; 2 Modules with competencies and detailed sub-topics ("අනු කොටස්").</p>
          </div>
          
          <!-- Quick Action Buttons -->
          <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="button" onclick="openGuideBookModal()" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-none">
              <i data-lucide="book-open" style="width: 16px; height: 16px;"></i>
              <span>View Full Guide Book (PDF)</span>
            </button>
            <a href="index.php?page=student_papers" class="btn btn-sm rounded-pill px-3 py-2 fw-bold text-white d-inline-flex align-items-center gap-2 shadow-sm" style="background: linear-gradient(135deg, #7A1418, #90161b);">
              <i data-lucide="file-text" style="width: 16px; height: 16px;"></i>
              <span>Past &amp; Model Papers</span>
            </a>
          </div>
        </div>

        <!-- Metric Summary Stats Cards -->
        <div class="row g-2 g-md-3 mt-3 pt-3 border-top">
          <div class="col-6 col-md-3">
            <div class="p-2.5 rounded-3 bg-light border d-flex align-items-center gap-2.5">
              <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: #fee2e2; color: #7A1418;">
                <i data-lucide="layers" style="width: 20px; height: 20px;"></i>
              </div>
              <div>
                <div class="fs-5 fw-bold text-dark lh-1"><?= $totalModules ?></div>
                <small class="text-muted" style="font-size: 0.74rem;">Total Modules</small>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="p-2.5 rounded-3 bg-light border d-flex align-items-center gap-2.5">
              <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: #e0f2fe; color: #0284c7;">
                <i data-lucide="list-tree" style="width: 20px; height: 20px;"></i>
              </div>
              <div>
                <div class="fs-5 fw-bold text-dark lh-1"><?= $totalTopics ?></div>
                <small class="text-muted" style="font-size: 0.74rem;">Learning Units ("අනු කොටස්")</small>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="p-2.5 rounded-3 bg-light border d-flex align-items-center gap-2.5">
              <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: #ecfdf5; color: #059669;">
                <i data-lucide="calendar" style="width: 20px; height: 20px;"></i>
              </div>
              <div>
                <div class="fs-5 fw-bold text-dark lh-1"><?= $sem1ModCount ?> Modules</div>
                <small class="text-muted" style="font-size: 0.74rem;">Semester 1 (M01-M08, EMP02)</small>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="p-2.5 rounded-3 bg-light border d-flex align-items-center gap-2.5">
              <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: #fef3c7; color: #d97706;">
                <i data-lucide="calendar-range" style="width: 20px; height: 20px;"></i>
              </div>
              <div>
                <div class="fs-5 fw-bold text-dark lh-1"><?= $sem2ModCount ?> Modules</div>
                <small class="text-muted" style="font-size: 0.74rem;">Semester 2 (M09-M15, EMP01,03)</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ================= FILTER & SEARCH BAR ================= -->
      <div class="card border rounded-4 mb-4 p-3 bg-white shadow-sm">
        <form method="GET" action="index.php" class="row g-2 align-items-center">
          <input type="hidden" name="page" value="student_curriculum">

          <!-- Search Input -->
          <div class="col-12 col-md-5">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light border-end-0 text-muted">
                <i data-lucide="search" style="width: 15px; height: 15px;"></i>
              </span>
              <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search module code (e.g. M03), title, or keywords..." value="<?= e($search) ?>">
            </div>
          </div>

          <!-- Semester Filter -->
          <div class="col-6 col-md-3">
            <select name="semester" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="0" <?= $semFilter === 0 ? 'selected' : '' ?>>All Semesters (1 &amp; 2)</option>
              <option value="1" <?= $semFilter === 1 ? 'selected' : '' ?>>Semester 1 (Year 1)</option>
              <option value="2" <?= $semFilter === 2 ? 'selected' : '' ?>>Semester 2 (Year 1)</option>
            </select>
          </div>

          <!-- Type Filter -->
          <div class="col-6 col-md-2">
            <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="" <?= $typeFilter === '' ? 'selected' : '' ?>>All Module Types</option>
              <option value="Technical" <?= $typeFilter === 'Technical' ? 'selected' : '' ?>>Technical (15)</option>
              <option value="Employability" <?= $typeFilter === 'Employability' ? 'selected' : '' ?>>Employability (3)</option>
            </select>
          </div>

          <!-- Submit / Reset Buttons -->
          <div class="col-12 col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary flex-grow-1 rounded-3">
              <i data-lucide="filter" style="width: 14px; height: 14px;" class="me-1"></i> Filter
            </button>
            <?php if (!empty($search) || $semFilter > 0 || !empty($typeFilter)): ?>
              <a href="index.php?page=student_curriculum" class="btn btn-sm btn-outline-secondary rounded-3" title="Clear Filters">
                <i data-lucide="rotate-ccw" style="width: 14px; height: 14px;"></i>
              </a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <!-- ================= MODULES & DETAILED SUB-TOPIC TABLES ================= -->
      <?php if (empty($modules)): ?>
        <div class="card border rounded-4 p-5 text-center bg-white shadow-sm my-4">
          <div class="rounded-circle p-3 bg-light d-inline-flex mx-auto mb-3" style="width: 64px; height: 64px; align-items: center; justify-content: center;">
            <i data-lucide="search-x" class="text-muted" style="width: 32px; height: 32px;"></i>
          </div>
          <h5 class="fw-bold text-dark">No modules match your filter</h5>
          <p class="text-muted small">Try searching with a different keyword or reset filters to view all 18 NVQ modules.</p>
          <a href="index.php?page=student_curriculum" class="btn btn-sm btn-primary rounded-pill px-4 mx-auto">Reset Filters</a>
        </div>
      <?php else: ?>

        <?php
          // If viewing all, we group by Semester for maximum clarity
          $grouped = [];
          foreach ($modules as $m) {
              $sem = (int)$m['semester'];
              $grouped[$sem][] = $m;
          }
          ksort($grouped);
        ?>

        <?php foreach ($grouped as $semNum => $semMods): ?>
          <div class="mb-4">
            <!-- Semester Section Header -->
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
              <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill px-3 py-1.5 fw-bold text-white shadow-sm" style="background: <?= $semNum === 1 ? '#0284c7' : '#7A1418' ?>; font-size: 0.85rem;">
                  Semester <?= $semNum ?>
                </span>
                <span class="fw-bold text-dark fs-5">
                  <?= $semNum === 1 ? 'Year 1 &bull; Semester 1 Foundation &amp; Core Systems' : 'Year 1 &bull; Semester 2 Advanced Development &amp; Professional Skills' ?>
                </span>
              </div>
              <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1">
                <?= count($semMods) ?> Modules
              </span>
            </div>

            <!-- List of Modules in this Semester -->
            <div class="d-flex flex-column gap-3">
              <?php foreach ($semMods as $mod): 
                $topics = $moduleTopics[$mod['id']] ?? [];
                $isEmployability = ($mod['module_type'] === 'Employability');
              ?>
                <div class="card border rounded-4 overflow-hidden bg-white shadow-sm" id="mod-<?= e($mod['module_code']) ?>">
                  <!-- Module Header Bar -->
                  <div class="card-header border-bottom py-3 px-3 px-md-4" style="background: <?= $isEmployability ? 'linear-gradient(90deg, #f0fdf4 0%, #ffffff 100%)' : 'linear-gradient(90deg, #f8fafc 0%, #ffffff 100%)' ?>;">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                      <div class="d-flex align-items-center gap-2.5">
                        <span class="badge font-monospace px-2.5 py-1.5 fw-bold shadow-sm" style="background: <?= $isEmployability ? '#059669' : '#7A1418' ?>; color: #ffffff; font-size: 0.8rem; letter-spacing: 0.5px;">
                          <?= e($mod['module_code']) ?>
                        </span>
                        <div>
                          <h6 class="fw-bold mb-0 text-dark" style="font-size: 1rem;">
                            <?= e($mod['module_title']) ?>
                          </h6>
                          <div class="d-flex flex-wrap align-items-center gap-2 mt-0.5">
                            <span class="badge <?= $isEmployability ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' ?> rounded-pill" style="font-size: 0.7rem;">
                              <?= e($mod['module_type']) ?> Module
                            </span>
                            <span class="text-muted small" style="font-size: 0.75rem;">
                              <i data-lucide="book" style="width: 12px; height: 12px;" class="me-0.5 align-middle"></i> Guide Book Page: <strong><?= e($mod['curriculum_page']) ?></strong>
                            </span>
                            <span class="text-muted small" style="font-size: 0.75rem;">
                              &bull; <strong><?= count($topics) ?></strong> Learning Units
                            </span>
                          </div>
                        </div>
                      </div>

                      <!-- Action Buttons for Module -->
                      <div class="d-flex align-items-center gap-2 ms-auto ms-md-0">
                        <a href="index.php?page=student_papers&module_code=<?= urlencode($mod['module_code']) ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 text-nowrap d-inline-flex align-items-center gap-1.5" style="font-size: 0.76rem;" title="Find past papers for this module">
                          <i data-lucide="file-text" style="width: 13px; height: 13px;"></i>
                          <span>Past Papers</span>
                        </a>
                        <a href="index.php?page=student_materials&search=<?= urlencode($mod['module_code']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 text-nowrap d-inline-flex align-items-center gap-1.5" style="font-size: 0.76rem;" title="Find study materials for this module">
                          <i data-lucide="folder" style="width: 13px; height: 13px;"></i>
                          <span>Study Notes</span>
                        </a>
                      </div>
                    </div>
                  </div>

                  <!-- Module Topics Table (Desktop) & Cards (Mobile) -->
                  <div class="card-body p-0">
                    <?php if (empty($topics)): ?>
                      <div class="p-3 text-center text-muted small">No sub-topics recorded for this module yet.</div>
                    <?php else: ?>
                      <!-- Desktop View -->
                      <div class="d-none d-md-block table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.83rem;">
                          <thead>
                            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: #475569;">
                              <th style="width: 60px;" class="text-center py-2.5">Unit</th>
                              <th style="width: 280px;" class="py-2.5">Learning Topic / Competency Area</th>
                              <th style="width: 110px;" class="text-center py-2.5">Allocated Hours</th>
                              <th class="py-2.5">Curriculum Sub-Components &amp; Key Content ("අනු කොටස්")</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($topics as $tp): 
                              $subItems = array_filter(array_map('trim', explode(';', $tp['sub_topics'] ?? '')));
                            ?>
                              <tr>
                                <!-- Topic Unit Number -->
                                <td class="text-center py-3">
                                  <span class="badge rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="background: #f1f5f9; color: #334155; width: 28px; height: 28px; font-weight: 700; font-size: 0.78rem;">
                                    <?= sprintf('%02d', $tp['topic_no']) ?>
                                  </span>
                                </td>

                                <!-- Topic Title -->
                                <td class="py-3">
                                  <div class="fw-bold text-dark mb-0.5" style="font-size: 0.86rem;">
                                    <?= e($tp['topic_title']) ?>
                                  </div>
                                  <span class="text-muted" style="font-size: 0.72rem;">Topic Code: <?= e($mod['module_code']) ?>.T<?= $tp['topic_no'] ?></span>
                                </td>

                                <!-- Allocated Hours -->
                                <td class="text-center py-3">
                                  <span class="badge rounded-pill px-2.5 py-1 font-monospace" style="background: #f8fafc; color: #1e293b; border: 1px solid #cbd5e1; font-size: 0.75rem;">
                                    <i data-lucide="clock" style="width: 11px; height: 11px;" class="me-1 align-middle"></i><?= e($tp['allocated_hours']) ?>
                                  </span>
                                </td>

                                <!-- Sub-Topics ("Anu Kotas") formatted as clean styled checklist items -->
                                <td class="py-3">
                                  <?php if (empty($subItems)): ?>
                                    <span class="text-muted italic small">General module syllabus competency.</span>
                                  <?php else: ?>
                                    <div class="d-flex flex-column gap-1.5">
                                      <?php foreach ($subItems as $sub): ?>
                                        <div class="d-flex align-items-start gap-2" style="line-height: 1.45;">
                                          <span class="text-success mt-0.5 flex-shrink-0" style="display: inline-flex;">
                                            <i data-lucide="check-circle-2" style="width: 13px; height: 13px;"></i>
                                          </span>
                                          <span class="text-secondary" style="font-size: 0.82rem;">
                                            <?= e($sub) ?>
                                          </span>
                                        </div>
                                      <?php endforeach; ?>
                                    </div>
                                  <?php endif; ?>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>

                      <!-- Mobile Card List (< 768px) -->
                      <div class="d-md-none p-3 bg-light d-flex flex-column gap-2.5">
                        <?php foreach ($topics as $tp): 
                          $subItems = array_filter(array_map('trim', explode(';', $tp['sub_topics'] ?? '')));
                        ?>
                          <div class="card border rounded-3 p-3 bg-white shadow-xs">
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                              <div class="d-flex align-items-center gap-2 min-w-0">
                                <span class="badge rounded-circle p-1 d-inline-flex align-items-center justify-content-center flex-shrink-0" style="background: #f1f5f9; color: #334155; width: 28px; height: 28px; font-weight: 700; font-size: 0.75rem;">
                                  <?= sprintf('%02d', $tp['topic_no']) ?>
                                </span>
                                <div class="min-w-0">
                                  <div class="fw-bold text-dark lh-sm" style="font-size: 0.88rem;"><?= e($tp['topic_title']) ?></div>
                                  <small class="text-muted" style="font-size: 0.72rem;"><?= e($mod['module_code']) ?>.T<?= $tp['topic_no'] ?></small>
                                </div>
                              </div>
                              <span class="badge rounded-pill px-2 py-1 font-monospace flex-shrink-0" style="background: #f8fafc; color: #1e293b; border: 1px solid #cbd5e1; font-size: 0.7rem;">
                                <i data-lucide="clock" style="width: 11px; height: 11px;" class="me-1 align-middle"></i><?= e($tp['allocated_hours']) ?>
                              </span>
                            </div>

                            <?php if (!empty($subItems)): ?>
                              <div class="pt-2 border-top d-flex flex-column gap-1.5">
                                <?php foreach ($subItems as $sub): ?>
                                  <div class="d-flex align-items-start gap-2" style="line-height: 1.4;">
                                    <span class="text-success mt-0.5 flex-shrink-0" style="display: inline-flex;">
                                      <i data-lucide="check-circle-2" style="width: 13px; height: 13px;"></i>
                                    </span>
                                    <span class="text-secondary" style="font-size: 0.8rem;"><?= e($sub) ?></span>
                                  </div>
                                <?php endforeach; ?>
                              </div>
                            <?php endif; ?>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>

      <?php endif; ?>

    </div>
  </main>
</div>

<!-- ================= TVEC GUIDE BOOK VIEWER MODAL ================= -->
<div class="modal fade" id="tvecGuideModal" tabindex="-1" aria-labelledby="tvecGuideModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-fullscreen-md-down modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
      <div class="modal-header border-bottom py-2.5 px-3 px-md-4 d-flex align-items-center justify-content-between gap-2" style="background: linear-gradient(90deg, #7A1418, #90161b); color: #ffffff;">
        <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0 overflow-hidden">
          <span class="badge bg-white text-danger rounded-circle p-1.5 d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 30px; height: 30px;">
            <i data-lucide="book-open" style="width: 16px; height: 16px;"></i>
          </span>
          <div class="min-w-0">
            <h6 class="modal-title fw-bold mb-0 text-white text-truncate" id="tvecGuideModalLabel">TVEC NVQ Level 5 ICT Guide Book (K72T001)</h6>
            <small class="text-white-50 d-block text-truncate" style="font-size: 0.72rem;">NAITA &bull; TVEC Sri Lanka</small>
          </div>
        </div>
        <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
          <a href="index.php?page=view_curriculum_guide" target="_blank" class="btn btn-sm btn-light rounded-pill px-2.5 py-1 text-danger fw-semibold d-inline-flex align-items-center gap-1" title="Open in New Tab">
            <i data-lucide="external-link" style="width: 13px; height: 13px;"></i>
            <span class="d-none d-md-inline">Open in New Tab</span>
          </a>
          <button type="button" class="btn-close btn-close-white shadow-none ms-1 flex-shrink-0" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      <div class="modal-body p-0 bg-dark" style="height: 76vh; min-height: 400px;">
        <iframe id="guideBookIframe" src="" class="w-100 h-100 border-0"></iframe>
      </div>
      <div class="modal-footer bg-white border-top py-2 px-3 d-flex d-md-none justify-content-between">
        <span class="small text-muted">TVEC Curriculum Guide</span>
        <button type="button" class="btn btn-danger btn-sm rounded-pill px-4 fw-bold" data-bs-dismiss="modal">
          <i class="bi bi-x-lg me-1"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function openGuideBookModal() {
  const iframe = document.getElementById('guideBookIframe');
  iframe.src = 'index.php?page=view_curriculum_guide';
  const modal = new bootstrap.Modal(document.getElementById('tvecGuideModal'));
  modal.show();
}

document.addEventListener('DOMContentLoaded', function() {
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
