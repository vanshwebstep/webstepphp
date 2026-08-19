<?php
require_once __DIR__ . '/../includes/helpers.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

if ($id) {
    $stmt = $db->prepare("
        SELECT p.id, p.title, t.name AS category, p.description AS des, p.symbol, p.price,
               p.price_desc AS pricedes, p.is_popular AS isPopular, p.btn_text AS btn
        FROM packages p
        JOIN package_tabs t ON t.id = p.category_id
        WHERE p.id = ? AND p.is_active = 1
    ");
    $stmt->execute([$id]);
    $pkg = $stmt->fetch();

    if (!$pkg) { respond(['error' => 'Package not found'], 404); exit; }

    $fStmt = $db->prepare("SELECT title FROM package_features WHERE package_id=? ORDER BY sort_order ASC");
    $fStmt->execute([$id]);
    $features = $fStmt->fetchAll(PDO::FETCH_ASSOC);

    respond(['package' => [
        'title'     => $pkg['title'],
        'category'  => $pkg['category'],
        'des'       => $pkg['des'],
        'symbol'    => $pkg['symbol'],
        'price'     => (float)$pkg['price'],
        'pricedes'  => $pkg['pricedes'],
        'events'    => $features,
        'btn'       => $pkg['btn'],
        'isPopular' => (bool)$pkg['isPopular'],
    ]]);
    exit;
}

$tabs = $db->query("SELECT name FROM package_tabs WHERE is_active=1 ORDER BY sort_order ASC")
           ->fetchAll(PDO::FETCH_COLUMN);

$pkgStmt = $db->query("
    SELECT p.id, p.title, t.name AS category, p.description AS des, p.symbol, p.price,
           p.price_desc AS pricedes, p.is_popular AS isPopular, p.btn_text AS btn
    FROM packages p
    JOIN package_tabs t ON t.id = p.category_id
    WHERE p.is_active = 1
    ORDER BY t.sort_order, p.sort_order ASC
");
$rawPkgs = $pkgStmt->fetchAll();

$featStmt = $db->query("SELECT package_id, title FROM package_features ORDER BY sort_order ASC");
$allFeatures = [];
foreach ($featStmt->fetchAll() as $row) {
    $allFeatures[$row['package_id']][] = ['title' => $row['title']];
}

$packages = array_map(function($pkg) use ($allFeatures) {
    return [
        'id'         => (int)$pkg['id'],
        'title'      => $pkg['title'],
        'category'   => $pkg['category'],
        'des'        => $pkg['des'],
        'symbol'     => $pkg['symbol'],
        'price'      => (float)$pkg['price'],
        'pricedes'   => $pkg['pricedes'],
        'events'     => $allFeatures[$pkg['id']] ?? [],
        'btn'        => $pkg['btn'],
        'isPopular'  => (bool)$pkg['isPopular'],
    ];
}, $rawPkgs);

respond(['tabs' => $tabs, 'packages' => $packages]);