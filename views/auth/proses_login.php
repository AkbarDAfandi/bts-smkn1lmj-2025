<?php
session_start();
require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username dan password harus diisi";
        header("Location: " . BASE_URL . "views/auth/login.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            if ($password === $user['password']) {
                session_regenerate_id(true);
                // Pisahkan session admin dan user
                if ($user['role'] === 'admin') {
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_role'] = 'admin';
                    header("Location: " . BASE_URL . "admin/dashboard.php");
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = 'user';
                    header("Location: " . BASE_URL . "download/download.php");
                }
                exit();
            } else {
                $_SESSION['error'] = "Username atau password salah";
                header("Location: " . BASE_URL . "views/auth/login.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "User tidak ditemukan";
            header("Location: " . BASE_URL . "views/auth/login.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
        error_log("Database error: " . $e->getMessage());
        header("Location: " . BASE_URL . "views/auth/login.php");
        exit();
    }
} else {
    header("Location: " . BASE_URL . "views/auth/login.php");
    exit();
}