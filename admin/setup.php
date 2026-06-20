<?php
/**
 * Law & Reason - One-Time Setup Page
 * Set admin password on first use. Becomes inaccessible after setup.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/helpers.php';

startSecureSession();

$db = getDB();
$error = '';
$success = '';

// Check if setup is still needed
$stmt = $db->query("SELECT id, password_hash FROM admin_users WHERE role = 'super_admin' LIMIT 1");
$admin = $stmt->fetch();

// If no admin exists or password is still the placeholder, allow setup
$setupAllowed = false;
if (!$admin) {
    $setupAllowed = true;
} elseif (strpos($admin['password_hash'], '$2y$12$placeholder') !== false) {
    $setupAllowed = true;
}

if (!$setupAllowed) {
    // Setup already complete
    header('Location: /admin/login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Validation
        if (empty($username) || empty($email) || empty($fullName) || empty($password)) {
            $error = 'All fields are required.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } elseif ($password !== $passwordConfirm) {
            $error = 'Passwords do not match.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            try {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                
                if ($admin) {
                    // Update existing admin
                    $stmt = $db->prepare("UPDATE admin_users SET username = ?, email = ?, password_hash = ?, full_name = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $passwordHash, $fullName, $admin['id']]);
                } else {
                    // Create new admin
                    $stmt = $db->prepare("INSERT INTO admin_users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, 'super_admin')");
                    $stmt->execute([$username, $email, $passwordHash, $fullName]);
                }
                
                $success = 'Setup complete! You can now log in with your credentials.';
            } catch (PDOException $e) {
                error_log('Setup error: ' . $e->getMessage());
                $error = 'Setup failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Law &amp; Reason Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1e3a5f 0%, #111827 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .setup-card {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .setup-brand { text-align: center; margin-bottom: 24px; }
        .setup-brand h1 { font-size: 1.5rem; color: #111827; }
        .setup-brand h1 span { color: #2563eb; }
        .setup-brand p { color: #6b7280; font-size: 0.85rem; margin-top: 4px; }
        .setup-info {
            background: #dbeafe; color: #1d4ed8; padding: 14px 18px;
            border-radius: 8px; font-size: 0.85rem; margin-bottom: 24px;
            border: 1px solid #bfdbfe;
        }
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .form-input {
            width: 100%; padding: 12px 14px; border: 1px solid #d1d5db;
            border-radius: 8px; font-size: 0.95rem; transition: border-color 0.2s;
        }
        .form-input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px #dbeafe; }
        .btn-setup {
            width: 100%; padding: 12px; background: #2563eb; color: #fff;
            border: none; border-radius: 8px; font-size: 1rem; font-weight: 600;
            cursor: pointer; transition: background 0.2s;
        }
        .btn-setup:hover { background: #1d4ed8; }
        .error-message { background: #fee2e2; color: #dc2626; padding: 12px 16px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 20px; border: 1px solid #fecaca; }
        .success-message { background: #dcfce7; color: #16a34a; padding: 12px 16px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 20px; border: 1px solid #bbf7d0; }
        .success-message a { color: #16a34a; font-weight: 600; }
    </style>
</head>
<body>
    <div class="setup-card">
        <div class="setup-brand">
            <h1><span>Law</span> &amp; Reason</h1>
            <p>First-Time Setup</p>
        </div>

        <div class="setup-info">
            Set up your admin account. This page will become inaccessible after setup is complete.
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message"><?= $success ?> <a href="/admin/login.php">Go to Login</a></div>
        <?php else: ?>
            <form method="POST">
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label" for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-input" required 
                           value="<?= htmlspecialchars($_POST['full_name'] ?? $admin['full_name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-input" required
                           value="<?= htmlspecialchars($_POST['username'] ?? $admin['username'] ?? 'admin') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required
                           value="<?= htmlspecialchars($_POST['email'] ?? $admin['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password (min 8 characters)</label>
                    <input type="password" id="password" name="password" class="form-input" required minlength="8">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirm">Confirm Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-input" required>
                </div>

                <button type="submit" class="btn-setup">Complete Setup</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
