<?php
/**
 * Law & Reason - Single Scenario Page
 * Shows full step-by-step guidance for a scenario
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
    $currentPage = 'guidance';
    $bodyClass = 'page-404';
    require_once __DIR__ . '/../../templates/header.php';
    echo '<section class="container"><h1>' . $strings['page_not_found'] . '</h1><p><a href="/scenarios/">' . $strings['back_home'] . '</a></p></section>';
    require_once __DIR__ . '/../../templates/footer.php';
    exit;
}

try {
    $db = getDB();

    // Fetch scenario
    $stmt = $db->prepare("
        SELECT s.*, t.title_en AS topic_title_en, t.title_hi AS topic_title_hi, t.slug AS topic_slug
        FROM scenarios s
        LEFT JOIN topics t ON s.topic_id = t.id
        WHERE s.slug = :slug AND s.is_published = 1
    ");
    $stmt->execute([':slug' => $slug]);
    $scenario = $stmt->fetch();

    if (!$scenario) {
        http_response_code(404);
        $pageTitle = $strings['page_not_found'] . ' | ' . SITE_NAME;
        $pageDescription = '';
        $pageCanonical = SITE_URL;
        $currentPage = 'guidance';
        $bodyClass = 'page-404';
        require_once __DIR__ . '/../../templates/header.php';
        echo '<section class="container"><h1>' . $strings['page_not_found'] . '</h1><p><a href="/scenarios/">' . $strings['back_home'] . '</a></p></section>';
        require_once __DIR__ . '/../../templates/footer.php';
        exit;
    }

} catch (PDOException $e) {
    error_log('Scenario page error: ' . $e->getMessage());
    http_response_code(500);
    $pageTitle = $strings['error_general'] . ' | ' . SITE_NAME;
    $pageDescription = '';
    $pageCanonical = SITE_URL;
    $currentPage = 'guidance';
    $bodyClass = 'page-error';
    require_once __DIR__ . '/../../templates/header.php';
    echo '<section class="container"><h1>' . $strings['error_general'] . '</h1></section>';
    require_once __DIR__ . '/../../templates/footer.php';
    exit;
}

// Page meta
$question = getLangValue($scenario, 'question');
$metaTitle = getLangValue($scenario, 'meta_title') ?: $question;
$metaDesc = getLangValue($scenario, 'meta_description') ?: truncateText(strip_tags(getLangValue($scenario, 'content')), 160);
$pageTitle = $metaTitle . ' | ' . SITE_NAME;
$pageDescription = $metaDesc;
$pageCanonical = canonicalURL('scenarios/' . $scenario['slug']);
$currentPage = 'guidance';
$bodyClass = 'page-scenario';

$breadcrumbs = getBreadcrumbs([
    ['label' => $strings['nav_home'], 'url' => SITE_URL],
    ['label' => $strings['guidance_title'], 'url' => canonicalURL('scenarios/')],
    ['label' => $question, 'url' => '']
]);

// FAQ schema (the scenario itself acts as a FAQ)
$faqSchema = [
    [
        'question' => $question,
        'answer' => truncateText(strip_tags(getLangValue($scenario, 'content')), 500)
    ]
];

require_once __DIR__ . '/../../templates/header.php';
?>

    <article class="scenario-page">
        <div class="container">
            <header class="scenario-header">
                <p class="eyebrow"><?php echo $strings['guidance_title']; ?></p>
                <h1><?php echo htmlspecialchars($question); ?></h1>
                <?php if (!empty($scenario['topic_title_en'])): ?>
                <p class="scenario-topic-link">
                    <a href="/legal-areas/<?php echo htmlspecialchars($scenario['topic_slug']); ?>">
                        <?php echo htmlspecialchars($lang === 'hi' ? ($scenario['topic_title_hi'] ?? $scenario['topic_title_en']) : $scenario['topic_title_en']); ?>
                    </a>
                </p>
                <?php endif; ?>
            </header>

            <div class="scenario-content">
                <?php echo sanitizeHTML(getLangValue($scenario, 'content')); ?>
            </div>

            <div class="scenario-features">
                <h2><?php echo $strings['guidance_feature_title']; ?></h2>
                <ul>
                    <li><?php echo $strings['guidance_f1']; ?></li>
                    <li><?php echo $strings['guidance_f2']; ?></li>
                    <li><?php echo $strings['guidance_f3']; ?></li>
                    <li><?php echo $strings['guidance_f4']; ?></li>
                    <li><?php echo $strings['guidance_f5']; ?></li>
                </ul>
            </div>

            <div class="scenario-cta">
                <a href="/scenarios/" class="button button-navy"><?php echo $strings['guidance_view_all']; ?></a>
            </div>
        </div>
    </article>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
