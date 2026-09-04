<?php
require_once __DIR__ . '/../includes/helpers.php';

requireAuth();

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id = (int)($_GET['id'] ?? 0);

function formatSeoSetting(array $row): array {
    return [
        'id' => (int)$row['id'],
        'page_url' => $row['page_url'],
        'title' => $row['title'],
        'description' => $row['description'],
        'keywords' => $row['keywords'],
        'updated_at' => $row['updated_at'],
    ];
}

if ($method === 'GET') {
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM seo_settings WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) error('SEO setting not found', 404);
        success(formatSeoSetting($row));
    }

    $rows = $db->query("SELECT * FROM seo_settings ORDER BY id DESC")->fetchAll();
    success(array_map('formatSeoSetting', $rows));
}

if ($method === 'POST' || $method === 'PUT') {
    $data = body();
    $page_url = trim($data['page_url'] ?? '');
    $title = trim($data['title'] ?? '');
    $description = trim($data['description'] ?? '');
    $keywords = trim($data['keywords'] ?? '');

    if (!$page_url) error('Page URL identifier is required');

    if ($method === 'POST') {
        $stmt = $db->prepare("
            INSERT INTO seo_settings (page_url, title, description, keywords)
            VALUES (:page_url, :title, :description, :keywords)
        ");
        try {
            $stmt->execute([
                ':page_url' => $page_url,
                ':title' => $title,
                ':description' => $description,
                ':keywords' => $keywords,
            ]);
            success(['id' => $db->lastInsertId()], 'SEO settings created');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                error('SEO settings for this page URL already exist.');
            }
            error('Database error.');
        }
    }

    if (!$id) error('ID required');
    $stmt = $db->prepare("
        UPDATE seo_settings
        SET page_url = :page_url, title = :title, description = :description, keywords = :keywords
        WHERE id = :id
    ");
    try {
        $stmt->execute([
            ':id' => $id,
            ':page_url' => $page_url,
            ':title' => $title,
            ':description' => $description,
            ':keywords' => $keywords,
        ]);
        success(null, 'SEO settings updated');
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            error('SEO settings for this page URL already exist.');
        }
        error('Database error.');
    }
}

if ($method === 'DELETE') {
    if (!$id) error('ID required');
    $db->prepare("DELETE FROM seo_settings WHERE id = ?")->execute([$id]);
    success(null, 'SEO setting deleted');
}

error('Invalid request', 400);
