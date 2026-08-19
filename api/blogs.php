<?php
require_once __DIR__ . '/../includes/helpers.php';

$db = getDB();

function decodeText(mixed $value): string {
    return html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function formatBlogRow(array $row): array {
    return [
        'id'                => (int)$row['id'],
        'title'             => decodeText($row['title']),
        'slug'              => decodeText($row['slug']),
        'category'          => decodeText($row['category']),
        'author'            => decodeText($row['author']),
        'author_bio'        => decodeText($row['author_bio'] ?? ''),
        'author_avatar'     => decodeText($row['author_avatar'] ?? ''),
        'image'             => decodeText($row['image']),
        'excerpt'           => decodeText($row['excerpt']),
        'content'           => decodeText($row['content']),
        'tags'              => json_decode($row['tags'] ?: '[]', true) ?: [],
        'published_date'    => $row['published_date'],
        'read_time'         => decodeText($row['read_time']),
        'views'             => (int)$row['views'],
        'likes'             => (int)$row['likes'],
        'featured'          => (bool)$row['featured'],
        'seo_title'         => decodeText($row['seo_title']),
        'seo_description'   => decodeText($row['seo_description']),
        'created_at'        => $row['created_at'],
    ];
}

$slug = $_GET['slug'] ?? null;

// ─── SINGLE BLOG (by slug) ─────────────────────────────────
if ($slug) {
    $stmt = $db->prepare("
        SELECT id, title, slug, category, author, author_bio, author_avatar,
               image, excerpt, content, tags, published_date, read_time,
               views, likes, featured, seo_title, seo_description, created_at
        FROM blogs
        WHERE slug = ? AND is_active = 1
        LIMIT 1
    ");
    $stmt->execute([$slug]);
    $blog = $stmt->fetch();

    if (!$blog) {
        respond(['error' => 'Blog not found'], 404);
        exit;
    }

    // views count +1
    $db->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?")->execute([$blog['id']]);
    $blog['views'] = (int)$blog['views'] + 1;

    // related blogs — same category, exclude current
    $relStmt = $db->prepare("
        SELECT id, title, slug, category, image, excerpt, published_date, read_time
        FROM blogs
        WHERE category = ? AND id != ? AND is_active = 1
        ORDER BY id DESC
        LIMIT 3
    ");
    $relStmt->execute([$blog['category'], $blog['id']]);
    $related = $relStmt->fetchAll();

    respond([
        'blog'    => formatBlogRow($blog),
        'related' => array_map(function($r) {
            return [
                'title'          => decodeText($r['title']),
                'slug'           => decodeText($r['slug']),
                'category'       => decodeText($r['category']),
                'image'          => decodeText($r['image']),
                'excerpt'        => decodeText($r['excerpt']),
                'published_date' => $r['published_date'],
                'read_time'      => decodeText($r['read_time']),
            ];
        }, $related),
    ]);
    exit;
}

// ─── LISTING (all blogs) ───────────────────────────────────
$categories = $db->query("
    SELECT DISTINCT category
    FROM blogs
    WHERE is_active = 1
    ORDER BY category ASC
")->fetchAll(PDO::FETCH_COLUMN);

$categories = array_map('decodeText', $categories);

$stmt = $db->query("
    SELECT id, title, slug, category, author, author_bio, author_avatar,
           image, excerpt, content, tags, published_date, read_time,
           views, likes, featured, seo_title, seo_description, created_at
    FROM blogs
    WHERE is_active = 1
    ORDER BY featured DESC, sort_order ASC, id DESC
");

$blogs = array_map('formatBlogRow', $stmt->fetchAll());

respond([
    'categories' => $categories ?: ['All'],
    'blogs'      => $blogs,
]);