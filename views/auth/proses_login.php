<?php
session_start();
require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];

    if (empty($password)) {
        $_SESSION['error'] = "Password harus diisi";
        header("Location: auth/login.php");
        exit();
    }

    try {
        // Cari user berdasarkan password saja (asumsi password unik)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE password = ?");
        $stmt->execute([$password]);
        $user = $stmt->fetch();

        if ($user) {
            session_regenerate_id(true);
            
            // Pisahkan session admin dan user
            if ($user['role'] === 'admin') {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_role'] = 'admin';
                header("Location: ../../admin/dashboard.php");
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = 'user';
                header("Location: ../../download/download.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Password salah";
            header("Location: auth/login.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
        error_log("Database error: " . $e->getMessage());
        header("Location: auth/login.php");
        exit();
    }
} else {
    header("Location: auth/login.php");
    exit();
}