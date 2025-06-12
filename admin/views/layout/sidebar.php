<?php
// Enhanced security check
if (!defined('ADMIN_PATH')) {
    define('ADMIN_PATH', realpath(dirname(__FILE__, 3))); // Go up 3 levels from current file
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Strict admin access check
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    header('Location: ' . ADMIN_PATH . '/auth/login.php');
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
$sidebar_collapsed = isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] === 'true';

// Calculate correct paths based on the admin directory structure
// $base_path = ADMIN_PATH;
// $web_root = str_replace($_SERVER['DOCUMENT_ROOT'], '', $base_path);

$base_admin_path = '/bts-smkn1lmj-2025/admin';
?>

<div class="sidebar <?php echo $sidebar_collapsed ? 'collapsed' : ''; ?>" id="sidebar">
    <div class="logo-container">
        <i class="fas fa-chart-line"></i>
        <h1>BTS Admin</h1>
    </div>

    <div class="sidebar-content">
        <p class="sidebar-category">Main</p>
        <ul class="sidebar-menu">
            <li class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <a href="<?= $base_admin_path ?>/views/dashboard.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        </ul>

        <p class="sidebar-category">Content</p>
        <ul class="sidebar-menu">
            <li class="<?= $current_page == 'category.php' ? 'active' : '' ?>">
                <a href="<?= $base_admin_path ?>/views/category/category.php" class="nav-link">
                    <i class="fas fa-calendar"></i>
                    <span>Category</span>
                </a>
            </li>
            <li class="<?= $current_page == 'empty.php' ? 'active' : '' ?>">
                <a href="<?= $base_admin_path ?>/views/tahun/empty.php" class="nav-link">
                    <i class="fas fa-calendar"></i>
                    <span>Year Selection</span>
                </a>
            </li>
            <li class="<?= $current_page == 'admin_management.php' ? 'active' : '' ?>">
                <a href="<?= $base_admin_path ?>/views/admin_management.php" class="nav-link">
                    <i class="fas fa-users-cog"></i>
                    <span>Kelola Admin</span>
                </a>
            </li>
        </ul>

        <p class="sidebar-category">Other</p>
        <ul class="sidebar-menu">
            <li class="logout-item">
                <a href="<?= $base_admin_path ?>/auth/logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-chevron-left"></i>
    </button>
</div>

<button class="mobile-menu-toggle" id="mobileMenuToggle" style="display: none;">
    <i class="fas fa-bars"></i>
</button>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Check if we're on dashboard and enable all links
    if (window.location.pathname.includes('dashboard.php')) {
        navLinks.forEach(link => {
            link.style.pointerEvents = 'auto';
            link.style.cursor = 'pointer';
        });
    }

    // Toggle sidebar collapse
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            const isCollapsed = sidebar.classList.toggle('collapsed');
            document.cookie = `sidebar_collapsed=${isCollapsed}; path=/; max-age=${60 * 60 * 24 * 30}`;
            
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-chevron-left');
                icon.classList.toggle('fa-chevron-right');
            }
        });
    }

    // Mobile menu toggle
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }

    // Responsive handling
    function handleResponsive() {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('collapsed');
            mobileMenuToggle.style.display = 'block';
            sidebarToggle.style.display = 'none';
            sidebar.style.transform = sidebar.classList.contains('show') ? 
                'translateX(0)' : 'translateX(-100%)';
        } else {
            mobileMenuToggle.style.display = 'none';
            sidebarToggle.style.display = 'flex';
            sidebar.style.transform = 'translateX(0)';
        }
    }

    window.addEventListener('resize', handleResponsive);
    handleResponsive();
});
</script>