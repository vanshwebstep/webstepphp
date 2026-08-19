<?php
require_once __DIR__ . '/../includes/helpers.php';

requireAuth();

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id = (int)($_GET['id'] ?? 0);

function plainText(mixed $value): string {
    return html_entity_decode(strip_tags(trim((string)$value)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
function formatBlog(array $item): array {

    foreach([
    'title',
    'slug',
    'category',
    'image',
    'excerpt',
    'content',
    'author',
    'author_bio',
    'author_avatar',
    'read_time',
    'seo_title',
    'seo_description'
] as $field){

        if(isset($item[$field])){
            $item[$field] = html_entity_decode((string)$item[$field],ENT_QUOTES | ENT_HTML5,'UTF-8');
        }
    }

    $item['tags'] = json_decode($item['tags'] ?: '[]', true) ?: [];

    return $item;
}

if ($method === 'GET') {

    if ($id) {
        $stmt = $db->prepare("SELECT * FROM blogs WHERE id=?");
        $stmt->execute([$id]);

        $blog = $stmt->fetch();

        if (!$blog) error("Blog not found",404);

        success(formatBlog($blog));
    }

    $blogs = $db->query("
        SELECT *
        FROM blogs
        ORDER BY sort_order ASC,id DESC
    ")->fetchAll();

    success(array_map('formatBlog',$blogs));
}

if ($method === 'POST') {

    $data = body();

    $title = plainText($data['title'] ?? '');

    if(!$title){
        error("Title is required");
    }

 $stmt = $db->prepare("
INSERT INTO blogs
(
title,
slug,
category,
author,
author_bio,
author_avatar,
image,
excerpt,
content,
tags,
published_date,
read_time,
views,
likes,
featured,
sort_order,
is_active,
seo_title,
seo_description
)
VALUES
(
:title,
:slug,
:category,
:author,
:author_bio,
:author_avatar,
:image,
:excerpt,
:content,
:tags,
:published_date,
:read_time,
:views,
:likes,
:featured,
:sort_order,
:is_active,
:seo_title,
:seo_description
)");
    $stmt->execute([

':title'=>$title,
':slug'=>plainText($data['slug'] ?? ''),
':category'=>plainText($data['category'] ?? ''),
':author'=>plainText($data['author'] ?? ''),
':author_bio'=>plainText($data['author_bio'] ?? ''),
':author_avatar'=>plainText($data['author_avatar'] ?? ''),

':image'=>plainText($data['image'] ?? ''),

':excerpt'=>plainText($data['excerpt'] ?? ''),
':content'=>$data['content'] ?? '',

':tags'=>json_encode($data['tags'] ?? []),

':published_date'=>$data['published_date'] ?? null,

':read_time'=>plainText($data['read_time'] ?? ''),

':views'=>(int)($data['views'] ?? 0),

':likes'=>(int)($data['likes'] ?? 0),

':featured'=>(int)($data['featured'] ?? 0),

':sort_order'=>(int)($data['sort_order'] ?? 0),

':is_active'=>(int)($data['is_active'] ?? 1),

':seo_title'=>plainText($data['seo_title'] ?? ''),

':seo_description'=>plainText($data['seo_description'] ?? '')

]);

    success([
        'id'=>$db->lastInsertId()
    ],"Blog created");
}

if ($method === 'PUT') {

    if (!$id) {
        error("ID required");
    }

    $data = body();

    $map = [
        'title'            => 'title',
        'slug'             => 'slug',
        'category'         => 'category',
        'author'           => 'author',
        'author_bio'       => 'author_bio',
        'author_avatar'    => 'author_avatar',
        'image'            => 'image',
        'excerpt'          => 'excerpt',
        'content'          => 'content',
        'published_date'   => 'published_date',
        'read_time'        => 'read_time',
        'views'            => 'views',
        'likes'            => 'likes',
        'featured'         => 'featured',
        'sort_order'       => 'sort_order',
        'is_active'        => 'is_active',
        'seo_title'        => 'seo_title',
        'seo_description'  => 'seo_description',
    ];

    $fields = [];
    $params = [':id' => $id];

    foreach ($map as $key => $column) {
        if (!array_key_exists($key, $data)) continue;

        $fields[] = "$column = :$key";

        if (in_array($key, ['views','likes','featured','sort_order','is_active'], true)) {
            $params[":$key"] = (int)$data[$key];
        } elseif ($key === 'content') {
            $params[":$key"] = $data[$key] ?? '';
        } elseif ($key === 'published_date') {
            $params[":$key"] = $data[$key] ?? null;
        } else {
            $params[":$key"] = plainText($data[$key]);
        }
    }

    if (array_key_exists('tags', $data)) {
        $fields[] = "tags = :tags";
        $params[':tags'] = json_encode($data['tags'] ?? []);
    }

    if ($fields) {
        $db->prepare("
            UPDATE blogs
            SET " . implode(',', $fields) . "
            WHERE id = :id
        ")->execute($params);
    }

    success(null, "Blog updated");
}

if ($method === 'DELETE') {

    if(!$id){
        error("ID required");
    }

    $db->prepare("
        DELETE FROM blogs
        WHERE id=?
    ")->execute([$id]);

    success(null,"Blog deleted");

}

error("Invalid request",400);