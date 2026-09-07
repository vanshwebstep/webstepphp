<?php
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$db = getDB();
$raw_url = trim($_GET['page_url'] ?? '');

if (!$raw_url) {
    error('Page URL parameter is required');
}

$url_without_slash = ltrim($raw_url, '/');
$url_with_slash = '/' . $url_without_slash;

$stmt = $db->prepare("
    SELECT title, description, keywords 
    FROM seo_settings 
    WHERE page_url = ? OR page_url = ? OR page_url = ?
    LIMIT 1
");
$stmt->execute([$raw_url, $url_without_slash, $url_with_slash]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    // Return empty defaults if not found so the frontend doesn't break
    success([
        'title' => '',
        'description' => '',
        'keywords' => ''
    ]);
}

success($row);

