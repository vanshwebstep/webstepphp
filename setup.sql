-- ─────────────────────────────────────────────────────────────────────────────
-- WEBSTEP DATABASE SETUP
-- Run this once: mysql -u root -p webstep < setup.sql
-- ─────────────────────────────────────────────────────────────────────────────

CREATE DATABASE IF NOT EXISTS `webstep` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `webstep`;

-- ─── Package Categories / Tabs ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `package_tabs` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(100) NOT NULL UNIQUE,
    `sort_order` INT DEFAULT 0,
    `is_active`  TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─── Packages ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `packages` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title`      VARCHAR(150) NOT NULL,
    `category`   VARCHAR(100) NOT NULL,
    `description`TEXT,
    `symbol`     VARCHAR(5) DEFAULT '$',
    `price`      DECIMAL(10,2) NOT NULL DEFAULT 0,
    `price_desc` VARCHAR(50) DEFAULT '/project',
    `is_popular` TINYINT(1) DEFAULT 0,
    `btn_text`   VARCHAR(100) DEFAULT 'Choose Plan',
    `sort_order` INT DEFAULT 0,
    `is_active`  TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_package_title_category` (`title`, `category`)
) ENGINE=InnoDB;

-- ─── Package Features (events) ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `package_features` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `package_id` INT UNSIGNED NOT NULL,
    `title`      VARCHAR(200) NOT NULL,
    `sort_order` INT DEFAULT 0,
    UNIQUE KEY `uq_package_feature_title` (`package_id`, `title`),
    FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── Custom Package Builder Options ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `custom_options` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category`   ENUM('websiteType','pages','design','features') NOT NULL,
    `option_id`  VARCHAR(50) NOT NULL,
    `title`      VARCHAR(150) NOT NULL,
    `description`VARCHAR(255) DEFAULT NULL,
    `icon`       VARCHAR(50) DEFAULT NULL,    -- e.g. 'FaShoppingCart'
    `price`      DECIMAL(10,2) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `is_active`  TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_custom_option_category_option` (`category`, `option_id`)
) ENGINE=InnoDB;

-- ─── Custom Package Submissions ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `custom_submissions` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `source`          VARCHAR(80) DEFAULT 'custom-package',
    `name`            VARCHAR(150) NOT NULL,
    `email`           VARCHAR(200) NOT NULL,
    `phone`           VARCHAR(30) DEFAULT NULL,
    `message`         TEXT DEFAULT NULL,
    `website_type_id` VARCHAR(50) DEFAULT NULL,
    `website_type`    VARCHAR(100) DEFAULT NULL,
    `pages_id`        VARCHAR(50) DEFAULT NULL,
    `pages`           VARCHAR(100) DEFAULT NULL,
    `design_id`       VARCHAR(50) DEFAULT NULL,
    `design`          VARCHAR(100) DEFAULT NULL,
    `features_json`   TEXT DEFAULT NULL,     -- JSON array of selected features
    `estimated_total` DECIMAL(10,2) DEFAULT 0,
    `status`          ENUM('new','viewed','contacted','closed') DEFAULT 'new',
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─── Package Inquiries (from Choose Plan button) ──────────────────────────────
CREATE TABLE IF NOT EXISTS `package_inquiries` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `package_id` INT UNSIGNED DEFAULT NULL,
    `name`       VARCHAR(150) NOT NULL,
    `email`      VARCHAR(200) NOT NULL,
    `phone`      VARCHAR(30) DEFAULT NULL,
    `message`    TEXT DEFAULT NULL,
    `status`     ENUM('new','viewed','contacted','closed') DEFAULT 'new',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email`      VARCHAR(200) NOT NULL UNIQUE,
    `status`     ENUM('active','unsubscribed') DEFAULT 'active',
    `source`     VARCHAR(80) DEFAULT 'footer',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─── Admin Users ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `service_pages` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug`         VARCHAR(120) NOT NULL UNIQUE,
    `title`        VARCHAR(180) NOT NULL,
    `content_json` LONGTEXT NOT NULL,
    `sort_order`   INT DEFAULT 0,
    `is_active`    TINYINT(1) DEFAULT 1,
    `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `admin_users` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username`   VARCHAR(100) NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin: username=admin, password=admin123 (CHANGE IN PRODUCTION!)
