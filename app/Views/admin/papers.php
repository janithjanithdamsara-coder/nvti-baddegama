<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

$semFilter = (int)($_GET['semester'] ?? 0);
$typeFilter = trim($_GET['paper_type'] ?? '');
$modFilter = trim($_GET['module_code'] ?? '');
$searchQuery = trim($_GET['search'] ?? '');

// Theme Palettes for Google Classroom card style
$paperPalettes = [
  'blue'   => ['bg' => 'linear-gradient(135deg, #1967d2 0%, #174ea6 100%)', 'icon' => 'bi-database-fill-gear', 'avatar_color' => '#1967d2', 'label' => 'Database'],
  'maroon' => ['bg' => 'linear-gradient(135deg, #7A1418 0%, #991b1b 100%)', 'icon' => 'bi-camera-reels-fill', 'avatar_color' => '#7A1418', 'label' => 'Media & Video'],
  'dark'   => ['bg' => 'linear-gradient(135deg, #37474f 0%, #263238 100%)', 'icon' => 'bi-code-slash', 'avatar_color' => '#37474f', 'label' => 'Programming'],
  'indigo' => ['bg' => 'linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%)', 'icon' => 'bi-hdd-network-fill', 'avatar_color' => '#1e3a8a', 'label' => 'Hardware & Net'],
  'teal'   => ['bg' => 'linear-gradient(135deg, #0f766e 0%, #115e59 100%)', 'icon' => 'bi-brush-fill', 'avatar_color' => '#0f766e', 'label' => 'Graphics Design'],
  'slate'  => ['bg' => 'linear-gradient(135deg, #334155 0%, #1e293b 100%)', 'icon' => 'bi-globe-americas', 'avatar_color' => '#334155', 'label' => 'Web Design'],
  'purple' => ['bg' => 'linear-gradient(135deg, #581c87 0%, #3b0764 100%)', 'icon' => 'bi-cpu-fill', 'avatar_color' => '#581c87', 'label' => 'System & CBT'],
  'amber'  => ['bg' => 'linear-gradient(135deg, #b45309 0%, #92400e 100%)', 'icon' => 'bi-calculator-fill', 'avatar_color' => '#b45309', 'label' => 'Mathematics'],
];

function getPaperTheme($paper, $palettes) {
    $str = strtolower(($paper['subject_title'] ?? '') . ' ' . ($paper['title'] ?? '') . ' ' . ($paper['module_code'] ?? ''));
    if (str_contains($str, 'database') || str_contains($str, 'sql') || str_contains($str, 'dbms')) {
        return $palettes['blue'];
    } elseif (str_contains($str, 'program') || str_contains($str, 'oop') || str_contains($str, 'code') || str_contains($str, 'algo') || str_contains($str, 'm05') || str_contains($str, 'm06')) {
        return $palettes['dark'];
    } elseif (str_contains($str, 'web') || str_contains($str, 'm07') || str_contains($str, 'host')) {
        return $palettes['slate'];
    } elseif (str_contains($str, 'graphic') || str_contains($str, 'print') || str_contains($str, 'm08')) {
        return $palettes['teal'];
    } elseif (str_contains($str, 'architect') || str_contains($str, 'electronic') || str_contains($str, 'network') || str_contains($str, 'm04')) {
        return $palettes['indigo'];
    } elseif (str_contains($str, 'math') || str_contains($str, 'm03')) {
        return $palettes['amber'];
    } elseif (str_contains($str, 'video') || str_contains($str, 'cinema')) {
        return $palettes['maroon'];
    } elseif ((int)$paper['semester'] === 2) {
        return $palettes['purple'];
    }
    return $palettes['blue'];
}
?>

