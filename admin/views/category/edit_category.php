<?php
session_start();
require_once __DIR__ . '/../../../config.php';

// Check admin session and role
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu";
    header("Location: index.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID kategori tidak valid";
    header("Location: category.php");
    exit();
}

$id = $_GET['id'];

// Get current category data
try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();
    
    if (!$category) {
        $_SESSION['error'] = "Kategori tidak ditemukan";
        header("Location: category.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal mengambil data kategori: " . $e->getMessage();
    header("Location: category.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = trim($_POST['category_name'] ?? '');

    if (empty($categoryName)) {
        $_SESSION['error'] = "Nama kategori tidak boleh kosong";
    } else {
        try {
            // Check if category with same name already exists (excluding current category)
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
            $stmt->execute([$categoryName, $id]);
            
            if ($stmt->fetch()) {
                $_SESSION['error'] = "Kategori dengan nama tersebut sudah ada";
            } else {
                // Update category
                $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
                $stmt->execute([$categoryName, $id]);
                
                $_SESSION['success'] = "Kategori berhasil diperbarui";
                header("Location: category.php");
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Gagal memperbarui kategori: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category | BTS Admin</title>
    <link rel="stylesheet" href="../../public/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/sidebar.css">
    <link rel="stylesheet" href="../../public/css/category/add_category.css">
</head>

<body>
    <?php include '../../views/layout/sidebar.php' ?>

    <div class="main-content full-screen-layout">
        <!-- Full-width header -->
        <div class="page-header full-width-header">
            <div class="header-content">
                <h1 class="header-title">Edit Category</h1>
            </div>
        </div>

        <!-- Full-width content area -->
        <div class="content-container full-width-content">
            <div class="form-card full-width-form">
                <form action="" method="POST">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Category Name Section -->
                    <div class="form-section">
                        <div class="form-group">
                            <label for="category_name">Category Name</label>
                            <input type="text" id="category_name" name="category_name" 
                                   placeholder="Enter category name" required
                                   value="<?php echo isset($_POST['category_name']) ? htmlspecialchars($_POST['category_name']) : htmlspecialchars($category['name']); ?>">
                            <p class="hint-text">Keep it short and descriptive</p>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="button" class="btn btn-cancel" onclick="window.history.back()">Cancel</button>
                        <button type="submit" class="btn btn-submit" style="margin-right: 20px;">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const categoryName = document.getElementById('category_name').value.trim();
            if (!categoryName) {
                e.preventDefault();
                alert('Please enter a category name');
                document.getElementById('category_name').focus();
            }
        });
    </script>
</body>
</html>