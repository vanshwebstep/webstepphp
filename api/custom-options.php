<?php
// GET /api/custom-options.php
// Returns all custom builder options grouped by category
require_once __DIR__ . '/../includes/helpers.php';

$db = getDB();

$rows = $db->query("
    SELECT category, option_id, title, description, icon, price
    FROM custom_options
    WHERE is_active = 1
    ORDER BY category, sort_order ASC
")->fetchAll();

$grouped = ['websiteType' => [], 'pages' => [], 'design' => [], 'features' => []];

foreach ($rows as $row) {
    $cat = $row['category'];
    $item = [
        'id'    => $row['option_id'],
        'title' => $row['title'],
        'price' => (float)$row['price'],
    ];
    if ($row['description']) $item['desc'] = $row['description'];
    if ($row['icon'])        $item['iconName'] = $row['icon'];
    $grouped[$cat][] = $item;
}

respond($grouped);
