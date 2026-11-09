<?php
require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/layouts/navbar.php';

// Fetch instructors dynamically from the database
$db = Database::getConnection();
$instructors = $db->query("
    SELECT u.id, u.name, u.email, u.profile_image, tp.teacher_code, tp.phone, tp.qualification, tp.specialization, tp.preferred_slot
    FROM users u
    LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
    WHERE u.role = 'teacher' AND u.status = 'active'
    ORDER BY u.id ASC
")->fetchAll();

// Fetch all CBT curriculum modules dynamically from database (editable in Admin Panel)
$cbtModulesDb = $db->query("SELECT * FROM curriculum_modules WHERE status = 'active' ORDER BY module_no ASC")->fetchAll();

$semester1Modules = [];
$semester2Modules = [];
$allModules = [];

foreach ($cbtModulesDb as $row) {
    $rawUnits = trim($row['related_units']);
    $unitsArr = !empty($rawUnits) ? array_map('trim', explode(',', $rawUnits)) : [];

    $item = [
        'no' => (int)$row['module_no'],
        'code' => $row['module_code'],
        'title' => $row['module_title'],
        'units' => $unitsArr,
        'type' => $row['module_type'],
        'page' => $row['curriculum_page'],
        'semester' => (int)$row['semester']
    ];

    $allModules[] = $item;
    if ((int)$row['semester'] === 1) {
        $semester1Modules[] = $item;
    } else {
        $semester2Modules[] = $item;
    }
}
?>

<!-- 1. Hero Section: Crystal Clear Video (Themed Dark Gradient & Smooth Fade-in) -->
<section class="hero-video-wrapper text-white text-center">
  <!-- Autoplay loop muted video with smooth fade-in once buffer/frame is ready -->
  <video id="heroVideo" class="hero-bg-video" autoplay muted loop playsinline preload="auto" onplaying="this.classList.add('is-loaded')" onloadeddata="this.classList.add('is-loaded')">
    <source src="1006_1_oponlw.mp4" type="video/mp4">
    <source src="assets/videos/1006_1_oponlw.mp4" type="video/mp4">
    <source src="https://res.cloudinary.com/dhxfrmepy/video/upload/v1759822024/1006_1_oponlw.mp4" type="video/mp4">
  </video>
  
  <!-- Subtle natural light scrim so video details stay 100% visible -->
  <div class="hero-overlay"></div>

  <div class="container hero-content py-4 py-md-5">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10 py-4 py-md-5 my-md-3">

        <!-- Course Title (Responsive Typography) -->
        <h1 class="hero-title fw-bold font-serif text-white text-uppercase tracking-wider mb-2">
          National Diploma - Information &amp; Communication Technology
        </h1>

      </div>
    </div>
  </div>
</section>

<script>
  // Ensure smooth fade-in for cached or loaded video
  (function() {
    var v = document.getElementById('heroVideo');
    if (v) {
      if (v.readyState >= 2) {
        v.classList.add('is-loaded');
      } else {
        v.addEventListener('loadeddata', function() { v.classList.add('is-loaded'); });
        v.addEventListener('playing', function() { v.classList.add('is-loaded'); });
        setTimeout(function() { v.classList.add('is-loaded'); }, 1500);
      }
    }
  })();
</script>

<!-- 2. Senior Instructors Section (Dynamic Profiles from Database - Exactly 2 Cards) -->
<section id="instructors" class="py-4 py-md-5 bg-white border-bottom">
  <div class="container py-2 py-md-3">

    <!-- Section Title -->
    <div class="text-center mx-auto mb-4 mb-md-5" style="max-width: 650px;">
      <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill mb-2 fw-semibold">
        <i class="bi bi-people-fill me-1"></i> Academic Faculty
      </span>
      <h2 class="fw-bold font-serif text-dark mb-2 fs-3 fs-md-2">Our Senior Instructors</h2>
      <p class="text-muted small mb-0 px-2">
        Dedicated faculty members leading the National Diploma in Information &amp; Communication Technology at NVTI Baddegama.
      </p>
    </div>

    <!-- 2 Instructor Cards Grid (Exact Match to Image 1 & Responsive for Mobile) -->
    <div class="row g-4 justify-content-center">
      <?php foreach ($instructors as $idx => $inst): ?>
        <?php
          $photo = !empty($inst['profile_image']) ? $inst['profile_image'] : ($idx === 0 ? 'assets/images/teachers/nishantha.jpg' : 'assets/images/teachers/lakshman.jpg');
          $modalId = 'instructorModal_' . $inst['id'];
        ?>
        <div class="col-11 col-sm-9 col-md-6 col-lg-5 col-xl-4">
          <div class="nvti-instructor-card h-100 d-flex flex-column justify-content-between">
            
            <!-- Instructor Photo on Top (Matching Image 1) -->
            <div class="nvti-instructor-img-box">
              <img src="<?= e($photo) ?>" 
                   onerror="this.onerror=null; this.src='assets/images/software_developer_logo.png';" 
                   alt="Profile of <?= e($inst['name']) ?>" 
                   class="nvti-instructor-img">
            </div>

            <!-- Card Body with Name, Title & Maroon View Profile Button (Matching Image 1) -->
            <div class="p-4 text-center d-flex flex-column justify-content-between flex-grow-1">
              <div>
                <h4 class="fw-bold text-dark mb-1 font-serif fs-5"><?= e($inst['name']) ?></h4>
                <p class="text-muted small mb-3">Senior Instructor</p>
                <?php if (!empty($inst['specialization'])): ?>
                  <span class="badge bg-light text-primary border px-2.5 py-1 small fw-medium mb-3 d-inline-block">
                    <i class="bi bi-check-circle-fill me-1"></i> <?= e($inst['specialization']) ?>
                  </span>
                <?php endif; ?>
              </div>

              <div>
                <!-- Maroon Button Matching Image 1 -->
                <button type="button" 
                        class="btn btn-nvti-maroon-card px-4 py-2 shadow-sm"
                        data-bs-toggle="modal" 
                        data-bs-target="#<?= $modalId ?>">
                  View Profile
                </button>
              </div>
            </div>

          </div>
        </div>

        <!-- Instructor Profile Details Modal -->
        <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-labelledby="<?= $modalId ?>Label" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
              <div class="modal-header text-white py-3 px-4" style="background-color: #7A1418;">
                <h5 class="modal-title fw-bold" id="<?= $modalId ?>Label">
                  <i class="bi bi-person-badge me-2"></i>Instructor Profile
                </h5>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body p-4 text-center">
                <img src="<?= e($photo) ?>" 
                     onerror="this.onerror=null; this.src='assets/images/software_developer_logo.png';" 
                     alt="<?= e($inst['name']) ?>" 
                     class="rounded-4 shadow-sm border mb-3" 
                     style="width: 150px; height: 180px; object-fit: cover;">

                <h4 class="fw-bold text-dark mb-1 font-serif"><?= e($inst['name']) ?></h4>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill mb-3 fw-semibold">
                  Senior Instructor &bull; NVTI Baddegama
                </span>

                <div class="text-start bg-light p-3.5 rounded-3 mb-3 border">
                  <div class="row g-2 small">
                    <div class="col-sm-5 text-muted fw-semibold">Faculty / Trade:</div>
                    <div class="col-sm-7 text-dark fw-bold">ICT &amp; Multimedia Technology</div>

                    <div class="col-sm-5 text-muted fw-semibold">Qualifications:</div>
                    <div class="col-sm-7 text-dark"><?= e($inst['qualification'] ?? 'Senior Instructor') ?></div>

                    <div class="col-sm-5 text-muted fw-semibold">Specialization:</div>
                    <div class="col-sm-7 text-dark"><?= e($inst['specialization'] ?? 'ICT Systems') ?></div>

                    <div class="col-sm-5 text-muted fw-semibold">Assigned Shift:</div>
                    <div class="col-sm-7 text-dark fw-bold text-primary"><?= e($inst['preferred_slot'] ?? 'Regular Shift') ?></div>

                    <?php if (!empty($inst['email'])): ?>
                      <div class="col-sm-5 text-muted fw-semibold">Email:</div>
                      <div class="col-sm-7"><a href="mailto:<?= e($inst['email']) ?>" class="text-decoration-none"><?= e($inst['email']) ?></a></div>
                    <?php endif; ?>

                    <?php if (!empty($inst['phone'])): ?>
                      <div class="col-sm-5 text-muted fw-semibold">Contact Phone:</div>
                      <div class="col-sm-7 text-dark"><?= e($inst['phone']) ?></div>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="d-grid gap-2">
                  <a href="index.php?page=login" class="btn btn-outline-primary fw-semibold py-2 rounded-pill">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Instructor Portal
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- 3. Competency Based Training Curriculum (Divided into Semester 1 & Semester 2 / Semester 3) -->
<section id="curriculum" class="py-5 bg-light border-bottom">
  <div class="container py-3">

    <!-- Section Header -->
    <div class="text-center mx-auto mb-4" style="max-width: 800px;">
      <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill mb-2 fw-semibold">
        <i class="bi bi-journal-code me-1"></i> Competency Based Training Curriculum
      </span>
      <h2 class="fw-bold font-serif text-dark mb-2">
        NVQ Level 5 National Diploma in Information &amp; Communication Technology
      </h2>
      <p class="text-muted small mb-0">
        Mapping of Competency Units and Curriculum Modules officially structured by Semester 1, Semester 2 &amp; Semester 3.
      </p>
    </div>

    <!-- Nav Tabs for Semesters (All Modules First as requested) -->
    <div class="d-flex justify-content-center mb-4">
      <ul class="nav nav-pills curriculum-nav-pills gap-2 bg-white p-2 rounded-pill border shadow-2xs" id="semesterTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="all-tab" data-bs-toggle="pill" data-bs-target="#all-content" type="button" role="tab">
            <i class="bi bi-collection-fill me-1.5"></i> All Modules (<?= count($allModules) ?> Modules)
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="sem1-tab" data-bs-toggle="pill" data-bs-target="#sem1-content" type="button" role="tab">
            <i class="bi bi-1-circle-fill me-1.5"></i> Semester 1 (<?= count($semester1Modules) ?> Modules)
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="sem2-tab" data-bs-toggle="pill" data-bs-target="#sem2-content" type="button" role="tab">
            <i class="bi bi-2-circle-fill me-1.5"></i> Semester 2 (<?= count($semester2Modules) ?> Modules)
          </button>
        </li>
      </ul>
    </div>

    <!-- Tab Contents -->
    <div class="tab-content" id="semesterTabsContent">

      <!-- ================= ALL MODULES (DEFAULT ACTIVE) ================= -->
      <div class="tab-pane fade show active" id="all-content" role="tabpanel">
        <div class="card curriculum-card border-0 shadow-sm">
          <div class="card-header bg-white py-3 px-3 px-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 border-bottom">
            <div>
              <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-card-checklist text-primary me-2"></i>Complete CBT Curriculum Modules (1 to <?= count($allModules) ?>)
              </h5>
              <small class="text-muted">Full competency syllabus for NVQ Level 5 National Diploma in ICT</small>
            </div>
            <span class="badge bg-dark text-white rounded-pill px-3 py-1.5 fw-semibold align-self-start align-self-md-auto">
              <?= count($allModules) ?> Modules Total
            </span>
          </div>

          <!-- Desktop Table (>= 768px) -->
          <div class="d-none d-md-block table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light text-uppercase small text-muted">
                <tr>
                  <th class="ps-4 py-3" style="width: 60px;">No.</th>
                  <th style="width: 160px;">Code No.</th>
                  <th>List of Modules</th>
                  <th>Related Competency Unit/s</th>
                  <th style="width: 140px;">Semester</th>
                  <th style="width: 120px;">Curriculum</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($allModules as $m): ?>
                  <tr>
                    <td class="ps-4 fw-bold text-muted"><?= $m['no'] ?>.</td>
                    <td><span class="module-badge <?= ($m['type'] === 'Employability') ? 'module-badge-emp' : '' ?>"><?= e($m['code']) ?></span></td>
                    <td><span class="fw-bold text-dark"><?= e($m['title']) ?></span></td>
                    <td>
                      <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($m['units'] as $u): ?>
                          <span class="unit-badge-sm"><?= e($u) ?></span>
                        <?php endforeach; ?>
                      </div>
                    </td>
                    <td>
                      <?php if ($m['semester'] === 1): ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">Semester 1</span>
                      <?php else: ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">Semester 2</span>
                      <?php endif; ?>
                    </td>
                    <td><span class="text-muted small">Page <?= e($m['page']) ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Mobile Card List (< 768px) Matching User's Screenshot -->
          <div class="d-block d-md-none p-3">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
              <h5 class="fw-bold text-dark mb-0 fs-6">Unit Details</h5>
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small"><?= count($allModules) ?> Modules</span>
            </div>
            <div class="mobile-unit-list">
              <?php foreach ($allModules as $m): ?>
                <div class="mobile-unit-card">
                  <div class="mobile-unit-left">
                    <span class="mobile-unit-code"><?= e($m['code']) ?></span>
                    <div class="mt-1 d-flex align-items-center gap-1">
                      <span class="badge <?= ($m['semester'] === 1) ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-success-subtle text-success border border-success-subtle' ?> rounded-pill" style="font-size: 0.65rem; padding: 0.18rem 0.45rem;">
                        Sem <?= $m['semester'] ?>
                      </span>
                      <?php if (!empty($m['page'])): ?>
                        <span class="text-muted" style="font-size: 0.7rem;">p.<?= e($m['page']) ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="mobile-unit-right text-end ps-2">
                    <div class="mobile-unit-title"><?= e($m['title']) ?></div>
                    <?php if (!empty($m['units'])): ?>
                      <div class="mobile-unit-units text-muted small mt-1">
                        <?= implode(', ', $m['units']) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- ================= SEMESTER 1 ================= -->
      <div class="tab-pane fade" id="sem1-content" role="tabpanel">
        <div class="card curriculum-card border-0 shadow-sm">
          <div class="card-header bg-white py-3 px-3 px-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 border-bottom">
            <div>
              <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-calendar2-check-fill text-primary me-2"></i>Semester 1 Modules &amp; Competencies
              </h5>
              <small class="text-muted">Foundation &amp; Core Technical Development Modules</small>
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fw-semibold align-self-start align-self-md-auto">
              <?= count($semester1Modules) ?> Curriculum Modules
            </span>
          </div>

          <!-- Desktop Table (>= 768px) -->
          <div class="d-none d-md-block table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light text-uppercase small text-muted">
                <tr>
                  <th class="ps-4 py-3" style="width: 60px;">No.</th>
                  <th style="width: 160px;">Module Code</th>
                  <th>List of Modules</th>
                  <th>Related Competency Unit/s</th>
                  <th style="width: 140px;">Type</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($semester1Modules as $m): ?>
                  <tr>
                    <td class="ps-4 fw-bold text-muted"><?= $m['no'] ?>.</td>
                    <td>
                      <span class="module-badge <?= ($m['type'] === 'Employability') ? 'module-badge-emp' : '' ?>">
                        <?= e($m['code']) ?>
                      </span>
                    </td>
                    <td>
                      <span class="fw-bold text-dark"><?= e($m['title']) ?></span>
                    </td>
                    <td>
                      <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($m['units'] as $u): ?>
                          <span class="unit-badge-sm"><?= e($u) ?></span>
                        <?php endforeach; ?>
                      </div>
                    </td>
                    <td>
                      <?php if ($m['type'] === 'Employability'): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                          Employability
                        </span>
                      <?php else: ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">
                          Technical
                        </span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Mobile Card List (< 768px) Matching User's Screenshot -->
          <div class="d-block d-md-none p-3">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
              <h5 class="fw-bold text-dark mb-0 fs-6">Semester 1 Unit Details</h5>
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small"><?= count($semester1Modules) ?> Modules</span>
            </div>
            <div class="mobile-unit-list">
              <?php foreach ($semester1Modules as $m): ?>
                <div class="mobile-unit-card">
                  <div class="mobile-unit-left">
                    <span class="mobile-unit-code"><?= e($m['code']) ?></span>
                    <div class="mt-1">
                      <span class="badge <?= ($m['type'] === 'Employability') ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' ?> rounded-pill" style="font-size: 0.65rem; padding: 0.18rem 0.45rem;">
                        <?= e($m['type']) ?>
                      </span>
                    </div>
                  </div>
                  <div class="mobile-unit-right text-end ps-2">
                    <div class="mobile-unit-title"><?= e($m['title']) ?></div>
                    <?php if (!empty($m['units'])): ?>
                      <div class="mobile-unit-units text-muted small mt-1">
                        <?= implode(', ', $m['units']) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- ================= SEMESTER 2 ================= -->
      <div class="tab-pane fade" id="sem2-content" role="tabpanel">
        <div class="card curriculum-card border-0 shadow-sm">
          <div class="card-header bg-white py-3 px-3 px-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 border-bottom">
            <div>
              <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-calendar2-check-fill text-success me-2"></i>Semester 2 Modules &amp; Competencies
              </h5>
              <small class="text-muted">Advanced Software Development, Networks &amp; Cloud Systems</small>
            </div>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold align-self-start align-self-md-auto">
              <?= count($semester2Modules) ?> Curriculum Modules
            </span>
          </div>

          <!-- Desktop Table (>= 768px) -->
          <div class="d-none d-md-block table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light text-uppercase small text-muted">
                <tr>
                  <th class="ps-4 py-3" style="width: 60px;">No.</th>
                  <th style="width: 160px;">Module Code</th>
                  <th>List of Modules</th>
                  <th>Related Competency Unit/s</th>
                  <th style="width: 140px;">Type</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($semester2Modules as $m): ?>
                  <tr>
                    <td class="ps-4 fw-bold text-muted"><?= $m['no'] ?>.</td>
                    <td>
                      <span class="module-badge <?= ($m['type'] === 'Employability') ? 'module-badge-emp' : '' ?>">
                        <?= e($m['code']) ?>
                      </span>
                    </td>
                    <td>
                      <span class="fw-bold text-dark"><?= e($m['title']) ?></span>
                    </td>
                    <td>
                      <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($m['units'] as $u): ?>
                          <span class="unit-badge-sm"><?= e($u) ?></span>
                        <?php endforeach; ?>
                      </div>
                    </td>
                    <td>
                      <?php if ($m['type'] === 'Employability'): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                          Employability
                        </span>
                      <?php else: ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">
                          Technical
                        </span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Mobile Card List (< 768px) Matching User's Screenshot -->
          <div class="d-block d-md-none p-3">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
              <h5 class="fw-bold text-dark mb-0 fs-6">Semester 2 Unit Details</h5>
              <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small"><?= count($semester2Modules) ?> Modules</span>
            </div>
            <div class="mobile-unit-list">
              <?php foreach ($semester2Modules as $m): ?>
                <div class="mobile-unit-card">
                  <div class="mobile-unit-left">
                    <span class="mobile-unit-code"><?= e($m['code']) ?></span>
                    <div class="mt-1">
                      <span class="badge <?= ($m['type'] === 'Employability') ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' ?> rounded-pill" style="font-size: 0.65rem; padding: 0.18rem 0.45rem;">
                        <?= e($m['type']) ?>
                      </span>
                    </div>
                  </div>
                  <div class="mobile-unit-right text-end ps-2">
                    <div class="mobile-unit-title"><?= e($m['title']) ?></div>
                    <?php if (!empty($m['units'])): ?>
                      <div class="mobile-unit-units text-muted small mt-1">
                        <?= implode(', ', $m['units']) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>
</section>

<!-- 4. Fully Responsive Clean Footer -->
<footer class="bg-white border-top py-4 mt-auto">
  <div class="container">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-center gap-3 text-muted small text-center text-lg-start">
      <!-- Logos & Branding -->
      <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start gap-2">
        <div class="d-flex align-items-center gap-2">
          <img src="assets/images/logo/vta_logo.png" alt="VTA Logo" style="height: 28px; width: auto; object-fit: contain;">
          <span class="text-secondary opacity-50">|</span>
          <img src="assets/images/logo/NVTI_logo.png" onerror="this.onerror=null; this.src='https://nvtibaddegama.site/images/logo/NVTI_logo.png';" alt="NVTI Logo" style="height: 24px; width: auto; object-fit: contain;">
        </div>
        <span class="d-inline-block">
          <strong>NVTI Baddegama</strong> &bull; National Diploma in ICT (NVQ Level 5) &copy; <?= date('Y') ?>
        </span>
      </div>

      <!-- Quick Nav Links -->
      <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 gap-sm-3">
        <a href="index.php?page=home#instructors" class="text-decoration-none text-secondary hover-primary">Instructors</a>
        <span class="text-muted opacity-50 d-none d-sm-inline">&bull;</span>
        <a href="index.php?page=home#curriculum" class="text-decoration-none text-secondary hover-primary">Curriculum</a>
        <span class="text-muted opacity-50 d-none d-sm-inline">&bull;</span>
        <a href="index.php?page=login" class="text-decoration-none text-secondary hover-primary">Portal Sign In</a>
        <span class="text-muted opacity-50 d-none d-sm-inline">&bull;</span>
        <a href="index.php?page=activate" class="text-decoration-none text-secondary hover-primary">Student Activation</a>
        <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">Online LMS</span>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom App JS -->
<script src="assets/js/app.js"></script>
</body>
</html>
