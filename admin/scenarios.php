<?php
/**
 * Law & Reason - Scenarios Management
 * List all scenarios with actions
 */

$pageTitle = 'Scenarios';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();

// Handle delete
if (isset($_GET['delete']) && isset($_GET['token'])) {
    if (verifyCSRFToken($_GET['token'])) {
        $id = (int)$_GET['delete'];
        $stmt = $db->prepare("DELETE FROM scenarios WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Scenario deleted successfully.');
        header('Location: /admin/scenarios.php');
        exit;
    }
}

// Fetch scenarios with topic name
$scenarios = $db->query("SELECT s.id, s.question_en, s.slug, s.sort_order, s.is_published, s.created_at, t.title_en as topic_name 
    FROM scenarios s LEFT JOIN topics t ON s.topic_id = t.id 
    ORDER BY s.sort_order ASC")->fetchAll();

$csrfToken = generateCSRFToken();
?>

<div class="page-header">
    <h1>Scenarios</h1>
    <a href="/admin/scenario-edit.php" class="btn btn-primary">+ New Scenario</a>
</div>

<div class="card">
    <?php if (empty($scenarios)): ?>
        <p style="color: var(--gray-500); text-align: center; padding: 40px;">No scenarios yet. <a href="/admin/scenario-edit.php" style="color: var(--primary);">Create your first scenario</a>.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Question</th>
                    <th>Topic</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scenarios as $scenario): ?>
                <tr>
                    <td><?= $scenario['sort_order'] ?></td>
                    <td style="font-weight: 500;"><?= htmlspecialchars(truncateText($scenario['question_en'], 60)) ?></td>
                    <td><?= htmlspecialchars($scenario['topic_name'] ?: 'None') ?></td>
                    <td>
                        <?php if ($scenario['is_published']): ?>
                            <span class="badge badge-success">Published</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <a href="/admin/scenario-edit.php?id=<?= $scenario['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                        <a href="/admin/scenarios.php?delete=<?= $scenario['id'] ?>&token=<?= $csrfToken ?>" 
                           class="btn btn-sm btn-danger" 
                           data-confirm="Are you sure you want to delete this scenario?">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
