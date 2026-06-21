<?php
/**
 * Law & Reason - Main Configuration File
 * Change values here when deploying to Hostinger
 */

// Site Information
define('SITE_NAME', 'Law & Reason');
define('SITE_TAGLINE', 'Understanding Law. Navigating Life.');
define('SITE_DOMAIN', 'lawandreason.com');
define('SITE_URL', 'https://lawandreason.com');
define('SITE_EMAIL', 'contact@lawandreason.com');

// Database Configuration (Update these on Hostinger)
define('DB_HOST', 'localhost');
define('DB_NAME', 'law_reason');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Directory Paths
define('ROOT_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('ADMIN_PATH', ROOT_PATH . 'admin/');
define('UPLOADS_PATH', ROOT_PATH . 'public/uploads/');
define('UPLOADS_URL', SITE_URL . '/public/uploads/');

// Image Upload Settings
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB max upload
define('IMAGE_MAX_WIDTH', 1600); // Max width for blog images
define('IMAGE_THUMB_WIDTH', 400); // Thumbnail width
define('IMAGE_QUALITY', 80); // WebP quality (0-100)
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// Security
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes
define('CSRF_TOKEN_NAME', 'csrf_token');

// Language Settings
define('DEFAULT_LANG', 'en');
define('SUPPORTED_LANGS', ['en', 'hi']);

// Analytics (Add your IDs when ready)
define('GA4_MEASUREMENT_ID', ''); // e.g., 'G-XXXXXXXXXX'
define('SEARCH_CONSOLE_VERIFICATION', ''); // Google Search Console meta tag content

// Pagination
define('POSTS_PER_PAGE', 9);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Timezone
date_default_timezone_set('Asia/Kolkata');
