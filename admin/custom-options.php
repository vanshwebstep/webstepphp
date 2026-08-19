<?php
// Admin Custom Options CRUD
// GET    /admin/custom-options.php              → list all
// GET    /admin/custom-options.php?id=X         → single
// POST   /admin/custom-options.php              → create
// PUT    /admin/custom-options.php?id=X         → update
// DELETE /admin/custom-options.php?id=X         → delete

require_once __DIR__ . '/../includes/helpers.php';

requireAuth();

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id     = (int)($_GET['id'] ?? 0);

if ($method === 'GET' && !$id) {
    $rows = $db->query("SELECT * FROM custom_options ORDER BY category, sort_order")->fetchAll();
    success($rows);
}

if ($method === 'GET' && $id) {
    $stmt = $db->prepare("SELECT * FROM custom_options WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) error('Not found', 404);
    success($row);
}

if ($method === 'POST') {
    $data = body();
    $category  = $data['category']    ?? '';
    $optionId  = sanitize($data['option_id']   ?? '');
    $title     = sanitize($data['title']       ?? '');
    $desc      = sanitize($data['description'] ?? '');
    $icon      = sanitize($data['icon']        ?? '');
    $price     = (float)($data['price']        ?? 0);
    $sort      = (int)($data['sort_order']     ?? 0);

    $validCats = ['websiteType','pages','design','features'];
    if (!in_array($category, $validCats)) error('Invalid category');
    if (!$optionId || !$title) error('option_id and title required');

    $stmt = $db->prepare("
        INSERT INTO custom_options (category, option_id, title, description, icon, price, sort_order)
        VALUES (:cat,:oid,:t,:d,:i,:p,:so)
    ");
    $stmt->execute([
        ':cat'=>$category,':oid'=>$optionId,':t'=>$title,
        ':d'=>$desc,':i'=>$icon,':p'=>$price,':so'=>$sort
    ]);
    success(['id' => $db->lastInsertId()], 'Option created');
}

if ($method === 'PUT') {
    if (!$id) error('ID required');
    $data = body();

    $fields = []; $params = [];
    $map = ['category'=>'category','option_id'=>'option_id','title'=>'title',
            'description'=>'description','icon'=>'icon','price'=>'price',
            'sort_order'=>'sort_order','is_active'=>'is_active'];

    foreach ($map as $key => $col) {
        if (array_key_exists($key, $data)) {
            $fields[] = "`$col` = :$key";
            $params[":$key"] = in_array($key, ['price','sort_order','is_active'])
                ? (float)$data[$key] : sanitize((string)$data[$key]);
        }
    }
    if ($fields) {
        $params[':id'] = $id;
        $db->prepare("UPDATE custom_options SET " . implode(', ', $fields) . " WHERE id=:id")
           ->execute($params);
    }
    success(null, 'Option updated');
}

if ($method === 'DELETE') {
    if (!$id) error('ID required');
    $db->prepare("DELETE FROM custom_options WHERE id=?")->execute([$id]);
    success(null, 'Option deleted');
}

error('Invalid request', 400);