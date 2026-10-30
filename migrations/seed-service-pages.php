<?php
require_once __DIR__ . '/../config/db.php';

$db = getDB();

$db->exec("
CREATE TABLE IF NOT EXISTS service_pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL UNIQUE,
    title VARCHAR(180) NOT NULL,
    content_json LONGTEXT NOT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

function pageContent(string $type): array {
    if ($type === 'industrial') {
        return [
            'hero' => [
                'eyebrow' => 'Career-ready training',
                'title' => 'Industrial Training That Turns Students Into Job-Ready Builders',
                'subtitle' => 'A hands-on program for web development, UI/UX, app development, backend engineering, and real client workflows. Learners work like a product team, not like a classroom batch.',
                'primaryCta' => 'Apply For Training',
                'secondaryCta' => 'See Our Work',
                'panelTitle' => 'Studio-style learning',
                'panelItems' => ['Live projects, Git workflow, code reviews, and sprint planning', 'Portfolio-ready assignments with deployment and documentation', 'Mentor feedback focused on industry standards and interview readiness'],
            ],
            'metrics' => [['value' => '8-24w', 'label' => 'Programs'], ['value' => '1:1', 'label' => 'Mentor Reviews'], ['value' => 'Live', 'label' => 'Project Work'], ['value' => 'Cert.', 'label' => 'Completion']],
            'sections' => [
                ['icon' => 'code', 'title' => 'Full-stack tracks', 'description' => 'Structured learning paths for frontend, backend, WordPress, Laravel, React, Next.js, and API integration.', 'points' => ['HTML/CSS/JS foundations', 'React and Next.js projects', 'PHP/Laravel and database basics']],
                ['icon' => 'layers', 'title' => 'Project-based curriculum', 'description' => 'Every module ends with a real deliverable so students build confidence through visible output.', 'points' => ['Landing pages', 'Dashboards', 'CMS and admin flows']],
                ['icon' => 'users', 'title' => 'Mentor-led reviews', 'description' => 'Students get direct feedback on code quality, UI polish, debugging approach, and communication.', 'points' => ['Weekly review calls', 'Code cleanup tasks', 'Interview preparation']],
                ['icon' => 'shield', 'title' => 'Professional workflow', 'description' => 'Training includes the habits companies expect from junior developers on day one.', 'points' => ['Git branches', 'Task breakdown', 'Deployment checklist']],
                ['icon' => 'trend', 'title' => 'Portfolio outcomes', 'description' => 'Learners leave with polished project proof, not only notes and certificates.', 'points' => ['Case-study format', 'Hosted projects', 'Resume-ready summaries']],
                ['icon' => 'clock', 'title' => 'Flexible batches', 'description' => 'Short and long-term tracks can be managed for colleges, groups, and individual learners.', 'points' => ['Online/offline mode', 'Weekend options', 'Custom duration']],
            ],
            'processTitle' => 'From fundamentals to production behavior',
            'processIntro' => 'The program is designed around execution: learn the concept, build the feature, review the output, then improve it.',
            'process' => [['title' => 'Skill audit', 'description' => 'We map the current level and choose the right track.'], ['title' => 'Core bootcamp', 'description' => 'Foundations are reinforced through focused tasks and small builds.'], ['title' => 'Live project sprint', 'description' => 'Students work on structured project modules with mentor reviews.'], ['title' => 'Portfolio and interview prep', 'description' => 'Final deliverables are packaged for resumes, interviews, and demos.']],
            'pricingTitle' => 'Training formats',
            'pricingIntro' => 'Programs can be configured for individuals, college batches, and company-sponsored upskilling.',
            'pricing' => [['title' => 'Foundation', 'price' => 'Custom', 'description' => 'Best for beginners who need strong basics.', 'items' => ['8 week track', 'Core web stack', 'Mini projects']], ['title' => 'Professional', 'price' => 'Custom', 'description' => 'Best for students who want portfolio-ready proof.', 'items' => ['12-16 week track', 'Live project', 'Code reviews']], ['title' => 'College Batch', 'price' => 'Custom', 'description' => 'Structured program for institutes and groups.', 'items' => ['Batch dashboard', 'Attendance reports', 'Certificate support']]],
            'proofTitle' => 'Built like a small software studio',
            'proof' => ['Production-style assignments', 'Mentor feedback on UI and code', 'Deployment and documentation included', 'Admin/project workflows included'],
            'faqs' => [['q' => 'Do students need prior coding experience?', 'a' => 'No. We can run beginner, intermediate, or advanced tracks depending on the batch level.'], ['q' => 'Will students work on live projects?', 'a' => 'Yes. The program includes practical project work with deployment and review checkpoints.'], ['q' => 'Can this be customized for a college?', 'a' => 'Yes. Duration, schedule, tracks, reports, and certificate format can be adjusted.']],
        ];
    }

    return [
        'hero' => [
            'eyebrow' => 'Dedicated talent',
            'title' => 'Hire Dedicated Developers for Your Web Projects',
            'subtitle' => 'Add vetted React, Laravel, WordPress, and Shopify developers to your team with managed delivery support, clean reporting, and flexible monthly engagement.',
            'primaryCta' => 'Hire Developers',
            'secondaryCta' => 'Explore Portfolio',
            'panelTitle' => 'Dedicated remote team',
            'panelItems' => ['Frontend, backend, full-stack, WordPress, Laravel, Shopify, and app developers', 'Daily communication, task boards, code reviews, and delivery reporting', 'Flexible monthly hiring for startups, agencies, and growing businesses'],
        ],
        'metrics' => [['value' => '7+', 'label' => 'Tech Stacks'], ['value' => 'Full', 'label' => 'Monthly Hire'], ['value' => 'Agile', 'label' => 'Delivery'], ['value' => 'NDA', 'label' => 'Ready']],
        'sections' => [
            ['icon' => 'users', 'title' => 'Dedicated developers', 'description' => 'Hire focused engineers who work as an extension of your internal product team.', 'points' => ['Full-time monthly model', 'Direct communication', 'Task ownership']],
            ['icon' => 'code', 'title' => 'Modern tech stack', 'description' => 'Get specialists across React, Next.js, Node.js, PHP, Laravel, WordPress, Shopify, and mobile app development.', 'points' => ['Frontend engineers', 'Backend engineers', 'CMS/eCommerce experts']],
            ['icon' => 'shield', 'title' => 'Managed quality', 'description' => 'We keep delivery controlled through code reviews, QA checks, and weekly reporting.', 'points' => ['Review checkpoints', 'Bug tracking', 'Deployment support']],
            ['icon' => 'layers', 'title' => 'Flexible team pods', 'description' => 'Scale from one developer to a small pod with UI/UX, QA, backend, and project coordination.', 'points' => ['Solo developer', 'Developer + QA', 'Product pod']],
            ['icon' => 'trend', 'title' => 'Faster roadmap', 'description' => 'Use dedicated hiring to clear backlogs, build MVPs, support client projects, or maintain products.', 'points' => ['MVP builds', 'Feature sprints', 'Maintenance teams']],
            ['icon' => 'clock', 'title' => 'Quick onboarding', 'description' => 'We align on scope, tools, communication, and sprint rhythm before the developer starts.', 'points' => ['Tool setup', 'Access checklist', 'Weekly planning']],
        ],
        'processTitle' => 'A hiring model with delivery discipline',
        'processIntro' => 'You get the speed of staff augmentation with the operational discipline of a development studio.',
        'process' => [['title' => 'Requirement mapping', 'description' => 'We define role, stack, seniority, working hours, and success metrics.'], ['title' => 'Profile shortlisting', 'description' => 'You review matching developers and select the best fit.'], ['title' => 'Onboarding sprint', 'description' => 'We set up tools, access, task board, and first sprint priorities.'], ['title' => 'Monthly delivery rhythm', 'description' => 'You receive consistent updates, reviews, and improvement loops.']],
        'pricingTitle' => 'Hiring options',
        'pricingIntro' => 'Choose a model based on workload, management needs, and product complexity.',
        'pricing' => [['title' => 'Single Developer', 'price' => 'Monthly', 'description' => 'Best for focused product backlog work.', 'items' => ['Full-time developer', 'Daily updates', 'Code review support']], ['title' => 'Developer Pod', 'price' => 'Monthly', 'description' => 'Best for feature-heavy products and agencies.', 'items' => ['Developer + QA', 'Project coordination', 'Sprint reporting']], ['title' => 'Managed Team', 'price' => 'Custom', 'description' => 'Best for long-term product development.', 'items' => ['Multi-role team', 'Delivery management', 'Release planning']]],
        'proofTitle' => 'Built for teams that need output, not excuses',
        'proof' => ['NDA-ready engagement', 'Daily and weekly reporting', 'Code ownership and documentation', 'Scalable team structure'],
        'faqs' => [['q' => 'Can we interview the developer first?', 'a' => 'Yes. You can review profiles and interview shortlisted developers before starting.'], ['q' => 'Can we hire for one month only?', 'a' => 'Yes. Monthly hiring is supported, and longer engagements can be planned when needed.'], ['q' => 'Do you provide project management?', 'a' => 'Yes. You can hire only a developer or choose a managed pod with coordination and QA.']],
    ];
}

$pages = [
    ['slug' => 'industrial-training', 'title' => 'Industrial Training', 'sort_order' => 1, 'content' => pageContent('industrial')],
    ['slug' => 'hire-dedicated-developers', 'title' => 'Hire Dedicated Developers', 'sort_order' => 2, 'content' => pageContent('hire')],
    ['slug' => 'hire-full-time-developers', 'title' => 'Hire Dedicated Developers', 'sort_order' => 3, 'content' => pageContent('hire')],
];

$stmt = $db->prepare("
INSERT INTO service_pages (slug, title, content_json, sort_order, is_active)
VALUES (:slug, :title, :content, :sort_order, 1)
ON DUPLICATE KEY UPDATE title = VALUES(title), content_json = VALUES(content_json), sort_order = VALUES(sort_order), is_active = 1
");

foreach ($pages as $page) {
    $stmt->execute([
        ':slug' => $page['slug'],
        ':title' => $page['title'],
        ':content' => json_encode($page['content'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ':sort_order' => $page['sort_order'],
    ]);
}

echo "Service pages migrated and seeded successfully!\n";
