<?php
/**
 * Law & Reason - Search Page
 * Searches across posts, topics, scenarios, and myths
 */

require_once __DIR__ . '/includes/helpers.php';
startSecureSession();
setSecurityHeaders();

$lang = getCurrentLang();
$strings = require __DIR__ . '/lang/' . $lang . '.php';

$query = sanitize($_GET['q'] ?? '');
$results = [
    'posts' => [],
    'topics' => [],
    'scenarios' => [],
    'myths' => []
];
$totalResults = 0;

if (!empty($query) && mb_strlen($query) >= 2) {
    try {
        $db = getDB();
        $searchTerm = '%' . $query . '%';

        // Search posts
        $stmt = $db->prepare("
            SELECT id, slug, title_en, title_hi, excerpt_en, excerpt_hi, published_at
            FROM posts
            WHERE is_published = 1
              AND (title_en LIKE :q1 OR title_hi LIKE :q2 OR content_en LIKE :q3 OR content_hi LIKE :q4)
            ORDER BY published_at DESC
            LIMIT 10
        ");
        $stmt->execute([':q1' => $searchTerm, ':q2' => $searchTerm, ':q3' => $searchTerm, ':q4' => $searchTerm]);
        $results['posts'] = $stmt->fetchAll();

        // Search topics
        $stmt = $db->prepare("
            SELECT id, slug, title_en, title_hi, description_en, description_hi
            FROM topics
            WHERE is_published = 1
              AND (title_en LIKE :q1 OR title_hi LIKE :q2 OR description_en LIKE :q3 OR description_hi LIKE :q4)
            ORDER BY sort_order ASC
            LIMIT 10
        ");
        $stmt->execute([':q1' => $searchTerm, ':q2' => $searchTerm, ':q3' => $searchTerm, ':q4' => $searchTerm]);
        $results['topics'] = $stmt->fetchAll();

        // Search scenarios
        $stmt = $db->prepare("
            SELECT id, slug, question_en, question_hi
            FROM scenarios
            WHERE is_published = 1
              AND (question_en LIKE :q1 OR question_hi LIKE :q2)
            ORDER BY sort_order ASC
            LIMIT 10
        ");
        $stmt->execute([':q1' => $searchTerm, ':q2' => $searchTerm]);
        $results['scenarios'] = $stmt->fetchAll();

        // Search myths
        $stmt = $db->prepare("
            SELECT id, slug, myth_en, myth_hi, reality_en, reality_hi
            FROM myths
            WHERE is_published = 1
              AND (myth_en LIKE :q1 OR myth_hi LIKE :q2 OR reality_en LIKE :q3 OR reality_hi LIKE :q4)
            ORDER BY sort_order ASC
            LIMIT 10
        ");
        $stmt->execute([':q1' => $searchTerm, ':q2' => $searchTerm, ':q3' => $searchTerm, ':q4' => $searchTerm]);
        $results['myths'] = $stmt->fetchAll();

        $totalResults = count($results['posts']) + count($results['topics']) + count($results['scenarios']) + count($results['myths']);

    } catch (PDOException $e) {
        error_log('Search error: ' . $e->getMessage());
    }
}

// Page meta
$pageTitle = $strings['nav_search'];
if ($query) {
    $pageTitle .= ': ' . $query;
}
$pageTitle .= ' | ' . SITE_NAME;
$pageDescription = $lang === 'hi'
    ? 'कानूनी विषयों, ब्लॉग लेखों, और संसाधनों में खोजें।'
    : 'Search across legal topics, blog articles, and resources.';
$pageCanonical = canonicalURL('search');
$currentPage = 'search';
$bodyClass = 'page-search';

$breadcrumbs = getBreadcrumbs([
    ['label' => $strings['nav_home'], 'url' => SITE_URL],
    ['label' => $strings['nav_search'], 'url' => '']
]);

require_once __DIR__ . '/templates/header.php';
?>

    <section class="search-page">
        <div class="container">
            <h1><?php echo $strings['nav_search']; ?></h1>

            <form class="search-form-page" action="/search" method="GET">
                <label class="sr-only" for="search-q"><?php echo $strings['search_placeholder']; ?></label>
                <input id="search-q" type="search" name="q" value="<?php echo htmlspecialchars($query); ?>"
                       placeholder="<?php echo $strings['search_placeholder']; ?>" required>
                <button class="button button-navy" type="submit"><?php echo $strings['search_btn']; ?></button>
            </form>

            <?php if (!empty($query)): ?>
            <p class="search-summary">
                <?php
                if ($totalResults > 0) {
                    echo $lang === 'hi'
                        ? '"' . htmlspecialchars($query) . '" के लिए ' . $totalResults . ' परिणाम मिले'
                        : $totalResults . ' result' . ($totalResults !== 1 ? 's' : '') . ' found for "' . htmlspecialchars($query) . '"';
                } else {
                    echo $strings['search_no_results'];
                }
                ?>
            </p>

            <?php if (!empty($results['topics'])): ?>
            <div class="search-group">
                <h2><?php echo $strings['nav_legal_guides']; ?></h2>
                <ul class="search-results">
                    <?php foreach ($results['topics'] as $item): ?>
                    <li>
                        <a href="/legal-areas/<?php echo htmlspecialchars($item['slug']); ?>">
                            <strong><?php echo htmlspecialchars(getLangValue($item, 'title')); ?></strong>
                        </a>
                        <p><?php echo htmlspecialchars(truncateText(getLangValue($item, 'description'), 150)); ?></p>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if (!empty($results['scenarios'])): ?>
            <div class="search-group">
                <h2><?php echo $strings['nav_guidance']; ?></h2>
                <ul class="search-results">
                    <?php foreach ($results['scenarios'] as $item): ?>
                    <li>
                        <a href="/scenarios/<?php echo htmlspecialchars($item['slug']); ?>">
                            <strong><?php echo htmlspecialchars(getLangValue($item, 'question')); ?></strong>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if (!empty($results['posts'])): ?>
            <div class="search-group">
                <h2><?php echo $strings['blog_title']; ?></h2>
                <ul class="search-results">
                    <?php foreach ($results['posts'] as $item): ?>
                    <li>
                        <a href="/blog/<?php echo htmlspecialchars($item['slug']); ?>">
                            <strong><?php echo htmlspecialchars(getLangValue($item, 'title')); ?></strong>
                        </a>
                        <p><?php echo htmlspecialchars(truncateText(getLangValue($item, 'excerpt'), 150)); ?></p>
                        <time datetime="<?php echo $item['published_at']; ?>">
                            <?php echo $lang === 'hi' ? formatDateHindi($item['published_at']) : formatDate($item['published_at']); ?>
                        </time>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if (!empty($results['myths'])): ?>
            <div class="search-group">
                <h2><?php echo $strings['myths_title']; ?></h2>
                <ul class="search-results">
                    <?php foreach ($results['myths'] as $item): ?>
                    <li>
                        <a href="/myths/<?php echo htmlspecialchars($item['slug']); ?>">
                            <strong><?php echo htmlspecialchars(getLangValue($item, 'myth')); ?></strong>
                        </a>
                        <p><?php echo htmlspecialchars(truncateText(getLangValue($item, 'reality'), 150)); ?></p>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php endif; ?>
        </div>
    </section>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
