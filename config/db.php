<?php
// ─── Database Configuration ───────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'u510451310_webstepphp');
define('DB_PASS', 'Webstepphp@135');
define('DB_NAME', 'u510451310_webstepphp');

define('LOCAL_DB_USER', 'root');
define('LOCAL_DB_PASS', '');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Fallback for local XAMPP environment
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $pdo = new PDO($dsn, LOCAL_DB_USER, LOCAL_DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e2) {
                // Try creating DB if local DB_NAME doesn't exist yet on MySQL
                try {
                    $dsnNoDb = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
                    $tmpPdo = new PDO($dsnNoDb, LOCAL_DB_USER, LOCAL_DB_PASS);
                    $tmpPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
                    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                    $pdo = new PDO($dsn, LOCAL_DB_USER, LOCAL_DB_PASS, [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]);
                } catch (PDOException $e3) {
                    http_response_code(500);
                    error_log('DB connection failed: ' . $e->getMessage());
                    die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
                }
            }
        }
    }
    return $pdo;
}
