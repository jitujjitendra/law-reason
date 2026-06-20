<?php
/**
 * Law & Reason - Myths vs Reality Listing Page
 * Lists all legal myths with their reality counterparts
 */

require_once __DIR__ . '/../../includes/helpers.php';
startSecureSession();
setSecurityHeaders();

$lang = getCurrentLang();
$strings = require __DIR__ . '/../../lang/' . $lang . '.php';

try {
    $db = getDB();
    $stmt = $db->query("
        SELECT m.*, t.title_en AS topic_title_en, t.title_hi AS topic_title_hi
        FROM myths m
        LEFT JOIN topics t ON m.topic_id = t.id
        WHERE m.is_published = 1
        ORDER BY m.sort_order ASC
    ");
    $myths = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Myths listing error: ' . $e->getMessage());
    $myths = [];
}

// Page meta
$pageTitle = $strings['myths_title'] . ' | ' . SITE_NAME;
$pageDescription = $lang === 'hi'
    ? 'कानूनी मिथकों और वास्तविकता के बीच का अंतर जानें।'
    : 'Know the difference between legal myths and reality.';
$pageCanonical = canonicalURL('myths/');
$currentPage = 'myths';
$bodyClass = 'page-myths';

$breadcrumbs = getBreadcrumbs([
    ['label' => $strings['nav_home'], 'url' => SITE_URL],
    ['label' => $strings['myths_title'], 'url' => '']
]);

require_once __DIR__ . '/../../templates/header.php';
?>

    <section class="myths-page">
        <div class="container">
            <p class="eyebrow"><?php echo $strings['myths_eyebrow']; ?></p>
            <h1><?php echo $strings['myths_title']; ?></h1>

            <?php if (!empty($myths)): ?>
            <div class="myths-grid">
                <?php foreach ($myths as $myth): ?>
                <div class="myth-card">
                    <?php if ($myth['icon']): ?>
                    <svg class="myth-card-icon" aria-hidden="true"><use href="#<?php echo htmlspecialchars($myth['icon']); ?>"></use></svg>
                    <?php endif; ?>
                    <div class="myth-card-body">
                        <div class="myth-label">
                            <span class="label-myth"><?php echo $strings['myths_label_myth']; ?></span>
                            <p><?php echo htmlspecialchars(getLangValue($myth, 'myth')); ?></p>
                        </div>
                        <div class="reality-label">
                            <span class="label-reality"><?php echo $strings['myths_label_reality']; ?></span>
                            <p><?php echo htmlspecialchars(getLangValue($myth, 'reality')); ?></p>
                        </div>
                        <a href="/myths/<?php echo htmlspecialchars($myth['slug']); ?>" class="myth-read-more">
                            <?php echo $strings['myths_read_more']; ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p><?php echo $strings['search_no_results']; ?></p>
            <?php endif; ?>
        </div>
    </section>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
