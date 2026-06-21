<?php
/**
 * Law & Reason - Inbox (Contact Messages)
 * View messages, mark as read, view full message
 */

$pageTitle = 'Inbox';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();

// Mark as read
if (isset($_GET['read']) && isset($_GET['token'])) {
    if (verifyCSRFToken($_GET['token'])) {
        $id = (int)$_GET['read'];
        $stmt = $db->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: /admin/inbox.php?view=' . $id);
        exit;
    }
}

// Delete message
if (isset($_GET['delete']) && isset($_GET['token'])) {
    if (verifyCSRFToken($_GET['token'])) {
        $id = (int)$_GET['delete'];
        $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Message deleted.');
        header('Location: /admin/inbox.php');
        exit;
    }
}

// View single message
$viewMessage = null;
if (isset($_GET['view'])) {
    $stmt = $db->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([(int)$_GET['view']]);
    $viewMessage = $stmt->fetch();
    if ($viewMessage && !$viewMessage['is_read']) {
        $db->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?")->execute([$viewMessage['id']]);
        $viewMessage['is_read'] = 1;
    }
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;
$totalMessages = $db->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$pagination = getPagination($totalMessages, $page, $perPage);

// Fetch messages
$stmt = $db->prepare("SELECT id, name, email, legal_area, message, is_read, is_replied, created_at FROM contact_messages ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(2, $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$messages = $stmt->fetchAll();

$csrfToken = generateCSRFToken();
?>

<div class="page-header">
    <h1>Inbox</h1>
</div>

<?php if ($viewMessage): ?>
<!-- Single Message View -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Message from <?= htmlspecialchars($viewMessage['name']) ?></h3>
        <a href="/admin/inbox.php" class="btn btn-sm btn-outline">Back to Inbox</a>
    </div>
    <div style="margin-bottom: 16px; padding: 16px; background: var(--gray-50); border-radius: 8px;">
        <p><strong>From:</strong> <?= htmlspecialchars($viewMessage['name']) ?> (<?= htmlspecialchars($viewMessage['email']) ?>)</p>
        <p><strong>Legal Area:</strong> <?= htmlspecialchars($viewMessage['legal_area'] ?: 'Not specified') ?></p>
        <p><strong>Date:</strong> <?= formatDate($viewMessage['created_at'], 'M d, Y \a\t H:i') ?></p>
        <p><strong>IP:</strong> <?= htmlspecialchars($viewMessage['ip_address'] ?: 'Unknown') ?></p>
    </div>
    <div style="padding: 20px; background: #fff; border: 1px solid var(--gray-200); border-radius: 8px; line-height: 1.8;">
        <?= nl2br(htmlspecialchars($viewMessage['message'])) ?>
    </div>
    <div style="margin-top: 16px; display: flex; gap: 10px;">
        <a href="mailto:<?= htmlspecialchars($viewMessage['email']) ?>?subject=Re: Your query on Law %26 Reason" class="btn btn-primary">Reply via Email</a>
        <a href="/admin/inbox.php?delete=<?= $viewMessage['id'] ?>&token=<?= $csrfToken ?>" class="btn btn-danger" data-confirm="Delete this message permanently?">Delete</a>
    </div>
</div>
<?php else: ?>
<!-- Messages List -->
<div class="card">
    <?php if (empty($messages)): ?>
        <p style="color: var(--gray-500); text-align: center; padding: 40px;">No messages yet.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Area</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                <tr style="<?= !$msg['is_read'] ? 'background: #eff6ff;' : '' ?>">
                    <td>
                        <?php if (!$msg['is_read']): ?>
                            <span class="badge badge-warning">New</span>
                        <?php else: ?>
                            <span class="badge badge-success">Read</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight: <?= $msg['is_read'] ? 'normal' : '600' ?>;"><?= htmlspecialchars($msg['name']) ?></td>
                    <td><?= htmlspecialchars($msg['email']) ?></td>
                    <td><?= htmlspecialchars($msg['legal_area'] ?: '-') ?></td>
                    <td><?= htmlspecialchars(truncateText($msg['message'], 40)) ?></td>
                    <td><?= formatDate($msg['created_at'], 'M d') ?></td>
                    <td class="actions">
                        <a href="/admin/inbox.php?view=<?= $msg['id'] ?>" class="btn btn-sm btn-outline">View</a>
                        <?php if (!$msg['is_read']): ?>
                            <a href="/admin/inbox.php?read=<?= $msg['id'] ?>&token=<?= $csrfToken ?>" class="btn btn-sm btn-outline">Mark Read</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($pagination['total_pages'] > 1): ?>
        <div class="pagination">
            <?php if ($pagination['has_prev']): ?>
                <a href="?page=<?= $page - 1 ?>">&laquo; Prev</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($pagination['has_next']): ?>
                <a href="?page=<?= $page + 1 ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
