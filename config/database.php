<?php
/**
 * Database Connection Helper (PDO)
 * EduCore LMS
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // Native prepared statements for high security
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Log and show user-friendly message
                error_log("Database Connection Error: " . $e->getMessage());
                die("<div style='font-family:sans-serif;padding:30px;background:#fff3f3;color:#d9534f;border:1px solid #f5c6cb;border-radius:6px;max-width:600px;margin:50px auto;'>
                    <h3>Database Connection Error</h3>
                    <p>Could not connect to the database. Please ensure MySQL is running in XAMPP and database <strong>" . htmlspecialchars(DB_NAME) . "</strong> is imported.</p>
                    <p><small>" . htmlspecialchars($e->getMessage()) . "</small></p>
                </div>");
            }
        }
        return self::$instance;
    }
}
