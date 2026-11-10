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
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
              <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-semibold">
                    <i class="bi bi-mortarboard-fill me-1"></i> Google Classroom Hub
                  </span>
                  <span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1">
                    NVQ Level 5 CBT
                  </span>
                </div>
                <h3 class="fw-bold text-dark mb-1">Subject Classes &amp; Learning Modules</h3>
                <p class="text-muted small mb-0">
                  Select a class to manage study notes, stream postings, create coursework assignments, and adjust deadlines.
                </p>
                <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                  <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 small">
                    <i class="bi bi-collection-fill text-primary me-1"></i> <?= (int)($totalMaterials ?? 0) ?> Total Materials
                  </span>
                  <?php if (!empty($totalHiddenMaterials)): ?>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 fw-bold small">
                      <i class="bi bi-eye-slash-fill me-1"></i> <?= (int)$totalHiddenMaterials ?> Hidden (New Batch Mode)
                    </span>
                  <?php else: ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small">
                      <i class="bi bi-eye-fill me-1"></i> All Visible to Students
                    </span>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Top Action Controls -->
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Batch Material Visibility & Reset Button -->
                <button type="button" class="btn btn-warning rounded-pill px-3.5 py-2 fw-bold text-dark d-inline-flex align-items-center gap-2 shadow-xs" data-bs-toggle="modal" data-bs-target="#batchMaterialControlModal">
                  <i class="bi bi-sliders2-vertical fs-6"></i>
                  <span>Batch Material Visibility / Reset</span>
                </button>

                <button type="button" class="btn btn-primary rounded-pill px-3.5 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2" style="background: #7A1418; border-color: #7A1418;" data-bs-toggle="modal" data-bs-target="#createClassModal">
                  <i class="bi bi-plus-lg fs-6"></i>
                  <span>+ Create Class Module</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Classroom Cards Grid (Exactly like Google Classroom) -->
        <div class="row g-4 mb-4">
          <?php 
            // Color Presets for Google Classroom themes
            $themePalettes = [
              'blue'     => ['bg' => 'linear-gradient(135deg, #1967d2 0%, #174ea6 100%)', 'icon' => 'bi-database-fill-gear', 'avatar_color' => '#5f6368'],
              'maroon'   => ['bg' => 'linear-gradient(135deg, #7A1418 0%, #991b1b 100%)', 'icon' => 'bi-camera-reels-fill', 'avatar_color' => '#7A1418'],
              'dark'     => ['bg' => 'linear-gradient(135deg, #455a64 0%, #37474f 100%)', 'icon' => 'bi-code-slash', 'avatar_color' => '#5f6368'],
              'charcoal' => ['bg' => 'linear-gradient(135deg, #334155 0%, #1e293b 100%)', 'icon' => 'bi-globe-americas', 'avatar_color' => '#5f6368'],
              'indigo'   => ['bg' => 'linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%)', 'icon' => 'bi-hdd-network-fill', 'avatar_color' => '#3b82f6'],
              'slate'    => ['bg' => 'linear-gradient(135deg, #374151 0%, #1f2937 100%)', 'icon' => 'bi-file-earmark-check-fill', 'avatar_color' => '#d97706'],
              'teal'     => ['bg' => 'linear-gradient(135deg, #0f766e 0%, #115e59 100%)', 'icon' => 'bi-brush-fill', 'avatar_color' => '#0d9488'],
              'green'    => ['bg' => 'linear-gradient(135deg, #065f46 0%, #047857 100%)', 'icon' => 'bi-journal-code', 'avatar_color' => '#059669'],
              'amber'    => ['bg' => 'linear-gradient(135deg, #b45309 0%, #92400e 100%)', 'icon' => 'bi-mortarboard-fill', 'avatar_color' => '#d97706'],
              'purple'   => ['bg' => 'linear-gradient(135deg, #581c87 0%, #3b0764 100%)', 'icon' => 'bi-cpu-fill', 'avatar_color' => '#7e22ce'],
            ];

            // Classroom Theme Palettes & Illustration Icons (Mirroring Google Classroom screenshot)
            $classThemes = [
              1 => ['bg' => 'linear-gradient(135deg, #1967d2 0%, #174ea6 100%)', 'sub' => 'Database Design & Development', 'icon' => 'bi-database-fill-gear', 'tag' => 'Database II', 'avatar' => 'N', 'avatar_color' => '#5f6368'],
              2 => ['bg' => 'linear-gradient(135deg, #455a64 0%, #37474f 100%)', 'sub' => 'Software Development (PHP & OOP)', 'icon' => 'bi-code-slash', 'tag' => 'Programming 26', 'avatar' => 'N', 'avatar_color' => '#5f6368'],
              3 => ['bg' => 'linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%)', 'sub' => 'NVQ L05 - Cloud & Security', 'icon' => 'bi-hdd-network-fill', 'tag' => 'ICTDip අලුත්', 'avatar' => 'D', 'avatar_color' => '#3b82f6'],
              4 => ['bg' => 'linear-gradient(135deg, #374151 0%, #1f2937 100%)', 'sub' => 'ICT V - Baddegama Requirements', 'icon' => 'bi-file-earmark-check-fill', 'tag' => '2026_ICT5', 'avatar' => 'H', 'avatar_color' => '#d97706'],
              6 => ['bg' => 'linear-gradient(135deg, #0f766e 0%, #115e59 100%)', 'sub' => 'Computer Graphics designing', 'icon' => 'bi-brush-fill', 'tag' => 'Graphics26{NVQ5}', 'avatar' => 'N', 'avatar_color' => '#5f6368'],
              7 => ['bg' => 'linear-gradient(135deg, #991b1b 0%, #7f1d1d 100%)', 'sub' => 'Cinematography & Video Production', 'icon' => 'bi-camera-reels-fill', 'tag' => 'Cinematography 2026', 'avatar' => 'N', 'avatar_color' => '#5f6368'],
              8 => ['bg' => 'linear-gradient(135deg, #334155 0%, #1e293b 100%)', 'sub' => 'Web Design, Development & Hosting', 'icon' => 'bi-globe-americas', 'tag' => 'Web Design & Hosting', 'avatar' => 'N', 'avatar_color' => '#5f6368'],
            ];

            foreach ($classrooms as $c): 
              $sId = (int)$c['subject_id'];
              $userColorKey = $c['color_theme'] ?? 'blue';
              $palette = $themePalettes[$userColorKey] ?? $themePalettes['blue'];

              $theme = $classThemes[$sId] ?? [
                'bg' => $palette['bg'],
                'sub' => !empty($c['description']) ? $c['description'] : $c['subject_code'],
                'icon' => !empty($c['icon']) ? $c['icon'] : $palette['icon'],
                'tag' => $c['subject_name'],
                'avatar' => 'N',
                'avatar_color' => $palette['avatar_color']
              ];
              if (!empty($c['icon'])) {
                $theme['icon'] = $c['icon'];
              }
              if (!empty($c['color_theme']) && isset($themePalettes[$c['color_theme']])) {
                $theme['bg'] = $themePalettes[$c['color_theme']]['bg'];
              }
              $teacherName = $c['teacher_name'] ?? 'U.G.P.K. Nishantha';
              if (stripos($teacherName, 'Nishantha') !== false) {
                  $avatarLetter = 'N';
              } elseif (stripos($teacherName, 'Gayan') !== false) {
                  $avatarLetter = 'G';
              } else {
                  $avatarLetter = strtoupper(substr($teacherName, 0, 1));
              }
          ?>
            <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
              <div class="card border-0 rounded-4 shadow-sm h-100 bg-white overflow-hidden g-classroom-card transition-hover position-relative d-flex flex-column" style="min-height: 290px;">
                <!-- Top Colorful Banner Header (Google Classroom Style with Centered Text & Ample Padding) -->
                <div class="position-relative text-white overflow-hidden d-flex flex-column justify-content-between" style="background: <?= $theme['bg'] ?>; min-height: 145px; padding: 22px 22px 18px 22px; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                  <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                    <div class="pe-2" style="max-width: 95%;">
                      <a href="index.php?page=admin_materials&subject_id=<?= $sId ?>" class="text-white text-decoration-none">
                        <h4 class="fw-bold text-white mb-1 text-truncate text-hover-underline" style="font-size: 1.25rem; letter-spacing: -0.2px; line-height: 1.3;" title="<?= e($c['subject_name']) ?>">
                          <?= e($theme['tag']) ?>
                        </h4>
                      </a>
                      <div class="text-white-50 text-truncate" style="font-size: 0.83rem; line-height: 1.35;">
                        <?= e($theme['sub']) ?>
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
                    <i class="bi <?= $theme['icon'] ?>"></i>
                  </div>
                </div>

                <!-- Floating Round Avatar Badge (Google Classroom Signature Feature) -->
                <div class="position-absolute" style="top: 116px; right: 22px; z-index: 5;">
                  <div class="rounded-circle shadow d-flex align-items-center justify-content-center text-white fw-bold border border-3 border-white" 
                       style="width: 58px; height: 58px; font-size: 1.35rem; background-color: <?= $theme['avatar_color'] ?>;">
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

                  <!-- Stats Pills & Bottom Quick Actions (Clearly Separated) -->
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
                      <a href="index.php?page=admin_materials&subject_id=<?= $sId ?>" class="btn btn-sm btn-light border text-primary fw-bold rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1 shadow-xs hover-open-btn" title="Open Classroom Stream" style="font-size: 0.75rem;">
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
                              <input type="hidden" name="class_id" value="1">
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
                              <input type="hidden" name="class_id" value="1">
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
                              <input type="hidden" name="class_id" value="1">
                              <input type="hidden" name="module_action" value="delete_all">
                              <input type="hidden" name="redirect_view" value="grid">
                              <button type="submit" class="dropdown-item py-1.5 text-danger fw-semibold">
                                <i class="bi bi-trash3 text-danger me-2"></i> Delete All Materials
                              </button>
                            </form>
                          </li>
                          <li><hr class="dropdown-divider my-1"></li>
                          <li>
                            <button type="button" class="dropdown-item py-1.5 text-danger fw-semibold" onclick="confirmDeleteClassModule(event, <?= $sId ?>, '<?= e(addslashes($theme['tag'])) ?>')">
                              <i class="bi bi-x-circle text-danger me-2"></i> Delete Classroom Card
                            </button>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Full Card Clickable Overlay Link -->
                <a href="index.php?page=admin_materials&subject_id=<?= $sId ?>" class="stretched-link" aria-label="Open <?= e($theme['tag']) ?>"></a>
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
        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
          <a href="index.php?page=admin_materials" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm bg-white">
            <i class="bi bi-arrow-left"></i>
            <span>All Classrooms</span>
          </a>

          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill">
              <i class="bi bi-shield-check text-primary me-1"></i> Administrator Mode
            </span>
          </div>
        </div>

        <!-- Google Classroom Top Cover Banner -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 text-white overflow-hidden position-relative" style="background: linear-gradient(135deg, #1967d2 0%, #1558b0 100%); min-height: 180px;">
          <div class="card-body p-4 p-md-5 d-flex flex-column justify-content-between">
            <div>
              <span class="badge bg-white bg-opacity-25 text-white mb-2 px-3 py-1 rounded-pill fw-semibold">
                <?= e($currentSubject['subject_code']) ?> &bull; NVQ Level 5 CBT
              </span>
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
                      <input type="hidden" name="class_id" value="1">
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
                      <input type="hidden" name="class_id" value="1">
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
                      <input type="hidden" name="class_id" value="1">
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
                                    <form action="index.php?page=admin_delete_assignment" method="POST" onsubmit="return confirm('Delete this assignment?');">
                                      <?= Csrf::field() ?>
                                      <input type="hidden" name="assignment_id" value="<?= (int)$item['id'] ?>">
                                      <input type="hidden" name="subject_id" value="<?= (int)$currentSubject['id'] ?>">
                                      <button type="submit" class="dropdown-item py-1.5 small text-danger fw-semibold">
                                        <i class="bi bi-trash3 me-2"></i> Delete Assignment
                                      </button>
                                    </form>
                                  </li>
                                <?php else: ?>
                                  <li>
                                    <!-- Toggle Visibility Option -->
                                    <form action="index.php?page=admin_toggle_material_visibility" method="POST">
                                      <?= Csrf::field() ?>
                                      <input type="hidden" name="material_id" value="<?= (int)$item['id'] ?>">
                                      <input type="hidden" name="subject_id" value="<?= (int)$currentSubject['id'] ?>">
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
                                    <form action="index.php?page=admin_delete_material" method="POST" onsubmit="return confirm('Delete this study material?');">
                                      <?= Csrf::field() ?>
                                      <input type="hidden" name="material_id" value="<?= (int)$item['id'] ?>">
                                      <input type="hidden" name="subject_id" value="<?= (int)$currentSubject['id'] ?>">
                                      <button type="submit" class="dropdown-item py-1.5 small text-danger fw-semibold">
                                        <i class="bi bi-trash3 me-2"></i> Delete Material
                                      </button>
                                    </form>
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
        <input type="hidden" name="class_id" value="1">
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
            <label class="form-label fw-bold text-dark small">Posting Lecturer <span class="text-danger">*</span></label>
            <select name="teacher_id" class="form-select" required>
              <?php foreach ($teachers as $t): ?>
                <option value="<?= $t['id'] ?>" <?= (isset($currentSubject) && $currentSubject['teacher_id'] == $t['id']) ? 'selected' : '' ?>>
                  <?= e($t['name']) ?> (<?= e($t['email']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold text-dark small">Upload File (PDF, DOCX, PPTX) <span class="text-danger">*</span></label>
            <input type="file" class="form-control" name="file_upload" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip" required>
            <small class="text-muted">Maximum file size: 25 MB.</small>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold text-dark small">Description / Notes (Optional)</label>
            <textarea class="form-control" name="description" rows="3" placeholder="Brief summary of what this note covers..."></textarea>
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
        <input type="hidden" name="class_id" value="1">
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
        <input type="hidden" name="class_id" value="1">

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
            <input type="file" class="form-control" name="reference_file" accept=".pdf,.doc,.docx,.zip">
            <small class="text-muted">PDF or Word document with assignment questions.</small>
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
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 920px;">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-bottom py-3 px-4 bg-light">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-file-earmark-pdf-fill text-danger fs-5"></i>
          <div>
            <h6 class="modal-title fw-bold text-dark mb-0" id="previewDocTitle">Document Preview</h6>
            <small class="text-muted" id="previewDocSub"></small>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <a href="#" id="previewDocNewTabBtn" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-box-arrow-up-right me-1"></i> Open Full View
          </a>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      <div class="modal-body p-0" style="min-height: 540px; background: #525659;">
        <iframe id="previewDocFrame" src="" style="width: 100%; height: 580px; border: none;"></iframe>
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
                    <option value="<?= $cl['id'] ?>"><?= e($cl['class_name']) ?></option>
                  <?php endforeach; ?>
                <?php else: ?>
                  <option value="1">NVTI Baddegama ICT L5 - Batch 2026</option>
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
          <input type="hidden" name="class_id" value="1">
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
          <h5 class="modal-title fw-bold text-white mb-0">Study Material Visibility &amp; Batch Management</h5>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="index.php?page=admin_global_materials_action" method="POST" id="globalMaterialsForm">
        <?= Csrf::field() ?>
        <div class="modal-body p-4 bg-light">
          <!-- Target Selector -->
          <div class="card border rounded-4 bg-white p-3.5 mb-3 shadow-xs">
            <label class="form-label fw-bold text-dark mb-1">
              <i class="bi bi-layers-fill text-primary me-1"></i> Select Target Subject / Class Module:
            </label>
            <p class="text-muted small mb-2">Choose whether you want to apply this action across ALL modules in the diploma or to a specific class module.</p>
            <select name="target_subject_id" class="form-select border-secondary-subtle rounded-3 fw-semibold">
              <option value="0" selected>⚡ All Modules System-Wide (සියලුම Subject Classes සඳහා)</option>
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
              පරණ Upload කර ඇති Study Materials (PDF, Doc, YouTube Links) <strong>Delete නොකර</strong>, නව Batch එකේ ළමයින්ට නොපෙනෙන පරිදි Hide කර තබා ගත හැක. පසුව සර්ලාට හෝ Adminට පාඩම උගන්වන විට අදාළ Material එක Unhide කර ළමයින්ට ලබාදිය හැක.
            </p>

            <div class="d-flex flex-wrap gap-2">
              <button type="submit" name="global_action" value="hide_all" class="btn btn-warning rounded-pill px-3.5 py-2 fw-bold text-dark shadow-xs d-inline-flex align-items-center gap-2">
                <i class="bi bi-eye-slash-fill"></i>
                <span>Hide Materials (නව Batch සඳහා සඟවන්න)</span>
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
              අවවාදයයි: ඔබ තෝරාගත් Module එකෙහි (හෝ All Modules වල) ඇති Study Materials සහ Upload කර ඇති භෞතික Files පද්ධතියෙන් ස්ථිරවම Delete වේ.
            </p>

            <div>
              <button type="submit" name="global_action" value="delete_all" class="btn btn-outline-danger btn-sm rounded-pill px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-xs" onclick="return confirm('WARNING: Are you absolutely sure? All selected study materials and physical files will be permanently erased and cannot be recovered.');">
                <i class="bi bi-trash3-fill"></i>
                <span>Permanently Delete Selected Materials</span>
              </button>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-white border-top px-4 py-2.5">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
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

function previewMaterial(matId, title, filename) {
  const url = 'index.php?page=view_material&id=' + matId;
  document.getElementById('previewDocTitle').textContent = title;
  document.getElementById('previewDocSub').textContent = filename;
  document.getElementById('previewDocNewTabBtn').href = url;
  document.getElementById('previewDocFrame').src = url;

  const modal = new bootstrap.Modal(document.getElementById('previewViewerModal'));
  modal.show();
}

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
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
