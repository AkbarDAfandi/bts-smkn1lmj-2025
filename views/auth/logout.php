<?php
session_start();
require_once __DIR__ . '/../../config.php';

// Hapus semua data sesi spesifik
unset($_SESSION['user_id']);
unset($_SESSION['user_role']);
unset($_SESSION['username']);

// Regenerasi ID sesi untuk mencegah session fixation
session_regenerate_id(true);

// Hapus cookie sesi jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Hancurkan sesi
session_destroy();

// Set pesan sukses sebelum redirect
$_SESSION['success'] = "Anda telah berhasil logout";

// Redirect ke halaman login
header("Location: ../years/2024/index.php");
exit();