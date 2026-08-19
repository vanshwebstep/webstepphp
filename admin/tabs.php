<?php
// Admin Tabs CRUD
// GET    /admin/tabs.php         → list all tabs
// POST   /admin/tabs.php         → create tab
// PUT    /admin/tabs.php?id=X    → update tab
// DELETE /admin/tabs.php?id=X    → delete tab

require_once __DIR__ . '/../includes/helpers.php';

requireAuth();

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id     = (int)($_GET['id'] ?? 0);

if ($method === 'GET') {
    success($db->query("SELECT * FROM package_tabs ORDER BY sort_order")->fetchAll());
}

if ($method === 'POST') {
    $data = body();
    $name = sanitize($data['name'] ?? '');
    $sort = (int)($data['sort_order'] ?? 0);
    if (!$name) error('Name required');

    $db->prepare("INSERT INTO package_tabs (name, sort_order) VALUES (?,?)")->execute([$name, $sort]);
    success(['id' => $db->lastInsertId()], 'Tab created');
}

if ($method === 'PUT') {
    if (!$id) error('ID required');
    $data = body();
    $fields = []; $params = [];

    if (isset($data['name']))       { $fields[] = 'name=:name';             $params[':name'] = sanitize($data['name']); }
    if (isset($data['sort_order'])) { $fields[] = 'sort_order=:sort_order'; $params[':sort_order'] = (int)$data['sort_order']; }
    if (isset($data['is_active']))  { $fields[] = 'is_active=:is_active';   $params[':is_active'] = (int)(bool)$data['is_active']; }

    if ($fields) {
        $params[':id'] = $id;
        $db->prepare("UPDATE package_tabs SET " . implode(', ', $fields) . " WHERE id=:id")->execute($params);
    }
    success(null, 'Tab updated');
}

if ($method === 'DELETE') {
    if (!$id) error('ID required');
    $db->prepare("DELETE FROM package_tabs WHERE id=?")->execute([$id]);
    success(null, 'Tab deleted');
}

error('Invalid request', 400);