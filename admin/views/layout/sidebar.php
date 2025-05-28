<?php
// Enhanced security check
if (!defined('ADMIN_PATH')) {
    define('ADMIN_PATH', realpath(__DIR__ . '/../../'));
}

// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Strict admin access check
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit();
}

$current_page = basename($_SERVER['PHP_SELF'] ?? 'dashboard.php');
$sidebar_collapsed = isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] === 'true';
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
                <a href="../../views/dashboard.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        </ul>

        <p class="sidebar-category">Content</p>
        <ul class="sidebar-menu">
            <li class="<?= $current_page == 'category.php' ? 'active' : '' ?>">
               <a href="<?= $current_page == 'empty.php' ? '../category/category.php' : '../views/category/category.php' ?>"
                    class="nav-link">
                    <i class="fas fa-calendar"></i>
                    <span>Category</span>
                </a>
            </li>
            <li class="<?= $current_page == 'empty.php' ? 'active' : '' ?>">
                <a href="<?= $current_page == 'category.php' ? '../tahun/empty.php' : '../views/tahun/empty.php' ?>"
                    class="nav-link">
                    <i class="fas fa-calendar"></i>
                    <span>Year Selection</span>
                </a>
            </li>
        </ul>

        <p class="sidebar-category">Other</p>
        <ul class="sidebar-menu">
            <li class="logout-item">
                <a href="../../auth/logout.php" class="nav-link">
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
        const mainContent = document.querySelector('.main-content') || document.body;

        // Toggle sidebar collapse with cookie persistence
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                const isCollapsed = sidebar.classList.toggle('collapsed');
                document.cookie =
                    `sidebar_collapsed=${isCollapsed}; path=/; max-age=${60 * 60 * 24 * 30}`; // 30 hari

                const icon = this.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-chevron-left');
                    icon.classList.toggle('fa-chevron-right');
                }
            });
        }

        // Toggle mobile menu
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
        }

        // Responsive handling
        function handleResponsive() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('collapsed');
                if (mobileMenuToggle) mobileMenuToggle.style.display = 'block';
                if (sidebarToggle) sidebarToggle.style.display = 'none';
                sidebar.style.transform = sidebar.classList.contains('show') ?
                    'translateX(0)' : 'translateX(-100%)';
            } else {
                if (mobileMenuToggle) mobileMenuToggle.style.display = 'none';
                if (sidebarToggle) sidebarToggle.style.display = 'flex';
                sidebar.style.transform = 'translateX(0)';
            }
        }

        // Event listeners
        window.addEventListener('resize', handleResponsive);

        if (mainContent) {
            mainContent.addEventListener('click', function() {
                if (window.innerWidth <= 768 && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            });
        }

        // Initialize
        handleResponsive();

        // Enhanced logout confirmation
        const logoutItem = document.querySelector('.logout-item a');
        if (logoutItem) {
            logoutItem.addEventListener('click', function(e) {
                if (!confirm('Apakah Anda yakin ingin logout?')) {
                    e.preventDefault();
                }
            });
        }
    });
</script>
