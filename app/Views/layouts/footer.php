<footer class="app-footer mt-auto py-3 bg-white border-top">
  <div class="container-fluid px-3 px-md-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 text-center text-md-start small text-muted">
      <div>
        <strong><?= APP_NAME ?></strong> &copy; <?= date('Y') ?> &bull; NVTI Baddegama ICT NVQ Level 5 Diploma Project
      </div>
      <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 gap-sm-3">
        <span class="badge bg-success-subtle text-success border border-success-subtle">
          <i class="bi bi-shield-check me-1"></i> CSRF &amp; SQL Injection Protected
        </span>
        <span class="text-muted">Server Time: <?= date('Y-m-d H:i') ?></span>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom App JS (with cache busting) -->
<script src="assets/js/app.js?v=<?= file_exists(__DIR__ . '/../../../assets/js/app.js') ? filemtime(__DIR__ . '/../../../assets/js/app.js') : time() ?>"></script>
<script>
  if (typeof lucide !== 'undefined' && lucide.createIcons) {
    lucide.createIcons();
  }
</script>
</body>
</html>
