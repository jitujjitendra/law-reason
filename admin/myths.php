<?php
/**
 * Law & Reason - Myths Management
 * List all myths with actions
 */

$pageTitle = 'Myths';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();

// Handle delete
if (isset($_GET['delete']) && isset($_GET['token'])) {
    if (verifyCSRFToken($_GET['token'])) {
        $id = (int)$_GET['delete'];
        $stmt = $db->prepare("DELETE FROM myths WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Myth deleted successfully.');
        header('Location: /admin/myths.php');
        exit;
    }
}

// Fetch myths with topic name
$myths = $db->query("SELECT m.id, m.myth_en, m.slug, m.icon, m.sort_order, m.is_published, m.created_at, t.title_en as topic_name 
    FROM myths m LEFT JOIN topics t ON m.topic_id = t.id 
    ORDER BY m.sort_order ASC")->fetchAll();

$csrfToken = generateCSRFToken();
?>

<div class="page-header">
    <h1>Myths vs Reality</h1>
    <a href="/admin/myth-edit.php" class="btn btn-primary">+ New Myth</a>
</div>

<div class="card">
    <?php if (empty($myths)): ?>
        <p style="color: var(--gray-500); text-align: center; padding: 40px;">No myths yet. <a href="/admin/myth-edit.php" style="color: var(--primary);">Create your first myth</a>.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Myth</th>
                    <th>Topic</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($myths as $myth): ?>
                <tr>
                    <td><?= $myth['sort_order'] ?></td>
                    <td style="font-weight: 500;"><?= htmlspecialchars(truncateText($myth['myth_en'], 60)) ?></td>
                    <td><?= htmlspecialchars($myth['topic_name'] ?: 'None') ?></td>
                    <td>
                        <?php if ($myth['is_published']): ?>
                            <span class="badge badge-success">Published</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <a href="/admin/myth-edit.php?id=<?= $myth['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                        <a href="/admin/myths.php?delete=<?= $myth['id'] ?>&token=<?= $csrfToken ?>" 
                           class="btn btn-sm btn-danger" 
                           data-confirm="Are you sure you want to delete this myth?">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
