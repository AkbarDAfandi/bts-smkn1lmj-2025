<?php
session_start();
require_once __DIR__ . '../../../../config.php';
require_once __DIR__ . '/../../../app/models/Book.php';

// Check admin session
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini";
    header("Location: ../../../index.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Parameter tidak valid";
    header("Location: ../empty.php");
    exit();
}

$bookId = (int)$_GET['id'];
$bookModel = new Book($pdo);

try {
    // Delete the book
    $result = $bookModel->delete($bookId);
    
    if ($result['success']) {
        $_SESSION['success'] = "Buku berhasil dihapus";
        
        if (isset($result['files_deleted']) && !$result['files_deleted']) {
            $_SESSION['warning'] = "Buku berhasil dihapus dari database tetapi ada masalah menghapus beberapa file";
        }
        
        $selectedYear = $result['tahun_akademik'] ?? ($_GET['tahun'] ?? date('Y'));
    } else {
        $_SESSION['error'] = $result['error'] ?? "Gagal menghapus buku";
        $selectedYear = $_GET['tahun'] ?? date('Y');
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Terjadi kesalahan sistem: " . $e->getMessage();
    $selectedYear = $_GET['tahun'] ?? date('Y');
}

header("Location: year_books.php?tahun=" . urlencode($selectedYear));
exit();
?>