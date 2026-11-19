<?php
/**
 * Database Connection Helper (PDO)
 * NVTI Baddegama National Diploma Portal
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

                // Production: Generic error page without sensitive connection strings
                if (PHP_OS_FAMILY === 'Linux' && getenv('APP_DEBUG') !== 'true') {
                    http_response_code(503);
                    die("<div style='font-family:-apple-system,BlinkMacSystemFont,\"Segoe UI\",Roboto,sans-serif;padding:30px;background:#fff3f3;color:#721c24;border:1px solid #f5c6cb;border-radius:8px;max-width:550px;margin:80px auto;text-align:center;box-shadow:0 4px 15px rgba(0,0,0,0.05);'>
                        <h2 style='margin-top:0;'>Service Temporarily Unavailable</h2>
                        <p>We are currently experiencing technical difficulties connecting to the portal database. Our technical team has been notified.</p>
                        <p style='color:#6c757d;font-size:0.9em;'>Please try again in a few moments.</p>
                    </div>");
                }

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
