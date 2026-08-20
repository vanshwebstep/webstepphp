<?php
require_once __DIR__ . '/../includes/helpers.php';

requireAuth();

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id     = (int)($_GET['id'] ?? 0);

// ─── GET all ─────────────────────────────────────────────────────────────────
if ($method === 'GET' && !$id) {
    $pkgs = $db->query("
        SELECT p.*,
               GROUP_CONCAT(pf.title ORDER BY pf.sort_order SEPARATOR '||') AS features_concat
        FROM packages p
        LEFT JOIN package_features pf ON pf.package_id = p.id
        GROUP BY p.id
        ORDER BY p.sort_order, p.id
    ")->fetchAll();

    $result = array_map(function($p) {
        $p['isPopular']       = (bool)$p['is_popular'];
        $p['show_on_b2b']      = (bool)$p['show_on_b2b'];
        $p['show_on_packages'] = (bool)$p['show_on_packages'];
        $p['features']  = $p['features_concat'] ? explode('||', $p['features_concat']) : [];
        unset($p['features_concat'], $p['is_popular']);
        return $p;
    }, $pkgs);

    success($result);
}

// ─── GET one ─────────────────────────────────────────────────────────────────
if ($method === 'GET' && $id) {
    $stmt = $db->prepare("SELECT * FROM packages WHERE id = ?");
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

    $title            = sanitize($data['title']       ?? '');
    $desc             = sanitize($data['description'] ?? '');
    $symbol           = sanitize($data['symbol']      ?? '$');
    $price            = (float)($data['price']        ?? 0);
    $priceDesc        = sanitize($data['price_desc']  ?? '/project');
    $isPopular        = (int)(bool)($data['is_popular'] ?? false);
    $btnText          = sanitize($data['btn_text']    ?? 'Choose Plan');
    $sortOrder        = (int)($data['sort_order']     ?? 0);
    $showOnB2b        = (int)(bool)($data['show_on_b2b'] ?? false);
    $showOnPackages   = (int)(bool)($data['show_on_packages'] ?? false);
    $features         = $data['features'] ?? [];

    if (!$title) error('Title is required');

    $db->beginTransaction();

    $stmt = $db->prepare("
        INSERT INTO packages (title, description, symbol, price, price_desc, is_popular, btn_text, sort_order, show_on_b2b, show_on_packages)
        VALUES (:t,:d,:s,:p,:pd,:ip,:bt,:so,:b2b,:pkg)
    ");
    $stmt->execute([
        ':t' => $title, ':d' => $desc, ':s' => $symbol,
        ':p' => $price, ':pd'=> $priceDesc, ':ip'=> $isPopular,
        ':bt'=> $btnText, ':so'=> $sortOrder,
        ':b2b' => $showOnB2b, ':pkg' => $showOnPackages,
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
        'title' => 'title', 'description' => 'description',
        'symbol' => 'symbol', 'price' => 'price', 'price_desc' => 'price_desc',
        'is_popular' => 'is_popular', 'btn_text' => 'btn_text',
        'sort_order' => 'sort_order', 'is_active' => 'is_active',
        'show_on_b2b' => 'show_on_b2b', 'show_on_packages' => 'show_on_packages',
    ];

    foreach ($map as $key => $col) {
        if (array_key_exists($key, $data)) {
            $fields[] = "`$col` = :$key";
            $params[":$key"] = in_array($key, ['price','is_popular','sort_order','is_active','show_on_b2b','show_on_packages'])
                ? (float)$data[$key]
                : sanitize((string)$data[$key]);
        }
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
    $db->prepare("DELETE FROM package_features WHERE package_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM packages WHERE id = ?")->execute([$id]);
    success(null, 'Package deleted');
}

error('Invalid request', 400);