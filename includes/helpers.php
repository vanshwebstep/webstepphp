<?php
// ─── Common Helpers ────────────────────────────────────────────────────────────
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

// ─── CORS (credentials-safe) ──────────────────────────────────────────────────
// NOTE: '*' origin does NOT work with credentials (cookies). Must echo back
// the exact allowed origin. Adjust $allowedOrigins for your real frontend(s).
$allowedOrigins = [
    'https://webstepdev.com',
    'http://localhost:3000',   // local Next.js dev
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function respond(mixed $data, int $code = 200): never {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function error(string $message, int $code = 400): never {
    respond(['success' => false, 'error' => $message], $code);
}

function success(mixed $data = null, string $message = 'OK'): never {
    respond(['success' => true, 'message' => $message, 'data' => $data]);
}

function body(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function method(string $expected): void {
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($expected)) {
        error('Method not allowed', 405);
    }
}

function sanitize(string $val): string {
    return htmlspecialchars(strip_tags(trim($val)));
}

// ─── Token-based admin auth ────────────────────────────────────────────────────

const TOKEN_TTL_SECONDS = 2 * 60 * 60;      // 2 hours
const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_WINDOW_SECONDS = 15 * 60;     // 15 min

function generateToken(): string {
    return bin2hex(random_bytes(32));
}

function hashToken(string $token): string {
    return hash('sha256', $token);
}

function issueToken(int $adminId): string {
    $db = getDB();
    $token = generateToken();
    $expiresAt = date('Y-m-d H:i:s', time() + TOKEN_TTL_SECONDS);

    $stmt = $db->prepare(
        "INSERT INTO admin_tokens (admin_id, token_hash, user_agent, ip_address, expires_at)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $adminId,
        hashToken($token),
        substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        $_SERVER['REMOTE_ADDR'] ?? null,
        $expiresAt,
    ]);

    return $token;
}

function currentAdmin(): ?array {
    $token = $_COOKIE['admin_token'] ?? null;
    if (!$token) return null;

    $db = getDB();
    $stmt = $db->prepare(
        "SELECT t.admin_id, t.expires_at, u.username
         FROM admin_tokens t
         JOIN admin_users u ON u.id = t.admin_id
         WHERE t.token_hash = ?"
    );
    $stmt->execute([hashToken($token)]);
    $row = $stmt->fetch();

    if (!$row) return null;
    if (strtotime($row['expires_at']) < time()) {
        revokeToken($token);
        return null;
    }
    return $row;
}

// Use this in every protected admin/*.php file instead of old requireAdmin()
function requireAuth(): array {
    $admin = currentAdmin();
    if (!$admin) {
        error('Unauthorized', 401);
    }
    return $admin;
}

function revokeToken(string $token): void {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM admin_tokens WHERE token_hash = ?");
    $stmt->execute([hashToken($token)]);
}

function revokeAllTokensForAdmin(int $adminId): void {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM admin_tokens WHERE admin_id = ?");
    $stmt->execute([$adminId]);
}
function isSecureRequest(): bool {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['SERVER_PORT'] ?? '') == 443;
}
function setTokenCookie(string $token): void {
    $secure = isSecureRequest();
    setcookie('admin_token', $token, [
        'expires'  => time() + TOKEN_TTL_SECONDS,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => $secure ? 'None' : 'Lax',
    ]);
}

function clearTokenCookie(): void {
    $secure = isSecureRequest();
    setcookie('admin_token', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => $secure ? 'None' : 'Lax',
    ]);
}
// ─── Login rate limiting ────────────────────────────────────────────────────────

function isRateLimited(string $identifier): bool {
    $db = getDB();
    $stmt = $db->prepare(
        "SELECT COUNT(*) AS c FROM login_attempts
         WHERE identifier = ? AND success = 0 AND attempted_at > ?"
    );
    $stmt->execute([$identifier, date('Y-m-d H:i:s', time() - LOCKOUT_WINDOW_SECONDS)]);
    return $stmt->fetch()['c'] >= MAX_LOGIN_ATTEMPTS;
}

function recordLoginAttempt(string $identifier, bool $success): void {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO login_attempts (identifier, success) VALUES (?, ?)");
    $stmt->execute([$identifier, $success ? 1 : 0]);
}