<div class="app-wrapper">
  <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <div class="container-fluid">
      <?= Flash::render() ?>

      <!-- Header Banner (Clean Google Classroom Hub Aesthetic) -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 text-white overflow-hidden position-relative" style="background: linear-gradient(135deg, #7A1418 0%, #991b1b 100%); padding: 1.85rem 2.25rem;">
        <!-- Watermark Background Crest -->
        <div class="position-absolute end-0 top-50 translate-middle-y me-4 opacity-10 pointer-events-none d-none d-md-block" style="font-size: 8rem; line-height: 0;">
          <i class="bi bi-mortarboard-fill text-white"></i>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 position-relative" style="z-index: 3;">
          <div class="pe-md-3">
            <h3 class="fw-bold mb-1.5 text-white" style="letter-spacing: -0.3px;">Past Examination &amp; Model Question Papers</h3>
            <p class="mb-0 text-white opacity-85 small" style="max-width: 680px;">Upload, categorize by semester, and manage official past papers, model questions, and marking schemes for NVTI Diploma students.</p>
          </div>

          <div class="d-flex align-items-center flex-shrink-0 align-self-start align-self-md-center">
            <button type="button" class="btn btn-light rounded-pill px-4 py-2.5 fw-bold text-danger d-inline-flex align-items-center gap-2 shadow" style="color: #7A1418 !important;" data-bs-toggle="modal" data-bs-target="#uploadPaperModal">
              <i class="bi bi-plus-circle-fill fs-6"></i> Upload New Paper
            </button>
          </div>
        </div>
      </div>

      <!-- Quick Metrics Counter Cards (Click to Filter by Type) -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <a href="index.php?page=admin_papers&semester=<?= $semFilter ?>&paper_type=past_paper" class="text-decoration-none">
            <div class="card border rounded-4 p-3 bg-white shadow-sm d-flex flex-row align-items-center gap-3 transition-hover" style="<?= ($typeFilter === 'past_paper') ? 'border: 2px solid #7A1418 !important; background: #fff5f5 !important;' : '' ?>">
              <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center text-white flex-shrink-0" style="background: #7A1418; width: 44px; height: 44px;">
                <i class="bi bi-archive-fill fs-5"></i>
              </div>
              <div>
                <div class="fs-4 fw-bold text-dark lh-1 mb-1"><?= $totalPastPapers ?></div>
                <div class="text-muted small fw-medium">Past Papers <i class="bi bi-arrow-right-short text-danger"></i></div>
              </div>
            </div>
          </a>
        </div>

        <div class="col-6 col-md-3">
          <a href="index.php?page=admin_papers&semester=<?= $semFilter ?>&paper_type=model_paper" class="text-decoration-none">
            <div class="card border rounded-4 p-3 bg-white shadow-sm d-flex flex-row align-items-center gap-3 transition-hover" style="<?= ($typeFilter === 'model_paper') ? 'border: 2px solid #1967d2 !important; background: #f0f7ff !important;' : '' ?>">
              <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center text-white flex-shrink-0" style="background: #1967d2; width: 44px; height: 44px;">
                <i class="bi bi-file-earmark-check-fill fs-5"></i>
              </div>
              <div>
                <div class="fs-4 fw-bold text-dark lh-1 mb-1"><?= $totalModelPapers ?></div>
                <div class="text-muted small fw-medium">Model Papers <i class="bi bi-arrow-right-short text-primary"></i></div>
              </div>
            </div>
          </a>
        </div>

        <div class="col-6 col-md-3">
          <a href="index.php?page=admin_papers&semester=<?= $semFilter ?>&paper_type=marking_scheme" class="text-decoration-none">
            <div class="card border rounded-4 p-3 bg-white shadow-sm d-flex flex-row align-items-center gap-3 transition-hover" style="<?= ($typeFilter === 'marking_scheme') ? 'border: 2px solid #0f766e !important; background: #f0fdfa !important;' : '' ?>">
              <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center text-white flex-shrink-0" style="background: #0f766e; width: 44px; height: 44px;">
                <i class="bi bi-check-circle-fill fs-5"></i>
              </div>
              <div>
                <div class="fs-4 fw-bold text-dark lh-1 mb-1"><?= $totalMarkingSchemes ?></div>
                <div class="text-muted small fw-medium">Marking Schemes <i class="bi bi-arrow-right-short text-success"></i></div>
              </div>
            </div>
          </a>
        </div>

        <div class="col-6 col-md-3">
          <a href="index.php?page=admin_papers&semester=<?= $semFilter ?>" class="text-decoration-none">
            <div class="card border rounded-4 p-3 bg-white shadow-sm d-flex flex-row align-items-center gap-3 transition-hover" style="<?= empty($typeFilter) ? 'border: 2px solid #475569 !important;' : '' ?>">
              <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center text-white flex-shrink-0" style="background: #475569; width: 44px; height: 44px;">
                <i class="bi bi-collection-fill fs-5"></i>
              </div>
              <div>
                <div class="fs-4 fw-bold text-dark lh-1 mb-1"><?= $totalPapers ?></div>
                <div class="text-muted small fw-medium">All Exam Files <i class="bi bi-arrow-right-short text-secondary"></i></div>
              </div>
            </div>
          </a>
        </div>
      </div>

      <!-- Comprehensive Exam Bank Filter Hub (Spacious & Clean Layout) -->
      <div class="card border rounded-4 bg-white shadow-sm p-3 p-md-3.5 mb-4">
        <!-- Row 1: Semester Tabs & Reset Action -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pb-3 border-bottom">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="fw-bold text-dark text-uppercase me-1 d-none d-sm-inline" style="font-size: 0.78rem; letter-spacing: 0.5px;">
              <i class="bi bi-layers-fill text-danger me-1"></i> Semester:
            </span>
            <a href="index.php?page=admin_papers&semester=0&paper_type=<?= urlencode($typeFilter) ?>&module_code=<?= urlencode($modFilter) ?>" 
               class="btn rounded-pill px-3.5 py-2 fw-semibold <?= ($semFilter === 0) ? 'text-white shadow-sm' : 'btn-light border text-secondary' ?>" 
               style="<?= ($semFilter === 0) ? 'background: #7A1418; border-color: #7A1418;' : '' ?> font-size: 0.88rem;">
              <i class="bi bi-collection-fill me-1.5"></i> All Semesters (<?= $totalPapers ?>)
            </a>
            <a href="index.php?page=admin_papers&semester=1&paper_type=<?= urlencode($typeFilter) ?>&module_code=<?= urlencode($modFilter) ?>" 
               class="btn rounded-pill px-3.5 py-2 fw-semibold <?= ($semFilter === 1) ? 'text-white shadow-sm' : 'btn-light border text-secondary' ?>" 
               style="<?= ($semFilter === 1) ? 'background: #7A1418; border-color: #7A1418;' : '' ?> font-size: 0.88rem;">
              <i class="bi bi-book-half me-1.5"></i> Semester 1 (<?= $sem1Count ?>)
            </a>
            <a href="index.php?page=admin_papers&semester=2&paper_type=<?= urlencode($typeFilter) ?>&module_code=<?= urlencode($modFilter) ?>" 
               class="btn rounded-pill px-3.5 py-2 fw-semibold <?= ($semFilter === 2) ? 'text-white shadow-sm' : 'btn-light border text-secondary' ?>" 
               style="<?= ($semFilter === 2) ? 'background: #7A1418; border-color: #7A1418;' : '' ?> font-size: 0.88rem;">
              <i class="bi bi-journal-bookmark-fill me-1.5"></i> Semester 2 (<?= $sem2Count ?>)
            </a>
          </div>

          <?php if (!empty($typeFilter) || !empty($modFilter) || !empty($searchQuery) || $semFilter > 0): ?>
            <div>
              <a href="index.php?page=admin_papers" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs" style="font-size: 0.8rem;">
                <i class="bi bi-arrow-counterclockwise"></i> Reset All Filters
              </a>
            </div>
          <?php endif; ?>
        </div>

        <!-- Row 2: Search, Category & Module Controls (Spacious, Large, Never Cramped) -->
        <form action="index.php" method="GET" class="row g-2.5 align-items-center pt-3">
          <input type="hidden" name="page" value="admin_papers">
          <input type="hidden" name="semester" value="<?= $semFilter ?>">

          <!-- Category Dropdown -->
          <div class="col-12 col-sm-6 col-lg-3">
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0 text-muted ps-3"><i class="bi bi-funnel-fill text-danger"></i></span>
              <select name="paper_type" class="form-select border-start-0 py-2 fw-medium" style="font-size: 0.9rem;" onchange="this.form.submit()">
                <option value="">All Categories (All Types)</option>
                <option value="past_paper" <?= ($typeFilter === 'past_paper') ? 'selected' : '' ?>>📕 Past Papers (<?= $totalPastPapers ?>)</option>
                <option value="model_paper" <?= ($typeFilter === 'model_paper') ? 'selected' : '' ?>>📘 Model Papers (<?= $totalModelPapers ?>)</option>
                <option value="marking_scheme" <?= ($typeFilter === 'marking_scheme') ? 'selected' : '' ?>>📗 Marking Schemes (<?= $totalMarkingSchemes ?>)</option>
              </select>
            </div>
          </div>

          <!-- Module Dropdown -->
          <div class="col-12 col-sm-6 col-lg-4">
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0 text-muted ps-3"><i class="bi bi-journal-text text-primary"></i></span>
              <select name="module_code" class="form-select border-start-0 py-2 fw-medium" style="font-size: 0.9rem;" onchange="this.form.submit()">
                <option value="">All Curriculum Modules</option>
                <?php foreach ($curriculumModules as $m): ?>
                  <option value="<?= e($m['module_code']) ?>" <?= ($modFilter === $m['module_code']) ? 'selected' : '' ?>>
                    <?= e($m['module_code']) ?> &bull; <?= e($m['module_title']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Search Input Box -->
          <div class="col-12 col-sm-8 col-lg-3 flex-grow-1">
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0 text-muted ps-3"><i class="bi bi-search"></i></span>
              <input type="text" name="search" class="form-control border-start-0 py-2" placeholder="Search by paper title..." value="<?= e($searchQuery) ?>" style="font-size: 0.9rem;">
            </div>
          </div>

          <!-- Filter Submit Button -->
          <div class="col-12 col-sm-4 col-lg-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-bold w-100 shadow-sm d-flex align-items-center justify-content-center gap-1.5" style="background: #7A1418; border-color: #7A1418; font-size: 0.92rem;">
              <i class="bi bi-funnel"></i> Filter
            </button>
            <?php if (!empty($typeFilter) || !empty($modFilter) || !empty($searchQuery)): ?>
              <a href="index.php?page=admin_papers&semester=<?= $semFilter ?>" class="btn btn-light border rounded-3 px-3 py-2 text-secondary d-flex align-items-center justify-content-center shadow-xs" title="Clear Search/Module Filters">
                <i class="bi bi-x-lg"></i>
              </a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <!-- Google Classroom Style Exam Papers Grid -->
      <div class="row g-4 mb-4">
        <?php if (empty($papers)): ?>
          <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted bg-white">
              <div class="rounded-circle p-3 bg-light mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                <i class="bi bi-file-earmark-x fs-2 text-secondary"></i>
              </div>
              <h5 class="fw-bold text-dark">No examination papers found</h5>
              <p class="small text-muted mb-3">No past or model papers have been uploaded matching your search.</p>
              <div>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 py-2 fw-bold" style="background: #7A1418; border-color: #7A1418;" data-bs-toggle="modal" data-bs-target="#uploadPaperModal">
                  <i class="bi bi-plus-circle me-1"></i> Upload First Exam Paper
                </button>
              </div>
            </div>
          </div>
        <?php else: ?>
          <?php foreach ($papers as $paper): 
            $theme = getPaperTheme($paper, $paperPalettes);
            $paperId = (int)$paper['id'];
            $isPast = ($paper['paper_type'] === 'past_paper');
            $isModel = ($paper['paper_type'] === 'model_paper');
            $isScheme = ($paper['paper_type'] === 'marking_scheme');

            if ($isPast) {
                $typeBadgeLabel = 'PAST PAPER';
                $typeFullTitle = 'Official Past Paper';
                $typeBadgeStyle = 'background: #dc2626 !important; color: #ffffff !important; border: 1px solid #b91c1c;';
                $avatarBg = '#991b1b';
                $avatarIcon = 'bi-archive-fill';
                $bodyBadgeBg = '#fef2f2';
                $bodyBadgeColor = '#991b1b';
                $bodyBadgeBorder = '#fecaca';
                $cardBorderAccent = 'border-top: 3px solid #dc2626;';
            } elseif ($isModel) {
                $typeBadgeLabel = 'MODEL PAPER';
                $typeFullTitle = 'Practice Model Paper';
                $typeBadgeStyle = 'background: #2563eb !important; color: #ffffff !important; border: 1px solid #1d4ed8;';
                $avatarBg = '#1d4ed8';
                $avatarIcon = 'bi-file-earmark-check-fill';
                $bodyBadgeBg = '#eff6ff';
                $bodyBadgeColor = '#1e40af';
                $bodyBadgeBorder = '#bfdbfe';
                $cardBorderAccent = 'border-top: 3px solid #2563eb;';
            } else {
                $typeBadgeLabel = 'MARKING SCHEME';
                $typeFullTitle = 'Official Marking Scheme';
                $typeBadgeStyle = 'background: #059669 !important; color: #ffffff !important; border: 1px solid #047857;';
                $avatarBg = '#0f766e';
                $avatarIcon = 'bi-check2-all';
                $bodyBadgeBg = '#f0fdf4';
                $bodyBadgeColor = '#166534';
                $bodyBadgeBorder = '#bbf7d0';
                $cardBorderAccent = 'border-top: 3px solid #059669;';
            }
          ?>
            <div class="col-12 col-md-6 col-xl-4">
              <div class="card border-0 rounded-4 shadow-sm h-100 bg-white overflow-hidden g-classroom-card transition-hover position-relative d-flex flex-column" style="min-height: 295px; <?= $cardBorderAccent ?>">
                
                <!-- Google Classroom Style Top Banner Header -->
                <div class="position-relative text-white overflow-hidden d-flex flex-column justify-content-between" style="background: <?= $theme['bg'] ?>; min-height: 140px; padding: 20px 22px 18px 22px; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                  
                  <div class="position-relative" style="z-index: 2; max-width: 84%;">
                    <div class="d-flex flex-wrap align-items-center gap-1.5 mb-1.5">
                      <span class="badge rounded-pill font-monospace px-2.5 py-1 shadow-xs" style="background: rgba(255, 255, 255, 0.22) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.35); font-size: 0.68rem; letter-spacing: 0.3px;">
                        <?= e($paper['module_code']) ?> &bull; Sem <?= $paper['semester'] ?>
                      </span>
                      <span class="badge rounded-pill px-2.5 py-1 fw-bold shadow-xs" style="<?= $typeBadgeStyle ?> font-size: 0.68rem; letter-spacing: 0.4px;">
                        <i class="bi <?= $avatarIcon ?> me-1"></i> <?= $typeBadgeLabel ?>
                      </span>
                    </div>
                    <h4 class="fw-bold text-white mb-0" style="font-size: 1.12rem; letter-spacing: -0.2px; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 44px;" title="<?= e($paper['subject_title']) ?>">
                      <?= e($paper['subject_title']) ?>
                    </h4>
                  </div>

                  <!-- Banner Bottom Tag -->
                  <div class="position-relative mt-2" style="z-index: 2;">
                    <span class="badge rounded-pill px-2.5 py-1 font-monospace shadow-xs" style="background: rgba(0, 0, 0, 0.25) !important; color: #ffffff !important; font-size: 0.72rem;">
                      <i class="bi bi-calendar-event me-1"></i> Exam Year <?= $paper['exam_year'] ?>
                    </span>
                  </div>

                  <!-- Decorative Top-Right Watermark Icon (Positioned safely away from the bottom avatar) -->
                  <div class="position-absolute p-0 text-white pointer-events-none" style="top: 12px; right: 14px; opacity: 0.14; font-size: 3.4rem; line-height: 0.8; z-index: 1;">
                    <i class="bi <?= $theme['icon'] ?>"></i>
                  </div>
                </div>

                <!-- Floating Round Avatar Badge (Type-Specific Signature) -->
                <div class="position-absolute" style="top: 114px; right: 20px; z-index: 5;">
                  <div class="rounded-circle shadow d-flex align-items-center justify-content-center text-white fw-bold border border-3 border-white" 
                       style="width: 52px; height: 52px; font-size: 1.25rem; background-color: <?= $avatarBg ?>;"
                       title="<?= $typeFullTitle ?>">
                    <i class="bi <?= $avatarIcon ?>"></i>
                  </div>
                </div>

                <!-- Card Middle Body -->
                <div class="card-body d-flex flex-column flex-grow-1 justify-content-between" style="padding: 22px 22px 18px 22px;">
                  <div>
                    <!-- Paper Type Badge (Prominent High-Contrast) -->
                    <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background: <?= $bodyBadgeBg ?>; color: <?= $bodyBadgeColor ?>; border: 1px solid <?= $bodyBadgeBorder ?>; font-size: 0.76rem;">
                      <i class="bi <?= $avatarIcon ?> me-1.5"></i> <?= $typeFullTitle ?>
                    </span>

                    <!-- Title -->
                    <h6 class="fw-bold text-dark mt-2 mb-1 lh-sm" style="font-size: 0.92rem; min-height: 40px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="<?= e($paper['title']) ?>">
                      <?= e($paper['title']) ?>
                    </h6>

                    <!-- Description -->
                    <p class="text-muted small mb-0" style="font-size: 0.77rem; min-height: 34px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                      <?= e($paper['description'] ?: 'Official NVTI Baddegama examination paper.') ?>
                    </p>
                  </div>

                  <!-- Card Footer: Stats & Separated Action Buttons (Clean & Always Visible) -->
                  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2" style="margin-top: 18px; padding-top: 14px; border-top: 1px solid #edf2f7;">
                    <div class="d-flex align-items-center">
                      <span class="badge rounded-pill px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 font-monospace" style="background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; font-size: 0.73rem;">
                        <i class="bi bi-file-earmark-pdf-fill text-danger fs-6"></i>
                        <span><?= round(($paper['file_size_bytes'] ?? 1500000) / (1024 * 1024), 1) ?> MB</span>
                      </span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex align-items-center gap-1.5 flex-wrap" style="position: relative; z-index: 6;">
                      <!-- View Button -->
                      <button type="button" onclick="previewPaper(<?= $paperId ?>, <?= htmlspecialchars(json_encode($paper['title']), ENT_QUOTES, 'UTF-8') ?>, 'index.php?page=view_paper&id=<?= $paperId ?>')" class="btn btn-sm rounded-pill px-2.5 py-1 fw-bold text-white shadow-xs d-inline-flex align-items-center gap-1" style="font-size: 0.76rem; background: #7A1418; border: 1px solid #7A1418;">
                        <i class="bi bi-eye-fill"></i> View
                      </button>

                      <!-- Download Button -->
                      <a href="index.php?page=download_paper&id=<?= $paperId ?>" class="btn btn-sm btn-light border text-secondary rounded-circle d-inline-flex align-items-center justify-content-center shadow-xs" title="Download Paper File" style="width: 32px; height: 32px;">
                        <i class="bi bi-download" style="font-size: 0.82rem;"></i>
                      </a>

                      <!-- Edit Button -->
                      <button type="button"
                              data-paper="<?= htmlspecialchars(json_encode($paper), ENT_QUOTES, 'UTF-8') ?>"
                              onclick="openEditPaperModal(this)"
                              class="btn btn-sm bg-primary-subtle border border-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center shadow-xs"
                              title="Edit &amp; Change Paper"
                              style="width: 32px; height: 32px;">
                        <i class="bi bi-pencil-fill" style="font-size: 0.82rem;"></i>
                      </button>

                      <!-- Delete Button -->
                      <button type="button"
                              onclick="confirmDeletePaper(<?= $paperId ?>, <?= htmlspecialchars(json_encode($paper['title']), ENT_QUOTES, 'UTF-8') ?>, <?= (int)$paper['semester'] ?>)"
                              class="btn btn-sm bg-danger-subtle border border-danger-subtle text-danger rounded-circle d-inline-flex align-items-center justify-content-center shadow-xs hover-del-btn"
                              title="Delete Paper"
                              style="width: 32px; height: 32px;">
                        <i class="bi bi-trash3-fill" style="font-size: 0.82rem;"></i>
                      </button>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<!-- ================= MODAL 1: UPLOAD NEW EXAM PAPER ================= -->
<div class="modal fade" id="uploadPaperModal" tabindex="-1" aria-labelledby="uploadPaperModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 rounded-4 shadow">
      <form action="index.php?page=admin_upload_paper" method="POST" enctype="multipart/form-data">
        <?= Csrf::field() ?>

        <div class="modal-header border-bottom py-3 px-4 bg-light">
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle p-2 text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #7A1418;">
              <i class="bi bi-cloud-arrow-up-fill fs-5"></i>
            </div>
            <div>
              <h6 class="modal-title fw-bold text-dark mb-0" id="uploadPaperModalLabel">Upload Examination / Model Paper</h6>
              <small class="text-muted">Add a past paper, model question paper, or marking scheme to the exam bank</small>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <!-- Row 1: Semester, Paper Type, Year -->
          <div class="row g-3 mb-3">
            <div class="col-12 col-md-4">
              <label class="form-label fw-bold text-dark small mb-1">Semester <span class="text-danger">*</span></label>
              <select name="semester" id="uploadSemester" class="form-select" required onchange="filterModuleDropdownBySemester()">
                <option value="1" <?= ($semFilter === 1 || $semFilter === 0) ? 'selected' : '' ?>>Semester 1</option>
                <option value="2" <?= ($semFilter === 2) ? 'selected' : '' ?>>Semester 2</option>
              </select>
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label fw-bold text-dark small mb-1">Paper Type <span class="text-danger">*</span></label>
              <select name="paper_type" class="form-select" required>
                <option value="past_paper">Past Paper (Final Exam)</option>
                <option value="model_paper">Model / Practice Paper</option>
                <option value="marking_scheme">Marking Scheme / Answers</option>
              </select>
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label fw-bold text-dark small mb-1">Exam Year <span class="text-danger">*</span></label>
              <input type="number" name="exam_year" class="form-control" value="<?= date('Y') ?>" min="2018" max="2035" required>
            </div>
          </div>

          <!-- Row 2: NVTI Curriculum Module Selection -->
          <div class="mb-3">
            <label class="form-label fw-bold text-dark small mb-1">Curriculum Module / Subject <span class="text-danger">*</span></label>
            <select name="module_select" id="uploadModuleSelect" class="form-select" required onchange="syncModuleDetails()">
              <option value="">-- Choose Module from NVTI Curriculum --</option>
              <?php foreach ($curriculumModules as $m): ?>
                <option value="<?= e($m['module_code']) ?>" data-title="<?= e($m['module_title']) ?>" data-sem="<?= (int)$m['semester'] ?>">
                  [Sem <?= (int)$m['semester'] ?>] <?= e($m['module_code']) ?> &bull; <?= e($m['module_title']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <input type="hidden" name="module_code" id="uploadModuleCode" value="">
            <input type="hidden" name="subject_title" id="uploadSubjectTitle" value="">
          </div>

          <!-- Row 3: Paper Title -->
          <div class="mb-3">
            <label class="form-label fw-bold text-dark small mb-1">Paper Title <span class="text-danger">*</span></label>
            <input type="text" name="title" id="uploadPaperTitle" class="form-control" placeholder="e.g. 2025 Semester 1 Final Exam - Database Design" required>
          </div>

          <!-- Row 4: Description -->
          <div class="mb-3">
            <label class="form-label fw-bold text-dark small mb-1">Instructions / Description (Optional)</label>
            <textarea name="description" class="form-control" rows="2" placeholder="e.g. Official paper covering 3 hours with Section A &amp; B questions."></textarea>
          </div>

          <!-- Row 5: File Upload (PDF, PNG, JPG) -->
          <div class="mb-2">
            <label class="form-label fw-bold text-dark small mb-1">Paper Document File (PDF or Image) <span class="text-danger">*</span></label>
            <input type="file" name="paper_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" required>
            <div class="form-text small">Accepted formats: PDF, JPG, PNG, DOCX (Max: 25 MB). High quality PDF recommended.</div>
          </div>
        </div>

        <div class="modal-footer bg-light border-top px-4 py-2.5">
          <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm" style="background: #7A1418; border-color: #7A1418;">
            <i class="bi bi-upload me-1"></i> Upload Paper
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= MODAL 2: IN-LINE PAPER PREVIEW ================= -->
<div class="modal fade" id="paperViewerModal" tabindex="-1" aria-labelledby="modalPaperTitle" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header border-bottom py-2.5 px-4 bg-white">
        <div class="d-flex align-items-center gap-2 min-w-0">
          <div class="rounded-circle p-1.5 bg-danger-subtle text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
            <i class="bi bi-file-earmark-pdf-fill fs-6"></i>
          </div>
          <div class="min-w-0">
            <h6 class="modal-title fw-bold text-dark mb-0 text-truncate" id="modalPaperTitle">Examination Paper Preview</h6>
            <small class="text-muted">In-browser Secure Viewer</small>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <a id="modalOpenExternalBtn" href="#" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-medium">
            <i class="bi bi-box-arrow-up-right me-1"></i> Open in New Tab
          </a>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      <div class="modal-body p-0" style="height: 75vh; background: #525659;">
        <iframe id="paperPreviewFrame" src="" style="width: 100%; height: 100%; border: none;"></iframe>
      </div>
    </div>
  </div>
</div>

<!-- ================= MODAL 3: CONFIRM DELETE PAPER ================= -->
<div class="modal fade" id="deletePaperModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-body p-4 text-center">
        <div class="rounded-circle p-3 bg-danger-subtle text-danger mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
          <i class="bi bi-trash3-fill fs-4"></i>
        </div>
        <h6 class="fw-bold text-dark mb-1">Delete Exam Paper?</h6>
        <p class="text-muted small mb-3">
          Are you sure you want to delete <span id="delPaperTitle" class="fw-bold text-dark"></span>? This action cannot be undone.
        </p>
        <form action="index.php?page=admin_delete_paper" method="POST">
          <?= Csrf::field() ?>
          <input type="hidden" name="id" id="delPaperId" value="">
          <input type="hidden" name="semester" id="delPaperSemester" value="">
          <div class="d-flex gap-2 justify-content-center">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold">Yes, Delete</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ================= MODAL 4: EDIT EXAM PAPER ================= -->
<div class="modal fade" id="editPaperModal" tabindex="-1" aria-labelledby="editPaperModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 rounded-4 shadow">
      <form action="index.php?page=admin_update_paper" method="POST" enctype="multipart/form-data">
        <?= Csrf::field() ?>
        <input type="hidden" name="id" id="editPaperId" value="">

        <div class="modal-header border-bottom py-3 px-4 bg-light">
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle p-2 text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #1967d2;">
              <i class="bi bi-pencil-square fs-5"></i>
            </div>
            <div>
              <h6 class="modal-title fw-bold text-dark mb-0" id="editPaperModalLabel">Edit Examination / Model Paper</h6>
              <small class="text-muted">Update paper metadata, semester, or replace the attached document</small>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <!-- Row 1: Semester, Paper Type, Year -->
          <div class="row g-3 mb-3">
            <div class="col-12 col-md-4">
              <label class="form-label fw-bold text-dark small mb-1">Semester <span class="text-danger">*</span></label>
              <select name="semester" id="editSemester" class="form-select" required onchange="filterEditModuleDropdownBySemester()">
                <option value="1">Semester 1</option>
                <option value="2">Semester 2</option>
              </select>
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label fw-bold text-dark small mb-1">Paper Type <span class="text-danger">*</span></label>
              <select name="paper_type" id="editPaperType" class="form-select" required>
                <option value="past_paper">📕 Past Paper (Final Exam)</option>
                <option value="model_paper">📘 Model / Practice Paper</option>
                <option value="marking_scheme">📗 Marking Scheme / Answers</option>
              </select>
            </div>

            <div class="col-12 col-md-4">
              <label class="form-label fw-bold text-dark small mb-1">Exam Year <span class="text-danger">*</span></label>
              <input type="number" name="exam_year" id="editExamYear" class="form-control" min="2018" max="2035" required>
            </div>
          </div>

          <!-- Row 2: Module Selection + Editable Subject Title -->
          <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
              <label class="form-label fw-bold text-dark small mb-1">Curriculum Module / Subject <span class="text-danger">*</span></label>
              <select name="module_select" id="editModuleSelect" class="form-select" required onchange="syncEditModuleDetails()">
                <option value="">-- Choose Module from NVTI Curriculum --</option>
                <?php foreach ($curriculumModules as $m): ?>
                  <option value="<?= e($m['module_code']) ?>" data-title="<?= e($m['module_title']) ?>" data-sem="<?= (int)$m['semester'] ?>">
                    [Sem <?= (int)$m['semester'] ?>] <?= e($m['module_code']) ?> &bull; <?= e($m['module_title']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <input type="hidden" name="module_code" id="editModuleCode" value="">
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label fw-bold text-dark small mb-1">Subject / Module Display Title <span class="text-danger">*</span></label>
              <input type="text" name="subject_title" id="editSubjectTitle" class="form-control" placeholder="e.g. Database Management Systems" required>
            </div>
          </div>

          <!-- Row 3: Title -->
          <div class="mb-3">
            <label class="form-label fw-bold text-dark small mb-1">Paper Title <span class="text-danger">*</span></label>
            <input type="text" name="title" id="editPaperTitle" class="form-control" required>
          </div>

          <!-- Row 4: Description -->
          <div class="mb-3">
            <label class="form-label fw-bold text-dark small mb-1">Notes / Description</label>
            <textarea name="description" id="editDescription" class="form-control" rows="2" placeholder="e.g. Model answers included, prepared by NVTI lecturers..."></textarea>
          </div>

          <!-- Row 5: Replace File (Optional) -->
          <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
              <label class="form-label fw-bold text-dark small mb-0">
                <i class="bi bi-file-earmark-arrow-up text-primary me-1"></i> Replace Document File (Optional)
              </label>
              <a href="#" id="editPreviewCurrentFileLink" target="_blank" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-0.5 small">
                <i class="bi bi-eye me-1"></i>View Current File
              </a>
            </div>
            <div class="text-muted small mb-2">
              Current file: <strong id="editCurrentFileName" class="text-dark">Attached document</strong>
            </div>
            <input type="file" name="paper_file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
            <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Leave empty if you do NOT want to change the existing file.</small>
          </div>
        </div>

        <div class="modal-footer bg-light px-4 py-3 border-top">
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" style="background: #1967d2; border-color: #1967d2;">
            <i class="bi bi-check2-circle me-1"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openEditPaperModal(triggerEl) {
  let paper = triggerEl;
  if (triggerEl && triggerEl.getAttribute) {
    paper = JSON.parse(triggerEl.getAttribute('data-paper') || '{}');
  }

  document.getElementById('editPaperId').value = paper.id || '';
  document.getElementById('editPaperTitle').value = paper.title || '';
  document.getElementById('editSemester').value = paper.semester || 1;
  document.getElementById('editPaperType').value = paper.paper_type || 'past_paper';
  document.getElementById('editExamYear').value = paper.exam_year || 2026;
  document.getElementById('editDescription').value = paper.description || '';
  document.getElementById('editModuleCode').value = paper.module_code || '';
  document.getElementById('editSubjectTitle').value = paper.subject_title || '';
  document.getElementById('editCurrentFileName').textContent = paper.original_filename || paper.file_path || 'Attached document';
  document.getElementById('editPreviewCurrentFileLink').href = 'index.php?page=view_paper&id=' + encodeURIComponent(paper.id || '');

  const select = document.getElementById('editModuleSelect');
  if (select) {
    const modCode = paper.module_code || '';
    let hasOption = Array.from(select.options).some(opt => opt.value === modCode);
    if (modCode && !hasOption) {
      const customOpt = document.createElement('option');
      customOpt.value = modCode;
      customOpt.textContent = `[Sem ${paper.semester || 1}] ${modCode} • ${paper.subject_title || modCode}`;
      customOpt.setAttribute('data-title', paper.subject_title || modCode);
      customOpt.setAttribute('data-sem', String(paper.semester || 1));
      select.appendChild(customOpt);
    }
    select.value = modCode;
  }
  filterEditModuleDropdownBySemester();

  const modal = new bootstrap.Modal(document.getElementById('editPaperModal'));
  modal.show();
}

function syncEditModuleDetails() {
  const select = document.getElementById('editModuleSelect');
  const opt = select.options[select.selectedIndex];
  if (!opt || !opt.value) return;

  const code = opt.value;
  const title = opt.getAttribute('data-title');
  const sem = opt.getAttribute('data-sem');

  document.getElementById('editModuleCode').value = code;
  document.getElementById('editSubjectTitle').value = title;

  if (sem) {
    document.getElementById('editSemester').value = sem;
  }
}

function filterEditModuleDropdownBySemester() {
  const semVal = document.getElementById('editSemester').value;
  const select = document.getElementById('editModuleSelect');
  const currentVal = select.value;
  for (let i = 1; i < select.options.length; i++) {
    const opt = select.options[i];
    const optSem = opt.getAttribute('data-sem');
    const keepVisible = (!semVal || optSem === semVal || opt.value === currentVal);
    opt.hidden = !keepVisible;
    opt.disabled = !keepVisible;
  }
}
function syncModuleDetails() {
  const select = document.getElementById('uploadModuleSelect');
  const opt = select.options[select.selectedIndex];
  if (!opt || !opt.value) return;

  const code = opt.value;
  const title = opt.getAttribute('data-title');
  const sem = opt.getAttribute('data-sem');

  document.getElementById('uploadModuleCode').value = code;
  document.getElementById('uploadSubjectTitle').value = title;

  if (sem) {
    document.getElementById('uploadSemester').value = sem;
  }

  // Auto suggest title if empty
  const titleInput = document.getElementById('uploadPaperTitle');
  if (!titleInput.value) {
    const year = new Date().getFullYear();
    titleInput.value = `${year} Semester ${sem || 1} Final Exam - ${title}`;
  }
}

function filterModuleDropdownBySemester() {
  const semVal = document.getElementById('uploadSemester').value;
  const select = document.getElementById('uploadModuleSelect');
  for (let i = 1; i < select.options.length; i++) {
    const opt = select.options[i];
    const optSem = opt.getAttribute('data-sem');
    if (!semVal || optSem === semVal) {
      opt.style.display = '';
    } else {
      opt.style.display = 'none';
    }
  }
}

function previewPaper(id, title, url) {
  document.getElementById('modalPaperTitle').textContent = title;
  document.getElementById('modalOpenExternalBtn').href = url;
  document.getElementById('paperPreviewFrame').src = url;
  const modal = new bootstrap.Modal(document.getElementById('paperViewerModal'));
  modal.show();
}

function confirmDeletePaper(id, title, semester) {
  document.getElementById('delPaperId').value = id;
  document.getElementById('delPaperTitle').textContent = title;
  document.getElementById('delPaperSemester').value = semester;
  const modal = new bootstrap.Modal(document.getElementById('deletePaperModal'));
  modal.show();
}

document.getElementById('paperViewerModal')?.addEventListener('hidden.bs.modal', function () {
  document.getElementById('paperPreviewFrame').src = '';
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
