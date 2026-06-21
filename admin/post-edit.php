<?php
/**
 * Law & Reason - Create/Edit Blog Post
 * Full bilingual editor with image upload and SEO fields
 */

$pageTitle = 'New Post';
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../includes/image-handler.php';

$db = getDB();
$errors = [];
$post = [
    'id' => '',
    'title_en' => '',
    'title_hi' => '',
    'excerpt_en' => '',
    'excerpt_hi' => '',
    'content_en' => '',
    'content_hi' => '',
    'featured_image' => '',
    'featured_image_thumb' => '',
    'featured_image_alt' => '',
    'category' => 'general',
    'tags' => '',
    'meta_title_en' => '',
    'meta_title_hi' => '',
    'meta_description_en' => '',
    'meta_description_hi' => '',
    'is_published' => 0,
    'published_at' => null,
];

// Load existing post if editing
$isEdit = false;
if (isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $existing = $stmt->fetch();
    if ($existing) {
        $post = $existing;
        $isEdit = true;
        $pageTitle = 'Edit Post';
    }
}

// Fetch categories for dropdown
$categories = $db->query("SELECT slug, name_en FROM categories ORDER BY sort_order")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Collect form data
        $post['title_en'] = trim($_POST['title_en'] ?? '');
        $post['title_hi'] = trim($_POST['title_hi'] ?? '');
        $post['excerpt_en'] = trim($_POST['excerpt_en'] ?? '');
        $post['excerpt_hi'] = trim($_POST['excerpt_hi'] ?? '');
        $post['content_en'] = $_POST['content_en'] ?? '';
        $post['content_hi'] = $_POST['content_hi'] ?? '';
        $post['featured_image_alt'] = trim($_POST['featured_image_alt'] ?? '');
        $post['category'] = trim($_POST['category'] ?? 'general');
        $post['tags'] = trim($_POST['tags'] ?? '');
        $post['meta_title_en'] = trim($_POST['meta_title_en'] ?? '');
        $post['meta_title_hi'] = trim($_POST['meta_title_hi'] ?? '');
        $post['meta_description_en'] = trim($_POST['meta_description_en'] ?? '');
        $post['meta_description_hi'] = trim($_POST['meta_description_hi'] ?? '');
        $post['is_published'] = isset($_POST['is_published']) ? 1 : 0;

        // Validation
        if (empty($post['title_en'])) {
            $errors[] = 'English title is required.';
        }
        if (empty($post['content_en'])) {
            $errors[] = 'English content is required.';
        }

        // Handle image upload
        if (!empty($_FILES['featured_image']['name']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $imageResult = ImageHandler::processUpload($_FILES['featured_image'], 'blog');
            if ($imageResult) {
                // Delete old image if replacing
                if ($isEdit && $post['featured_image']) {
                    ImageHandler::deleteImage($post['featured_image']);
                }
                $post['featured_image'] = $imageResult['original'];
                $post['featured_image_thumb'] = $imageResult['thumb'];
            } else {
                $errors[] = 'Image upload failed. Please check file size and format (JPEG, PNG, WebP, GIF; max 5MB).';
            }
        }

        if (empty($errors)) {
            try {
                if ($isEdit) {
                    // Update existing post
                    $slug = $post['slug']; // Keep existing slug
                    
                    // Set published_at if publishing for first time
                    $publishedAt = $post['published_at'];
                    if ($post['is_published'] && empty($publishedAt)) {
                        $publishedAt = date('Y-m-d H:i:s');
                    }

                    $stmt = $db->prepare("UPDATE posts SET 
                        title_en = ?, title_hi = ?, excerpt_en = ?, excerpt_hi = ?,
                        content_en = ?, content_hi = ?, featured_image = ?, featured_image_thumb = ?,
                        featured_image_alt = ?, category = ?, tags = ?,
                        meta_title_en = ?, meta_title_hi = ?, meta_description_en = ?, meta_description_hi = ?,
                        is_published = ?, published_at = ?
                        WHERE id = ?");
                    $stmt->execute([
                        $post['title_en'], $post['title_hi'], $post['excerpt_en'], $post['excerpt_hi'],
                        $post['content_en'], $post['content_hi'], $post['featured_image'], $post['featured_image_thumb'],
                        $post['featured_image_alt'], $post['category'], $post['tags'],
                        $post['meta_title_en'], $post['meta_title_hi'], $post['meta_description_en'], $post['meta_description_hi'],
                        $post['is_published'], $publishedAt,
                        $post['id']
                    ]);
                    setFlash('success', 'Post updated successfully.');
                    header('Location: /admin/posts.php');
                    exit;
                } else {
                    // Create new post
                    $slug = generateSlug($post['title_en']);
                    
                    // Ensure unique slug
                    $checkSlug = $db->prepare("SELECT COUNT(*) FROM posts WHERE slug = ?");
                    $checkSlug->execute([$slug]);
                    if ($checkSlug->fetchColumn() > 0) {
                        $slug .= '-' . time();
                    }

                    $publishedAt = $post['is_published'] ? date('Y-m-d H:i:s') : null;

                    $stmt = $db->prepare("INSERT INTO posts 
                        (slug, title_en, title_hi, excerpt_en, excerpt_hi, content_en, content_hi,
                         featured_image, featured_image_thumb, featured_image_alt, author_id, category, tags,
                         meta_title_en, meta_title_hi, meta_description_en, meta_description_hi,
                         is_published, published_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $slug, $post['title_en'], $post['title_hi'], $post['excerpt_en'], $post['excerpt_hi'],
                        $post['content_en'], $post['content_hi'],
                        $post['featured_image'], $post['featured_image_thumb'], $post['featured_image_alt'],
                        $_SESSION['admin_id'], $post['category'], $post['tags'],
                        $post['meta_title_en'], $post['meta_title_hi'], $post['meta_description_en'], $post['meta_description_hi'],
                        $post['is_published'], $publishedAt
                    ]);
                    setFlash('success', 'Post created successfully.');
                    header('Location: /admin/posts.php');
                    exit;
                }
            } catch (PDOException $e) {
                error_log('Post save error: ' . $e->getMessage());
                $errors[] = 'Failed to save post. Please try again.';
            }
        }
    }
}
?>

