<?php
/**
 * Law & Reason - Create/Edit Myth
 * Bilingual myth/reality/detail content, icon, linked topic
 */

$pageTitle = 'New Myth';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();
$errors = [];
$myth = [
    'id' => '',
    'slug' => '',
    'myth_en' => '',
    'myth_hi' => '',
    'reality_en' => '',
    'reality_hi' => '',
    'detail_content_en' => '',
    'detail_content_hi' => '',
    'icon' => '',
    'topic_id' => '',
    'meta_title_en' => '',
    'meta_title_hi' => '',
    'meta_description_en' => '',
    'meta_description_hi' => '',
    'sort_order' => 0,
    'is_published' => 1,
];

$isEdit = false;
if (isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM myths WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $existing = $stmt->fetch();
    if ($existing) {
        $myth = $existing;
        $isEdit = true;
        $pageTitle = 'Edit Myth';
    }
}

// Fetch topics for dropdown
$topics = $db->query("SELECT id, title_en FROM topics ORDER BY sort_order")->fetchAll();

$iconOptions = [
    '' => 'None', 'icon-shield' => 'Shield', 'icon-document' => 'Document',
    'icon-gavel' => 'Gavel', 'icon-scale' => 'Scale', 'icon-home' => 'Home',
    'icon-users' => 'Users', 'icon-briefcase' => 'Briefcase', 'icon-cart' => 'Cart',
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $myth['myth_en'] = trim($_POST['myth_en'] ?? '');
        $myth['myth_hi'] = trim($_POST['myth_hi'] ?? '');
        $myth['reality_en'] = trim($_POST['reality_en'] ?? '');
        $myth['reality_hi'] = trim($_POST['reality_hi'] ?? '');
        $myth['detail_content_en'] = $_POST['detail_content_en'] ?? '';
        $myth['detail_content_hi'] = $_POST['detail_content_hi'] ?? '';
        $myth['icon'] = trim($_POST['icon'] ?? '');
        $myth['topic_id'] = !empty($_POST['topic_id']) ? (int)$_POST['topic_id'] : null;
        $myth['meta_title_en'] = trim($_POST['meta_title_en'] ?? '');
        $myth['meta_title_hi'] = trim($_POST['meta_title_hi'] ?? '');
        $myth['meta_description_en'] = trim($_POST['meta_description_en'] ?? '');
        $myth['meta_description_hi'] = trim($_POST['meta_description_hi'] ?? '');
        $myth['sort_order'] = (int)($_POST['sort_order'] ?? 0);
        $myth['is_published'] = isset($_POST['is_published']) ? 1 : 0;

        if (empty($myth['myth_en'])) $errors[] = 'English myth statement is required.';
        if (empty($myth['reality_en'])) $errors[] = 'English reality statement is required.';

        if (empty($errors)) {
            try {
                if ($isEdit) {
                    $stmt = $db->prepare("UPDATE myths SET 
                        myth_en = ?, myth_hi = ?, reality_en = ?, reality_hi = ?,
                        detail_content_en = ?, detail_content_hi = ?, icon = ?, topic_id = ?,
                        meta_title_en = ?, meta_title_hi = ?, meta_description_en = ?, meta_description_hi = ?,
                        sort_order = ?, is_published = ?
                        WHERE id = ?");
                    $stmt->execute([
                        $myth['myth_en'], $myth['myth_hi'], $myth['reality_en'], $myth['reality_hi'],
                        $myth['detail_content_en'], $myth['detail_content_hi'], $myth['icon'], $myth['topic_id'],
                        $myth['meta_title_en'], $myth['meta_title_hi'], $myth['meta_description_en'], $myth['meta_description_hi'],
                        $myth['sort_order'], $myth['is_published'],
                        $myth['id']
                    ]);
                    setFlash('success', 'Myth updated successfully.');
                } else {
                    $slug = generateSlug($myth['myth_en']);
                    if (strlen($slug) > 150) $slug = substr($slug, 0, 150);
                    $checkSlug = $db->prepare("SELECT COUNT(*) FROM myths WHERE slug = ?");
                    $checkSlug->execute([$slug]);
                    if ($checkSlug->fetchColumn() > 0) $slug .= '-' . time();

                    $stmt = $db->prepare("INSERT INTO myths 
                        (slug, myth_en, myth_hi, reality_en, reality_hi, detail_content_en, detail_content_hi,
                         icon, topic_id, meta_title_en, meta_title_hi, meta_description_en, meta_description_hi,
                         sort_order, is_published)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $slug, $myth['myth_en'], $myth['myth_hi'], $myth['reality_en'], $myth['reality_hi'],
                        $myth['detail_content_en'], $myth['detail_content_hi'],
                        $myth['icon'], $myth['topic_id'],
                        $myth['meta_title_en'], $myth['meta_title_hi'], $myth['meta_description_en'], $myth['meta_description_hi'],
                        $myth['sort_order'], $myth['is_published']
                    ]);
                    setFlash('success', 'Myth created successfully.');
                }
                header('Location: /admin/myths.php');
                exit;
            } catch (PDOException $e) {
                error_log('Myth save error: ' . $e->getMessage());
                $errors[] = 'Failed to save myth. Please try again.';
            }
        }
    }
}
?>

