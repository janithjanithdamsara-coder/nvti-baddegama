<?php
/**
 * Header Layout Template
 */
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Helpers/Auth.php';
require_once __DIR__ . '/../../Helpers/Sanitizer.php';
require_once __DIR__ . '/../../Helpers/Csrf.php';
require_once __DIR__ . '/../../Helpers/Flash.php';

$pageTitle = $pageTitle ?? APP_NAME;
$isPublicPage = in_array($_GET['page'] ?? 'home', ['home', 'login', 'register', 'activate', 'forgot_password']);
$bodyClass = (Auth::check() && !$isPublicPage) ? 'portal-body' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> | <?= APP_NAME ?></title>
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Google Fonts Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- Lucide Icons Library -->
  <script src="assets/js/lucide.min.js"></script>
  <!-- Custom Styles (with cache busting) -->
  <link rel="stylesheet" href="assets/css/custom.css?v=<?= file_exists(__DIR__ . '/../../../assets/css/custom.css') ? filemtime(__DIR__ . '/../../../assets/css/custom.css') : time() ?>">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
  </style>
</head>
<body class="<?= $bodyClass ?>">
