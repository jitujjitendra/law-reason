<?php
/**
 * Law & Reason - Create/Edit Scenario
 * Bilingual question + content, linked topic, SEO
 */

$pageTitle = 'New Scenario';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();
$errors = [];
$scenario = [
    'id' => '',
    'slug' => '',
    'question_en' => '',
    'question_hi' => '',
    'content_en' => '',
    'content_hi' => '',
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
    $stmt = $db->prepare("SELECT * FROM scenarios WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $existing = $stmt->fetch();
    if ($existing) {
        $scenario = $existing;
        $isEdit = true;
        $pageTitle = 'Edit Scenario';
    }
}

// Fetch topics for dropdown
$topics = $db->query("SELECT id, title_en FROM topics ORDER BY sort_order")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $scenario['question_en'] = trim($_POST['question_en'] ?? '');
        $scenario['question_hi'] = trim($_POST['question_hi'] ?? '');
        $scenario['content_en'] = $_POST['content_en'] ?? '';
        $scenario['content_hi'] = $_POST['content_hi'] ?? '';
        $scenario['topic_id'] = !empty($_POST['topic_id']) ? (int)$_POST['topic_id'] : null;
        $scenario['meta_title_en'] = trim($_POST['meta_title_en'] ?? '');
        $scenario['meta_title_hi'] = trim($_POST['meta_title_hi'] ?? '');
        $scenario['meta_description_en'] = trim($_POST['meta_description_en'] ?? '');
        $scenario['meta_description_hi'] = trim($_POST['meta_description_hi'] ?? '');
        $scenario['sort_order'] = (int)($_POST['sort_order'] ?? 0);
        $scenario['is_published'] = isset($_POST['is_published']) ? 1 : 0;

        if (empty($scenario['question_en'])) {
            $errors[] = 'English question is required.';
        }

        if (empty($errors)) {
            try {
                if ($isEdit) {
                    $stmt = $db->prepare("UPDATE scenarios SET 
                        question_en = ?, question_hi = ?, content_en = ?, content_hi = ?,
                        topic_id = ?, meta_title_en = ?, meta_title_hi = ?,
                        meta_description_en = ?, meta_description_hi = ?,
                        sort_order = ?, is_published = ?
                        WHERE id = ?");
                    $stmt->execute([
                        $scenario['question_en'], $scenario['question_hi'],
                        $scenario['content_en'], $scenario['content_hi'],
                        $scenario['topic_id'], $scenario['meta_title_en'], $scenario['meta_title_hi'],
                        $scenario['meta_description_en'], $scenario['meta_description_hi'],
                        $scenario['sort_order'], $scenario['is_published'],
                        $scenario['id']
                    ]);
                    setFlash('success', 'Scenario updated successfully.');
                } else {
                    $slug = generateSlug($scenario['question_en']);
                    if (strlen($slug) > 150) $slug = substr($slug, 0, 150);
                    $checkSlug = $db->prepare("SELECT COUNT(*) FROM scenarios WHERE slug = ?");
                    $checkSlug->execute([$slug]);
                    if ($checkSlug->fetchColumn() > 0) {
                        $slug .= '-' . time();
                    }

                    $stmt = $db->prepare("INSERT INTO scenarios 
                        (slug, question_en, question_hi, content_en, content_hi, topic_id,
                         meta_title_en, meta_title_hi, meta_description_en, meta_description_hi,
                         sort_order, is_published)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $slug, $scenario['question_en'], $scenario['question_hi'],
                        $scenario['content_en'], $scenario['content_hi'], $scenario['topic_id'],
                        $scenario['meta_title_en'], $scenario['meta_title_hi'],
                        $scenario['meta_description_en'], $scenario['meta_description_hi'],
                        $scenario['sort_order'], $scenario['is_published']
                    ]);
                    setFlash('success', 'Scenario created successfully.');
                }
                header('Location: /admin/scenarios.php');
                exit;
            } catch (PDOException $e) {
                error_log('Scenario save error: ' . $e->getMessage());
                $errors[] = 'Failed to save scenario. Please try again.';
            }
        }
    }
}
?>

