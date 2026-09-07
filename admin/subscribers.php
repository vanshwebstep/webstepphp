<?php
// Admin Subscribers
// GET  /admin/subscribers.php    → list all subscribers
// DELETE /admin/subscribers.php?id=X  → delete subscriber

require_once __DIR__ . '/../includes/helpers.php';

requireAuth();

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id     = (int)($_GET['id'] ?? 0);

// ─── GET list ────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    $rows = $db->prepare("SELECT * FROM newsletter_subscribers ORDER BY created_at DESC");
    $rows->execute();
    success($rows->fetchAll());
}

// ─── DELETE ──────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    if (!$id) error('ID required');
    $db->prepare("DELETE FROM newsletter_subscribers WHERE id=?")->execute([$id]);
    success(null, 'Subscriber deleted');
}

error('Invalid request', 400);
