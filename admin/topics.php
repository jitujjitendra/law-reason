<?php
/**
 * Law & Reason - Topics Management
 * List all legal topics with actions
 */

$pageTitle = 'Topics';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();

// Handle delete
if (isset($_GET['delete']) && isset($_GET['token'])) {
    if (verifyCSRFToken($_GET['token'])) {
        $id = (int)$_GET['delete'];
        $stmt = $db->prepare("DELETE FROM topics WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Topic deleted successfully.');
        header('Location: /admin/topics.php');
        exit;
    }
}

// Fetch topics
$topics = $db->query("SELECT id, title_en, slug, icon, sort_order, is_published, created_at FROM topics ORDER BY sort_order ASC")->fetchAll();
$csrfToken = generateCSRFToken();
?>

<div class="page-header">
    <h1>Topics</h1>
    <a href="/admin/topic-edit.php" class="btn btn-primary">+ New Topic</a>
</div>

<div class="card">
    <?php if (empty($topics)): ?>
        <p style="color: var(--gray-500); text-align: center; padding: 40px;">No topics yet. <a href="/admin/topic-edit.php" style="color: var(--primary);">Create your first topic</a>.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Title</th>
                    <th>Icon</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topics as $topic): ?>
                <tr>
                    <td><?= $topic['sort_order'] ?></td>
                    <td style="font-weight: 500;"><?= htmlspecialchars($topic['title_en']) ?></td>
                    <td><?= htmlspecialchars($topic['icon'] ?: '-') ?></td>
                    <td>
                        <?php if ($topic['is_published']): ?>
                            <span class="badge badge-success">Published</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <a href="/admin/topic-edit.php?id=<?= $topic['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                        <a href="/admin/topics.php?delete=<?= $topic['id'] ?>&token=<?= $csrfToken ?>" 
                           class="btn btn-sm btn-danger" 
                           data-confirm="Are you sure you want to delete this topic? Related scenarios and myths may be affected.">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
