<?php
session_start();
require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../app/models/Book.php';

// Check admin session
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini";
    header("Location: " . BASE_URL);
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID buku tidak valid";
    header("Location: " . BASE_URL . "admin/views/tahun/duaempat/duaempat.php");
    exit();
}

$bookId = (int)$_GET['id'];
$bookModel = new Book($pdo);

try {
    // Delete the book
    if ($bookModel->delete($bookId)) {
        $_SESSION['success'] = "Buku berhasil dihapus";
    } else {
        $_SESSION['error'] = "Gagal menghapus buku atau buku tidak ditemukan";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
}

// Redirect back to the books list
header("Location: " . BASE_URL . "admin/views/tahun/duaempat/duaempat.php");
exit();
?>