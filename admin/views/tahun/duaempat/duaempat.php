<?php
session_start();
require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../app/models/Book.php';

// Cek session dan role
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini";
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// Tampilkan alert success jika ada
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>';
    unset($_SESSION['success']);
}

$bookModel = new Book($pdo);
$selectedYear = '2024';
$bukuTahunan = $bookModel->getBooksByAcademicYear($selectedYear);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Buku</title>
    <link rel="stylesheet" href="../../../public/css/all.min.css">
    <link rel="stylesheet" href="../../../public/css/sidebar.css">
    <link rel="stylesheet" href="../../../public/css/dashboard.css">
    <link rel="stylesheet" href="../../../public/css/books/duaempat/duaempat.css">
</head>

<body>
    <?php include '../../../views/layout/sidebar.php' ?>

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
                Daftar Buku
            </h2>
            <div class="user-info">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>
                <i class="far fa-bell"></i>
            </div>
        </div>

        <div class="sticky-action-header">
            <div class="button-container">
                <button class="btn-back modern-back" onclick="window.location.href='../empty.php'">
                    <i class="fas fa-chevron-left"></i>
                    <span>Back</span>
                </button>
                <button class="btn-new" onclick="window.location.href='add_book.php'">Add Book</button>
            </div>
            <h2 class="section-title">Siswa dan Siswi</h2>
        </div>

        <div class="carousel-wrapper">
            <div class="component-container" id="cardContainer">
                <?php foreach ($bukuTahunan as $buku) : ?>
                    <div class="component-card">
                        <div class="image-container">
                            <img src="../../../public/uploads/<?php echo $buku['cover_path']; ?>" alt="Sampul <?php echo $buku['judul']; ?>">
                        </div>
                        <div class="card-content">
                            <h3><?php echo $buku['judul']; ?></h3>
                            <p><?php echo $buku['penerbit']; ?></p>
                            <p>Kategori: <?php echo $buku['category_name']; ?></p>
                            <p>Tahun: <?= htmlspecialchars($buku['tahun']) ?></p>
                            <div class="action-buttons">
                                <a href="edit_book.php?id=<?php echo $buku['id']; ?>" class="btn-edit">Edit</a>
                                <a href="delete_book.php?id=<?php echo $buku['id']; ?>" class="btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?')">Delete</a>
                                <a href="../../../public/uploads/<?php echo $buku['content_path']; ?>" class="lihat-selengkapnya" target="_blank">Lihat Buku</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>

</html>