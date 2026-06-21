<?php
/**
 * Law & Reason - Header Template
 * Included at the top of every public page
 */

require_once __DIR__ . '/../includes/helpers.php';
startSecureSession();

$lang = getCurrentLang();
$strings = require __DIR__ . '/../lang/' . $lang . '.php';

// Page-specific variables (set before including header)
$pageTitle = $pageTitle ?? SITE_NAME . ' | ' . SITE_TAGLINE;
$pageDescription = $pageDescription ?? $strings['hero_copy'];
$pageCanonical = $pageCanonical ?? SITE_URL;
$pageImage = $pageImage ?? SITE_URL . '/assets/law-reason-hero.png';
$bodyClass = $bodyClass ?? '';
$currentPage = $currentPage ?? 'home';
?>
<!doctype html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta -->
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($pageCanonical); ?>">
    <?php if (isset($noIndex) && $noIndex): ?>
    <meta name="robots" content="noindex, follow">
    <?php endif; ?>
    
    <!-- Open Graph / Social -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($pageCanonical); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($pageImage); ?>">
    <meta property="og:site_name" content="<?php echo SITE_NAME; ?>">
    <meta property="og:locale" content="<?php echo $lang === 'hi' ? 'hi_IN' : 'en_IN'; ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($pageImage); ?>">
    
    <!-- Hreflang for bilingual SEO -->
    <link rel="alternate" hreflang="en" href="<?php echo htmlspecialchars(langSwitchURL('en')); ?>">
    <link rel="alternate" hreflang="hi" href="<?php echo htmlspecialchars(langSwitchURL('hi')); ?>">
    <link rel="alternate" hreflang="x-default" href="<?php echo htmlspecialchars($pageCanonical); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/assets/favicon.svg">
    
    <!-- Google Search Console Verification -->
    <?php if (SEARCH_CONSOLE_VERIFICATION): ?>
    <meta name="google-site-verification" content="<?php echo SEARCH_CONSOLE_VERIFICATION; ?>">
    <?php endif; ?>
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="/public/css/styles.css">
    
    <!-- GA4 Analytics -->
    <?php if (GA4_MEASUREMENT_ID): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo GA4_MEASUREMENT_ID; ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?php echo GA4_MEASUREMENT_ID; ?>');
    </script>
    <?php endif; ?>
    
    <!-- Structured Data -->
    <?php if ($currentPage === 'home'): ?>
    <?php echo generateJSONLD('website', ['description' => $pageDescription]); ?>
    <?php endif; ?>
    <?php if (isset($breadcrumbs)): ?>
    <?php echo generateJSONLD('breadcrumb', ['items' => $breadcrumbs]); ?>
    <?php endif; ?>
    <?php if (isset($faqSchema)): ?>
    <?php echo generateJSONLD('faq', ['questions' => $faqSchema]); ?>
    <?php endif; ?>
    <?php if (isset($articleSchema)): ?>
    <?php echo generateJSONLD('article', $articleSchema); ?>
    <?php endif; ?>
