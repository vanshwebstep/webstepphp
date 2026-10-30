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

// Drop accidental unique index on title if present from previous schemas
try {
    $db->exec("ALTER TABLE seo_settings DROP INDEX title;");
} catch (Exception $e) {
    // Index might not exist, ignore
}

$seoData = [
    // Home
    [
        'page_url' => '/',
        'title' => 'Web Development Company & Dedicated Developers | Webstep',
        'description' => 'Custom WordPress, Shopify, React and Laravel development for agencies and growing businesses. Hire dedicated developers monthly. Request a quote.',
        'keywords' => 'web development company, dedicated developers, custom WordPress development, Shopify development, React development, Laravel development'
    ],
    [
        'page_url' => 'home',
        'title' => 'Web Development Company & Dedicated Developers | Webstep',
        'description' => 'Custom WordPress, Shopify, React and Laravel development for agencies and growing businesses. Hire dedicated developers monthly. Request a quote.',
        'keywords' => 'web development company, dedicated developers, custom WordPress development, Shopify development, React development, Laravel development'
    ],

    // Services page
    [
        'page_url' => 'services',
        'title' => 'Web Development Services | WordPress, Shopify, Next.js',
        'description' => 'Web development services covering WordPress, Shopify, WooCommerce, Next.js and Laravel, plus API integrations. See what we build and request a quote.',
        'keywords' => 'web development services, WordPress, Shopify, WooCommerce, Next.js, Laravel, API integrations'
    ],

    // Web Development
    [
        'page_url' => 'web-development',
        'title' => 'Custom Web Development Services | Next.js, React, Laravel',
        'description' => 'Custom websites and web apps built on Next.js, React and Laravel. Fast, SEO-ready code from a dedicated team. Tell us about your project.',
        'keywords' => 'custom web development services, Next.js, React, Laravel, fast website development, SEO ready code'
    ],
    [
        'page_url' => 'services/web-development',
        'title' => 'Custom Web Development Services | Next.js, React, Laravel',
        'description' => 'Custom websites and web apps built on Next.js, React and Laravel. Fast, SEO-ready code from a dedicated team. Tell us about your project.',
        'keywords' => 'custom web development services, Next.js, React, Laravel, fast website development, SEO ready code'
    ],

    // WordPress
    [
        'page_url' => 'wordpress',
        'title' => 'WordPress Development Services | Custom Themes & Plugins',
        'description' => 'Hand-coded WordPress themes, custom plugins and headless builds from your Figma designs. No page-builder bloat. Request a WordPress quote.',
        'keywords' => 'WordPress development services, custom themes, custom plugins, headless WordPress, Figma to WordPress'
    ],
    [
        'page_url' => 'services/wordpress',
        'title' => 'WordPress Development Services | Custom Themes & Plugins',
        'description' => 'Hand-coded WordPress themes, custom plugins and headless builds from your Figma designs. No page-builder bloat. Request a WordPress quote.',
        'keywords' => 'WordPress development services, custom themes, custom plugins, headless WordPress, Figma to WordPress'
    ],

    // WooCommerce
    [
        'page_url' => 'woocommerce',
        'title' => 'WooCommerce Development Services | Custom Online Stores',
        'description' => 'Custom WooCommerce stores with tailored product types, checkout flows and payment gateways. Migrations and plugin development included.',
        'keywords' => 'WooCommerce development services, custom online stores, WooCommerce checkout, payment gateways, WooCommerce migration'
    ],
    [
        'page_url' => 'services/woocommerce',
        'title' => 'WooCommerce Development Services | Custom Online Stores',
        'description' => 'Custom WooCommerce stores with tailored product types, checkout flows and payment gateways. Migrations and plugin development included.',
        'keywords' => 'WooCommerce development services, custom online stores, WooCommerce checkout, payment gateways, WooCommerce migration'
    ],

    // Shopify
    [
        'page_url' => 'shopify',
        'title' => 'Shopify Development Services | Custom Themes & Apps',
        'description' => 'Custom Shopify themes, Liquid development and private apps for brands and agencies. Shopify Plus and store migrations supported. Get a quote.',
        'keywords' => 'Shopify development services, custom Shopify themes, Liquid development, private apps, Shopify Plus, store migration'
    ],
    [
        'page_url' => 'services/shopify',
        'title' => 'Shopify Development Services | Custom Themes & Apps',
        'description' => 'Custom Shopify themes, Liquid development and private apps for brands and agencies. Shopify Plus and store migrations supported. Get a quote.',
        'keywords' => 'Shopify development services, custom Shopify themes, Liquid development, private apps, Shopify Plus, store migration'
    ],

    // eCommerce
    [
        'page_url' => 'ecommerce',
        'title' => 'eCommerce Development Company | Shopify & WooCommerce',
        'description' => 'Build or migrate your online store on Shopify, WooCommerce or a headless setup. Conversion-focused design, ERP and POS integrations, ongoing support.',
        'keywords' => 'eCommerce development company, Shopify, WooCommerce, headless eCommerce, ERP integration, POS integration'
    ],
    [
        'page_url' => 'services/ecommerce',
        'title' => 'eCommerce Development Company | Shopify & WooCommerce',
        'description' => 'Build or migrate your online store on Shopify, WooCommerce or a headless setup. Conversion-focused design, ERP and POS integrations, ongoing support.',
        'keywords' => 'eCommerce development company, Shopify, WooCommerce, headless eCommerce, ERP integration, POS integration'
    ],

    // Laravel
    [
        'page_url' => 'laravel',
        'title' => 'Laravel Development Company | Custom PHP Web Apps',
        'description' => 'Secure Laravel applications, REST APIs and custom PHP development, from MVPs to systems built to scale. Talk to a Laravel developer.',
        'keywords' => 'Laravel development company, custom PHP web apps, REST APIs, Laravel developers'
    ],
    [
        'page_url' => 'services/laravel',
        'title' => 'Laravel Development Company | Custom PHP Web Apps',
        'description' => 'Secure Laravel applications, REST APIs and custom PHP development, from MVPs to systems built to scale. Talk to a Laravel developer.',
        'keywords' => 'Laravel development company, custom PHP web apps, REST APIs, Laravel developers'
    ],

    // Hire Developers
    [
        'page_url' => 'hire-full-time-developers',
        'title' => 'Hire Dedicated Developers | Monthly Remote Teams | Webstep',
        'description' => 'Hire vetted React, Laravel, WordPress and Shopify developers monthly. Work with them directly, with QA and weekly reporting. NDA-ready.',
        'keywords' => 'hire dedicated developers, monthly remote teams, React developers, Laravel developers, WordPress developers, Shopify developers'
    ],

    // Website Maintenance & Support
    [
        'page_url' => 'website-maintenance',
        'title' => 'Website Maintenance & Support Services | Webstep',
        'description' => 'Ongoing website maintenance for WordPress, Shopify and custom sites: updates, security, bug fixes and speed checks. Ask about a monthly plan.',
        'keywords' => 'website maintenance services, support services, WordPress updates, Shopify maintenance, site security'
    ],
    [
        'page_url' => 'services/website-maintenance',
        'title' => 'Website Maintenance & Support Services | Webstep',
        'description' => 'Ongoing website maintenance for WordPress, Shopify and custom sites: updates, security, bug fixes and speed checks. Ask about a monthly plan.',
        'keywords' => 'website maintenance services, support services, WordPress updates, Shopify maintenance, site security'
    ],

    // Portfolio
    [
        'page_url' => 'works',
        'title' => 'Web Development Portfolio | Webstep Solutions',
        'description' => 'Recent projects built with React, Next.js, Laravel and WordPress for healthcare, SaaS, infrastructure and non-profit clients. See the results.',
        'keywords' => 'web development portfolio, React projects, Next.js projects, Laravel case studies'
    ],
    [
        'page_url' => 'projects',
        'title' => 'Web Development Portfolio | Webstep Solutions',
        'description' => 'Recent projects built with React, Next.js, Laravel and WordPress for healthcare, SaaS, infrastructure and non-profit clients. See the results.',
        'keywords' => 'web development portfolio, React projects, Next.js projects, Laravel case studies'
    ],

    // About
    [
        'page_url' => 'about',
        'title' => 'About Webstep | Web Development Team Since 2012',
        'description' => 'Mohali-based team with 12+ years building WordPress, Shopify and custom web apps for clients in 15+ countries. Meet the people behind Webstep.',
        'keywords' => 'about webstep, web development team, Mohali developers, 12 years experience'
    ],

    // Contact
    [
        'page_url' => 'contactus',
        'title' => 'Contact Webstep | Request a Web Development Quote',
        'description' => 'Tell us about your project or the developers you need. Share your requirements and we will reply within one business day.',
        'keywords' => 'contact webstep, request web development quote, hire web developers'
    ],
    [
        'page_url' => 'contact',
        'title' => 'Contact Webstep | Request a Web Development Quote',
        'description' => 'Tell us about your project or the developers you need. Share your requirements and we will reply within one business day.',
        'keywords' => 'contact webstep, request web development quote, hire web developers'
    ],

    // App Development
    [
        'page_url' => 'mobileApp',
        'title' => 'Mobile App Development Company | iOS & Android | Webstep',
        'description' => 'Native iOS apps in Swift and Android apps in Kotlin, from idea to App Store and Play Store launch. Share your app idea and get a quote.',
        'keywords' => 'mobile app development company, iOS apps, Android apps, Swift, Kotlin'
    ],
    [
        'page_url' => 'services/mobile-app',
        'title' => 'Mobile App Development Company | iOS & Android | Webstep',
        'description' => 'Native iOS apps in Swift and Android apps in Kotlin, from idea to App Store and Play Store launch. Share your app idea and get a quote.',
        'keywords' => 'mobile app development company, iOS apps, Android apps, Swift, Kotlin'
    ],

    // Full Stack Development
    [
        'page_url' => 'fullstack',
        'title' => 'Full Stack Development Services | Node.js, Vue & APIs',
        'description' => 'Full stack developers for Vue, Node.js and PHP projects, API integrations and AI chatbot builds. Get a clear scope and quote for your project.',
        'keywords' => 'full stack development services, Node.js, Vue.js, PHP APIs, AI chatbot builds'
    ],
    [
        'page_url' => 'services/fullstack',
        'title' => 'Full Stack Development Services | Node.js, Vue & APIs',
        'description' => 'Full stack developers for Vue, Node.js and PHP projects, API integrations and AI chatbot builds. Get a clear scope and quote for your project.',
        'keywords' => 'full stack development services, Node.js, Vue.js, PHP APIs, AI chatbot builds'
    ],

    // AI Chatbots
    [
        'page_url' => 'ai-chatbots',
        'title' => 'AI Chatbot Development Services | Custom Chatbots',
        'description' => 'Custom AI chatbots and API integrations that handle support questions, qualify leads and automate routine work. Tell us what you want to automate.',
        'keywords' => 'AI chatbot development services, custom chatbots, automated support, lead qualification'
    ],
    [
        'page_url' => 'services/ai-chatbots',
        'title' => 'AI Chatbot Development Services | Custom Chatbots',
        'description' => 'Custom AI chatbots and API integrations that handle support questions, qualify leads and automate routine work. Tell us what you want to automate.',
        'keywords' => 'AI chatbot development services, custom chatbots, automated support, lead qualification'
    ],

    // UI/UX Design
    [
        'page_url' => 'uiux',
        'title' => 'UI/UX Design Services | Web & App Design | Webstep',
        'description' => 'User-first interface design for websites and apps, from wireframes to approved Figma designs, ready for development. Request a design quote.',
        'keywords' => 'UI/UX design services, web design, app design, wireframes, Figma designs'
    ],
    [
        'page_url' => 'services/ui-ux',
        'title' => 'UI/UX Design Services | Web & App Design | Webstep',
        'description' => 'User-first interface design for websites and apps, from wireframes to approved Figma designs, ready for development. Request a design quote.',
        'keywords' => 'UI/UX design services, web design, app design, wireframes, Figma designs'
    ],

    // React & Next.js
    [
        'page_url' => 'services/reactjs',
        'title' => 'React & Next.js Development Services | Webstep',
        'description' => 'Fast, SEO-friendly web apps built with React and Next.js, with server-side rendering and clean, scalable code. Tell us about your project.',
        'keywords' => 'React & Next.js development services, server-side rendering, scalable web apps'
    ],
    [
        'page_url' => 'reactjs',
        'title' => 'React & Next.js Development Services | Webstep',
        'description' => 'Fast, SEO-friendly web apps built with React and Next.js, with server-side rendering and clean, scalable code. Tell us about your project.',
        'keywords' => 'React & Next.js development services, server-side rendering, scalable web apps'
    ],

    // PHP
    [
        'page_url' => 'php',
        'title' => 'PHP Development Services | Custom PHP & Laravel Apps',
        'description' => 'Custom PHP development, from web applications and APIs to upgrades of older systems. Talk to a PHP developer about your project.',
        'keywords' => 'PHP development services, custom PHP, Laravel apps, web API development'
    ],
    [
        'page_url' => 'services/php-development',
        'title' => 'PHP Development Services | Custom PHP & Laravel Apps',
        'description' => 'Custom PHP development, from web applications and APIs to upgrades of older systems. Talk to a PHP developer about your project.',
        'keywords' => 'PHP development services, custom PHP, Laravel apps, web API development'
    ],

    // Case Studies
    [
        'page_url' => 'case-study',
        'title' => 'Web Development Case Studies | Webstep',
        'description' => 'See how we planned, built and delivered web projects for real clients, with the problem, the tech used and the results.',
        'keywords' => 'web development case studies, client results, technology stack'
    ],

    // Packages
    [
        'page_url' => 'packages',
        'title' => 'Web Development Packages & Pricing | Webstep',
        'description' => 'Choose a clear web development package for your project, or customize one to fit your needs. See what is included and request a quote.',
        'keywords' => 'web development packages, web development pricing, transparent package pricing'
    ],

    // For Agencies
    [
        'page_url' => 'b2b-sales',
        'title' => 'Development Partner for Agencies | Webstep',
        'description' => 'A reliable development partner for design and marketing agencies. WordPress, Shopify and custom builds with NDA-ready engagement.',
        'keywords' => 'development partner for agencies, NDA ready engagement, agency white label'
    ],
    [
        'page_url' => 'for-agencies',
        'title' => 'Development Partner for Agencies | Webstep',
        'description' => 'A reliable development partner for design and marketing agencies. WordPress, Shopify and custom builds with NDA-ready engagement.',
        'keywords' => 'development partner for agencies, NDA ready engagement, agency white label'
    ],

    // Our Team
    [
        'page_url' => 'our-team',
        'title' => 'Our Team | Developers Behind Webstep Solutions',
        'description' => 'Meet the developers, designers and project managers who build and support your projects at Webstep Solutions.',
        'keywords' => 'our team, webstep developers, project managers, designers'
    ],
    [
        'page_url' => 'team',
        'title' => 'Our Team | Developers Behind Webstep Solutions',
        'description' => 'Meet the developers, designers and project managers who build and support your projects at Webstep Solutions.',
        'keywords' => 'our team, webstep developers, project managers, designers'
    ],

    // Blog
    [
        'page_url' => 'blog',
        'title' => 'Web Development Blog | Webstep Solutions',
        'description' => 'Practical guides on choosing platforms, hiring developers and building better websites, from the Webstep development team.',
        'keywords' => 'web development blog, tech guides, platform choices'
    ],

    // Get a Quote
    [
        'page_url' => 'customize-package',
        'title' => 'Get a Free Quote | Customize Your Project | Webstep',
        'description' => 'Tell us what you need built and get a clear quote. Choose your services and share your requirements in two minutes.',
        'keywords' => 'get a free quote, customize project, web development quote'
    ],

    // SEO Services
    [
        'page_url' => 'seo',
        'title' => 'SEO Services | Technical SEO & Core Web Vitals | Webstep',
        'description' => 'Technical SEO audits, Core Web Vitals fixes and conversion improvements for business websites. Request an SEO audit.',
        'keywords' => 'SEO services, technical SEO, Core Web Vitals, conversion improvements'
    ],
    [
        'page_url' => 'services/seo',
        'title' => 'SEO Services | Technical SEO & Core Web Vitals | Webstep',
        'description' => 'Technical SEO audits, Core Web Vitals fixes and conversion improvements for business websites. Request an SEO audit.',
        'keywords' => 'SEO services, technical SEO, Core Web Vitals, conversion improvements'
    ],

    // Social Media
    [
        'page_url' => 'social-media',
        'title' => 'Social Media Marketing Services | Webstep',
        'description' => 'Social media content and paid ads for businesses. Plan your campaigns with our team.',
        'keywords' => 'social media marketing services, paid ads, content campaigns'
    ],
    [
        'page_url' => 'services/social-media',
        'title' => 'Social Media Marketing Services | Webstep',
        'description' => 'Social media content and paid ads for businesses. Plan your campaigns with our team.',
        'keywords' => 'social media marketing services, paid ads, content campaigns'
    ],

    // Email Marketing
    [
        'page_url' => 'email-marketing',
        'title' => 'Email Marketing Services | Webstep',
        'description' => 'Email campaigns and automation that bring customers back. Tell us your goal and get a plan.',
        'keywords' => 'email marketing services, email campaigns, automation'
    ],
    [
        'page_url' => 'services/email-marketing',
        'title' => 'Email Marketing Services | Webstep',
        'description' => 'Email campaigns and automation that bring customers back. Tell us your goal and get a plan.',
        'keywords' => 'email marketing services, email campaigns, automation'
    ],

    // Brand Design
    [
        'page_url' => 'brand-design',
        'title' => 'Brand Design & Logo Design Services | Webstep',
        'description' => 'Logos and brand identity design for new and growing businesses. Share your brand idea and get a quote.',
        'keywords' => 'brand design, logo design services, brand identity'
    ],
    [
        'page_url' => 'services/brand-design',
        'title' => 'Brand Design & Logo Design Services | Webstep',
        'description' => 'Logos and brand identity design for new and growing businesses. Share your brand idea and get a quote.',
        'keywords' => 'brand design, logo design services, brand identity'
    ],

    // Industrial Training
    [
        'page_url' => 'industrial-training',
        'title' => 'Industrial Training in Web Development, Mohali | Webstep',
        'description' => 'Career-ready web development training programs in Mohali for students and freshers. Check the courses and apply.',
        'keywords' => 'industrial training Mohali, web development training, freshers training'
    ],

    // Privacy Policy
    [
        'page_url' => 'privacy',
        'title' => 'Privacy Policy | Webstep Solutions',
        'description' => 'Privacy Policy for Webstep Solutions.',
        'keywords' => 'privacy policy, webstep solutions'
    ],

    // Terms of Service
    [
        'page_url' => 'terms',
        'title' => 'Terms of Service | Webstep Solutions',
        'description' => 'Terms of Service for Webstep Solutions.',
        'keywords' => 'terms of service, webstep solutions'
    ]
];

$inserted = 0;
$updated = 0;

foreach ($seoData as $data) {
    try {
        $stmt = $db->prepare("
            INSERT INTO seo_settings (page_url, title, description, keywords) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), keywords = VALUES(keywords)
        ");
        $stmt->execute([$data['page_url'], $data['title'], $data['description'], $data['keywords']]);
        if ($stmt->rowCount() == 1) {
            $inserted++;
        } else if ($stmt->rowCount() == 2) {
            $updated++;
        }
    } catch (PDOException $e) {
        echo "Error on " . $data['page_url'] . ": " . $e->getMessage() . "\n";
    }
}

echo "SEO Seeding Complete!\n";
echo "Inserted: $inserted\n";
echo "Updated: $updated\n";
