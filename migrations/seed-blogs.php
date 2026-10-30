<?php
require_once 'F:/xampp/htdocs/webstepphp/config/db.php';

$db = getDB();

$db->exec("
CREATE TABLE IF NOT EXISTS `blog_categories` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(100) NOT NULL UNIQUE,
    `sort_order` INT DEFAULT 0,
    `is_active`  TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$db->exec("
CREATE TABLE IF NOT EXISTS `blogs` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title`           VARCHAR(255) NOT NULL,
    `slug`            VARCHAR(200) NOT NULL UNIQUE,
    `category`        VARCHAR(100) NOT NULL,
    `author`          VARCHAR(150) DEFAULT 'Webstep Solutions Team',
    `author_bio`      TEXT DEFAULT NULL,
    `author_avatar`   VARCHAR(255) DEFAULT NULL,
    `image`           VARCHAR(255) DEFAULT NULL,
    `excerpt`         TEXT DEFAULT NULL,
    `content`         LONGTEXT DEFAULT NULL,
    `tags`            TEXT DEFAULT NULL,
    `published_date`  DATE DEFAULT NULL,
    `read_time`       VARCHAR(50) DEFAULT '5 min read',
    `views`           INT UNSIGNED DEFAULT 0,
    `likes`           INT UNSIGNED DEFAULT 0,
    `featured`        TINYINT(1) DEFAULT 0,
    `sort_order`      INT DEFAULT 0,
    `is_active`       TINYINT(1) DEFAULT 1,
    `seo_title`       VARCHAR(255) DEFAULT NULL,
    `seo_description` TEXT DEFAULT NULL,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// Categories
$categories = ['Web Development', 'eCommerce', 'AI & Automation', 'Tech Strategy'];
foreach ($categories as $idx => $cat) {
    $db->prepare("INSERT IGNORE INTO `blog_categories` (`name`, `sort_order`) VALUES (?, ?)")->execute([$cat, $idx + 1]);
}

$blogs = [
    [
        'title' => 'React vs Next.js: Which One Should You Choose for Your 2025 Web Project?',
        'slug' => 'react-vs-nextjs-2025',
        'category' => 'Web Development',
        'author' => 'Webstep Solutions Team',
        'author_bio' => 'Senior engineers at Webstep Solutions specializing in React, Next.js, and high-performance frontend architecture.',
        'author_avatar' => 'assets/img/team1.jpg',
        'image' => 'blog1.png',
        'excerpt' => 'A practical comparison between pure client-side React and full-stack Next.js with App Router, SSR, and SEO considerations for 2025.',
        'content' => '<p>Choosing between standalone React and Next.js depends largely on whether your web application requires public search indexing, fast initial paint times, and server-side rendering.</p><h2>When to Choose Next.js</h2><p>Next.js is the premier choice for public-facing websites, marketing platforms, and content-rich SaaS where Core Web Vitals and organic SEO are crucial for business acquisition.</p><h2>When Pure React Works</h2><p>Single-page applications (SPAs) behind authentication logins, internal CRM dashboards, and admin portals often benefit from standard React without server rendering overhead.</p>',
        'tags' => json_encode(['React', 'Next.js', 'Frontend', 'SEO']),
        'published_date' => '2026-01-15',
        'read_time' => '6 min read',
        'featured' => 1,
        'sort_order' => 1,
        'seo_title' => 'React vs Next.js in 2025 | Webstep Solutions Guide',
        'seo_description' => 'Comprehensive technical breakdown of React vs Next.js for modern web applications.',
    ],
    [
        'title' => 'Best eCommerce Platform for SEO: Shopify vs WooCommerce',
        'slug' => 'best-ecommerce-platform-seo',
        'category' => 'eCommerce',
        'author' => 'Webstep Solutions Team',
        'author_bio' => 'eCommerce development specialists helping brands build high-converting online stores.',
        'author_avatar' => 'assets/img/team2.jpg',
        'image' => 'blog2.png',
        'excerpt' => 'How Shopify and WooCommerce stack up for technical SEO, URL structures, schema markup, and Core Web Vitals performance.',
        'content' => '<p>When evaluating an eCommerce engine, search engine discoverability is just as critical as checkout speed. Both platforms offer strong organic SEO capabilities, but with different control models.</p><h2>WooCommerce SEO Strengths</h2><p>WooCommerce gives you 100% control over URL rewriting, server-side caching, deep schema integration, and open database access.</p><h2>Shopify SEO Benefits</h2><p>Shopify provides managed global CDN infrastructure, automated robots.txt generation, fast canonical management, and reliable hosting uptime.</p>',
        'tags' => json_encode(['eCommerce', 'Shopify', 'WooCommerce', 'SEO']),
        'published_date' => '2026-02-10',
        'read_time' => '7 min read',
        'featured' => 1,
        'sort_order' => 2,
        'seo_title' => 'Shopify vs WooCommerce for SEO | Webstep Solutions Guide',
        'seo_description' => 'Detailed analysis of search engine performance between Shopify and WooCommerce.',
    ],
    [
        'title' => 'Shopify vs Custom Laravel for eCommerce: What Growing Brands Need to Know',
        'slug' => 'shopify-vs-laravel-ecommerce',
        'category' => 'eCommerce',
        'author' => 'Webstep Solutions Team',
        'author_bio' => 'Enterprise architecture team at Webstep Solutions.',
        'author_avatar' => 'assets/img/team3.jpg',
        'image' => 'blog3.png',
        'excerpt' => 'When out-of-the-box Shopify hits its limits and a custom Laravel headless architecture becomes necessary for complex catalogs and B2B pricing.',
        'content' => '<p>Standard retail DTC stores thrive on Shopify, but brands requiring tiered wholesale pricing, ERP synchronization, and complex product configurators often graduate to custom Laravel architectures.</p>',
        'tags' => json_encode(['Shopify', 'Laravel', 'PHP', 'Architecture']),
        'published_date' => '2026-03-01',
        'read_time' => '8 min read',
        'featured' => 0,
        'sort_order' => 3,
        'seo_title' => 'Shopify vs Custom Laravel eCommerce | Webstep Solutions',
        'seo_description' => 'Architectural comparison between Shopify and bespoke Laravel stores.',
    ],
    [
        'title' => 'How AI Chatbots Are Transforming 24/7 Customer Support for Modern Websites',
        'slug' => 'ai-chatbots-customer-support-2025',
        'category' => 'AI & Automation',
        'author' => 'Webstep Solutions Team',
        'author_bio' => 'AI engineering specialists building conversational agents and workflow automations.',
        'author_avatar' => 'assets/img/team4.jpg',
        'image' => 'blog4.png',
        'excerpt' => 'Learn how integrating custom RAG-powered AI chatbots qualifies leads, resolves user tickets, and operates 24/7 without growing support payroll.',
        'content' => '<p>Modern AI chatbots powered by Retrieval-Augmented Generation (RAG) read your documentation, company services, and pricing models to answer nuanced client inquiries instantly.</p>',
        'tags' => json_encode(['AI', 'Chatbots', 'Customer Support', 'Automation']),
        'published_date' => '2026-03-20',
        'read_time' => '5 min read',
        'featured' => 0,
        'sort_order' => 4,
        'seo_title' => 'AI Chatbots for Customer Support | Webstep Solutions',
        'seo_description' => 'How to implement intelligent AI agents to automate website inquiries and lead qualification.',
    ]
];

$stmt = $db->prepare("
INSERT INTO `blogs` 
    (`title`, `slug`, `category`, `author`, `author_bio`, `author_avatar`, `image`, `excerpt`, `content`, `tags`, `published_date`, `read_time`, `featured`, `sort_order`, `is_active`, `seo_title`, `seo_description`)
VALUES 
    (:title, :slug, :category, :author, :author_bio, :author_avatar, :image, :excerpt, :content, :tags, :published_date, :read_time, :featured, :sort_order, 1, :seo_title, :seo_description)
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `category` = VALUES(`category`),
    `author` = VALUES(`author`),
    `excerpt` = VALUES(`excerpt`),
    `content` = VALUES(`content`),
    `tags` = VALUES(`tags`),
    `read_time` = VALUES(`read_time`),
    `seo_title` = VALUES(`seo_title`),
    `seo_description` = VALUES(`seo_description`);
");

foreach ($blogs as $b) {
    $stmt->execute([
        ':title' => $b['title'],
        ':slug' => $b['slug'],
        ':category' => $b['category'],
        ':author' => $b['author'],
        ':author_bio' => $b['author_bio'],
        ':author_avatar' => $b['author_avatar'],
        ':image' => $b['image'],
        ':excerpt' => $b['excerpt'],
        ':content' => $b['content'],
        ':tags' => $b['tags'],
        ':published_date' => $b['published_date'],
        ':read_time' => $b['read_time'],
        ':featured' => $b['featured'],
        ':sort_order' => $b['sort_order'],
        ':seo_title' => $b['seo_title'],
        ':seo_description' => $b['seo_description'],
    ]);
}

echo "Blogs table migrated and 4 articles seeded successfully!\n";
