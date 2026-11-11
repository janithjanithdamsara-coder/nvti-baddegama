<?php
/**
 * Email Settings & Delivery Log Helper
 * Manages runtime email toggle switches and email send history in MySQL.
 * NVTI Baddegama National Diploma Portal
 */

require_once __DIR__ . '/../../config/database.php';

class EmailSettings {
    private static bool $tablesEnsured = false;
    private static ?array $cachedSettings = null;

    /**
     * Default values for email controls
     */
    public static function getDefaults(): array {
        $configEnabled = (!defined('MAIL_ENABLED') || MAIL_ENABLED) ? '1' : '0';
        return [
            'mail_master_enabled'         => $configEnabled,
            'mail_notify_new_material'    => '1',
            'mail_notify_new_assignment'  => '1',
            'mail_notify_student_approval'=> '1',
            'mail_notify_password_reset'  => '1',
            'mail_notify_security_alerts' => '1',
            'mail_action_default_checked' => '1',
        ];
    }

    /**
     * Ensure system_settings and email_logs tables exist (idempotent per request)
     */
    public static function ensureTables(): void {
        if (self::$tablesEnsured) {
            return;
        }

        try {
            $db = Database::getConnection();
            $db->exec("
                CREATE TABLE IF NOT EXISTS `system_settings` (
                    `setting_key` VARCHAR(100) NOT NULL PRIMARY KEY,
                    `setting_value` TEXT NULL,
                    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $db->exec("
                CREATE TABLE IF NOT EXISTS `email_logs` (
                    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `recipient_email` VARCHAR(255) NOT NULL,
                    `recipient_name` VARCHAR(255) NULL,
                    `subject` VARCHAR(255) NOT NULL,
                    `html_body` MEDIUMTEXT NULL,
                    `email_type` VARCHAR(50) NOT NULL DEFAULT 'general',
                    `status` ENUM('sent', 'skipped', 'failed') NOT NULL DEFAULT 'sent',
                    `error_message` TEXT NULL,
                    `sent_by` INT NULL,
                    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_email_logs_created` (`created_at`),
                    INDEX `idx_email_logs_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            self::$tablesEnsured = true;
        } catch (Throwable $e) {
            error_log('EmailSettings::ensureTables error: ' . $e->getMessage());
        }
    }

    /**
     * Load all email settings merged with defaults
     */
    public static function getAll(bool $forceRefresh = false): array {
        if (self::$cachedSettings !== null && !$forceRefresh) {
            return self::$cachedSettings;
        }

        $defaults = self::getDefaults();
        self::ensureTables();

        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'mail_%'");
            $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            if (is_array($rows)) {
                foreach ($rows as $k => $v) {
                    if (array_key_exists($k, $defaults)) {
                        $defaults[$k] = (string)$v;
                    }
                }
            }
        } catch (Throwable $e) {
            error_log('EmailSettings::getAll error: ' . $e->getMessage());
        }

        self::$cachedSettings = $defaults;
        return $defaults;
    }

    /**
     * Get a single setting value
     */
    public static function get(string $key, ?string $default = null): string {
        $all = self::getAll();
        if (array_key_exists($key, $all)) {
            return (string)$all[$key];
        }
        return (string)($default ?? '');
    }

    /**
     * Save multiple settings at once
     */
    public static function setMany(array $pairs): void {
        self::ensureTables();
        $defaults = self::getDefaults();

        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO system_settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");

        foreach ($pairs as $key => $val) {
            if (!array_key_exists($key, $defaults)) {
                continue;
            }
            $stmt->execute([$key, (string)$val]);
        }

        self::$cachedSettings = null;
    }

    /**
     * Check whether the Master Email Switch is ON
     */
    public static function isMasterEnabled(): bool {
        return self::get('mail_master_enabled', '1') === '1';
    }

    /**
     * Map email type/category to its corresponding setting key
     */
    public static function getCategorySettingKey(string $category): ?string {
        return match (strtolower(trim($category))) {
            'material', 'new_material'       => 'mail_notify_new_material',
            'assignment', 'new_assignment'   => 'mail_notify_new_assignment',
            'approval', 'student_approval'   => 'mail_notify_student_approval',
            'password_reset', 'reset'        => 'mail_notify_password_reset',
            'security_alert', 'security'     => 'mail_notify_security_alerts',
            default                          => null,
        };
    }

    /**
     * Check whether both Master Switch and the given category are enabled
     */
    public static function isCategoryEnabled(string $category): bool {
        if (!self::isMasterEnabled()) {
            return false;
        }
        $key = self::getCategorySettingKey($category);
        if ($key === null) {
            return true;
        }
        return self::get($key, '1') === '1';
    }

    /**
     * Determine whether an action-level "Send email notification" checkbox should be checked by default
     */
    public static function shouldDefaultCheckAction(string $category): bool {
        if (!self::isCategoryEnabled($category)) {
            return false;
        }
        return self::get('mail_action_default_checked', '1') === '1';
    }

    /**
     * Record an email send / skip / failure event
     */
    public static function logEmail(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        string $emailType = 'general',
        string $status = 'sent',
        ?string $errorMessage = null,
        ?int $sentBy = null
    ): int {
        self::ensureTables();

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO email_logs (recipient_email, recipient_name, subject, html_body, email_type, status, error_message, sent_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                mb_substr($toEmail, 0, 255),
                $toName !== '' ? mb_substr($toName, 0, 255) : null,
                mb_substr($subject, 0, 255),
                $htmlBody !== '' ? $htmlBody : null,
                mb_substr($emailType ?: 'general', 0, 50),
                in_array($status, ['sent', 'skipped', 'failed'], true) ? $status : 'sent',
                $errorMessage,
                $sentBy
            ]);
            return (int)$db->lastInsertId();
        } catch (Throwable $e) {
            error_log('EmailSettings::logEmail error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Fetch recent email logs with optional status filter
     */
    public static function getRecentLogs(int $limit = 30, string $statusFilter = ''): array {
        self::ensureTables();

        try {
            $db = Database::getConnection();
            $limit = max(1, min(100, $limit));
            $sql = "
                SELECT el.*, u.name AS sender_name
                FROM email_logs el
                LEFT JOIN users u ON el.sent_by = u.id
            ";
            $params = [];
            if (in_array($statusFilter, ['sent', 'skipped', 'failed'], true)) {
                $sql .= " WHERE el.status = ?";
                $params[] = $statusFilter;
            }
            $sql .= " ORDER BY el.id DESC LIMIT " . (int)$limit;

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('EmailSettings::getRecentLogs error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch a single log row by ID
     */
    public static function getLogById(int $id): ?array {
        self::ensureTables();
        if ($id <= 0) {
            return null;
        }
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM email_logs WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Return summary counts for email_logs
     */
    public static function getLogStats(): array {
        self::ensureTables();
        $stats = ['sent' => 0, 'skipped' => 0, 'failed' => 0, 'total' => 0];
        try {
            $db = Database::getConnection();
            $rows = $db->query("SELECT status, COUNT(*) AS cnt FROM email_logs GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
            if (is_array($rows)) {
                foreach (['sent', 'skipped', 'failed'] as $st) {
                    $stats[$st] = (int)($rows[$st] ?? 0);
                    $stats['total'] += $stats[$st];
                }
            }
        } catch (Throwable $e) {
            // ignore
        }
        return $stats;
    }

    /**
     * Clear all email logs
     */
    public static function clearLogs(): void {
        self::ensureTables();
        $db = Database::getConnection();
        $db->exec("DELETE FROM email_logs");
    }
}
