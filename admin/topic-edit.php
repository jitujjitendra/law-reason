<?php
/**
 * Law & Reason - Create/Edit Topic
 * Bilingual fields, icon, content editor, SEO
 */

$pageTitle = 'New Topic';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();
$errors = [];
$topic = [
    'id' => '',
    'slug' => '',
    'title_en' => '',
    'title_hi' => '',
    'description_en' => '',
    'description_hi' => '',
    'content_en' => '',
    'content_hi' => '',
    'icon' => '',
    'meta_title_en' => '',
    'meta_title_hi' => '',
    'meta_description_en' => '',
    'meta_description_hi' => '',
    'sort_order' => 0,
    'is_published' => 1,
];

// Load existing topic if editing
$isEdit = false;
if (isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM topics WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $existing = $stmt->fetch();
    if ($existing) {
        $topic = $existing;
        $isEdit = true;
        $pageTitle = 'Edit Topic';
    }
}

// Icon options
$iconOptions = [
    '' => 'None',
    'icon-home' => 'Home (Property)',
    'icon-users' => 'Users (Family)',
    'icon-cart' => 'Cart (Consumer)',
    'icon-briefcase' => 'Briefcase (Employment)',
    'icon-document' => 'Document (Cheque/Docs)',
    'icon-shield' => 'Shield (Police/Criminal)',
    'icon-person' => 'Person (Senior Citizen)',
    'icon-clipboard' => 'Clipboard (Documentation)',
    'icon-gavel' => 'Gavel (Law)',
    'icon-scale' => 'Scale (Justice)',
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $topic['title_en'] = trim($_POST['title_en'] ?? '');
        $topic['title_hi'] = trim($_POST['title_hi'] ?? '');
        $topic['description_en'] = trim($_POST['description_en'] ?? '');
        $topic['description_hi'] = trim($_POST['description_hi'] ?? '');
        $topic['content_en'] = $_POST['content_en'] ?? '';
        $topic['content_hi'] = $_POST['content_hi'] ?? '';
        $topic['icon'] = trim($_POST['icon'] ?? '');
        $topic['meta_title_en'] = trim($_POST['meta_title_en'] ?? '');
        $topic['meta_title_hi'] = trim($_POST['meta_title_hi'] ?? '');
        $topic['meta_description_en'] = trim($_POST['meta_description_en'] ?? '');
        $topic['meta_description_hi'] = trim($_POST['meta_description_hi'] ?? '');
        $topic['sort_order'] = (int)($_POST['sort_order'] ?? 0);
        $topic['is_published'] = isset($_POST['is_published']) ? 1 : 0;

        if (empty($topic['title_en'])) {
            $errors[] = 'English title is required.';
        }

        if (empty($errors)) {
            try {
                if ($isEdit) {
                    $stmt = $db->prepare("UPDATE topics SET 
                        title_en = ?, title_hi = ?, description_en = ?, description_hi = ?,
                        content_en = ?, content_hi = ?, icon = ?,
                        meta_title_en = ?, meta_title_hi = ?, meta_description_en = ?, meta_description_hi = ?,
                        sort_order = ?, is_published = ?
                        WHERE id = ?");
                    $stmt->execute([
                        $topic['title_en'], $topic['title_hi'], $topic['description_en'], $topic['description_hi'],
                        $topic['content_en'], $topic['content_hi'], $topic['icon'],
                        $topic['meta_title_en'], $topic['meta_title_hi'], $topic['meta_description_en'], $topic['meta_description_hi'],
                        $topic['sort_order'], $topic['is_published'],
                        $topic['id']
                    ]);
                    setFlash('success', 'Topic updated successfully.');
                } else {
                    $slug = generateSlug($topic['title_en']);
                    $checkSlug = $db->prepare("SELECT COUNT(*) FROM topics WHERE slug = ?");
                    $checkSlug->execute([$slug]);
                    if ($checkSlug->fetchColumn() > 0) {
                        $slug .= '-' . time();
                    }

                    $stmt = $db->prepare("INSERT INTO topics 
                        (slug, title_en, title_hi, description_en, description_hi, content_en, content_hi, icon,
                         meta_title_en, meta_title_hi, meta_description_en, meta_description_hi, sort_order, is_published)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $slug, $topic['title_en'], $topic['title_hi'], $topic['description_en'], $topic['description_hi'],
                        $topic['content_en'], $topic['content_hi'], $topic['icon'],
                        $topic['meta_title_en'], $topic['meta_title_hi'], $topic['meta_description_en'], $topic['meta_description_hi'],
                        $topic['sort_order'], $topic['is_published']
                    ]);
                    setFlash('success', 'Topic created successfully.');
                }
                header('Location: /admin/topics.php');
                exit;
            } catch (PDOException $e) {
                error_log('Topic save error: ' . $e->getMessage());
                $errors[] = 'Failed to save topic. Please try again.';
            }
        }
    }
}
?>

