<?php
/**
 * Law & Reason - Newsletter Subscription Handler
 * Handles newsletter sign-up from footer form
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
    echo json_encode(['success' => true, 'message' => $strings['weekly_success']]);
    exit;
}

// Rate limiting
if (isRateLimited('subscribe', 5, 600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many requests. Please try again later.']);
    exit;
}

// Validate email
$email = sanitizeEmail($_POST['email'] ?? '');

if (!isValidEmail($email)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
    exit;
}

try {
    $db = getDB();

    // Check for duplicate
    $stmt = $db->prepare("SELECT id, is_active FROM subscribers WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['is_active']) {
            // Already subscribed
            echo json_encode(['success' => true, 'message' => $strings['weekly_success']]);
        } else {
            // Reactivate subscription
            $stmt = $db->prepare("UPDATE subscribers SET is_active = 1, unsubscribed_at = NULL WHERE id = :id");
            $stmt->execute([':id' => $existing['id']]);
            echo json_encode(['success' => true, 'message' => $strings['weekly_success']]);
        }
        exit;
    }

    // Insert new subscriber
    $stmt = $db->prepare("INSERT INTO subscribers (email, is_active, subscribed_at) VALUES (:email, 1, NOW())");
    $stmt->execute([':email' => $email]);

    echo json_encode(['success' => true, 'message' => $strings['weekly_success']]);

} catch (PDOException $e) {
    error_log('Subscription error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $strings['error_general']]);
}
