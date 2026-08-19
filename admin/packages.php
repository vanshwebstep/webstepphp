<?php
require_once __DIR__ . '/../includes/helpers.php';

requireAuth();

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id     = (int)($_GET['id'] ?? 0);

// ─── GET all ─────────────────────────────────────────────────────────────────
if ($method === 'GET' && !$id) {
    $pkgs = $db->query("
        SELECT p.*, t.name AS category_name,
               GROUP_CONCAT(pf.title ORDER BY pf.sort_order SEPARATOR '||') AS features_concat
        FROM packages p
        JOIN package_tabs t ON t.id = p.category_id
        LEFT JOIN package_features pf ON pf.package_id = p.id
        GROUP BY p.id
        ORDER BY t.sort_order, p.sort_order
    ")->fetchAll();

    $result = array_map(function($p) {
        $p['isPopular'] = (bool)$p['is_popular'];
        $p['features']  = $p['features_concat'] ? explode('||', $p['features_concat']) : [];
        unset($p['features_concat'], $p['is_popular']);
        return $p;
    }, $pkgs);

    success($result);
}

// ─── GET one ─────────────────────────────────────────────────────────────────
if ($method === 'GET' && $id) {
    $stmt = $db->prepare("
        SELECT p.*, t.name AS category_name
        FROM packages p JOIN package_tabs t ON t.id = p.category_id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $pkg = $stmt->fetch();
    if (!$pkg) error('Package not found', 404);

    $fStmt = $db->prepare("SELECT title FROM package_features WHERE package_id=? ORDER BY sort_order");
    $fStmt->execute([$id]);
    $pkg['features'] = $fStmt->fetchAll(PDO::FETCH_COLUMN);

    success($pkg);
}

// ─── POST create ─────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $data = body();

    $title      = sanitize($data['title']       ?? '');
    $categoryId = (int)($data['category_id']    ?? 0);
    $desc       = sanitize($data['description'] ?? '');
    $symbol     = sanitize($data['symbol']      ?? '$');
    $price      = (float)($data['price']        ?? 0);
    $priceDesc  = sanitize($data['price_desc']  ?? '/project');
    $isPopular  = (int)(bool)($data['is_popular'] ?? false);
    $btnText    = sanitize($data['btn_text']    ?? 'Choose Plan');
    $sortOrder  = (int)($data['sort_order']     ?? 0);
    $features   = $data['features'] ?? [];

    if (!$title || !$categoryId) error('Title and category are required');

    $chk = $db->prepare("SELECT id FROM package_tabs WHERE id = ?");
    $chk->execute([$categoryId]);
    if (!$chk->fetch()) error('Invalid category');

    $db->beginTransaction();

    $stmt = $db->prepare("
        INSERT INTO packages (title, category_id, description, symbol, price, price_desc, is_popular, btn_text, sort_order)
        VALUES (:t,:cid,:d,:s,:p,:pd,:ip,:bt,:so)
    ");
    $stmt->execute([
        ':t' => $title, ':cid' => $categoryId, ':d' => $desc, ':s' => $symbol,
        ':p' => $price, ':pd'=> $priceDesc, ':ip'=> $isPopular,
        ':bt'=> $btnText, ':so'=> $sortOrder,
    ]);
    $newId = $db->lastInsertId();

    $fStmt = $db->prepare("INSERT INTO package_features (package_id, title, sort_order) VALUES (?,?,?)");
    foreach ($features as $i => $feat) {
        if ($feat = sanitize((string)$feat)) {
            $fStmt->execute([$newId, $feat, $i + 1]);
        }
    }

    $db->commit();
    success(['id' => $newId], 'Package created');
}

// ─── PUT update ──────────────────────────────────────────────────────────────
if ($method === 'PUT') {
    if (!$id) error('ID required');
    $data = body();

    $fields = [];
    $params = [];
    $map = [
        'title' => 'title', 'category_id' => 'category_id', 'description' => 'description',
        'symbol' => 'symbol', 'price' => 'price', 'price_desc' => 'price_desc',
        'is_popular' => 'is_popular', 'btn_text' => 'btn_text',
        'sort_order' => 'sort_order', 'is_active' => 'is_active',
    ];

    foreach ($map as $key => $col) {
        if (array_key_exists($key, $data)) {
            $fields[] = "`$col` = :$key";
            $params[":$key"] = in_array($key, ['price','is_popular','sort_order','is_active','category_id'])
                ? (float)$data[$key]
                : sanitize((string)$data[$key]);
        }
    }

    if (isset($params[':category_id'])) {
        $chk = $db->prepare("SELECT id FROM package_tabs WHERE id = ?");
        $chk->execute([(int)$params[':category_id']]);
        if (!$chk->fetch()) error('Invalid category');
    }

    if ($fields) {
        $params[':id'] = $id;
        $db->prepare("UPDATE packages SET " . implode(', ', $fields) . " WHERE id = :id")
           ->execute($params);
    }

    if (isset($data['features']) && is_array($data['features'])) {
        $db->prepare("DELETE FROM package_features WHERE package_id = ?")->execute([$id]);
        $fStmt = $db->prepare("INSERT INTO package_features (package_id, title, sort_order) VALUES (?,?,?)");
        foreach ($data['features'] as $i => $feat) {
            if ($feat = sanitize((string)$feat)) {
                $fStmt->execute([$id, $feat, $i + 1]);
            }
        }
    }

    success(null, 'Package updated');
}

// ─── DELETE ──────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    if (!$id) error('ID required');
    $chk = $db->prepare("SELECT COUNT(*) FROM packages WHERE category_id = ?");
    $chk->execute([$id]);
    if ($chk->fetchColumn() > 0) {
        error('Cannot delete: packages exist under this category');
    }
    $db->prepare("DELETE FROM package_tabs WHERE id = ?")->execute([$id]);
    success(null, 'Category deleted');
}

error('Invalid request', 400);