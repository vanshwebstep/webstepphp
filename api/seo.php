<?php
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$db = getDB();
$page_url = $_GET['page_url'] ?? '';

if (!$page_url) {
    error('Page URL parameter is required');
}

$stmt = $db->prepare("SELECT title, description, keywords FROM seo_settings WHERE page_url = ?");
$stmt->execute([$page_url]);
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
