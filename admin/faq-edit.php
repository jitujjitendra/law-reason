<?php
/**
 * Law & Reason - Create/Edit FAQ
 * Bilingual question + answer, linked topic or post
 */

$pageTitle = 'New FAQ';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();
$errors = [];
$faq = [
    'id' => '',
    'question_en' => '',
    'question_hi' => '',
    'answer_en' => '',
    'answer_hi' => '',
    'topic_id' => '',
    'post_id' => '',
    'sort_order' => 0,
    'is_published' => 1,
];

$isEdit = false;
if (isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $existing = $stmt->fetch();
    if ($existing) {
        $faq = $existing;
        $isEdit = true;
        $pageTitle = 'Edit FAQ';
    }
}

$topics = $db->query("SELECT id, title_en FROM topics ORDER BY sort_order")->fetchAll();
$posts = $db->query("SELECT id, title_en FROM posts ORDER BY created_at DESC LIMIT 50")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $faq['question_en'] = trim($_POST['question_en'] ?? '');
        $faq['question_hi'] = trim($_POST['question_hi'] ?? '');
        $faq['answer_en'] = trim($_POST['answer_en'] ?? '');
        $faq['answer_hi'] = trim($_POST['answer_hi'] ?? '');
        $faq['topic_id'] = !empty($_POST['topic_id']) ? (int)$_POST['topic_id'] : null;
        $faq['post_id'] = !empty($_POST['post_id']) ? (int)$_POST['post_id'] : null;
        $faq['sort_order'] = (int)($_POST['sort_order'] ?? 0);
        $faq['is_published'] = isset($_POST['is_published']) ? 1 : 0;

        if (empty($faq['question_en'])) $errors[] = 'English question is required.';
        if (empty($faq['answer_en'])) $errors[] = 'English answer is required.';

        if (empty($errors)) {
            try {
                if ($isEdit) {
                    $stmt = $db->prepare("UPDATE faqs SET 
                        question_en = ?, question_hi = ?, answer_en = ?, answer_hi = ?,
                        topic_id = ?, post_id = ?, sort_order = ?, is_published = ?
                        WHERE id = ?");
                    $stmt->execute([
                        $faq['question_en'], $faq['question_hi'],
                        $faq['answer_en'], $faq['answer_hi'],
                        $faq['topic_id'], $faq['post_id'],
                        $faq['sort_order'], $faq['is_published'],
                        $faq['id']
                    ]);
                    setFlash('success', 'FAQ updated successfully.');
                } else {
                    $stmt = $db->prepare("INSERT INTO faqs 
                        (question_en, question_hi, answer_en, answer_hi, topic_id, post_id, sort_order, is_published)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $faq['question_en'], $faq['question_hi'],
                        $faq['answer_en'], $faq['answer_hi'],
                        $faq['topic_id'], $faq['post_id'],
                        $faq['sort_order'], $faq['is_published']
                    ]);
                    setFlash('success', 'FAQ created successfully.');
                }
                header('Location: /admin/faqs.php');
                exit;
            } catch (PDOException $e) {
                error_log('FAQ save error: ' . $e->getMessage());
                $errors[] = 'Failed to save FAQ.';
            }
        }
    }
}
?>

<div class="page-header">
    <h1><?= $isEdit ? 'Edit FAQ' : 'New FAQ' ?></h1>
    <a href="/admin/faqs.php" class="btn btn-outline">Back to FAQs</a>
</div>

<?php if (!empty($errors)): ?>
<div class="flash-message flash-error">
    <ul style="margin:0; padding-left: 16px;"><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST">
    <?= csrfField() ?>

    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Question</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Question <span class="lang-label lang-en">EN</span></label>
                <input type="text" name="question_en" class="form-input" value="<?= htmlspecialchars($faq['question_en']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Question <span class="lang-label lang-hi">HI</span></label>
                <input type="text" name="question_hi" class="form-input" value="<?= htmlspecialchars($faq['question_hi']) ?>">
            </div>
        </div>
    </div>

    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Answer</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Answer <span class="lang-label lang-en">EN</span></label>
                <textarea name="answer_en" class="form-textarea" rows="5" required><?= htmlspecialchars($faq['answer_en']) ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Answer <span class="lang-label lang-hi">HI</span></label>
                <textarea name="answer_hi" class="form-textarea" rows="5"><?= htmlspecialchars($faq['answer_hi']) ?></textarea>
            </div>
        </div>
    </div>

    <div class="card">
        <h3 class="card-title" style="margin-bottom: 16px;">Options</h3>
        <div class="bilingual-row">
            <div class="form-group">
                <label class="form-label">Link to Topic</label>
                <select name="topic_id" class="form-select">
                    <option value="">None</option>
                    <?php foreach ($topics as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= $faq['topic_id'] == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['title_en']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Link to Post</label>
                <select name="post_id" class="form-select">
                    <option value="">None</option>
                    <?php foreach ($posts as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $faq['post_id'] == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars(truncateText($p['title_en'], 50)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-input" value="<?= (int)$faq['sort_order'] ?>" min="0" style="max-width: 150px;">
        </div>
    </div>

    <div class="card">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <label class="toggle-label">
                <input type="checkbox" name="is_published" value="1" class="toggle-input" <?= $faq['is_published'] ? 'checked' : '' ?>>
                <span class="toggle-switch"></span>
                <span style="font-weight: 600;">Published</span>
            </label>
            <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 1rem;">
                <?= $isEdit ? 'Update FAQ' : 'Save FAQ' ?>
            </button>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