<div class="page-header">
    <h1><?= $isEdit ? 'Edit Scenario' : 'New Scenario' ?></h1>
    <a href="/admin/scenarios.php" class="btn btn-outline">Back to Scenarios</a>
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

    <!-- Question -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Question</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Question <span class="lang-label lang-en">EN</span></label>
                <input type="text" name="question_en" class="form-input" value="<?= htmlspecialchars($scenario['question_en']) ?>" required placeholder="What should I do if...">
            </div>
            <div class="form-group">
                <label class="form-label">Question <span class="lang-label lang-hi">HI</span></label>
                <input type="text" name="question_hi" class="form-input" value="<?= htmlspecialchars($scenario['question_hi']) ?>">
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Answer / Content</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Content <span class="lang-label lang-en">EN</span></label>
                <div class="editor-toolbar">
                    <button type="button" onclick="execCmd('bold')" title="Bold"><b>B</b></button>
                    <button type="button" onclick="execCmd('italic')" title="Italic"><i>I</i></button>
                    <button type="button" onclick="execCmd('insertUnorderedList')" title="List">&#8226;</button>
                    <button type="button" onclick="execCmd('insertOrderedList')" title="Numbered">1.</button>
                    <button type="button" onclick="execHeading()" title="Heading">H</button>
                    <button type="button" onclick="execLink()" title="Link">&#128279;</button>
                </div>
                <div class="rich-editor" contenteditable="true" id="editor_content_en"><?= $scenario['content_en'] ?></div>
                <input type="hidden" name="content_en" id="content_en" value="<?= htmlspecialchars($scenario['content_en']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Content <span class="lang-label lang-hi">HI</span></label>
                <div class="editor-toolbar">
                    <button type="button" onclick="execCmd('bold')" title="Bold"><b>B</b></button>
                    <button type="button" onclick="execCmd('italic')" title="Italic"><i>I</i></button>
                    <button type="button" onclick="execCmd('insertUnorderedList')" title="List">&#8226;</button>
                    <button type="button" onclick="execCmd('insertOrderedList')" title="Numbered">1.</button>
                    <button type="button" onclick="execHeading()" title="Heading">H</button>
                    <button type="button" onclick="execLink()" title="Link">&#128279;</button>
                </div>
                <div class="rich-editor" contenteditable="true" id="editor_content_hi"><?= $scenario['content_hi'] ?></div>
                <input type="hidden" name="content_hi" id="content_hi" value="<?= htmlspecialchars($scenario['content_hi']) ?>">
            </div>
        </div>
    </div>

    <!-- Topic & Sort -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Options</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Related Topic</label>
                <select name="topic_id" class="form-select">
                    <option value="">None</option>
                    <?php foreach ($topics as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= $scenario['topic_id'] == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['title_en']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-input" value="<?= (int)$scenario['sort_order'] ?>" min="0">
            </div>
        </div>
    </div>

    <!-- SEO -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">SEO Settings</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Meta Title <span class="lang-label lang-en">EN</span></label>
                <input type="text" name="meta_title_en" class="form-input" value="<?= htmlspecialchars($scenario['meta_title_en']) ?>" maxlength="160">
            </div>
            <div class="form-group">
                <label class="form-label">Meta Title <span class="lang-label lang-hi">HI</span></label>
                <input type="text" name="meta_title_hi" class="form-input" value="<?= htmlspecialchars($scenario['meta_title_hi']) ?>" maxlength="160">
            </div>
        </div>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Meta Description <span class="lang-label lang-en">EN</span></label>
                <textarea name="meta_description_en" class="form-textarea" rows="2" maxlength="320"><?= htmlspecialchars($scenario['meta_description_en']) ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Meta Description <span class="lang-label lang-hi">HI</span></label>
                <textarea name="meta_description_hi" class="form-textarea" rows="2" maxlength="320"><?= htmlspecialchars($scenario['meta_description_hi']) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Publish -->
    <div class="card">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <label class="toggle-label">
                <input type="checkbox" name="is_published" value="1" class="toggle-input" <?= $scenario['is_published'] ? 'checked' : '' ?>>
                <span class="toggle-switch"></span>
                <span style="font-weight: 600;">Published</span>
            </label>
            <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 1rem;">
                <?= $isEdit ? 'Update Scenario' : 'Save Scenario' ?>
            </button>
        </div>
    </div>
</form>

<style>
.editor-toolbar { display: flex; gap: 4px; padding: 8px; background: var(--gray-100); border: 1px solid var(--gray-300); border-bottom: none; border-radius: 6px 6px 0 0; }
.editor-toolbar button { padding: 6px 10px; border: 1px solid var(--gray-300); background: #fff; border-radius: 4px; cursor: pointer; font-size: 0.85rem; line-height: 1; }
.editor-toolbar button:hover { background: var(--gray-200); }
.rich-editor { min-height: 200px; padding: 14px; border: 1px solid var(--gray-300); border-radius: 0 0 6px 6px; font-size: 0.95rem; line-height: 1.7; overflow-y: auto; max-height: 400px; background: #fff; }
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
    document.getElementById('content_en').value = document.getElementById('editor_content_en').innerHTML;
    document.getElementById('content_hi').value = document.getElementById('editor_content_hi').innerHTML;
});
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
