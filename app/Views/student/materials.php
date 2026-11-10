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
          <div>
            <h5 class="fw-bold mb-0 text-dark">Enrolled Subject Classes</h5>
            <small class="text-muted">Click any class to explore lecture notes and resources</small>
          </div>
          <span class="badge bg-light text-dark border rounded-pill px-3 py-1.5 fw-semibold">
            <?= count($subjects) ?> Subjects Active
          </span>
        </div>

        <div class="row g-3 g-md-4 mb-4">
          <?php 
            $cardColors = [
              ['bg' => 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%)', 'accent' => '#3b82f6'],
              ['bg' => 'linear-gradient(135deg, #7A1418 0%, #991b1b 100%)', 'accent' => '#dc2626'],
              ['bg' => 'linear-gradient(135deg, #065f46 0%, #059669 100%)', 'accent' => '#10b981'],
              ['bg' => 'linear-gradient(135deg, #4c1d95 0%, #7c3aed 100%)', 'accent' => '#8b5cf6'],
              ['bg' => 'linear-gradient(135deg, #78350f 0%, #d97706 100%)', 'accent' => '#f59e0b'],
              ['bg' => 'linear-gradient(135deg, #0f766e 0%, #0d9488 100%)', 'accent' => '#14b8a6'],
            ];
            $colorIdx = 0;
            foreach ($subjects as $sb):
              $color = $cardColors[$colorIdx % count($cardColors)];
              $colorIdx++;
              $teacherName = $sb['teacher_name'] ?: 'Nishantha Gamage';
              $initial = strtoupper(substr($teacherName, 0, 1));
          ?>
            <div class="col-12 col-md-6 col-lg-4">
              <div class="card border rounded-4 overflow-hidden shadow-sm h-100 classroom-card transition-all d-flex flex-column">
                <!-- Classroom Card Header Banner (Centered, Generous Padding) -->
                <div class="text-white position-relative text-center d-flex flex-column justify-content-center align-items-center" style="background: <?= $color['bg'] ?>; min-height: 152px; padding: 22px 24px 32px 24px;">
                  <!-- Top Right Dropdown Menu -->
                  <div class="dropdown position-absolute" style="top: 12px; right: 14px; z-index: 3;">
                    <button class="btn btn-sm btn-link text-white text-decoration-none p-1 opacity-75" type="button" data-bs-toggle="dropdown" aria-label="Module options">
                      <i data-lucide="more-vertical" style="width: 18px; height: 18px;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border py-1 small">
                      <li><a class="dropdown-item" href="index.php?page=student_curriculum#mod-<?= e($sb['subject_code']) ?>">View Syllabus Topics</a></li>
                      <li><a class="dropdown-item" href="index.php?page=student_papers&module_code=<?= e($sb['subject_code']) ?>">Past Papers</a></li>
                    </ul>
                  </div>

                  <span class="badge bg-white bg-opacity-25 text-white font-monospace mb-2 px-2.5 py-1 rounded-pill" style="font-size: 0.72rem; letter-spacing: 0.4px;">
                    <?= e($sb['subject_code']) ?>
                  </span>
                  <h5 class="fw-bold mb-1.5 text-white px-2 lh-sm" style="font-size: 1.06rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="<?= e($sb['subject_name']) ?>">
                    <?= e($sb['subject_name']) ?>
                  </h5>
                  <div class="small text-white-50 fw-medium text-truncate" style="max-width: 90%;">
                    <i data-lucide="user-check" style="width: 13px; height: 13px;" class="me-1 align-middle"></i><?= e($teacherName) ?>
                  </div>

                  <!-- Centered Lecturer Avatar Circle -->
                  <div class="position-absolute" style="left: 50%; transform: translateX(-50%); bottom: -22px; z-index: 4;">
                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow border border-3 border-white" style="width: 46px; height: 46px; background: #334155; font-size: 1.1rem;">
                      <?= $initial ?>
                    </div>
                  </div>
                </div>

                <!-- Classroom Card Body (Centered Content) -->
                <div class="card-body bg-white d-flex flex-column justify-content-between text-center" style="padding: 34px 20px 20px 20px;">
                  <p class="small text-muted mb-3 mx-auto" style="min-height: 40px; line-height: 1.5; max-width: 95%;">
                    <?= e($sb['description'] ?: 'Official NVTI Baddegama Diploma curriculum coursework and resources.') ?>
                  </p>

                  <?php
                    $matCount = (int)($sb['materials_count'] ?? 0);
                    $asgCount = (int)($sb['assignments_count'] ?? 0);
                  ?>
                  <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap mb-3">
                    <span class="badge rounded-pill px-3 py-1.5 fw-bold <?= $matCount > 0 ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-light text-secondary border' ?>" style="font-size: 0.76rem;">
                      <i data-lucide="file-text" style="width: 13px; height: 13px;" class="me-1 align-middle"></i>
                      <?= $matCount ?> <?= $matCount === 1 ? 'Material' : 'Materials' ?>
                    </span>
                    <?php if ($asgCount > 0): ?>
                      <span class="badge rounded-pill px-3 py-1.5 fw-bold bg-warning-subtle text-dark border border-warning-subtle" style="font-size: 0.76rem;">
                        <i data-lucide="clipboard-check" style="width: 13px; height: 13px;" class="me-1 align-middle"></i>
                        <?= $asgCount ?> <?= $asgCount === 1 ? 'Task' : 'Tasks' ?>
                      </span>
                    <?php endif; ?>
                  </div>

                  <div class="d-flex align-items-center justify-content-center gap-2 pt-3 border-top">
                    <a href="index.php?page=student_materials&tab=all&subject_id=<?= $sb['id'] ?>" class="btn btn-sm <?= $matCount > 0 ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill px-4 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs">
                      <i data-lucide="folder-open" style="width: 14px; height: 14px;"></i> View Materials (<?= $matCount ?>)
                    </a>
                    <a href="index.php?page=student_curriculum#mod-<?= e($sb['subject_code']) ?>" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 text-secondary small d-inline-flex align-items-center gap-1" title="View Syllabus">
                      <i data-lucide="list-tree" style="width: 14px; height: 14px;"></i>
                    </a>
                  </div>
                </div>
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
                              <button type="button" onclick="previewMaterial(<?= (int)$post['id'] ?>, '<?= addslashes(e($post['title'])) ?>', 'file', 'index.php?page=view_material&id=<?= (int)$post['id'] ?>')" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1.5 fw-medium">
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
              <div class="col-md-6 col-lg-4">
                <div class="card material-card h-100 shadow-sm rounded-4 p-3 d-flex flex-column justify-content-between bg-white border">
                  <div>
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1.5 px-2">
                        <i class="bi <?= e($m['category_icon']) ?> me-1"></i> <?= e($m['category_name']) ?>
                      </span>
                      <?php if ($m['material_type'] === 'file'): ?>
                        <span class="badge bg-danger-subtle text-danger"><i data-lucide="file-text" style="width: 12px; height: 12px;"></i> PDF File</span>
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
                        <button type="button" onclick="previewMaterial(<?= (int)$m['id'] ?>, '<?= addslashes(e($m['title'])) ?>', 'file', 'index.php?page=view_material&id=<?= (int)$m['id'] ?>')" class="btn btn-outline-primary btn-sm flex-grow-1 fw-semibold rounded-pill d-inline-flex align-items-center justify-content-center gap-1.5">
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
      <div class="modal-body p-0 bg-dark position-relative" style="height: 75vh; min-height: 400px;">
        <!-- Loading spinner -->
        <div id="modalLoadingSpinner" class="d-flex align-items-center justify-content-center h-100 text-white">
          <div class="spinner-border text-danger me-2" role="status"></div>
          <span>Loading document preview...</span>
        </div>

        <!-- PDF & Document Viewer IFrame -->
        <iframe id="modalDocIframe" src="" class="w-100 h-100 border-0" style="display:none;" onload="document.getElementById('modalLoadingSpinner').style.display='none'; this.style.display='block';"></iframe>

        <!-- Video Player IFrame -->
        <div id="modalVideoContainer" class="w-100 h-100 d-none">
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
function previewMaterial(id, title, type, url) {
  const modalEl = document.getElementById('materialViewerModal');
  const titleEl = document.getElementById('modalDocTitle');
  const typeBadge = document.getElementById('modalTypeBadge');
  const docIframe = document.getElementById('modalDocIframe');
  const videoContainer = document.getElementById('modalVideoContainer');
  const videoIframe = document.getElementById('modalVideoIframe');
  const spinner = document.getElementById('modalLoadingSpinner');
  const openTabBtn = document.getElementById('modalOpenTabBtn');
  const downloadBtn = document.getElementById('modalDirectDownloadBtn');

  titleEl.textContent = title;
  spinner.style.display = 'flex';
  docIframe.style.display = 'none';
  videoContainer.classList.add('d-none');

  if (type === 'youtube') {
    typeBadge.innerHTML = '<i data-lucide="youtube" style="width: 13px; height: 13px;" class="me-1"></i> Video Lecture';
    // Extract video ID from youtube url
    let videoId = '';
    const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
    if (match && match[1]) {
      videoId = match[1];
    }
    const embedUrl = videoId ? 'https://www.youtube.com/embed/' + videoId + '?autoplay=1' : url;
    videoIframe.src = embedUrl;
    videoContainer.classList.remove('d-none');
    spinner.style.display = 'none';
    openTabBtn.href = url;
    downloadBtn.style.display = 'none';
  } else {
    typeBadge.innerHTML = '<i data-lucide="file-text" style="width: 13px; height: 13px;" class="me-1"></i> PDF Document';
    const viewUrl = 'index.php?page=view_material&id=' + id;
    docIframe.src = viewUrl;
    openTabBtn.href = viewUrl;
    downloadBtn.href = 'index.php?page=download_material&id=' + id;
    downloadBtn.style.display = 'inline-flex';
  }

  if (typeof lucide !== 'undefined' && lucide.createIcons) {
    lucide.createIcons();
  }

  const bsModal = new bootstrap.Modal(modalEl);
  bsModal.show();
}

// Stop video playback when modal is closed
document.getElementById('materialViewerModal').addEventListener('hidden.bs.modal', function () {
  document.getElementById('modalDocIframe').src = '';
  document.getElementById('modalVideoIframe').src = '';
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
