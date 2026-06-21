<?php
/**
 * Law & Reason - Admin Dashboard
 * Overview stats, latest messages, recent posts, quick actions
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();

// Fetch stats
$totalPosts = $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$totalTopics = $db->query("SELECT COUNT(*) FROM topics")->fetchColumn();
$totalScenarios = $db->query("SELECT COUNT(*) FROM scenarios")->fetchColumn();
$totalMessages = $db->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$unreadMessages = $db->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();
$totalSubscribers = $db->query("SELECT COUNT(*) FROM subscribers WHERE is_active = 1")->fetchColumn();

// Latest 5 messages
$latestMessages = $db->query("SELECT id, name, email, legal_area, message, is_read, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Latest 5 posts
$latestPosts = $db->query("SELECT id, title_en, is_published, views, created_at FROM posts ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= $totalPosts ?></div>
        <div class="stat-label">Blog Posts</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $totalTopics ?></div>
        <div class="stat-label">Topics</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $totalScenarios ?></div>
        <div class="stat-label">Scenarios</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $totalMessages ?> <small style="font-size:0.7rem;color:#dc2626;">(<?= $unreadMessages ?> new)</small></div>
        <div class="stat-label">Messages</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $totalSubscribers ?></div>
        <div class="stat-label">Subscribers</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <a href="/admin/post-edit.php" class="btn btn-primary">+ New Post</a>
    <a href="/admin/topic-edit.php" class="btn btn-outline">+ New Topic</a>
    <a href="/admin/scenario-edit.php" class="btn btn-outline">+ New Scenario</a>
    <a href="/admin/myth-edit.php" class="btn btn-outline">+ New Myth</a>
    <a href="/admin/inbox.php" class="btn btn-outline">View Inbox</a>
</div>

<!-- Latest Messages -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Latest Messages</h3>
        <a href="/admin/inbox.php" class="btn btn-sm btn-outline">View All</a>
    </div>
    <?php if (empty($latestMessages)): ?>
        <p style="color: var(--gray-500); font-size: 0.9rem;">No messages yet.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Subject/Area</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($latestMessages as $msg): ?>
                <tr>
                    <td style="font-weight: <?= $msg['is_read'] ? 'normal' : '600' ?>"><?= htmlspecialchars($msg['name']) ?></td>
                    <td><?= htmlspecialchars($msg['legal_area'] ?: 'General') ?></td>
                    <td><?= formatDate($msg['created_at'], 'M d, H:i') ?></td>
                    <td>
                        <?php if ($msg['is_read']): ?>
                            <span class="badge badge-success">Read</span>
                        <?php else: ?>
                            <span class="badge badge-warning">New</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Latest Posts -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Blog Posts</h3>
        <a href="/admin/posts.php" class="btn btn-sm btn-outline">View All</a>
    </div>
    <?php if (empty($latestPosts)): ?>
        <p style="color: var(--gray-500); font-size: 0.9rem;">No posts yet. <a href="/admin/post-edit.php">Create your first post</a>.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Views</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($latestPosts as $post): ?>
                <tr>
                    <td><a href="/admin/post-edit.php?id=<?= $post['id'] ?>" style="color: var(--primary); text-decoration: none;"><?= htmlspecialchars(truncateText($post['title_en'], 60)) ?></a></td>
                    <td>
                        <?php if ($post['is_published']): ?>
                            <span class="badge badge-success">Published</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td><?= number_format($post['views']) ?></td>
                    <td><?= formatDate($post['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
