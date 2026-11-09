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
          <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Academic Structure &amp; Curriculum</h4>
          <p class="text-muted small mb-0">Manage CBT Diploma Modules (Semester 1 &amp; 2), NVQ Courses, Batches, and Subjects</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
          <button class="btn btn-primary btn-sm px-3 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addCbtModuleModal">
            <i class="bi bi-plus-circle-fill me-1"></i> Add CBT Module
          </button>
          <button class="btn btn-outline-primary btn-sm px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
            <i class="bi bi-plus-lg me-1"></i> Add Subject
          </button>
          <button class="btn btn-outline-secondary btn-sm px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#addCourseModal">
            <i class="bi bi-mortarboard me-1"></i> Add Course
          </button>
        </div>
      </div>

      <!-- 1. CBT Curriculum Modules (1 to 18) Section - Semester 1 & 2 -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 border-bottom">
          <div>
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-journal-check text-primary me-2"></i>CBT Curriculum Modules (1 to 18) - Semester 1 &amp; 2</h5>
            <small class="text-muted">Manage all diploma modules, semester allocations, and related NVQ competency standards displayed dynamically on the Home page.</small>
          </div>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fw-bold">
            <?= count($curriculumModules ?? []) ?> Modules Total
          </span>
        </div>

        <!-- Desktop Table (>= 768px) -->
        <div class="d-none d-md-block table-responsive">
          <table class="table table-hover table-custom align-middle mb-0">
            <thead>
              <tr>
                <th class="ps-4" style="width: 50px;">No.</th>
                <th style="width: 140px;">Code No.</th>
                <th>Module Title</th>
                <th style="width: 130px;">Semester</th>
                <th style="width: 140px;">Type</th>
                <th>Related Competency Unit/s</th>
                <th style="width: 90px;">Page</th>
                <th style="width: 100px;">Status</th>
                <th class="text-end pe-4" style="width: 160px;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (($curriculumModules ?? []) as $cm): ?>
                <tr>
                  <td class="ps-4 fw-bold text-muted"><?= (int)$cm['module_no'] ?>.</td>
                  <td>
                    <span class="badge bg-light text-primary border font-monospace fw-bold px-2.5 py-1">
                      <?= e($cm['module_code']) ?>
                    </span>
                  </td>
                  <td>
                    <strong class="text-dark"><?= e($cm['module_title']) ?></strong>
                  </td>
                  <td>
                    <?php if ((int)$cm['semester'] === 1): ?>
                      <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill">Semester 1</span>
                    <?php else: ?>
                      <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">Semester 2</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge <?= ($cm['module_type'] === 'Employability') ? 'bg-info-subtle text-info-emphasis border border-info-subtle' : 'bg-light text-dark border' ?> px-2.5 py-1 rounded-pill">
                      <?= e($cm['module_type']) ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge bg-light text-secondary border font-monospace"><?= e($cm['related_units']) ?></span>
                  </td>
                  <td>
                    <small class="text-muted">Pg. <?= e($cm['curriculum_page']) ?></small>
                  </td>
                  <td>
                    <span class="badge <?= ($cm['status'] === 'active') ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?> px-2 py-1">
                      <?= ucfirst($cm['status']) ?>
                    </span>
                  </td>
                  <td class="text-end pe-4">
                    <button type="button" class="btn btn-outline-primary btn-sm px-2.5 py-1 rounded-pill me-1" data-bs-toggle="modal" data-bs-target="#editCbtModal_<?= $cm['id'] ?>">
                      <i class="bi bi-pencil-square me-1"></i> Edit
                    </button>
                    <form action="index.php?page=admin_delete_curriculum_module" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete module <?= e($cm['module_code']) ?>?');">
                      <?= Csrf::field() ?>
                      <input type="hidden" name="module_id" value="<?= $cm['id'] ?>">
                      <button type="submit" class="btn btn-outline-danger btn-sm px-2 py-1 rounded-pill">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Mobile Card List for Admin (< 768px) -->
        <div class="d-block d-md-none p-3">
          <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <h6 class="fw-bold text-dark mb-0">Modules (<?= count($curriculumModules ?? []) ?>)</h6>
            <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addCbtModuleModal">
              <i class="bi bi-plus-lg me-1"></i> Add Module
            </button>
          </div>
          <?php foreach (($curriculumModules ?? []) as $cm): ?>
            <div class="card border rounded-3 p-3 mb-2.5 bg-white shadow-2xs">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <span class="badge bg-light text-primary border font-monospace fw-bold px-2.5 py-1">
                    <?= e($cm['module_code']) ?>
                  </span>
                  <span class="badge <?= ((int)$cm['semester'] === 1) ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-success-subtle text-success border border-success-subtle' ?> rounded-pill ms-1" style="font-size: 0.7rem;">
                    Sem <?= (int)$cm['semester'] ?>
                  </span>
                </div>
                <span class="badge <?= ($cm['status'] === 'active') ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?> px-2 py-0.5">
                  <?= ucfirst($cm['status']) ?>
                </span>
              </div>
              <h6 class="fw-bold text-dark mb-1" style="font-size: 0.95rem;"><?= e($cm['module_title']) ?></h6>
              <div class="small text-muted mb-2">
                <i class="bi bi-tag me-1"></i><?= e($cm['module_type']) ?> &bull; 
                <i class="bi bi-diagram-3 me-1"></i><?= e($cm['related_units']) ?>
                <?php if (!empty($cm['curriculum_page'])): ?>
                  &bull; <i class="bi bi-file-earmark me-1"></i>Pg. <?= e($cm['curriculum_page']) ?>
                <?php endif; ?>
              </div>
              <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                <button type="button" class="btn btn-outline-primary btn-sm px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#editCbtModal_<?= $cm['id'] ?>">
                  <i class="bi bi-pencil-square me-1"></i> Edit
                </button>
                <form action="index.php?page=admin_delete_curriculum_module" method="POST" class="d-inline" onsubmit="return confirm('Delete <?= e($cm['module_code']) ?>?');">
                  <?= Csrf::field() ?>
                  <input type="hidden" name="module_id" value="<?= $cm['id'] ?>">
                  <button type="submit" class="btn btn-outline-danger btn-sm px-2.5 rounded-pill">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Modals: Edit CBT Modules (for both desktop and mobile) -->
        <?php foreach (($curriculumModules ?? []) as $cm): ?>
          <div class="modal fade" id="editCbtModal_<?= $cm['id'] ?>" tabindex="-1">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                      <div class="modal-header bg-primary text-white py-3">
                        <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Module: <?= e($cm['module_code']) ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                      </div>
                      <form action="index.php?page=admin_update_curriculum_module" method="POST">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="module_id" value="<?= $cm['id'] ?>">
                        <div class="modal-body p-4">
                          <div class="row g-3">
                            <div class="col-4">
                              <label class="form-label fw-semibold small text-secondary">Module No. *</label>
                              <input type="number" name="module_no" class="form-control" value="<?= (int)$cm['module_no'] ?>" required>
                            </div>
                            <div class="col-8">
                              <label class="form-label fw-semibold small text-secondary">Module Code *</label>
                              <input type="text" name="module_code" class="form-control font-monospace" value="<?= e($cm['module_code']) ?>" required>
                            </div>
                            <div class="col-12">
                              <label class="form-label fw-semibold small text-secondary">Module Title *</label>
                              <input type="text" name="module_title" class="form-control" value="<?= e($cm['module_title']) ?>" required>
                            </div>
                            <div class="col-6">
                              <label class="form-label fw-semibold small text-secondary">Semester *</label>
                              <select name="semester" class="form-select" required>
                                <option value="1" <?= ((int)$cm['semester'] === 1) ? 'selected' : '' ?>>Semester 1</option>
                                <option value="2" <?= ((int)$cm['semester'] === 2) ? 'selected' : '' ?>>Semester 2</option>
                              </select>
                            </div>
                            <div class="col-6">
                              <label class="form-label fw-semibold small text-secondary">Module Type *</label>
                              <select name="module_type" class="form-select" required>
                                <option value="Technical" <?= ($cm['module_type'] === 'Technical') ? 'selected' : '' ?>>Technical</option>
                                <option value="Employability" <?= ($cm['module_type'] === 'Employability') ? 'selected' : '' ?>>Employability</option>
                              </select>
                            </div>
                            <div class="col-12">
                              <label class="form-label fw-semibold small text-secondary">Related Competency Unit/s</label>
                              <input type="text" name="related_units" class="form-control font-monospace" value="<?= e($cm['related_units']) ?>" placeholder="e.g. K72T001U01 or All Units">
                            </div>
                            <div class="col-6">
                              <label class="form-label fw-semibold small text-secondary">Curriculum Page</label>
                              <input type="text" name="curriculum_page" class="form-control" value="<?= e($cm['curriculum_page']) ?>" placeholder="e.g. 01">
                            </div>
                            <div class="col-6">
                              <label class="form-label fw-semibold small text-secondary">Status</label>
                              <select name="status" class="form-select">
                                <option value="active" <?= ($cm['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($cm['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                              </select>
                            </div>
                          </div>
                        </div>
                        <div class="modal-footer bg-light py-2 px-4">
                          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">Update Module</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 2. Courses, Batches & Subjects Section -->
      <div class="row g-4">
        <!-- Subjects List -->
        <div class="col-lg-7">
          <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white py-3 px-4 border-bottom">
              <h5 class="fw-bold mb-0 text-dark">LMS Subject Registrations</h5>
            </div>
            <div class="table-responsive">
              <table class="table table-hover table-custom mb-0">
                <thead>
                  <tr>
                    <th>Code</th>
                    <th>Subject Name</th>
                    <th>Credits</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($subjects as $sb): ?>
                    <tr>
                      <td><span class="badge bg-light text-primary border fw-bold"><?= e($sb['subject_code']) ?></span></td>
                      <td>
                        <div class="fw-bold text-dark"><?= e($sb['subject_name']) ?></div>
                        <small class="text-muted"><?= e($sb['description']) ?></small>
                      </td>
                      <td><span class="badge bg-secondary-subtle text-secondary"><?= (int)$sb['credits'] ?> Credits</span></td>
                      <td><span class="badge bg-success-subtle text-success">Active</span></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Courses & Batches List -->
        <div class="col-lg-5">
          <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white py-3 px-4 border-bottom">
              <h5 class="fw-bold mb-0 text-dark">Active Courses &amp; Batches</h5>
            </div>
            <div class="card-body p-4">
              <?php foreach ($courses as $c): ?>
                <div class="p-3 border rounded-3 mb-3 bg-light">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-primary text-white fw-bold"><?= e($c['course_code']) ?></span>
                    <span class="badge bg-warning text-dark"><?= e($c['nvq_level']) ?></span>
                  </div>
                  <h6 class="fw-bold text-dark mb-1"><?= e($c['course_name']) ?></h6>
                  <p class="small text-muted mb-2"><?= e($c['description']) ?></p>

                  <div class="mt-2 pt-2 border-top">
                    <small class="fw-bold text-secondary d-block mb-1">Enrolled Batches:</small>
                    <?php foreach ($classes as $cl): ?>
                      <?php if ($cl['course_id'] == $c['id']): ?>
                        <span class="badge bg-white text-dark border me-1"><i class="bi bi-people me-1"></i><?= e($cl['class_name']) ?></span>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- Modal: Add CBT Module -->
<div class="modal fade" id="addCbtModuleModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
      <div class="modal-header bg-primary text-white py-3">
        <h5 class="modal-title fw-bold"><i class="bi bi-journal-plus me-2"></i>Add CBT Curriculum Module</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="index.php?page=admin_create_curriculum_module" method="POST">
        <?= Csrf::field() ?>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-4">
              <label class="form-label fw-semibold small text-secondary">Module No. *</label>
              <input type="number" name="module_no" class="form-control" value="<?= count($curriculumModules ?? []) + 1 ?>" required>
            </div>
            <div class="col-8">
              <label class="form-label fw-semibold small text-secondary">Module Code *</label>
              <input type="text" name="module_code" class="form-control font-monospace" placeholder="e.g. K72T001M16" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold small text-secondary">Module Title *</label>
              <input type="text" name="module_title" class="form-control" placeholder="e.g. Advanced AI & Cloud Architecture" required>
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold small text-secondary">Semester *</label>
              <select name="semester" class="form-select" required>
                <option value="1">Semester 1</option>
                <option value="2">Semester 2</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold small text-secondary">Module Type *</label>
              <select name="module_type" class="form-select" required>
                <option value="Technical" selected>Technical</option>
                <option value="Employability">Employability</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold small text-secondary">Related Competency Unit/s</label>
              <input type="text" name="related_units" class="form-control font-monospace" placeholder="e.g. K72T001U04, K72T001U07">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold small text-secondary">Curriculum Page</label>
              <input type="text" name="curriculum_page" class="form-control" placeholder="e.g. 48">
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light py-2 px-4">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">Save Module</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Add Course -->
<div class="modal fade" id="addCourseModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
      <div class="modal-header bg-edu-navy text-white py-3">
        <h5 class="modal-title fw-bold"><i class="bi bi-mortarboard me-2 text-warning"></i>Add New Course</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="index.php?page=admin_create_course" method="POST">
        <?= Csrf::field() ?>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Course Code *</label>
            <input type="text" name="course_code" class="form-control" placeholder="e.g. NVTI-ICT-L5" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Course Name *</label>
            <input type="text" name="course_name" class="form-control" placeholder="e.g. National Diploma in ICT" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">NVQ Level</label>
            <input type="text" name="nvq_level" class="form-control" value="NVQ Level 5">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Description</label>
            <textarea name="description" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light py-2 px-4">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">Save Course</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Add Subject -->
<div class="modal fade" id="addSubjectModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
      <div class="modal-header bg-edu-navy text-white py-3">
        <h5 class="modal-title fw-bold"><i class="bi bi-book me-2 text-warning"></i>Add Subject / Module</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="index.php?page=admin_create_subject" method="POST">
        <?= Csrf::field() ?>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Subject Code *</label>
            <input type="text" name="subject_code" class="form-control" placeholder="e.g. ICT505" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Subject Name *</label>
            <input type="text" name="subject_name" class="form-control" placeholder="e.g. Mobile Application Development" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Description</label>
            <textarea name="description" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light py-2 px-4">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">Save Subject</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
