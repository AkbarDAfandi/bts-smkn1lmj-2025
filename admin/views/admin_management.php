<?php
session_start();
require_once __DIR__ . '/../../config.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini";
    header("Location: ../auth/index.php");
    exit();
}

// Get all admins
try {
    $stmt = $pdo->query("SELECT id, username, created_at FROM users WHERE role = 'admin' ORDER BY created_at DESC");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal mengambil data admin";
    error_log("Database error: " . $e->getMessage());
    $admins = [];
}

// Handle add admin form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_admin'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "Semua field harus diisi";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = "Password dan konfirmasi password tidak cocok";
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['error'] = "Username sudah digunakan";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new admin
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
                $stmt->execute([$username, $hashed_password]);
                
                $_SESSION['success'] = "Admin berhasil ditambahkan";
                header("Location: admin_management.php");
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Gagal menambahkan admin";
            error_log("Database error: " . $e->getMessage());
        }
    }
}

// Handle edit admin form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_admin'])) {
    $admin_id = $_POST['admin_id'];
    $new_username = trim($_POST['username']);
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input
    if (empty($new_username)) {
        $_SESSION['error'] = "Username tidak boleh kosong";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $_SESSION['error'] = "Password dan konfirmasi password tidak cocok";
    } else {
        try {
            // Check if username already exists (excluding current admin)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$new_username, $admin_id]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['error'] = "Username sudah digunakan";
            } else {
                if (!empty($new_password)) {
                    // Update both username and password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
                    $stmt->execute([$new_username, $hashed_password, $admin_id]);
                } else {
                    // Update only username
                    $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
                    $stmt->execute([$new_username, $admin_id]);
                }
                
                $_SESSION['success'] = "Admin berhasil diperbarui";
                header("Location: admin_management.php");
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Gagal memperbarui admin";
            error_log("Database error: " . $e->getMessage());
        }
    }
}

// Handle delete admin request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $admin_id = $_GET['delete'];
    
    // Prevent deleting yourself
    if ($admin_id == $_SESSION['admin_id']) {
        $_SESSION['error'] = "Anda tidak dapat menghapus akun sendiri";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'");
            $stmt->execute([$admin_id]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['success'] = "Admin berhasil dihapus";
            } else {
                $_SESSION['error'] = "Admin tidak ditemukan";
            }
            header("Location: admin_management.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Gagal menghapus admin";
            error_log("Database error: " . $e->getMessage());
        }
    }
}

// Get admin data for editing
$edit_admin = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $admin_id = $_GET['edit'];
    
    try {
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ? AND role = 'admin'");
        $stmt->execute([$admin_id]);
        $edit_admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$edit_admin) {
            $_SESSION['error'] = "Admin tidak ditemukan";
            header("Location: admin_management.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Gagal mengambil data admin";
        error_log("Database error: " . $e->getMessage());
        header("Location: admin_management.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Admin</title>
    <link rel="stylesheet" href="../public/css/all.min.css">
    <link rel="stylesheet" href="../public/css/sidebar.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .btn {
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            border: none;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0069d9;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 500px;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
        }
        
        @media (max-width: 768px) {
            .table {
                display: block;
                overflow-x: auto;
            }
            
            .modal-content {
                width: 95%;
                margin: 20% auto;
            }
        }
    </style>
</head>
<body>
    <?php include 'layout/sidebar.php' ?>
    
    <div class="main-content">
        <div class="container">
            <h2>Kelola Admin</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Add Admin Form (only for super admin) -->
            <?php if ($_SESSION['is_super_admin']): ?>
            <div class="card">
                <h3><?= $edit_admin ? 'Edit Admin' : 'Tambah Admin Baru' ?></h3>
                <form method="POST" action="">
                    <?php if ($edit_admin): ?>
                        <input type="hidden" name="admin_id" value="<?= $edit_admin['id'] ?>">
                        <input type="hidden" name="edit_admin" value="1">
                    <?php else: ?>
                        <input type="hidden" name="add_admin" value="1">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?= $edit_admin ? htmlspecialchars($edit_admin['username']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password"><?= $edit_admin ? 'Password Baru (biarkan kosong jika tidak ingin mengubah)' : 'Password' ?></label>
                        <input type="password" id="password" name="password" class="form-control" 
                               <?= !$edit_admin ? 'required' : '' ?>>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                               <?= !$edit_admin ? 'required' : '' ?>>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <?= $edit_admin ? 'Update Admin' : 'Tambah Admin' ?>
                    </button>
                    
                    <?php if ($edit_admin): ?>
                        <a href="admin_management.php" class="btn btn-warning">Batal</a>
                    <?php endif; ?>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- Admin List -->
            <div class="card">
                <h3>Daftar Admin</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Tanggal Dibuat</th>
                            <?php if ($_SESSION['is_super_admin']): ?>
                            <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($admins)): ?>
                            <tr>
                                <td colspan="3">Tidak ada admin</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td><?= htmlspecialchars($admin['username']) ?></td>
                                <td><?= date('d M Y', strtotime($admin['created_at'])) ?></td>
                                <?php if ($_SESSION['is_super_admin']): ?>
                                <td>
                                    <a href="admin_management.php?edit=<?= $admin['id'] ?>" 
                                       class="btn btn-warning">Edit</a>
                                    <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                    <a href="admin_management.php?delete=<?= $admin['id'] ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus admin ini?')">
                                        Hapus
                                    </a>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Function to show confirmation dialog for delete action
        function confirmDelete(adminId) {
            if (confirm("Apakah Anda yakin ingin menghapus admin ini?")) {
                window.location.href = "admin_management.php?delete=" + adminId;
            }
        }
    </script>
</body>
</html>