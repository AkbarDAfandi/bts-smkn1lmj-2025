<?php
session_start();
require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validasi input
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username dan password harus diisi";
        header("Location: index.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Verifikasi password yang sudah di-hash
            if (password_verify($password, $user['password'])) {
                // Cek role user
                if ($user['role'] !== 'admin') {
                    $_SESSION['error'] = "Anda tidak memiliki akses sebagai admin";
                    header("Location: index.php");
                    exit();
                }
                
                // Regenerate session ID untuk mencegah session fixation
                session_regenerate_id(true);
                
                // Set session data
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_role'] = $user['role'];  // Simpan role di session
                $_SESSION['logged_in'] = true;
                
                header("Location: ../views/dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Username atau password salah";
                header("Location: index.php");
                exit();
            }
        } 
    } catch (PDOException $e) {
        $_SESSION['error'] = "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
        error_log("Database error: " . $e->getMessage());
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}