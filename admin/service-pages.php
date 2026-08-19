<?php
require_once __DIR__ . '/../includes/helpers.php';

requireAuth();

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id = (int)($_GET['id'] ?? 0);

function cleanText(mixed $value): string {
    return html_entity_decode(strip_tags(trim((string)$value)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function formatServicePage(array $row): array {
    return [
        'id' => (int)$row['id'],
        'slug' => cleanText($row['slug']),
        'title' => cleanText($row['title']),
        'content' => json_decode($row['content_json'] ?: '{}', true) ?: [],
        'sort_order' => (int)$row['sort_order'],
        'is_active' => (int)$row['is_active'],
        'updated_at' => $row['updated_at'],
    ];
}

if ($method === 'GET') {
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM service_pages WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) error('Service page not found', 404);
        success(formatServicePage($row));
    }

    $rows = $db->query("SELECT * FROM service_pages ORDER BY sort_order ASC, id ASC")->fetchAll();
    success(array_map('formatServicePage', $rows));
}

if ($method === 'POST' || $method === 'PUT') {
    $data = body();
    $slug = cleanText($data['slug'] ?? '');
    $title = cleanText($data['title'] ?? '');
    $content = $data['content'] ?? [];

    if (!$slug || !$title) error('Slug and title are required');
    if (!is_array($content)) error('Content must be a JSON object');

    $json = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($method === 'POST') {
        $stmt = $db->prepare("
            INSERT INTO service_pages (slug, title, content_json, sort_order, is_active)
            VALUES (:slug, :title, :content, :sort_order, :is_active)
        ");
        $stmt->execute([
            ':slug' => $slug,
            ':title' => $title,
            ':content' => $json,
            ':sort_order' => (int)($data['sort_order'] ?? 0),
            ':is_active' => (int)(bool)($data['is_active'] ?? true),
        ]);
        success(['id' => $db->lastInsertId()], 'Service page created');
    }

    if (!$id) error('ID required');
    $stmt = $db->prepare("
        UPDATE service_pages
        SET slug = :slug, title = :title, content_json = :content, sort_order = :sort_order, is_active = :is_active
        WHERE id = :id
    ");
    $stmt->execute([
        ':id' => $id,
        ':slug' => $slug,
        ':title' => $title,
        ':content' => $json,
        ':sort_order' => (int)($data['sort_order'] ?? 0),
        ':is_active' => (int)(bool)($data['is_active'] ?? true),
    ]);
    success(null, 'Service page updated');
}

if ($method === 'DELETE') {
    if (!$id) error('ID required');
    $db->prepare("DELETE FROM service_pages WHERE id = ?")->execute([$id]);
    success(null, 'Service page deleted');
}

error('Invalid request', 400);