INSERT IGNORE INTO `admin_users` (`username`, `password`)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ─── Seed Default Tabs ────────────────────────────────────────────────────────
INSERT IGNORE INTO `package_tabs` (`name`, `sort_order`) VALUES
('website development', 1),
('digital marketing', 2),
('mobile app development', 3);

-- ─── Seed Default Packages ───────────────────────────────────────────────────
INSERT IGNORE INTO `packages` (`title`, `category`, `description`, `price`, `price_desc`, `is_popular`, `sort_order`) VALUES
('Website Starter',    'website development',    'A clean launch package for small business websites.', 199,  '/project', 0, 1),
('Website Pro',        'website development',    'Custom website package with premium UI and analytics.', 499, '/project', 1, 2),
('Marketing Basic',    'digital marketing',      'Monthly growth setup for search and social visibility.', 149, '/mo',      0, 1),
('Marketing Growth',   'digital marketing',      'Performance-focused marketing for scaling businesses.', 399, '/mo',      1, 2),
('App MVP',            'mobile app development', 'Start with a focused mobile app MVP.', 999,  '/project', 0, 1),
('App Scale',          'mobile app development', 'Full mobile app build with richer product workflows.', 2499,'/project', 1, 2);

-- ─── Seed Package Features ────────────────────────────────────────────────────
INSERT IGNORE INTO `package_features` (`package_id`, `title`, `sort_order`) VALUES
(1,'Up to 5 Pages',1),(1,'Responsive Design',2),(1,'Basic SEO Setup',3),(1,'Contact Form',4),
(2,'Up to 15 Pages',1),(2,'Custom UI/UX Design',2),(2,'Advanced SEO & Analytics',3),(2,'Speed Optimization',4),
(3,'Keyword Research',1),(3,'On-page SEO',2),(3,'4 Social Posts',3),(3,'Monthly Report',4),
(4,'Advanced SEO',1),(4,'Paid Ads Setup',2),(4,'Content Calendar',3),(4,'Conversion Tracking',4),
(5,'Core App Screens',1),(5,'API Integration',2),(5,'Basic Admin Panel',3),(5,'Testing Support',4),
(6,'Custom UI/UX',1),(6,'Advanced Features',2),(6,'Push Notifications',3),(6,'Launch Support',4);

-- ─── Seed Custom Builder Options ─────────────────────────────────────────────
INSERT IGNORE INTO `custom_options` (`category`, `option_id`, `title`, `description`, `icon`, `price`, `sort_order`) VALUES
('websiteType','ecommerce','E-Commerce',NULL,'FaShoppingCart',500,1),
('websiteType','corporate','Corporate',NULL,'FaBriefcase',300,2),
('websiteType','portfolio','Portfolio',NULL,'FaLaptopCode',200,3),
('websiteType','landing','Landing Page',NULL,'FaPaintBrush',100,4),
('pages','1-5','1 - 5 Pages',NULL,NULL,0,1),
('pages','5-10','5 - 10 Pages',NULL,NULL,100,2),
('pages','10-20','10 - 20 Pages',NULL,NULL,250,3),
('pages','20+','20+ Pages',NULL,NULL,500,4),
('design','template','Template Based','Standard pre-made design',NULL,0,1),
('design','custom','Custom Design','Tailored to your brand',NULL,300,2),
('design','premium','Premium UI/UX','High-end interactions & animations',NULL,600,3),
('features','seo','SEO Setup',NULL,'FaSearch',150,1),
('features','content','Content Writing',NULL,'FaPenNib',200,2),
('features','payment','Payment Gateway',NULL,'FaCreditCard',100,3),
('features','social','Social Media Integrations',NULL,'FaShareAlt',50,4);

