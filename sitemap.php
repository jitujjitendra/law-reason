<?php
/**
 * Law & Reason - Dynamic Sitemap Generator
 * Generates XML sitemap for SEO
 * Access via: /sitemap.xml (rewritten by .htaccess)
 */

require_once __DIR__ . '/config/database.php';

header('Content-Type: application/xml; charset=utf-8');

$baseUrl = SITE_URL;

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
    
    <!-- Homepage -->
    <url>
        <loc><?php echo $baseUrl; ?>/</loc>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
        <xhtml:link rel="alternate" hreflang="en" href="<?php echo $baseUrl; ?>/?lang=en"/>
        <xhtml:link rel="alternate" hreflang="hi" href="<?php echo $baseUrl; ?>/?lang=hi"/>
    </url>
    
    <!-- Blog Index -->
    <url>
        <loc><?php echo $baseUrl; ?>/blog/</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>

<?php
try {
    $db = getDB();
    
    // Blog Posts
    $posts = $db->query("SELECT slug, updated_at FROM posts WHERE is_published = 1 ORDER BY published_at DESC")->fetchAll();
    foreach ($posts as $post):
?>
    <url>
        <loc><?php echo $baseUrl; ?>/blog/<?php echo htmlspecialchars($post['slug']); ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($post['updated_at'])); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
<?php endforeach; ?>

<?php
    // Topics
    $topics = $db->query("SELECT slug, updated_at FROM topics WHERE is_published = 1 ORDER BY sort_order")->fetchAll();
    foreach ($topics as $topic):
?>
    <url>
        <loc><?php echo $baseUrl; ?>/legal-areas/<?php echo htmlspecialchars($topic['slug']); ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($topic['updated_at'])); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
<?php endforeach; ?>

<?php
    // Scenarios
    $scenarios = $db->query("SELECT slug, updated_at FROM scenarios WHERE is_published = 1 ORDER BY sort_order")->fetchAll();
    foreach ($scenarios as $scenario):
?>
    <url>
        <loc><?php echo $baseUrl; ?>/scenarios/<?php echo htmlspecialchars($scenario['slug']); ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($scenario['updated_at'])); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
<?php endforeach; ?>

<?php
    // Myths
    $myths = $db->query("SELECT slug, updated_at FROM myths WHERE is_published = 1 ORDER BY sort_order")->fetchAll();
    foreach ($myths as $myth):
?>
    <url>
        <loc><?php echo $baseUrl; ?>/myths/<?php echo htmlspecialchars($myth['slug']); ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($myth['updated_at'])); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
<?php endforeach; ?>

<?php } catch (Exception $e) { /* DB not connected, skip dynamic entries */ } ?>

    <!-- Static Pages -->
    <url>
        <loc><?php echo $baseUrl; ?>/legal-areas/</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?php echo $baseUrl; ?>/scenarios/</loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc><?php echo $baseUrl; ?>/myths/</loc>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    <url>
        <loc><?php echo $baseUrl; ?>/search</loc>
        <changefreq>monthly</changefreq>
        <priority>0.4</priority>
    </url>
</urlset>
