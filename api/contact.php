<?php
/**
 * Law & Reason - Contact Form Handler (Ask Law & Reason)
 * Handles form submissions from the Ask modal
 */

require_once __DIR__ . '/../includes/helpers.php';
startSecureSession();

header('Content-Type: application/json; charset=utf-8');
setSecurityHeaders();

$lang = getCurrentLang();
$strings = require __DIR__ . '/../lang/' . $lang . '.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Verify CSRF token
$token = $_POST[CSRF_TOKEN_NAME] ?? '';
if (!verifyCSRFToken($token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.']);
    exit;
}

// Check honeypot (spam protection)
if (isHoneypotFilled('website_url')) {
    // Silently reject spam
    echo json_encode(['success' => true, 'message' => $strings['ask_success']]);
    exit;
}

// Rate limiting
if (isRateLimited('contact', 3, 600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many requests. Please try again later.']);
    exit;
}

// Sanitize and validate inputs
$name = sanitize($_POST['name'] ?? '');
$email = sanitizeEmail($_POST['email'] ?? '');
$area = sanitize($_POST['area'] ?? '');
$query = sanitize($_POST['query'] ?? '');

// Validation
$errors = [];

if (empty($name) || mb_strlen($name) < 2) {
    $errors[] = 'Please provide a valid name.';
}

if (!isValidEmail($email)) {
    $errors[] = 'Please provide a valid email address.';
}

if (empty($area)) {
    $errors[] = 'Please select a legal area.';
}

if (empty($query) || mb_strlen($query) < 10) {
    $errors[] = 'Please describe your concern in at least 10 characters.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Save to database
try {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO contact_messages (name, email, legal_area, message, ip_address, created_at)
        VALUES (:name, :email, :area, :message, :ip, NOW())
    ");
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':area' => $area,
        ':message' => $query,
        ':ip' => getClientIP()
    ]);

    // Send email notification
    $notificationEmail = getSiteSetting('notification_email') ?: SITE_EMAIL;
    $subject = 'New Query - ' . ucfirst($area) . ' | ' . SITE_NAME;
    $body = "<h3>New Query Received</h3>";
    $body .= "<p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>";
    $body .= "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
    $body .= "<p><strong>Legal Area:</strong> " . htmlspecialchars($area) . "</p>";
    $body .= "<p><strong>Query:</strong></p><p>" . nl2br(htmlspecialchars($query)) . "</p>";
    $body .= "<p><small>Submitted on " . date('d M Y, h:i A') . " from IP: " . getClientIP() . "</small></p>";

    sendNotificationEmail($notificationEmail, $subject, $body);

    echo json_encode(['success' => true, 'message' => $strings['ask_success']]);

} catch (PDOException $e) {
    error_log('Contact form error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $strings['error_general']]);
}
