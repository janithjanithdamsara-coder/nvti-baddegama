<?php
/**
 * Admin Access Control & Content Ownership Helper
 * Separates Main Admin (e.g. Nishantha Sir) and Co-Admin (e.g. Gayan Sir)
 * permissions and enforces strict creator-only deletion ownership.
 * NVTI Baddegama National Diploma Portal
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Auth.php';

class AdminAccess {
    private static bool $schemaEnsured = false;
    private static array $levelCache = [];

    /**
     * Ensure admin_level and created_by columns exist (idempotent per request)
     */
    public static function ensureSchema(): void {
        if (self::$schemaEnsured) {
            return;
        }

        try {
            $db = Database::getConnection();

            // 1. users.admin_level
            $col = $db->query("SHOW COLUMNS FROM users LIKE 'admin_level'")->fetch();
            if (!$col) {
                $db->exec("ALTER TABLE users ADD COLUMN admin_level ENUM('main_admin', 'co_admin') NOT NULL DEFAULT 'co_admin' AFTER role");
                // Initialize existing administrators: Primary (#1) & Nishantha Sir (#14 / nishantha@nvti.edu.lk) = main_admin
                $db->exec("UPDATE users SET admin_level = 'main_admin' WHERE role = 'admin' AND (id = 1 OR LOWER(email) = 'nishantha@nvti.edu.lk')");
                $db->exec("UPDATE users SET admin_level = 'co_admin' WHERE role = 'admin' AND id <> 1 AND LOWER(email) <> 'nishantha@nvti.edu.lk'");
            }

            // 2. created_by on materials, exam_papers, assignments
            foreach (['materials', 'exam_papers', 'assignments'] as $tbl) {
                $c = $db->query("SHOW COLUMNS FROM `{$tbl}` LIKE 'created_by'")->fetch();
                if (!$c) {
                    $db->exec("ALTER TABLE `{$tbl}` ADD COLUMN created_by INT NULL");
                }
            }

            self::$schemaEnsured = true;
        } catch (Throwable $e) {
            error_log('AdminAccess::ensureSchema error: ' . $e->getMessage());
        }
    }

    /**
     * Get the admin_level ('main_admin' or 'co_admin') for an admin user
     */
    public static function getAdminLevel(?int $userId = null): string {
        $uid = $userId ?? (int)(Auth::id() ?? 0);
        if ($uid <= 0) {
            return 'co_admin';
        }
        if (isset(self::$levelCache[$uid])) {
            return self::$levelCache[$uid];
        }

        self::ensureSchema();
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT role, email, admin_level FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$uid]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && $row['role'] === 'admin') {
                $lvl = $row['admin_level'] ?? '';
                if (!in_array($lvl, ['main_admin', 'co_admin'], true)) {
                    $lvl = ($uid === 1 || strtolower((string)$row['email']) === 'nishantha@nvti.edu.lk') ? 'main_admin' : 'co_admin';
                }
                self::$levelCache[$uid] = $lvl;
                return $lvl;
            }
        } catch (Throwable $e) {
            // Fallback
        }

        self::$levelCache[$uid] = ($uid === 1) ? 'main_admin' : 'co_admin';
        return self::$levelCache[$uid];
    }

    /**
     * Check whether the logged-in (or given) user is a Main Admin
     */
    public static function isMainAdmin(?int $userId = null): bool {
        if ($userId === null && Auth::role() !== 'admin') {
            return false;
        }
        return self::getAdminLevel($userId) === 'main_admin';
    }

    /**
     * Abort with 403 if current user is an admin who is NOT a Main Admin
     * (Teachers still keep their assigned access where TeacherController is used).
     */
    public static function requireMainAdmin(bool $allowTeachers = false): void {
        Auth::requireLogin();
        $role = Auth::role();

        if ($allowTeachers && $role === 'teacher') {
            return;
        }
        if ($role !== 'admin' || !self::isMainAdmin()) {
            http_response_code(403);
            die("<!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <title>403 Access Restricted | NVTI Baddegama</title>
                <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
                <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css' rel='stylesheet'>
            </head>
            <body class='bg-light d-flex align-items-center justify-content-center' style='min-height:100vh;font-family:sans-serif;'>
                <div class='card border-0 shadow-sm rounded-4 p-5 text-center' style='max-width:460px;'>
                    <div class='rounded-circle bg-danger-subtle text-danger mx-auto mb-3 d-flex align-items-center justify-content-center' style='width:64px;height:64px;'>
                        <i class='bi bi-shield-lock-fill fs-2'></i>
                    </div>
                    <h4 class='fw-bold text-dark mb-2'>Main Administrator Only</h4>
                    <p class='text-muted small mb-4'>This module (Attendance &amp; Candidate Selection) is restricted to the Main Administrator account.</p>
                    <a href='index.php?page=admin_dashboard' class='btn btn-danger rounded-pill px-4 fw-semibold' style='background:#7A1418;border-color:#7A1418;'>Back to Dashboard</a>
                </div>
            </body>
            </html>");
        }
    }

    /**
     * Check whether the current user owns / can delete (or modify) an item.
     * - Items with a recorded created_by can ONLY be deleted by that exact creator.
     * - Legacy items without created_by (NULL) can only be managed by a Main Admin.
     */
    public static function canDelete(?int $createdBy, ?int $fallbackTeacherId = null): bool {
        $uid = (int)(Auth::id() ?? 0);
        if ($uid <= 0) {
            return false;
        }

        if ($createdBy !== null && $createdBy > 0) {
            return $createdBy === $uid;
        }

        // Legacy items uploaded before created_by tracking
        if (Auth::role() === 'admin' && self::isMainAdmin($uid)) {
            return true;
        }
        if ($fallbackTeacherId !== null && $fallbackTeacherId === $uid) {
            return true;
        }
        return false;
    }

    /**
     * Format clean role badge label for UI
     */
    public static function getRoleLabel(?int $userId = null): string {
        return self::isMainAdmin($userId) ? 'Main Administrator' : 'Co-Administrator';
    }
}
