<?php
/**
 * Application Configuration
 * EduCore LMS - NVTI Baddegama Diploma
 */

// Error Reporting (adjust for production)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// App Details
define('APP_NAME', 'EduCore LMS');
define('APP_SUBTITLE', 'NVTI Baddegama - ICT Level 5 Diploma');
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
define('ALLOWED_FILE_EXTENSIONS', ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'rar', 'jpg', 'jpeg', 'png', 'txt']);

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
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'NVTI Baddegama - ICT Diploma');

// Session Configuration (Strict & Secure with unique project session name)
if (session_status() === PHP_SESSION_NONE) {
    session_name('EDUCORE_NVTI_LMS_SESSION');
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}
