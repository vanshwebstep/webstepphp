<?php
require_once __DIR__ . '/../includes/helpers.php';

$db = getDB();

function decodeCaseText(mixed $value): string {
    return html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function decodeCaseValue(mixed $value): mixed {
    if (is_array($value)) {
        return array_map('decodeCaseValue', $value);
    }
    return decodeCaseText($value);
}

$categories = $db->query("
    SELECT name
    FROM case_study_categories
    WHERE is_active = 1
    ORDER BY sort_order ASC, name ASC
")->fetchAll(PDO::FETCH_COLUMN);
$categories = decodeCaseValue($categories);

$stmt = $db->query("
    SELECT *
    FROM case_studies
    WHERE is_active = 1
    ORDER BY featured DESC, sort_order ASC, id ASC
");

$caseStudies = array_map(function ($row) {
    return [
        'id' => decodeCaseText($row['slug']),
        'title' => decodeCaseText($row['title']),
        'tagline' => decodeCaseText($row['tagline']),
        'category' => decodeCaseText($row['category']),
        'tags' => decodeCaseValue(json_decode($row['tags_json'] ?: '[]', true) ?: []),
        'img' => decodeCaseText($row['image']),
        'logo' => decodeCaseText($row['logo']),
        'client' => decodeCaseText($row['client']),
        'duration' => decodeCaseText($row['duration']),
        'year' => decodeCaseText($row['year']),
        'tech' => decodeCaseValue(json_decode($row['tech_json'] ?: '[]', true) ?: []),
        'challenge' => decodeCaseText($row['challenge']),
        'solution' => decodeCaseText($row['solution']),
        'result' => decodeCaseText($row['result']),
        'metrics' => decodeCaseValue(json_decode($row['metrics_json'] ?: '[]', true) ?: []),
        'deliverables' => decodeCaseValue(json_decode($row['deliverables_json'] ?: '[]', true) ?: []),
        'testimonial' => [
            'quote' => decodeCaseText($row['testimonial_quote']),
            'author' => decodeCaseText($row['testimonial_author']),
            'role' => decodeCaseText($row['testimonial_role']),
            'avatar' => decodeCaseText($row['testimonial_avatar']),
        ],
        'featured' => (bool)$row['featured'],
        'color' => $row['color'],
        'accent' => $row['accent'],
        'lightText' => $row['light_text'],
        'darkBg' => $row['dark_bg'],
    ];
}, $stmt->fetchAll());

respond([
    'categories' => $categories ?: ['All'],
    'caseStudies' => $caseStudies,
]);
