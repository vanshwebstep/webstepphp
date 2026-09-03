<?php
// ─── Database Configuration ───────────────────────────────────────────────────
// define('DB_HOST', 'localhost');
// define('DB_USER', 'u510451310_webstepphp');
// define('DB_PASS', 'Webstepphp@135');
// define('DB_NAME', 'u510451310_webstepphp');

define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // XAMPP/WAMP ka default user
define('DB_PASS', '');          // XAMPP me default password blank hota hai
define('DB_NAME', 'u510451310_webstepphp');

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
            http_response_code(500);
            error_log('DB connection failed: ' . $e->getMessage()); // server log me detail
            die(json_encode(['error' => 'Database connection failed'])); // client ko generic message
        }
    }
    return $pdo;
}
// http://localhost/webstepphp
// http://localhost/webstepphp



