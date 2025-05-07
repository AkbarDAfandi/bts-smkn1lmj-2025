<?php
session_start();
require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../app/models/Book.php';
require_once __DIR__ . '/../function_year.php';

// Cek session dan role
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini";
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// Ambil tahun dari parameter URL
$selectedYear = $_GET['tahun'] ?? '';
$selectedYear = htmlspecialchars($selectedYear);

// Validasi tahun
try {
    $availableYears = getAvailableYears($pdo);
    if (!in_array($selectedYear, $availableYears)) {
        $_SESSION['error'] = "Tahun akademik tidak valid";
        header("Location: ../empty.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error validasi tahun: " . $e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan sistem";
    header("Location: ../empty.php");
    exit();
}

// Inisialisasi model Book
$bookModel = new Book($pdo);

// Ambil data buku berdasarkan tahun
try {
    $books = $bookModel->getBooksByAcademicYear($selectedYear);
} catch (PDOException $e) {
    error_log("Error get books: " . $e->getMessage());
    $books = [];
    $_SESSION['error'] = "Gagal memuat data buku";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tahun <?= $selectedYear ?></title>
    <link rel="stylesheet" href="../../../public/css/all.min.css">
    <link rel="stylesheet" href="../../../public/css/sidebar.css">
    <link rel="stylesheet" href="../../../public/css/dashboard.css">
    <link rel="stylesheet" href="../../../public/css/books/dualima/dualima.css">
</head>
<body>
    <?php include '../../layout/sidebar.php' ?>
    
    <div class="main-content">
        <div class="header">
            <h2 class="greeting">Buku Tahun <?= $selectedYear ?></h2>
            <div class="user-info">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search..." id="searchInput">
                </div>
                <i class="far fa-bell"></i>
            </div>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="sticky-action-header">
            <div class="button-container">
                <button class="btn-back modern-back" onclick="window.location.href='../empty.php'">
                    <i class="fas fa-chevron-left"></i>
                    <span>Back</span>
                </button>
                <button class="btn-new" onclick="window.location.href='add_book.php?tahun=<?= $selectedYear ?>'">
                    <i class="fas fa-plus"></i> Add Book
                </button>
            </div>
        </div>

        <?php if (!empty($books)): ?>
            <div class="book-list-container">
                <?php foreach ($books as $book): 
                    $coverPath = !empty($book['cover_path']) ? 
                        "../../../../public/uploads/" . htmlspecialchars($book['cover_path']) : 
                        "../../../../public/images/default-cover.jpg";
                    
                    $contentPath = !empty($book['content_path']) ? 
                        "../../../../public/uploads/" . htmlspecialchars($book['content_path']) : 
                        "#";
                ?>
                    <div class="book-card">
                        <div class="book-cover">
                            <img src="<?= $coverPath ?>" alt="Cover <?= htmlspecialchars($book['judul']) ?>" 
                                 onerror="this.src='../../../../public/images/default-cover.jpg'">
                        </div>
                        <div class="book-details">
                            <h3><?= htmlspecialchars($book['judul']) ?></h3>
                            <p>Penerbit: <?= htmlspecialchars($book['penerbit'] ?? '-') ?></p>
                            <p>Tahun: <?= htmlspecialchars($book['tahun']) ?></p>
                            <div class="book-actions">
                                <a href="edit_book.php?id=<?= $book['id'] ?>&tahun=<?= $selectedYear ?>" class="btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete_book.php?id=<?= $book['id'] ?>&tahun=<?= $selectedYear ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                                <?php if (!empty($book['content_path'])): ?>
                                    <a href="<?= $contentPath ?>" class="btn-view" target="_blank">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <img src="../../../../public/images/empty-book.png" alt="No books">
                <h3>Tidak ada buku untuk tahun <?= $selectedYear ?></h3>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const bookCards = document.querySelectorAll('.book-card');
            
            bookCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const publisher = card.querySelector('p:nth-of-type(1)').textContent.toLowerCase();
                const year = card.querySelector('p:nth-of-type(2)').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || publisher.includes(searchTerm) || year.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>