<div class="page-header">
    <h1><?= $isEdit ? 'Edit Myth' : 'New Myth' ?></h1>
    <a href="/admin/myths.php" class="btn btn-outline">Back to Myths</a>
</div>

<?php if (!empty($errors)): ?>
<div class="flash-message flash-error">
    <ul style="margin:0; padding-left: 16px;">
        <?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST">
    <?= csrfField() ?>

    <!-- Myth Statement -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Myth Statement</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Myth <span class="lang-label lang-en">EN</span></label>
                <textarea name="myth_en" class="form-textarea" rows="2" required><?= htmlspecialchars($myth['myth_en']) ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Myth <span class="lang-label lang-hi">HI</span></label>
                <textarea name="myth_hi" class="form-textarea" rows="2"><?= htmlspecialchars($myth['myth_hi']) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Reality -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Reality</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Reality <span class="lang-label lang-en">EN</span></label>
                <textarea name="reality_en" class="form-textarea" rows="3" required><?= htmlspecialchars($myth['reality_en']) ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Reality <span class="lang-label lang-hi">HI</span></label>
                <textarea name="reality_hi" class="form-textarea" rows="3"><?= htmlspecialchars($myth['reality_hi']) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Detail Content -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Detailed Explanation (Optional)</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Detail <span class="lang-label lang-en">EN</span></label>
                <div class="editor-toolbar">
                    <button type="button" onclick="execCmd('bold')"><b>B</b></button>
                    <button type="button" onclick="execCmd('italic')"><i>I</i></button>
                    <button type="button" onclick="execCmd('insertUnorderedList')">&#8226;</button>
                    <button type="button" onclick="execCmd('insertOrderedList')">1.</button>
                    <button type="button" onclick="execHeading()">H</button>
                    <button type="button" onclick="execLink()">&#128279;</button>
                </div>
                <div class="rich-editor" contenteditable="true" id="editor_detail_content_en"><?= $myth['detail_content_en'] ?></div>
                <input type="hidden" name="detail_content_en" id="detail_content_en" value="<?= htmlspecialchars($myth['detail_content_en']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Detail <span class="lang-label lang-hi">HI</span></label>
                <div class="editor-toolbar">
                    <button type="button" onclick="execCmd('bold')"><b>B</b></button>
                    <button type="button" onclick="execCmd('italic')"><i>I</i></button>
                    <button type="button" onclick="execCmd('insertUnorderedList')">&#8226;</button>
                    <button type="button" onclick="execCmd('insertOrderedList')">1.</button>
                    <button type="button" onclick="execHeading()">H</button>
                    <button type="button" onclick="execLink()">&#128279;</button>
                </div>
                <div class="rich-editor" contenteditable="true" id="editor_detail_content_hi"><?= $myth['detail_content_hi'] ?></div>
                <input type="hidden" name="detail_content_hi" id="detail_content_hi" value="<?= htmlspecialchars($myth['detail_content_hi']) ?>">
            </div>
        </div>
    </div>

    <!-- Options -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Options</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Icon</label>
                <select name="icon" class="form-select">
                    <?php foreach ($iconOptions as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= $myth['icon'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Related Topic</label>
                <select name="topic_id" class="form-select">
                    <option value="">None</option>
                    <?php foreach ($topics as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= $myth['topic_id'] == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['title_en']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-input" value="<?= (int)$myth['sort_order'] ?>" min="0" style="max-width: 150px;">
        </div>
    </div>

    <!-- SEO -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">SEO Settings</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Meta Title <span class="lang-label lang-en">EN</span></label>
                <input type="text" name="meta_title_en" class="form-input" value="<?= htmlspecialchars($myth['meta_title_en']) ?>" maxlength="160">
            </div>
            <div class="form-group">
                <label class="form-label">Meta Title <span class="lang-label lang-hi">HI</span></label>
                <input type="text" name="meta_title_hi" class="form-input" value="<?= htmlspecialchars($myth['meta_title_hi']) ?>" maxlength="160">
            </div>
        </div>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Meta Description <span class="lang-label lang-en">EN</span></label>
                <textarea name="meta_description_en" class="form-textarea" rows="2" maxlength="320"><?= htmlspecialchars($myth['meta_description_en']) ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Meta Description <span class="lang-label lang-hi">HI</span></label>
                <textarea name="meta_description_hi" class="form-textarea" rows="2" maxlength="320"><?= htmlspecialchars($myth['meta_description_hi']) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Publish -->
    <div class="card">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <label class="toggle-label">
                <input type="checkbox" name="is_published" value="1" class="toggle-input" <?= $myth['is_published'] ? 'checked' : '' ?>>
                <span class="toggle-switch"></span>
                <span style="font-weight: 600;">Published</span>
            </label>
            <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 1rem;">
                <?= $isEdit ? 'Update Myth' : 'Save Myth' ?>
            </button>
        </div>
    </div>
</form>

<style>
.editor-toolbar { display: flex; gap: 4px; padding: 8px; background: var(--gray-100); border: 1px solid var(--gray-300); border-bottom: none; border-radius: 6px 6px 0 0; }
.editor-toolbar button { padding: 6px 10px; border: 1px solid var(--gray-300); background: #fff; border-radius: 4px; cursor: pointer; font-size: 0.85rem; line-height: 1; }
.editor-toolbar button:hover { background: var(--gray-200); }
.rich-editor { min-height: 150px; padding: 14px; border: 1px solid var(--gray-300); border-radius: 0 0 6px 6px; font-size: 0.95rem; line-height: 1.7; overflow-y: auto; max-height: 400px; background: #fff; }
.rich-editor:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }
</style>

<script>
var activeEditor = null;
document.querySelectorAll('.rich-editor').forEach(function(editor) {
    editor.addEventListener('focus', function() { activeEditor = this; });
});
function execCmd(command) { if (activeEditor) activeEditor.focus(); document.execCommand(command, false, null); }
function execHeading() { if (activeEditor) activeEditor.focus(); document.execCommand('formatBlock', false, '<h3>'); }
function execLink() { var url = prompt('Enter URL:'); if (url) { if (activeEditor) activeEditor.focus(); document.execCommand('createLink', false, url); } }
document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('detail_content_en').value = document.getElementById('editor_detail_content_en').innerHTML;
    document.getElementById('detail_content_hi').value = document.getElementById('editor_detail_content_hi').innerHTML;
});
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
