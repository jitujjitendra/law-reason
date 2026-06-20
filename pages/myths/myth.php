<?php
/**
 * Law & Reason - Single Myth Detail Page
 * Shows full myth vs reality explanation
 */

require_once __DIR__ . '/../../includes/helpers.php';
startSecureSession();
setSecurityHeaders();

$lang = getCurrentLang();
$strings = require __DIR__ . '/../../lang/' . $lang . '.php';

$slug = sanitize($_GET['slug'] ?? '');

if (empty($slug)) {
    http_response_code(404);
    $pageTitle = $strings['page_not_found'] . ' | ' . SITE_NAME;
    $pageDescription = '';
    $pageCanonical = SITE_URL;
    $currentPage = 'myths';
    $bodyClass = 'page-404';
    require_once __DIR__ . '/../../templates/header.php';
    echo '<section class="container"><h1>' . $strings['page_not_found'] . '</h1><p><a href="/myths/">' . $strings['back_home'] . '</a></p></section>';
    require_once __DIR__ . '/../../templates/footer.php';
    exit;
}

try {
    $db = getDB();

    // Fetch myth
    $stmt = $db->prepare("
        SELECT m.*, t.title_en AS topic_title_en, t.title_hi AS topic_title_hi, t.slug AS topic_slug
        FROM myths m
        LEFT JOIN topics t ON m.topic_id = t.id
        WHERE m.slug = :slug AND m.is_published = 1
    ");
    $stmt->execute([':slug' => $slug]);
    $myth = $stmt->fetch();

    if (!$myth) {
        http_response_code(404);
        $pageTitle = $strings['page_not_found'] . ' | ' . SITE_NAME;
        $pageDescription = '';
        $pageCanonical = SITE_URL;
        $currentPage = 'myths';
        $bodyClass = 'page-404';
        require_once __DIR__ . '/../../templates/header.php';
        echo '<section class="container"><h1>' . $strings['page_not_found'] . '</h1><p><a href="/myths/">' . $strings['back_home'] . '</a></p></section>';
        require_once __DIR__ . '/../../templates/footer.php';
        exit;
    }

} catch (PDOException $e) {
    error_log('Myth page error: ' . $e->getMessage());
    http_response_code(500);
    $pageTitle = $strings['error_general'] . ' | ' . SITE_NAME;
    $pageDescription = '';
    $pageCanonical = SITE_URL;
    $currentPage = 'myths';
    $bodyClass = 'page-error';
    require_once __DIR__ . '/../../templates/header.php';
    echo '<section class="container"><h1>' . $strings['error_general'] . '</h1></section>';
    require_once __DIR__ . '/../../templates/footer.php';
    exit;
}

// Page meta
$mythText = getLangValue($myth, 'myth');
$metaTitle = getLangValue($myth, 'meta_title') ?: $mythText;
$metaDesc = getLangValue($myth, 'meta_description') ?: truncateText(getLangValue($myth, 'reality'), 160);
$pageTitle = $metaTitle . ' | ' . SITE_NAME;
$pageDescription = $metaDesc;
$pageCanonical = canonicalURL('myths/' . $myth['slug']);
$currentPage = 'myths';
$bodyClass = 'page-myth-detail';

$breadcrumbs = getBreadcrumbs([
    ['label' => $strings['nav_home'], 'url' => SITE_URL],
    ['label' => $strings['myths_title'], 'url' => canonicalURL('myths/')],
    ['label' => $mythText, 'url' => '']
]);

// FAQ schema for this myth
$faqSchema = [
    [
        'question' => $mythText,
        'answer' => strip_tags(getLangValue($myth, 'reality'))
    ]
];

require_once __DIR__ . '/../../templates/header.php';
?>

    <article class="myth-detail-page">
        <div class="container">
            <header class="myth-detail-header">
                <?php if ($myth['icon']): ?>
                <svg class="myth-detail-icon" aria-hidden="true"><use href="#<?php echo htmlspecialchars($myth['icon']); ?>"></use></svg>
                <?php endif; ?>

                <div class="myth-detail-summary">
                    <div class="myth-label">
                        <span class="label-myth"><?php echo $strings['myths_label_myth']; ?></span>
                        <h1><?php echo htmlspecialchars($mythText); ?></h1>
                    </div>
                    <div class="reality-label">
                        <span class="label-reality"><?php echo $strings['myths_label_reality']; ?></span>
                        <p class="reality-text"><?php echo htmlspecialchars(getLangValue($myth, 'reality')); ?></p>
                    </div>
                </div>
            </header>

            <?php $detailContent = getLangValue($myth, 'detail_content'); ?>
            <?php if (!empty($detailContent)): ?>
            <div class="myth-detail-content">
                <?php echo $detailContent; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($myth['topic_title_en'])): ?>
            <div class="myth-related-topic">
                <p>
                    <?php echo $lang === 'hi' ? 'संबंधित विषय:' : 'Related topic:'; ?>
                    <a href="/legal-areas/<?php echo htmlspecialchars($myth['topic_slug']); ?>">
                        <?php echo htmlspecialchars($lang === 'hi' ? ($myth['topic_title_hi'] ?? $myth['topic_title_en']) : $myth['topic_title_en']); ?>
                    </a>
                </p>
            </div>
            <?php endif; ?>

            <div class="myth-cta">
                <a href="/myths/" class="button button-navy"><?php echo $strings['myths_view_all']; ?></a>
            </div>
        </div>
    </article>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
