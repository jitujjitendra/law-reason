<?php
/**
 * Law & Reason - Admin Header/Layout Template
 * Included at the top of every admin page (except login)
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/helpers.php';

startSecureSession();
requireAdmin();

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>Law &amp; Reason Admin</title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #dbeafe;
            --success: #16a34a;
            --success-light: #dcfce7;
            --danger: #dc2626;
            --danger-light: #fee2e2;
            --warning: #d97706;
            --warning-light: #fef3c7;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --sidebar-width: 250px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--gray-50);
            color: var(--gray-800);
            line-height: 1.6;
        }

        /* Sidebar */
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: var(--gray-900);
            color: #fff;
            overflow-y: auto;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 20px;
            border-bottom: 1px solid var(--gray-700);
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
            display: block;
        }

        .sidebar-brand span { color: var(--primary); }

        .sidebar-nav { padding: 16px 0; }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            color: var(--gray-300);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .sidebar-nav a:hover {
            background: var(--gray-800);
            color: #fff;
        }

        .sidebar-nav a.active {
            background: var(--gray-800);
            color: #fff;
            border-left-color: var(--primary);
        }

        .sidebar-nav a .nav-icon {
            width: 20px;
            margin-right: 12px;
            text-align: center;
            font-size: 1rem;
        }

        .sidebar-section {
            padding: 12px 20px 6px;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray-500);
            font-weight: 600;
        }

        /* Top Bar */
        .admin-topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: 60px;
            background: #fff;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 99;
        }

        .topbar-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .topbar-user {
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .topbar-logout {
            padding: 6px 14px;
            background: var(--gray-100);
            color: var(--gray-700);
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.8rem;
            transition: background 0.2s;
        }

        .topbar-logout:hover { background: var(--gray-200); }

        /* Main Content */
        .admin-main {
            margin-left: var(--sidebar-width);
            margin-top: 60px;
            padding: 24px;
            min-height: calc(100vh - 60px);
        }

        /* Flash Messages */
        .flash-message {
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .flash-success {
            background: var(--success-light);
            color: var(--success);
            border: 1px solid #bbf7d0;
        }

        .flash-error {
            background: var(--danger-light);
            color: var(--danger);
            border: 1px solid #fecaca;
        }

        .flash-warning {
            background: var(--warning-light);
            color: var(--warning);
            border: 1px solid #fde68a;
        }

        /* Cards */
        .card {
            background: #fff;
            border-radius: 10px;
            border: 1px solid var(--gray-200);
            padding: 24px;
            margin-bottom: 20px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #fff;
            border-radius: 10px;
            border: 1px solid var(--gray-200);
            padding: 20px;
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 4px;
        }

        /* Tables */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th {
            text-align: left;
            padding: 12px 16px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray-500);
            border-bottom: 2px solid var(--gray-200);
            font-weight: 600;
        }

        .admin-table td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--gray-100);
            font-size: 0.875rem;
            color: var(--gray-700);
        }

        .admin-table tr:hover td { background: var(--gray-50); }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            line-height: 1.5;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }
        .btn-primary:hover { background: var(--primary-dark); }

        .btn-success {
            background: var(--success);
            color: #fff;
        }
        .btn-success:hover { background: #15803d; }

        .btn-danger {
            background: var(--danger);
            color: #fff;
        }
        .btn-danger:hover { background: #b91c1c; }

        .btn-outline {
            background: #fff;
            color: var(--gray-700);
            border: 1px solid var(--gray-300);
        }
        .btn-outline:hover { background: var(--gray-50); border-color: var(--gray-400); }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 6px;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--gray-300);
            border-radius: 6px;
            font-size: 0.9rem;
            font-family: inherit;
            transition: border-color 0.2s;
            background: #fff;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .form-textarea { min-height: 120px; resize: vertical; }

        /* Bilingual columns */
        .bilingual-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .lang-label {
            display: inline-block;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 3px;
            margin-left: 6px;
            font-weight: 600;
        }

        .lang-en { background: #dbeafe; color: #1d4ed8; }
        .lang-hi { background: #fef3c7; color: #d97706; }

        /* Status badges */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success { background: var(--success-light); color: var(--success); }
        .badge-warning { background: var(--warning-light); color: var(--warning); }
        .badge-danger { background: var(--danger-light); color: var(--danger); }
        .badge-info { background: var(--primary-light); color: var(--primary); }

        /* Pagination */
        .pagination {
            display: flex;
            gap: 6px;
            margin-top: 20px;
            justify-content: center;
        }

        .pagination a, .pagination span {
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            border: 1px solid var(--gray-200);
            color: var(--gray-600);
        }

        .pagination a:hover { background: var(--gray-100); }
        .pagination .active { background: var(--primary); color: #fff; border-color: var(--primary); }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 24px;
        }

        /* Toggle Switch */
        .toggle-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .toggle-switch {
            position: relative;
            width: 44px;
            height: 24px;
            background: var(--gray-300);
            border-radius: 12px;
            transition: background 0.3s;
        }

        .toggle-switch::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            background: #fff;
            border-radius: 50%;
            transition: transform 0.3s;
        }

        .toggle-input { display: none; }
        .toggle-input:checked + .toggle-switch { background: var(--primary); }
        .toggle-input:checked + .toggle-switch::after { transform: translateX(20px); }

        /* Image Preview */
        .image-preview {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            border: 1px solid var(--gray-200);
            margin-top: 8px;
            object-fit: cover;
        }

        /* Action buttons in tables */
        .actions { display: flex; gap: 6px; }

        /* Page header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .bilingual-row { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .admin-sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .admin-sidebar.open { transform: translateX(0); }
            .admin-topbar { left: 0; }
            .admin-main { margin-left: 0; }
            .mobile-menu-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                border: 1px solid var(--gray-200);
                border-radius: 6px;
                background: var(--gray-50);
                cursor: pointer;
                font-size: 1.2rem;
            }
            .admin-overlay {
                display: none;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.4);
                z-index: 99;
            }
            .admin-overlay.active { display: block; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (min-width: 769px) {
            .mobile-menu-toggle { display: none; }
            .admin-overlay { display: none !important; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <a href="/admin/" class="sidebar-brand">
            <span>Law</span> &amp; Reason
        </a>
        <nav class="sidebar-nav">
            <div class="sidebar-section">Main</div>
            <a href="/admin/index.php" class="<?= $currentPage === 'index' ? 'active' : '' ?>">
                <span class="nav-icon">&#9632;</span> Dashboard
            </a>

            <div class="sidebar-section">Content</div>
            <a href="/admin/posts.php" class="<?= in_array($currentPage, ['posts', 'post-edit']) ? 'active' : '' ?>">
                <span class="nav-icon">&#9998;</span> Blog Posts
            </a>
            <a href="/admin/topics.php" class="<?= in_array($currentPage, ['topics', 'topic-edit']) ? 'active' : '' ?>">
                <span class="nav-icon">&#9733;</span> Topics
            </a>
            <a href="/admin/scenarios.php" class="<?= in_array($currentPage, ['scenarios', 'scenario-edit']) ? 'active' : '' ?>">
                <span class="nav-icon">&#10067;</span> Scenarios
            </a>
            <a href="/admin/myths.php" class="<?= in_array($currentPage, ['myths', 'myth-edit']) ? 'active' : '' ?>">
                <span class="nav-icon">&#10005;</span> Myths
            </a>
            <a href="/admin/resources.php" class="<?= in_array($currentPage, ['resources', 'resource-edit']) ? 'active' : '' ?>">
                <span class="nav-icon">&#128196;</span> Resources
            </a>
            <a href="/admin/faqs.php" class="<?= in_array($currentPage, ['faqs', 'faq-edit']) ? 'active' : '' ?>">
                <span class="nav-icon">&#10068;</span> FAQs
            </a>

            <div class="sidebar-section">Communication</div>
            <a href="/admin/inbox.php" class="<?= $currentPage === 'inbox' ? 'active' : '' ?>">
                <span class="nav-icon">&#9993;</span> Inbox
            </a>
            <a href="/admin/subscribers.php" class="<?= $currentPage === 'subscribers' ? 'active' : '' ?>">
                <span class="nav-icon">&#128233;</span> Subscribers
            </a>

            <div class="sidebar-section">System</div>
            <a href="/admin/settings.php" class="<?= $currentPage === 'settings' ? 'active' : '' ?>">
                <span class="nav-icon">&#9881;</span> Settings
            </a>
        </nav>
    </aside>

    <!-- Top Bar -->
    <header class="admin-topbar">
        <div style="display:flex;align-items:center;gap:12px;">
            <button class="mobile-menu-toggle" id="adminMenuToggle" type="button" aria-label="Open menu">&#9776;</button>
            <div class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></div>
        </div>
        <div class="topbar-right">
            <span class="topbar-user">Hello, <?= htmlspecialchars($adminName) ?></span>
            <a href="/admin/logout.php" class="topbar-logout">Logout</a>
        </div>
    </header>

    <!-- Mobile overlay -->
    <div class="admin-overlay" id="adminOverlay"></div>

    <!-- Main Content -->
    <main class="admin-main">
        <?php
        $flash = getFlash();
        if ($flash):
        ?>
        <div class="flash-message flash-<?= htmlspecialchars($flash['type']) ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>