<div class="page-header">
    <h1><?= $isEdit ? 'Edit Topic' : 'New Topic' ?></h1>
    <a href="/admin/topics.php" class="btn btn-outline">Back to Topics</a>
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

    <!-- Title -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Title</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Title <span class="lang-label lang-en">EN</span></label>
                <input type="text" name="title_en" class="form-input" value="<?= htmlspecialchars($topic['title_en']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Title <span class="lang-label lang-hi">HI</span></label>
                <input type="text" name="title_hi" class="form-input" value="<?= htmlspecialchars($topic['title_hi']) ?>">
            </div>
        </div>
    </div>

    <!-- Description -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Short Description</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Description <span class="lang-label lang-en">EN</span></label>
                <textarea name="description_en" class="form-textarea" rows="3"><?= htmlspecialchars($topic['description_en']) ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Description <span class="lang-label lang-hi">HI</span></label>
                <textarea name="description_hi" class="form-textarea" rows="3"><?= htmlspecialchars($topic['description_hi']) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Detailed Content</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Content <span class="lang-label lang-en">EN</span></label>
                <div class="editor-toolbar" data-target="content_en">
                    <button type="button" onclick="execCmd('bold')" title="Bold"><b>B</b></button>
                    <button type="button" onclick="execCmd('italic')" title="Italic"><i>I</i></button>
                    <button type="button" onclick="execCmd('insertUnorderedList')" title="List">&#8226;</button>
                    <button type="button" onclick="execCmd('insertOrderedList')" title="Numbered">1.</button>
                    <button type="button" onclick="execHeading()" title="Heading">H</button>
                    <button type="button" onclick="execLink()" title="Link">&#128279;</button>
                </div>
                <div class="rich-editor" contenteditable="true" id="editor_content_en"><?= $topic['content_en'] ?></div>
                <input type="hidden" name="content_en" id="content_en" value="<?= htmlspecialchars($topic['content_en']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Content <span class="lang-label lang-hi">HI</span></label>
                <div class="editor-toolbar" data-target="content_hi">
                    <button type="button" onclick="execCmd('bold')" title="Bold"><b>B</b></button>
                    <button type="button" onclick="execCmd('italic')" title="Italic"><i>I</i></button>
                    <button type="button" onclick="execCmd('insertUnorderedList')" title="List">&#8226;</button>
                    <button type="button" onclick="execCmd('insertOrderedList')" title="Numbered">1.</button>
                    <button type="button" onclick="execHeading()" title="Heading">H</button>
                    <button type="button" onclick="execLink()" title="Link">&#128279;</button>
                </div>
                <div class="rich-editor" contenteditable="true" id="editor_content_hi"><?= $topic['content_hi'] ?></div>
                <input type="hidden" name="content_hi" id="content_hi" value="<?= htmlspecialchars($topic['content_hi']) ?>">
            </div>
        </div>
    </div>

    <!-- Icon & Sort Order -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Display Options</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Icon</label>
                <select name="icon" class="form-select">
                    <?php foreach ($iconOptions as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= $topic['icon'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-input" value="<?= (int)$topic['sort_order'] ?>" min="0">
            </div>
        </div>
    </div>

    <!-- SEO -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">SEO Settings</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Meta Title <span class="lang-label lang-en">EN</span></label>
                <input type="text" name="meta_title_en" class="form-input" value="<?= htmlspecialchars($topic['meta_title_en']) ?>" maxlength="160">
            </div>
            <div class="form-group">
                <label class="form-label">Meta Title <span class="lang-label lang-hi">HI</span></label>
                <input type="text" name="meta_title_hi" class="form-input" value="<?= htmlspecialchars($topic['meta_title_hi']) ?>" maxlength="160">
            </div>
        </div>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Meta Description <span class="lang-label lang-en">EN</span></label>
                <textarea name="meta_description_en" class="form-textarea" rows="2" maxlength="320"><?= htmlspecialchars($topic['meta_description_en']) ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Meta Description <span class="lang-label lang-hi">HI</span></label>
                <textarea name="meta_description_hi" class="form-textarea" rows="2" maxlength="320"><?= htmlspecialchars($topic['meta_description_hi']) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Publish -->
    <div class="card">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <label class="toggle-label">
                <input type="checkbox" name="is_published" value="1" class="toggle-input" <?= $topic['is_published'] ? 'checked' : '' ?>>
                <span class="toggle-switch"></span>
                <span style="font-weight: 600;">Published</span>
            </label>
            <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 1rem;">
                <?= $isEdit ? 'Update Topic' : 'Save Topic' ?>
            </button>
        </div>
    </div>
</form>

<style>
.editor-toolbar {
    display: flex; gap: 4px; padding: 8px; background: var(--gray-100);
    border: 1px solid var(--gray-300); border-bottom: none; border-radius: 6px 6px 0 0;
}
.editor-toolbar button {
    padding: 6px 10px; border: 1px solid var(--gray-300); background: #fff;
    border-radius: 4px; cursor: pointer; font-size: 0.85rem; line-height: 1;
}
.editor-toolbar button:hover { background: var(--gray-200); }
.rich-editor {
    min-height: 200px; padding: 14px; border: 1px solid var(--gray-300);
    border-radius: 0 0 6px 6px; font-size: 0.95rem; line-height: 1.7;
    overflow-y: auto; max-height: 400px; background: #fff;
}
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