</head>
<body class="<?php echo $bodyClass; ?>">
    <!-- SVG Sprite -->
    <svg class="svg-sprite" aria-hidden="true">
        <symbol id="icon-scales" viewBox="0 0 64 64">
            <path d="M32 8v46M21 55h22M25 13h14M12 19h40M14 19 7 36h14L14 19Zm36 0-7 17h14L50 19ZM5 36c1 6 17 6 18 0M41 36c1 6 17 6 18 0" />
        </symbol>
        <symbol id="icon-home" viewBox="0 0 48 48">
            <path d="M6 22 24 7l18 15M10 20v21h11V29h6v12h11V20" />
        </symbol>
        <symbol id="icon-users" viewBox="0 0 48 48">
            <circle cx="15" cy="16" r="6" /><circle cx="34" cy="16" r="6" />
            <path d="M5 40V30c0-5 4-8 10-8s10 3 10 8v10M24 40V30c0-5 4-8 10-8s9 3 9 8v10M24 10v11" />
        </symbol>
        <symbol id="icon-cart" viewBox="0 0 48 48">
            <path d="M5 8h6l5 23h20l5-16H13M17 21h22M20 38h.1M34 38h.1" />
        </symbol>
        <symbol id="icon-briefcase" viewBox="0 0 48 48">
            <rect x="5" y="15" width="38" height="26" rx="2" />
            <path d="M17 15v-5h14v5M5 25h38M20 23v5h8v-5" />
        </symbol>
        <symbol id="icon-document" viewBox="0 0 48 48">
            <path d="M10 5h19l9 9v29H10zM29 5v10h9M16 22h15M16 29h10" />
            <circle cx="34" cy="34" r="7" /><path d="M34 30v8M31 32h5" />
        </symbol>
        <symbol id="icon-shield" viewBox="0 0 48 48">
            <path d="M24 5 40 11v12c0 10-7 17-16 21C15 40 8 33 8 23V11zM24 14v20M20 18h8M20 30h8" />
        </symbol>
        <symbol id="icon-person" viewBox="0 0 48 48">
            <circle cx="24" cy="13" r="7" /><path d="M11 42V29c0-7 5-11 13-11s13 4 13 11v13M18 42V31h12v11" />
        </symbol>
        <symbol id="icon-clipboard" viewBox="0 0 48 48">
            <rect x="9" y="8" width="30" height="36" rx="2" />
            <path d="M18 8V4h12v4M16 19h3M23 19h10M16 27h3M23 27h10M16 35h3M23 35h10" />
        </symbol>
        <symbol id="icon-folder" viewBox="0 0 48 48">
            <path d="M4 14h15l5 5h20l-5 23H9zM6 14V9h15l4 5M9 27h30" />
        </symbol>
        <symbol id="icon-news" viewBox="0 0 48 48">
            <rect x="7" y="5" width="31" height="38" rx="2" />
            <path d="M14 13h10v10H14zM28 14h5M28 20h5M14 29h19M14 35h19M38 13h4v25c0 3-2 5-5 5" />
        </symbol>
        <symbol id="icon-chat" viewBox="0 0 48 48">
            <path d="M5 9h29v22H16L7 39v-8H5zM20 17h.1M27 17h.1M13 17h.1" />
            <path d="M34 18h9v20h-5v6l-8-6H19v-7" />
        </symbol>
        <symbol id="icon-search" viewBox="0 0 48 48">
            <circle cx="21" cy="21" r="13" /><path d="m31 31 11 11" />
        </symbol>
        <symbol id="icon-mail" viewBox="0 0 48 48">
            <rect x="5" y="10" width="38" height="28" rx="2" /><path d="m7 13 17 14 17-14" />
        </symbol>
        <symbol id="icon-book" viewBox="0 0 48 48">
            <path d="M5 9h14c5 0 7 3 7 7v25c0-4-3-6-7-6H5zM43 9H29c-2 0-3 1-3 3v29c0-4 3-6 7-6h10z" />
        </symbol>
        <symbol id="icon-clock" viewBox="0 0 48 48">
            <circle cx="24" cy="24" r="19" /><path d="M24 13v12l8 5" />
        </symbol>
        <symbol id="icon-gavel" viewBox="0 0 48 48">
            <path d="m9 11 12 12M17 5l16 16-6 6L11 11zM29 25 43 39M6 42h28" />
        </symbol>
        <symbol id="icon-instagram" viewBox="0 0 48 48">
            <rect x="7" y="7" width="34" height="34" rx="9" /><circle cx="24" cy="24" r="8" /><circle cx="34" cy="14" r="1" />
        </symbol>
        <symbol id="icon-menu" viewBox="0 0 48 48">
            <path d="M7 13h34M7 24h34M7 35h34" />
        </symbol>
        <symbol id="icon-close" viewBox="0 0 48 48">
            <path d="M10 10l28 28M38 10 10 38" />
        </symbol>
        <symbol id="icon-lang" viewBox="0 0 48 48">
            <circle cx="24" cy="24" r="18" /><path d="M6 24h36M24 6c-5 5-8 11-8 18s3 13 8 18M24 6c5 5 8 11 8 18s-3 13-8 18" />
        </symbol>
    </svg>

    <!-- Announcement Bar -->
    <div class="announcement">
        <div class="container announcement-inner">
            <p><?php echo $strings['announcement']; ?></p>
            <nav aria-label="Utility navigation">
                <a href="/blog"><?php echo $strings['nav_blog']; ?></a>
                <a href="/#resources"><?php echo $strings['nav_resources']; ?></a>
                <a href="/about"><?php echo $strings['nav_about']; ?></a>
                <a href="/contact"><?php echo $strings['nav_contact']; ?></a>
                <!-- Language Switch -->
                <a href="<?php echo langSwitchURL($lang === 'en' ? 'hi' : 'en'); ?>" class="lang-switch" title="<?php echo $strings['lang_switch']; ?>">
                    <svg aria-hidden="true" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;"><use href="#icon-lang"></use></svg>
                    <?php echo $strings['lang_switch']; ?>
                </a>
            </nav>
        </div>
    </div>

    <!-- Site Header -->
    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="/" aria-label="<?php echo SITE_NAME; ?> home">
                <svg aria-hidden="true"><use href="#icon-scales"></use></svg>
                <span>
                    <strong>LAW &amp; REASON</strong>
                    <small><?php echo $lang === 'hi' ? 'कानून को समझें। जीवन को सँवारें।' : 'Understanding Law. Navigating Life.'; ?></small>
                </span>
            </a>

            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-nav">
                <svg aria-hidden="true"><use href="#icon-menu"></use></svg>
                <span class="sr-only">Open menu</span>
            </button>

            <nav class="primary-nav" id="primary-nav" aria-label="Primary navigation">
                <a <?php echo $currentPage === 'home' ? 'class="active"' : ''; ?> href="/"><?php echo $strings['nav_home']; ?></a>
                <a <?php echo $currentPage === 'guidance' ? 'class="active"' : ''; ?> href="/pages/scenarios/"><?php echo $strings['nav_guidance']; ?></a>
                <a <?php echo $currentPage === 'legal-areas' ? 'class="active"' : ''; ?> href="/pages/legal-areas/"><?php echo $strings['nav_legal_guides']; ?></a>
                <a <?php echo $currentPage === 'blog' ? 'class="active"' : ''; ?> href="/blog/"><?php echo $strings['nav_blog']; ?></a>
                <a <?php echo $currentPage === 'resources' ? 'class="active"' : ''; ?> href="/pages/resources/"><?php echo $strings['nav_resources']; ?></a>
                <button class="button button-gold js-open-query" type="button"><?php echo $strings['nav_ask']; ?></button>
                <button class="search-button js-open-search" type="button" aria-label="<?php echo $strings['nav_search']; ?>">
                    <svg aria-hidden="true"><use href="#icon-search"></use></svg>
                </button>
            </nav>
        </div>
    </header>

    <main id="top">
