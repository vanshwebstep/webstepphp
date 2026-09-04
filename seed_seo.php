<?php
require_once __DIR__ . '/config/db.php';

$db = getDB();

// Create table if it doesn't exist
$db->exec("
    CREATE TABLE IF NOT EXISTS seo_settings (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        page_url VARCHAR(150) NOT NULL UNIQUE,
        title VARCHAR(255) DEFAULT NULL,
        description TEXT DEFAULT NULL,
        keywords TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;
");

$seoData = [
    [
        'page_url' => '/',
        'title' => 'Webstep Solutions | Top Enterprise Software & App Development Company',
        'description' => 'Webstep Solutions is a leading software development agency specializing in scalable enterprise web applications, mobile apps, e-commerce, and custom software.',
        'keywords' => 'software development company, custom app development, enterprise software, web development agency'
    ],
    [
        'page_url' => 'about',
        'title' => 'About Webstep Solutions | Our Mission & Expert IT Team',
        'description' => 'Learn about Webstep Solutions, our core values, and the talented team of developers and designers driving digital transformation globally.',
        'keywords' => 'about webstep solutions, IT company profile, software team, top IT agency'
    ],
    [
        'page_url' => 'services',
        'title' => 'Custom Software & IT Services | Webstep Solutions',
        'description' => 'Explore our full suite of IT services including full-stack web development, mobile applications, UI/UX design, SEO, and digital marketing.',
        'keywords' => 'IT services, custom software development services, mobile app services, web development services'
    ],
    [
        'page_url' => 'contactus',
        'title' => 'Contact Webstep Solutions | Get a Free Project Quote',
        'description' => 'Ready to build your next big project? Contact the Webstep Solutions team today for a free consultation and project estimation.',
        'keywords' => 'contact software company, hire developers, get IT quote, contact webstep'
    ],
    [
        'page_url' => 'blog',
        'title' => 'Tech Blog & Insights | Webstep Solutions',
        'description' => 'Read the latest trends, tutorials, and insights on web development, mobile apps, AI, and digital marketing from our expert engineers.',
        'keywords' => 'technology blog, web development articles, software engineering insights'
    ],
    [
        'page_url' => 'packages',
        'title' => 'Affordable Web & App Development Packages | Webstep Solutions',
        'description' => 'View our transparent pricing and development packages for websites, e-commerce stores, and mobile apps designed to fit any business size.',
        'keywords' => 'web development packages, app development cost, ecommerce pricing, IT agency packages'
    ],
    [
        'page_url' => 'case-study',
        'title' => 'Client Success Stories & Case Studies | Webstep Solutions',
        'description' => 'Discover how we have helped businesses scale and succeed with custom software, scalable infrastructure, and modern design.',
        'keywords' => 'software case studies, IT success stories, web development portfolio'
    ],
    [
        'page_url' => 'works',
        'title' => 'Our Portfolio & Recent Projects | Webstep Solutions',
        'description' => 'Browse our extensive portfolio of high-performance websites, mobile apps, and enterprise platforms delivered globally.',
        'keywords' => 'software portfolio, web development projects, app developer portfolio'
    ],
    [
        'page_url' => 'services/ui-ux',
        'title' => 'Professional UI/UX Design Services | Webstep Solutions',
        'description' => 'We design intuitive, user-centric interfaces and experiences that increase engagement and conversions for web and mobile applications.',
        'keywords' => 'UI UX design company, user interface design, user experience agency, wireframing'
    ],
    [
        'page_url' => 'services/php',
        'title' => 'Expert PHP Development Services | Webstep Solutions',
        'description' => 'Hire top PHP developers for secure, scalable, and high-performing backend systems, custom APIs, and web applications.',
        'keywords' => 'PHP development company, hire PHP developers, backend development services'
    ],
    [
        'page_url' => 'services/nodejs',
        'title' => 'Node.js Development Company | Fast & Scalable APIs',
        'description' => 'Leverage the power of Node.js for ultra-fast, event-driven backend architectures and real-time enterprise applications.',
        'keywords' => 'Node.js development services, hire Node developers, real-time app development'
    ],
    [
        'page_url' => 'services/laravel',
        'title' => 'Laravel Development Services & Solutions | Webstep Solutions',
        'description' => 'Build robust and elegant web applications using Laravel. We offer custom Laravel development, API integration, and enterprise solutions.',
        'keywords' => 'Laravel development company, hire Laravel experts, custom PHP framework'
    ],
    [
        'page_url' => 'services/seo',
        'title' => 'SEO & Digital Marketing Services | Webstep Solutions',
        'description' => 'Boost your online visibility with our data-driven SEO strategies, keyword optimization, and technical SEO implementations.',
        'keywords' => 'SEO agency, technical SEO services, search engine optimization, digital marketing'
    ],
    [
        'page_url' => 'hire-full-time-developers',
        'title' => 'Hire Full-Time Dedicated Developers | Webstep Solutions',
        'description' => 'Scale your tech team quickly by hiring our pre-vetted, full-time dedicated developers for React, Node, PHP, Laravel, and mobile apps.',
        'keywords' => 'hire dedicated developers, full-time programmers, offshore development team'
    ]
];

$inserted = 0;
$updated = 0;

foreach ($seoData as $data) {
    try {
        // Check if exists
        $stmt = $db->prepare("SELECT id FROM seo_settings WHERE page_url = ?");
        $stmt->execute([$data['page_url']]);
        $exists = $stmt->fetch();

        if ($exists) {
            $update = $db->prepare("UPDATE seo_settings SET title = ?, description = ?, keywords = ? WHERE page_url = ?");
            $update->execute([$data['title'], $data['description'], $data['keywords'], $data['page_url']]);
            $updated++;
        } else {
            $insert = $db->prepare("INSERT INTO seo_settings (page_url, title, description, keywords) VALUES (?, ?, ?, ?)");
            $insert->execute([$data['page_url'], $data['title'], $data['description'], $data['keywords']]);
            $inserted++;
        }
    } catch (PDOException $e) {
        echo "Error on " . $data['page_url'] . ": " . $e->getMessage() . "\n";
    }
}

echo "SEO Seeding Complete!\n";
echo "Inserted: $inserted\n";
echo "Updated: $updated\n";
