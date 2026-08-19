<?php
// POST /admin/auth.php?action=login|logout
require_once __DIR__ . '/../includes/helpers.php';

$action = $_GET['action'] ?? 'login';
if ($action === 'check') {
    $admin = currentAdmin();
    if ($admin) {
        success(['username' => $admin['username']], 'Authenticated');
    } else {
        error('Not authenticated', 401);
    }
}
if ($action === 'logout') {
    $token = $_COOKIE['admin_token'] ?? null;
    if ($token) revokeToken($token);
    clearTokenCookie();
    success(null, 'Logged out');
}

method('POST');
$data = body();

$username = sanitize($data['username'] ?? '');
$password = $data['password'] ?? '';

if (!$username || !$password) error('Username and password required');

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateLimitKey = $username . '|' . $ip;

if (isRateLimited($rateLimitKey)) {
    error('Too many failed attempts. Try again in 15 minutes.', 429);
}

$db = getDB();
$stmt = $db->prepare("SELECT id, password FROM admin_users WHERE username = ?");
$stmt->execute([$username]);
$admin = $stmt->fetch();

if (!$admin || !password_verify($password, $admin['password'])) {
    recordLoginAttempt($rateLimitKey, false);
    error('Invalid credentials', 401);
}

recordLoginAttempt($rateLimitKey, true);

$token = issueToken($admin['id']);
setTokenCookie($token);

success(['username' => $username], 'Login successful');