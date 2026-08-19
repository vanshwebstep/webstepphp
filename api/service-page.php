<?php
require_once __DIR__ . '/../includes/helpers.php';

$db = getDB();
$slug = sanitize($_GET['slug'] ?? '');

if (!$slug) error('Slug is required');

$stmt = $db->prepare("SELECT slug, title, content_json FROM service_pages WHERE slug = ? AND is_active = 1 LIMIT 1");
$stmt->execute([$slug]);
$row = $stmt->fetch();

if (!$row) error('Page not found', 404);

$content = json_decode($row['content_json'] ?: '{}', true) ?: [];
$content['slug'] = html_entity_decode((string)$row['slug'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
$content['title'] = html_entity_decode((string)$row['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');

success($content);