<div class="page-header">
    <h1><?= $isEdit ? 'Edit Post' : 'New Post' ?></h1>
    <a href="/admin/posts.php" class="btn btn-outline">Back to Posts</a>
</div>

<?php if (!empty($errors)): ?>
<div class="flash-message flash-error">
    <ul style="margin:0; padding-left: 16px;">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <?= csrfField() ?>

    <!-- Title -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Title</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Title <span class="lang-label lang-en">EN</span></label>
                <input type="text" name="title_en" class="form-input" value="<?= htmlspecialchars($post['title_en']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Title <span class="lang-label lang-hi">HI</span></label>
                <input type="text" name="title_hi" class="form-input" value="<?= htmlspecialchars($post['title_hi']) ?>">
            </div>
        </div>
    </div>

    <!-- Excerpt -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Excerpt / Summary</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Excerpt <span class="lang-label lang-en">EN</span></label>
                <textarea name="excerpt_en" class="form-textarea" rows="3"><?= htmlspecialchars($post['excerpt_en']) ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Excerpt <span class="lang-label lang-hi">HI</span></label>
                <textarea name="excerpt_hi" class="form-textarea" rows="3"><?= htmlspecialchars($post['excerpt_hi']) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Content</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Content <span class="lang-label lang-en">EN</span></label>
                <div class="editor-toolbar" data-target="content_en">
                    <button type="button" onclick="execCmd('bold')" title="Bold"><b>B</b></button>
                    <button type="button" onclick="execCmd('italic')" title="Italic"><i>I</i></button>
                    <button type="button" onclick="execCmd('insertUnorderedList')" title="List">&#8226;</button>
                    <button type="button" onclick="execCmd('insertOrderedList')" title="Numbered List">1.</button>
                    <button type="button" onclick="execHeading()" title="Heading">H</button>
                    <button type="button" onclick="execLink()" title="Link">&#128279;</button>
                </div>
                <div class="rich-editor" contenteditable="true" id="editor_content_en"><?= $post['content_en'] ?></div>
                <input type="hidden" name="content_en" id="content_en" value="<?= htmlspecialchars($post['content_en']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Content <span class="lang-label lang-hi">HI</span></label>
                <div class="editor-toolbar" data-target="content_hi">
                    <button type="button" onclick="execCmd('bold')" title="Bold"><b>B</b></button>
                    <button type="button" onclick="execCmd('italic')" title="Italic"><i>I</i></button>
                    <button type="button" onclick="execCmd('insertUnorderedList')" title="List">&#8226;</button>
                    <button type="button" onclick="execCmd('insertOrderedList')" title="Numbered List">1.</button>
                    <button type="button" onclick="execHeading()" title="Heading">H</button>
                    <button type="button" onclick="execLink()" title="Link">&#128279;</button>
                </div>
                <div class="rich-editor" contenteditable="true" id="editor_content_hi"><?= $post['content_hi'] ?></div>
                <input type="hidden" name="content_hi" id="content_hi" value="<?= htmlspecialchars($post['content_hi']) ?>">
            </div>
        </div>
    </div>

    <!-- Featured Image -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Featured Image</h3>
        <div class="form-group">
            <label class="form-label">Upload Image (JPEG, PNG, WebP, GIF - max 5MB)</label>
            <input type="file" name="featured_image" class="form-input" accept="image/*" data-preview="imagePreview">
            <?php if ($post['featured_image']): ?>
                <img src="/public/<?= htmlspecialchars($post['featured_image_thumb'] ?: $post['featured_image']) ?>" 
                     class="image-preview" id="imagePreview" alt="Current image">
            <?php else: ?>
                <img src="" class="image-preview" id="imagePreview" style="display:none;" alt="Preview">
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label class="form-label">Image Alt Text</label>
            <input type="text" name="featured_image_alt" class="form-input" value="<?= htmlspecialchars($post['featured_image_alt']) ?>" placeholder="Describe the image for accessibility">
        </div>
    </div>

    <!-- Category & Tags -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Category &amp; Tags</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Category</label>
                <select name="category" class="form-select">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= $post['category'] === $cat['slug'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name_en']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Tags (comma-separated)</label>
                <input type="text" name="tags" class="form-input" value="<?= htmlspecialchars($post['tags']) ?>" placeholder="e.g. property, tenant, rent">
            </div>
        </div>
    </div>

    <!-- SEO Fields -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">SEO Settings</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Meta Title <span class="lang-label lang-en">EN</span></label>
                <input type="text" name="meta_title_en" class="form-input" value="<?= htmlspecialchars($post['meta_title_en']) ?>" maxlength="160" placeholder="Leave blank to use post title">
            </div>
            <div class="form-group">
                <label class="form-label">Meta Title <span class="lang-label lang-hi">HI</span></label>
                <input type="text" name="meta_title_hi" class="form-input" value="<?= htmlspecialchars($post['meta_title_hi']) ?>" maxlength="160">
            </div>
        </div>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Meta Description <span class="lang-label lang-en">EN</span></label>
                <textarea name="meta_description_en" class="form-textarea" rows="2" maxlength="320" placeholder="Leave blank to use excerpt"><?= htmlspecialchars($post['meta_description_en']) ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Meta Description <span class="lang-label lang-hi">HI</span></label>
                <textarea name="meta_description_hi" class="form-textarea" rows="2" maxlength="320"><?= htmlspecialchars($post['meta_description_hi']) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Publish -->
    <div class="card">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <label class="toggle-label">
                <input type="checkbox" name="is_published" value="1" class="toggle-input" <?= $post['is_published'] ? 'checked' : '' ?>>
                <span class="toggle-switch"></span>
                <span style="font-weight: 600;">Publish</span>
            </label>
            <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 1rem;">
                <?= $isEdit ? 'Update Post' : 'Save Post' ?>
            </button>
        </div>
    </div>
</form>

<style>
.editor-toolbar {
    display: flex;
    gap: 4px;
    padding: 8px;
    background: var(--gray-100);
    border: 1px solid var(--gray-300);
    border-bottom: none;
    border-radius: 6px 6px 0 0;
}
.editor-toolbar button {
    padding: 6px 10px;
    border: 1px solid var(--gray-300);
    background: #fff;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.85rem;
    line-height: 1;
}
.editor-toolbar button:hover { background: var(--gray-200); }
.rich-editor {
    min-height: 250px;
    padding: 14px;
    border: 1px solid var(--gray-300);
    border-radius: 0 0 6px 6px;
    font-size: 0.95rem;
    line-height: 1.7;
    overflow-y: auto;
    max-height: 500px;
    background: #fff;
}
.rich-editor:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }
.rich-editor h2, .rich-editor h3 { margin: 12px 0 8px; }
.rich-editor ul, .rich-editor ol { padding-left: 20px; }
.rich-editor a { color: var(--primary); }
</style>

<script>
// Rich text editor functions
var activeEditor = null;

document.querySelectorAll('.rich-editor').forEach(function(editor) {
    editor.addEventListener('focus', function() {
        activeEditor = this;
    });
});

function execCmd(command) {
    if (activeEditor) activeEditor.focus();
    document.execCommand(command, false, null);
}

function execHeading() {
    if (activeEditor) activeEditor.focus();
    document.execCommand('formatBlock', false, '<h3>');
}

function execLink() {
    var url = prompt('Enter URL:');
    if (url) {
        if (activeEditor) activeEditor.focus();
        document.execCommand('createLink', false, url);
    }
}

// Sync editor content to hidden fields before form submission
document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('content_en').value = document.getElementById('editor_content_en').innerHTML;
    document.getElementById('content_hi').value = document.getElementById('editor_content_hi').innerHTML;
});
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
