<?php
/**
 * Law & Reason - FAQs Management
 * List all FAQs with actions
 */

$pageTitle = 'FAQs';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();

// Handle delete
if (isset($_GET['delete']) && isset($_GET['token'])) {
    if (verifyCSRFToken($_GET['token'])) {
        $id = (int)$_GET['delete'];
        $stmt = $db->prepare("DELETE FROM faqs WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'FAQ deleted successfully.');
        header('Location: /admin/faqs.php');
        exit;
    }
}

// Fetch FAQs
$faqs = $db->query("SELECT f.id, f.question_en, f.sort_order, f.is_published, f.created_at, 
    t.title_en as topic_name, p.title_en as post_name
    FROM faqs f 
    LEFT JOIN topics t ON f.topic_id = t.id 
    LEFT JOIN posts p ON f.post_id = p.id
    ORDER BY f.sort_order ASC")->fetchAll();

$csrfToken = generateCSRFToken();
?>

<div class="page-header">
    <h1>FAQs</h1>
    <a href="/admin/faq-edit.php" class="btn btn-primary">+ New FAQ</a>
</div>

<div class="card">
    <?php if (empty($faqs)): ?>
        <p style="color: var(--gray-500); text-align: center; padding: 40px;">No FAQs yet. <a href="/admin/faq-edit.php" style="color: var(--primary);">Create your first FAQ</a>.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Question</th>
                    <th>Linked To</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($faqs as $faq): ?>
                <tr>
                    <td><?= $faq['sort_order'] ?></td>
                    <td style="font-weight: 500;"><?= htmlspecialchars(truncateText($faq['question_en'], 60)) ?></td>
                    <td>
                        <?php if ($faq['topic_name']): ?>
                            <span class="badge badge-info">Topic: <?= htmlspecialchars(truncateText($faq['topic_name'], 20)) ?></span>
                        <?php elseif ($faq['post_name']): ?>
                            <span class="badge badge-info">Post: <?= htmlspecialchars(truncateText($faq['post_name'], 20)) ?></span>
                        <?php else: ?>
                            <span style="color: var(--gray-400);">General</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($faq['is_published']): ?>
                            <span class="badge badge-success">Published</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <a href="/admin/faq-edit.php?id=<?= $faq['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                        <a href="/admin/faqs.php?delete=<?= $faq['id'] ?>&token=<?= $csrfToken ?>" 
                           class="btn btn-sm btn-danger" data-confirm="Delete this FAQ?">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
