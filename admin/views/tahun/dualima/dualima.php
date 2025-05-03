<?php
session_start();
require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../app/models/Book.php';

// Cek session dan role (sama seperti file lain)
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini";
    header("Location: " . BASE_URL . "index.php");
    exit();
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tahun <?= $selectedYear ?></title>
</head>
<body>
    <h1>Daftar Buku Tahun <?= $selectedYear ?></h1>

    <?php if (!empty($books)): ?>
        <ul>
            <?php foreach ($books as $book): ?>
                <li><?= $book['judul_buku'] ?> (Tahun: <?= $book['tahun_terbit'] ?>)</li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Tidak ada buku untuk tahun <?= $selectedYear ?>.</p>
    <?php endif; ?>
</body>
</html>