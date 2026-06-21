<?php
/**
 * Law & Reason - Site Settings
 * Edit site_settings table entries
 */

$pageTitle = 'Settings';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        setFlash('error', 'Invalid request. Please try again.');
    } else {
        $settings = [
            'notification_email' => trim($_POST['notification_email'] ?? ''),
            'social_instagram' => trim($_POST['social_instagram'] ?? ''),
            'social_linkedin' => trim($_POST['social_linkedin'] ?? ''),
            'social_youtube' => trim($_POST['social_youtube'] ?? ''),
            'disclaimer_en' => trim($_POST['disclaimer_en'] ?? ''),
            'disclaimer_hi' => trim($_POST['disclaimer_hi'] ?? ''),
            'site_tagline_en' => trim($_POST['site_tagline_en'] ?? ''),
            'site_tagline_hi' => trim($_POST['site_tagline_hi'] ?? ''),
        ];

        try {
            $stmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            foreach ($settings as $key => $value) {
                $stmt->execute([$key, $value]);
            }
            setFlash('success', 'Settings saved successfully.');
            header('Location: /admin/settings.php');
            exit;
        } catch (PDOException $e) {
            error_log('Settings save error: ' . $e->getMessage());
            setFlash('error', 'Failed to save settings. Please try again.');
        }
    }
}

// Load current settings
$settingsStmt = $db->query("SELECT setting_key, setting_value FROM site_settings");
$currentSettings = $settingsStmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="page-header">
    <h1>Site Settings</h1>
</div>

<form method="POST">
    <?= csrfField() ?>

    <!-- General -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">General</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Site Tagline <span class="lang-label lang-en">EN</span></label>
                <input type="text" name="site_tagline_en" class="form-input" value="<?= htmlspecialchars($currentSettings['site_tagline_en'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Site Tagline <span class="lang-label lang-hi">HI</span></label>
                <input type="text" name="site_tagline_hi" class="form-input" value="<?= htmlspecialchars($currentSettings['site_tagline_hi'] ?? '') ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Notification Email</label>
            <input type="email" name="notification_email" class="form-input" value="<?= htmlspecialchars($currentSettings['notification_email'] ?? '') ?>" placeholder="Email for receiving contact form notifications">
        </div>
    </div>

    <!-- Social Links -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Social Media Links</h3>
        <div class="form-group">
            <label class="form-label">Instagram URL</label>
            <input type="url" name="social_instagram" class="form-input" value="<?= htmlspecialchars($currentSettings['social_instagram'] ?? '') ?>" placeholder="https://instagram.com/...">
        </div>
        <div class="form-group">
            <label class="form-label">LinkedIn URL</label>
            <input type="url" name="social_linkedin" class="form-input" value="<?= htmlspecialchars($currentSettings['social_linkedin'] ?? '') ?>" placeholder="https://linkedin.com/in/...">
        </div>
        <div class="form-group">
            <label class="form-label">YouTube URL</label>
            <input type="url" name="social_youtube" class="form-input" value="<?= htmlspecialchars($currentSettings['social_youtube'] ?? '') ?>" placeholder="https://youtube.com/...">
        </div>
    </div>

    <!-- Disclaimer -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Disclaimer Text</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Disclaimer <span class="lang-label lang-en">EN</span></label>
                <textarea name="disclaimer_en" class="form-textarea" rows="4"><?= htmlspecialchars($currentSettings['disclaimer_en'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Disclaimer <span class="lang-label lang-hi">HI</span></label>
                <textarea name="disclaimer_hi" class="form-textarea" rows="4"><?= htmlspecialchars($currentSettings['disclaimer_hi'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Save -->
    <div class="card">
        <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 1rem;">Save Settings</button>
    </div>
</form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
