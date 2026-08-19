<?php
require_once __DIR__ . '/../includes/helpers.php';

requireAuth();

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id = (int)($_GET['id'] ?? 0);

function plainText(mixed $value): string {
    return html_entity_decode(strip_tags(trim((string)$value)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function jsonList(mixed $value): string {
    if (is_array($value)) {
        $items = array_values(array_filter(array_map(fn($item) => plainText($item), $value)));
        return json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    $items = array_values(array_filter(array_map(fn($item) => plainText($item), explode(',', (string)$value))));
    return json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function formatPortfolioProject(array $item): array {
    foreach (['title', 'category', 'image', 'description', 'year'] as $field) {
        if (isset($item[$field])) $item[$field] = html_entity_decode((string)$item[$field], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    $item['tags'] = array_map(fn($tag) => html_entity_decode((string)$tag, ENT_QUOTES | ENT_HTML5, 'UTF-8'), json_decode($item['tags_json'] ?: '[]', true) ?: []);
    $item['tech'] = array_map(fn($tech) => html_entity_decode((string)$tech, ENT_QUOTES | ENT_HTML5, 'UTF-8'), json_decode($item['tech_json'] ?: '[]', true) ?: []);
    unset($item['tags_json'], $item['tech_json']);
    return $item;
}

if ($method === 'GET') {
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM portfolio_projects WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        if (!$item) error('Project not found', 404);
        success(formatPortfolioProject($item));
    }

    $items = $db->query("SELECT * FROM portfolio_projects ORDER BY sort_order ASC, id ASC")->fetchAll();
    success(array_map('formatPortfolioProject', $items));
}

if ($method === 'POST') {
    $data = body();
    $title = plainText($data['title'] ?? '');
    $category = plainText($data['category'] ?? '');
    if (!$title || !$category) error('Title and category are required');

    $stmt = $db->prepare("
        INSERT INTO portfolio_projects
            (title, category, image, description, tags_json, tech_json, year, sort_order, is_active)
        VALUES
            (:title, :category, :image, :description, :tags, :tech, :year, :sort_order, :is_active)
    ");
    $stmt->execute([
        ':title' => $title,
        ':category' => $category,
        ':image' => plainText($data['image'] ?? ''),
        ':description' => plainText($data['description'] ?? ''),
        ':tags' => jsonList($data['tags'] ?? []),
        ':tech' => jsonList($data['tech'] ?? []),
        ':year' => plainText((string)($data['year'] ?? date('Y'))),
        ':sort_order' => (int)($data['sort_order'] ?? 0),
        ':is_active' => (int)(bool)($data['is_active'] ?? true),
    ]);
    success(['id' => $db->lastInsertId()], 'Portfolio project created');
}

if ($method === 'PUT') {
    if (!$id) error('ID required');
    $data = body();
    $fields = [];
    $params = [':id' => $id];
    $map = [
        'title' => 'title',
        'category' => 'category',
        'image' => 'image',
        'description' => 'description',
        'year' => 'year',
        'sort_order' => 'sort_order',
        'is_active' => 'is_active',
    ];

    foreach ($map as $key => $column) {
        if (array_key_exists($key, $data)) {
            $fields[] = "`$column` = :$key";
            $params[":$key"] = in_array($key, ['sort_order', 'is_active'], true)
                ? (int)$data[$key]
                : plainText($data[$key]);
        }
    }
    if (array_key_exists('tags', $data)) {
        $fields[] = 'tags_json = :tags_json';
        $params[':tags_json'] = jsonList($data['tags']);
    }
    if (array_key_exists('tech', $data)) {
        $fields[] = 'tech_json = :tech_json';
        $params[':tech_json'] = jsonList($data['tech']);
    }

    if ($fields) {
        $db->prepare("UPDATE portfolio_projects SET " . implode(', ', $fields) . " WHERE id = :id")->execute($params);
    }
    success(null, 'Portfolio project updated');
}

if ($method === 'DELETE') {
    if (!$id) error('ID required');
    $db->prepare("DELETE FROM portfolio_projects WHERE id = ?")->execute([$id]);
    success(null, 'Portfolio project deleted');
}

error('Invalid request', 400);