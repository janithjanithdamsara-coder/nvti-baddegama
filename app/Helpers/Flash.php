<?php
/**
 * Flash Notification Helper
 */

class Flash {
    public static function set(string $type, string $message): void {
        $_SESSION['flash'][$type][] = $message;
    }

    public static function success(string $message): void {
        self::set('success', $message);
    }

    public static function error(string $message): void {
        self::set('danger', $message);
    }

    public static function warning(string $message): void {
        self::set('warning', $message);
    }

    public static function info(string $message): void {
        self::set('info', $message);
    }

    public static function render(): string {
        if (empty($_SESSION['flash'])) {
            return '';
        }

        $html = '';
        foreach ($_SESSION['flash'] as $type => $messages) {
            foreach ($messages as $msg) {
                $html .= '<div class="alert alert-' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . ' alert-dismissible fade show shadow-sm" role="alert">';
                $html .= '<i class="bi bi-info-circle-fill me-2"></i>' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
                $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                $html .= '</div>';
            }
        }
        unset($_SESSION['flash']);
        return $html;
    }
}
