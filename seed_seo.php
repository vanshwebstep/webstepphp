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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// Drop accidental unique index on title if present from previous schemas
try {
    $db->exec("ALTER TABLE seo_settings DROP INDEX title;");
} catch (Exception $e) {
    // Index might not exist, ignore
}

$seoData = [
    // Home (/)
    [
        'page_url' => '/',
        'title' => 'Web Development Company & Hire Developers | Webstep Solutions',
        'description' => 'Custom WordPress, Shopify, React and Laravel development for agencies and growing businesses. Hire dedicated developers monthly. Request a quote.',
        'keywords' => 'web development company, hire dedicated developers, web development services, custom WordPress development, Shopify development, React development, Laravel development'
    ],
    [
        'page_url' => 'home',
        'title' => 'Web Development Company & Hire Developers | Webstep Solutions',
        'description' => 'Custom WordPress, Shopify, React and Laravel development for agencies and growing businesses. Hire dedicated developers monthly. Request a quote.',
        'keywords' => 'web development company, hire dedicated developers, web development services, custom WordPress development, Shopify development, React development, Laravel development'
    ],

    // Services page (/services/)
    [
        'page_url' => 'services',
        'title' => 'Web Development Services | WordPress, Shopify, Next.js',
        'description' => 'Web development services covering WordPress, Shopify, WooCommerce, Next.js and Laravel, plus API integrations. See what we build and request a quote.',
        'keywords' => 'web development services, WordPress, Shopify, WooCommerce, Next.js, Laravel, API integrations'
    ],
    [
        'page_url' => 'services/',
        'title' => 'Web Development Services | WordPress, Shopify, Next.js',
        'description' => 'Web development services covering WordPress, Shopify, WooCommerce, Next.js and Laravel, plus API integrations. See what we build and request a quote.',
        'keywords' => 'web development services, WordPress, Shopify, WooCommerce, Next.js, Laravel, API integrations'
    ],

    // Web Development (/services/web-development/)
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

    // Full Stack (/services/full-stack-development/ & /fullstack/)
    [
        'page_url' => 'fullstack',
        'title' => 'Full Stack Development Services | Node.js, Vue & APIs',
        'description' => 'Full stack developers for Vue, Node.js and PHP projects and API integrations. Get a clear scope and quote for your project.',
        'keywords' => 'full stack development services, Node.js, Vue, PHP, REST APIs'
    ],
    [
        'page_url' => 'services/full-stack-development',
        'title' => 'Full Stack Development Services | Node.js, Vue & APIs',
        'description' => 'Full stack developers for Vue, Node.js and PHP projects and API integrations. Get a clear scope and quote for your project.',
        'keywords' => 'full stack development services, Node.js, Vue, PHP, REST APIs'
    ],

    // WordPress (/services/wordpress/ & /services/wordpress-development/)
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
    [
        'page_url' => 'services/wordpress-development',
        'title' => 'WordPress Development Services | Custom Themes & Plugins',
        'description' => 'Hand-coded WordPress themes, custom plugins and headless builds from your Figma designs. No page-builder bloat. Request a WordPress quote.',
        'keywords' => 'WordPress development services, custom themes, custom plugins, headless WordPress, Figma to WordPress'
    ],

    // WooCommerce (/services/woocommerce/ & /services/woocommerce-development/)
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
    [
        'page_url' => 'services/woocommerce-development',
        'title' => 'WooCommerce Development Services | Custom Online Stores',
        'description' => 'Custom WooCommerce stores with tailored product types, checkout flows and payment gateways. Migrations and plugin development included.',
        'keywords' => 'WooCommerce development services, custom online stores, WooCommerce checkout, payment gateways, WooCommerce migration'
    ],

    // Shopify (/services/shopify/ & /services/shopify-development/)
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
    [
        'page_url' => 'services/shopify-development',
        'title' => 'Shopify Development Services | Custom Themes & Apps',
        'description' => 'Custom Shopify themes, Liquid development and private apps for brands and agencies. Shopify Plus and store migrations supported. Get a quote.',
        'keywords' => 'Shopify development services, custom Shopify themes, Liquid development, private apps, Shopify Plus, store migration'
    ],

    // eCommerce overview (/services/ecommerce/)
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

    // Laravel (/services/laravel/ & /services/laravel-development/)
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
    [
        'page_url' => 'services/laravel-development',
        'title' => 'Laravel Development Company | Custom PHP Web Apps',
        'description' => 'Secure Laravel applications, REST APIs and custom PHP development, from MVPs to systems built to scale. Talk to a Laravel developer.',
        'keywords' => 'Laravel development company, custom PHP web apps, REST APIs, Laravel developers'
    ],

    // React & Next.js (/services/reactjs/)
    [
        'page_url' => 'reactjs',
        'title' => 'React & Next.js Development Services | Webstep Solutions',
        'description' => 'Fast, SEO-friendly web apps built with React and Next.js, with server-side rendering and clean, scalable code. Tell us about your project.',
        'keywords' => 'React development, Next.js development services, SSR, server-side rendering, scalable web apps'
    ],
    [
        'page_url' => 'services/reactjs',
        'title' => 'React & Next.js Development Services | Webstep Solutions',
        'description' => 'Fast, SEO-friendly web apps built with React and Next.js, with server-side rendering and clean, scalable code. Tell us about your project.',
        'keywords' => 'React development, Next.js development services, SSR, server-side rendering, scalable web apps'
    ],

    // Mobile App (/services/app-development/ & /mobileapp/)
    [
        'page_url' => 'mobileapp',
        'title' => 'Mobile App Development | iOS & Android | Webstep Solutions',
        'description' => 'Native iOS apps in Swift and Android apps in Kotlin, from idea to App Store and Play Store launch. Share your app idea and get a quote.',
        'keywords' => 'mobile app development, iOS app, Android app, Swift, Kotlin, React Native'
    ],
    [
        'page_url' => 'services/app-development',
        'title' => 'Mobile App Development | iOS & Android | Webstep Solutions',
        'description' => 'Native iOS apps in Swift and Android apps in Kotlin, from idea to App Store and Play Store launch. Share your app idea and get a quote.',
        'keywords' => 'mobile app development, iOS app, Android app, Swift, Kotlin, React Native'
    ],

    // UI/UX (/services/ui-ux/ & /uiux/)
    [
        'page_url' => 'uiux',
        'title' => 'UI/UX Design Services | Webstep Solutions',
        'description' => 'User-first interface design for websites and apps, from wireframes to approved Figma designs, ready for development. Request a design quote.',
        'keywords' => 'UI/UX design services, Figma design, wireframing, user interface design, prototype design'
    ],
    [
        'page_url' => 'services/ui-ux',
        'title' => 'UI/UX Design Services | Webstep Solutions',
        'description' => 'User-first interface design for websites and apps, from wireframes to approved Figma designs, ready for development. Request a design quote.',
        'keywords' => 'UI/UX design services, Figma design, wireframing, user interface design, prototype design'
    ],

    // AI Chatbots (/services/ai-chatbots/)
    [
        'page_url' => 'ai-chatbots',
        'title' => 'AI Chatbot Development Services | Custom Chatbots',
        'description' => 'Custom AI chatbots and API integrations that handle support questions, qualify leads and automate routine work. Tell us what you want to automate.',
        'keywords' => 'AI chatbot development services, custom chatbots, LLM integration, customer support automation'
    ],
    [
        'page_url' => 'services/ai-chatbots',
        'title' => 'AI Chatbot Development Services | Custom Chatbots',
        'description' => 'Custom AI chatbots and API integrations that handle support questions, qualify leads and automate routine work. Tell us what you want to automate.',
        'keywords' => 'AI chatbot development services, custom chatbots, LLM integration, customer support automation'
    ],

    // AI-Powered Websites (/services/ai-powered-websites/)
    [
        'page_url' => 'ai-powered-websites',
        'title' => 'AI-Powered Website Development | Webstep Solutions',
        'description' => 'Fast Next.js websites with built-in AI features such as chat, search and personalization. Tell us what you want your site to do.',
        'keywords' => 'AI powered website development, Next.js AI, AI search, personalization'
    ],
    [
        'page_url' => 'services/ai-powered-websites',
        'title' => 'AI-Powered Website Development | Webstep Solutions',
        'description' => 'Fast Next.js websites with built-in AI features such as chat, search and personalization. Tell us what you want your site to do.',
        'keywords' => 'AI powered website development, Next.js AI, AI search, personalization'
    ],

    // AI Automation (/services/ai-automation/)
    [
        'page_url' => 'ai-automation',
        'title' => 'AI Workflow Automation Services | Webstep Solutions',
        'description' => 'Automate repetitive work with AI workflows that connect your tools and answer routine requests. Describe your process and get a plan.',
        'keywords' => 'AI workflow automation services, business process automation, autonomous workflows'
    ],
    [
        'page_url' => 'services/ai-automation',
        'title' => 'AI Workflow Automation Services | Webstep Solutions',
        'description' => 'Automate repetitive work with AI workflows that connect your tools and answer routine requests. Describe your process and get a plan.',
        'keywords' => 'AI workflow automation services, business process automation, autonomous workflows'
    ],

    // Hire Developers (/hire-dedicated-developers/)
    [
        'page_url' => 'hire-dedicated-developers',
        'title' => 'Hire Dedicated Developers | Remote Teams | Webstep Solutions',
        'description' => 'Hire vetted React, Laravel, WordPress and Shopify developers monthly. Work with them directly, with QA and weekly reporting. NDA-ready.',
        'keywords' => 'hire dedicated developers, remote teams, React developers, Laravel developers, WordPress developers, Shopify developers'
    ],
    [
        'page_url' => 'hire-full-time-developers',
        'title' => 'Hire Dedicated Developers | Remote Teams | Webstep Solutions',
        'description' => 'Hire vetted React, Laravel, WordPress and Shopify developers monthly. Work with them directly, with QA and weekly reporting. NDA-ready.',
        'keywords' => 'hire dedicated developers, remote teams, React developers, Laravel developers, WordPress developers, Shopify developers'
    ],

    // For Agencies (/b2b-sales/)
    [
        'page_url' => 'b2b-sales',
        'title' => 'Development Partner for Agencies | Webstep Solutions',
        'description' => 'A reliable development partner for design and marketing agencies. WordPress, Shopify and custom builds with NDA-ready engagement.',
        'keywords' => 'development partner for agencies, white label development, agency outsourcing partner'
    ],
    [
        'page_url' => 'services/b2b-sales',
        'title' => 'Development Partner for Agencies | Webstep Solutions',
        'description' => 'A reliable development partner for design and marketing agencies. WordPress, Shopify and custom builds with NDA-ready engagement.',
        'keywords' => 'development partner for agencies, white label development, agency outsourcing partner'
    ],

    // Portfolio (/works/)
    [
        'page_url' => 'works',
        'title' => 'Web Development Portfolio | Webstep Solutions',
        'description' => 'Recent projects built with React, Next.js, Laravel and WordPress for healthcare, SaaS, infrastructure and non-profit clients. See the results.',
        'keywords' => 'web development portfolio, client projects, React projects, Laravel projects'
    ],
    [
        'page_url' => 'services/works',
        'title' => 'Web Development Portfolio | Webstep Solutions',
        'description' => 'Recent projects built with React, Next.js, Laravel and WordPress for healthcare, SaaS, infrastructure and non-profit clients. See the results.',
        'keywords' => 'web development portfolio, client projects, React projects, Laravel projects'
    ],

    // Case Studies (/case-study/)
    [
        'page_url' => 'case-study',
        'title' => 'Web Development Case Studies | Webstep Solutions',
        'description' => 'See how we planned, built and delivered web projects for real clients, with the problem, the tech used and the results.',
        'keywords' => 'web development case studies, client success stories, project results'
    ],

    // About (/about/)
    [
        'page_url' => 'about',
        'title' => 'About Webstep Solutions | Web Development Team Since 2012',
        'description' => 'Mohali-based team with 12+ years building WordPress, Shopify and custom web apps for clients in 15+ countries. Meet the people behind Webstep Solutions.',
        'keywords' => 'about webstep solutions, web development team Mohali, 12 years experience'
    ],
    [
        'page_url' => 'services/about',
        'title' => 'About Webstep Solutions | Web Development Team Since 2012',
        'description' => 'Mohali-based team with 12+ years building WordPress, Shopify and custom web apps for clients in 15+ countries. Meet the people behind Webstep Solutions.',
        'keywords' => 'about webstep solutions, web development team Mohali, 12 years experience'
    ],

    // Contact (/contactus/)
    [
        'page_url' => 'contactus',
        'title' => 'Contact Webstep Solutions | Request a Web Development Quote',
        'description' => 'Tell us about your project or the developers you need. Share your requirements and we will reply within one business day.',
        'keywords' => 'contact webstep solutions, request quote, hire developers contact'
    ],
    [
        'page_url' => 'contact',
        'title' => 'Contact Webstep Solutions | Request a Web Development Quote',
        'description' => 'Tell us about your project or the developers you need. Share your requirements and we will reply within one business day.',
        'keywords' => 'contact webstep solutions, request quote, hire developers contact'
    ],

    // Packages (/packages/)
    [
        'page_url' => 'packages',
        'title' => 'Web Development Packages & Pricing | Webstep Solutions',
        'description' => 'Choose a clear web development package for your project, or customize one to fit your needs. See what is included and request a quote.',
        'keywords' => 'web development packages, website pricing, clear package pricing'
    ],

    // Get a Quote (/customize-package/)
    [
        'page_url' => 'customize-package',
        'title' => 'Get a Free Quote | Webstep Solutions',
        'description' => 'Tell us what you need built and get a clear quote. Choose your services and share your requirements in two minutes.',
        'keywords' => 'get a free quote, customize project, web development quote'
    ],

    // Website Maintenance & Support (/services/website-maintenance/)
    [
        'page_url' => 'website-maintenance',
        'title' => 'Website Maintenance & Support Services | Webstep Solutions',
        'description' => 'Ongoing website maintenance for WordPress, Shopify and custom sites: updates, security, bug fixes and speed checks. Ask about a monthly plan.',
        'keywords' => 'website maintenance services, WordPress maintenance, Shopify support, website security updates'
    ],
    [
        'page_url' => 'services/website-maintenance',
        'title' => 'Website Maintenance & Support Services | Webstep Solutions',
        'description' => 'Ongoing website maintenance for WordPress, Shopify and custom sites: updates, security, bug fixes and speed checks. Ask about a monthly plan.',
        'keywords' => 'website maintenance services, WordPress maintenance, Shopify support, website security updates'
    ],

    // Blog (/blog/)
    [
        'page_url' => 'blog',
        'title' => 'Web Development Blog | Webstep Solutions',
        'description' => 'Practical guides on choosing platforms, hiring developers and building better websites, from the Webstep Solutions team.',
        'keywords' => 'web development blog, tech insights, hiring guides'
    ],

    // Our Team
    [
        'page_url' => 'team',
        'title' => 'Our Team | Developers Behind Webstep Solutions',
        'description' => 'Meet our engineers, designers, and project managers delivering high-performance digital products worldwide.',
        'keywords' => 'our team, web developers, software engineers'
    ],

    // SEO Services
    [
        'page_url' => 'seo',
        'title' => 'Technical SEO Services | Webstep Solutions',
        'description' => 'Technical SEO audits, Core Web Vitals fixes and conversion improvements for business websites. Request an SEO audit.',
        'keywords' => 'technical SEO services, SEO audits, Core Web Vitals, conversion improvements'
    ],
    [
        'page_url' => 'services/seo',
        'title' => 'Technical SEO Services | Webstep Solutions',
        'description' => 'Technical SEO audits, Core Web Vitals fixes and conversion improvements for business websites. Request an SEO audit.',
        'keywords' => 'technical SEO services, SEO audits, Core Web Vitals, conversion improvements'
    ],

    // Social Media
    [
        'page_url' => 'social-media',
        'title' => 'Social Media Marketing Services | Webstep Solutions',
        'description' => 'Social media content and paid ads for businesses. Plan your campaigns with our team.',
        'keywords' => 'social media marketing services, paid ads, content campaigns'
    ],
    [
        'page_url' => 'services/social-media',
        'title' => 'Social Media Marketing Services | Webstep Solutions',
        'description' => 'Social media content and paid ads for businesses. Plan your campaigns with our team.',
        'keywords' => 'social media marketing services, paid ads, content campaigns'
    ],

    // Email Marketing
    [
        'page_url' => 'email-marketing',
        'title' => 'Email Marketing Services | Webstep Solutions',
        'description' => 'Email campaigns and automation that bring customers back. Tell us your goal and get a plan.',
        'keywords' => 'email marketing services, email campaigns, automation'
    ],
    [
        'page_url' => 'services/email-marketing',
        'title' => 'Email Marketing Services | Webstep Solutions',
        'description' => 'Email campaigns and automation that bring customers back. Tell us your goal and get a plan.',
        'keywords' => 'email marketing services, email campaigns, automation'
    ],

    // Brand Design
    [
        'page_url' => 'brand-design',
        'title' => 'Brand & Logo Design Services | Webstep Solutions',
        'description' => 'Logos and brand identity design for new and growing businesses. Share your brand idea and get a quote.',
        'keywords' => 'brand design, logo design services, brand identity'
    ],
    [
        'page_url' => 'services/brand-design',
        'title' => 'Brand & Logo Design Services | Webstep Solutions',
        'description' => 'Logos and brand identity design for new and growing businesses. Share your brand idea and get a quote.',
        'keywords' => 'brand design, logo design services, brand identity'
    ],

    // Industrial Training
    [
        'page_url' => 'industrial-training',
        'title' => 'Web Development Training in Mohali | Webstep Solutions',
        'description' => 'Career-ready web development training programs in Mohali for students and freshers. Check the courses and apply.',
        'keywords' => 'web development training in Mohali, freshers training, coding bootcamp'
    ],

    // Privacy Policy
    [
        'page_url' => 'privacy',
        'title' => 'Privacy Policy | Webstep Solutions',
        'description' => 'Privacy Policy for Webstep Solutions.',
        'keywords' => 'privacy policy, webstep solutions'
    ],
    [
        'page_url' => 'privacy-policy',
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
    ],
    [
        'page_url' => 'terms-and-conditions',
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
        echo 'Error on ' . $data['page_url'] . ': ' . $e->getMessage() . "\n";
    }
}

echo "SEO Seeding Complete!\n";
echo "Inserted: $inserted\n";
echo "Updated: $updated\n";
