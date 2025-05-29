<?php
session_start();
require_once __DIR__ . '../../../../config.php';
require_once __DIR__ . '../../../../app/models/Book.php';
require_once __DIR__ . '../../tahun/function_year.php';

// Cek session dan role
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman ini';
    header('Location: index.php');
    exit();
}

// Ambil tahun dari parameter URL
$selectedYear = isset($_GET['tahun']) ? $_GET['tahun'] : null;

// Validasi tahun
if (!$selectedYear || !is_numeric($selectedYear)) {
    $_SESSION['error'] = 'Tahun akademik tidak valid';
    header('Location: ../empty.php');
    exit();
}

// Cek apakah tahun tersedia di database
$availableYears = getAvailableYears($pdo);
if (!in_array($selectedYear, $availableYears)) {
    $_SESSION['error'] = 'Tahun akademik tidak ditemukan';
    header('Location: ../empty.php');
    exit();
}

try {
    $bookModel = new Book($pdo);
    $allBooks = $bookModel->getBooksByAcademicYear($selectedYear);

    // Kelompokkan berdasarkan kategori
    $booksByCategory = [];
    foreach ($allBooks as $book) {
        $booksByCategory[$book['category_name']][] = $book;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Gagal mengambil data: ' . $e->getMessage();
    $booksByCategory = [];
    error_log('Database error: ' . $e->getMessage());
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    $booksByCategory = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Buku Tahun <?= htmlspecialchars($selectedYear) ?></title>
    <link rel="stylesheet" href="../../public/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/sidebar.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <link rel="stylesheet" href="../../public/css/books/year_books.css">
</head>
    <style>
        
    </style>
</head>

<body>
    <?php include '../../views/layout/sidebar.php'; ?>

    <div class="main-content">
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success'] ?>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
        <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error'] ?>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="header">
            <h2 class="greeting">
                Daftar Buku Tahun <?= htmlspecialchars($selectedYear) ?>
            </h2>
            <div class="user-info"></div>
        </div>

        <div class="sticky-action-header" style="margin-bottom: 50px;">
            <div class="button-container">
                <button class="btn-back modern-back" onclick="window.location.href='../tahun/empty.php'">
                    <i class="fas fa-chevron-left"></i>
                    <span>Back</span>
                </button>
                <button class="btn-new" onclick="window.location.href='add_book.php?tahun=<?= $selectedYear ?>'">Add Book</button>
            </div>
        </div>

        <?php if (empty($booksByCategory)): ?>
        <div class="alert alert-info">
            Tidak ada buku yang ditemukan untuk tahun akademik <?= htmlspecialchars($selectedYear) ?>
        </div>
        <?php else: ?>
        <?php foreach ($booksByCategory as $categoryName => $books): ?>
        <div class="category-section">
            <div class="category-header">
                <h2 class="category-title"><?= htmlspecialchars($categoryName) ?></h2>
            </div>

            <div class="component-container">
                <?php foreach ($books as $buku): ?>
                <div class="component-card">
                    <div class="image-container">
                        <img src="../../public/uploads/<?= htmlspecialchars($buku['cover_path']) ?>" 
                             alt="Sampul <?= htmlspecialchars($buku['judul']) ?>">
                    </div>
                    <div class="card-content">
                        <h3><?= htmlspecialchars($buku['judul']) ?></h3>
                        
                        <p class="author"><?= htmlspecialchars($buku['penerbit'] ?? '-') ?></p>
                        <p class="category">Kategori: <?= htmlspecialchars($categoryName) ?></p>
                        <p class="year">Tahun: <?= htmlspecialchars($buku['tahun']) ?></p>
                        <div class="action-buttons">
                            <a href="edit_book.php?id=<?= $buku['id'] ?>&tahun=<?= $selectedYear ?>" class="btn-action btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete.php?id=<?= $buku['id'] ?>&tahun=<?= $selectedYear ?>" class="btn-action btn-delete"
                                onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                               <a href="../../../public/uploads/<?= $buku['content_path'] ?>" class="btn-action btn-view"
                                target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>