<?php
require_once __DIR__ . '/../includes/helpers.php';

requireAuth();

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id     = (int)($_GET['id'] ?? 0);

if ($method === 'GET') {
    $rows = $db->query("SELECT * FROM portfolio_categories ORDER BY sort_order, id")->fetchAll();
    success($rows);
}

if ($method === 'POST') {
    $data = body();
    $name = sanitize($data['name'] ?? '');
    if (!$name) error('Name is required');

    $stmt = $db->prepare("INSERT INTO portfolio_categories (name, sort_order, is_active) VALUES (?,?,?)");
    $stmt->execute([$name, (int)($data['sort_order'] ?? 0), (int)($data['is_active'] ?? 1)]);
    success(['id' => $db->lastInsertId()], 'Category created');
}

if ($method === 'PUT') {
    if (!$id) error('ID required');
    $data = body();
    $stmt = $db->prepare("UPDATE portfolio_categories SET name=?, sort_order=?, is_active=? WHERE id=?");
    $stmt->execute([
        sanitize($data['name'] ?? ''),
        (int)($data['sort_order'] ?? 0),
        (int)($data['is_active'] ?? 1),
        $id
    ]);
    success(null, 'Category updated');
}

if ($method === 'DELETE') {
    if (!$id) error('ID required');
    $db->prepare("DELETE FROM portfolio_categories WHERE id=?")->execute([$id]);
    success(null, 'Category deleted');
}

error('Invalid request', 400);