<?php
/**
 * Law & Reason - Single Topic Page
 * Shows topic details, related scenarios, and FAQs
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
    $currentPage = 'legal-areas';
    $bodyClass = 'page-404';
    require_once __DIR__ . '/../../templates/header.php';
    echo '<section class="container"><h1>' . $strings['page_not_found'] . '</h1><p><a href="/legal-areas/">' . $strings['back_home'] . '</a></p></section>';
    require_once __DIR__ . '/../../templates/footer.php';
    exit;
}

try {
    $db = getDB();

    // Fetch topic
    $stmt = $db->prepare("SELECT * FROM topics WHERE slug = :slug AND is_published = 1");
    $stmt->execute([':slug' => $slug]);
    $topic = $stmt->fetch();

    if (!$topic) {
        http_response_code(404);
        $pageTitle = $strings['page_not_found'] . ' | ' . SITE_NAME;
        $pageDescription = '';
        $pageCanonical = SITE_URL;
        $currentPage = 'legal-areas';
        $bodyClass = 'page-404';
        require_once __DIR__ . '/../../templates/header.php';
        echo '<section class="container"><h1>' . $strings['page_not_found'] . '</h1><p><a href="/legal-areas/">' . $strings['back_home'] . '</a></p></section>';
        require_once __DIR__ . '/../../templates/footer.php';
        exit;
    }

    // Fetch related scenarios
    $scenarioStmt = $db->prepare("
        SELECT id, slug, question_en, question_hi
        FROM scenarios
        WHERE topic_id = :topic_id AND is_published = 1
        ORDER BY sort_order ASC
    ");
    $scenarioStmt->execute([':topic_id' => $topic['id']]);
    $relatedScenarios = $scenarioStmt->fetchAll();

    // Fetch related FAQs
    $faqStmt = $db->prepare("
        SELECT question_en, question_hi, answer_en, answer_hi
        FROM faqs
        WHERE topic_id = :topic_id AND is_published = 1
        ORDER BY sort_order ASC
    ");
    $faqStmt->execute([':topic_id' => $topic['id']]);
    $faqs = $faqStmt->fetchAll();

} catch (PDOException $e) {
    error_log('Topic page error: ' . $e->getMessage());
    http_response_code(500);
    $pageTitle = $strings['error_general'] . ' | ' . SITE_NAME;
    $pageDescription = '';
    $pageCanonical = SITE_URL;
    $currentPage = 'legal-areas';
    $bodyClass = 'page-error';
    require_once __DIR__ . '/../../templates/header.php';
    echo '<section class="container"><h1>' . $strings['error_general'] . '</h1></section>';
    require_once __DIR__ . '/../../templates/footer.php';
    exit;
}

// Page meta
$title = getLangValue($topic, 'title');
$metaTitle = getLangValue($topic, 'meta_title') ?: $title;
$metaDesc = getLangValue($topic, 'meta_description') ?: truncateText(getLangValue($topic, 'description'), 160);
$pageTitle = $metaTitle . ' | ' . SITE_NAME;
$pageDescription = $metaDesc;
$pageCanonical = canonicalURL('legal-areas/' . $topic['slug']);
$currentPage = 'legal-areas';
$bodyClass = 'page-topic';

$breadcrumbs = getBreadcrumbs([
    ['label' => $strings['nav_home'], 'url' => SITE_URL],
    ['label' => $strings['nav_legal_guides'], 'url' => canonicalURL('legal-areas/')],
    ['label' => $title, 'url' => '']
]);

// FAQ schema for SEO/AEO
if (!empty($faqs)) {
    $faqSchema = [];
    foreach ($faqs as $faq) {
        $faqSchema[] = [
            'question' => getLangValue($faq, 'question'),
            'answer' => strip_tags(getLangValue($faq, 'answer'))
        ];
    }
}

require_once __DIR__ . '/../../templates/header.php';
?>

    <section class="topic-page">
        <div class="container">
            <header class="topic-header">
                <?php if ($topic['icon']): ?>
                <svg class="topic-icon" aria-hidden="true"><use href="#<?php echo htmlspecialchars($topic['icon']); ?>"></use></svg>
                <?php endif; ?>
                <h1><?php echo htmlspecialchars($title); ?></h1>
                <p class="topic-description"><?php echo htmlspecialchars(getLangValue($topic, 'description')); ?></p>
            </header>

            <?php $content = getLangValue($topic, 'content'); ?>
            <?php if (!empty($content)): ?>
            <div class="topic-content">
                <?php echo $content; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($relatedScenarios)): ?>
            <div class="topic-scenarios">
                <h2><?php echo $strings['guidance_title']; ?></h2>
                <ul class="scenario-list">
                    <?php foreach ($relatedScenarios as $scenario): ?>
                    <li>
                        <a href="/scenarios/<?php echo htmlspecialchars($scenario['slug']); ?>">
                            <?php echo htmlspecialchars(getLangValue($scenario, 'question')); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if (!empty($faqs)): ?>
            <div class="topic-faqs">
                <h2><?php echo $lang === 'hi' ? 'अक्सर पूछे जाने वाले प्रश्न' : 'Frequently Asked Questions'; ?></h2>
                <div class="faq-list">
                    <?php foreach ($faqs as $faq): ?>
                    <details class="faq-item">
                        <summary><?php echo htmlspecialchars(getLangValue($faq, 'question')); ?></summary>
                        <div class="faq-answer">
                            <?php echo getLangValue($faq, 'answer'); ?>
                        </div>
                    </details>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
