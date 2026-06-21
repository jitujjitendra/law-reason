<?php
/**
 * Law & Reason - Helper Functions
 * Common utilities used across the site
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/security.php';

// Get current language
function getCurrentLang() {
    if (isset($_GET['lang']) && in_array($_GET['lang'], SUPPORTED_LANGS)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    return $_SESSION['lang'] ?? DEFAULT_LANG;
}

// Get localized field name (appends _en or _hi)
function langField($fieldBase) {
    $lang = getCurrentLang();
    return $fieldBase . '_' . $lang;
}

// Get localized value from a row
function getLangValue($row, $fieldBase) {
    $field = langField($fieldBase);
    return $row[$field] ?? $row[$fieldBase . '_en'] ?? '';
}

// Generate URL-safe slug
function generateSlug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

// Format date
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

// Format date in Hindi
function formatDateHindi($date) {
    $months = [
        1 => 'जनवरी', 2 => 'फ़रवरी', 3 => 'मार्च', 4 => 'अप्रैल',
        5 => 'मई', 6 => 'जून', 7 => 'जुलाई', 8 => 'अगस्त',
        9 => 'सितंबर', 10 => 'अक्टूबर', 11 => 'नवंबर', 12 => 'दिसंबर'
    ];
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = $months[(int)date('m', $timestamp)];
    $year = date('Y', $timestamp);
    return "$day $month $year";
}

// Truncate text
function truncateText($text, $length = 150) {
    $text = strip_tags($text);
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '...';
}

// Get site setting from DB
function getSiteSetting($key) {
    static $settings = null;
    
    if ($settings === null) {
        try {
            $db = getDB();
            $stmt = $db->query("SELECT setting_key, setting_value FROM site_settings");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $e) {
            $settings = [];
        }
    }
    
    return $settings[$key] ?? '';
}

// Pagination helper
function getPagination($totalItems, $currentPage, $perPage = POSTS_PER_PAGE) {
    $totalPages = ceil($totalItems / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'per_page' => $perPage,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

// Generate canonical URL
function canonicalURL($path = '') {
    return SITE_URL . '/' . ltrim($path, '/');
}

// Generate language switch URL
function langSwitchURL($targetLang) {
    $currentURL = $_SERVER['REQUEST_URI'];
    $parsed = parse_url($currentURL);
    $path = $parsed['path'] ?? '/';
    parse_str($parsed['query'] ?? '', $params);
    $params['lang'] = $targetLang;
    return $path . '?' . http_build_query($params);
}

// Get breadcrumbs array
function getBreadcrumbs($items) {
    // $items = [['label' => 'Home', 'url' => '/'], ['label' => 'Blog', 'url' => '/blog'], ...]
    return $items;
}

// JSON-LD structured data for SEO
function generateJSONLD($type, $data) {
    $schema = [];
    
    switch ($type) {
        case 'website':
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => SITE_NAME,
                'url' => SITE_URL,
                'description' => $data['description'] ?? SITE_TAGLINE,
                'inLanguage' => ['en', 'hi'],
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => SITE_URL . '/search?q={search_term_string}',
                    'query-input' => 'required name=search_term_string'
                ]
            ];
            break;
            
        case 'article':
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $data['title'],
                'description' => $data['description'] ?? '',
                'image' => $data['image'] ?? '',
                'author' => [
                    '@type' => 'Organization',
                    'name' => SITE_NAME
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => SITE_NAME,
                    'url' => SITE_URL
                ],
                'datePublished' => $data['published'] ?? '',
                'dateModified' => $data['modified'] ?? $data['published'] ?? ''
            ];
            break;
            
        case 'faq':
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => []
            ];
            foreach ($data['questions'] as $q) {
                $schema['mainEntity'][] = [
                    '@type' => 'Question',
                    'name' => $q['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $q['answer']
                    ]
                ];
            }
            break;
            
        case 'breadcrumb':
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => []
            ];
            foreach ($data['items'] as $i => $item) {
                $schema['itemListElement'][] = [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $item['label'],
                    'item' => $item['url'] ?? ''
                ];
            }
            break;
    }
    
    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}

// Flash messages
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Send email notification (basic — works with Hostinger SMTP later)
function sendNotificationEmail($to, $subject, $body) {
    $headers = [
        'From: ' . SITE_NAME . ' <' . SITE_EMAIL . '>',
        'Reply-To: ' . SITE_EMAIL,
        'Content-Type: text/html; charset=UTF-8',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    return @mail($to, $subject, $body, implode("\r\n", $headers));
}

/**
 * Sanitize HTML content from admin editor
 * Allows safe formatting tags, strips dangerous ones (script, iframe, etc.)
 */
function sanitizeHTML($html) {
    // Allowed tags for blog/topic content
    $allowedTags = '<h2><h3><h4><p><br><strong><b><em><i><u><a><ul><ol><li><blockquote><hr><img><span><div><table><thead><tbody><tr><th><td>';
    
    // Strip all tags except allowed
    $clean = strip_tags($html, $allowedTags);
    
    // Remove any event handlers (onclick, onerror, etc.)
    $clean = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $clean);
    $clean = preg_replace('/\s*on\w+\s*=\s*\S+/i', '', $clean);
    
    // Remove javascript: protocol in links
    $clean = preg_replace('/href\s*=\s*["\']?\s*javascript\s*:/i', 'href="', $clean);
    
    // Remove data: protocol in src (except data:image)
    $clean = preg_replace('/src\s*=\s*["\']?\s*data\s*:(?!image)/i', 'src="', $clean);
    
    return $clean;
}
