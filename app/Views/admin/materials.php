<?php
/**
 * Admin Learning Materials & Google Classroom Hub View
 * NVTI Baddegama National Diploma Portal
 */

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

$isSubjectView = isset($currentSubject) && !empty($currentSubject);
?>

<div class="app-wrapper">
  <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <div class="container-fluid">
      <?= Flash::render() ?>

      <?php if (!$isSubjectView): ?>
        <!-- ============================================================== -->
        <!-- VIEW 1: GOOGLE CLASSROOM GRID OF SUBJECT MODULES (IMAGE 1)     -->
        <!-- ============================================================== -->
        
        <!-- Header Banner -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
          <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
              <div>
                <h3 class="fw-bold text-dark mb-1">Subject Classes &amp; Learning Modules</h3>
                <p class="text-muted small mb-0">
                  Select a class to manage study notes, stream postings, coursework assignments, and deadlines for students in this cohort.
                </p>
                <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                  <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 small">
                    <i class="bi bi-journal-bookmark-fill text-danger me-1"></i> <?= count($classrooms ?? []) ?> Subjects Synced
                  </span>
                  <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 small">
                    <i class="bi bi-collection-fill text-primary me-1"></i> <?= (int)($totalMaterials ?? 0) ?> Total Materials
                  </span>
                  <?php if (!empty($totalHiddenMaterials)): ?>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 fw-bold small">
                      <i class="bi bi-eye-slash-fill me-1"></i> <?= (int)$totalHiddenMaterials ?> Hidden
                    </span>
                  <?php else: ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small">
                      <i class="bi bi-eye-fill me-1"></i> All Visible to Students
                    </span>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Top Action Controls & Batch Switcher -->
              <div class="d-flex flex-column align-items-stretch align-items-lg-end gap-2">
                <div class="d-flex align-items-center gap-2">
                  <label for="adminMaterialsBatchSelect" class="small fw-bold text-secondary text-nowrap mb-0">
                    <i class="bi bi-layers me-1"></i> Cohort:
                  </label>
                  <select id="adminMaterialsBatchSelect" class="form-select form-select-sm rounded-pill fw-semibold border-secondary-subtle shadow-xs" style="min-width: 240px;"
                          onchange="window.location.href='index.php?page=admin_materials&class_id=' + this.value">
                    <?php foreach (($classes ?? []) as $cl): ?>
                      <option value="<?= (int)$cl['id'] ?>" <?= ((int)$cl['id'] === (int)$activeClassId) ? 'selected' : '' ?>>
                        <?= e($cl['class_name']) ?> (<?= ($cl['status'] ?? '') === 'active' ? 'Active' : 'Archived' ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap justify-content-lg-end">
                  <!-- Batch Material Visibility & Reset Button -->
                  <button type="button" class="btn btn-warning btn-sm rounded-pill px-3.5 py-2 fw-bold text-dark d-inline-flex align-items-center gap-2 shadow-xs" data-bs-toggle="modal" data-bs-target="#batchMaterialControlModal">
                    <i class="bi bi-sliders2-vertical fs-6"></i>
                    <span>Batch Visibility &amp; Copy Hub</span>
                  </button>

                  <!-- 1-Click Sync All 18 NVQ Curriculum Modules -->
                  <form action="index.php?page=admin_sync_curriculum_modules" method="POST" class="d-inline m-0" onsubmit="return confirm('Sync and register all 18 NVQ Curriculum Modules into this batch? Any missing modules will be automatically linked.');">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="class_id" value="<?= (int)$activeClassId ?>">
                    <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs" title="Sync all 18 NVQ Level 5 Modules for this cohort">
                      <i class="bi bi-arrow-repeat fs-6 text-success"></i>
                      <span>Sync All 18 Modules</span>
                    </button>
                  </form>

                  <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2" style="background: #7A1418; border-color: #7A1418;" data-bs-toggle="modal" data-bs-target="#createClassModal">
                    <i class="bi bi-plus-lg fs-6"></i>
                    <span>+ Create Class Module</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <?php if ((int)($totalMaterials ?? 0) === 0 && !empty($sourceBatches)): ?>
          <!-- Helpful 1-Click Sync Banner for New Batches -->
          <div class="card border border-warning-subtle rounded-4 mb-4 shadow-xs" style="background: #fffbeb;">
            <div class="card-body p-3 p-md-4">
              <form action="index.php?page=admin_copy_batch_materials" method="POST" class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                <?= Csrf::field() ?>
                <input type="hidden" name="target_class_id" value="<?= (int)$activeClassId ?>">
                <div class="d-flex align-items-start gap-3">
                  <div class="rounded-circle bg-warning bg-opacity-25 text-warning-emphasis d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="bi bi-files fs-5"></i>
                  </div>
                  <div>
                    <h6 class="fw-bold text-dark mb-1">No Study Materials Uploaded Yet for <?= e($selectedBatch['class_name'] ?? 'This Batch') ?></h6>
                    <p class="small text-secondary mb-2 mb-lg-0">
                      You can upload fresh notes into each module card below, or copy existing curriculum notes from an earlier batch in 1 click.
                    </p>
                    <div class="form-check form-check-inline mt-1 mb-0">
                      <input class="form-check-input" type="checkbox" name="import_hidden" value="1" id="bannerImportHidden">
                      <label class="form-check-label small text-muted" for="bannerImportHidden">Import as hidden initially (uncheck to show to students immediately)</label>
                    </div>
                  </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
                  <select name="source_class_id" class="form-select form-select-sm rounded-pill fw-semibold" style="min-width: 220px;" required>
                    <?php foreach ($sourceBatches as $sb): ?>
                      <option value="<?= (int)$sb['id'] ?>"><?= e($sb['class_name']) ?> (<?= (int)$sb['mat_count'] ?> notes)</option>
                    <?php endforeach; ?>
                  </select>
                  <button type="submit" class="btn btn-dark btn-sm rounded-pill px-3.5 py-2 fw-bold text-nowrap">
                    <i class="bi bi-cloud-download me-1"></i> Copy Notes to This Batch
                  </button>
                </div>
              </form>
            </div>
          </div>
        <?php endif; ?>

        <?php if (count($classrooms ?? []) < 18): ?>
          <!-- Helpful 1-Click Sync Banner for 18 NVQ Modules -->
          <div class="alert border-0 rounded-4 p-3 mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 shadow-xs" style="background: #eff6ff; border-left: 5px solid #2563eb !important;">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle p-2 bg-white text-primary shadow-xs d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                <i class="bi bi-mortarboard-fill fs-5 text-primary"></i>
              </div>
              <div>
                <h6 class="fw-bold mb-0.5 text-dark">NVTI Diploma Curriculum Has 18 Modules (දැනට මොඩියුල <?= count($classrooms ?? []) ?>/18 ක් සම්බන්ධ කර ඇත)</h6>
                <small class="text-secondary">මෙම කණ්ඩායම (Batch) සඳහා ඉතිරි සියලුම නිල මොඩියුල 18 (Semester 1 &amp; Semester 2) එක ක්ලික් එකකින් Classroom Hub එකට එක් කර ගන්න.</small>
              </div>
            </div>
            <form action="index.php?page=admin_sync_curriculum_modules" method="POST" class="m-0 flex-shrink-0" onsubmit="return confirm('Sync and register all 18 NVQ Curriculum Modules for this batch?');">
              <?= Csrf::field() ?>
              <input type="hidden" name="class_id" value="<?= (int)$activeClassId ?>">
              <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3.5 py-2 fw-bold text-nowrap shadow-sm d-inline-flex align-items-center gap-1.5" style="background: #2563eb; border-color: #2563eb;">
                <i class="bi bi-magic"></i>
                <span>Sync All 18 Modules Now</span>
              </button>
            </form>
          </div>
        <?php endif; ?>

        <!-- Quick Semester Filter & Count Bar -->
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
          <div class="btn-group btn-group-sm rounded-pill p-1 bg-white border shadow-xs" role="group">
            <button type="button" class="btn btn-dark rounded-pill px-3 fw-semibold active-filter-btn" onclick="filterClassroomGrid('all', this)">
              All Modules (<?= count($classrooms ?? []) ?>)
            </button>
            <button type="button" class="btn btn-light rounded-pill px-3 fw-semibold text-secondary" onclick="filterClassroomGrid('sem1', this)">
              Semester 1
            </button>
            <button type="button" class="btn btn-light rounded-pill px-3 fw-semibold text-secondary" onclick="filterClassroomGrid('sem2', this)">
              Semester 2
            </button>
          </div>
          <div class="small text-muted d-none d-md-block">
            <i class="bi bi-info-circle text-primary me-1"></i> Official NVTI 18 NVQ Level 5 Diploma Modules (M01 - M15 + EMPM01 - EMPM03)
          </div>
        </div>

        <!-- Classroom Cards Grid (Synced with Student Portal Modules) -->
        <div class="row g-4 mb-4" id="classroomGridRow">
          <?php 
            $themePalettes = [
              'blue'     => ['bg' => 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%)', 'icon' => 'bi-database-fill-gear', 'avatar_color' => '#334155'],
              'maroon'   => ['bg' => 'linear-gradient(135deg, #7A1418 0%, #991b1b 100%)', 'icon' => 'bi-camera-reels-fill', 'avatar_color' => '#7A1418'],
              'dark'     => ['bg' => 'linear-gradient(135deg, #455a64 0%, #37474f 100%)', 'icon' => 'bi-code-slash', 'avatar_color' => '#334155'],
              'charcoal' => ['bg' => 'linear-gradient(135deg, #334155 0%, #1e293b 100%)', 'icon' => 'bi-globe-americas', 'avatar_color' => '#334155'],
              'indigo'   => ['bg' => 'linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%)', 'icon' => 'bi-hdd-network-fill', 'avatar_color' => '#3b82f6'],
              'slate'    => ['bg' => 'linear-gradient(135deg, #374151 0%, #1f2937 100%)', 'icon' => 'bi-file-earmark-check-fill', 'avatar_color' => '#d97706'],
              'teal'     => ['bg' => 'linear-gradient(135deg, #0f766e 0%, #115e59 100%)', 'icon' => 'bi-brush-fill', 'avatar_color' => '#0d9488'],
              'green'    => ['bg' => 'linear-gradient(135deg, #065f46 0%, #059669 100%)', 'icon' => 'bi-journal-code', 'avatar_color' => '#059669'],
              'amber'    => ['bg' => 'linear-gradient(135deg, #78350f 0%, #d97706 100%)', 'icon' => 'bi-mortarboard-fill', 'avatar_color' => '#d97706'],
              'purple'   => ['bg' => 'linear-gradient(135deg, #4c1d95 0%, #7c3aed 100%)', 'icon' => 'bi-cpu-fill', 'avatar_color' => '#7c3aed'],
            ];
            $paletteKeys = array_keys($themePalettes);
            $cardIdx = 0;

            foreach ($classrooms as $c): 
              $sId = (int)$c['subject_id'];
              $userColorKey = (!empty($c['color_theme']) && isset($themePalettes[$c['color_theme']]))
                ? $c['color_theme']
                : $paletteKeys[$cardIdx % count($paletteKeys)];
              $cardIdx++;
              $palette = $themePalettes[$userColorKey];

              $cardBg = $palette['bg'];
              $cardIcon = !empty($c['icon']) ? $c['icon'] : $palette['icon'];
              $avatarColor = $palette['avatar_color'];
              $cardTitle = $c['subject_name'];
              $cardSubtitle = !empty($c['description']) ? $c['description'] : $c['subject_code'];

              $teacherName = $c['teacher_name'] ?: 'U.G.P.K. Nishantha';
              $avatarLetter = strtoupper(substr($teacherName, 0, 1));

              $cCode = strtoupper(trim($c['subject_code']));
              $semNum = 0;
              if (str_contains(strtolower($cardSubtitle), 'semester 1') || preg_match('/M0[1-8]/i', $cCode) || $cCode === 'EMPM02') {
                $semNum = 1;
              } elseif (str_contains(strtolower($cardSubtitle), 'semester 2') || preg_match('/M(09|1[0-5])/i', $cCode) || in_array($cCode, ['EMPM01', 'EMPM03'])) {
                $semNum = 2;
              }
              $semTag = ($semNum === 1) ? 'sem1' : (($semNum === 2) ? 'sem2' : 'other');
          ?>
            <div class="col-12 col-md-6 col-xl-4 col-xxl-3 classroom-grid-item" data-sem="<?= $semTag ?>">
              <div class="card border-0 rounded-4 shadow-sm h-100 bg-white overflow-hidden g-classroom-card transition-hover position-relative d-flex flex-column" style="min-height: 290px;">
                <!-- Top Colorful Banner Header -->
                <div class="position-relative text-white overflow-hidden d-flex flex-column justify-content-between" style="background: <?= $cardBg ?>; min-height: 150px; padding: 22px 22px 18px 22px; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                  <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                    <div class="pe-2" style="max-width: 95%;">
                      <a href="index.php?page=admin_materials&subject_id=<?= $sId ?>&class_id=<?= (int)$activeClassId ?>" class="text-white text-decoration-none">
                        <h4 class="fw-bold text-white mb-1 text-truncate text-hover-underline" style="font-size: 1.15rem; letter-spacing: -0.2px; line-height: 1.3;" title="<?= e($cardTitle) ?>">
                          <?= e($cardTitle) ?>
                        </h4>
                      </a>
                      <div class="text-white-50 text-truncate" style="font-size: 0.82rem; line-height: 1.35;" title="<?= e($cardSubtitle) ?>">
                        <?= e($cardSubtitle) ?>
                      </div>
                    </div>
                  </div>

                  <div class="position-relative mt-2" style="z-index: 2;">
                    <span class="text-white small fw-medium d-inline-flex align-items-center gap-1.5 text-truncate" style="font-size: 0.84rem; text-shadow: 0 1px 2px rgba(0,0,0,0.25);">
                      <i class="bi bi-person-fill opacity-75"></i>
                      <?= e($teacherName) ?>
                    </span>
                  </div>

                  <!-- Decorative Right-side Icon Outline -->
                  <div class="position-absolute end-0 bottom-0 p-3 opacity-20 text-white pointer-events-none" style="font-size: 4rem; line-height: 0.8; transform: translate(6px, 6px);">
                    <i class="bi <?= e($cardIcon) ?>"></i>
                  </div>
                </div>

                <!-- Floating Round Avatar Badge -->
                <div class="position-absolute" style="top: 120px; right: 22px; z-index: 5;">
                  <div class="rounded-circle shadow d-flex align-items-center justify-content-center text-white fw-bold border border-3 border-white" 
                       style="width: 56px; height: 56px; font-size: 1.3rem; background-color: <?= $avatarColor ?>;">
                    <?= $avatarLetter ?>
                  </div>
                </div>

                <!-- Card Middle Body -->
                <div class="card-body d-flex flex-column flex-grow-1 justify-content-between" style="padding: 22px 22px 18px 22px;">
                  <div class="mb-3">
                    <span class="badge bg-light text-dark border font-monospace px-2.5 py-1" style="font-size: 0.74rem;">
                      <?= e($c['subject_code']) ?>
                    </span>
                    <span class="badge bg-light text-secondary border px-2.5 py-1 ms-1" style="font-size: 0.74rem;">
                      NVTI Baddegama
                    </span>
                  </div>

                  <!-- Stats Pills & Bottom Quick Actions -->
                  <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                      <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                        <i class="bi bi-file-earmark-text me-1"></i> <?= (int)$c['materials_count'] ?> Notes
                      </span>
                      <?php if (!empty($c['hidden_materials_count'])): ?>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1" style="font-size: 0.7rem;" title="Hidden from students">
                          <i class="bi bi-eye-slash-fill me-1"></i> <?= (int)$c['hidden_materials_count'] ?> Hidden
                        </span>
                      <?php endif; ?>
                      <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                        <i class="bi bi-clipboard2-check me-1"></i> <?= (int)$c['assignments_count'] ?> Tasks
                      </span>
                    </div>

                    <!-- Bottom Right Separated Actions: Open, Quick Batch Menu & Delete -->
                    <div class="d-flex align-items-center gap-1.5" style="position: relative; z-index: 6;">
                      <!-- Open Classroom Button -->
                      <a href="index.php?page=admin_materials&subject_id=<?= $sId ?>&class_id=<?= (int)$activeClassId ?>" class="btn btn-sm btn-light border text-primary fw-bold rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1 shadow-xs hover-open-btn" title="Open Classroom Stream" style="font-size: 0.75rem;">
                        <i class="bi bi-box-arrow-in-right"></i> Open
                      </a>

                      <!-- Module Actions Dropdown -->
                      <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-light border rounded-circle d-inline-flex align-items-center justify-content-center shadow-xs" 
                                style="width: 28px; height: 28px;" data-bs-toggle="dropdown" aria-expanded="false" title="Module Options">
                          <i class="bi bi-three-dots-vertical" style="font-size: 0.78rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 py-1 small">
                          <li>
                            <form action="index.php?page=admin_module_materials_action" method="POST" onsubmit="return confirm('Hide all study materials in this class module from students?');">
                              <?= Csrf::field() ?>
                              <input type="hidden" name="subject_id" value="<?= $sId ?>">
                              <input type="hidden" name="class_id" value="<?= (int)$activeClassId ?>">
                              <input type="hidden" name="module_action" value="hide_all">
                              <input type="hidden" name="redirect_view" value="grid">
                              <button type="submit" class="dropdown-item py-1.5">
                                <i class="bi bi-eye-slash text-secondary me-2"></i> Hide All Materials (නව Batch සඳහා)
                              </button>
                            </form>
                          </li>
                          <li>
                            <form action="index.php?page=admin_module_materials_action" method="POST">
                              <?= Csrf::field() ?>
                              <input type="hidden" name="subject_id" value="<?= $sId ?>">
                              <input type="hidden" name="class_id" value="<?= (int)$activeClassId ?>">
                              <input type="hidden" name="module_action" value="unhide_all">
                              <input type="hidden" name="redirect_view" value="grid">
                              <button type="submit" class="dropdown-item py-1.5">
                                <i class="bi bi-eye text-success me-2"></i> Unhide All Materials (පෙන්වන්න)
                              </button>
                            </form>
                          </li>
                          <li><hr class="dropdown-divider my-1"></li>
                          <li>
                            <form action="index.php?page=admin_module_materials_action" method="POST" onsubmit="return confirm('WARNING: Are you sure you want to permanently delete all study materials in this module? Uploaded files will be erased.');">
                              <?= Csrf::field() ?>
                              <input type="hidden" name="subject_id" value="<?= $sId ?>">
                              <input type="hidden" name="class_id" value="<?= (int)$activeClassId ?>">
                              <input type="hidden" name="module_action" value="delete_all">
                              <input type="hidden" name="redirect_view" value="grid">
                              <button type="submit" class="dropdown-item py-1.5 text-danger fw-semibold">
                                <i class="bi bi-trash3 text-danger me-2"></i> Delete All Materials
                              </button>
                            </form>
                          </li>
                          <li><hr class="dropdown-divider my-1"></li>
                          <li>
                            <button type="button" class="dropdown-item py-1.5 text-danger fw-semibold" onclick="confirmDeleteClassModule(event, <?= $sId ?>, '<?= e(addslashes($cardTitle)) ?>')">
                              <i class="bi bi-x-circle text-danger me-2"></i> Delete Classroom Card
                            </button>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Full Card Clickable Overlay Link -->
                <a href="index.php?page=admin_materials&subject_id=<?= $sId ?>&class_id=<?= (int)$activeClassId ?>" class="stretched-link" aria-label="Open <?= e($cardTitle) ?>"></a>
              </div>
            </div>
          <?php endforeach; ?>

          <!-- ================= ADD NEW CLASS CARD (Google Classroom Style) ================= -->
          <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
            <div class="card border-2 border-dashed rounded-4 h-100 p-4 text-center d-flex flex-column align-items-center justify-content-center bg-white transition-hover position-relative" 
                 style="border-color: #cbd5e1 !important; min-height: 290px; cursor: pointer;"
                 data-bs-toggle="modal" data-bs-target="#createClassModal">
              <div class="rounded-circle p-3 mb-3 d-flex align-items-center justify-content-center text-primary shadow-sm" style="width: 64px; height: 64px; background: #fdf2f2; color: #7A1418 !important;">
                <i class="bi bi-plus-lg fs-3"></i>
              </div>
              <h5 class="fw-bold text-dark mb-1">Create Class Module</h5>
              <p class="text-muted small mb-3">Add a new subject card, assign lecturer, and start posting materials &amp; assignments.</p>
              <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3.5 fw-bold" style="border-color: #7A1418; color: #7A1418;">
                <i class="bi bi-plus-circle me-1"></i> Add Classroom
              </button>
            </div>
          </div>
        </div>

      <?php else: ?>
        <!-- ============================================================== -->
        <!-- VIEW 2: DEDICATED SUBJECT STREAM & CONTENT HUB (IMAGE 2)       -->
        <!-- ============================================================== -->

        <!-- Back Button & Breadcrumbs -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
          <a href="index.php?page=admin_materials&class_id=<?= (int)$activeClassId ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm bg-white">
            <i class="bi bi-arrow-left"></i>
            <span>All Classrooms</span>
          </a>

          <div class="d-flex align-items-center gap-2 flex-wrap">
            <select class="form-select form-select-sm rounded-pill fw-semibold border-secondary-subtle bg-white" style="min-width: 210px;"
                    onchange="window.location.href='index.php?page=admin_materials&subject_id=<?= (int)$currentSubject['id'] ?>&class_id=' + this.value">
              <?php foreach (($classes ?? []) as $cl): ?>
                <option value="<?= (int)$cl['id'] ?>" <?= ((int)$cl['id'] === (int)$activeClassId) ? 'selected' : '' ?>>
                  <?= e($cl['class_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Google Classroom Top Cover Banner -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 text-white overflow-hidden position-relative" style="background: linear-gradient(135deg, #1967d2 0%, #1558b0 100%); min-height: 180px;">
          <div class="card-body p-4 p-md-5 d-flex flex-column justify-content-between">
            <div>
              <div class="text-white-50 font-monospace small fw-semibold mb-1"><?= e($currentSubject['subject_code']) ?></div>
              <h2 class="fw-bold mb-1 text-white"><?= e($currentSubject['subject_name']) ?></h2>
              <p class="text-white-50 mb-0">Lecturer: <strong><?= e($currentSubject['teacher_name'] ?? 'U.G.P.K. Nishantha') ?></strong> &bull; <?= e($currentSubject['class_name']) ?></p>
            </div>
          </div>
          <!-- Decorative Background Icon -->
          <div class="position-absolute end-0 bottom-0 p-4 opacity-15 text-white pointer-events-none" style="font-size: 7rem; line-height: 0.8;">
            <i class="bi bi-mortarboard"></i>
          </div>
        </div>

        <!-- Google Classroom Actions Bar (Post Note, Add Link, Create Assignment) -->
        <div class="card border rounded-4 shadow-sm bg-white mb-4 p-3">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-2">
              <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="bi bi-plus-circle-fill fs-5"></i>
              </div>
              <div>
                <strong class="text-dark d-block">Post Content to Class Stream</strong>
                <small class="text-muted">Share notes, PDF materials, YouTube lessons, or announce new coursework.</small>
              </div>
            </div>

            <!-- Action Buttons Group -->
            <div class="d-flex flex-wrap align-items-center gap-2">
              <!-- Module-level Batch Actions Dropdown -->
              <div class="dropdown">
                <button class="btn btn-outline-dark btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-sliders text-primary"></i>
                  <span>Batch &amp; Module Actions</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 py-1 small">
                  <li><h6 class="dropdown-header text-uppercase small" style="font-size:0.7rem;">Batch Visibility Control</h6></li>
                  <li>
                    <form action="index.php?page=admin_module_materials_action" method="POST" onsubmit="return confirm('Hide all study materials in this module from students? (Students will not see these until you unhide them)');">
                      <?= Csrf::field() ?>
                      <input type="hidden" name="subject_id" value="<?= (int)$currentSubject['id'] ?>">
                      <input type="hidden" name="class_id" value="<?= (int)$activeClassId ?>">
                      <input type="hidden" name="module_action" value="hide_all">
                      <button type="submit" class="dropdown-item py-1.5">
                        <i class="bi bi-eye-slash text-secondary me-2"></i> Hide All Materials (නව Batch සඳහා සඟවන්න)
                      </button>
                    </form>
                  </li>
                  <li>
                    <form action="index.php?page=admin_module_materials_action" method="POST">
                      <?= Csrf::field() ?>
                      <input type="hidden" name="subject_id" value="<?= (int)$currentSubject['id'] ?>">
                      <input type="hidden" name="class_id" value="<?= (int)$activeClassId ?>">
                      <input type="hidden" name="module_action" value="unhide_all">
                      <button type="submit" class="dropdown-item py-1.5">
                        <i class="bi bi-eye text-success me-2"></i> Unhide All Materials (සියල්ල ළමයින්ට පෙන්වන්න)
                      </button>
                    </form>
                  </li>
                  <li><hr class="dropdown-divider my-1"></li>
                  <li>
                    <form action="index.php?page=admin_module_materials_action" method="POST" onsubmit="return confirm('WARNING: Are you sure you want to PERMANENTLY DELETE ALL study materials in this module? Uploaded files will be erased.');">
                      <?= Csrf::field() ?>
                      <input type="hidden" name="subject_id" value="<?= (int)$currentSubject['id'] ?>">
                      <input type="hidden" name="class_id" value="<?= (int)$activeClassId ?>">
                      <input type="hidden" name="module_action" value="delete_all">
                      <button type="submit" class="dropdown-item py-1.5 text-danger fw-bold">
                        <i class="bi bi-trash3 text-danger me-2"></i> Delete All Materials in Module (සියල්ල මකන්න)
                      </button>
                    </form>
                  </li>
                </ul>
              </div>

              <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#uploadNoteModal">
                <i class="bi bi-file-earmark-arrow-up-fill text-primary"></i>
                <span>Post Study Note (PDF/Doc)</span>
              </button>

              <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#addLinkModal">
                <i class="bi bi-link-45deg fs-6"></i>
                <span>Add Video / Web Link</span>
              </button>

              <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" style="background: #7A1418; border-color: #7A1418;" data-bs-toggle="modal" data-bs-target="#createAssignmentModal">
                <i class="bi bi-clipboard-plus-fill"></i>
                <span>Create Assignment</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Stream Feed Area (Image 2 - Google Classroom Stream) -->
        <div class="row g-4 mb-4">
          <!-- Left Sidebar / Stream Info Box -->
          <div class="col-12 col-lg-3">
            <div class="card border rounded-4 shadow-sm bg-white p-3 mb-3">
              <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history text-primary me-2"></i>Upcoming Deadlines</h6>
              <?php 
                $activeAsgs = array_filter($assignments, function($a) {
                  return strtotime($a['deadline']) >= time();
                });
              ?>
              <?php if (empty($activeAsgs)): ?>
                <p class="text-muted small mb-0">Woohoo, no work due soon!</p>
              <?php else: ?>
                <div class="list-group list-group-flush small">
                  <?php foreach (array_slice($activeAsgs, 0, 4) as $ua): ?>
                    <div class="list-group-item px-0 py-2 border-bottom">
                      <div class="fw-bold text-dark text-truncate" title="<?= e($ua['title']) ?>"><?= e($ua['title']) ?></div>
                      <div class="text-danger small mt-0.5">
                        <i class="bi bi-alarm me-1"></i>Due <?= date('M d, h:i A', strtotime($ua['deadline'])) ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="card border rounded-4 shadow-sm bg-white p-3">
              <h6 class="fw-bold text-dark mb-2"><i class="bi bi-bar-chart-fill text-success me-2"></i>Class Summary</h6>
              <div class="d-flex justify-content-between small text-muted py-1 border-bottom">
                <span>Study Materials:</span>
                <strong class="text-dark"><?= count($materials) ?></strong>
              </div>
              <div class="d-flex justify-content-between small text-muted py-1 border-bottom">
                <span>Hidden from Students:</span>
                <strong class="text-secondary">
                  <?= count(array_filter($materials, fn($m) => !empty($m['is_hidden']))) ?>
                </strong>
              </div>
              <div class="d-flex justify-content-between small text-muted py-1 border-bottom">
                <span>Coursework Tasks:</span>
                <strong class="text-dark"><?= count($assignments) ?></strong>
              </div>
              <div class="d-flex justify-content-between small text-muted py-1">
                <span>Enrolled Batch:</span>
                <strong class="text-dark">Batch 2026</strong>
              </div>
            </div>
          </div>

          <!-- Main Stream Postings Column (Image 2) -->
          <div class="col-12 col-lg-9">
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
              <div>
                <h5 class="fw-bold text-dark mb-0">Class Stream Feed</h5>
                <small class="text-muted">Manage postings, toggle student visibility, or perform bulk batch cleanup.</small>
              </div>
              <span class="badge bg-light text-secondary border rounded-pill px-3 py-1 fw-semibold align-self-start align-self-sm-auto">
                <?= count($streamFeed) ?> Items Posted
              </span>
            </div>

            <?php if (empty($streamFeed)): ?>
              <div class="card border rounded-4 shadow-sm bg-white p-5 text-center">
                <i class="bi bi-chat-square-text text-muted fs-1 mb-2"></i>
                <h5 class="fw-bold text-dark">This is where you can talk to your class</h5>
                <p class="text-muted small mb-3">Use the buttons above to post your first study note, video lecture, or assignment!</p>
              </div>
            <?php else: ?>

              <!-- Form for Bulk Operations on Materials -->
              <form id="bulkMaterialsForm" action="index.php?page=admin_bulk_materials" method="POST">
                <?= Csrf::field() ?>
                <input type="hidden" name="subject_id" value="<?= (int)$currentSubject['id'] ?>">
                <input type="hidden" name="class_id" value="<?= (int)$activeClassId ?>">
                <input type="hidden" name="bulk_action" id="bulkActionInput" value="">

                <!-- Bulk Selection Toolbar -->
                <div class="card border rounded-4 shadow-sm bg-white p-2.5 mb-3">
                  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                      <div class="form-check ms-1 mb-0">
                        <input class="form-check-input" type="checkbox" id="selectAllMaterials" onclick="toggleSelectAll(this)">
                        <label class="form-check-label small fw-bold text-dark user-select-none" for="selectAllMaterials">
                          Select All Materials
                        </label>
                      </div>
                      <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 small" id="selectedCountBadge">0 selected</span>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <span class="small text-muted fw-semibold me-1 d-none d-lg-inline">Bulk Action:</span>
                      <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold shadow-xs" onclick="submitBulk('hide')" title="Hide selected materials from students">
                        <i class="bi bi-eye-slash me-1"></i> Hide Selected
                      </button>
                      <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-xs" onclick="submitBulk('unhide')" title="Unhide & show selected to students">
                        <i class="bi bi-eye me-1"></i> Unhide Selected
                      </button>
                      <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold shadow-xs" onclick="submitBulk('delete')" title="Delete selected materials permanently">
                        <i class="bi bi-trash3 me-1"></i> Delete Selected
                      </button>
                    </div>
                  </div>
                </div>

                <div class="d-flex flex-column gap-3">
                  <?php foreach ($streamFeed as $item): 
                    $isAssignment = ($item['feed_type'] === 'assignment');
                    $isMaterial   = ($item['feed_type'] === 'material');
                    $isHidden     = !empty($item['is_hidden']);
                  ?>
                    <div class="card border rounded-4 shadow-sm bg-white p-3.5 stream-card transition-hover position-relative <?= ($isMaterial && $isHidden) ? 'border-secondary-subtle' : '' ?>" style="<?= ($isMaterial && $isHidden) ? 'background-color: #fafbfc !important;' : '' ?>">
                      <div class="d-flex align-items-start gap-3">
                        <?php if ($isMaterial): ?>
                          <!-- Checkbox for Bulk Selection -->
                          <div class="form-check pt-1 mb-0 flex-shrink-0" style="position: relative; z-index: 5;">
                            <input class="form-check-input material-checkbox" type="checkbox" name="material_ids[]" value="<?= (int)$item['id'] ?>" onchange="updateSelectedCount()">
                          </div>
                        <?php endif; ?>

                        <!-- Stream Item Round Icon (Mirroring Google Classroom Icon) -->
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 <?= $isAssignment ? 'bg-primary-subtle text-primary' : ($isHidden ? 'bg-secondary-subtle text-secondary' : 'bg-info-subtle text-info') ?>" style="width: 44px; height: 44px;">
                          <?php if ($isAssignment): ?>
                            <i class="bi bi-clipboard2-check-fill fs-5"></i>
                          <?php elseif ($item['material_type'] === 'youtube'): ?>
                            <i class="bi bi-youtube text-danger fs-5"></i>
                          <?php elseif ($item['material_type'] === 'url'): ?>
                            <i class="bi bi-link-45deg fs-4"></i>
                          <?php else: ?>
                            <i class="bi bi-file-earmark-text-fill fs-5"></i>
                          <?php endif; ?>
                        </div>

                        <!-- Stream Item Content Details -->
                        <div class="flex-grow-1 pe-2">
                          <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-1">
                            <div>
                              <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <!-- Visibility Status Badge for Materials -->
                                <?php if ($isMaterial): ?>
                                  <?php if ($isHidden): ?>
                                    <span class="badge bg-secondary text-white rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 0.72rem;">
                                      <i class="bi bi-eye-slash-fill me-1"></i> Hidden from Students
                                    </span>
                                  <?php else: ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 0.72rem;">
                                      <i class="bi bi-eye-fill me-1"></i> Visible to Students
                                    </span>
                                  <?php endif; ?>
                                <?php endif; ?>
                              </div>

                              <!-- Google Classroom Exact Title Format -->
                              <h6 class="fw-bold text-dark mb-0.5" style="font-size: 0.96rem;">
                                <?= e($item['author_name']) ?> posted a new <?= $isAssignment ? 'assignment' : 'material' ?>: 
                                <span class="text-primary"><?= e($item['title']) ?></span>
                              </h6>
                              <span class="text-muted small" style="font-size: 0.8rem;">
                                <?= date('d M Y, h:i A', strtotime($item['created_at'])) ?>
                              </span>
                            </div>

                            <!-- Actions Dropdown -->
                            <?php $canOwnDelete = AdminAccess::canDelete($item['created_by'] ?? null, $item['teacher_id'] ?? null); ?>
                            <div class="dropdown" style="position: relative; z-index: 5;">
                              <button class="btn btn-link text-muted p-1 border-0 shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical fs-5"></i>
                              </button>
                              <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 py-1">
                                <?php if ($isAssignment): ?>
                                  <li>
                                    <button type="button" class="dropdown-item py-1.5 small fw-semibold" onclick="openDeadlineModal(<?= (int)$item['id'] ?>, '<?= e(addslashes($item['title'])) ?>', '<?= date('Y-m-d\TH:i', strtotime($item['deadline'])) ?>')">
                                      <i class="bi bi-calendar-event text-warning me-2"></i> Edit / Change Deadline
                                    </button>
                                  </li>
                                  <li><hr class="dropdown-divider my-1"></li>
                                  <li>
                                    <?php if ($canOwnDelete): ?>
                                      <form action="index.php?page=admin_delete_assignment" method="POST" onsubmit="return confirm('Delete this assignment?');">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="assignment_id" value="<?= (int)$item['id'] ?>">
                                        <input type="hidden" name="subject_id" value="<?= (int)$currentSubject['id'] ?>">
                                        <input type="hidden" name="class_id" value="<?= (int)$activeClassId ?>">
                                        <button type="submit" class="dropdown-item py-1.5 small text-danger fw-semibold">
                                          <i class="bi bi-trash3 me-2"></i> Delete Assignment
                                        </button>
                                      </form>
                                    <?php else: ?>
                                      <span class="dropdown-item-text py-1.5 small text-muted">
                                        <i class="bi bi-lock-fill me-2"></i> Delete locked (Owner: <?= e($item['author_name']) ?>)
                                      </span>
                                    <?php endif; ?>
                                  </li>
                                <?php else: ?>
                                  <li>
                                    <!-- Toggle Visibility Option -->
                                    <form action="index.php?page=admin_toggle_material_visibility" method="POST">
                                      <?= Csrf::field() ?>
                                      <input type="hidden" name="material_id" value="<?= (int)$item['id'] ?>">
                                      <input type="hidden" name="subject_id" value="<?= (int)$currentSubject['id'] ?>">
                                      <input type="hidden" name="class_id" value="<?= (int)$activeClassId ?>">
                                      <button type="submit" class="dropdown-item py-1.5 small fw-semibold <?= $isHidden ? 'text-success' : 'text-secondary' ?>">
                                        <?php if ($isHidden): ?>
                                          <i class="bi bi-eye text-success me-2"></i> Unhide &amp; Show to Students
                                        <?php else: ?>
                                          <i class="bi bi-eye-slash text-secondary me-2"></i> Hide from Students
                                        <?php endif; ?>
                                      </button>
                                    </form>
                                  </li>
                                  <li><hr class="dropdown-divider my-1"></li>
                                  <li>
                                    <?php if ($canOwnDelete): ?>
                                      <form action="index.php?page=admin_delete_material" method="POST" onsubmit="return confirm('Delete this study material?');">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="material_id" value="<?= (int)$item['id'] ?>">
                                        <input type="hidden" name="subject_id" value="<?= (int)$currentSubject['id'] ?>">
                                        <input type="hidden" name="class_id" value="<?= (int)$activeClassId ?>">
                                        <button type="submit" class="dropdown-item py-1.5 small text-danger fw-semibold">
                                          <i class="bi bi-trash3 me-2"></i> Delete Material
                                        </button>
                                      </form>
                                    <?php else: ?>
                                      <span class="dropdown-item-text py-1.5 small text-muted">
                                        <i class="bi bi-lock-fill me-2"></i> Delete locked (Owner: <?= e($item['author_name']) ?>)
                                      </span>
                                    <?php endif; ?>
                                  </li>
                                <?php endif; ?>
                              </ul>
                            </div>
                          </div>

                          <!-- Description / Instructions Snippet -->
                          <?php if (!empty($item['description'])): ?>
                            <p class="text-secondary small mb-2" style="font-size: 0.86rem; line-height: 1.45;">
                              <?= nl2br(e($item['description'])) ?>
                            </p>
                          <?php endif; ?>

                          <!-- Attachment / Resource Bar -->
                          <?php if ($isAssignment): ?>
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2 pt-2 border-top">
                              <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill fw-bold">
                                  <i class="bi bi-alarm me-1"></i> Due: <?= date('d M Y, h:i A', strtotime($item['deadline'])) ?>
                                </span>
                                <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill">
                                  Points: <?= e($item['total_marks']) ?>
                                </span>
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 rounded-pill">
                                  <?= (int)$item['submissions_count'] ?> Submitted
                                </span>
                              </div>

                              <button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1.5" onclick="openDeadlineModal(<?= (int)$item['id'] ?>, '<?= e(addslashes($item['title'])) ?>', '<?= date('Y-m-d\TH:i', strtotime($item['deadline'])) ?>')">
                                <i class="bi bi-calendar2-range"></i>
                                <span>Change Deadline</span>
                              </button>
                            </div>
                          <?php elseif ($isMaterial): ?>
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2 pt-2 border-top">
                              <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill small">
                                  <?= e($item['category']) ?>
                                </span>
                                <?php if (!empty($item['file_size'])): ?>
                                  <span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill small">
                                    <?= round($item['file_size'] / 1024, 1) ?> KB
                                  </span>
                                <?php endif; ?>
                              </div>

                              <div class="d-flex align-items-center gap-2 flex-wrap" style="position: relative; z-index: 5;">
                                <!-- Quick Visibility Toggle Button -->
                                <form action="index.php?page=admin_toggle_material_visibility" method="POST" class="d-inline">
                                  <?= Csrf::field() ?>
                                  <input type="hidden" name="material_id" value="<?= (int)$item['id'] ?>">
                                  <input type="hidden" name="subject_id" value="<?= (int)$currentSubject['id'] ?>">
                                  <input type="hidden" name="class_id" value="<?= (int)$activeClassId ?>">
                                  <?php if ($isHidden): ?>
                                    <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-2.5 py-1 fw-bold d-inline-flex align-items-center gap-1 shadow-xs" title="Unhide this material so students can view and download">
                                      <i class="bi bi-eye-fill"></i> Unhide
                                    </button>
                                  <?php else: ?>
                                    <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1 shadow-xs" title="Hide this material from students">
                                      <i class="bi bi-eye-slash-fill"></i> Hide
                                    </button>
                                  <?php endif; ?>
                                </form>

                                <?php if ($item['material_type'] === 'file'): ?>
                                  <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1" onclick="previewMaterial(<?= (int)$item['id'] ?>, '<?= e(addslashes($item['title'])) ?>', '<?= e(addslashes($item['original_filename'])) ?>')">
                                    <i class="bi bi-eye"></i> View Online
                                  </button>
                                  <a href="index.php?page=download_material&id=<?= (int)$item['id'] ?>" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" style="background: #7A1418; border-color: #7A1418;">
                                    <i class="bi bi-download"></i> Download
                                  </a>
                                <?php else: ?>
                                  <a href="<?= e($item['external_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-box-arrow-up-right"></i> Open Link
                                  </a>
                                <?php endif; ?>
                              </div>
                            </div>
                          <?php endif; ?>

                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </form>
            <?php endif; ?>
          </div>
        </div>

      <?php endif; ?>

    </div>
  </main>
</div>

<!-- ================= MODAL 1: POST STUDY NOTE / MATERIAL ================= -->
<div class="modal fade" id="uploadNoteModal" tabindex="-1" aria-labelledby="uploadNoteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <form action="index.php?page=admin_upload_material" method="POST" enctype="multipart/form-data">
        <?= Csrf::field() ?>
        <input type="hidden" name="subject_id" value="<?= (int)($currentSubject['id'] ?? 0) ?>">
        <input type="hidden" name="class_id" value="<?= (int)$activeClassId ?>">
        <input type="hidden" name="material_type" value="file">

        <div class="modal-header border-bottom py-3 px-4 bg-light">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-arrow-up-fill text-primary fs-5"></i>
            <h6 class="modal-title fw-bold text-dark mb-0" id="uploadNoteModalLabel">Post Study Note / Material</h6>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-bold text-dark small">Material Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="title" placeholder="e.g. EER Note, Lecture 02 Normalization Slides" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold text-dark small">Category <span class="text-danger">*</span></label>
            <select name="category_id" class="form-select" required>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= e($cat['category_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold text-dark small">Posted By</label>
            <input type="text" class="form-control bg-light fw-semibold" value="<?= e(Auth::name()) ?>" readonly>
            <input type="hidden" name="teacher_id" value="<?= (int)Auth::id() ?>">
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold text-dark small">Upload File (PDF, DOCX, PPTX, SQL, ZIP) <span class="text-danger">*</span></label>
            <input type="file" class="form-control" name="file_upload" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.txt,.sql" required>
            <small class="text-muted">Maximum file size: 25 MB.</small>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold text-dark small">Description / Notes (Optional)</label>
            <textarea class="form-control" name="description" rows="3" placeholder="Brief summary of what this note covers..."></textarea>
          </div>

          <input type="hidden" name="email_prompt_present" value="1">
          <div class="form-check form-switch bg-light border rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between">
            <div>
              <label class="form-check-label fw-semibold text-dark small mb-0" for="adminMaterialFileNotifyEmail">
                <i class="bi bi-envelope-check text-primary me-1"></i> Notify enrolled students via Email
              </label>
              <div class="text-muted" style="font-size: 0.74rem;">
                <?= EmailSettings::isCategoryEnabled('material') ? 'Uncheck if you want to post silently without emailing students.' : 'Currently disabled globally in Email Settings.' ?>
              </div>
            </div>
            <input class="form-check-input ms-2" type="checkbox" role="switch" id="adminMaterialFileNotifyEmail" name="send_email_notification" value="1" <?= EmailSettings::shouldDefaultCheckAction('material') ? 'checked' : '' ?> <?= !EmailSettings::isCategoryEnabled('material') ? 'disabled' : '' ?>>
          </div>
        </div>

        <div class="modal-footer bg-light border-top px-4 py-2.5">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold" style="background: #7A1418; border-color: #7A1418;">
            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Post to Stream
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= MODAL 2: ADD YOUTUBE / WEB LINK ================= -->
<div class="modal fade" id="addLinkModal" tabindex="-1" aria-labelledby="addLinkModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <form action="index.php?page=admin_upload_material" method="POST">
        <?= Csrf::field() ?>
        <input type="hidden" name="subject_id" value="<?= (int)($currentSubject['id'] ?? 0) ?>">
        <input type="hidden" name="class_id" value="<?= (int)$activeClassId ?>">
        <input type="hidden" name="category_id" value="6"> <!-- Video Lessons -->

        <div class="modal-header border-bottom py-3 px-4 bg-light">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-link-45deg text-info fs-4"></i>
            <h6 class="modal-title fw-bold text-dark mb-0" id="addLinkModalLabel">Add YouTube Video or External Link</h6>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-bold text-dark small">Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="title" placeholder="e.g. Complete PHP 8 & MySQL Tutorial Series" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold text-dark small">Resource Type <span class="text-danger">*</span></label>
            <select name="material_type" class="form-select" required>
              <option value="youtube">YouTube Video / Playlist</option>
              <option value="url">External Documentation / Website</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold text-dark small">URL Address <span class="text-danger">*</span></label>
            <input type="url" class="form-control" name="external_url" placeholder="https://www.youtube.com/watch?v=..." required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold text-dark small">Description</label>
            <textarea class="form-control" name="description" rows="2" placeholder="Video summary or directions..."></textarea>
          </div>

          <input type="hidden" name="email_prompt_present" value="1">
          <div class="form-check form-switch bg-light border rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between">
            <div>
              <label class="form-check-label fw-semibold text-dark small mb-0" for="adminMaterialLinkNotifyEmail">
                <i class="bi bi-envelope-check text-primary me-1"></i> Notify enrolled students via Email
              </label>
              <div class="text-muted" style="font-size: 0.74rem;">
                <?= EmailSettings::isCategoryEnabled('material') ? 'Uncheck to post silently without emailing students.' : 'Currently disabled globally in Email Settings.' ?>
              </div>
            </div>
            <input class="form-check-input ms-2" type="checkbox" role="switch" id="adminMaterialLinkNotifyEmail" name="send_email_notification" value="1" <?= EmailSettings::shouldDefaultCheckAction('material') ? 'checked' : '' ?> <?= !EmailSettings::isCategoryEnabled('material') ? 'disabled' : '' ?>>
          </div>
        </div>

        <div class="modal-footer bg-light border-top px-4 py-2.5">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-info btn-sm rounded-pill px-4 fw-bold text-white">
            <i class="bi bi-plus-circle me-1"></i> Add Resource Link
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= MODAL 3: CREATE ASSIGNMENT ================= -->
<div class="modal fade" id="createAssignmentModal" tabindex="-1" aria-labelledby="createAssignmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 rounded-4 shadow">
      <form action="index.php?page=admin_create_assignment" method="POST" enctype="multipart/form-data">
        <?= Csrf::field() ?>
        <input type="hidden" name="subject_id" value="<?= (int)($currentSubject['id'] ?? 0) ?>">
        <input type="hidden" name="class_id" value="<?= (int)$activeClassId ?>">

        <div class="modal-header border-bottom py-3 px-4 bg-light">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-clipboard2-plus-fill text-primary fs-5"></i>
            <h6 class="modal-title fw-bold text-dark mb-0" id="createAssignmentModalLabel">Create Coursework Assignment</h6>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-bold text-dark small">Assignment Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="title" placeholder="e.g. Assignment 01-Written Submission (DBMS Architecture & ERD)" required>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
              <label class="form-label fw-bold text-dark small">Deadline (Due Date &amp; Time) <span class="text-danger">*</span></label>
              <input type="datetime-local" class="form-control" name="deadline" value="<?= date('Y-m-d\T23:59', strtotime('+14 days')) ?>" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label fw-bold text-dark small">Total Points / Maximum Marks</label>
              <input type="number" class="form-control" name="total_marks" value="100" min="1" max="1000">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold text-dark small">Instructions for Students <span class="text-danger">*</span></label>
            <textarea class="form-control" name="instructions" rows="4" placeholder="Specify tasks, submission guidelines, format required, and evaluation criteria..." required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold text-dark small">Attach Question Paper / Reference Guide (Optional)</label>
            <input type="file" class="form-control" name="reference_file" accept=".pdf,.doc,.docx,.zip,.sql,.txt">
            <small class="text-muted">PDF, Word document, SQL script, or ZIP archive.</small>
          </div>

          <input type="hidden" name="email_prompt_present" value="1">
          <div class="form-check form-switch bg-light border rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between">
            <div>
              <label class="form-check-label fw-semibold text-dark small mb-0" for="adminMatAsgNotifyEmail">
                <i class="bi bi-envelope-check text-primary me-1"></i> Notify enrolled students via Email
              </label>
              <div class="text-muted" style="font-size: 0.74rem;">
                <?= EmailSettings::isCategoryEnabled('assignment') ? 'Uncheck to publish assignment silently without emailing students.' : 'Currently disabled globally in Email Settings.' ?>
              </div>
            </div>
            <input class="form-check-input ms-2" type="checkbox" role="switch" id="adminMatAsgNotifyEmail" name="send_email_notification" value="1" <?= EmailSettings::shouldDefaultCheckAction('assignment') ? 'checked' : '' ?> <?= !EmailSettings::isCategoryEnabled('assignment') ? 'disabled' : '' ?>>
          </div>
        </div>

        <div class="modal-footer bg-light border-top px-4 py-2.5">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold" style="background: #7A1418; border-color: #7A1418;">
            <i class="bi bi-clipboard2-check-fill me-1"></i> Post Assignment
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= MODAL 4: CHANGE ASSIGNMENT DEADLINE ================= -->
<div class="modal fade" id="deadlineModal" tabindex="-1" aria-labelledby="deadlineModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <form action="index.php?page=admin_update_assignment_deadline" method="POST">
        <?= Csrf::field() ?>
        <input type="hidden" name="assignment_id" id="deadlineModalAsgId" value="">
        <input type="hidden" name="subject_id" value="<?= (int)($currentSubject['id'] ?? 0) ?>">
        <input type="hidden" name="class_id" value="<?= (int)$activeClassId ?>">

        <div class="modal-header border-bottom py-3 px-4 bg-light">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-calendar2-range text-warning fs-5"></i>
            <h6 class="modal-title fw-bold text-dark mb-0" id="deadlineModalLabel">Update Assignment Deadline</h6>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-bold text-dark small">Assignment</label>
            <input type="text" class="form-control bg-light fw-bold" id="deadlineModalAsgTitle" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold text-dark small">New Deadline (Date &amp; Time) <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control" name="deadline" id="deadlineModalInput" required>
            <div class="form-text small text-muted">
              Students will be able to submit their work up until this date and time.
            </div>
          </div>
        </div>

        <div class="modal-footer bg-light border-top px-4 py-2.5">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning btn-sm rounded-pill px-4 fw-bold text-dark shadow-sm">
            <i class="bi bi-check-circle-fill me-1"></i> Update Deadline
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= MODAL 5: INLINE DOCUMENT VIEWER ================= -->
<div class="modal fade" id="previewViewerModal" tabindex="-1" aria-labelledby="previewViewerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 960px;">
    <div class="modal-content border-0 rounded-4 shadow overflow-hidden">
      <div class="modal-header border-bottom py-3 px-4 bg-light">
        <div class="d-flex align-items-center gap-2 min-w-0">
          <i class="bi bi-file-earmark-richtext-fill text-danger fs-5"></i>
          <div class="min-w-0">
            <h6 class="modal-title fw-bold text-dark mb-0 text-truncate" id="previewDocTitle">Document Preview</h6>
            <small class="text-muted d-block text-truncate" id="previewDocSub"></small>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
          <div id="adminOfficeSwitcher" class="btn-group btn-group-sm d-none" role="group">
            <button type="button" id="adminBtnOffice" class="btn btn-primary rounded-start-pill px-2.5" onclick="switchAdminOfficeEngine('office')">Office</button>
            <button type="button" id="adminBtnGoogle" class="btn btn-outline-secondary rounded-end-pill px-2.5" onclick="switchAdminOfficeEngine('google')">Google</button>
          </div>
          <a href="#" id="previewDocDownloadBtn" class="btn btn-sm btn-outline-primary rounded-pill px-3">
            <i class="bi bi-download me-1"></i> Download
          </a>
          <a href="#" id="previewDocNewTabBtn" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-box-arrow-up-right me-1"></i> Open Full View
          </a>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      <div class="modal-body p-0 position-relative" style="height: 580px; background: #1e293b;">
        <div id="adminPreviewSpinner" class="position-absolute top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center text-white" style="z-index: 5; background: rgba(15,23,42,0.9);">
          <div class="spinner-border text-light me-2" role="status"></div>
          <span>Loading preview...</span>
        </div>

        <iframe id="previewDocFrame" src="about:blank" class="w-100 h-100 border-0 bg-white d-block" onload="hideAdminPreviewSpinner()"></iframe>

        <div id="adminTextContainer" class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column" style="z-index: 4; background: #0f172a;">
          <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom border-secondary-subtle" style="background: #1e293b;">
            <span class="text-light small font-monospace" id="adminTextFilename">preview.sql</span>
            <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-2.5 py-0.5 small" onclick="navigator.clipboard.writeText(document.getElementById('adminTextContent').textContent || '')">
              <i class="bi bi-clipboard me-1"></i>Copy
            </button>
          </div>
          <pre id="adminTextContent" class="flex-grow-1 m-0 p-3 text-light overflow-auto" style="font-size: 13px; line-height: 1.55;"></pre>
        </div>

        <div id="adminImageContainer" class="position-absolute top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center bg-dark p-3" style="z-index: 4;">
          <img id="adminPreviewImage" src="" alt="Preview" class="img-fluid rounded shadow" style="max-height: 100%; max-width: 100%; object-fit: contain;">
        </div>

        <div id="adminUnsupportedContainer" class="position-absolute top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center bg-light p-4 text-center" style="z-index: 6;">
          <div style="max-width: 420px;">
            <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
              <i class="bi bi-file-earmark-arrow-down-fill fs-2"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1" id="adminUnsupportedTitle">Preview Not Available Inline</h6>
            <p class="small text-muted mb-3" id="adminUnsupportedDesc">Archive files cannot be previewed inside this popup.</p>
            <a href="#" id="adminUnsupportedDownloadBtn" class="btn btn-primary btn-sm rounded-pill px-4 py-2 fw-bold">
              <i class="bi bi-download me-1"></i> Download File
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ================= MODAL 6: CREATE NEW CLASS MODULE ================= -->
<div class="modal fade" id="createClassModal" tabindex="-1" aria-labelledby="createClassModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 rounded-4 shadow">
      <form action="index.php?page=admin_create_classroom" method="POST">
        <?= Csrf::field() ?>

        <div class="modal-header border-bottom py-3 px-4 bg-light">
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
              <i class="bi bi-mortarboard-fill fs-5"></i>
            </div>
            <div>
              <h6 class="modal-title fw-bold text-dark mb-0" id="createClassModalLabel">Create New Class / Subject Module</h6>
              <small class="text-muted">Adds a new card to the Google Classroom Hub</small>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4 custom-theme-picker">
          <!-- Preset Picker from 18 NVQ Curriculum Modules -->
          <div class="mb-3.5 p-3 bg-light rounded-3 border">
            <label class="form-label fw-bold text-dark small mb-1.5 d-flex align-items-center justify-content-between">
              <span><i class="bi bi-magic text-danger me-1"></i> Auto-Fill from Official 18 NVQ Modules (ප්‍රතීතනය ලත් මොඩියුල 18න් තෝරන්න)</span>
              <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill" style="font-size: 0.68rem;">18 NVQ Modules</span>
            </label>
            <select id="curriculumModulePresetSelect" class="form-select form-select-sm fw-medium shadow-xs" onchange="applyModulePreset(this)">
              <option value="">-- Choose an NVQ Module to Auto-Fill Title, Code &amp; Details --</option>
              <optgroup label="Semester 1 (Modules 1 - 9)">
                <?php foreach (($curriculumModules ?? []) as $cm): if ((int)$cm['semester'] === 1): ?>
                  <option value="<?= e($cm['module_code']) ?>" 
                          data-title="<?= e($cm['module_title']) ?>" 
                          data-code="<?= e($cm['module_code']) ?>" 
                          data-desc="<?= e("Semester 1 • " . ($cm['module_type'] ?? 'Technical') . " Module") ?>"
                          data-type="<?= e($cm['module_type']) ?>">
                    <?= e($cm['module_code']) ?> &bull; <?= e($cm['module_title']) ?>
                  </option>
                <?php endif; endforeach; ?>
              </optgroup>
              <optgroup label="Semester 2 (Modules 10 - 18)">
                <?php foreach (($curriculumModules ?? []) as $cm): if ((int)$cm['semester'] === 2): ?>
                  <option value="<?= e($cm['module_code']) ?>" 
                          data-title="<?= e($cm['module_title']) ?>" 
                          data-code="<?= e($cm['module_code']) ?>" 
                          data-desc="<?= e("Semester 2 • " . ($cm['module_type'] ?? 'Technical') . " Module") ?>"
                          data-type="<?= e($cm['module_type']) ?>">
                    <?= e($cm['module_code']) ?> &bull; <?= e($cm['module_title']) ?>
                  </option>
                <?php endif; endforeach; ?>
              </optgroup>
            </select>
          </div>

          <!-- Section 1: Module Information -->
          <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
            <span class="badge rounded-circle p-1 bg-danger text-white d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 0.72rem; background: #7A1418 !important;">1</span>
            <span class="fw-bold small text-uppercase text-secondary" style="letter-spacing: 0.5px;">Module Information (විෂය මූලික විස්තර)</span>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-12 col-md-8">
              <label class="form-label fw-bold text-dark small mb-1">
                Class / Module Title <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control" name="subject_name" placeholder="e.g. Cloud Computing &amp; DevOps" required>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label fw-bold text-dark small mb-1">
                Subject Code <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control font-monospace text-uppercase" name="subject_code" placeholder="e.g. K72T001U10" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-bold text-dark small mb-1">Subtitle / Competency Area</label>
              <input type="text" class="form-control" name="description" placeholder="e.g. AWS Cloud Infrastructure, Microservices &amp; Docker">
            </div>
          </div>

          <!-- Section 2: Lecturer & Enrollment -->
          <div class="d-flex align-items-center gap-2 mb-3 mt-4 pb-2 border-bottom">
            <span class="badge rounded-circle p-1 bg-danger text-white d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 0.72rem; background: #7A1418 !important;">2</span>
            <span class="fw-bold small text-uppercase text-secondary" style="letter-spacing: 0.5px;">Academic Assignment (දේශක සහ ශිෂ්‍ය කණ්ඩායම)</span>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
              <label class="form-label fw-bold text-dark small mb-1">Assigned Lecturer / Instructor <span class="text-danger">*</span></label>
              <select name="teacher_id" class="form-select" required>
                <?php foreach ($teachers as $t): ?>
                  <option value="<?= $t['id'] ?>"><?= e($t['name']) ?> (<?= e($t['email']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label fw-bold text-dark small mb-1">Batch / Class Enrollment <span class="text-danger">*</span></label>
              <select name="class_id" class="form-select" required>
                <?php if (!empty($classes)): ?>
                  <?php foreach ($classes as $cl): ?>
                    <option value="<?= (int)$cl['id'] ?>" <?= ((int)$cl['id'] === (int)$activeClassId) ? 'selected' : '' ?>>
                      <?= e($cl['class_name']) ?> (<?= ($cl['status'] ?? '') === 'active' ? 'Active' : 'Archived' ?>)
                    </option>
                  <?php endforeach; ?>
                <?php else: ?>
                  <option value="<?= (int)$activeClassId ?>">Current Active Batch</option>
                <?php endif; ?>
              </select>
            </div>
          </div>

          <!-- Section 3: Visual Theme & Appearance -->
          <div class="d-flex align-items-center gap-2 mb-3 mt-4 pb-2 border-bottom">
            <span class="badge rounded-circle p-1 bg-danger text-white d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 0.72rem; background: #7A1418 !important;">3</span>
            <span class="fw-bold small text-uppercase text-secondary" style="letter-spacing: 0.5px;">Card Styling &amp; Appearance (වර්ණය සහ Icon එක)</span>
          </div>

          <!-- Banner Color Theme -->
          <div class="mb-4">
            <label class="form-label fw-bold text-dark small mb-2 d-block">Class Banner Theme Color</label>
            <div class="d-flex flex-wrap gap-2">
              <input type="radio" class="btn-check" name="color_theme" id="ct_blue" value="blue" checked autocomplete="off">
              <label class="btn btn-outline-secondary border bg-white d-flex align-items-center gap-2 py-1.5 px-3 rounded-pill" for="ct_blue">
                <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #1967d2;"></span>
                <span class="small fw-semibold text-dark">Royal Blue</span>
              </label>

              <input type="radio" class="btn-check" name="color_theme" id="ct_maroon" value="maroon" autocomplete="off">
              <label class="btn btn-outline-secondary border bg-white d-flex align-items-center gap-2 py-1.5 px-3 rounded-pill" for="ct_maroon">
                <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #7A1418;"></span>
                <span class="small fw-semibold text-dark">NVTI Maroon</span>
              </label>

              <input type="radio" class="btn-check" name="color_theme" id="ct_dark" value="dark" autocomplete="off">
              <label class="btn btn-outline-secondary border bg-white d-flex align-items-center gap-2 py-1.5 px-3 rounded-pill" for="ct_dark">
                <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #37474f;"></span>
                <span class="small fw-semibold text-dark">Slate Grey</span>
              </label>

              <input type="radio" class="btn-check" name="color_theme" id="ct_indigo" value="indigo" autocomplete="off">
              <label class="btn btn-outline-secondary border bg-white d-flex align-items-center gap-2 py-1.5 px-3 rounded-pill" for="ct_indigo">
                <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #1e3a8a;"></span>
                <span class="small fw-semibold text-dark">Deep Indigo</span>
              </label>

              <input type="radio" class="btn-check" name="color_theme" id="ct_teal" value="teal" autocomplete="off">
              <label class="btn btn-outline-secondary border bg-white d-flex align-items-center gap-2 py-1.5 px-3 rounded-pill" for="ct_teal">
                <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #0f766e;"></span>
                <span class="small fw-semibold text-dark">Teal Green</span>
              </label>

              <input type="radio" class="btn-check" name="color_theme" id="ct_green" value="green" autocomplete="off">
              <label class="btn btn-outline-secondary border bg-white d-flex align-items-center gap-2 py-1.5 px-3 rounded-pill" for="ct_green">
                <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #065f46;"></span>
                <span class="small fw-semibold text-dark">Emerald</span>
              </label>

              <input type="radio" class="btn-check" name="color_theme" id="ct_amber" value="amber" autocomplete="off">
              <label class="btn btn-outline-secondary border bg-white d-flex align-items-center gap-2 py-1.5 px-3 rounded-pill" for="ct_amber">
                <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #b45309;"></span>
                <span class="small fw-semibold text-dark">Warm Amber</span>
              </label>

              <input type="radio" class="btn-check" name="color_theme" id="ct_purple" value="purple" autocomplete="off">
              <label class="btn btn-outline-secondary border bg-white d-flex align-items-center gap-2 py-1.5 px-3 rounded-pill" for="ct_purple">
                <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #581c87;"></span>
                <span class="small fw-semibold text-dark">Purple</span>
              </label>
            </div>
          </div>

          <!-- Decorative Watermark Icon -->
          <div>
            <label class="form-label fw-bold text-dark small mb-2 d-block">Decorative Watermark Icon</label>
            
            <!-- Default Auto Detect Card -->
            <input type="radio" class="btn-check" name="icon" id="ic_auto" value="auto" checked autocomplete="off">
            <label class="btn btn-outline-secondary border bg-white w-100 text-start p-2.5 rounded-3 mb-2.5 d-flex align-items-center justify-content-between" for="ic_auto">
              <div class="d-flex align-items-center gap-2.5">
                <div class="rounded-circle p-2 bg-warning-subtle text-warning d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                  <i class="bi bi-stars fs-6"></i>
                </div>
                <div>
                  <div class="fw-bold small text-dark">Auto-Detect from Subject Title (Recommended)</div>
                  <div class="text-muted" style="font-size: 0.74rem;">විෂය නාමය අනුව නියම Icon එක ස්වයංක්‍රීයව තෝරාගනී (Database, Web, Code, Network, Video...)</div>
                </div>
              </div>
              <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-semibold">Default</span>
            </label>

            <!-- Manual Selection 3x3 Grid -->
            <div class="row g-2">
              <div class="col-6 col-md-4">
                <input type="radio" class="btn-check" name="icon" id="ic_db" value="bi-database-fill-gear" autocomplete="off">
                <label class="btn btn-outline-secondary border bg-white w-100 text-start p-2 rounded-3 d-flex align-items-center gap-2" for="ic_db">
                  <i class="bi bi-database-fill-gear text-primary fs-5 flex-shrink-0"></i>
                  <div class="text-truncate">
                    <div class="fw-semibold small text-dark">Database</div>
                    <div class="text-muted" style="font-size: 0.68rem;">SQL, DBMS</div>
                  </div>
                </label>
              </div>

              <div class="col-6 col-md-4">
                <input type="radio" class="btn-check" name="icon" id="ic_code" value="bi-code-slash" autocomplete="off">
                <label class="btn btn-outline-secondary border bg-white w-100 text-start p-2 rounded-3 d-flex align-items-center gap-2" for="ic_code">
                  <i class="bi bi-code-slash text-dark fs-5 flex-shrink-0"></i>
                  <div class="text-truncate">
                    <div class="fw-semibold small text-dark">Programming</div>
                    <div class="text-muted" style="font-size: 0.68rem;">Code, OOP, Java</div>
                  </div>
                </label>
              </div>

              <div class="col-6 col-md-4">
                <input type="radio" class="btn-check" name="icon" id="ic_web" value="bi-globe-americas" autocomplete="off">
                <label class="btn btn-outline-secondary border bg-white w-100 text-start p-2 rounded-3 d-flex align-items-center gap-2" for="ic_web">
                  <i class="bi bi-globe-americas text-info fs-5 flex-shrink-0"></i>
                  <div class="text-truncate">
                    <div class="fw-semibold small text-dark">Web Design</div>
                    <div class="text-muted" style="font-size: 0.68rem;">HTML, CSS, Web</div>
                  </div>
                </label>
              </div>

              <div class="col-6 col-md-4">
                <input type="radio" class="btn-check" name="icon" id="ic_network" value="bi-hdd-network-fill" autocomplete="off">
                <label class="btn btn-outline-secondary border bg-white w-100 text-start p-2 rounded-3 d-flex align-items-center gap-2" for="ic_network">
                  <i class="bi bi-hdd-network-fill text-primary fs-5 flex-shrink-0"></i>
                  <div class="text-truncate">
                    <div class="fw-semibold small text-dark">Network / Cyber</div>
                    <div class="text-muted" style="font-size: 0.68rem;">Hardware, Security</div>
                  </div>
                </label>
              </div>

              <div class="col-6 col-md-4">
                <input type="radio" class="btn-check" name="icon" id="ic_cloud" value="bi-cloud-check-fill" autocomplete="off">
                <label class="btn btn-outline-secondary border bg-white w-100 text-start p-2 rounded-3 d-flex align-items-center gap-2" for="ic_cloud">
                  <i class="bi bi-cloud-check-fill text-primary fs-5 flex-shrink-0"></i>
                  <div class="text-truncate">
                    <div class="fw-semibold small text-dark">Cloud &amp; Server</div>
                    <div class="text-muted" style="font-size: 0.68rem;">AWS, Hosting</div>
                  </div>
                </label>
              </div>

              <div class="col-6 col-md-4">
                <input type="radio" class="btn-check" name="icon" id="ic_graphics" value="bi-brush-fill" autocomplete="off">
                <label class="btn btn-outline-secondary border bg-white w-100 text-start p-2 rounded-3 d-flex align-items-center gap-2" for="ic_graphics">
                  <i class="bi bi-brush-fill text-success fs-5 flex-shrink-0"></i>
                  <div class="text-truncate">
                    <div class="fw-semibold small text-dark">Graphic Design</div>
                    <div class="text-muted" style="font-size: 0.68rem;">Photoshop, Vector</div>
                  </div>
                </label>
              </div>

              <div class="col-6 col-md-4">
                <input type="radio" class="btn-check" name="icon" id="ic_video" value="bi-camera-reels-fill" autocomplete="off">
                <label class="btn btn-outline-secondary border bg-white w-100 text-start p-2 rounded-3 d-flex align-items-center gap-2" for="ic_video">
                  <i class="bi bi-camera-reels-fill text-danger fs-5 flex-shrink-0"></i>
                  <div class="text-truncate">
                    <div class="fw-semibold small text-dark">Cinematography</div>
                    <div class="text-muted" style="font-size: 0.68rem;">Video, Media</div>
                  </div>
                </label>
              </div>

              <div class="col-6 col-md-4">
                <input type="radio" class="btn-check" name="icon" id="ic_mobile" value="bi-phone-fill" autocomplete="off">
                <label class="btn btn-outline-secondary border bg-white w-100 text-start p-2 rounded-3 d-flex align-items-center gap-2" for="ic_mobile">
                  <i class="bi bi-phone-fill text-secondary fs-5 flex-shrink-0"></i>
                  <div class="text-truncate">
                    <div class="fw-semibold small text-dark">Mobile Apps</div>
                    <div class="text-muted" style="font-size: 0.68rem;">Android, Flutter</div>
                  </div>
                </label>
              </div>

              <div class="col-6 col-md-4">
                <input type="radio" class="btn-check" name="icon" id="ic_academic" value="bi-mortarboard-fill" autocomplete="off">
                <label class="btn btn-outline-secondary border bg-white w-100 text-start p-2 rounded-3 d-flex align-items-center gap-2" for="ic_academic">
                  <i class="bi bi-mortarboard-fill text-warning fs-5 flex-shrink-0"></i>
                  <div class="text-truncate">
                    <div class="fw-semibold small text-dark">Academic / NVQ</div>
                    <div class="text-muted" style="font-size: 0.68rem;">Theory, General</div>
                  </div>
                </label>
              </div>
            </div>
          </div>
        </div>

        <style>
        .custom-theme-picker .btn-check:checked + label {
          border-color: #7A1418 !important;
          background-color: #fdf2f2 !important;
          box-shadow: 0 0 0 2px rgba(122, 20, 24, 0.2) !important;
        }
        .custom-theme-picker .btn-check:checked + label .text-dark,
        .custom-theme-picker .btn-check:checked + label .fw-bold,
        .custom-theme-picker .btn-check:checked + label .fw-semibold {
          color: #7A1418 !important;
        }
        .custom-theme-picker label:hover {
          border-color: #cbd5e1 !important;
          background-color: #f8fafc !important;
        }
        </style>

        <div class="modal-footer bg-light border-top px-4 py-2.5">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm" style="background: #7A1418; border-color: #7A1418;">
            <i class="bi bi-plus-circle-fill me-1"></i> Create Class Card
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= MODAL 7: CONFIRM DELETE CLASS MODULE ================= -->
<div class="modal fade" id="deleteClassModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-body p-4 text-center">
        <div class="rounded-circle p-3 bg-danger-subtle text-danger mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
          <i class="bi bi-trash3-fill fs-4"></i>
        </div>
        <h6 class="fw-bold text-dark mb-1">Delete Class Module?</h6>
        <p class="text-muted small mb-3">
          Are you sure you want to delete <span id="delClassModuleName" class="fw-bold text-dark"></span>? This will remove this card from your Google Classroom Hub.
        </p>
        <form action="index.php?page=admin_delete_classroom" method="POST">
          <?= Csrf::field() ?>
          <input type="hidden" name="subject_id" id="delClassModuleSubjectId" value="">
          <input type="hidden" name="class_id" value="<?= (int)$activeClassId ?>">
          <div class="d-flex gap-2 justify-content-center">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold">Yes, Delete</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ================= MODAL 8: GLOBAL BATCH MATERIALS VISIBILITY & RESET ================= -->
<div class="modal fade" id="batchMaterialControlModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 rounded-4 shadow overflow-hidden">
      <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #7A1418 0%, #991b1b 100%);">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-white text-dark rounded-pill px-2.5 py-0.5 font-monospace fw-bold" style="font-size: 0.72rem;">
              <i class="bi bi-sliders2-vertical me-1"></i> BATCH TRANSITION &amp; VISIBILITY HUB
            </span>
          </div>
          <h5 class="modal-title fw-bold text-white mb-0">
            Study Material Visibility &amp; Batch Copy (<?= e($selectedBatch['class_name'] ?? 'Current Batch') ?>)
          </h5>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light">
        <?php if (!empty($sourceBatches)): ?>
          <!-- Copy / Import from Previous Batch -->
          <div class="card border border-primary-subtle rounded-4 bg-white p-3.5 mb-3 shadow-xs">
            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-bold">
                <i class="bi bi-files me-1"></i> COPY NOTES FROM EARLIER BATCH
              </span>
            </div>
            <h6 class="fw-bold text-dark mb-1">Import Study Materials into <?= e($selectedBatch['class_name'] ?? 'This Batch') ?></h6>
            <p class="text-secondary small mb-3">
              කලින් Batch එකක දාලා තියෙන PDF / YouTube / Notes මේ Batch එකේ ළමයින්ටත් ලබා දීමට 1 click එකෙන් copy කරන්න.
            </p>
            <form action="index.php?page=admin_copy_batch_materials" method="POST" class="d-flex flex-column gap-2">
              <?= Csrf::field() ?>
              <input type="hidden" name="target_class_id" value="<?= (int)$activeClassId ?>">
              <div class="d-flex flex-wrap gap-2 align-items-center">
                <select name="source_class_id" class="form-select form-select-sm flex-grow-1 fw-semibold" required>
                  <?php foreach ($sourceBatches as $sb): ?>
                    <option value="<?= (int)$sb['id'] ?>"><?= e($sb['class_name']) ?> (<?= (int)$sb['mat_count'] ?> notes)</option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3.5 py-2 fw-bold text-nowrap">
                  <i class="bi bi-cloud-download me-1"></i> Copy Materials Here
                </button>
              </div>
              <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" name="import_hidden" value="1" id="modalImportHidden">
                <label class="form-check-label small text-muted" for="modalImportHidden">
                  Import as hidden initially (uncheck to make notes visible to students immediately)
                </label>
              </div>
            </form>
          </div>
        <?php endif; ?>

        <form action="index.php?page=admin_global_materials_action" method="POST" id="globalMaterialsForm">
          <?= Csrf::field() ?>
          <input type="hidden" name="class_id" value="<?= (int)$activeClassId ?>">

          <!-- Target Selector -->
          <div class="card border rounded-4 bg-white p-3.5 mb-3 shadow-xs">
            <label class="form-label fw-bold text-dark mb-1">
              <i class="bi bi-layers-fill text-primary me-1"></i> Select Target Subject / Class Module:
            </label>
            <p class="text-muted small mb-2">Choose whether you want to apply this action across ALL modules in this batch or to a specific class module.</p>
            <select name="target_subject_id" class="form-select border-secondary-subtle rounded-3 fw-semibold">
              <option value="0" selected>⚡ All Modules in This Batch (මෙම Batch එකේ සියලුම Modules සඳහා)</option>
              <?php foreach (($classrooms ?? []) as $cls): ?>
                <option value="<?= (int)$cls['subject_id'] ?>">
                  <?= e($cls['subject_name']) ?> (<?= e($cls['subject_code']) ?> &bull; <?= (int)$cls['materials_count'] ?> notes)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Section 1: Visibility Control (Recommended for New Batches) -->
          <div class="card border rounded-4 bg-white p-3.5 mb-3 shadow-xs">
            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold">
                <i class="bi bi-shield-check me-1"></i> RECOMMENDED METHOD (FILES PRESERVED)
              </span>
            </div>
            <h6 class="fw-bold text-dark mb-1">Hide or Unhide Study Materials (සඟවන්න / පෙන්වන්න)</h6>
            <p class="text-secondary small mb-3">
              Upload කර ඇති Study Materials (PDF, Doc, YouTube Links) <strong>Delete නොකර</strong>, Batch එකේ ළමයින්ට නොපෙනෙන පරිදි Hide කර තබා ගත හැක. පසුව පාඩම උගන්වන විට Unhide කර ළමයින්ට ලබාදිය හැක.
            </p>

            <div class="d-flex flex-wrap gap-2">
              <button type="submit" name="global_action" value="hide_all" class="btn btn-warning rounded-pill px-3.5 py-2 fw-bold text-dark shadow-xs d-inline-flex align-items-center gap-2">
                <i class="bi bi-eye-slash-fill"></i>
                <span>Hide Materials (සඟවන්න)</span>
              </button>

              <button type="submit" name="global_action" value="unhide_all" class="btn btn-outline-success rounded-pill px-3.5 py-2 fw-bold shadow-xs d-inline-flex align-items-center gap-2">
                <i class="bi bi-eye-fill"></i>
                <span>Unhide Materials (ළමයින්ට පෙන්වන්න)</span>
              </button>
            </div>
          </div>

          <!-- Section 2: Permanent Deletion (Danger Zone) -->
          <div class="card border rounded-4 bg-white p-3.5 shadow-xs" style="border-left: 4px solid #dc2626 !important;">
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-0.5 fw-bold">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> DANGER ZONE
              </span>
            </div>
            <h6 class="fw-bold text-dark mb-1">Permanently Delete Study Materials (ස්ථිරවම මකා දැමීම)</h6>
            <p class="text-danger small mb-3">
              අවවාදයයි: ඔබ තෝරාගත් Module එකෙහි (හෝ මෙම Batch එකේ සියලු Modules වල) ඇති Study Materials පද්ධතියෙන් ස්ථිරවම Delete වේ.
            </p>

            <div>
              <button type="submit" name="global_action" value="delete_all" class="btn btn-outline-danger btn-sm rounded-pill px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-xs" onclick="return confirm('WARNING: Are you absolutely sure? Selected study materials in this batch will be permanently deleted.');">
                <i class="bi bi-trash3-fill"></i>
                <span>Permanently Delete Selected Materials</span>
              </button>
            </div>
          </div>
        </form>
      </div>

      <div class="modal-footer bg-white border-top px-4 py-2.5">
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<style>
.hover-open-btn:hover {
  background-color: #1967d2 !important;
  color: #ffffff !important;
  border-color: #1967d2 !important;
}
.hover-del-btn:hover {
  background-color: #dc3545 !important;
  color: #ffffff !important;
  border-color: #dc3545 !important;
}
</style>

<script>
function confirmDeleteClassModule(event, subjectId, subjectName) {
  if (event) {
    event.stopPropagation();
    event.preventDefault();
  }
  document.getElementById('delClassModuleSubjectId').value = subjectId;
  document.getElementById('delClassModuleName').textContent = subjectName;
  const modal = new bootstrap.Modal(document.getElementById('deleteClassModal'));
  modal.show();
}

function openDeadlineModal(asgId, title, currentDeadline) {
  document.getElementById('deadlineModalAsgId').value = asgId;
  document.getElementById('deadlineModalAsgTitle').value = title;
  document.getElementById('deadlineModalInput').value = currentDeadline;

  const modal = new bootstrap.Modal(document.getElementById('deadlineModal'));
  modal.show();
}

let adminPreviewTimer = null;
let adminOfficeUrls = { office: '', google: '' };

function showAdminPreviewSpinner() {
  const sp = document.getElementById('adminPreviewSpinner');
  if (sp) { sp.classList.remove('d-none'); sp.classList.add('d-flex'); }
}

function hideAdminPreviewSpinner() {
  const sp = document.getElementById('adminPreviewSpinner');
  if (sp) { sp.classList.add('d-none'); sp.classList.remove('d-flex'); }
}

function switchAdminOfficeEngine(engine) {
  const frame = document.getElementById('previewDocFrame');
  const bOff = document.getElementById('adminBtnOffice');
  const bGoo = document.getElementById('adminBtnGoogle');
  if (!adminOfficeUrls[engine]) return;

  showAdminPreviewSpinner();
  frame.src = adminOfficeUrls[engine];
  clearTimeout(adminPreviewTimer);
  adminPreviewTimer = setTimeout(hideAdminPreviewSpinner, 4500);

  if (bOff && bGoo) {
    bOff.className = 'btn rounded-start-pill px-2.5 ' + (engine === 'office' ? 'btn-primary' : 'btn-outline-secondary');
    bGoo.className = 'btn rounded-end-pill px-2.5 ' + (engine === 'google' ? 'btn-primary' : 'btn-outline-secondary');
  }
}

function previewMaterial(matId, title, filename) {
  clearTimeout(adminPreviewTimer);
  adminOfficeUrls = { office: '', google: '' };

  const viewUrl = 'index.php?page=view_material&id=' + matId;
  const downloadUrl = 'index.php?page=download_material&id=' + matId;
  const ext = (filename || '').split('.').pop().toLowerCase();

  const frame = document.getElementById('previewDocFrame');
  const textBox = document.getElementById('adminTextContainer');
  const imgBox = document.getElementById('adminImageContainer');
  const imgEl = document.getElementById('adminPreviewImage');
  const unsupBox = document.getElementById('adminUnsupportedContainer');
  const switcher = document.getElementById('adminOfficeSwitcher');

  document.getElementById('previewDocTitle').textContent = title;
  document.getElementById('previewDocSub').textContent = filename || '';
  document.getElementById('previewDocNewTabBtn').href = viewUrl;
  document.getElementById('previewDocDownloadBtn').href = downloadUrl;

  showAdminPreviewSpinner();
  frame.classList.remove('d-none');
  frame.src = 'about:blank';
  textBox.classList.add('d-none'); textBox.classList.remove('d-flex');
  imgBox.classList.add('d-none'); imgBox.classList.remove('d-flex');
  imgEl.src = '';
  unsupBox.classList.add('d-none'); unsupBox.classList.remove('d-flex');
  switcher.classList.add('d-none');

  if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext)) {
    frame.classList.add('d-none');
    imgEl.onload = hideAdminPreviewSpinner;
    imgEl.onerror = hideAdminPreviewSpinner;
    imgEl.src = viewUrl;
    imgBox.classList.remove('d-none'); imgBox.classList.add('d-flex');
  } else if (['ppt', 'pptx', 'doc', 'docx', 'xls', 'xlsx', 'sql', 'txt'].includes(ext)) {
    fetch('index.php?page=preview_meta&kind=material&id=' + matId)
      .then(r => r.json())
      .then(data => {
        if (data && data.ok && data.mode === 'office' && data.office_url) {
          adminOfficeUrls.office = data.office_url;
          adminOfficeUrls.google = data.google_url || '';
          switcher.classList.remove('d-none');
          switchAdminOfficeEngine('office');
        } else if (data && data.ok && data.mode === 'text') {
          frame.classList.add('d-none');
          document.getElementById('adminTextFilename').textContent = data.filename || filename || 'preview';
          document.getElementById('adminTextContent').textContent = data.text_content || '';
          textBox.classList.remove('d-none'); textBox.classList.add('d-flex');
          hideAdminPreviewSpinner();
        } else {
          frame.classList.add('d-none');
          document.getElementById('adminUnsupportedTitle').textContent = (filename || title) + ' (' + ext.toUpperCase() + ')';
          document.getElementById('adminUnsupportedDesc').textContent = data && !data.is_public_domain
            ? 'Online Office preview works on the live HTTPS portal. On localhost, click Download to view this file.'
            : 'Preview unavailable for this file. Click Download to open it on your device.';
          document.getElementById('adminUnsupportedDownloadBtn').href = downloadUrl;
          unsupBox.classList.remove('d-none'); unsupBox.classList.add('d-flex');
          hideAdminPreviewSpinner();
        }
      })
      .catch(() => {
        frame.classList.add('d-none');
        document.getElementById('adminUnsupportedTitle').textContent = (filename || title) + ' (' + ext.toUpperCase() + ')';
        document.getElementById('adminUnsupportedDownloadBtn').href = downloadUrl;
        unsupBox.classList.remove('d-none'); unsupBox.classList.add('d-flex');
        hideAdminPreviewSpinner();
      });
  } else if (['zip', 'rar'].includes(ext)) {
    frame.classList.add('d-none');
    document.getElementById('adminUnsupportedTitle').textContent = (filename || title) + ' (' + ext.toUpperCase() + ')';
    document.getElementById('adminUnsupportedDesc').textContent = 'Compressed archive files cannot be previewed inline. Click Download to extract and open.';
    document.getElementById('adminUnsupportedDownloadBtn').href = downloadUrl;
    unsupBox.classList.remove('d-none'); unsupBox.classList.add('d-flex');
    hideAdminPreviewSpinner();
  } else {
    frame.src = viewUrl;
    adminPreviewTimer = setTimeout(hideAdminPreviewSpinner, 1800);
  }

  const modal = new bootstrap.Modal(document.getElementById('previewViewerModal'));
  modal.show();
}

document.getElementById('previewViewerModal')?.addEventListener('hidden.bs.modal', function () {
  clearTimeout(adminPreviewTimer);
  document.getElementById('previewDocFrame').src = 'about:blank';
  document.getElementById('adminPreviewImage').src = '';
});

function toggleSelectAll(master) {
  const checkboxes = document.querySelectorAll('.material-checkbox');
  checkboxes.forEach(cb => cb.checked = master.checked);
  updateSelectedCount();
}

function updateSelectedCount() {
  const checked = document.querySelectorAll('.material-checkbox:checked').length;
  const badge = document.getElementById('selectedCountBadge');
  if (badge) {
    badge.textContent = checked + ' selected';
    if (checked > 0) {
      badge.classList.replace('text-secondary', 'text-primary');
      badge.classList.replace('bg-light', 'bg-primary-subtle');
    } else {
      badge.classList.replace('text-primary', 'text-secondary');
      badge.classList.replace('bg-primary-subtle', 'bg-light');
    }
  }
}

function submitBulk(action) {
  const checked = document.querySelectorAll('.material-checkbox:checked').length;
  if (checked === 0) {
    alert('Please select at least one study material first.');
    return;
  }
  if (action === 'delete') {
    if (!confirm('Are you sure you want to permanently delete ' + checked + ' selected study material(s)? This will also permanently erase the uploaded physical files.')) {
      return;
    }
  } else if (action === 'hide') {
    if (!confirm('Hide ' + checked + ' selected material(s) from students?')) {
      return;
    }
  }
  document.getElementById('bulkActionInput').value = action;
  document.getElementById('bulkMaterialsForm').submit();
}

function applyModulePreset(sel) {
  const opt = sel.options[sel.selectedIndex];
  if (!opt || !opt.value) return;
  const title = opt.getAttribute('data-title') || '';
  const code = opt.getAttribute('data-code') || '';
  const desc = opt.getAttribute('data-desc') || '';

  const modal = document.getElementById('createClassModal');
  if (!modal) return;

  const titleInput = modal.querySelector('input[name="subject_name"]');
  const codeInput = modal.querySelector('input[name="subject_code"]');
  const descInput = modal.querySelector('input[name="description"]');

  if (titleInput) titleInput.value = title;
  if (codeInput) codeInput.value = code;
  if (descInput) descInput.value = desc;

  // Smart color theme selection based on module code
  const codeUp = code.toUpperCase();
  let themeId = 'ct_blue';
  if (codeUp === 'K72T001M04' || codeUp === 'K72T001M09' || codeUp === 'K72T001M14') themeId = 'ct_dark';
  else if (codeUp === 'K72T001M05' || codeUp === 'K72T001M12') themeId = 'ct_maroon';
  else if (codeUp === 'K72T001M02' || codeUp === 'K72T001M11') themeId = 'ct_indigo';
  else if (codeUp === 'K72T001M03' || codeUp === 'K72T001M13') themeId = 'ct_teal';
  else if (codeUp === 'K72T001M08' || codeUp === 'EMPM03') themeId = 'ct_amber';
  else if (codeUp === 'EMPM01' || codeUp === 'EMPM02') themeId = 'ct_purple';

  const themeRadio = modal.querySelector('#' + themeId);
  if (themeRadio) themeRadio.checked = true;

  // Reset icon to auto-detect
  const icAuto = modal.querySelector('#ic_auto');
  if (icAuto) icAuto.checked = true;
}

function filterClassroomGrid(filter, btn) {
  const gridRow = document.getElementById('classroomGridRow');
  if (!gridRow) return;

  const btnGroup = btn.closest('.btn-group');
  if (btnGroup) {
    btnGroup.querySelectorAll('button').forEach(b => {
      b.classList.remove('btn-dark', 'active-filter-btn');
      b.classList.add('btn-light', 'text-secondary');
    });
    btn.classList.remove('btn-light', 'text-secondary');
    btn.classList.add('btn-dark', 'active-filter-btn');
  }

  const items = gridRow.querySelectorAll('.classroom-grid-item');
  items.forEach(item => {
    if (filter === 'all') {
      item.style.display = '';
    } else {
      const sem = item.getAttribute('data-sem');
      item.style.display = (sem === filter) ? '' : 'none';
    }
  });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
