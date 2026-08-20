<?php
require_once __DIR__ . '/../includes/helpers.php';

$db = getDB();

function decodeText(mixed $value): string {
    return html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function decodeList(mixed $value): array {
    $items = is_array($value) ? $value : [];
    return array_map(fn($item) => decodeText($item), $items);
}

$categories = $db->query("
    SELECT name
    FROM portfolio_categories
    WHERE is_active = 1
    ORDER BY sort_order ASC, name ASC
")->fetchAll(PDO::FETCH_COLUMN);
$categories = decodeList($categories);

$stmt = $db->query("
    SELECT title, category, image AS img, description AS `desc`, tags_json, tech_json, year
    FROM portfolio_projects
    WHERE is_active = 1
    ORDER BY sort_order ASC, id ASC
");

$projects = array_map(function ($row) {
    return [
        'title'    => decodeText($row['title']),
        'category' => decodeText($row['category']),
        'img'      => decodeText($row['img']),
        'desc'     => decodeText($row['desc']),
        'tags'     => decodeList(json_decode($row['tags_json'] ?: '[]', true) ?: []),
        'tech'     => decodeList(json_decode($row['tech_json'] ?: '[]', true) ?: []),
        'year'     => decodeText($row['year']),
    ];
}, $stmt->fetchAll());

respond([
    'categories' => $categories ?: ['All'],
    'projects' => $projects,
]);