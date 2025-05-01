<?php
session_start();
require_once __DIR__ . '/../../../config.php';

// Check admin session
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu";
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// Get all categories
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY created_at DESC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal mengambil data kategori: " . $e->getMessage();
    $categories = [];
}

// Handle delete request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Kategori berhasil dihapus";
        header("Location: " . BASE_URL . "views/category/category.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Gagal menghapus kategori: " . $e->getMessage();
        header("Location: " . BASE_URL . "views/category/category.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Dashboard | BTS Admin</title>
    <link rel="stylesheet" href="../../public/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/sidebar.css">
    <style>
    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 300;
        src: url('../public/fonts/poppins/Poppins-Light.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 400;
        src: url('../public/fonts/poppins/Poppins-Regular.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 500;
        src: url('../public/fonts/poppins/Poppins-Medium.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 600;
        src: url('../public/fonts/poppins/Poppins-SemiBold.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 700;
        src: url('../public/fonts/poppins/Poppins-Bold.ttf') format('truetype');
    }
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    :root {
        --primary-color: #4361ee;
        --secondary-color: #3f37c9;
        --accent-color: #4895ef;
        --text-color: #333;
        --text-light: #7c7c7c;
        --bg-color: #f8f9fa;
        --card-bg: #ffffff;
        --sidebar-bg: #1a1c23;
        --sidebar-text: #e2e8f0;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
    }

    body {
        display: flex;
        background-color: var(--bg-color);
        color: var(--text-color);
        min-height: 100vh;
    }

    .main-content {
        flex: 1;
        padding: 2rem;
        margin-left: 250px;
        min-height: 100vh;
    }

    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .page-title {
        font-size: 1.8rem;
        color: var(--primary-color);
        font-weight: 600;
    }

    .btn-new {
        padding: 10px 20px;
        background-color: var(--primary-color);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-new:hover {
        background-color: var(--secondary-color);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Category Cards */
    .categories-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .category-card {
        background-color: var(--card-bg);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .category-name {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 1rem;
    }

    .category-meta {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        color: var(--text-light);
    }

    .meta-item i {
        width: 20px;
        text-align: center;
    }

    .category-actions {
        display: flex;
        gap: 10px;
        margin-top: 1rem;
    }

    .btn-action {
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
    }

    .btn-edit {
        background-color: rgba(67, 97, 238, 0.1);
        color: var(--primary-color);
    }

    .btn-edit:hover {
        background-color: rgba(67, 97, 238, 0.2);
    }

    .btn-delete {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger-color);
    }

    .btn-delete:hover {
        background-color: rgba(239, 68, 68, 0.2);
    }

    /* Notification styles */
    .alert {
        padding: 12px 20px;
        border-radius: 4px;
        margin-bottom: 20px;
        font-size: 14px;
    }
    
    .alert-success {
        background-color: #dff0d8;
        color: #3c763d;
        border: 1px solid #d6e9c6;
    }
    
    .alert-danger {
        background-color: #f2dede;
        color: #a94442;
        border: 1px solid #ebccd1;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 3rem;
        grid-column: 1 / -1;
    }

    .empty-state i {
        font-size: 3rem;
        color: var(--text-light);
        margin-bottom: 1rem;
    }

    .empty-state p {
        color: var(--text-light);
        font-size: 1.1rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            padding: 1rem;
        }
        
        .header-container {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .categories-container {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>

<body>
    <?php include '../../views/layout/sidebar.php' ?>
    
    <div class="main-content">
        <div class="header-container">
            <h1 class="page-title">Category Dashboard</h1>
            <button class="btn-new" onclick="window.location.href='add_category.php'">
                <i class="fas fa-plus"></i> Add Category
            </button>
        </div>
        
        <!-- Notification Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Category Cards -->
        <div class="categories-container">
            <?php if (empty($categories)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>No categories found. Add your first category!</p>
                </div>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <div class="category-card">
                        <h3 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                        
                        <div class="category-meta">
                            <div class="meta-item">
                                <i class="fas fa-calendar-plus"></i>
                                <span>Created: <?php echo date('d M Y', strtotime($category['created_at'])); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-calendar-check"></i>
                                <span>Updated: <?php echo date('d M Y', strtotime($category['updated_at'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="category-actions">
                            <button class="btn-action btn-edit" onclick="window.location.href='edit_category.php?id=<?php echo $category['id']; ?>'">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn-action btn-delete" onclick="if(confirm('Are you sure you want to delete this category?')) { window.location.href='?delete=<?php echo $category['id']; ?>' }">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>