-- Portfolio and case study dynamic content
CREATE TABLE IF NOT EXISTS `portfolio_categories` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(100) NOT NULL UNIQUE,
    `sort_order` INT DEFAULT 0,
    `is_active`  TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `portfolio_projects` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title`       VARCHAR(150) NOT NULL,
    `category`    VARCHAR(150) NOT NULL,
    `image`       VARCHAR(500) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `tags_json`   TEXT DEFAULT NULL,
    `tech_json`   TEXT DEFAULT NULL,
    `year`        VARCHAR(20) DEFAULT NULL,
    `sort_order`  INT DEFAULT 0,
    `is_active`   TINYINT(1) DEFAULT 1,
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_portfolio_title` (`title`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `case_study_categories` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(100) NOT NULL UNIQUE,
    `sort_order` INT DEFAULT 0,
    `is_active`  TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `case_studies` (
    `id`                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug`               VARCHAR(160) NOT NULL UNIQUE,
    `title`              VARCHAR(180) NOT NULL,
    `tagline`            VARCHAR(255) DEFAULT NULL,
    `category`           VARCHAR(100) NOT NULL,
    `tags_json`          TEXT DEFAULT NULL,
    `image`              VARCHAR(500) DEFAULT NULL,
    `logo`               VARCHAR(50) DEFAULT NULL,
    `client`             VARCHAR(180) DEFAULT NULL,
    `duration`           VARCHAR(80) DEFAULT NULL,
    `year`               VARCHAR(20) DEFAULT NULL,
    `tech_json`          TEXT DEFAULT NULL,
    `challenge`          TEXT DEFAULT NULL,
    `solution`           TEXT DEFAULT NULL,
    `result`             TEXT DEFAULT NULL,
    `metrics_json`       TEXT DEFAULT NULL,
    `deliverables_json`  TEXT DEFAULT NULL,
    `testimonial_quote`  TEXT DEFAULT NULL,
    `testimonial_author` VARCHAR(150) DEFAULT NULL,
    `testimonial_role`   VARCHAR(180) DEFAULT NULL,
    `testimonial_avatar` VARCHAR(500) DEFAULT NULL,
    `featured`           TINYINT(1) DEFAULT 0,
    `color`              VARCHAR(20) DEFAULT '#6366f1',
    `accent`             VARCHAR(20) DEFAULT '#e0e7ff',
    `light_text`         VARCHAR(20) DEFAULT '#4338ca',
    `dark_bg`            VARCHAR(20) DEFAULT '#4338ca',
    `sort_order`         INT DEFAULT 0,
    `is_active`          TINYINT(1) DEFAULT 1,
    `created_at`         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO `portfolio_categories` (`name`, `sort_order`) VALUES
('All', 0), ('UI & UX', 1), ('Shopify', 2), ('WordPress', 3), ('Front End Dev', 4),
('Development', 5), ('PHP', 6), ('Laravel', 7), ('Node.js', 8), ('AI', 9);

INSERT IGNORE INTO `portfolio_projects`
(`title`, `category`, `image`, `description`, `tags_json`, `tech_json`, `year`, `sort_order`) VALUES
('Dr. Treat', 'Healthcare / React.js', 'https://webstepdev.com/demo/webstepnextassets/img/dr-treat-case.png', 'Patient-centric healthcare platform with intuitive UX flows and real-time appointment scheduling.', '["UI & UX","Development","Front End Dev"]', '["React","Node.js","Tailwind"]', '2023', 1),
('Stillwell Hansen', 'Infrastructure / Next.js', 'https://webstepdev.com/demo/webstepnextassets/img/Stillwell-Hansen-work.png', 'Large-scale infrastructure project portal with real-time progress tracking and team collaboration.', '["Development","Node.js","Front End Dev"]', '["Next.js","Node.js","MongoDB"]', '2023', 2),
('Retrievr', 'SaaS / Laravel', 'https://webstepdev.com/demo/webstepnextassets/img/Retrievr-work1.png', 'Smart asset-tracking SaaS used by businesses across North America.', '["Node.js","Development","Laravel","PHP"]', '["Laravel","Vue","MySQL"]', '2022', 3),
('Grace Mercy', 'Non-Profit / WordPress', 'https://webstepdev.com/demo/webstepnextassets/img/Grace-Mercy-work.png', 'Emotionally driven web presence for a non-profit with a stronger donation journey.', '["UI & UX","WordPress","Front End Dev"]', '["WordPress","Tailwind"]', '2022', 4);

INSERT IGNORE INTO `case_study_categories` (`name`, `sort_order`) VALUES
('All', 0), ('Healthcare', 1), ('E-Commerce', 2), ('SaaS', 3), ('Education', 4),
('Fintech', 5), ('Non-Profit', 6), ('Enterprise', 7);

INSERT IGNORE INTO `case_studies`
(`slug`, `title`, `tagline`, `category`, `tags_json`, `image`, `logo`, `client`, `duration`, `year`, `tech_json`, `challenge`, `solution`, `result`, `metrics_json`, `deliverables_json`, `testimonial_quote`, `testimonial_author`, `testimonial_role`, `testimonial_avatar`, `featured`, `color`, `accent`, `light_text`, `dark_bg`, `sort_order`) VALUES
('dr-treat', 'Dr. Treat', 'Redesigning patient care from the ground up', 'Healthcare', '["Healthcare","SaaS"]', 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=900&q=80', 'DT', 'Dr. Treat Inc.', '6 months', '2023', '["React","Node.js","AWS","Figma"]', 'A fragmented patient onboarding system was causing appointment drop-offs and manual admin follow-ups.', 'We rebuilt the patient journey with a mobile-first booking engine, slot management, and automated reminders.', 'Drop-offs reduced sharply while appointment throughput grew within 90 days.', '[{"label":"Drop-off Reduced","val":"72%"},{"label":"Booking Speed","val":"3x"},{"label":"Patient NPS","val":"+58"}]', '["Custom booking engine","Patient mobile app","Admin dashboard","Automated SMS/Email reminders","Real-time slot management"]', 'Webstep Solutions transformed how our patients experience care.', 'Dr. Sarah Linton', 'CEO, Dr. Treat Inc.', 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=80&q=80', 1, '#6366f1', '#e0e7ff', '#4338ca', '#4338ca', 1),
('shopvault', 'ShopVault', 'An e-commerce engine that scales with ambition', 'E-Commerce', '["E-Commerce","Enterprise"]', 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=900&q=80', 'SV', 'ShopVault Ltd.', '8 months', '2023', '["Laravel","React","MySQL","Stripe","Redis"]', 'The legacy store failed under sale traffic and had a dated mobile checkout.', 'We built a custom Laravel commerce API, React storefront, Redis caching, and scalable infrastructure.', 'The store launched with stronger mobile conversion and no peak-sale downtime.', '[{"label":"Monthly GMV","val":"$2M+"},{"label":"Page Load","val":"0.8s"},{"label":"Conversion Lift","val":"+44%"}]', '["Headless React storefront","Laravel commerce API","Redis caching layer","Stripe payment integration","AWS auto-scaling setup"]', 'Webstep built something reliable for our busiest sales windows.', 'Marcus Webb', 'CTO, ShopVault Ltd.', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=80&q=80', 1, '#f59e0b', '#fef3c7', '#b45309', '#92400e', 2),
('eduforge', 'EduForge LMS', 'Learning infrastructure for the next generation', 'Education', '["Education","SaaS"]', 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=900&q=80', 'EF', 'EduForge Group', '10 months', '2024', '["PHP","React","MySQL","WebRTC","AWS"]', 'Institutions were managing learning workflows with spreadsheets and disconnected tools.', 'We delivered an LMS with live classrooms, automated grading, analytics, and mobile access.', 'Students and institutions moved into one measurable learning workflow.', '[{"label":"Students Served","val":"50K+"},{"label":"Completion Rate","val":"92%"},{"label":"Institutions","val":"120+"}]', '["LMS web platform","Live WebRTC classrooms","Mobile student app","Automated grading engine","Analytics dashboard"]', 'The platform gave our teachers time back and made online learning manageable.', 'Prof. Amara Osei', 'Director, EduForge Group', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=80&q=80', 0, '#10b981', '#d1fae5', '#047857', '#064e3b', 3);
