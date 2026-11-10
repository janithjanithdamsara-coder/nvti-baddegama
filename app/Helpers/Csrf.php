<?php
/**
 * CSRF Protection Helper
 */

class Csrf {
    public static function token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function field(): string {
        $token = self::token();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function verify(?string $token): bool {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function checkOrAbort(): void {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        if (!self::verify($token)) {
            http_response_code(403);
            die("<div style='font-family:sans-serif;padding:30px;background:#fff3f3;color:#d9534f;border:1px solid #f5c6cb;border-radius:6px;max-width:500px;margin:50px auto;'>
                <h3>403 Security Check Failed</h3>
                <p>Invalid or expired CSRF security token. Please go back and try again.</p>
                <a href='index.php'>Return to Dashboard</a>
            </div>");
        }
    }

    /**
     * Validate CSRF token or abort (alias of checkOrAbort)
     */
    public static function validate(): void {
        self::checkOrAbort();
    }

    /**
     * Prevent accidental duplicate POST submissions within a short TTL window.
     * Returns true on first submission; false if an identical submission was already claimed.
     */
    public static function claimSubmission(string $scope, array $parts = [], int $ttlSeconds = 60): bool {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return true;
        }

        $now = time();
        $store = $_SESSION['recent_submissions'] ?? [];
        foreach ($store as $k => $expiresAt) {
            if ((int)$expiresAt < $now) {
                unset($store[$k]);
            }
        }

        $fingerprint = hash('sha256', $scope . '|' . json_encode($parts));
        if (isset($store[$fingerprint]) && (int)$store[$fingerprint] >= $now) {
            $_SESSION['recent_submissions'] = $store;
            return false;
        }

        $store[$fingerprint] = $now + max(1, $ttlSeconds);
        $_SESSION['recent_submissions'] = $store;
        return true;
    }
}
