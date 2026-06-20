<?php
/**
 * Law & Reason - Blog Posts Management
 * List all posts with status, date, views, and actions
 */

$pageTitle = 'Blog Posts';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();

// Handle delete
if (isset($_GET['delete']) && isset($_GET['token'])) {
    if (verifyCSRFToken($_GET['token'])) {
        $id = (int)$_GET['delete'];
        // Get image path to delete
        $stmt = $db->prepare("SELECT featured_image FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch();
        if ($post && $post['featured_image']) {
            require_once __DIR__ . '/../includes/image-handler.php';
            ImageHandler::deleteImage($post['featured_image']);
        }
        $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Post deleted successfully.');
        header('Location: /admin/posts.php');
        exit;
    }
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;
$totalPosts = $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$pagination = getPagination($totalPosts, $page, $perPage);

// Fetch posts
$stmt = $db->prepare("SELECT id, title_en, slug, is_published, views, category, published_at, created_at FROM posts ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(2, $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

$csrfToken = generateCSRFToken();
?>

<div class="page-header">
    <h1>Blog Posts</h1>
    <a href="/admin/post-edit.php" class="btn btn-primary">+ New Post</a>
</div>

<div class="card">
    <?php if (empty($posts)): ?>
        <p style="color: var(--gray-500); font-size: 0.9rem; text-align: center; padding: 40px;">
            No posts yet. <a href="/admin/post-edit.php" style="color: var(--primary);">Create your first blog post</a>.
        </p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Views</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                <tr>
                    <td>
                        <a href="/admin/post-edit.php?id=<?= $post['id'] ?>" style="color: var(--gray-800); text-decoration: none; font-weight: 500;">
                            <?= htmlspecialchars(truncateText($post['title_en'], 50)) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($post['category']) ?></td>
                    <td>
                        <?php if ($post['is_published']): ?>
                            <span class="badge badge-success">Published</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td><?= number_format($post['views']) ?></td>
                    <td><?= formatDate($post['created_at'], 'M d, Y') ?></td>
                    <td class="actions">
                        <a href="/admin/post-edit.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                        <a href="/admin/posts.php?delete=<?= $post['id'] ?>&token=<?= $csrfToken ?>" 
                           class="btn btn-sm btn-danger" 
                           data-confirm="Are you sure you want to delete this post? This cannot be undone.">Delete</a>
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

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
