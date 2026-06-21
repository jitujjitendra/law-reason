<?php
/**
 * Law & Reason - Blog Listing Page
 * Displays published blog posts with pagination and category filtering
 */

require_once __DIR__ . '/../includes/helpers.php';
startSecureSession();
setSecurityHeaders();

$lang = getCurrentLang();
$strings = require __DIR__ . '/../lang/' . $lang . '.php';

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = POSTS_PER_PAGE;

// Category filter
$categorySlug = isset($_GET['category']) ? sanitize($_GET['category']) : null;
$categoryName = '';

try {
    $db = getDB();

    // Get category name if filtering
    if ($categorySlug) {
        $stmt = $db->prepare("SELECT * FROM categories WHERE slug = :slug");
        $stmt->execute([':slug' => $categorySlug]);
        $category = $stmt->fetch();
        if ($category) {
            $categoryName = getLangValue($category, 'name');
        }
    }

    // Count total posts
    $countSQL = "SELECT COUNT(*) FROM posts WHERE is_published = 1";
    $params = [];
    if ($categorySlug) {
        $countSQL .= " AND category = :category";
        $params[':category'] = $categorySlug;
    }
    $stmt = $db->prepare($countSQL);
    $stmt->execute($params);
    $totalPosts = (int)$stmt->fetchColumn();

    $pagination = getPagination($totalPosts, $page, $perPage);

    // Fetch posts
    $sql = "SELECT p.*, c.name_en AS cat_name_en, c.name_hi AS cat_name_hi, c.slug AS cat_slug
            FROM posts p
            LEFT JOIN categories c ON p.category = c.slug
            WHERE p.is_published = 1";
    if ($categorySlug) {
        $sql .= " AND p.category = :category";
    }
    $sql .= " ORDER BY p.published_at DESC LIMIT :limit OFFSET :offset";

    $stmt = $db->prepare($sql);
    if ($categorySlug) {
        $stmt->bindValue(':category', $categorySlug, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
    $stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll();

    // Fetch all categories for filter
    $catStmt = $db->query("SELECT * FROM categories ORDER BY sort_order ASC");
    $categories = $catStmt->fetchAll();

} catch (PDOException $e) {
    error_log('Blog listing error: ' . $e->getMessage());
    $posts = [];
    $categories = [];
    $totalPosts = 0;
    $pagination = getPagination(0, 1, $perPage);
}

// Page meta
$pageTitle = $strings['blog_title'];
if ($categoryName) {
    $pageTitle .= ' - ' . $categoryName;
}
$pageTitle .= ' | ' . SITE_NAME;
$pageDescription = $lang === 'hi'
    ? 'कानूनी जागरूकता, अपडेट और व्यावहारिक जानकारी के लिए हमारे ब्लॉग लेख पढ़ें।'
    : 'Read our blog articles on legal awareness, updates, and practical information.';
$pageCanonical = canonicalURL('blog/');
$currentPage = 'blog';
$bodyClass = 'page-blog';

$breadcrumbs = getBreadcrumbs([
    ['label' => $strings['nav_home'], 'url' => SITE_URL],
    ['label' => $strings['blog_title'], 'url' => canonicalURL('blog/')]
]);

if ($categoryName) {
    $breadcrumbs[] = ['label' => $categoryName, 'url' => ''];
}

require_once __DIR__ . '/../templates/header.php';
?>

    <section class="blog-listing">
        <div class="container">
            <p class="eyebrow"><?php echo $strings['blog_title']; ?></p>
            <h1><?php echo $categoryName ? htmlspecialchars($categoryName) : $strings['blog_latest']; ?></h1>

            <?php if (!empty($categories)): ?>
            <nav class="blog-categories" aria-label="Blog categories">
                <a href="/blog/" <?php echo !$categorySlug ? 'class="active"' : ''; ?>><?php echo $lang === 'hi' ? 'सभी' : 'All'; ?></a>
                <?php foreach ($categories as $cat): ?>
                <a href="/blog/?category=<?php echo urlencode($cat['slug']); ?>"
                   <?php echo $categorySlug === $cat['slug'] ? 'class="active"' : ''; ?>>
                    <?php echo htmlspecialchars(getLangValue($cat, 'name')); ?>
                </a>
                <?php endforeach; ?>
            </nav>
            <?php endif; ?>

            <?php if (!empty($posts)): ?>
            <div class="blog-grid">
                <?php foreach ($posts as $post): ?>
                <article class="blog-card">
                    <?php if ($post['featured_image_thumb']): ?>
                    <a href="/blog/<?php echo htmlspecialchars($post['slug']); ?>" class="blog-card-image">
                        <img src="<?php echo htmlspecialchars($post['featured_image_thumb']); ?>"
                             alt="<?php echo htmlspecialchars($post['featured_image_alt'] ?? getLangValue($post, 'title')); ?>"
                             loading="lazy" width="400" height="250">
                    </a>
                    <?php endif; ?>
                    <div class="blog-card-body">
                        <?php if (!empty($post['cat_name_en'])): ?>
                        <span class="blog-card-category">
                            <?php echo htmlspecialchars($lang === 'hi' ? ($post['cat_name_hi'] ?? $post['cat_name_en']) : $post['cat_name_en']); ?>
                        </span>
                        <?php endif; ?>
                        <h2 class="blog-card-title">
                            <a href="/blog/<?php echo htmlspecialchars($post['slug']); ?>">
                                <?php echo htmlspecialchars(getLangValue($post, 'title')); ?>
                            </a>
                        </h2>
                        <p class="blog-card-excerpt">
                            <?php echo htmlspecialchars(truncateText(getLangValue($post, 'excerpt'), 120)); ?>
                        </p>
                        <time class="blog-card-date" datetime="<?php echo $post['published_at']; ?>">
                            <?php echo $lang === 'hi' ? formatDateHindi($post['published_at']) : formatDate($post['published_at']); ?>
                        </time>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <?php if ($pagination['total_pages'] > 1): ?>
            <nav class="pagination" aria-label="Blog pagination">
                <?php if ($pagination['has_prev']): ?>
                <a href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo $categorySlug ? '&category=' . urlencode($categorySlug) : ''; ?>" rel="prev">&laquo; <?php echo $lang === 'hi' ? 'पिछला' : 'Previous'; ?></a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <a href="?page=<?php echo $i; ?><?php echo $categorySlug ? '&category=' . urlencode($categorySlug) : ''; ?>"
                   <?php echo $i === $pagination['current_page'] ? 'class="active" aria-current="page"' : ''; ?>>
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>

                <?php if ($pagination['has_next']): ?>
                <a href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo $categorySlug ? '&category=' . urlencode($categorySlug) : ''; ?>" rel="next"><?php echo $lang === 'hi' ? 'अगला' : 'Next'; ?> &raquo;</a>
                <?php endif; ?>
            </nav>
            <?php endif; ?>

            <?php else: ?>
            <p class="no-results"><?php echo $strings['blog_no_posts']; ?></p>
            <?php endif; ?>
        </div>
    </section>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
