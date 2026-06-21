<?php
/**
 * Law & Reason - Resources Management
 * List all resources with actions
 */

$pageTitle = 'Resources';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();

// Handle delete
if (isset($_GET['delete']) && isset($_GET['token'])) {
    if (verifyCSRFToken($_GET['token'])) {
        $id = (int)$_GET['delete'];
        $stmt = $db->prepare("DELETE FROM resources WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Resource deleted successfully.');
        header('Location: /admin/resources.php');
        exit;
    }
}

// Fetch resources
$resources = $db->query("SELECT r.id, r.title_en, r.slug, r.resource_type, r.sort_order, r.is_published, r.created_at, t.title_en as topic_name 
    FROM resources r LEFT JOIN topics t ON r.topic_id = t.id 
    ORDER BY r.sort_order ASC")->fetchAll();

$csrfToken = generateCSRFToken();
?>

<div class="page-header">
    <h1>Resources</h1>
    <a href="/admin/resource-edit.php" class="btn btn-primary">+ New Resource</a>
</div>

<div class="card">
    <?php if (empty($resources)): ?>
        <p style="color: var(--gray-500); text-align: center; padding: 40px;">No resources yet. <a href="/admin/resource-edit.php" style="color: var(--primary);">Create your first resource</a>.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Topic</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resources as $resource): ?>
                <tr>
                    <td><?= $resource['sort_order'] ?></td>
                    <td style="font-weight: 500;"><?= htmlspecialchars(truncateText($resource['title_en'], 50)) ?></td>
                    <td><span class="badge badge-info"><?= htmlspecialchars($resource['resource_type']) ?></span></td>
                    <td><?= htmlspecialchars($resource['topic_name'] ?: 'None') ?></td>
                    <td>
                        <?php if ($resource['is_published']): ?>
                            <span class="badge badge-success">Published</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <a href="/admin/resource-edit.php?id=<?= $resource['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                        <a href="/admin/resources.php?delete=<?= $resource['id'] ?>&token=<?= $csrfToken ?>" 
                           class="btn btn-sm btn-danger" data-confirm="Delete this resource?">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
