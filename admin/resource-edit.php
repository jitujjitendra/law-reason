<?php
/**
 * Law & Reason - Create/Edit Resource
 * Bilingual fields, type, topic, content
 */

$pageTitle = 'New Resource';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();
$errors = [];
$resource = [
    'id' => '',
    'slug' => '',
    'title_en' => '',
    'title_hi' => '',
    'description_en' => '',
    'description_hi' => '',
    'content_en' => '',
    'content_hi' => '',
    'file_path' => '',
    'topic_id' => '',
    'resource_type' => 'checklist',
    'sort_order' => 0,
    'is_published' => 1,
];

$isEdit = false;
if (isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM resources WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $existing = $stmt->fetch();
    if ($existing) {
        $resource = $existing;
        $isEdit = true;
        $pageTitle = 'Edit Resource';
    }
}

$topics = $db->query("SELECT id, title_en FROM topics ORDER BY sort_order")->fetchAll();
$resourceTypes = ['checklist' => 'Checklist', 'organiser' => 'Organiser', 'tracker' => 'Tracker', 'guide' => 'Guide'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $resource['title_en'] = trim($_POST['title_en'] ?? '');
        $resource['title_hi'] = trim($_POST['title_hi'] ?? '');
        $resource['description_en'] = trim($_POST['description_en'] ?? '');
        $resource['description_hi'] = trim($_POST['description_hi'] ?? '');
        $resource['content_en'] = $_POST['content_en'] ?? '';
        $resource['content_hi'] = $_POST['content_hi'] ?? '';
        $resource['topic_id'] = !empty($_POST['topic_id']) ? (int)$_POST['topic_id'] : null;
        $resource['resource_type'] = $_POST['resource_type'] ?? 'checklist';
        $resource['sort_order'] = (int)($_POST['sort_order'] ?? 0);
        $resource['is_published'] = isset($_POST['is_published']) ? 1 : 0;

        if (empty($resource['title_en'])) $errors[] = 'English title is required.';

        if (empty($errors)) {
            try {
                if ($isEdit) {
                    $stmt = $db->prepare("UPDATE resources SET 
                        title_en = ?, title_hi = ?, description_en = ?, description_hi = ?,
                        content_en = ?, content_hi = ?, topic_id = ?, resource_type = ?,
                        sort_order = ?, is_published = ?
                        WHERE id = ?");
                    $stmt->execute([
                        $resource['title_en'], $resource['title_hi'],
                        $resource['description_en'], $resource['description_hi'],
                        $resource['content_en'], $resource['content_hi'],
                        $resource['topic_id'], $resource['resource_type'],
                        $resource['sort_order'], $resource['is_published'],
                        $resource['id']
                    ]);
                    setFlash('success', 'Resource updated successfully.');
                } else {
                    $slug = generateSlug($resource['title_en']);
                    $checkSlug = $db->prepare("SELECT COUNT(*) FROM resources WHERE slug = ?");
                    $checkSlug->execute([$slug]);
                    if ($checkSlug->fetchColumn() > 0) $slug .= '-' . time();

                    $stmt = $db->prepare("INSERT INTO resources 
                        (slug, title_en, title_hi, description_en, description_hi, content_en, content_hi,
                         topic_id, resource_type, sort_order, is_published)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $slug, $resource['title_en'], $resource['title_hi'],
                        $resource['description_en'], $resource['description_hi'],
                        $resource['content_en'], $resource['content_hi'],
                        $resource['topic_id'], $resource['resource_type'],
                        $resource['sort_order'], $resource['is_published']
                    ]);
                    setFlash('success', 'Resource created successfully.');
                }
                header('Location: /admin/resources.php');
                exit;
            } catch (PDOException $e) {
                error_log('Resource save error: ' . $e->getMessage());
                $errors[] = 'Failed to save resource.';
            }
        }
    }
}
?>

<div class="page-header">
    <h1><?= $isEdit ? 'Edit Resource' : 'New Resource' ?></h1>
    <a href="/admin/resources.php" class="btn btn-outline">Back to Resources</a>
</div>

<?php if (!empty($errors)): ?>
<div class="flash-message flash-error">
    <ul style="margin:0; padding-left: 16px;"><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST">
    <?= csrfField() ?>

    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Title</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Title <span class="lang-label lang-en">EN</span></label>
                <input type="text" name="title_en" class="form-input" value="<?= htmlspecialchars($resource['title_en']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Title <span class="lang-label lang-hi">HI</span></label>
                <input type="text" name="title_hi" class="form-input" value="<?= htmlspecialchars($resource['title_hi']) ?>">
            </div>
        </div>
    </div>

    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Description</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Description <span class="lang-label lang-en">EN</span></label>
                <textarea name="description_en" class="form-textarea" rows="3"><?= htmlspecialchars($resource['description_en']) ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Description <span class="lang-label lang-hi">HI</span></label>
                <textarea name="description_hi" class="form-textarea" rows="3"><?= htmlspecialchars($resource['description_hi']) ?></textarea>
            </div>
        </div>
    </div>

    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Content</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Content <span class="lang-label lang-en">EN</span></label>
                <div class="editor-toolbar">
                    <button type="button" onclick="execCmd('bold')"><b>B</b></button>
                    <button type="button" onclick="execCmd('italic')"><i>I</i></button>
                    <button type="button" onclick="execCmd('insertUnorderedList')">&#8226;</button>
                    <button type="button" onclick="execCmd('insertOrderedList')">1.</button>
                    <button type="button" onclick="execHeading()">H</button>
                    <button type="button" onclick="execLink()">&#128279;</button>
                </div>
                <div class="rich-editor" contenteditable="true" id="editor_content_en"><?= $resource['content_en'] ?></div>
                <input type="hidden" name="content_en" id="content_en" value="<?= htmlspecialchars($resource['content_en']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Content <span class="lang-label lang-hi">HI</span></label>
                <div class="editor-toolbar">
                    <button type="button" onclick="execCmd('bold')"><b>B</b></button>
                    <button type="button" onclick="execCmd('italic')"><i>I</i></button>
                    <button type="button" onclick="execCmd('insertUnorderedList')">&#8226;</button>
                    <button type="button" onclick="execCmd('insertOrderedList')">1.</button>
                    <button type="button" onclick="execHeading()">H</button>
                    <button type="button" onclick="execLink()">&#128279;</button>
                </div>
                <div class="rich-editor" contenteditable="true" id="editor_content_hi"><?= $resource['content_hi'] ?></div>
                <input type="hidden" name="content_hi" id="content_hi" value="<?= htmlspecialchars($resource['content_hi']) ?>">
            </div>
        </div>
    </div>

    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Options</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Resource Type</label>
                <select name="resource_type" class="form-select">
                    <?php foreach ($resourceTypes as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $resource['resource_type'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Related Topic</label>
                <select name="topic_id" class="form-select">
                    <option value="">None</option>
                    <?php foreach ($topics as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= $resource['topic_id'] == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['title_en']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-input" value="<?= (int)$resource['sort_order'] ?>" min="0" style="max-width: 150px;">
        </div>
    </div>

    <div class="card">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <label class="toggle-label">
                <input type="checkbox" name="is_published" value="1" class="toggle-input" <?= $resource['is_published'] ? 'checked' : '' ?>>
                <span class="toggle-switch"></span>
                <span style="font-weight: 600;">Published</span>
            </label>
            <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 1rem;">
                <?= $isEdit ? 'Update Resource' : 'Save Resource' ?>
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
