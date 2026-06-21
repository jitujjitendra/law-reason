<?php
/**
 * Law & Reason - Scenarios Listing Page
 * Lists all "What Should I Do?" scenarios
 */

require_once __DIR__ . '/../../includes/helpers.php';
startSecureSession();
setSecurityHeaders();

$lang = getCurrentLang();
$strings = require __DIR__ . '/../../lang/' . $lang . '.php';

try {
    $db = getDB();
    $stmt = $db->query("
        SELECT s.*, t.title_en AS topic_title_en, t.title_hi AS topic_title_hi, t.slug AS topic_slug
        FROM scenarios s
        LEFT JOIN topics t ON s.topic_id = t.id
        WHERE s.is_published = 1
        ORDER BY s.sort_order ASC
    ");
    $scenarios = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Scenarios listing error: ' . $e->getMessage());
    $scenarios = [];
}

// Page meta
$pageTitle = $strings['guidance_title'] . ' | ' . SITE_NAME;
$pageDescription = $lang === 'hi'
    ? 'आम कानूनी स्थितियों के लिए चरण-दर-चरण मार्गदर्शन।'
    : 'Step-by-step guidance for common legal situations.';
$pageCanonical = canonicalURL('scenarios/');
$currentPage = 'guidance';
$bodyClass = 'page-scenarios';

$breadcrumbs = getBreadcrumbs([
    ['label' => $strings['nav_home'], 'url' => SITE_URL],
    ['label' => $strings['guidance_title'], 'url' => '']
]);

require_once __DIR__ . '/../../templates/header.php';
?>

    <section class="scenarios-page">
        <div class="container">
            <p class="eyebrow"><?php echo $strings['guidance_eyebrow']; ?></p>
            <h1><?php echo $strings['guidance_title']; ?></h1>
            <p class="section-intro"><?php echo $strings['guidance_copy']; ?></p>

            <?php if (!empty($scenarios)): ?>
            <div class="scenarios-grid">
                <?php foreach ($scenarios as $scenario): ?>
                <a href="/scenarios/<?php echo htmlspecialchars($scenario['slug']); ?>" class="scenario-card">
                    <h2><?php echo htmlspecialchars(getLangValue($scenario, 'question')); ?></h2>
                    <?php if (!empty($scenario['topic_title_en'])): ?>
                    <span class="scenario-topic">
                        <?php echo htmlspecialchars($lang === 'hi' ? ($scenario['topic_title_hi'] ?? $scenario['topic_title_en']) : $scenario['topic_title_en']); ?>
                    </span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p><?php echo $strings['search_no_results']; ?></p>
            <?php endif; ?>
        </div>
    </section>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
