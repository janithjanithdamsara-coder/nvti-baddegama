<?php
/**
 * Authentication & Access Control Helper (RBAC)
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

class Auth {
    public static function check(): bool {
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role'])) {
            return false;
        }

        // Validate permitted roles strictly
        if (!in_array($_SESSION['user_role'], ['admin', 'teacher', 'student'], true)) {
            $_SESSION = [];
            return false;
        }

        return true;
    }

    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }
        return [
            'id'    => $_SESSION['user_id'],
            'name'  => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role'  => $_SESSION['user_role'] ?? '',
            'image' => $_SESSION['user_image'] ?? null,
        ];
    }

    public static function id(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string {
        return $_SESSION['user_role'] ?? null;
    }

    public static function name(): string {
        return $_SESSION['user_name'] ?? 'User';
    }

    public static function login(array $user): void {
        session_regenerate_id(true); // Prevent session fixation
        $_SESSION['user_id']    = (int)$user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['user_image'] = $user['profile_image'] ?? null;
        $_SESSION['logged_in_at'] = time();

        // Audit Log
        self::logAudit($user['id'], 'LOGIN', 'User successfully logged in.');
    }

    public static function logout(): void {
        $uid = $_SESSION['user_id'] ?? null;
        if (!empty($uid)) {
            self::logAudit((int)$uid, 'LOGOUT', 'User logged out.');
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function requireLogin(): void {
        if (!self::check()) {
            header('Location: index.php?page=login');
            exit;
        }
    }

    public static function requireRole(array|string $roles): void {
        self::requireLogin();
        $allowedRoles = is_array($roles) ? $roles : [$roles];
        $currentRole = self::role();

        if (!in_array($currentRole, $allowedRoles, true)) {
            http_response_code(403);
            die("<!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <title>403 Access Denied | NVTI Baddegama National Diploma</title>
                <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
                <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css' rel='stylesheet'>
            </head>
            <body class='bg-light d-flex align-items-center justify-content-center' style='min-height: 100vh;'>
                <div class='card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center' style='max-width: 500px;'>
                    <div class='rounded-circle bg-danger bg-opacity-10 text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-3' style='width: 64px; height: 64px;'>
                        <i class='bi bi-shield-lock-fill fs-2'></i>
                    </div>
                    <h4 class='fw-bold text-dark mb-1'>403 Access Restricted</h4>
                    <p class='text-muted small mb-4'>You do not have permission to access this page.<br>Your active role is <strong>" . htmlspecialchars((string)$currentRole) . "</strong>.</p>
                    <div class='d-grid gap-2'>
                        <a href='index.php?page=dashboard' class='btn btn-primary fw-bold'><i class='bi bi-speedometer2 me-1'></i> Go to My Dashboard</a>
                        <a href='index.php?page=logout' class='btn btn-outline-danger'><i class='bi bi-box-arrow-right me-1'></i> Sign Out / Switch Account</a>
                        <a href='index.php' class='btn btn-light text-secondary btn-sm'>Return to Home</a>
                    </div>
                </div>
            </body>
            </html>");
        }
    }

    public static function logAudit(?int $userId, string $action, string $details = ''): void {
        try {
            $db = Database::getConnection();
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $action, $details, $ip]);
        } catch (Exception $e) {
            error_log("Audit log failed: " . $e->getMessage());
        }
    }

    public static function logLoginAttempt(string $email, bool $success): void {
        try {
            $db = Database::getConnection();
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
            $status = $success ? 'success' : 'failed';
            $stmt = $db->prepare("INSERT INTO login_logs (email, status, ip_address, user_agent) VALUES (?, ?, ?, ?)");
            $stmt->execute([$email, $status, $ip, $agent]);
        } catch (Exception $e) {
            error_log("Login log failed: " . $e->getMessage());
        }
    }
}
