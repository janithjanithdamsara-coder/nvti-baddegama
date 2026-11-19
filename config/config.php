<?php
/**
 * Application Configuration
 * NVTI Baddegama National Diploma Portal
 */

// Load environment variables from .env if present
$envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envPath)) {
    $envLines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (strpos($line, '=') !== false) {
            [$k, $v] = explode('=', $line, 2);
            $k = trim($k);
            $v = trim($v, " \t\n\r\0\x0B\"'");
            if (!getenv($k)) {
                putenv("{$k}={$v}");
                $_ENV[$k] = $v;
            }
        }
    }
}

// Error Reporting (Hide raw error output in Linux/production, but always log to server logs)
error_reporting(E_ALL);
ini_set('log_errors', '1');
if (PHP_OS_FAMILY === 'Linux' && getenv('APP_DEBUG') !== 'true') {
    ini_set('display_errors', '0');
} else {
    ini_set('display_errors', '1');
}

// Global Security Response Headers
if (!headers_sent()) {
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
    
    // Content-Security-Policy (CSP)
    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://code.jquery.com https://unpkg.com https://www.youtube.com https://s.ytimg.com; " .
           "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://unpkg.com; " .
           "font-src 'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com; " .
           "img-src 'self' data: https: blob:; " .
           "media-src 'self' data: blob: https:; " .
           "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com; " .
           "connect-src 'self' https:;";
    header("Content-Security-Policy: " . $csp);

    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
    }
}

// App Details
define('APP_NAME', 'NVTI Baddegama National Diploma');
define('APP_SUBTITLE', 'National Diploma in ICT (NVQ Level 5)');
define('APP_VERSION', '1.0.0');

// Paths
define('BASE_PATH', dirname(__DIR__));
define('STORAGE_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'storage');
define('UPLOAD_PATH', STORAGE_PATH . DIRECTORY_SEPARATOR . 'uploads');

// Subdirectories for uploads
define('MATERIAL_UPLOAD_PATH', UPLOAD_PATH . DIRECTORY_SEPARATOR . 'materials');
define('ASSIGNMENT_UPLOAD_PATH', UPLOAD_PATH . DIRECTORY_SEPARATOR . 'assignments');
define('SUBMISSION_UPLOAD_PATH', UPLOAD_PATH . DIRECTORY_SEPARATOR . 'submissions');
define('PROFILE_UPLOAD_PATH', UPLOAD_PATH . DIRECTORY_SEPARATOR . 'profiles');
define('DOCUMENT_UPLOAD_PATH', UPLOAD_PATH . DIRECTORY_SEPARATOR . 'documents');

// Detect Base URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = str_replace('\\', '/', $scriptDir);
$baseUrl = rtrim($protocol . $host . $scriptDir, '/') . '/';
define('BASE_URL', $baseUrl);

// Database Credentials (Auto-detect Local vs Linux VPS)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'educore_lms');
define('DB_USER', getenv('DB_USER') ?: (PHP_OS_FAMILY === 'Linux' ? 'educore_user' : 'root'));
define('DB_PASS', getenv('DB_PASS') ?: (PHP_OS_FAMILY === 'Linux' ? 'EducoreNVTI@2026' : ''));
define('DB_CHARSET', 'utf8mb4');

// Security Settings
define('MAX_UPLOAD_SIZE', 25 * 1024 * 1024); // 25 MB
define('ALLOWED_FILE_EXTENSIONS', ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'rar', 'jpg', 'jpeg', 'png', 'txt', 'sql']);

// Gmail / SMTP Mail Configuration
define('MAIL_ENABLED', true);
define('MAIL_DRIVER', getenv('MAIL_DRIVER') ?: 'smtp'); 
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: 465)); // 465 (SSL)
define('SMTP_ENCRYPTION', getenv('SMTP_ENCRYPTION') ?: 'ssl'); // 'ssl'
define('SMTP_USER', getenv('SMTP_USER') ?: 'sahashrajanith@gmail.com');
define('SMTP_PASS', getenv('SMTP_PASS') ?: 'pvmeelqjvicjbqcf');
define('BREVO_API_KEY', getenv('BREVO_API_KEY') ?: '');
define('MAIL_FROM_EMAIL', getenv('MAIL_FROM_EMAIL') ?: 'sahashrajanith@gmail.com');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'NVTI Baddegama National Diploma');

// NVTI Baddegama Main Website Candidate Application Sync API
define('NVTI_MAIN_SITE_API_URL', getenv('NVTI_MAIN_SITE_API_URL') ?: 'https://nvtibaddegama.site/api/get_applications.php');
define('NVTI_MAIN_SITE_API_KEY', getenv('NVTI_MAIN_SITE_API_KEY') ?: 'NVTI_BADDEGAMA_SEC_KEY_2026_DIPLOMA_XYZ');

// Session Configuration (Strict & Secure with unique project session name)
if (session_status() === PHP_SESSION_NONE) {
    session_name('NVTI_BADDEGAMA_DIPLOMA_SESSION');
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// Session Inactivity / Idle Timeout (2 Hours = 7200 seconds)
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        header("Location: " . BASE_URL . "index.php?page=login&timeout=1");
        exit;
    }
    $_SESSION['last_activity'] = time();
}
