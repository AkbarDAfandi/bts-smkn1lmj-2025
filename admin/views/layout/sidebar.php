<?php
include_once(__DIR__ . '/../../../config.php');
$current_page = basename($_SERVER['PHP_SELF']);


// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
 header("Location: " . BASE_URL . "index.php");
 exit();
}

?>
<div class="sidebar" id="sidebar">
 <div class="logo-container">
  <i class="fas fa-chart-line"></i>
  <h1>BTS Admin</h1>
 </div>

 <p class="sidebar-category">Main</p>
 <ul class="sidebar-menu">
  <li class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
   <a href="<?= BASE_URL ?>admin/views/dashboard.php" class="nav-link">
    <i class="fas fa-home"></i><span>Dashboard</span>
   </a>
  </li>
  <!-- <li><a href="#"><i class="fas fa-shopping-cart"></i> <span>E-commerce</span></a></li> -->
 </ul>

 <p class="sidebar-category">Content</p>
 <ul class="sidebar-menu">
  <li class="<?= $current_page == 'category.php' ? 'active' : '' ?>">
   <a href="<?= BASE_URL ?>admin/views/category/category.php" class="nav-link">
    <i class="fas fa-list-alt"></i>
    <span>Category</span>
   </a>
  </li>
  <li class="<?= $current_page == 'empty.php' ? 'active' : '' ?>">
   <a href="<?= BASE_URL ?>admin/views/tahun/empty.php" class="nav-link">
    <i class="fas fa-calendar"></i>
    <span>Year Selection</span>
   </a>
  </li>
 </ul>

 <p class="sidebar-category">Other</p>
 <ul class="sidebar-menu">
  <li><a href="#"><i class="fas fa-users"></i> <span>Users</span></a></li>
  <li class="logout-item">
   <a href="<?= BASE_URL ?>admin/auth/logout.php" class="nav-link">
    <i class="fas fa-sign-out-alt"></i>
    <span>Logout</span>
   </a>
  </li>
 </ul>

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
  const mainContent = document.querySelector('.main-content');

  // Toggle sidebar collapse
  sidebarToggle.addEventListener('click', function() {
   sidebar.classList.toggle('collapsed');
   const icon = this.querySelector('i');
   if (sidebar.classList.contains('collapsed')) {
    icon.classList.remove('fa-chevron-left');
    icon.classList.add('fa-chevron-right');
   } else {
    icon.classList.remove('fa-chevron-right');
    icon.classList.add('fa-chevron-left');
   }
  });

  // Toggle mobile menu
  mobileMenuToggle.addEventListener('click', function() {
   sidebar.classList.toggle('show');
  });

  // Check screen size and adjust sidebar
  function checkScreenSize() {
   if (window.innerWidth <= 768) {
    sidebar.classList.remove('collapsed');
    mobileMenuToggle.style.display = 'block';
    sidebarToggle.style.display = 'none';
    if (!sidebar.classList.contains('show')) {
     sidebar.style.transform = 'translateX(-100%)';
    }
   } else {
    mobileMenuToggle.style.display = 'none';
    sidebarToggle.style.display = 'flex';
    sidebar.style.transform = 'translateX(0)';
   }
  }

  // Initial check
  checkScreenSize();

  // Check on resize
  window.addEventListener('resize', checkScreenSize);

  // Close mobile menu when clicking on content
  mainContent.addEventListener('click', function() {
   if (window.innerWidth <= 768 && sidebar.classList.contains('show')) {
    sidebar.classList.remove('show');
   }
  });
 });

 // confrim logout<script>
document.addEventListener('DOMContentLoaded', function() {
    const logoutLink = document.querySelector('.logout-item a');
    
    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            const confirmLogout = confirm('Apakah Anda yakin ingin logout?');
            
            if (confirmLogout) {
                window.location.href = this.href;
            }
        });
    }
});

</script>