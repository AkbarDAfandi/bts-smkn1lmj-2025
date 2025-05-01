<?php
session_start();
require_once __DIR__ . '/../../../../config.php';

// Cek session dan role
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini";
    header("Location: " . BASE_URL . "index.php");
    exit();
}


// Data buku (simulasi dari database)
$dataBuku = [
    2023 => [
        ['judul' => 'ACCOUNTING 3', 'penulis' => 'oleh SMKN 1 Lumajang', 'gambar' => 'Akuntansi/sampul.jpg'],
        ['judul' => 'MANAJEMEN PERKANTORAN 1', 'penulis' => 'oleh SMKN 1 Lumajang', 'gambar' => 'Manajemen_perkantoran/sampul.jpg'],
    ],
    2024 => [
        ['judul' => 'ACCOUNTING 3', 'penulis' => 'oleh SMKN 1 Lumajang', 'gambar' => 'Akuntansi/sampul.jpg'],
        ['judul' => 'MANAJEMEN PERKANTORAN 2', 'penulis' => 'oleh SMKN 1 Lumajang', 'gambar' => 'Manajemen_perkantoran/sampul.jpg'],
    ],
    2025 => [
        ['judul' => 'ACCOUNTING 3', 'penulis' => 'oleh SMKN 1 Lumajang', 'gambar' => 'Akuntansi/sampul.jpg'],
        ['judul' => 'MANAJEMEN PERKANTORAN 3', 'penulis' => 'oleh SMKN 1 Lumajang', 'gambar' => 'Manajemen_perkantoran/sampul.jpg'],
    ],
    2026 => [
        ['judul' => 'ACCOUNTING 4', 'penulis' => 'oleh SMKN 1 Lumajang', 'gambar' => 'Akuntansi/sampul.jpg'],
        ['judul' => 'MANAJEMEN PERKANTORAN 4', 'penulis' => 'oleh SMKN 1 Lumajang', 'gambar' => 'Manajemen_perkantoran/sampul.jpg'],
    ],
];

// Ambil tahun yang dipilih dari URL (jika ada)
$selectedTahun = isset($_GET['tahun']) ? $_GET['tahun'] : null;

// Generate opsi dropdown secara dinamis dari tahun yang ada di data
$daftarTahun = array_keys($dataBuku);
rsort($daftarTahun); // Urutkan tahun dari terbaru

// Jika tidak ada tahun yang dipilih, tampilkan halaman kosong
if (!$selectedTahun) {
    include 'empty_state.php';
    exit();
}

// Jika tahun dipilih tapi tidak ada di data
if (!isset($dataBuku[$selectedTahun])) {
    $_SESSION['error'] = "Tahun $selectedTahun tidak ditemukan";
    header("Location: " . BASE_URL . "admin/tahun/duaempat.php");
    exit();
}

$bukuTahunan = $dataBuku[$selectedTahun];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tahunan <?php echo $selectedTahun; ?></title>
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
                List Buku Tahunan Siswa <?php echo $selectedTahun; ?>
            </h2>
            <div class="user-info">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>
                <i class="far fa-bell"></i>
            </div>
        </div>

        <div class="dropdown-container">
            <div class="dropdown-wrapper" id="dropdownTahunWrapper"></div>
            <div class="button-group">
                <button class="btn-back modern-back"  onclick="window.history.back()">
                    <i class="fas fa-chevron-left"></i>
                    <span>Back</span>
                </button>
                <button class="btn-new" onclick="window.location.href='add_book.php'">Add Book</button>
            </div>
        </div>

        <h2 class="section-title">Siswa dan Siswi</h2>

        <div class="carousel-wrapper">
            <div class="component-container" id="cardContainer">
                <?php foreach ($bukuTahunan as $buku) : ?>
                    <div class="component-card">
                        <div class="image-container">
                            <img src="../../../public/assets/img/tahun/<?php echo $selectedTahun; ?>/<?php echo $buku['gambar']; ?>" alt="Sampul <?php echo $buku['judul']; ?>">
                        </div>
                        <div class="card-content">
                            <h3><?php echo $buku['judul']; ?></h3>
                            <p><?php echo $buku['penulis']; ?></p>
                            <button class="lihat-selengkapnya">Lihat Selengkapnya</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="../../public/assets/js/tahun/duaempat.js"></script>
</body>

</html>