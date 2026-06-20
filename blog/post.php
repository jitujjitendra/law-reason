<?php
/**
 * Law & Reason - Single Blog Post Page
 * Displays full blog post content with related posts
 */

require_once __DIR__ . '/../includes/helpers.php';
startSecureSession();
setSecurityHeaders();

$lang = getCurrentLang();
$strings = require __DIR__ . '/../lang/' . $lang . '.php';

$slug = sanitize($_GET['slug'] ?? '');

if (empty($slug)) {
    http_response_code(404);
    $pageTitle = $strings['page_not_found'] . ' | ' . SITE_NAME;
    $pageDescription = '';
    $pageCanonical = SITE_URL;
    $currentPage = 'blog';
    $bodyClass = 'page-404';
    require_once __DIR__ . '/../templates/header.php';
    echo '<section class="container"><h1>' . $strings['page_not_found'] . '</h1><p><a href="/blog/">' . $strings['back_home'] . '</a></p></section>';
    require_once __DIR__ . '/../templates/footer.php';
    exit;
}

try {
    $db = getDB();

    // Fetch post
    $stmt = $db->prepare("
        SELECT p.*, c.name_en AS cat_name_en, c.name_hi AS cat_name_hi, c.slug AS cat_slug,
               a.full_name AS author_name
        FROM posts p
        LEFT JOIN categories c ON p.category = c.slug
        LEFT JOIN admin_users a ON p.author_id = a.id
        WHERE p.slug = :slug AND p.is_published = 1
    ");
    $stmt->execute([':slug' => $slug]);
    $post = $stmt->fetch();

    if (!$post) {
        http_response_code(404);
        $pageTitle = $strings['page_not_found'] . ' | ' . SITE_NAME;
        $pageDescription = '';
        $pageCanonical = SITE_URL;
        $currentPage = 'blog';
        $bodyClass = 'page-404';
        require_once __DIR__ . '/../templates/header.php';
        echo '<section class="container"><h1>' . $strings['page_not_found'] . '</h1><p><a href="/blog/">' . $strings['back_home'] . '</a></p></section>';
        require_once __DIR__ . '/../templates/footer.php';
        exit;
    }

    // Increment view counter
    $updateStmt = $db->prepare("UPDATE posts SET views = views + 1 WHERE id = :id");
    $updateStmt->execute([':id' => $post['id']]);

    // Fetch related posts (same category, excluding current)
    $relatedStmt = $db->prepare("
        SELECT id, slug, title_en, title_hi, excerpt_en, excerpt_hi, featured_image_thumb, published_at
        FROM posts
        WHERE category = :category AND id != :id AND is_published = 1
        ORDER BY published_at DESC
        LIMIT 3
    ");
    $relatedStmt->execute([':category' => $post['category'], ':id' => $post['id']]);
    $relatedPosts = $relatedStmt->fetchAll();

} catch (PDOException $e) {
    error_log('Blog post error: ' . $e->getMessage());
    http_response_code(500);
    $pageTitle = $strings['error_general'] . ' | ' . SITE_NAME;
    $pageDescription = '';
    $pageCanonical = SITE_URL;
    $currentPage = 'blog';
    $bodyClass = 'page-error';
    require_once __DIR__ . '/../templates/header.php';
    echo '<section class="container"><h1>' . $strings['error_general'] . '</h1></section>';
    require_once __DIR__ . '/../templates/footer.php';
    exit;
}

// Page meta
$title = getLangValue($post, 'title');
$metaTitle = getLangValue($post, 'meta_title') ?: $title;
$metaDesc = getLangValue($post, 'meta_description') ?: truncateText(getLangValue($post, 'excerpt'), 160);
$pageTitle = $metaTitle . ' | ' . SITE_NAME;
$pageDescription = $metaDesc;
$pageCanonical = canonicalURL('blog/' . $post['slug']);
$pageImage = $post['featured_image'] ? SITE_URL . $post['featured_image'] : SITE_URL . '/assets/law-reason-hero.png';
$currentPage = 'blog';
$bodyClass = 'page-blog-post';

$breadcrumbs = getBreadcrumbs([
    ['label' => $strings['nav_home'], 'url' => SITE_URL],
    ['label' => $strings['blog_title'], 'url' => canonicalURL('blog/')],
    ['label' => $title, 'url' => '']
]);

// Article JSON-LD schema
$articleSchema = [
    'title' => $title,
    'description' => $metaDesc,
    'image' => $pageImage,
    'published' => $post['published_at'],
    'modified' => $post['updated_at']
];

require_once __DIR__ . '/../templates/header.php';
?>

    <article class="blog-post">
        <div class="container">
            <header class="blog-post-header">
                <?php if (!empty($post['cat_name_en'])): ?>
                <a href="/blog/?category=<?php echo htmlspecialchars($post['cat_slug']); ?>" class="blog-post-category">
                    <?php echo htmlspecialchars($lang === 'hi' ? ($post['cat_name_hi'] ?? $post['cat_name_en']) : $post['cat_name_en']); ?>
                </a>
                <?php endif; ?>

                <h1><?php echo htmlspecialchars($title); ?></h1>

                <div class="blog-post-meta">
                    <time datetime="<?php echo $post['published_at']; ?>">
                        <?php echo $strings['blog_published']; ?>
                        <?php echo $lang === 'hi' ? formatDateHindi($post['published_at']) : formatDate($post['published_at']); ?>
                    </time>
                    <?php if ($post['author_name']): ?>
                    <span class="blog-post-author"><?php echo htmlspecialchars($post['author_name']); ?></span>
                    <?php endif; ?>
                </div>
            </header>

            <?php if ($post['featured_image']): ?>
            <figure class="blog-post-image">
                <img src="<?php echo htmlspecialchars($post['featured_image']); ?>"
                     alt="<?php echo htmlspecialchars($post['featured_image_alt'] ?? $title); ?>"
                     width="1200" height="630">
            </figure>
            <?php endif; ?>

            <div class="blog-post-content">
                <?php echo getLangValue($post, 'content'); ?>
            </div>

            <?php if ($post['tags']): ?>
            <div class="blog-post-tags">
                <strong><?php echo $strings['blog_tags']; ?>:</strong>
                <?php
                $tags = array_map('trim', explode(',', $post['tags']));
                foreach ($tags as $tag):
                ?>
                <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </article>

    <?php if (!empty($relatedPosts)): ?>
    <section class="blog-related">
        <div class="container">
            <h2><?php echo $strings['blog_related']; ?></h2>
            <div class="blog-grid">
                <?php foreach ($relatedPosts as $related): ?>
                <article class="blog-card">
                    <?php if ($related['featured_image_thumb']): ?>
                    <a href="/blog/<?php echo htmlspecialchars($related['slug']); ?>" class="blog-card-image">
                        <img src="<?php echo htmlspecialchars($related['featured_image_thumb']); ?>"
                             alt="<?php echo htmlspecialchars(getLangValue($related, 'title')); ?>"
                             loading="lazy" width="400" height="250">
                    </a>
                    <?php endif; ?>
                    <div class="blog-card-body">
                        <h3 class="blog-card-title">
                            <a href="/blog/<?php echo htmlspecialchars($related['slug']); ?>">
                                <?php echo htmlspecialchars(getLangValue($related, 'title')); ?>
                            </a>
                        </h3>
                        <time class="blog-card-date" datetime="<?php echo $related['published_at']; ?>">
                            <?php echo $lang === 'hi' ? formatDateHindi($related['published_at']) : formatDate($related['published_at']); ?>
                        </time>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
