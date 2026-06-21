<?php
/**
 * Law & Reason - 404 Not Found Page
 */

http_response_code(404);

$pageTitle = 'Page Not Found | ' . 'Law & Reason';
$pageDescription = 'The page you are looking for could not be found.';
$pageCanonical = 'https://lawandreason.com';
$currentPage = '';
$bodyClass = 'page-404';

require_once __DIR__ . '/templates/header.php';
?>

<section class="not-found">
    <div class="container">
        <h1>404</h1>
        <h2><?php echo $strings['page_not_found']; ?></h2>
        <p><?php echo $lang === 'hi' ? 'जो पेज आप ढूंढ रहे हैं वह मौजूद नहीं है।' : 'The page you are looking for does not exist or has been moved.'; ?></p>
        <a class="button button-navy" href="/"><?php echo $strings['back_home']; ?></a>
    </div>
</section>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
