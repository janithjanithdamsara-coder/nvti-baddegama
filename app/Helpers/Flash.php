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

    /**
     * Format safe plain/strong inline text from flash messages while blocking XSS.
     */
    private static function formatMessage(string $message): string {
        $safe = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        // Decode harmless double-escaped entities commonly passed in controller strings
        $safe = str_replace('&amp;amp;', '&amp;', $safe);
        // Allow only attribute-free <strong>...</strong> tags to render bold
        return preg_replace('/&lt;strong&gt;(.*?)&lt;\/strong&gt;/is', '<strong>$1</strong>', $safe) ?? $safe;
    }

    public static function render(): string {
        if (empty($_SESSION['flash'])) {
            return '';
        }

        $metaMap = [
            'success' => [
                'icon'   => 'bi-check-circle-fill',
                'title'  => 'Success',
                'accent' => '#10b981',
                'bg'     => '#f0fdf4',
                'border' => '#bbf7d0',
                'text'   => '#065f46',
                'badge'  => '#dcfce7',
            ],
            'danger' => [
                'icon'   => 'bi-exclamation-octagon-fill',
                'title'  => 'Action Required',
                'accent' => '#dc2626',
                'bg'     => '#fef2f2',
                'border' => '#fecaca',
                'text'   => '#991b1b',
                'badge'  => '#fee2e2',
            ],
            'warning' => [
                'icon'   => 'bi-exclamation-triangle-fill',
                'title'  => 'Notice',
                'accent' => '#d97706',
                'bg'     => '#fffbeb',
                'border' => '#fde68a',
                'text'   => '#92400e',
                'badge'  => '#fef3c7',
            ],
            'info' => [
                'icon'   => 'bi-info-circle-fill',
                'title'  => 'Information',
                'accent' => '#2563eb',
                'bg'     => '#eff6ff',
                'border' => '#bfdbfe',
                'text'   => '#1e40af',
                'badge'  => '#dbeafe',
            ],
        ];

        $html = '';
        foreach ($_SESSION['flash'] as $type => $messages) {
            $meta = $metaMap[$type] ?? $metaMap['info'];
            $persistClass = ($type === 'danger') ? ' alert-persistent' : '';
            foreach ($messages as $msg) {
                $formatted = self::formatMessage((string)$msg);
                $html .= '<div class="alert alert-' . htmlspecialchars((string)$type, ENT_QUOTES, 'UTF-8') . ' alert-dismissible fade show shadow-sm rounded-4 p-3 mb-3 d-flex align-items-start gap-3' . $persistClass . '" role="alert" style="background: ' . $meta['bg'] . '; border: 1px solid ' . $meta['border'] . '; border-left: 4px solid ' . $meta['accent'] . '; color: ' . $meta['text'] . ';">';
                $html .= '<div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 34px; height: 34px; background: ' . $meta['badge'] . '; color: ' . $meta['accent'] . ';">';
                $html .= '<i class="bi ' . $meta['icon'] . ' fs-6"></i>';
                $html .= '</div>';
                $html .= '<div class="flex-grow-1 pe-4" style="font-size: 0.9rem; line-height: 1.5;">';
                $html .= '<div class="fw-bold mb-0.5" style="font-size: 0.84rem; letter-spacing: 0.1px;">' . $meta['title'] . '</div>';
                $html .= '<div class="text-dark" style="opacity: 0.9;">' . $formatted . '</div>';
                $html .= '</div>';
                $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                $html .= '</div>';
            }
        }
        unset($_SESSION['flash']);
        return $html;
    }
}
