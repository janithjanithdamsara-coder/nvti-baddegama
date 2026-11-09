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

    /**
     * OWASP Password Strength Policy:
     * - Minimum 8 characters
     * - At least one uppercase letter (A-Z)
     * - At least one lowercase letter (a-z)
     * - At least one digit (0-9)
     */
    public static function validatePassword(string $password): array {
        $errors = [];
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must include at least one uppercase letter (A-Z).";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must include at least one lowercase letter (a-z).";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must include at least one number (0-9).";
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
            'message'=> implode(' ', $errors)
        ];
    }
}

// Convenient global helper function for views
function e(?string $str): string {
    return Sanitizer::e($str);
}
