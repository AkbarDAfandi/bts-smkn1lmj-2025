<?php
session_start();
require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '../../../../../app/Models/Book.php';

// Cek session dan role
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini";
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$bookModel = new Book($pdo);
$bukuTahunan = $bookModel->getAll();
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

        <div class="button-group">
            <button class="btn-back modern-back" onclick="window.history.back()">
                <i class="fas fa-chevron-left"></i>
                <span>Back</span>
            </button>
            <button class="btn-new" onclick="window.location.href='add_book.php'">Add Book</button>
        </div>

        <h2 class="section-title">Daftar Buku</h2>

        <div class="carousel-wrapper">
            <div class="component-container" id="cardContainer">
                <?php foreach ($bukuTahunan as $buku) : ?>
                    <div class="component-card">
                        <div class="image-container">
                        <img src="../../../../public/uploads/cover/<?php echo $buku['cover_path']; ?> " alt="Sampul <?php echo $buku['judul']; ?>">
                        </div>
                        <div class="card-content">
                            <h3><?php echo $buku['judul']; ?></h3>
                            <p><?php echo $buku['penerbit']; ?></p>
                            <p>Kategori: <?php echo $buku['kategori_name']; ?></p>
                            <div class="action-buttons">
                                <a href="edit_book.php?id=<?php echo $buku['id']; ?>" class="btn-edit">Edit</a>
                                <a href="delete_book.php?id=<?php echo $buku['id']; ?>" class="btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?')">Delete</a>
                                <a href="../../../../public/uploads<?php echo $buku['content_path']; ?>" class="lihat-selengkapnya" target="_blank">Lihat Buku</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>