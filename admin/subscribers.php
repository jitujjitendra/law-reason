<?php
/**
 * Law & Reason - Subscribers Management
 * View subscriber list, export CSV
 */

$pageTitle = 'Subscribers';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();

// Export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $subscribers = $db->query("SELECT email, is_active, subscribed_at FROM subscribers ORDER BY subscribed_at DESC")->fetchAll();
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="subscribers_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Email', 'Status', 'Subscribed Date']);
    foreach ($subscribers as $sub) {
        fputcsv($output, [
            $sub['email'],
            $sub['is_active'] ? 'Active' : 'Inactive',
            $sub['subscribed_at']
        ]);
    }
    fclose($output);
    exit;
}

// Toggle status
if (isset($_GET['toggle']) && isset($_GET['token'])) {
    if (verifyCSRFToken($_GET['token'])) {
        $id = (int)$_GET['toggle'];
        $db->prepare("UPDATE subscribers SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
        setFlash('success', 'Subscriber status updated.');
        header('Location: /admin/subscribers.php');
        exit;
    }
}

// Delete subscriber
if (isset($_GET['delete']) && isset($_GET['token'])) {
    if (verifyCSRFToken($_GET['token'])) {
        $id = (int)$_GET['delete'];
        $db->prepare("DELETE FROM subscribers WHERE id = ?")->execute([$id]);
        setFlash('success', 'Subscriber removed.');
        header('Location: /admin/subscribers.php');
        exit;
    }
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;
$totalSubscribers = $db->query("SELECT COUNT(*) FROM subscribers")->fetchColumn();
$activeCount = $db->query("SELECT COUNT(*) FROM subscribers WHERE is_active = 1")->fetchColumn();
$pagination = getPagination($totalSubscribers, $page, $perPage);

$stmt = $db->prepare("SELECT id, email, is_active, subscribed_at FROM subscribers ORDER BY subscribed_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(2, $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$subscribers = $stmt->fetchAll();

$csrfToken = generateCSRFToken();
?>

<div class="page-header">
    <h1>Subscribers</h1>
    <a href="/admin/subscribers.php?export=csv" class="btn btn-success">Export CSV</a>
</div>

<!-- Stats -->
<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-number"><?= $totalSubscribers ?></div>
        <div class="stat-label">Total Subscribers</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $activeCount ?></div>
        <div class="stat-label">Active</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $totalSubscribers - $activeCount ?></div>
        <div class="stat-label">Inactive</div>
    </div>
</div>

<div class="card">
    <?php if (empty($subscribers)): ?>
        <p style="color: var(--gray-500); text-align: center; padding: 40px;">No subscribers yet.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Subscribed</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscribers as $sub): ?>
                <tr>
                    <td><?= htmlspecialchars($sub['email']) ?></td>
                    <td>
                        <?php if ($sub['is_active']): ?>
                            <span class="badge badge-success">Active</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td><?= formatDate($sub['subscribed_at']) ?></td>
                    <td class="actions">
                        <a href="/admin/subscribers.php?toggle=<?= $sub['id'] ?>&token=<?= $csrfToken ?>" class="btn btn-sm btn-outline">
                            <?= $sub['is_active'] ? 'Deactivate' : 'Activate' ?>
                        </a>
                        <a href="/admin/subscribers.php?delete=<?= $sub['id'] ?>&token=<?= $csrfToken ?>" 
                           class="btn btn-sm btn-danger" data-confirm="Remove this subscriber?">Remove</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($pagination['total_pages'] > 1): ?>
        <div class="pagination">
            <?php if ($pagination['has_prev']): ?><a href="?page=<?= $page - 1 ?>">&laquo; Prev</a><?php endif; ?>
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <?php if ($i == $page): ?><span class="active"><?= $i ?></span>
                <?php else: ?><a href="?page=<?= $i ?>"><?= $i ?></a><?php endif; ?>
            <?php endfor; ?>
            <?php if ($pagination['has_next']): ?><a href="?page=<?= $page + 1 ?>">Next &raquo;</a><?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
