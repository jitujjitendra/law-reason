<?php
/**
 * Law & Reason - Legal Areas Listing Page
 * Displays all 8 legal topics as cards
 */

require_once __DIR__ . '/../../includes/helpers.php';
startSecureSession();
setSecurityHeaders();

$lang = getCurrentLang();
$strings = require __DIR__ . '/../../lang/' . $lang . '.php';

try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM topics WHERE is_published = 1 ORDER BY sort_order ASC");
    $topics = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Legal areas listing error: ' . $e->getMessage());
    $topics = [];
}

// Page meta
$pageTitle = $strings['nav_legal_guides'] . ' | ' . SITE_NAME;
$pageDescription = $lang === 'hi'
    ? 'संपत्ति, परिवार, उपभोक्ता, रोजगार और अन्य कानूनी विषयों पर जानकारी प्राप्त करें।'
    : 'Explore information on property, family, consumer, employment, and other legal topics.';
$pageCanonical = canonicalURL('legal-areas/');
$currentPage = 'legal-areas';
$bodyClass = 'page-legal-areas';

$breadcrumbs = getBreadcrumbs([
    ['label' => $strings['nav_home'], 'url' => SITE_URL],
    ['label' => $strings['nav_legal_guides'], 'url' => '']
]);

require_once __DIR__ . '/../../templates/header.php';
?>

    <section class="legal-areas-page">
        <div class="container">
            <p class="eyebrow"><?php echo $strings['areas_eyebrow']; ?></p>
            <h1><?php echo $strings['areas_title']; ?></h1>

            <?php if (!empty($topics)): ?>
            <div class="topics-grid">
                <?php foreach ($topics as $topic): ?>
                <a href="/legal-areas/<?php echo htmlspecialchars($topic['slug']); ?>" class="topic-card">
                    <?php if ($topic['icon']): ?>
                    <svg class="topic-card-icon" aria-hidden="true"><use href="#<?php echo htmlspecialchars($topic['icon']); ?>"></use></svg>
                    <?php endif; ?>
                    <h2><?php echo htmlspecialchars(getLangValue($topic, 'title')); ?></h2>
                    <p><?php echo htmlspecialchars(getLangValue($topic, 'description')); ?></p>
                </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p><?php echo $strings['search_no_results']; ?></p>
            <?php endif; ?>
        </div>
    </section>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
