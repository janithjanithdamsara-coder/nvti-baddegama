<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

$activeTab = $_GET['tab'] ?? 'classroom';
$selectedSubjectId = (int)($_GET['subject_id'] ?? 0);
?>

<div class="app-wrapper">
  <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <div class="container-fluid">
      <?= Flash::render() ?>

      <!-- Header Banner -->
      <div class="card border rounded-4 mb-4 p-3 p-md-4 bg-white shadow-sm">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
          <div class="text-center text-md-start">
            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 mb-2">
              <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background: #fef2f2; color: #7A1418; border: 1px solid #fecaca; font-size: 0.74rem;">
                <i data-lucide="graduation-cap" style="width: 13px; height: 13px;" class="me-1"></i> NVQ Level 5 &bull; Semester 1 &amp; 2
              </span>
              <span class="badge bg-light text-secondary border fw-medium rounded-pill">ICT Curriculum</span>
            </div>
            <h3 class="fw-bold mb-1 text-dark">Learning Materials &amp; Classroom Hub</h3>
            <p class="text-muted small mb-0">Explore lecture notes, videos, and study resources uploaded for your subject classes.</p>
          </div>
          <div class="d-flex align-items-center gap-2 flex-wrap justify-content-center">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-bold">
              <i data-lucide="files" style="width: 14px; height: 14px;" class="me-1 align-middle"></i> <?= count($materials) ?> Materials Uploaded
            </span>
          </div>
        </div>
      </div>

      <!-- ============================================================== -->
      <!-- TAB 1: SUBJECT CLASSES (Centered Responsive Classroom Cards)   -->
      <!-- ============================================================== -->
      <?php if ($activeTab === 'classroom'): ?>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <div>
              <h5 class="fw-bold mb-0 text-dark">Enrolled Subject Classes</h5>
              <small class="text-muted">Click any class to explore lecture notes and resources</small>
            </div>
            <div class="btn-group btn-group-sm rounded-pill p-1 bg-white border shadow-xs" role="group">
              <button type="button" class="btn btn-dark rounded-pill px-3 fw-semibold active-filter-btn" onclick="filterStudentClassrooms('all', this)">
                All Modules (<?= count($subjects) ?>)
              </button>
              <button type="button" class="btn btn-light rounded-pill px-3 fw-semibold text-secondary" onclick="filterStudentClassrooms('sem1', this)">
                Semester 1
              </button>
              <button type="button" class="btn btn-light rounded-pill px-3 fw-semibold text-secondary" onclick="filterStudentClassrooms('sem2', this)">
                Semester 2
              </button>
            </div>
          </div>
          <span class="badge bg-light text-dark border rounded-pill px-3 py-1.5 fw-semibold">
            <?= count($subjects) ?> Modules Active
          </span>
        </div>

        <div class="row g-4 mb-4" id="studentClassroomGridRow">
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
            $colorIdx = 0;

            foreach ($subjects as $sb):
              $userColorKey = (!empty($sb['color_theme']) && isset($themePalettes[$sb['color_theme']]))
                ? $sb['color_theme']
                : $paletteKeys[$colorIdx % count($paletteKeys)];
              $colorIdx++;
              $palette = $themePalettes[$userColorKey];

              $cardBg = $palette['bg'];
              $cardIcon = !empty($sb['icon']) ? $sb['icon'] : $palette['icon'];
              $avatarColor = $palette['avatar_color'];
              $cardTitle = $sb['subject_name'];
              $cardSubtitle = !empty($sb['description']) ? $sb['description'] : $sb['subject_code'];

              $teacherName = $sb['teacher_name'] ?: 'U.G.P.K. Nishantha';
              $initial = strtoupper(substr($teacherName, 0, 1));
              $matCount = (int)($sb['materials_count'] ?? 0);
              $asgCount = (int)($sb['assignments_count'] ?? 0);

              $cCode = strtoupper(trim($sb['subject_code']));
              $semNum = 0;
              if (str_contains(strtolower($cardSubtitle), 'semester 1') || preg_match('/M0[1-8]/i', $cCode) || $cCode === 'EMPM02') {
                $semNum = 1;
              } elseif (str_contains(strtolower($cardSubtitle), 'semester 2') || preg_match('/M(09|1[0-5])/i', $cCode) || in_array($cCode, ['EMPM01', 'EMPM03'])) {
                $semNum = 2;
              }
              $semTag = ($semNum === 1) ? 'sem1' : (($semNum === 2) ? 'sem2' : 'other');
          ?>
            <div class="col-12 col-md-6 col-xl-4 col-xxl-3 student-classroom-grid-item" data-sem="<?= $semTag ?>">
              <div class="card border-0 rounded-4 shadow-sm h-100 bg-white overflow-hidden g-classroom-card transition-hover position-relative d-flex flex-column" style="min-height: 290px;">
                <!-- Top Colorful Banner Header (Matching Admin Classroom Hub) -->
                <div class="position-relative text-white overflow-hidden d-flex flex-column justify-content-between" style="background: <?= $cardBg ?>; min-height: 150px; padding: 22px 22px 18px 22px; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                  <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                    <div class="pe-2" style="max-width: 95%;">
                      <a href="index.php?page=student_materials&tab=all&subject_id=<?= (int)$sb['id'] ?>" class="text-white text-decoration-none">
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
                    <?= $initial ?>
                  </div>
                </div>

                <!-- Card Middle Body -->
                <div class="card-body d-flex flex-column flex-grow-1 justify-content-between" style="padding: 22px 22px 18px 22px;">
                  <div class="mb-3">
                    <span class="badge bg-light text-dark border font-monospace px-2.5 py-1" style="font-size: 0.74rem;">
                      <?= e($sb['subject_code']) ?>
                    </span>
                    <span class="badge bg-light text-secondary border px-2.5 py-1 ms-1" style="font-size: 0.74rem;">
                      NVTI Baddegama
                    </span>
                  </div>

                  <!-- Stats Pills & Bottom Quick Actions -->
                  <div class="pt-2 border-top d-flex justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                      <span class="badge rounded-pill px-2.5 py-1 <?= $matCount > 0 ? 'bg-primary-subtle text-primary border border-primary-subtle fw-bold' : 'bg-danger-subtle text-danger border border-danger-subtle' ?>" style="font-size: 0.72rem;">
                        <i class="bi bi-file-earmark-text me-1"></i> <?= $matCount ?> <?= $matCount === 1 ? 'Note' : 'Notes' ?>
                      </span>
                      <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                        <i class="bi bi-clipboard2-check me-1"></i> <?= $asgCount ?> <?= $asgCount === 1 ? 'Task' : 'Tasks' ?>
                      </span>
                    </div>

                    <div class="d-flex align-items-center gap-1.5" style="position: relative; z-index: 6;">
                      <a href="index.php?page=student_materials&tab=all&subject_id=<?= (int)$sb['id'] ?>" class="btn btn-sm btn-light border text-primary fw-bold rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1 shadow-xs" style="font-size: 0.75rem;">
                        <i class="bi bi-box-arrow-in-right"></i> Open
                      </a>
                      <a href="index.php?page=student_curriculum#mod-<?= e($sb['subject_code']) ?>" class="btn btn-sm btn-light border rounded-circle d-inline-flex align-items-center justify-content-center text-secondary shadow-xs" style="width: 28px; height: 28px;" title="View Syllabus Topics">
                        <i class="bi bi-list-ul" style="font-size: 0.8rem;"></i>
                      </a>
                    </div>
                  </div>
                </div>

                <!-- Full Card Clickable Overlay Link -->
                <a href="index.php?page=student_materials&tab=all&subject_id=<?= (int)$sb['id'] ?>" class="stretched-link" aria-label="Open <?= e($cardTitle) ?>"></a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

      <!-- ============================================================== -->
      <!-- TAB 2: CLASS STREAM (Google Classroom Stream Posts Feed)       -->
      <!-- ============================================================== -->
      <?php elseif ($activeTab === 'stream'): ?>
        <div class="row g-3">
          <!-- Left Column: Class Info Banner -->
          <div class="col-lg-4">
            <div class="card border rounded-4 p-3.5 bg-white shadow-sm mb-3">
              <h6 class="fw-bold mb-2 text-dark d-flex align-items-center gap-2">
                <i data-lucide="info" class="text-primary" style="width: 18px; height: 18px;"></i>
                Class Announcements
              </h6>
              <p class="small text-muted mb-3">All updates, lecture notes, lab sheets, and assignments posted by lecturers appear here in real-time.</p>
              
              <div class="p-2.5 rounded-3 mb-2" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <div class="fw-bold small text-dark mb-0.5"><i data-lucide="shield-check" style="width: 14px; height: 14px;" class="me-1 text-success"></i> 80% Attendance Rule</div>
                <div class="text-muted" style="font-size: 0.72rem;">Minimum 80% class attendance is mandatory to sit for the TVEC semester final examination.</div>
              </div>

              <div class="p-2.5 rounded-3" style="background: #fef2f2; border: 1px solid #fecaca;">
                <div class="fw-bold small text-danger mb-0.5"><i data-lucide="calendar" style="width: 14px; height: 14px;" class="me-1"></i> Exam Preparation</div>
                <div class="text-muted" style="font-size: 0.72rem;">Past papers for Semesters 1 &amp; 2 are ready for download in the <a href="index.php?page=student_papers" class="text-danger fw-bold">Past Papers Hub</a>.</div>
              </div>
            </div>
          </div>

          <!-- Right Column: Stream Posts (Matching Google Classroom Feed Screenshot) -->
          <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <h5 class="fw-bold mb-0 text-dark">Class Activity Stream</h5>
              <span class="text-muted small">Latest Posts from Lecturers</span>
            </div>

            <div class="d-flex flex-column gap-2.5 mb-4">
              <?php if (empty($streamPosts)): ?>
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center text-muted">
                  <i data-lucide="inbox" class="mx-auto mb-2 text-secondary" style="width: 40px; height: 40px;"></i>
                  <h6>No stream updates yet</h6>
                  <p class="small mb-0">Check back later when instructors post new materials.</p>
                </div>
              <?php else: ?>
                <?php foreach ($streamPosts as $post): ?>
                  <!-- Google Classroom Style Feed Item Card -->
                  <div class="card border rounded-4 shadow-sm p-3 bg-white transition-all hover-shadow-sm">
                    <div class="d-flex align-items-start gap-3">
                      <!-- Post Icon Badge -->
                      <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; <?= ($post['post_type'] === 'assignment') ? 'background: #eff6ff; color: #2563eb;' : 'background: #fef2f2; color: #7A1418;' ?>">
                        <?php if ($post['post_type'] === 'assignment'): ?>
                          <i data-lucide="clipboard-check" style="width: 20px; height: 20px;"></i>
                        <?php else: ?>
                          <i data-lucide="book-open" style="width: 20px; height: 20px;"></i>
                        <?php endif; ?>
                      </div>

                      <!-- Post Details -->
                      <div class="flex-grow-1 min-w-0">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-1">
                          <div class="min-w-0">
                            <h6 class="fw-bold text-dark mb-0.5 text-truncate">
                              <?= e($post['teacher_name']) ?> posted a new <?= ($post['post_type'] === 'assignment') ? 'assignment' : 'material' ?>: 
                              <span class="text-primary"><?= e($post['title']) ?></span>
                            </h6>
                            <div class="text-muted small" style="font-size: 0.75rem;">
                              <span class="badge bg-light text-secondary border me-1"><?= e($post['subject_code']) ?>: <?= e($post['subject_name']) ?></span>
                              &bull; <?= date('d M Y, h:i A', strtotime($post['created_at'])) ?>
                            </div>
                          </div>
                        </div>

                        <?php if (!empty($post['description'])): ?>
                          <p class="small text-secondary mt-2 mb-2"><?= nl2br(e($post['description'])) ?></p>
                        <?php endif; ?>

                        <!-- Interactive Actions (View & Download) -->
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-2 pt-2 border-top">
                          <?php if ($post['post_type'] === 'material'): ?>
                            <?php if ($post['material_type'] === 'file'): ?>
                              <button type="button" onclick="previewMaterial(<?= (int)$post['id'] ?>, '<?= addslashes(e($post['title'])) ?>', 'file', 'index.php?page=view_material&id=<?= (int)$post['id'] ?>', '<?= addslashes(e($post['original_filename'] ?: $post['file_path'])) ?>')" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1.5 fw-medium">
                                <i data-lucide="eye" style="width: 14px; height: 14px;"></i> View Online
                              </button>
                              <a href="index.php?page=download_material&id=<?= (int)$post['id'] ?>" class="btn btn-sm btn-light border rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1.5 fw-medium">
                                <i data-lucide="download" style="width: 14px; height: 14px;"></i> Download
                              </a>
                            <?php elseif ($post['material_type'] === 'youtube'): ?>
                              <button type="button" onclick="previewMaterial(<?= (int)$post['id'] ?>, '<?= addslashes(e($post['title'])) ?>', 'youtube', '<?= e($post['external_url']) ?>')" class="btn btn-sm btn-danger rounded-pill px-3 py-1 text-white d-inline-flex align-items-center gap-1.5 fw-medium">
                                <i data-lucide="play-circle" style="width: 14px; height: 14px;"></i> Watch Lecture
                              </button>
                            <?php else: ?>
                              <a href="<?= e($post['external_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-info text-white rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1.5 fw-medium">
                                <i data-lucide="external-link" style="width: 14px; height: 14px;"></i> Open Resource
                              </a>
                            <?php endif; ?>
                          <?php else: ?>
                            <a href="index.php?page=student_assignments" class="btn btn-sm btn-primary rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1.5 fw-medium">
                              <i data-lucide="upload" style="width: 14px; height: 14px;"></i> View &amp; Submit Assignment
                            </a>
                            <?php if (!empty($post['deadline'])): ?>
                              <span class="badge bg-warning-subtle text-dark border ms-auto small">
                                <i data-lucide="clock" style="width: 12px; height: 12px;" class="me-1"></i> Due: <?= date('M d, Y', strtotime($post['deadline'])) ?>
                              </span>
                            <?php endif; ?>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

      <!-- ============================================================== -->
      <!-- TAB 3: CURRICULUM SYLLABUS & SUB-TOPICS (TVEC Guide Book)      -->
      <!-- ============================================================== -->
      <?php elseif ($activeTab === 'curriculum'): ?>
        <!-- Curriculum Redirect / Welcome Card -->
        <div class="card border rounded-4 p-4 p-md-5 bg-white shadow-sm mb-4 text-center">
          <div class="rounded-circle p-3 d-inline-flex mx-auto mb-3" style="background: #fef2f2; color: #7A1418; width: 68px; height: 68px; align-items: center; justify-content: center;">
            <i data-lucide="book-open-check" style="width: 34px; height: 34px;"></i>
          </div>
          <h4 class="fw-bold text-dark mb-2">NVQ Level 5 Curriculum Has Moved to a Dedicated Page!</h4>
          <p class="text-muted mx-auto mb-4" style="max-width: 620px;">
            We have created a dedicated, full-screen portal for the official TVEC CBT Curriculum (K72T001) with enhanced tables, unit-by-unit checklists for sub-topics ("අනු කොටස්"), search filters, and direct past paper lookups.
          </p>
          <div class="d-flex justify-content-center gap-2">
            <a href="index.php?page=student_curriculum" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold d-inline-flex align-items-center gap-2 shadow-sm" style="background: linear-gradient(135deg, #7A1418, #90161b);">
              <i data-lucide="external-link" style="width: 16px; height: 16px;"></i>
              <span>Open Dedicated Curriculum Portal</span>
            </a>
            <a href="index.php?page=student_materials&tab=classroom" class="btn btn-outline-secondary rounded-pill px-4 py-2.5 fw-semibold">
              <span>Back to Classes</span>
            </a>
          </div>
        </div>

      <!-- ============================================================== -->
      <!-- TAB 4: ALL RESOURCES GRID & SEARCH (View Online & Download)    -->
      <!-- ============================================================== -->
      <?php else: ?>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
          <a href="index.php?page=student_materials" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1.5 bg-white shadow-xs">
            <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
            <span>Back to Subject Classes</span>
          </a>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold">
            <?= count($materials) ?> <?= count($materials) === 1 ? 'Material' : 'Materials' ?> Found
          </span>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
          <form action="index.php" method="GET" class="row g-2 align-items-center">
            <input type="hidden" name="page" value="student_materials">
            <input type="hidden" name="tab" value="all">

            <div class="col-md-4">
              <label class="form-label small text-muted mb-1">Search Resources</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i data-lucide="search" style="width: 15px; height: 15px;"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search by title, keywords..." value="<?= e($_GET['search'] ?? '') ?>">
              </div>
            </div>

            <div class="col-md-3">
              <label class="form-label small text-muted mb-1">Filter by Category</label>
              <select name="category_id" class="form-select" onchange="this.form.submit()">
                <option value="0">-- All Categories --</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>" <?= ((int)($_GET['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                    <?= e($cat['category_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label small text-muted mb-1">Filter by Subject</label>
              <select name="subject_id" class="form-select" onchange="this.form.submit()">
                <option value="0">-- All Subjects --</option>
                <?php foreach ($subjects as $sb): ?>
                  <option value="<?= $sb['id'] ?>" <?= ($selectedSubjectId === (int)$sb['id']) ? 'selected' : '' ?>>
                    <?= e($sb['subject_code']) ?> - <?= e($sb['subject_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
              <a href="index.php?page=student_materials&tab=all" class="btn btn-outline-secondary w-100">
                <i data-lucide="rotate-ccw" style="width: 14px; height: 14px;" class="me-1"></i> Reset
              </a>
            </div>
          </form>
        </div>

        <!-- Category Filter Pills -->
        <div class="d-flex flex-wrap gap-2 mb-4">
          <a href="index.php?page=student_materials&tab=all" class="badge rounded-pill text-decoration-none px-3 py-2 <?= empty($_GET['category_id']) ? 'bg-primary text-white' : 'bg-white text-dark border' ?>">
            All Resources
          </a>
          <?php foreach ($categories as $cat): ?>
            <a href="index.php?page=student_materials&tab=all&category_id=<?= $cat['id'] ?>" class="badge rounded-pill text-decoration-none px-3 py-2 <?= ((int)($_GET['category_id'] ?? 0) === (int)$cat['id']) ? 'bg-primary text-white' : 'bg-white text-dark border' ?>">
              <i class="bi <?= e($cat['icon']) ?> me-1"></i> <?= e($cat['category_name']) ?>
            </a>
          <?php endforeach; ?>
        </div>

        <!-- Materials Grid with Dual View & Download Action Buttons -->
        <div class="row g-3">
          <?php if (empty($materials)): ?>
            <div class="col-12">
              <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
                <i data-lucide="folder-x" class="mx-auto mb-2 text-secondary" style="width: 48px; height: 48px;"></i>
                <h5>No study materials found</h5>
                <p class="small">Try clearing your filters or search keywords.</p>
              </div>
            </div>
          <?php else: ?>
            <?php foreach ($materials as $m): ?>
              <?php
                $fname = (string)($m['original_filename'] ?: $m['file_path'] ?: '');
                $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                $fileBadgeLabel = match ($ext) {
                  'pdf' => 'PDF File',
                  'doc', 'docx' => 'Word Doc',
                  'ppt', 'pptx' => 'Slides (' . strtoupper($ext) . ')',
                  'xls', 'xlsx' => 'Spreadsheet',
                  'jpg', 'jpeg', 'png', 'webp', 'gif' => 'Image',
                  'zip', 'rar' => 'Archive',
                  default => ($ext ? strtoupper($ext) . ' File' : 'Document'),
                };
              ?>
              <div class="col-md-6 col-lg-4">
                <div class="card material-card h-100 shadow-sm rounded-4 p-3 d-flex flex-column justify-content-between bg-white border">
                  <div>
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1.5 px-2">
                        <i class="bi <?= e($m['category_icon']) ?> me-1"></i> <?= e($m['category_name']) ?>
                      </span>
                      <?php if ($m['material_type'] === 'file'): ?>
                        <span class="badge bg-danger-subtle text-danger"><i data-lucide="file-text" style="width: 12px; height: 12px;"></i> <?= e($fileBadgeLabel) ?></span>
                      <?php elseif ($m['material_type'] === 'youtube'): ?>
                        <span class="badge bg-danger text-white"><i data-lucide="youtube" style="width: 12px; height: 12px;"></i> YouTube</span>
                      <?php else: ?>
                        <span class="badge bg-info-subtle text-info"><i data-lucide="link" style="width: 12px; height: 12px;"></i> Web Link</span>
                      <?php endif; ?>
                    </div>

                    <h6 class="fw-bold text-dark mb-1"><?= e($m['title']) ?></h6>
                    <p class="small text-muted mb-2"><?= e($m['description'] ?: 'Study resource provided by lecturer.') ?></p>

                    <div class="small text-secondary mb-2">
                      <i data-lucide="book-marked" class="me-1 text-primary align-middle" style="width: 14px; height: 14px;"></i> 
                      <strong><?= e($m['subject_code']) ?>:</strong> <?= e($m['subject_name']) ?>
                    </div>
                    <div class="small text-muted mb-3" style="font-size:0.75rem;">
                      <i data-lucide="user" class="me-1 align-middle" style="width: 13px; height: 13px;"></i> Lecturer: <?= e($m['teacher_name']) ?> &bull; <?= date('M d, Y', strtotime($m['created_at'])) ?>
                    </div>
                  </div>

                  <!-- Dual Action Buttons: View Online & Download -->
                  <div class="pt-2.5 border-top mt-2">
                    <?php if ($m['material_type'] === 'file'): ?>
                      <div class="d-flex gap-2">
                        <button type="button" onclick="previewMaterial(<?= (int)$m['id'] ?>, '<?= addslashes(e($m['title'])) ?>', 'file', 'index.php?page=view_material&id=<?= (int)$m['id'] ?>', '<?= addslashes(e($fname)) ?>')" class="btn btn-outline-primary btn-sm flex-grow-1 fw-semibold rounded-pill d-inline-flex align-items-center justify-content-center gap-1.5">
                          <i data-lucide="eye" style="width: 14px; height: 14px;"></i> View Online
                        </button>
                        <a href="index.php?page=download_material&id=<?= $m['id'] ?>" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1" title="Download to device">
                          <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                        </a>
                      </div>
                    <?php elseif ($m['material_type'] === 'youtube'): ?>
                      <button type="button" onclick="previewMaterial(<?= (int)$m['id'] ?>, '<?= addslashes(e($m['title'])) ?>', 'youtube', '<?= e($m['external_url']) ?>')" class="btn btn-danger btn-sm w-100 fw-semibold text-white rounded-pill d-inline-flex align-items-center justify-content-center gap-1.5">
                        <i data-lucide="play-circle" style="width: 16px; height: 16px;"></i> Watch Video Lecture
                      </button>
                    <?php else: ?>
                      <a href="<?= e($m['external_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-info btn-sm w-100 fw-semibold text-white rounded-pill d-inline-flex align-items-center justify-content-center gap-1.5">
                        <i data-lucide="external-link" style="width: 14px; height: 14px;"></i> Open External Resource
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    </div>
  </main>
</div>

<!-- ============================================================== -->
<!-- Interactive Document / Video Modal Viewer                      -->
<!-- ============================================================== -->
<div class="modal fade" id="materialViewerModal" tabindex="-1" aria-labelledby="modalDocTitle" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-fullscreen-md-down modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <!-- Modal Header (Zero Overflow on Mobile) -->
      <div class="modal-header bg-white border-bottom py-2.5 px-3 px-md-4 d-flex align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0 overflow-hidden">
          <span class="badge rounded-pill px-2.5 py-1 flex-shrink-0 d-none d-sm-inline-flex align-items-center" id="modalTypeBadge" style="background: #fef2f2; color: #7A1418; border: 1px solid #fecaca; font-size: 0.72rem;">
            <i data-lucide="file-text" style="width: 13px; height: 13px;" class="me-1"></i> Document
          </span>
          <h6 class="modal-title fw-bold text-dark text-truncate mb-0" id="modalDocTitle">Material Title</h6>
        </div>
        
        <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
          <div id="officeViewerSwitcher" class="btn-group btn-group-sm d-none me-1" role="group">
            <button type="button" id="btnSwitchOffice" class="btn btn-primary rounded-start-pill px-2.5 py-1 fw-medium" onclick="switchOfficeEngine('office')">Office</button>
            <button type="button" id="btnSwitchGoogle" class="btn btn-outline-secondary rounded-end-pill px-2.5 py-1 fw-medium" onclick="switchOfficeEngine('google')">Google</button>
          </div>
          <a href="#" id="modalDirectDownloadBtn" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 px-md-3 py-1 d-inline-flex align-items-center gap-1 fw-medium small" title="Download">
            <i data-lucide="download" style="width: 14px; height: 14px;"></i>
            <span class="d-none d-md-inline">Download</span>
          </a>
          <a href="#" id="modalOpenTabBtn" target="_blank" class="btn btn-sm text-white rounded-pill px-2.5 px-md-3 py-1 d-inline-flex align-items-center gap-1 fw-medium small" style="background: #7A1418; border-color: #7A1418;" title="Open in New Tab">
            <i data-lucide="external-link" style="width: 14px; height: 14px;"></i>
            <span class="d-none d-md-inline">Open in New Tab</span>
          </a>
          <button type="button" class="btn-close ms-1 flex-shrink-0" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>

      <!-- Modal Body / Content Viewer -->
      <div class="modal-body p-0 bg-dark position-relative" style="height: clamp(320px, 72vh, 640px);">
        <!-- Loading spinner overlay -->
        <div id="modalLoadingSpinner" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-white bg-dark" style="z-index: 5;">
          <div class="spinner-border text-danger me-2" role="status"></div>
          <span>Loading document preview...</span>
        </div>

        <!-- PDF & Office Viewer IFrame (always rendered; never display:none while loading) -->
        <iframe id="modalDocIframe" src="about:blank" class="w-100 h-100 border-0 d-block bg-white" onload="hideViewerSpinner();"></iframe>

        <!-- Text / SQL Code Viewer Container -->
        <div id="modalTextContainer" class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column" style="z-index: 4; background: #0f172a;">
          <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom border-secondary-subtle" style="background: #1e293b;">
            <span class="text-light small font-monospace" id="modalTextFilename">file.sql</span>
            <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-2.5 py-0.5 small" onclick="copyTextPreview()">
              <i class="bi bi-clipboard me-1"></i>Copy
            </button>
          </div>
          <pre id="modalTextContent" class="flex-grow-1 m-0 p-3 text-light overflow-auto" style="font-size: 13px; line-height: 1.55;"></pre>
        </div>

        <!-- Image Viewer Container -->
        <div id="modalImageContainer" class="position-absolute top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center bg-dark p-3" style="z-index: 4;">
          <img id="modalPreviewImage" src="" alt="Preview" class="img-fluid rounded shadow" style="max-height: 100%; max-width: 100%; object-fit: contain;">
        </div>

        <!-- Unsupported Inline Format Fallback (ZIP / RAR / Offline) -->
        <div id="modalUnsupportedContainer" class="position-absolute top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center bg-light p-4 text-center" style="z-index: 6;">
          <div style="max-width: 420px;">
            <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
              <i class="bi bi-file-earmark-arrow-down-fill fs-2"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1" id="modalUnsupportedTitle">File Preview Not Available Inline</h5>
            <p class="small text-muted mb-3" id="modalUnsupportedDesc">
              Archive and compressed files cannot be previewed inside this popup. Download the file directly to open it on your device.
            </p>
            <a href="#" id="modalUnsupportedDownloadBtn" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">
              <i class="bi bi-download me-1"></i> Download File
            </a>
          </div>
        </div>

        <!-- Video Player IFrame -->
        <div id="modalVideoContainer" class="position-absolute top-0 start-0 w-100 h-100 d-none bg-black" style="z-index: 6;">
          <iframe id="modalVideoIframe" src="" class="w-100 h-100 border-0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
      </div>

      <!-- Mobile Friendly Footer Bar (Guaranteed Easy Close on Mobile) -->
      <div class="modal-footer bg-white border-top py-2 px-3 d-flex d-md-none justify-content-between">
        <span class="small text-muted">Document Viewer</span>
        <button type="button" class="btn btn-danger btn-sm rounded-pill px-4 fw-bold" data-bs-dismiss="modal">
          <i class="bi bi-x-lg me-1"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Viewer JavaScript -->
<script>
let viewerFallbackTimer = null;
let currentOfficeUrls = { office: '', google: '' };

function hideViewerSpinner() {
  const spinner = document.getElementById('modalLoadingSpinner');
  if (spinner) {
    spinner.classList.add('d-none');
    spinner.classList.remove('d-flex');
  }
}

function showViewerSpinner() {
  const spinner = document.getElementById('modalLoadingSpinner');
  if (spinner) {
    spinner.classList.remove('d-none');
    spinner.classList.add('d-flex');
  }
}

function switchOfficeEngine(engine) {
  const docIframe = document.getElementById('modalDocIframe');
  const btnOff = document.getElementById('btnSwitchOffice');
  const btnGoo = document.getElementById('btnSwitchGoogle');
  if (!currentOfficeUrls[engine]) return;

  showViewerSpinner();
  docIframe.src = currentOfficeUrls[engine];
  clearTimeout(viewerFallbackTimer);
  viewerFallbackTimer = setTimeout(hideViewerSpinner, 4500);

  if (btnOff && btnGoo) {
    btnOff.className = 'btn rounded-start-pill px-2.5 py-1 fw-medium ' + (engine === 'office' ? 'btn-primary' : 'btn-outline-secondary');
    btnGoo.className = 'btn rounded-end-pill px-2.5 py-1 fw-medium ' + (engine === 'google' ? 'btn-primary' : 'btn-outline-secondary');
  }
}

function copyTextPreview() {
  const txt = document.getElementById('modalTextContent').textContent || '';
  navigator.clipboard.writeText(txt);
}

function previewMaterial(id, title, type, url, filename) {
  clearTimeout(viewerFallbackTimer);
  currentOfficeUrls = { office: '', google: '' };

  const modalEl = document.getElementById('materialViewerModal');
  const titleEl = document.getElementById('modalDocTitle');
  const typeBadge = document.getElementById('modalTypeBadge');
  const docIframe = document.getElementById('modalDocIframe');
  const textContainer = document.getElementById('modalTextContainer');
  const textContent = document.getElementById('modalTextContent');
  const textFilename = document.getElementById('modalTextFilename');
  const officeSwitcher = document.getElementById('officeViewerSwitcher');
  const imageContainer = document.getElementById('modalImageContainer');
  const previewImg = document.getElementById('modalPreviewImage');
  const unsupportedContainer = document.getElementById('modalUnsupportedContainer');
  const unsupportedDownloadBtn = document.getElementById('modalUnsupportedDownloadBtn');
  const unsupportedTitle = document.getElementById('modalUnsupportedTitle');
  const unsupportedDesc = document.getElementById('modalUnsupportedDesc');
  const videoContainer = document.getElementById('modalVideoContainer');
  const videoIframe = document.getElementById('modalVideoIframe');
  const openTabBtn = document.getElementById('modalOpenTabBtn');
  const downloadBtn = document.getElementById('modalDirectDownloadBtn');

  titleEl.textContent = title;
  showViewerSpinner();
  docIframe.classList.remove('d-none');
  docIframe.src = 'about:blank';
  textContainer.classList.add('d-none');
  textContainer.classList.remove('d-flex');
  textContent.textContent = '';
  officeSwitcher.classList.add('d-none');
  imageContainer.classList.add('d-none');
  imageContainer.classList.remove('d-flex');
  previewImg.src = '';
  unsupportedContainer.classList.add('d-none');
  unsupportedContainer.classList.remove('d-flex');
  videoContainer.classList.add('d-none');
  videoIframe.src = '';

  const ext = (filename || '').split('.').pop().toLowerCase();

  if (type === 'youtube') {
    typeBadge.innerHTML = '<i data-lucide="youtube" style="width: 13px; height: 13px;" class="me-1"></i> Video Lecture';
    let videoId = '';
    const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
    if (match && match[1]) {
      videoId = match[1];
    }
    const embedUrl = videoId ? 'https://www.youtube.com/embed/' + videoId + '?autoplay=1' : url;
    docIframe.classList.add('d-none');
    videoIframe.src = embedUrl;
    videoContainer.classList.remove('d-none');
    hideViewerSpinner();
    openTabBtn.href = url;
    downloadBtn.style.display = 'none';
  } else {
    const viewUrl = 'index.php?page=view_material&id=' + id;
    const downloadUrl = 'index.php?page=download_material&id=' + id;
    openTabBtn.href = viewUrl;
    downloadBtn.href = downloadUrl;
    downloadBtn.style.display = 'inline-flex';

    if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext)) {
      typeBadge.innerHTML = '<i data-lucide="image" style="width: 13px; height: 13px;" class="me-1"></i> Image File';
      docIframe.classList.add('d-none');
      previewImg.onload = hideViewerSpinner;
      previewImg.onerror = hideViewerSpinner;
      previewImg.src = viewUrl;
      imageContainer.classList.remove('d-none');
      imageContainer.classList.add('d-flex');
    } else if (['ppt', 'pptx', 'doc', 'docx', 'xls', 'xlsx', 'sql', 'txt'].includes(ext)) {
      const isText = ['sql', 'txt'].includes(ext);
      typeBadge.innerHTML = '<i data-lucide="file-text" style="width: 13px; height: 13px;" class="me-1"></i> ' + ext.toUpperCase() + (isText ? ' Script' : ' Slides/Doc');

      fetch('index.php?page=preview_meta&kind=material&id=' + id)
        .then(r => r.json())
        .then(data => {
          if (data && data.ok && data.mode === 'office' && data.office_url) {
            currentOfficeUrls.office = data.office_url;
            currentOfficeUrls.google = data.google_url || '';
            officeSwitcher.classList.remove('d-none');
            switchOfficeEngine('office');
          } else if (data && data.ok && data.mode === 'text') {
            docIframe.classList.add('d-none');
            textFilename.textContent = data.filename || filename || 'preview';
            textContent.textContent = data.text_content || '';
            textContainer.classList.remove('d-none');
            textContainer.classList.add('d-flex');
            hideViewerSpinner();
          } else {
            docIframe.classList.add('d-none');
            unsupportedTitle.textContent = (filename || title) + ' (' + ext.toUpperCase() + ')';
            unsupportedDesc.textContent = data && !data.is_public_domain
              ? 'Online Office preview works on the live HTTPS portal. On localhost, click Download to view this file.'
              : 'Preview unavailable for this file. Download it directly to view on your device.';
            unsupportedDownloadBtn.href = downloadUrl;
            unsupportedContainer.classList.remove('d-none');
            unsupportedContainer.classList.add('d-flex');
            hideViewerSpinner();
          }
        })
        .catch(() => {
          docIframe.classList.add('d-none');
          unsupportedTitle.textContent = (filename || title) + ' (' + ext.toUpperCase() + ')';
          unsupportedDownloadBtn.href = downloadUrl;
          unsupportedContainer.classList.remove('d-none');
          unsupportedContainer.classList.add('d-flex');
          hideViewerSpinner();
        });
    } else if (['zip', 'rar'].includes(ext)) {
      typeBadge.innerHTML = '<i data-lucide="file-archive" style="width: 13px; height: 13px;" class="me-1"></i> ' + ext.toUpperCase() + ' Archive';
      docIframe.classList.add('d-none');
      unsupportedTitle.textContent = (filename || title) + ' (' + ext.toUpperCase() + ')';
      unsupportedDesc.textContent = 'Compressed archive files cannot be previewed inside the browser. Click Download to extract and open the contents.';
      unsupportedDownloadBtn.href = downloadUrl;
      unsupportedContainer.classList.remove('d-none');
      unsupportedContainer.classList.add('d-flex');
      hideViewerSpinner();
    } else {
      typeBadge.innerHTML = '<i data-lucide="file-text" style="width: 13px; height: 13px;" class="me-1"></i> PDF Document';
      docIframe.src = viewUrl;
      // Fallback timer so browser PDF plugins that skip iframe onload never leave spinner stuck
      viewerFallbackTimer = setTimeout(hideViewerSpinner, 1800);
    }
  }

  if (typeof lucide !== 'undefined' && lucide.createIcons) {
    lucide.createIcons();
  }

  const bsModal = new bootstrap.Modal(modalEl);
  bsModal.show();
}

// Stop video/document loading when modal is closed
document.getElementById('materialViewerModal').addEventListener('hidden.bs.modal', function () {
  clearTimeout(viewerFallbackTimer);
  document.getElementById('modalDocIframe').src = 'about:blank';
  document.getElementById('modalVideoIframe').src = '';
  document.getElementById('modalPreviewImage').src = '';
});

function filterStudentClassrooms(filter, btn) {
  const gridRow = document.getElementById('studentClassroomGridRow');
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

  const items = gridRow.querySelectorAll('.student-classroom-grid-item');
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
