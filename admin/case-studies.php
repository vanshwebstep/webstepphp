<?php
require_once __DIR__ . '/../includes/helpers.php';

requireAuth();

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id = (int)($_GET['id'] ?? 0);

function casePlainText(mixed $value): string {
    return html_entity_decode(strip_tags(trim((string)$value)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function casePlainValue(mixed $value): mixed {
    if (is_array($value)) {
        return array_map('casePlainValue', $value);
    }
    return casePlainText($value);
}

function caseJsonList(mixed $value): string {
    if (is_array($value)) {
        return json_encode(array_values(casePlainValue($value)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    $items = array_values(array_filter(array_map(fn($item) => casePlainText($item), explode(',', (string)$value))));
    return json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function formatCaseStudy(array $item): array {
    foreach ($item as $key => $value) {
        if (is_string($value)) $item[$key] = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    $item['tags'] = casePlainValue(json_decode($item['tags_json'] ?: '[]', true) ?: []);
    $item['tech'] = casePlainValue(json_decode($item['tech_json'] ?: '[]', true) ?: []);
    $item['metrics'] = casePlainValue(json_decode($item['metrics_json'] ?: '[]', true) ?: []);
    $item['deliverables'] = casePlainValue(json_decode($item['deliverables_json'] ?: '[]', true) ?: []);
    unset($item['tags_json'], $item['tech_json'], $item['metrics_json'], $item['deliverables_json']);
    return $item;
}

if ($method === 'GET') {
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM case_studies WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        if (!$item) error('Case study not found', 404);
        success(formatCaseStudy($item));
    }

    $items = $db->query("SELECT * FROM case_studies ORDER BY featured DESC, sort_order ASC, id ASC")->fetchAll();
    success(array_map('formatCaseStudy', $items));
}

if ($method === 'POST') {
    $data = body();
    $title = casePlainText($data['title'] ?? '');
    $category = casePlainText($data['category'] ?? '');
    if (!$title || !$category) error('Title and category are required');

    $slug = sanitize($data['slug'] ?? strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title)));
    $slug = trim($slug, '-');

    $stmt = $db->prepare("
        INSERT INTO case_studies
            (slug, title, tagline, category, tags_json, image, logo, client, duration, year,
             tech_json, challenge, solution, result, metrics_json, deliverables_json,
             testimonial_quote, testimonial_author, testimonial_role, testimonial_avatar,
             featured, color, accent, light_text, dark_bg, sort_order, is_active)
        VALUES
            (:slug, :title, :tagline, :category, :tags, :image, :logo, :client, :duration, :year,
             :tech, :challenge, :solution, :result, :metrics, :deliverables,
             :testimonial_quote, :testimonial_author, :testimonial_role, :testimonial_avatar,
             :featured, :color, :accent, :light_text, :dark_bg, :sort_order, :is_active)
    ");
    $stmt->execute([
        ':slug' => $slug,
        ':title' => $title,
        ':tagline' => casePlainText($data['tagline'] ?? ''),
        ':category' => $category,
        ':tags' => caseJsonList($data['tags'] ?? []),
        ':image' => casePlainText($data['image'] ?? ''),
        ':logo' => casePlainText($data['logo'] ?? ''),
        ':client' => casePlainText($data['client'] ?? ''),
        ':duration' => casePlainText($data['duration'] ?? ''),
        ':year' => casePlainText((string)($data['year'] ?? date('Y'))),
        ':tech' => caseJsonList($data['tech'] ?? []),
        ':challenge' => casePlainText($data['challenge'] ?? ''),
        ':solution' => casePlainText($data['solution'] ?? ''),
        ':result' => casePlainText($data['result'] ?? ''),
        ':metrics' => caseJsonList($data['metrics'] ?? []),
        ':deliverables' => caseJsonList($data['deliverables'] ?? []),
        ':testimonial_quote' => casePlainText($data['testimonial_quote'] ?? ''),
        ':testimonial_author' => casePlainText($data['testimonial_author'] ?? ''),
        ':testimonial_role' => casePlainText($data['testimonial_role'] ?? ''),
        ':testimonial_avatar' => casePlainText($data['testimonial_avatar'] ?? ''),
        ':featured' => (int)(bool)($data['featured'] ?? false),
        ':color' => sanitize($data['color'] ?? '#6366f1'),
        ':accent' => sanitize($data['accent'] ?? '#e0e7ff'),
        ':light_text' => sanitize($data['light_text'] ?? '#4338ca'),
        ':dark_bg' => sanitize($data['dark_bg'] ?? '#4338ca'),
        ':sort_order' => (int)($data['sort_order'] ?? 0),
        ':is_active' => (int)(bool)($data['is_active'] ?? true),
    ]);
    success(['id' => $db->lastInsertId()], 'Case study created');
}

if ($method === 'PUT') {
    if (!$id) error('ID required');
    $data = body();
    $fields = [];
    $params = [':id' => $id];
    $map = [
        'slug' => 'slug',
        'title' => 'title',
        'tagline' => 'tagline',
        'category' => 'category',
        'image' => 'image',
        'logo' => 'logo',
        'client' => 'client',
        'duration' => 'duration',
        'year' => 'year',
        'challenge' => 'challenge',
        'solution' => 'solution',
        'result' => 'result',
        'testimonial_quote' => 'testimonial_quote',
        'testimonial_author' => 'testimonial_author',
        'testimonial_role' => 'testimonial_role',
        'testimonial_avatar' => 'testimonial_avatar',
        'featured' => 'featured',
        'color' => 'color',
        'accent' => 'accent',
        'light_text' => 'light_text',
        'dark_bg' => 'dark_bg',
        'sort_order' => 'sort_order',
        'is_active' => 'is_active',
    ];

    foreach ($map as $key => $column) {
        if (array_key_exists($key, $data)) {
            $fields[] = "`$column` = :$key";
            $params[":$key"] = in_array($key, ['featured', 'sort_order', 'is_active'], true)
                ? (int)$data[$key]
                : ($key === 'slug' ? sanitize((string)$data[$key]) : casePlainText($data[$key]));
        }
    }

    foreach (['tags' => 'tags_json', 'tech' => 'tech_json', 'metrics' => 'metrics_json', 'deliverables' => 'deliverables_json'] as $key => $column) {
        if (array_key_exists($key, $data)) {
            $fields[] = "`$column` = :$column";
            $params[":$column"] = caseJsonList($data[$key]);
        }
    }

    if ($fields) {
        $db->prepare("UPDATE case_studies SET " . implode(', ', $fields) . " WHERE id = :id")->execute($params);
    }
    success(null, 'Case study updated');
}

if ($method === 'DELETE') {
    if (!$id) error('ID required');
    $db->prepare("DELETE FROM case_studies WHERE id = ?")->execute([$id]);
    success(null, 'Case study deleted');
}

error('Invalid request', 400);