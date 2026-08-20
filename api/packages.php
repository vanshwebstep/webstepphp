<?php
require_once __DIR__ . '/../includes/helpers.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

// 'b2b' | 'packages' | '' (empty = no filter, sab dikha do)
$page = $_GET['page'] ?? '';
$filterCol = null;
if ($page === 'b2b') $filterCol = 'show_on_b2b';
elseif ($page === 'packages') $filterCol = 'show_on_packages';

if ($id) {
    $stmt = $db->prepare("
        SELECT p.id, p.title, p.description AS des, p.symbol, p.price,
               p.price_desc AS pricedes, p.is_popular AS isPopular, p.btn_text AS btn
        FROM packages p
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

$sql = "
    SELECT p.id, p.title, p.description AS des, p.symbol, p.price,
           p.price_desc AS pricedes, p.is_popular AS isPopular, p.btn_text AS btn
    FROM packages p
    WHERE p.is_active = 1
";
if ($filterCol) {
    $sql .= " AND p.`$filterCol` = 1";
}
$sql .= " ORDER BY p.sort_order ASC";

$pkgStmt = $db->query($sql);
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
        'des'        => $pkg['des'],
        'symbol'     => $pkg['symbol'],
        'price'      => (float)$pkg['price'],
        'pricedes'   => $pkg['pricedes'],
        'events'     => $allFeatures[$pkg['id']] ?? [],
        'btn'        => $pkg['btn'],
        'isPopular'  => (bool)$pkg['isPopular'],
    ];
}, $rawPkgs);

respond(['tabs' => [], 'packages' => $packages]);