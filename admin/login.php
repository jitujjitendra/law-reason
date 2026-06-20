<?php
/**
 * Law & Reason - Admin Login Page
 * Standalone login form with brute-force protection
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/helpers.php';

startSecureSession();

// Already logged in? Redirect to dashboard
if (isAdminLoggedIn()) {
    header('Location: /admin/');
    exit;
}

$error = '';
$username = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            try {
                $db = getDB();
                $stmt = $db->prepare("SELECT id, username, email, password_hash, full_name, role, login_attempts, locked_until FROM admin_users WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $user = $stmt->fetch();

                if ($user) {
                    // Check if account is locked
                    if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                        $remaining = ceil((strtotime($user['locked_until']) - time()) / 60);
                        $error = "Account is locked. Please try again in {$remaining} minutes.";
                    } else {
                        // Verify password
                        if (password_verify($password, $user['password_hash'])) {
                            // Success - reset attempts and create session
                            $stmt = $db->prepare("UPDATE admin_users SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?");
                            $stmt->execute([$user['id']]);

                            // Set session variables
                            $_SESSION['admin_id'] = $user['id'];
                            $_SESSION['admin_role'] = $user['role'];
                            $_SESSION['admin_name'] = $user['full_name'];
                            $_SESSION['last_activity'] = time();

                            // Regenerate session ID for security
                            session_regenerate_id(true);

                            header('Location: /admin/');
                            exit;
                        } else {
                            // Failed login - increment attempts
                            $attempts = $user['login_attempts'] + 1;
                            $lockUntil = null;

                            if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                                $lockUntil = date('Y-m-d H:i:s', time() + LOGIN_LOCKOUT_TIME);
                                $error = 'Too many failed attempts. Account locked for ' . (LOGIN_LOCKOUT_TIME / 60) . ' minutes.';
                            } else {
                                $remaining = MAX_LOGIN_ATTEMPTS - $attempts;
                                $error = "Invalid credentials. {$remaining} attempts remaining.";
                            }

                            $stmt = $db->prepare("UPDATE admin_users SET login_attempts = ?, locked_until = ? WHERE id = ?");
                            $stmt->execute([$attempts, $lockUntil, $user['id']]);
                        }
                    }
                } else {
                    $error = 'Invalid credentials.';
                }
            } catch (PDOException $e) {
                error_log('Login error: ' . $e->getMessage());
                $error = 'A system error occurred. Please try again later.';
            }
        }
    }
}

$timeout = isset($_GET['timeout']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Law &amp; Reason Admin</title>
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
        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-brand {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-brand h1 {
            font-size: 1.5rem;
            color: #111827;
        }
        .login-brand h1 span { color: #2563eb; }
        .login-brand p {
            color: #6b7280;
            font-size: 0.85rem;
            margin-top: 4px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .form-input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }
        .form-input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px #dbeafe;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-login:hover { background: #1d4ed8; }
        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
        }
        .info-message {
            background: #fef3c7;
            color: #d97706;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            border: 1px solid #fde68a;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-brand">
            <h1><span>Law</span> &amp; Reason</h1>
            <p>Admin Panel</p>
        </div>

        <?php if ($timeout): ?>
            <div class="info-message">Your session has expired. Please log in again.</div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <?= csrfField() ?>
            
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-input" 
                       value="<?= htmlspecialchars($username) ?>" required autocomplete="username">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" 
                       required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-login">Sign In</button>
        </form>
    </div>
</body>
</html>
