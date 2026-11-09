<?php
/**
 * Sanitizer Helper
 */

class Sanitizer {
    public static function e(?string $string): string {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }

    public static function clean(mixed $data): mixed {
        if (is_array($data)) {
            return array_map([self::class, 'clean'], $data);
        }
        return is_string($data) ? trim(strip_tags($data)) : $data;
    }
}

// Convenient global helper function for views
function e(?string $str): string {
    return Sanitizer::e($str);
}
